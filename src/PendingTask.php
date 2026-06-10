<?php

namespace furbo\craftschedule;

class PendingTask
{
    public string $command;
    public array $params;
    public ?string $cronExpression = null;
    public ?string $timezone = null;
    public bool $persisted = false;

    public function __construct(string $command, array $params = [])
    {
        $this->command = $command;
        $this->params = $params;
    }

    public function cron(string $expression): self
    {
        $this->cronExpression = $expression;
        return $this;
    }

    public function everyMinute(): self
    {
        return $this->cron('* * * * *');
    }

    public function everyTwoMinutes(): self
    {
        return $this->cron('*/2 * * * *');
    }

    public function everyThreeMinutes(): self
    {
        return $this->cron('*/3 * * * *');
    }

    public function everyFourMinutes(): self
    {
        return $this->cron('*/4 * * * *');
    }

    public function everyFiveMinutes(): self
    {
        return $this->cron('*/5 * * * *');
    }

    public function everyTenMinutes(): self
    {
        return $this->cron('*/10 * * * *');
    }

    public function everyFifteenMinutes(): self
    {
        return $this->cron('*/15 * * * *');
    }

    public function everyThirtyMinutes(): self
    {
        return $this->cron('*/30 * * * *');
    }

    public function hourly(): self
    {
        return $this->cron('0 * * * *');
    }

    public function hourlyAt(array|int $offset): self
    {
        $minutes = is_array($offset) ? implode(',', $offset) : $offset;
        return $this->cron("{$minutes} * * * *");
    }

    public function daily(): self
    {
        return $this->cron('0 0 * * *');
    }

    public function dailyAt(string $time): self
    {
        $parts = explode(':', $time);
        $hour = (int) ($parts[0] ?? 0);
        $minute = (int) ($parts[1] ?? 0);
        return $this->cron("{$minute} {$hour} * * *");
    }

    public function twiceDaily(int $first = 1, int $second = 13): self
    {
        return $this->cron("0 {$first},{$second} * * *");
    }

    public function weekly(): self
    {
        return $this->cron('0 0 * * 0');
    }

    public function weeklyOn(int $day, string $time = '0:0'): self
    {
        $parts = explode(':', $time);
        $hour = (int) ($parts[0] ?? 0);
        $minute = (int) ($parts[1] ?? 0);
        return $this->cron("{$minute} {$hour} * * {$day}");
    }

    public function monthly(): self
    {
        return $this->cron('0 0 1 * *');
    }

    public function monthlyOn(int $day = 1, string $time = '0:0'): self
    {
        $parts = explode(':', $time);
        $hour = (int) ($parts[0] ?? 0);
        $minute = (int) ($parts[1] ?? 0);
        return $this->cron("{$minute} {$hour} {$day} * *");
    }

    public function twiceMonthly(int $first = 1, int $second = 16, string $time = '0:0'): self
    {
        $parts = explode(':', $time);
        $hour = (int) ($parts[0] ?? 0);
        $minute = (int) ($parts[1] ?? 0);
        return $this->cron("{$minute} {$hour} {$first},{$second} * *");
    }

    public function lastDayOfMonth(string $time = '0:0'): self
    {
        $parts = explode(':', $time);
        $hour = (int) ($parts[0] ?? 0);
        $minute = (int) ($parts[1] ?? 0);
        return $this->cron("{$minute} {$hour} 28-31 * *");
    }

    public function yearly(): self
    {
        return $this->cron('0 0 1 1 *');
    }

    public function weekdays(): self
    {
        return $this->cron('* * * * 1-5');
    }

    public function weekends(): self
    {
        return $this->cron('* * * * 0,6');
    }

    public function sundays(): self
    {
        return $this->cron('* * * * 0');
    }

    public function mondays(): self
    {
        return $this->cron('* * * * 1');
    }

    public function tuesdays(): self
    {
        return $this->cron('* * * * 2');
    }

    public function wednesdays(): self
    {
        return $this->cron('* * * * 3');
    }

    public function thursdays(): self
    {
        return $this->cron('* * * * 4');
    }

    public function fridays(): self
    {
        return $this->cron('* * * * 5');
    }

    public function saturdays(): self
    {
        return $this->cron('* * * * 6');
    }

    public function timezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }
}
