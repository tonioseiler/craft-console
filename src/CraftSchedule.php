<?php

namespace furbo\craftschedule;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Utilities;
use furbo\craftschedule\models\Settings;
use furbo\craftschedule\services\ScheduleService;
use furbo\craftschedule\utilities\ScheduleUtility;
use yii\base\Event;

class CraftSchedule extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static CraftSchedule $plugin;

    public static function config(): array
    {
        return [
            'components' => [
                'scheduleService' => ScheduleService::class,
            ],
        ];
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('craft-schedule/settings', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    public function detectPhpBinary(): string
    {
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

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ScheduleUtility::class;
            }
        );

        Craft::$app->onInit(function () {
            Schedule::register();
        });
    }
}
