<?php

namespace furbo\craftschedule;

use Craft;
use furbo\craftschedule\records\ScheduledTaskRecord;

class Schedule
{
    private static array $pendingTasks = [];
    private static bool $registered = false;

    public static function command(string $command, array $params = []): PendingTask
    {
        $task = new PendingTask($command, $params);
        self::$pendingTasks[] = $task;
        return $task;
    }

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;

        $plugin = Craft::$app->plugins->getPlugin('craft-schedule');
        if (!$plugin) {
            return;
        }

        $service = $plugin->scheduleService;

        foreach (self::$pendingTasks as $task) {
            $service->upsertTask($task);
            $task->persisted = true;
        }

        self::$pendingTasks = [];
    }

    public static function getPendingTasks(): array
    {
        return self::$pendingTasks;
    }

    public static function reset(): void
    {
        self::$pendingTasks = [];
        self::$registered = false;
    }
}
