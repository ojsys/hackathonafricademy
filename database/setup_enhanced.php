<?php
/**
 * HackathonAfrica LMS — Database Setup
 * Supports both SQLite (development) and MySQL (production / cPanel)
 * 
 * Run: php database/setup_enhanced.php
 * Or visit: /database/setup.php in browser
 */

echo "Starting HackathonAfrica LMS Database Setup...\n";

require_once __DIR__ . '/../config/database.php';

echo "Driver: " . DB_DRIVER . "\n\n";

$pdo = db();
$isMysql = (DB_DRIVER === 'mysql');

// ─── Helper: adapt SQL to driver ──────────────────────────
function sql(string $sqliteSQL): string {
    global $isMysql;
    if (!$isMysql) return $sqliteSQL;
    
    // AUTOINCREMENT → AUTO_INCREMENT
    $sql = str_replace('INTEGER PRIMARY KEY AUTOINCREMENT', 'INT AUTO_INCREMENT PRIMARY KEY', $sqliteSQL);
    // SQLite CHECK constraints → remove for MySQL 5.x compat
    $sql = preg_replace("/CHECK\s*\([^)]+\)/i", '', $sql);
    // INTEGER DEFAULT → INT DEFAULT
    $sql = preg_replace('/\bINTEGER\s+DEFAULT/i', 'INT DEFAULT', $sql);
    $sql = preg_replace('/\bINTEGER\s+NOT\s+NULL/i', 'INT NOT NULL', $sql);
    // DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    $sql = str_replace('DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', $sql);
    
    return $sql;
}

// ─── Drop existing tables ─────────────────────────────────
echo "Dropping existing tables...\n";

$dropTables = [
    'site_settings',
    'admin_notes',
    'candidate_reviews', 
    'code_submissions',
    'coding_exercises',
    'final_exam_attempts',
    'final_exam_questions',
    'final_exams',
    'quiz_attempts',
    'user_lesson_progress',
    'quiz_options',
    'quiz_questions',
    'quizzes',
    'lessons',
    'modules',
    'user_enrollments',
    'courses',
    'activity_log',
    'users'
];

if (!$isMysql) {
    $pdo->exec('PRAGMA foreign_keys = OFF');
}
if ($isMysql) {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
}

foreach ($dropTables as $table) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    } catch (PDOException $e) {
        // Table might not exist
    }
}

if ($isMysql) {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

echo "Creating tables...\n";

// ─── Users ────────────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role TEXT NOT NULL DEFAULT 'student' CHECK(role IN ('student','admin','superadmin')),
    is_active INTEGER NOT NULL DEFAULT 1,
    phone VARCHAR(20),
    city VARCHAR(100),
    country VARCHAR(100),
    education_level VARCHAR(100),
    years_experience VARCHAR(20),
    bio TEXT,
    github_url VARCHAR(255),
    linkedin_url VARCHAR(255),
    portfolio_url VARCHAR(255),
    how_heard VARCHAR(100),
    reset_token VARCHAR(64),
    reset_expires DATETIME,
    total_time_spent INTEGER DEFAULT 0,
    last_activity DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)"));
if ($isMysql) $pdo->exec("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'student'");

// ─── Courses ──────────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),
    status TEXT NOT NULL DEFAULT 'draft' CHECK(status IN ('draft','published')),
    order_index INTEGER NOT NULL DEFAULT 0,
    estimated_hours INTEGER DEFAULT 0,
    difficulty TEXT DEFAULT 'beginner' CHECK(difficulty IN ('beginner','intermediate','advanced')),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)"));
if ($isMysql) {
    $pdo->exec("ALTER TABLE courses MODIFY status VARCHAR(20) NOT NULL DEFAULT 'draft'");
    $pdo->exec("ALTER TABLE courses MODIFY difficulty VARCHAR(20) DEFAULT 'beginner'");
}

// ─── User enrollments ─────────────────────────────────────
$pdo->exec(sql("CREATE TABLE user_enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    time_spent INTEGER DEFAULT 0,
    UNIQUE(user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)"));

// ─── Modules ──────────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    video_url VARCHAR(500),
    order_index INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)"));

// ─── Lessons ──────────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE lessons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT,
    video_url VARCHAR(500),
    video_placeholder INTEGER DEFAULT 0,
    analogy TEXT,
    common_mistakes TEXT,
    key_takeaways TEXT,
    order_index INTEGER NOT NULL DEFAULT 0,
    estimated_minutes INTEGER DEFAULT 10,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
)"));

// ─── Coding Exercises ─────────────────────────────────────
$pdo->exec(sql("CREATE TABLE coding_exercises (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lesson_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    instructions TEXT NOT NULL,
    starter_code TEXT,
    solution_code TEXT,
    hints TEXT,
    exercise_type TEXT DEFAULT 'html' CHECK(exercise_type IN ('html','css','javascript','combined')),
    difficulty TEXT DEFAULT 'easy' CHECK(difficulty IN ('easy','medium','hard')),
    test_cases TEXT,
    expected_output TEXT,
    order_index INTEGER NOT NULL DEFAULT 0,
    points INTEGER DEFAULT 10,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
)"));
if ($isMysql) {
    $pdo->exec("ALTER TABLE coding_exercises MODIFY exercise_type VARCHAR(20) DEFAULT 'html'");
    $pdo->exec("ALTER TABLE coding_exercises MODIFY difficulty VARCHAR(20) DEFAULT 'easy'");
}

// ─── Code Submissions ─────────────────────────────────────
$pdo->exec(sql("CREATE TABLE code_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    submitted_code TEXT NOT NULL,
    is_correct INTEGER DEFAULT 0,
    score INTEGER DEFAULT 0,
    feedback TEXT,
    attempts INTEGER DEFAULT 1,
    time_spent INTEGER DEFAULT 0,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES coding_exercises(id) ON DELETE CASCADE
)"));

// ─── Quizzes ──────────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE quizzes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    pass_mark INTEGER NOT NULL DEFAULT 70,
    time_limit INTEGER,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
)"));

// ─── Quiz Questions ───────────────────────────────────────
$pdo->exec(sql("CREATE TABLE quiz_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quiz_id INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    explanation TEXT,
    order_index INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
)"));

// ─── Quiz Options ─────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE quiz_options (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    question_id INTEGER NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
)"));

// ─── User Lesson Progress ─────────────────────────────────
$pdo->exec(sql("CREATE TABLE user_lesson_progress (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    lesson_id INTEGER NOT NULL,
    time_spent INTEGER DEFAULT 0,
    completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
)"));

// ─── Quiz Attempts ────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE quiz_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    quiz_id INTEGER NOT NULL,
    score INTEGER NOT NULL DEFAULT 0,
    passed INTEGER NOT NULL DEFAULT 0,
    time_taken INTEGER,
    answers_json TEXT,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
)"));

// ─── Final Exams ──────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE final_exams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id INTEGER NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    pass_mark INTEGER NOT NULL DEFAULT 70,
    time_limit INTEGER DEFAULT 60,
    mcq_count INTEGER DEFAULT 10,
    coding_count INTEGER DEFAULT 2,
    is_active INTEGER DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
)"));

// ─── Final Exam Questions ─────────────────────────────────
$pdo->exec(sql("CREATE TABLE final_exam_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    exam_id INTEGER NOT NULL,
    question_type TEXT NOT NULL CHECK(question_type IN ('mcq','coding')),
    question_text TEXT NOT NULL,
    options_json TEXT,
    correct_answer TEXT,
    starter_code TEXT,
    solution_code TEXT,
    test_cases TEXT,
    points INTEGER DEFAULT 10,
    order_index INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (exam_id) REFERENCES final_exams(id) ON DELETE CASCADE
)"));
if ($isMysql) $pdo->exec("ALTER TABLE final_exam_questions MODIFY question_type VARCHAR(20) NOT NULL");

// ─── Final Exam Attempts ──────────────────────────────────
$pdo->exec(sql("CREATE TABLE final_exam_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    exam_id INTEGER NOT NULL,
    score INTEGER NOT NULL DEFAULT 0,
    mcq_score INTEGER DEFAULT 0,
    coding_score INTEGER DEFAULT 0,
    passed INTEGER NOT NULL DEFAULT 0,
    time_taken INTEGER,
    answers_json TEXT,
    code_submissions_json TEXT,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES final_exams(id) ON DELETE CASCADE
)"));

// ─── Candidate Reviews ────────────────────────────────────
$pdo->exec(sql("CREATE TABLE candidate_reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    reviewed_by INTEGER,
    eligibility_status TEXT DEFAULT 'pending' CHECK(eligibility_status IN ('pending','eligible','needs_review','rejected')),
    courses_completed INTEGER DEFAULT 0,
    total_score DECIMAL(5,2) DEFAULT 0,
    avg_quiz_score DECIMAL(5,2) DEFAULT 0,
    avg_exam_score DECIMAL(5,2) DEFAULT 0,
    coding_proficiency TEXT,
    strengths TEXT,
    areas_to_improve TEXT,
    admin_notes TEXT,
    interview_scheduled DATETIME,
    final_decision TEXT,
    reviewed_at DATETIME,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
)"));
if ($isMysql) $pdo->exec("ALTER TABLE candidate_reviews MODIFY eligibility_status VARCHAR(20) DEFAULT 'pending'");

// ─── Admin Notes ──────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE admin_notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    admin_id INTEGER NOT NULL,
    note TEXT NOT NULL,
    note_type TEXT DEFAULT 'general' CHECK(note_type IN ('general','interview','technical','recommendation')),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
)"));
if ($isMysql) $pdo->exec("ALTER TABLE admin_notes MODIFY note_type VARCHAR(20) DEFAULT 'general'");

// ─── Activity Log ─────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    activity_type TEXT NOT NULL,
    entity_type VARCHAR(50),
    entity_id INTEGER,
    details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)"));

// ─── Site Settings ────────────────────────────────────────
$pdo->exec(sql("CREATE TABLE site_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)"));

// ─── Indexes ──────────────────────────────────────────────
echo "Creating indexes...\n";

$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_modules_course ON modules(course_id)",
    "CREATE INDEX IF NOT EXISTS idx_lessons_module ON lessons(module_id)",
    "CREATE INDEX IF NOT EXISTS idx_exercises_lesson ON coding_exercises(lesson_id)",
    "CREATE INDEX IF NOT EXISTS idx_submissions_user ON code_submissions(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_submissions_exercise ON code_submissions(exercise_id)",
    "CREATE INDEX IF NOT EXISTS idx_progress_user ON user_lesson_progress(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_attempts_user ON quiz_attempts(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_enrollments_user ON user_enrollments(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_exam_attempts_user ON final_exam_attempts(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_reviews_status ON candidate_reviews(eligibility_status)",
    "CREATE INDEX IF NOT EXISTS idx_activity_user ON activity_log(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_activity_type ON activity_log(activity_type)",
];

foreach ($indexes as $idx) {
    try { $pdo->exec($idx); } catch (PDOException $e) { /* skip if exists */ }
}

echo "Tables created successfully!\n\n";

// ─── Seed Users ───────────────────────────────────────────
echo "Creating admin user...\n";
$adminPassword = password_hash('Admin@1234', PASSWORD_BCRYPT);
if ($isMysql) {
    $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@hackathon.africa', ?, 'admin')")->execute([$adminPassword]);
} else {
    $pdo->prepare("INSERT INTO users (id, name, email, password, role) VALUES (1, 'Admin', 'admin@hackathon.africa', ?, 'admin')")->execute([$adminPassword]);
}
echo "Admin user created: admin@hackathon.africa / Admin@1234\n\n";

echo "Creating demo student...\n";
$demoPassword = password_hash('Demo@1234', PASSWORD_BCRYPT);
if ($isMysql) {
    $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Demo Student', 'demo@hackathon.africa', ?, 'student')")->execute([$demoPassword]);
} else {
    $pdo->prepare("INSERT INTO users (id, name, email, password, role) VALUES (2, 'Demo Student', 'demo@hackathon.africa', ?, 'student')")->execute([$demoPassword]);
}
echo "Demo student created: demo@hackathon.africa / Demo@1234\n\n";

// ─── Re-enable FK (SQLite only) ───────────────────────────
if (!$isMysql) {
    $pdo->exec('PRAGMA foreign_keys = ON');
}

// ─── Seed Settings ────────────────────────────────────────
echo "Setting up default site settings...\n";
$pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES ('site_name', 'HackathonAfrica LMS')");
$pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES ('logo_path', '/public/img/logo.png')");
$pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES ('favicon_path', '/public/img/favicon.png')");

echo "Database schema setup complete!\n";
echo "Now loading comprehensive course content...\n\n";

// Include the full content seeder (3 courses x 4 modules x 5 lessons = 60 lessons)
include __DIR__ . '/seed_full_content.php';

echo "\n===================================\n";
echo "SETUP COMPLETE!\n";
echo "Admin login: admin@hackathon.africa / Admin@1234\n";
echo "Demo login:  demo@hackathon.africa / Demo@1234\n";
echo "===================================\n";
