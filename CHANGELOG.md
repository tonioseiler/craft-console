# Changelog

## 1.0.0 - 2026-06-10

### Added
- Complete rewrite as **Craft Schedule**
- Fluent scheduling API: `Schedule::command(...)->daily()`
- Auto-register tasks in the database from code
- Admin utility with read-only task list and enable/disable toggle
- Console command `craft-schedule/run` for system crontab
- Full cron expression support with shortcuts (`@daily`, `@hourly`, etc.)
- Settings page with PHP binary path configuration

### Removed
- Command execution from the admin panel (security)
- Cronjob CRUD (create/edit/delete) — tasks are now defined in code
