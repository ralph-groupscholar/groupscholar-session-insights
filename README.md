# Group Scholar Session Insights

A lightweight PHP CLI that logs mentoring session insights, action items, and tags so Group Scholar staff can see what follow-ups are pending and what themes are trending.

## Features
- Add new session insights with scholar, coach, summary, action items, status, and tags.
- List recent sessions with consistent formatting for quick review.
- Generate a summary view with status counts and top tags.
- PostgreSQL-backed storage with seeded sample data for immediate usage.

## Getting Started

### Requirements
- PHP 8.2+ with `pdo_pgsql`
- Access to the Group Scholar production PostgreSQL database

### Environment Variables
Set these before running any commands:

```
export GS_DB_HOST=...
export GS_DB_PORT=...
export GS_DB_NAME=...
export GS_DB_USER=...
export GS_DB_PASSWORD=...
```

### Initialize Database

```
php scripts/setup_db.php
```

### Usage

```
./bin/gs-session-insights add --scholar "Ari Lewis" --coach "Morgan Reid" --summary "Reviewed FAFSA progress" --next "Collect missing transcript" --tags "financial_aid,documentation" --status open --date 2026-02-05

./bin/gs-session-insights list --status open --limit 10

./bin/gs-session-insights summary

./bin/gs-session-insights coach-summary --status all --limit 10
```

### Tests

```
php tests/run.php
```

## Technology
- PHP 8.5 (CLI)
- PostgreSQL (via PDO)

## Project Structure
- `bin/gs-session-insights`: CLI entrypoint
- `src/lib.php`: core functions
- `scripts/setup_db.php`: schema + seed data
- `tests/run.php`: lightweight tests
