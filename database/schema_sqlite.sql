-- HackathonAfrica LMS — SQLite Schema (development)

PRAGMA foreign_keys = ON;

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

CREATE TABLE users (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    name             TEXT NOT NULL,
    email            TEXT NOT NULL UNIQUE,
    password         TEXT NOT NULL,
    role             TEXT NOT NULL DEFAULT 'student' CHECK (role IN ('student','admin','superadmin')),
    is_active        INTEGER NOT NULL DEFAULT 1,
    reset_token      TEXT DEFAULT NULL,
    reset_expires    TEXT DEFAULT NULL,
    -- Extended profile fields
    phone            TEXT DEFAULT NULL,
    country          TEXT DEFAULT NULL,
    city             TEXT DEFAULT NULL,
    education_level  TEXT DEFAULT NULL,
    years_experience TEXT DEFAULT NULL,
    github_url       TEXT DEFAULT NULL,
    linkedin_url     TEXT DEFAULT NULL,
    bio              TEXT DEFAULT NULL,
    how_heard        TEXT DEFAULT NULL,
    created_at       TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE courses (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    title       TEXT NOT NULL,
    description TEXT,
    thumbnail   TEXT DEFAULT NULL,
    status      TEXT NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','published')),
    order_index INTEGER NOT NULL DEFAULT 0,
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE user_enrollments (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL,
    course_id   INTEGER NOT NULL,
    enrolled_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (user_id, course_id),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE modules (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    course_id   INTEGER NOT NULL,
    title       TEXT NOT NULL,
    description TEXT,
    video_url   TEXT DEFAULT NULL,
    order_index INTEGER NOT NULL DEFAULT 0,
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE lessons (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id   INTEGER NOT NULL,
    title       TEXT NOT NULL,
    content     TEXT,
    video_url   TEXT DEFAULT NULL,
    order_index INTEGER NOT NULL DEFAULT 0,
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

CREATE TABLE quizzes (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id   INTEGER NOT NULL UNIQUE,
    title       TEXT NOT NULL,
    pass_mark   INTEGER NOT NULL DEFAULT 70,
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
);

CREATE TABLE quiz_questions (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    quiz_id       INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    order_index   INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE quiz_options (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    question_id INTEGER NOT NULL,
    option_text TEXT NOT NULL,
    is_correct  INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

CREATE TABLE user_lesson_progress (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL,
    lesson_id   INTEGER NOT NULL,
    completed_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE (user_id, lesson_id),
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

CREATE TABLE quiz_attempts (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id      INTEGER NOT NULL,
    quiz_id      INTEGER NOT NULL,
    score        INTEGER NOT NULL DEFAULT 0,
    passed       INTEGER NOT NULL DEFAULT 0,
    attempted_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE INDEX idx_modules_course  ON modules(course_id);
CREATE INDEX idx_lessons_module  ON lessons(module_id);
CREATE INDEX idx_progress_user   ON user_lesson_progress(user_id);
CREATE INDEX idx_attempts_user   ON quiz_attempts(user_id);
CREATE INDEX idx_enrollments_user ON user_enrollments(user_id);
