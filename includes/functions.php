<?php
require_once __DIR__ . '/../config/database.php';

// ─── Error Logging ────────────────────────────────────────
function log_error(Throwable $e): void {
    $timestamp = date('Y-m-d H:i:s');
    $log_file = __DIR__ . '/../logs/error.log';
    $message = sprintf("[%s] Uncaught exception: %s in %s on line %d\nStack trace:\n%s\n\n",
        $timestamp, $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    file_put_contents($log_file, $message, FILE_APPEND);

    // If headers haven't been sent, redirect to a generic 500 error page
    if (!headers_sent()) {
        http_response_code(500);
        include __DIR__ . '/../error_pages/500.php';
        exit;
    }
}

// Global exception handler
set_exception_handler('log_error');

// Global error handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    // Only handle errors that are actual problems, not notices or warnings
    if ($severity === E_RECOVERABLE_ERROR || $severity === E_USER_ERROR || $severity === E_ERROR) {
        log_error(new ErrorException($message, 0, $severity, $file, $line));
    }
    return false; // Let PHP's default error handler continue for non-critical errors
});

// ─── Session ──────────────────────────────────────────────
function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// ─── CSRF ─────────────────────────────────────────────────
function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void {
    start_session();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        include __DIR__ . '/../error_pages/403.php'; // Include custom 403 page
        exit; // Terminate script execution
    }
}

// ─── Output escaping ──────────────────────────────────────
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ─── Auth helpers ─────────────────────────────────────────
function is_logged_in(): bool {
    start_session();
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin(): void {
    require_login();
    $user = current_user();
    if (!in_array($user['role'], ['admin', 'superadmin'])) {
        header('Location: /pages/dashboard.php');
        exit;
    }
}

// ─── Flash messages ───────────────────────────────────────
function set_flash(string $type, string $message): void {
    start_session();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    start_session();
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function render_flash(): void {
    $flash = get_flash();
    if (!$flash) return;
    $type = $flash['type'] === 'error' ? 'danger' : h($flash['type']);
    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
    echo h($flash['message']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}

// ─── Course / Progress helpers ────────────────────────────
function get_all_published_courses(): array {
    $stmt = db()->query('SELECT * FROM courses WHERE status = "published" ORDER BY order_index');
    return $stmt->fetchAll();
}

function get_course(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_modules_for_course(int $courseId): array {
    $stmt = db()->prepare('SELECT * FROM modules WHERE course_id = ? ORDER BY order_index');
    $stmt->execute([$courseId]);
    return $stmt->fetchAll();
}

function get_lessons_for_module(int $moduleId): array {
    $stmt = db()->prepare('SELECT * FROM lessons WHERE module_id = ? ORDER BY order_index');
    $stmt->execute([$moduleId]);
    return $stmt->fetchAll();
}

function get_lesson(int $id): ?array {
    $stmt = db()->prepare('SELECT l.*, m.course_id, m.title AS module_title FROM lessons l JOIN modules m ON l.module_id = m.id WHERE l.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_quiz_for_module(int $moduleId): ?array {
    $stmt = db()->prepare('SELECT * FROM quizzes WHERE module_id = ?');
    $stmt->execute([$moduleId]);
    return $stmt->fetch() ?: null;
}

function get_quiz(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM quizzes WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_questions_for_quiz(int $quizId): array {
    $stmt = db()->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY order_index');
    $stmt->execute([$quizId]);
    $questions = $stmt->fetchAll();

    foreach ($questions as &$q) {
        $optStmt = db()->prepare('SELECT * FROM quiz_options WHERE question_id = ? ORDER BY id');
        $optStmt->execute([$q['id']]);
        $q['options'] = array_map(function ($opt) {
            return [
                'id'         => $opt['id'],
                'text'       => $opt['option_text'],
                'is_correct' => (bool) $opt['is_correct'],
            ];
        }, $optStmt->fetchAll());
    }

    return $questions;
}

function is_module_accessible(int $userId, int $moduleId): bool {
    // Get this module's course and order
    $stmt = db()->prepare('SELECT course_id, order_index FROM modules WHERE id = ?');
    $stmt->execute([$moduleId]);
    $mod = $stmt->fetch();
    if (!$mod) return false;

    // Get all previous modules in the same course (lower order_index)
    $stmt = db()->prepare('SELECT id FROM modules WHERE course_id = ? AND order_index < ? ORDER BY order_index');
    $stmt->execute([$mod['course_id'], $mod['order_index']]);
    $prevModules = $stmt->fetchAll();

    // Every previous module that has a quiz must have it passed
    foreach ($prevModules as $prev) {
        $quiz = get_quiz_for_module($prev['id']);
        if ($quiz) {
            $attempt = get_best_quiz_attempt($userId, $quiz['id']);
            if (!$attempt || !$attempt['passed']) return false;
        }
    }
    return true;
}

function is_lesson_completed(int $userId, int $lessonId): bool {
    $stmt = db()->prepare('SELECT 1 FROM user_lesson_progress WHERE user_id = ? AND lesson_id = ?');
    $stmt->execute([$userId, $lessonId]);
    return (bool)$stmt->fetch();
}

function get_best_quiz_attempt(int $userId, int $quizId): ?array {
    $stmt = db()->prepare('SELECT * FROM quiz_attempts WHERE user_id = ? AND quiz_id = ? ORDER BY score DESC LIMIT 1');
    $stmt->execute([$userId, $quizId]);
    return $stmt->fetch() ?: null;
}

function is_enrolled(int $userId, int $courseId): bool {
    $stmt = db()->prepare('SELECT 1 FROM user_enrollments WHERE user_id = ? AND course_id = ?');
    $stmt->execute([$userId, $courseId]);
    return (bool)$stmt->fetch();
}

function enroll_user(int $userId, int $courseId): void {
    $ignore = DB_DRIVER === 'sqlite' ? 'OR IGNORE' : 'IGNORE';
    $stmt = db()->prepare("INSERT $ignore INTO user_enrollments (user_id, course_id) VALUES (?, ?)");
    $stmt->execute([$userId, $courseId]);
}

function get_course_progress(int $userId, int $courseId): array {
    // Total lessons in course
    $stmt = db()->prepare('SELECT COUNT(l.id) AS total FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = ?');
    $stmt->execute([$courseId]);
    $total = (int)$stmt->fetchColumn();

    // Completed lessons
    $stmt = db()->prepare('SELECT COUNT(ulp.id) AS done FROM user_lesson_progress ulp JOIN lessons l ON ulp.lesson_id = l.id JOIN modules m ON l.module_id = m.id WHERE m.course_id = ? AND ulp.user_id = ?');
    $stmt->execute([$courseId, $userId]);
    $done = (int)$stmt->fetchColumn();

    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
    return ['total' => $total, 'done' => $done, 'percent' => $pct];
}

function get_module_completion(int $userId, int $moduleId): array {
    $lessons = get_lessons_for_module($moduleId);
    $total = count($lessons);
    $done = 0;
    foreach ($lessons as $l) {
        if (is_lesson_completed($userId, $l['id'])) $done++;
    }
    $quiz = get_quiz_for_module($moduleId);
    $quizPassed = false;
    if ($quiz) {
        $attempt = get_best_quiz_attempt($userId, $quiz['id']);
        $quizPassed = $attempt && $attempt['passed'];
    }
    $complete = ($total > 0 && $done === $total) && (!$quiz || $quizPassed);
    return ['lessons_total' => $total, 'lessons_done' => $done, 'quiz_passed' => $quizPassed, 'complete' => $complete];
}

function is_course_complete(int $userId, int $courseId): bool {
    $modules = get_modules_for_course($courseId);
    if (empty($modules)) return false;
    foreach ($modules as $m) {
        $mc = get_module_completion($userId, $m['id']);
        if (!$mc['complete']) return false;
    }
    return true;
}

function is_eligible(int $userId): bool {
    $courses = get_all_published_courses();
    if (empty($courses)) return false;
    foreach ($courses as $c) {
        if (!is_course_complete($userId, $c['id'])) return false;
    }
    return true;
}

/**
 * Returns true if the course is locked because a prerequisite course
 * (lower order_index) has not yet been completed by this user.
 */
function is_course_locked(int $userId, int $courseId): bool {
    $stmt = db()->prepare('SELECT order_index FROM courses WHERE id = ?');
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();
    if (!$course || $course['order_index'] <= 1) return false; // first course never locked

    // Get all courses that come before this one
    $prev = db()->prepare('SELECT id FROM courses WHERE order_index < ? AND status = "published" ORDER BY order_index');
    $prev->execute([$course['order_index']]);
    foreach ($prev->fetchAll() as $p) {
        if (!is_course_complete($userId, (int)$p['id'])) return true;
    }
    return false;
}

function get_next_lesson(int $userId, int $courseId): ?array {
    $modules = get_modules_for_course($courseId);
    foreach ($modules as $m) {
        $lessons = get_lessons_for_module($m['id']);
        foreach ($lessons as $l) {
            if (!is_lesson_completed($userId, $l['id'])) {
                return $l;
            }
        }
    }
    return null;
}

// ─── Code Exercise helpers ────────────────────────────────
function get_exercises_for_lesson(int $lessonId): array {
    $stmt = db()->prepare('SELECT * FROM coding_exercises WHERE lesson_id = ? ORDER BY order_index');
    $stmt->execute([$lessonId]);
    return $stmt->fetchAll();
}

function get_exercise(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM coding_exercises WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function get_user_exercise_submission(int $userId, int $exerciseId): ?array {
    $stmt = db()->prepare('SELECT * FROM code_submissions WHERE user_id = ? AND exercise_id = ? ORDER BY submitted_at DESC LIMIT 1');
    $stmt->execute([$userId, $exerciseId]);
    return $stmt->fetch() ?: null;
}

function has_completed_exercise(int $userId, int $exerciseId): bool {
    $stmt = db()->prepare('SELECT 1 FROM code_submissions WHERE user_id = ? AND exercise_id = ? AND is_correct = 1');
    $stmt->execute([$userId, $exerciseId]);
    return (bool)$stmt->fetch();
}

// ─── Qualifying Exam helpers ──────────────────────────────

function get_qualifying_exam(): ?array {
    return db()->query('SELECT * FROM qualifying_exam WHERE is_active = 1 ORDER BY id DESC LIMIT 1')->fetch() ?: null;
}

function get_qualifying_questions(int $examId): array {
    $stmt = db()->prepare('SELECT * FROM qualifying_questions WHERE exam_id = ? ORDER BY order_index');
    $stmt->execute([$examId]);
    return $stmt->fetchAll();
}

function get_active_qualifying_attempt(int $userId): ?array {
    $stmt = db()->prepare('SELECT * FROM qualifying_attempts WHERE user_id = ? AND completed_at IS NULL ORDER BY started_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function get_best_qualifying_attempt(int $userId): ?array {
    $stmt = db()->prepare('SELECT * FROM qualifying_attempts WHERE user_id = ? AND completed_at IS NOT NULL ORDER BY percentage DESC, completed_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function get_qualifying_attempt(int $attemptId): ?array {
    $stmt = db()->prepare('SELECT * FROM qualifying_attempts WHERE id = ?');
    $stmt->execute([$attemptId]);
    return $stmt->fetch() ?: null;
}

function get_proctor_session(int $attemptId): ?array {
    $stmt = db()->prepare('SELECT * FROM proctor_sessions WHERE attempt_id = ? LIMIT 1');
    $stmt->execute([$attemptId]);
    return $stmt->fetch() ?: null;
}

function get_proctor_images_for_attempt(int $attemptId): array {
    $stmt = db()->prepare('SELECT * FROM proctor_images WHERE attempt_id = ? ORDER BY captured_at');
    $stmt->execute([$attemptId]);
    return $stmt->fetchAll();
}

function get_all_proctor_images_for_user(int $userId): array {
    $stmt = db()->prepare('SELECT pi.*, qa.percentage, qa.passed, qa.completed_at FROM proctor_images pi JOIN qualifying_attempts qa ON qa.id = pi.attempt_id WHERE pi.user_id = ? ORDER BY pi.captured_at');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function has_passed_qualifying_exam(int $userId): bool {
    $stmt = db()->prepare('SELECT 1 FROM qualifying_attempts WHERE user_id = ? AND passed = 1 AND completed_at IS NOT NULL');
    $stmt->execute([$userId]);
    return (bool)$stmt->fetch();
}

// ─── Final Exam helpers ───────────────────────────────────
function get_final_exam_for_course(int $courseId): ?array {
    $stmt = db()->prepare('SELECT * FROM final_exams WHERE course_id = ? AND is_active = 1');
    $stmt->execute([$courseId]);
    return $stmt->fetch() ?: null;
}

function get_final_exam_questions(int $examId): array {
    $stmt = db()->prepare('SELECT * FROM final_exam_questions WHERE exam_id = ? ORDER BY order_index');
    $stmt->execute([$examId]);
    return $stmt->fetchAll();
}

function get_best_exam_attempt(int $userId, int $examId): ?array {
    $stmt = db()->prepare('SELECT * FROM final_exam_attempts WHERE user_id = ? AND exam_id = ? AND completed_at IS NOT NULL ORDER BY score DESC LIMIT 1');
    $stmt->execute([$userId, $examId]);
    return $stmt->fetch() ?: null;
}

function has_passed_final_exam(int $userId, int $courseId): bool {
    $exam = get_final_exam_for_course($courseId);
    if (!$exam) return true; // No exam required
    $attempt = get_best_exam_attempt($userId, $exam['id']);
    return $attempt && $attempt['passed'];
}

// ─── Candidate Review helpers ─────────────────────────────
function get_candidate_review(int $userId): ?array {
    $stmt = db()->prepare('SELECT * FROM candidate_reviews WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function create_or_update_candidate_review(int $userId): void {
    $review  = get_candidate_review($userId);
    $courses = get_all_published_courses();

    $coursesCompleted = 0;
    $totalQuizScore   = 0;
    $quizCount        = 0;
    $totalExamScore   = 0;
    $examCount        = 0;
    $totalAttempts    = 0; // extra exam attempts beyond first
    $totalTimeTaken   = 0; // seconds across all exams

    foreach ($courses as $course) {
        if (is_course_complete($userId, $course['id'])) {
            $coursesCompleted++;
        }

        // Best quiz score per module (30% weight)
        $modules = get_modules_for_course($course['id']);
        foreach ($modules as $module) {
            $quiz = get_quiz_for_module($module['id']);
            if ($quiz) {
                $attempt = get_best_quiz_attempt($userId, $quiz['id']);
                if ($attempt) {
                    $totalQuizScore += $attempt['score'];
                    $quizCount++;
                }
            }
        }

        // Best exam score (70% weight) + attempt count + speed
        $exam = get_final_exam_for_course($course['id']);
        if ($exam) {
            $best = get_best_exam_attempt($userId, $exam['id']);
            if ($best) {
                $totalExamScore += $best['score'];
                $examCount++;
                $totalTimeTaken += (int)($best['time_taken'] ?? 0);
            }
            // Count all attempts for this exam (penalty for extra tries)
            $allAttempts = db()->prepare('SELECT COUNT(*) FROM final_exam_attempts WHERE user_id = ? AND exam_id = ?');
            $allAttempts->execute([$userId, $exam['id']]);
            $n = (int)$allAttempts->fetchColumn();
            if ($n > 1) $totalAttempts += ($n - 1); // only penalise attempts beyond the first
        }
    }

    $avgQuizScore = $quizCount > 0 ? round($totalQuizScore / $quizCount, 2) : 0;
    $avgExamScore = $examCount  > 0 ? round($totalExamScore / $examCount,  2) : 0;

    // Weighted base: Quizzes 30%, Exams 70%
    $baseScore = ($avgQuizScore * 0.30) + ($avgExamScore * 0.70);

    // Speed bonus: up to +5 points.
    // Benchmark: 3 exams × 45 min = 8100 s. Finishing in ≤ half that = +5 pts.
    $speedBonus = 0;
    if ($examCount > 0 && $totalTimeTaken > 0) {
        $benchmark  = $examCount * 2700; // 45 min per exam in seconds
        $ratio      = $totalTimeTaken / $benchmark;
        $speedBonus = round(max(0, (1 - $ratio) * 5), 2); // 0–5 pts
    }

    // Attempt penalty: -2 points per extra attempt, max -10
    $attemptPenalty = round(min($totalAttempts * 2, 10), 2);

    $compositeScore = round(max(0, min(100, $baseScore + $speedBonus - $attemptPenalty)), 2);

    // Eligibility: must pass every course AND hit the 75% composite threshold
    $meetsThreshold = $compositeScore >= 75 && is_eligible($userId);
    $status = 'pending';
    if ($meetsThreshold) {
        $status = 'eligible';
    } elseif ($coursesCompleted > 0) {
        $status = 'needs_review';
    }

    if ($review) {
        $stmt = db()->prepare('
            UPDATE candidate_reviews
            SET courses_completed = ?, total_score = ?, avg_quiz_score = ?,
                avg_exam_score = ?, composite_score = ?, speed_bonus = ?,
                attempt_penalty = ?, eligibility_status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
        ');
        $stmt->execute([
            $coursesCompleted, $baseScore, $avgQuizScore,
            $avgExamScore, $compositeScore, $speedBonus,
            $attemptPenalty, $status, $userId,
        ]);
    } else {
        $stmt = db()->prepare('
            INSERT INTO candidate_reviews
                (user_id, courses_completed, total_score, avg_quiz_score, avg_exam_score,
                 composite_score, speed_bonus, attempt_penalty, eligibility_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $userId, $coursesCompleted, $baseScore, $avgQuizScore, $avgExamScore,
            $compositeScore, $speedBonus, $attemptPenalty, $status,
        ]);
    }
}

// ─── Admin stats ──────────────────────────────────────────
function count_users(): int {
    return (int)db()->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn();
}

function count_enrollments(): int {
    return (int)db()->query('SELECT COUNT(*) FROM user_enrollments')->fetchColumn();
}

function count_quiz_attempts(): int {
    return (int)db()->query('SELECT COUNT(*) FROM quiz_attempts')->fetchColumn();
}

function get_course_stats(): array {
    $stmt = db()->query('SELECT c.title, COUNT(DISTINCT ue.user_id) AS enrolled FROM courses c LEFT JOIN user_enrollments ue ON ue.course_id = c.id WHERE c.status = "published" GROUP BY c.id ORDER BY c.order_index');
    return $stmt->fetchAll();
}

// ─── Analytics helpers ────────────────────────────────────
function get_analytics_overview(): array {
    $totalStudents = count_users();
    $totalEnrollments = count_enrollments();
    $totalAttempts = count_quiz_attempts();
    
    // Average completion rate
    $avgCompletion = db()->query('
        SELECT AVG(completion_rate) as avg FROM (
            SELECT ue.user_id, 
                   CAST(COUNT(DISTINCT ulp.lesson_id) AS FLOAT) / NULLIF(COUNT(DISTINCT l.id), 0) * 100 as completion_rate
            FROM user_enrollments ue
            JOIN modules m ON m.course_id = ue.course_id
            JOIN lessons l ON l.module_id = m.id
            LEFT JOIN user_lesson_progress ulp ON ulp.lesson_id = l.id AND ulp.user_id = ue.user_id
            GROUP BY ue.user_id, ue.course_id
        )
    ')->fetchColumn() ?: 0;
    
    // Average quiz score
    $avgQuizScore = db()->query('SELECT AVG(score) FROM quiz_attempts')->fetchColumn() ?: 0;
    
    // Pass rate
    $passRate = db()->query('SELECT ROUND(AVG(passed) * 100) FROM quiz_attempts')->fetchColumn() ?: 0;
    
    // Eligible candidates count
    $eligibleCount = db()->query('SELECT COUNT(*) FROM candidate_reviews WHERE eligibility_status = "eligible"')->fetchColumn() ?: 0;
    
    return [
        'total_students' => $totalStudents,
        'total_enrollments' => $totalEnrollments,
        'total_quiz_attempts' => $totalAttempts,
        'avg_completion_rate' => round($avgCompletion, 1),
        'avg_quiz_score' => round($avgQuizScore, 1),
        'pass_rate' => $passRate,
        'eligible_candidates' => $eligibleCount
    ];
}

function get_course_analytics(): array {
    $courses = get_all_published_courses();
    $analytics = [];
    
    foreach ($courses as $course) {
        // Enrollment count
        $enrolled = db()->prepare('SELECT COUNT(*) FROM user_enrollments WHERE course_id = ?');
        $enrolled->execute([$course['id']]);
        $enrolledCount = $enrolled->fetchColumn();
        
        // Completion count
        $completed = 0;
        $enrolledUsers = db()->prepare('SELECT user_id FROM user_enrollments WHERE course_id = ?');
        $enrolledUsers->execute([$course['id']]);
        while ($row = $enrolledUsers->fetch()) {
            if (is_course_complete($row['user_id'], $course['id'])) {
                $completed++;
            }
        }
        
        // Average quiz score for this course
        $avgScore = db()->prepare('
            SELECT AVG(qa.score) 
            FROM quiz_attempts qa 
            JOIN quizzes q ON qa.quiz_id = q.id 
            JOIN modules m ON q.module_id = m.id 
            WHERE m.course_id = ?
        ');
        $avgScore->execute([$course['id']]);
        
        $analytics[] = [
            'course' => $course,
            'enrolled' => $enrolledCount,
            'completed' => $completed,
            'completion_rate' => $enrolledCount > 0 ? round(($completed / $enrolledCount) * 100, 1) : 0,
            'avg_score' => round($avgScore->fetchColumn() ?: 0, 1)
        ];
    }
    
    return $analytics;
}

function get_candidates_for_review(string $status = 'all', int $limit = 0, int $offset = 0): array {
    $sql = '
        SELECT u.*, cr.*,
               u.id as id,
               (SELECT COUNT(*) FROM user_enrollments WHERE user_id = u.id) as enrollment_count,
               (SELECT COUNT(*) FROM user_lesson_progress WHERE user_id = u.id) as lessons_completed
        FROM users u
        LEFT JOIN candidate_reviews cr ON cr.user_id = u.id
        WHERE u.role = "student"
    ';

    if ($status !== 'all') {
        $sql .= ' AND cr.eligibility_status = ?';
    }

    $sql .= ' ORDER BY cr.total_score DESC, u.created_at DESC';

    if ($limit > 0) {
        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
    }

    $stmt = db()->prepare($sql);

    if ($status !== 'all') {
        $stmt->execute([$status]);
    } else {
        $stmt->execute();
    }

    return $stmt->fetchAll();
}

function count_candidates_for_review(string $status = 'all'): int {
    if ($status !== 'all') {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users u LEFT JOIN candidate_reviews cr ON cr.user_id = u.id WHERE u.role = "student" AND cr.eligibility_status = ?');
        $stmt->execute([$status]);
    } else {
        $stmt = db()->query('SELECT COUNT(*) FROM users WHERE role = "student"');
    }
    return (int) $stmt->fetchColumn();
}

function render_pagination(int $currentPage, int $totalPages, string $baseUrl): string {
    if ($totalPages <= 1) return '';

    $html = '<nav aria-label="Page navigation" class="d-flex justify-content-center mt-3"><ul class="pagination pagination-sm mb-0">';

    $html .= '<li class="page-item ' . ($currentPage <= 1 ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . h($baseUrl . 'page=' . ($currentPage - 1)) . '">&laquo; Prev</a></li>';

    $start = max(1, $currentPage - 2);
    $end   = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . h($baseUrl . 'page=1') . '">1</a></li>';
        if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . h($baseUrl . 'page=' . $i) . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . h($baseUrl . 'page=' . $totalPages) . '">' . $totalPages . '</a></li>';
    }

    $html .= '<li class="page-item ' . ($currentPage >= $totalPages ? 'disabled' : '') . '">';
    $html .= '<a class="page-link" href="' . h($baseUrl . 'page=' . ($currentPage + 1)) . '">Next &raquo;</a></li>';

    $html .= '</ul></nav>';
    return $html;
}

// ─── Activity logging ─────────────────────────────────────
function log_activity(int $userId, string $type, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void {
    $stmt = db()->prepare('INSERT INTO activity_log (user_id, activity_type, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $type, $entityType, $entityId, $details]);
}

function update_user_activity(int $userId): void {
    $stmt = db()->prepare('UPDATE users SET last_activity = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$userId]);
}

// ─── Site Settings helpers ────────────────────────────────
function get_site_settings(): array {
    try {
        $stmt = db()->query('SELECT setting_key, setting_value FROM site_settings');
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (\PDOException $e) {
        // Table may not exist yet (before setup)
        return [];
    }
}

function get_setting(string $key, ?string $default = null): ?string {
    try {
        $stmt = db()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (\PDOException $e) {
        return $default;
    }
}

function set_setting(string $key, string $value): void {
    $existing = get_setting($key);
    if ($existing !== null) {
        $stmt = db()->prepare('UPDATE site_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?');
        $stmt->execute([$value, $key]);
    } else {
        $stmt = db()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
        $stmt->execute([$key, $value]);
    }
}
