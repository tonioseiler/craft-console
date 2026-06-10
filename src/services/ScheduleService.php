<?php

namespace furbo\craftschedule\services;

use Craft;
use craft\db\Query;
use furbo\craftschedule\PendingTask;
use furbo\craftschedule\records\ScheduledTaskRecord;
use yii\base\Component;

class ScheduleService extends Component
{
    public function upsertTask(PendingTask $task): ScheduledTaskRecord
    {
        $paramsJson = json_encode($task->params);
        $fingerprint = hash('sha256', $task->command . '|' . $paramsJson . '|' . ($task->cronExpression ?? ''));

        $record = ScheduledTaskRecord::find()
            ->where(['fingerprint' => $fingerprint])
            ->one();

        if (!$record) {
            $record = new ScheduledTaskRecord();
            $record->fingerprint = $fingerprint;
        }

        $record->command = $task->command;
        $record->params = $paramsJson;
        $record->schedule = $task->cronExpression ?? '* * * * *';
        $record->enabled = true;

        $record->save(false);

        return $record;
    }

    public function getAll(): array
    {
        return array_map(
            fn(ScheduledTaskRecord $r) => $r->toArray(),
            ScheduledTaskRecord::find()
                ->orderBy(['command' => SORT_ASC])
                ->all()
        );
    }

    public function getDue(): array
    {
        $now = new \DateTimeImmutable();
        $tasks = ScheduledTaskRecord::find()
            ->where(['enabled' => true])
            ->all();

        $due = [];

        foreach ($tasks as $task) {
            if ($this->isDue($task->schedule, $now)) {
                $due[] = $task;
            }
        }

        return $due;
    }

    public function toggle(int $id): array
    {
        $record = ScheduledTaskRecord::findOne($id);
        if (!$record) {
            return ['error' => 'Scheduled task not found.'];
        }
        $record->enabled = !$record->enabled;
        $record->save(false);
        return ['task' => $record->toArray()];
    }

    public function isDue(string $expression, \DateTimeImmutable $now): bool
    {
        $expression = $this->resolveShortcut($expression);

        $parts = preg_split('/\s+/', trim($expression));
        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $weekday] = $parts;

        return $this->matchesField($minute, (int) $now->format('i'), 0, 59) &&
            $this->matchesField($hour, (int) $now->format('G'), 0, 23) &&
            $this->matchesField($day, (int) $now->format('j'), 1, 31) &&
            $this->matchesField($month, (int) $now->format('n'), 1, 12, [
                'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
                'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
            ]) &&
            $this->matchesField($weekday, (int) $now->format('w'), 0, 7, [
                'sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6,
            ]);
    }

    private function resolveShortcut(string $expression): string
    {
        return match (strtolower(trim($expression))) {
            '@yearly', '@annually' => '0 0 1 1 *',
            '@monthly' => '0 0 1 * *',
            '@weekly' => '0 0 * * 0',
            '@daily' => '0 0 * * *',
            '@hourly' => '0 * * * *',
            '@reboot' => '* * * * *',
            default => $expression,
        };
    }

    private function matchesField(string $field, int $value, int $min, int $max, array $names = []): bool
    {
        if (str_contains($field, ',')) {
            foreach (explode(',', $field) as $part) {
                if ($this->matchesSingleField(trim($part), $value, $min, $max, $names)) {
                    return true;
                }
            }
            return false;
        }

        return $this->matchesSingleField($field, $value, $min, $max, $names);
    }

    private function matchesSingleField(string $field, int $value, int $min, int $max, array $names): bool
    {
        $fieldLower = strtolower($field);
        $fieldNumeric = isset($names[$fieldLower]) ? $names[$fieldLower] : $field;

        if ($field === '*') {
            return true;
        }

        if (str_contains($field, '/')) {
            [$range, $step] = explode('/', $field, 2);
            $step = (int) $step;
            if ($step === 0) return false;

            if ($range === '*') {
                $rangeMin = $min;
                $rangeMax = $max;
            } elseif (str_contains($range, '-')) {
                [$rangeMin, $rangeMax] = explode('-', $range, 2);
                $rangeMin = (int) $rangeMin;
                $rangeMax = (int) $rangeMax;
            } else {
                return false;
            }

            for ($i = $rangeMin; $i <= $rangeMax; $i += $step) {
                if ($i === $value) return true;
            }
            return false;
        }

        if (str_contains($field, '-')) {
            [$rangeMin, $rangeMax] = explode('-', $field, 2);
            return $value >= (int) $rangeMin && $value <= (int) $rangeMax;
        }

        return (int) $fieldNumeric === $value;
    }

    public function runTask(ScheduledTaskRecord $task): array
    {
        $craftPath = Craft::getAlias('@root') . '/craft';

        if (!file_exists($craftPath)) {
            return ['error' => "craft file not found at $craftPath", 'output' => '', 'exitCode' => -1];
        }

        $phpBin = $this->resolvePhpBinary();

        $paramsStr = '';
        $params = json_decode($task->params, true) ?? [];
        foreach ($params as $p) {
            $paramsStr .= ' ' . escapeshellarg((string) $p);
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'cs_');
        $cmd = sprintf(
            '%s %s %s%s > %s 2>/dev/null',
            escapeshellcmd($phpBin),
            escapeshellarg($craftPath),
            escapeshellarg($task->command),
            $paramsStr,
            escapeshellarg($tmpFile)
        );

        $code = -1;
        exec($cmd, $_, $code);

        $stdout = '';
        if (file_exists($tmpFile)) {
            $stdout = file_get_contents($tmpFile);
            unlink($tmpFile);
        }

        return [
            'exitCode' => $code,
            'output' => $stdout,
        ];
    }

    private function resolvePhpBinary(): string
    {
        $settings = Craft::$app->plugins->getPlugin('craft-schedule')->getSettings();
        if (!empty($settings->phpPath)) {
            $path = trim($settings->phpPath);
            if (is_executable($path)) {
                return $path;
            }
        }

        $phpBin = PHP_BINARY;

        if (str_contains($phpBin, 'php-fpm') || !is_executable($phpBin)) {
            $dir = dirname($phpBin);
            $candidates = [
                $dir . '/php',
                dirname($dir) . '/bin/php',
                trim((string) shell_exec('which php 2>/dev/null')),
                'php',
            ];
            $phpBin = 'php';
            foreach ($candidates as $c) {
                if ($c !== '' && is_executable($c)) {
                    $phpBin = $c;
                    break;
                }
            }
        }

        return $phpBin;
    }
}
