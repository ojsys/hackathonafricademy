-- Migration: question bank support for qualifying exam
-- Stores the specific 50 questions selected for each attempt so that
-- (a) page refresh doesn't re-randomise, and
-- (b) grading only covers the questions the candidate actually saw.

ALTER TABLE qualifying_attempts ADD COLUMN question_ids_json TEXT;
