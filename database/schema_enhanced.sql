-- HackathonAfrica LMS Enhanced Database Schema
-- SQLite version with code exercises, final exams, analytics

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS admin_notes;
DROP TABLE IF EXISTS candidate_reviews;
DROP TABLE IF EXISTS code_submissions;
DROP TABLE IF EXISTS coding_exercises;
DROP TABLE IF EXISTS final_exam_attempts;
DROP TABLE IF EXISTS final_exam_questions;
DROP TABLE IF EXISTS final_exams;
DROP TABLE IF EXISTS quiz_attempts;
DROP TABLE IF EXISTS user_lesson_progress;
DROP TABLE IF EXISTS quiz_options;
DROP TABLE IF EXISTS quiz_questions;
DROP TABLE IF EXISTS quizzes;
DROP TABLE IF EXISTS lessons;
DROP TABLE IF EXISTS modules;
DROP TABLE IF EXISTS user_enrollments;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;

-- Users with enhanced profile fields
CREATE TABLE users (
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
);

-- Courses
CREATE TABLE courses (
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
);

-- User course enrollments
CREATE TABLE user_enrollments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    time_spent INTEGER DEFAULT 0,
    UNIQUE(user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Modules (chapters within a course)
CREATE TABLE modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    video_url VARCHAR(500),
    order_index INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Lessons with enhanced content structure
CREATE TABLE lessons (
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
);

-- Coding exercises linked to lessons
CREATE TABLE coding_exercises (
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
);

-- Code submissions from users
CREATE TABLE code_submissions (
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
);

-- Module quizzes (MCQ only)
CREATE TABLE quizzes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    pass_mark INTEGER NOT NULL DEFAULT 70,
    time_limit INTEGER,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

-- Quiz questions
CREATE TABLE quiz_questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    quiz_id INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    explanation TEXT,
    order_index INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Answer options per question
CREATE TABLE quiz_options (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    question_id INTEGER NOT NULL,
    option_text VARCHAR(500) NOT NULL,
    is_correct INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- User lesson completion tracking
CREATE TABLE user_lesson_progress (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    lesson_id INTEGER NOT NULL,
    time_spent INTEGER DEFAULT 0,
    completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

-- Quiz attempt records
CREATE TABLE quiz_attempts (
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
);

-- Final exams (per course, combines MCQ + Coding)
CREATE TABLE final_exams (
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
);

-- Final exam questions (can be MCQ or coding)
CREATE TABLE final_exam_questions (
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
);

-- Final exam attempts
CREATE TABLE final_exam_attempts (
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
);

-- Candidate reviews for admin evaluation
CREATE TABLE candidate_reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    reviewed_by INTEGER,
    eligibility_status TEXT DEFAULT 'pending' CHECK(eligibility_status IN ('pending','eligible','needs_review','rejected','to_be_decided')),
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
);

-- Admin notes on candidates
CREATE TABLE admin_notes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    admin_id INTEGER NOT NULL,
    note TEXT NOT NULL,
    note_type TEXT DEFAULT 'general' CHECK(note_type IN ('general','interview','technical','recommendation')),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Activity log for analytics
CREATE TABLE activity_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    activity_type TEXT NOT NULL,
    entity_type VARCHAR(50),
    entity_id INTEGER,
    details TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Site Settings (admin-configurable logo, favicon, etc.)
CREATE TABLE site_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- Indexes for performance
CREATE INDEX idx_modules_course ON modules(course_id);
CREATE INDEX idx_lessons_module ON lessons(module_id);
CREATE INDEX idx_exercises_lesson ON coding_exercises(lesson_id);
CREATE INDEX idx_submissions_user ON code_submissions(user_id);
CREATE INDEX idx_submissions_exercise ON code_submissions(exercise_id);
CREATE INDEX idx_progress_user ON user_lesson_progress(user_id);
CREATE INDEX idx_attempts_user ON quiz_attempts(user_id);
CREATE INDEX idx_enrollments_user ON user_enrollments(user_id);
CREATE INDEX idx_exam_attempts_user ON final_exam_attempts(user_id);
CREATE INDEX idx_reviews_status ON candidate_reviews(eligibility_status);
CREATE INDEX idx_activity_user ON activity_log(user_id);
CREATE INDEX idx_activity_type ON activity_log(activity_type);
