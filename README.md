# Craft Schedule

A [Craft CMS](https://craftcms.com/) 5 plugin for managing recurring console tasks with a Laravel-inspired fluent API.

## Features

- **Code-defined tasks** — Schedule tasks using a clean fluent API: `Schedule::command(...)->daily()`
- **Auto-registration** — Tasks are automatically stored in the database when your plugin or module initializes
- **Enable/disable toggle** — View and toggle tasks on/off from the control panel
- **CLI runner** — Console command `craft-schedule/run` for system crontab execution
- **Full cron support** — Standard 5-field expressions plus shortcuts (`@daily`, `@hourly`, etc.)
- **Settings page** — Configure PHP binary path for custom environments

## Requirements

- Craft CMS 5.0.0+
- PHP 8.2+

## Installation

```bash
# Install via Composer
composer require furbo/craft-schedule

# Install the plugin
php craft plugin/install craft-schedule
```

## Usage

### Defining scheduled tasks

Tasks are defined in your plugin or module's `init()` method using the fluent API:

```php
use furbo\craftschedule\Schedule;

// In your plugin/module init():
public function init(): void
{
    parent::init();

    Schedule::command('cache/flush-all')->daily();
    Schedule::command('backup/db')->hourly();
    Schedule::command('send-emails/send', ['--limit=50'])->everyFifteenMinutes();
    Schedule::command('report/generate', ['weekly'])->weekly()->fridays();
}
```

The plugin automatically persists all tasks to the database after all plugins and modules have initialized.

### Frequency methods

| Method | Cron expression |
|--------|----------------|
| `->everyMinute()` | `* * * * *` |
| `->everyTwoMinutes()` | `*/2 * * * *` |
| `->everyFiveMinutes()` | `*/5 * * * *` |
| `->everyTenMinutes()` | `*/10 * * * *` |
| `->everyFifteenMinutes()` | `*/15 * * * *` |
| `->everyThirtyMinutes()` | `*/30 * * * *` |
| `->hourly()` | `0 * * * *` |
| `->hourlyAt(15)` | `15 * * * *` |
| `->daily()` | `0 0 * * *` |
| `->dailyAt('13:00')` | `0 13 * * *` |
| `->twiceDaily(1, 13)` | `0 1,13 * * *` |
| `->weekly()` | `0 0 * * 0` |
| `->weeklyOn(1, '8:00')` | `0 8 * * 1` |
| `->monthly()` | `0 0 1 * *` |
| `->monthlyOn(15, '10:00')` | `0 10 15 * *` |
| `->twiceMonthly(1, 16)` | `0 0 1,16 * *` |
| `->yearly()` | `0 0 1 1 *` |
| `->weekdays()` | `* * * * 1-5` |
| `->weekends()` | `* * * * 0,6` |
| `->sundays()` / `->mondays()` / etc. | `* * * * 0..6` |
| `->cron('*/15 9-17 * * 1-5')` | Custom expression |

You can also use shortcuts: `@yearly`, `@monthly`, `@weekly`, `@daily`, `@hourly`.

### Admin panel

Navigate to **Utilities → Craft Schedule** to see all registered tasks and toggle them on or off. Tasks cannot be created, edited, or deleted from the UI — they are defined entirely in code.

### System crontab

Add this line to your server crontab to run tasks every minute:

```bash
* * * * * /path/to/php /path/to/project/craft craft-schedule/run 2>&1 >> /dev/null
```

## API

### `Schedule::command(string $command, array $params = []): PendingTask`

Register a console command to be scheduled.

- `$command` — The Craft console route (e.g. `'cache/flush-all'`)
- `$params` — Optional array of CLI arguments (e.g. `['--limit=50']`)

Returns a `PendingTask` instance with fluent frequency methods.

## Settings

Navigate to **Settings → Craft Schedule** to configure the PHP binary path and view the crontab setup instructions.

## Architecture

| Path | Description |
|------|-------------|
| `src/CraftSchedule.php` | Main plugin class |
| `src/Schedule.php` | Static facade for registering tasks |
| `src/PendingTask.php` | Fluent task builder with frequency methods |
| `src/services/ScheduleService.php` | DB persistence, cron evaluation, and command execution |
| `src/records/ScheduledTaskRecord.php` | ActiveRecord for scheduled tasks |
| `src/controllers/ScheduleController.php` | Web controller (list + toggle) |
| `src/console/controllers/RunController.php` | Console command for system crontab |
| `src/utilities/ScheduleUtility.php` | CP utility registration |
| `src/templates/_utilities/schedule.html` | Utility template with inline JS/CSS |
| `src/templates/settings.html` | Settings page template |

### Database table: `craftschedule_scheduled_tasks`

| Column | Type | Description |
|--------|------|-------------|
| `id` | PK | Unique identifier |
| `fingerprint` | string(64), unique | SHA-256 of command + params + schedule |
| `command` | string | Command route (e.g. `cache/flush-all`) |
| `params` | text | JSON-encoded CLI arguments |
| `schedule` | string | 5-field cron expression |
| `enabled` | boolean | Whether the task is active |
| `lastRunAt` | datetime | Last execution timestamp |
| `dateCreated` | datetime | Creation timestamp |
| `dateUpdated` | datetime | Last update timestamp |
| `uid` | uuid | Universal unique identifier |

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

[Proprietary — Craft License](LICENSE.md)
