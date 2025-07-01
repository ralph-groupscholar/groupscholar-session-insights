<?php

declare(strict_types=1);

function gssi_load_config(): array
{
    $required = [
        'GS_DB_HOST',
        'GS_DB_PORT',
        'GS_DB_NAME',
        'GS_DB_USER',
        'GS_DB_PASSWORD',
    ];

    $config = [];
    foreach ($required as $key) {
        $value = getenv($key);
        if ($value === false || $value === '') {
            throw new RuntimeException("Missing required environment variable: {$key}");
        }
        $config[$key] = $value;
    }

    return $config;
}

function gssi_connect(array $config): PDO
{
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $config['GS_DB_HOST'],
        $config['GS_DB_PORT'],
        $config['GS_DB_NAME']
    );

    $pdo = new PDO($dsn, $config['GS_DB_USER'], $config['GS_DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function gssi_parse_args(array $argv): array
{
    $command = $argv[1] ?? 'help';
    $options = [
        'scholar' => null,
        'coach' => null,
        'summary' => null,
        'next' => null,
        'tags' => [],
        'date' => null,
        'status' => 'open',
        'limit' => 20,
    ];

    $flags = array_slice($argv, 2);
    for ($i = 0; $i < count($flags); $i++) {
        $flag = $flags[$i];
        if ($flag === '--tags') {
            $value = $flags[$i + 1] ?? '';
            $options['tags'] = gssi_parse_tags($value);
            $i++;
            continue;
        }
        if ($flag === '--limit') {
            $value = $flags[$i + 1] ?? '';
            $options['limit'] = max(1, (int)$value);
            $i++;
            continue;
        }
        if (str_starts_with($flag, '--')) {
            $key = substr($flag, 2);
            $value = $flags[$i + 1] ?? null;
            if ($value === null || str_starts_with($value, '--')) {
                continue;
            }
            if (array_key_exists($key, $options)) {
                $options[$key] = $value;
                $i++;
            }
        }
    }

    if ($options['date'] === null) {
        $options['date'] = (new DateTimeImmutable('now'))->format('Y-m-d');
    }

    return [$command, $options];
}

function gssi_parse_tags(string $raw): array
{
    $parts = array_map('trim', explode(',', $raw));
    $tags = [];
    foreach ($parts as $tag) {
        if ($tag !== '') {
            $tags[] = strtolower($tag);
        }
    }
    return array_values(array_unique($tags));
}

function gssi_format_tags(array $tags): string
{
    if (count($tags) === 0) {
        return 'none';
    }
    return implode(', ', $tags);
}

function gssi_render_list(array $rows): string
{
    if (count($rows) === 0) {
        return "No session insights found.\n";
    }

    $lines = [];
    foreach ($rows as $row) {
        $lines[] = sprintf(
            "#%d | %s | %s | %s | %s",
            $row['id'],
            $row['session_date'],
            $row['scholar_name'],
            $row['coach_name'],
            $row['status']
        );
        $lines[] = "  Summary: {$row['summary']}";
        $lines[] = "  Next: {$row['action_items']}";
        $lines[] = "  Tags: " . gssi_format_tags($row['tags'] ?? []);
        $lines[] = '';
    }

    return implode("\n", $lines);
}

function gssi_render_summary(array $statusCounts, array $tagCounts): string
{
    $lines = ["Session Insight Summary"]; 
    $lines[] = str_repeat('-', 24);
    $lines[] = 'Status counts:';
    foreach ($statusCounts as $row) {
        $lines[] = sprintf("  %s: %d", $row['status'], $row['count']);
    }

    $lines[] = '';
    $lines[] = 'Top tags:';
    if (count($tagCounts) === 0) {
        $lines[] = '  none';
    } else {
        foreach ($tagCounts as $row) {
            $lines[] = sprintf("  %s: %d", $row['tag'], $row['count']);
        }
    }

    return implode("\n", $lines) . "\n";
}

function gssi_help(): string
{
    return <<<TEXT
Group Scholar Session Insights CLI

Usage:
  gs-session-insights add --scholar "Name" --coach "Name" --summary "Notes" --next "Action" --tags "tag,tag" [--date YYYY-MM-DD] [--status open|closed]
  gs-session-insights list [--status open|closed] [--limit N]
  gs-session-insights summary

Environment variables:
  GS_DB_HOST, GS_DB_PORT, GS_DB_NAME, GS_DB_USER, GS_DB_PASSWORD
TEXT;
}
