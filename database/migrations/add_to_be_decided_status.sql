-- Migration: add 'to_be_decided' to eligibility_status CHECK constraint
-- DRIVER: sqlite
-- SQLite requires recreating the table to modify a CHECK constraint.

PRAGMA foreign_keys=OFF;

CREATE TABLE candidate_reviews_new (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL UNIQUE,
    reviewed_by INTEGER,
    eligibility_status TEXT DEFAULT 'pending' CHECK(eligibility_status IN ('pending','eligible','needs_review','rejected','to_be_decided')),
    courses_completed INTEGER DEFAULT 0,
    total_score DECIMAL(5,2) DEFAULT 0,
    avg_quiz_score DECIMAL(5,2) DEFAULT 0,
    avg_exam_score DECIMAL(5,2) DEFAULT 0,
    composite_score DECIMAL(5,2) DEFAULT 0,
    speed_bonus DECIMAL(5,2) DEFAULT 0,
    attempt_penalty DECIMAL(5,2) DEFAULT 0,
    qualifying_score INTEGER DEFAULT 0,
    qualifying_passed INTEGER DEFAULT 0,
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

INSERT INTO candidate_reviews_new SELECT * FROM candidate_reviews;
DROP TABLE candidate_reviews;
ALTER TABLE candidate_reviews_new RENAME TO candidate_reviews;

PRAGMA foreign_keys=ON;
