UPDATE final_exams SET pass_mark = 80 WHERE pass_mark < 80;

-- Migration: raise final exam pass mark from 70 to 80 percent.
-- Works on both MySQL (production) and SQLite (local) as a plain UPDATE.
-- Idempotent: the WHERE guard means re-running changes nothing.
-- NOTE: the statement is placed FIRST on purpose. The migration runner splits
-- on the semicolon character and discards any chunk beginning with a comment
-- marker, so a leading comment block would otherwise swallow the statement.
