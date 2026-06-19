<?php
/**
 * Migration v10 — Attach verified YouTube videos to the FastAPI course lessons.
 * Run ONCE via browser, then DELETE this file.
 *
 *   Access: /database/migrate_v10.php?key=hackathon2026fastapivideos
 *
 * DATA-SAFE: only runs narrow UPDATEs that set lessons.video_url for specific
 * lessons in the "FastAPI & Databases: Build Production APIs" course, matched by
 * course + lesson title. It does NOT delete/insert anything, does NOT touch
 * lesson IDs, student progress, other lessons, or other courses. Idempotent:
 * re-running just re-applies the same URLs.
 *
 * Each URL has been verified live and embeddable via YouTube's oEmbed endpoint.
 * Only embed-form URLs (youtube.com/embed/ID) are used, as pages/lesson.php
 * renders video_url inside an <iframe>.
 *
 * Wires up ALL 7 modules (30 lessons) — the complete FastAPI course.
 */

define('MIGRATION_PASSWORD', 'hackathon2026fastapivideos');
if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026fastapivideos to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();
header('Content-Type: text/html; charset=utf-8');

$COURSE_TITLE = 'FastAPI & Databases: Build Production APIs';

// lesson title  =>  YouTube EMBED url (verified live + embeddable)
$VIDEOS = [
    // ── Module 1: REST APIs & FastAPI Fundamentals ──
    'What is a REST API?'
        => 'https://www.youtube.com/embed/lsMQRaeKNDk',   // IBM Technology — What is a REST API?
    'Setting up FastAPI and your first endpoint'
        => 'https://www.youtube.com/embed/7AMjmCTumuo',   // Corey Schafer — FastAPI Part 1: Getting Started
    'Path and query parameters'
        => 'https://www.youtube.com/embed/WRjXIA5pMtk',   // Corey Schafer — FastAPI Part 3: Path Parameters
    'Request bodies, status codes, and responses'
        => 'https://www.youtube.com/embed/zq0_g3BKltE',   // BugBytes — Request Body and POST requests

    // ── Module 2: Request/Response Modelling with Pydantic ──
    'Pydantic models and validation'
        => 'https://www.youtube.com/embed/ok8bF8M7gjk',   // MathByte Academy — Pydantic (V2) In-depth Starter Guide
    'Request bodies with Pydantic'
        => 'https://www.youtube.com/embed/9GHxnttXxrA',   // Corey Schafer — FastAPI Part 4: Pydantic Schemas
    'Response models and serialization'
        => 'https://www.youtube.com/embed/WjStUP4XFCc',   // Sarthi Technology — FastAPI Response Model
    'Custom validation: validators and nested models'
        => 'https://www.youtube.com/embed/mOpeuZeBYt8',   // Yash Jain — field_validator and model_validator

    // ── Module 3: PostgreSQL & SQLAlchemy ORM ──
    'Relational databases & PostgreSQL basics'
        => 'https://www.youtube.com/embed/WivyLhbR-_g',   // Ruslan Brilenkov — Relational Database EXPLAINED
    'SQLAlchemy setup and models'
        => 'https://www.youtube.com/embed/XWtj4zLl_tg',   // Ssali Jonathan — SQLAlchemy 2.0 ORM Crash Course
    'CRUD operations with SQLAlchemy'
        => 'https://www.youtube.com/embed/f0-kEG37GE0',   // Zeq Tech — SQLAlchemy ORM CRUD
    'Relationships: one-to-many and many-to-many'
        => 'https://www.youtube.com/embed/wvQJzMrKy9E',   // Ssali Jonathan — One-to-Many Relationships (SQLAlchemy 2.0)
    'Schema migrations with Alembic'
        => 'https://www.youtube.com/embed/HuOG7VS8qvE',   // Describly — Auto-Generating Migrations with Alembic

    // ── Module 4: Building a Full CRUD API ──
    'Structuring a FastAPI project'
        => 'https://www.youtube.com/embed/_kNyYIFSOFU',   // Ssali Jonathan — Modular Project Structure (Beyond CRUD Part 4)
    'Dependency injection and database sessions'
        => 'https://www.youtube.com/embed/Tyhtp1Ou_Pk',   // Ssali Jonathan — Intro to Dependency Injection in FastAPI
    'Building the CRUD endpoints'
        => 'https://www.youtube.com/embed/HxRKUlb4qqk',   // AhsanDev — Complete CRUD FastAPI + SQLAlchemy + PostgreSQL
    'Error handling and validation in APIs'
        => 'https://www.youtube.com/embed/7MHDDOrDx-w',   // ArjanCodes — Proper Exception Handling with FastAPI

    // ── Module 5: Authentication: JWT & OAuth2 ──
    'Authentication vs authorization, and how tokens work'
        => 'https://www.youtube.com/embed/Y2H3DXDeS3Q',   // Ariel Weinberger — JWT Explained In Under 10 Minutes
    'Hashing passwords safely'
        => 'https://www.youtube.com/embed/hNa05wr0DSA',   // Julian Nash — Hashing passwords with Python and Bcrypt
    'OAuth2 password flow and issuing JWTs'
        => 'https://www.youtube.com/embed/Go4wYJJhR3k',   // Corey Schafer — FastAPI Part 10: Auth (Registration & Login with JWT)
    'Protecting routes with a current-user dependency'
        => 'https://www.youtube.com/embed/MY0TFMMm9B0',   // Corey Schafer — FastAPI Part 11: Authorization (Protecting Routes / Current User)
    'Roles, scopes, and authorization'
        => 'https://www.youtube.com/embed/_k2M-LpxId8',   // Ssali Jonathan — Role-Based Access Control (Beyond CRUD Part 13)

    // ── Module 6: Git Workflows & CI/CD Basics ──
    'Git workflow essentials: branches and pull requests'
        => 'https://www.youtube.com/embed/oFYyTZwMyAg',   // LearnCode.academy — GitHub PR, Branching, Merging & Team Workflow
    'Testing your FastAPI app'
        => 'https://www.youtube.com/embed/SO7m7nod0ts',   // Corey Schafer — FastAPI Part 17: Testing (Pytest, Fixtures)
    'Continuous Integration with GitHub Actions'
        => 'https://www.youtube.com/embed/ZR34Cnkelk4',   // PyTest With Eric — Automated testing with GitHub Actions
    'From CI to CD: automating delivery'
        => 'https://www.youtube.com/embed/w6Y19RWawc0',   // Eye on Tech — The CI/CD Pipeline, Explained

    // ── Module 7: Deployment on Railway & Render ──
    'Preparing your app for production'
        => 'https://www.youtube.com/embed/iDgbS3RTkTE',   // Analytics Vidhya — FastAPI Settings: Pydantic-Settings
    'Deploying to Render'
        => 'https://www.youtube.com/embed/nPUA8BLWzeY',   // Ssali Jonathan — Deploying FastAPI and PostgreSQL to Render
    'Deploying to Railway with managed PostgreSQL'
        => 'https://www.youtube.com/embed/HFVuJUlO7ik',   // Programming with Kumaresan — Deploy FastAPI on Railway
    'Production operations: migrations, logs & monitoring'
        => 'https://www.youtube.com/embed/kmJz8w5ij8Y',   // Code Collider — 15 FastAPI Best Practices For Production
];

echo '<h2>Attaching videos to: ' . htmlspecialchars($COURSE_TITLE) . '</h2>';

$stmt = $pdo->prepare('SELECT id FROM courses WHERE title = ?');
$stmt->execute([$COURSE_TITLE]);
$courseId = $stmt->fetchColumn();

if (!$courseId) {
    http_response_code(404);
    die('<h3 style="color:red">Course not found. Run migrate_v9.php first.</h3>');
}

$pdo->beginTransaction();
try {
    $upd = $pdo->prepare(
        'UPDATE lessons SET video_url = ?
         WHERE title = ?
           AND module_id IN (SELECT id FROM modules WHERE course_id = ?)'
    );

    $missing = [];
    foreach ($VIDEOS as $lessonTitle => $embedUrl) {
        $upd->execute([$embedUrl, $lessonTitle, $courseId]);
        if ($upd->rowCount() > 0) {
            echo '<p>&#9989; ' . htmlspecialchars($lessonTitle) . ' &rarr; ' . htmlspecialchars($embedUrl) . '</p>';
        } else {
            $check = $pdo->prepare(
                'SELECT video_url FROM lessons
                 WHERE title = ? AND module_id IN (SELECT id FROM modules WHERE course_id = ?)'
            );
            $check->execute([$lessonTitle, $courseId]);
            $current = $check->fetchColumn();
            if ($current === false) {
                $missing[] = $lessonTitle;
                echo '<p style="color:#b00">&#9888; Lesson not found: ' . htmlspecialchars($lessonTitle) . '</p>';
            } else {
                echo '<p>&#8505;&#65039; Already set: ' . htmlspecialchars($lessonTitle) . '</p>';
            }
        }
    }

    $pdo->commit();
    echo '<h3 style="color:green">Done. Applied ' . count($VIDEOS) . ' video link(s).</h3>';
    if ($missing) {
        echo '<p style="color:#b00">Check the titles above — these did not match any lesson: '
            . htmlspecialchars(implode('; ', $missing)) . '</p>';
    }
    echo '<p><strong>Now DELETE this file from the server.</strong></p>';
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo '<h3 style="color:red">Failed: ' . htmlspecialchars($e->getMessage()) . '</h3><p>No changes were saved.</p>';
}
