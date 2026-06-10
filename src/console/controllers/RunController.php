<?php

namespace furbo\craftschedule\console\controllers;

use Craft;
use craft\console\Controller;
use furbo\craftschedule\CraftSchedule;
use yii\console\ExitCode;

class RunController extends Controller
{
    public function actionIndex(): int
    {
        $tasks = CraftSchedule::$plugin->scheduleService->getDue();

        if (empty($tasks)) {
            $this->stdout("No scheduled tasks due." . PHP_EOL);
            return ExitCode::OK;
        }

        $this->stdout("Running " . count($tasks) . " task(s)..." . PHP_EOL);
        $runCount = 0;

        foreach ($tasks as $task) {
            $params = json_decode($task->params, true) ?? [];
            $paramsStr = !empty($params) ? ' ' . implode(' ', $params) : '';
            $this->stdout("{$task->command}{$paramsStr}... ");

            $result = CraftSchedule::$plugin->scheduleService->runTask($task);

            if ($result['exitCode'] === 0) {
                $this->stdout("OK" . PHP_EOL);
            } else {
                $this->stdout("ERROR (exit code {$result['exitCode']})" . PHP_EOL);
            }

            if (!empty($result['output'])) {
                $this->stdout($result['output'] . PHP_EOL);
            }

            $runCount++;
        }

        $this->stdout("Done. Ran {$runCount} task(s)." . PHP_EOL);
        return ExitCode::OK;
    }
}
