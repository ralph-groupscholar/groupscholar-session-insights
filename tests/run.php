<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/lib.php';

function run_test(string $name, callable $fn): void
{
    try {
        $fn();
        echo "[PASS] {$name}\n";
    } catch (Throwable $e) {
        echo "[FAIL] {$name}: {$e->getMessage()}\n";
        exit(1);
    }
}

function expect(bool $condition, string $message = 'Assertion failed'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

run_test('parse tags trims and lowercases', function (): void {
    $tags = gssi_parse_tags(' FAFSA , Essay , essay ');
    expect($tags === ['fafsa', 'essay'], 'Tags were not normalized');
});

run_test('format tags handles empty', function (): void {
    expect(gssi_format_tags([]) === 'none', 'Empty tags should render as none');
});

run_test('parse args defaults date and status', function (): void {
    [$cmd, $opts] = gssi_parse_args(['gs', 'add', '--scholar', 'A', '--coach', 'B', '--summary', 'C', '--next', 'D']);
    expect($cmd === 'add', 'Command should be add');
    expect($opts['status'] === 'open', 'Default status should be open');
    expect($opts['date'] !== null, 'Date should be set');
});

run_test('normalize status handles all', function (): void {
    expect(gssi_normalize_status('all') === null, 'Status all should normalize to null');
    expect(gssi_normalize_status('OPEN') === 'open', 'Status should be lowercased');
});

run_test('render summary formats output', function (): void {
    $summary = gssi_render_summary([
        ['status' => 'open', 'count' => 2],
        ['status' => 'closed', 'count' => 1],
    ], [
        ['tag' => 'fafsa', 'count' => 2],
    ]);

    expect(str_contains($summary, 'Session Insight Summary'), 'Missing header');
    expect(str_contains($summary, 'open: 2'), 'Missing open count');
    expect(str_contains($summary, 'fafsa: 2'), 'Missing tag count');
});

run_test('render coach summary formats output', function (): void {
    $summary = gssi_render_coach_summary([
        ['coach_name' => 'Morgan Reid', 'total' => 3, 'open_count' => 2, 'last_session' => '2026-02-05'],
    ]);

    expect(str_contains($summary, 'Coach Summary'), 'Missing coach summary header');
    expect(str_contains($summary, 'Morgan Reid | total 3 | open 2 | last 2026-02-05'), 'Missing coach row');
});

run_test('render scholar summary formats output', function (): void {
    $summary = gssi_render_scholar_summary([
        [
            'scholar_name' => 'Ari Lewis',
            'total' => 2,
            'open_count' => 1,
            'last_session' => '2026-02-05',
            'action_items' => 'Send transcript.',
        ],
    ]);

    expect(str_contains($summary, 'Scholar Summary'), 'Missing scholar summary header');
    expect(str_contains($summary, 'Ari Lewis | total 2 | open 1 | last 2026-02-05'), 'Missing scholar row');
    expect(str_contains($summary, 'Next: Send transcript.'), 'Missing next action line');
});

echo "All tests passed.\n";
