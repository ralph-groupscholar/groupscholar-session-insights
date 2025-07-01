<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/lib.php';

$config = gssi_load_config();
$pdo = gssi_connect($config);

$pdo->exec('CREATE SCHEMA IF NOT EXISTS gs_session_insights');
$pdo->exec('CREATE TABLE IF NOT EXISTS gs_session_insights.session_logs (
    id SERIAL PRIMARY KEY,
    scholar_name TEXT NOT NULL,
    coach_name TEXT NOT NULL,
    session_date DATE NOT NULL,
    summary TEXT NOT NULL,
    action_items TEXT NOT NULL,
    tags TEXT[] NOT NULL DEFAULT ARRAY[]::TEXT[],
    status TEXT NOT NULL DEFAULT \'open\',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
)');

$existing = $pdo->query('SELECT COUNT(*) AS count FROM gs_session_insights.session_logs')->fetch();
if ((int)$existing['count'] === 0) {
    $stmt = $pdo->prepare('INSERT INTO gs_session_insights.session_logs
        (scholar_name, coach_name, session_date, summary, action_items, tags, status)
        VALUES (:scholar, :coach, :session_date, :summary, :action_items, :tags, :status)');

    $seed = [
        [
            'scholar' => 'Ari Lewis',
            'coach' => 'Morgan Reid',
            'date' => '2026-02-05',
            'summary' => 'Reviewed FAFSA progress and clarified missing documentation.',
            'next' => 'Collect parent tax transcript and upload by Feb 12.',
            'tags' => '{financial_aid,documentation}',
            'status' => 'open',
        ],
        [
            'scholar' => 'Camila Ortiz',
            'coach' => 'Devin Park',
            'date' => '2026-02-03',
            'summary' => 'Discussed scholarship essay structure and theme alignment.',
            'next' => 'Draft new intro paragraph and send for review.',
            'tags' => '{essay,storytelling}',
            'status' => 'open',
        ],
        [
            'scholar' => 'Jae Kim',
            'coach' => 'Morgan Reid',
            'date' => '2026-02-01',
            'summary' => 'Shared internship outreach targets and networking plan.',
            'next' => 'Email three contacts from alumni list.',
            'tags' => '{career,networking}',
            'status' => 'open',
        ],
        [
            'scholar' => 'Noor Patel',
            'coach' => 'Avery Shaw',
            'date' => '2026-01-29',
            'summary' => 'Closed out spring goal review and documented progress.',
            'next' => 'Schedule February check-in.',
            'tags' => '{goals,planning}',
            'status' => 'closed',
        ],
        [
            'scholar' => 'Samir Khan',
            'coach' => 'Devin Park',
            'date' => '2026-01-27',
            'summary' => 'Identified support gaps and resource referrals.',
            'next' => 'Share tutoring resources and confirm signup.',
            'tags' => '{support,resources}',
            'status' => 'open',
        ],
    ];

    foreach ($seed as $row) {
        $stmt->execute([
            ':scholar' => $row['scholar'],
            ':coach' => $row['coach'],
            ':session_date' => $row['date'],
            ':summary' => $row['summary'],
            ':action_items' => $row['next'],
            ':tags' => $row['tags'],
            ':status' => $row['status'],
        ]);
    }

    echo "Seeded gs_session_insights.session_logs with sample data.\n";
} else {
    echo "Seed data already present; skipping.\n";
}
