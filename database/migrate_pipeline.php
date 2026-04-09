<?php
/**
 * Migration: HackathonAfrica 3.0 pipeline fields
 * Run once: php database/migrate_pipeline.php
 */
$pdo = new PDO('sqlite:' . __DIR__ . '/lms.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$columns = fn($table) => array_column(
    $pdo->query("PRAGMA table_info($table)")->fetchAll(PDO::FETCH_ASSOC), 'name'
);

// ── users: add skill_level ────────────────────────────────────
if (!in_array('skill_level', $columns('users'))) {
    $pdo->exec("ALTER TABLE users ADD COLUMN skill_level VARCHAR(20) DEFAULT 'beginner'");
    echo "Added: users.skill_level\n";
}

// ── candidate_reviews: pipeline tracking fields ───────────────
$cr = $columns('candidate_reviews');
$add = [
    'composite_score'  => 'DECIMAL(5,2) DEFAULT 0',
    'speed_bonus'      => 'DECIMAL(4,2) DEFAULT 0',
    'attempt_penalty'  => 'DECIMAL(4,2) DEFAULT 0',
    'shortlisted'      => 'INTEGER DEFAULT 0',
    'shortlisted_at'   => 'DATETIME',
    'bootcamp_status'  => "TEXT DEFAULT 'not_invited'",
    'invited_at'       => 'DATETIME',
    'final_decision'   => "TEXT DEFAULT 'pending'",
];
foreach ($add as $col => $def) {
    if (!in_array($col, $cr)) {
        $pdo->exec("ALTER TABLE candidate_reviews ADD COLUMN $col $def");
        echo "Added: candidate_reviews.$col\n";
    }
}

// ── site_settings: email + deadline config ────────────────────
$settings = [
    'smtp_host'         => 'smtp.hostinger.com',
    'smtp_port'         => '465',
    'smtp_user'         => '',
    'smtp_pass'         => '',
    'smtp_from_email'   => '',
    'smtp_from_name'    => 'HackathonAfrica',
    'smtp_encryption'   => 'ssl',
    'completion_deadline' => '',
    'shortlist_limit'   => '100',
    'primary_color'     => '#F8B526',
];
$check = $pdo->prepare("SELECT id FROM site_settings WHERE setting_key = ?");
$insert = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($settings as $key => $val) {
    $check->execute([$key]);
    if (!$check->fetch()) {
        $insert->execute([$key, $val]);
        echo "Added setting: $key\n";
    }
}

echo "\nMigration complete.\n";
