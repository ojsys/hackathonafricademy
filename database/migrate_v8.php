<?php
/**
 * Migration v8 — Attach verified YouTube videos to the Python course lessons.
 * Run ONCE via browser, then DELETE this file.
 *
 *   Access: /database/migrate_v8.php?key=hackathon2026pyvideos
 *
 * DATA-SAFE: only runs narrow UPDATEs that set lessons.video_url for specific
 * lessons in the "Python: From Zero to Developer" course, matched by course +
 * lesson title. It does NOT delete/insert anything, does NOT touch lesson IDs,
 * student progress, other lessons, or other courses. Idempotent: re-running
 * just re-applies the same URLs.
 *
 * Each URL has been verified live and embeddable via YouTube's oEmbed endpoint.
 * Only embed-form URLs (youtube.com/embed/ID) are used, as pages/lesson.php
 * renders video_url inside an <iframe>.
 *
 * Wires up ALL 12 modules (46 lessons) — the complete Python course.
 */

define('MIGRATION_PASSWORD', 'hackathon2026pyvideos');
if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026pyvideos to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();
header('Content-Type: text/html; charset=utf-8');

$COURSE_TITLE = 'Python: From Zero to Developer';

// lesson title  =>  YouTube EMBED url (verified live + embeddable)
$VIDEOS = [
    // ── Module 1: Python Fundamentals — Getting Started ──
    'What is Python and how does it run?'
        => 'https://www.youtube.com/embed/BkHdmAhapws',   // Afternerd — What is the Python Interpreter?
    'Installing Python and your first program'
        => 'https://www.youtube.com/embed/D2cwvpJSBX4',   // Visual Studio Code (official) — Getting Started with Python in VS Code
    'Variables, objects, and dynamic typing'
        => 'https://www.youtube.com/embed/cQT33yu9pY8',   // Programming with Mosh — Python Variables
    'Comments, the REPL workflow, and the Zen of Python'
        => 'https://www.youtube.com/embed/uBHOb55-fBo',   // Indian Pythonista — The Zen of Python, decoded.

    // ── Module 2: Core Syntax & Data Types ──
    'Numbers: integers, floats, and arithmetic'
        => 'https://www.youtube.com/embed/khKv-8q7YmY',   // Corey Schafer — Integers and Floats
    'Strings and f-strings'
        => 'https://www.youtube.com/embed/k9TUPpGqYTo',   // Corey Schafer — Strings: Working with Textual Data
    'Booleans, None, and comparison/logical operators'
        => 'https://www.youtube.com/embed/0MBLkNlffT0',   // ProgrammingKnowledge — Boolean, Comparison & Logical Operators
    'Type conversion, input() and print()'
        => 'https://www.youtube.com/embed/8T14Q1LDDwk',   // rory mulcahey — Input & Type Conversion

    // ── Module 3: Control Flow ──
    'Making decisions: if, elif, else'
        => 'https://www.youtube.com/embed/DZwmZ8Usvnk',   // Corey Schafer — Conditionals and Booleans
    'Looping with for and range'
        => 'https://www.youtube.com/embed/0P_FYKA7sa8',   // Kody Simpson — For Loops, range(), enumerate()
    'while loops, break, continue, and loop-else'
        => 'https://www.youtube.com/embed/2nAOQGJPefU',   // Digital Academy — WHILE Loop (break, continue, else)

    // ── Module 4: Data Structures ──
    'Lists: ordered, changeable collections'
        => 'https://www.youtube.com/embed/W8KRzm-HUcc',   // Corey Schafer — Lists, Tuples, and Sets
    'Tuples: fixed, immutable records'
        => 'https://www.youtube.com/embed/LaswEEqcgv0',   // Academind — Tuples & Tuple Unpacking
    'Dictionaries: key → value lookup'
        => 'https://www.youtube.com/embed/daefaLgNkw0',   // Corey Schafer — Dictionaries: Key-Value Pairs
    'Sets: uniqueness and membership'
        => 'https://www.youtube.com/embed/sBvaPopWOmQ',   // Socratica — Sets in Python
    'Comprehensions and choosing the right structure'
        => 'https://www.youtube.com/embed/3dt4OGnU5sM',   // Corey Schafer — Comprehensions

    // ── Module 5: Functions ──
    'Defining and calling functions'
        => 'https://www.youtube.com/embed/9Os0o3wzS_I',   // Corey Schafer — Functions
    'Arguments in depth: defaults, keywords, *args, **kwargs'
        => 'https://www.youtube.com/embed/Vh__2V2tXUM',   // Bro Code — *args & **kwargs
    'Scope: where names live'
        => 'https://www.youtube.com/embed/QVdf0LgmICw',   // Corey Schafer — Variable Scope (LEGB)
    'Lambdas, higher-order functions, docstrings & type hints'
        => 'https://www.youtube.com/embed/YIOYJgLQYiY',   // Dave Gray — lambda, map, filter, reduce (HOFs)

    // ── Module 6: Object-Oriented Programming ──
    'Classes and objects: the basics'
        => 'https://www.youtube.com/embed/ZDa-Z5JzLYM',   // Corey Schafer — OOP 1: Classes and Instances
    'The four pillars of OOP — overview'
        => 'https://www.youtube.com/embed/ZVTuWsrjvyU',   // JimShapedCoding — OOP Principles with Examples
    'Pillar 1 — Encapsulation'
        => 'https://www.youtube.com/embed/jCzT9XFZ5bw',   // Corey Schafer — Property Decorators (Getters/Setters/Deleters)
    'Pillar 2 — Abstraction'
        => 'https://www.youtube.com/embed/0HbgNSoexFw',   // Real Python — Abstract Base Classes
    'Pillar 3 — Inheritance'
        => 'https://www.youtube.com/embed/RSl87lqOXDE',   // Corey Schafer — OOP 4: Inheritance
    'Pillar 4 — Polymorphism'
        => 'https://www.youtube.com/embed/P1vH3Pfw6BI',   // Telusko — Introduction to Polymorphism
    'Dunder methods and dataclasses'
        => 'https://www.youtube.com/embed/3ohzBxoFHAY',   // Corey Schafer — OOP 5: Special (Magic/Dunder) Methods

    // ── Module 7: Clean Code & SOLID ──
    'What is clean code, and why it matters'
        => 'https://www.youtube.com/embed/vhdUyGs_f6c',   // ArjanCodes — 5 Tips for Writing Clean Python Code
    'SOLID part 1 — SRP & OCP'
        => 'https://www.youtube.com/embed/pTB30aXS77U',   // ArjanCodes — Uncle Bob's SOLID Principles Made Easy (Python)
    'SOLID part 2 — LSP, ISP & DIP'
        => 'https://www.youtube.com/embed/ZkknJI3QMss',   // Eric Roby — Learn SOLID Principles Easy in Python
    'DRY, KISS, YAGNI & composition over inheritance'
        => 'https://www.youtube.com/embed/TAgQCliUDmg',   // Software Developer Diaries — KISS, YAGNI, DRY crash course

    // ── Module 8: Error & Exception Handling ──
    'Exceptions and reading tracebacks'
        => 'https://www.youtube.com/embed/Qmj5UQRkL4s',   // Python Morsels — Python's tracebacks explained
    'try, except, else, finally'
        => 'https://www.youtube.com/embed/NIWwJbo-9_8',   // Corey Schafer — Try/Except Blocks for Error Handling
    'Raising exceptions, custom exceptions & EAFP'
        => 'https://www.youtube.com/embed/bUJzaWAw8Sk',   // Digital Academy — Raise User Defined (Custom) Exceptions

    // ── Module 9: File Handling & Data Formats ──
    'Reading and writing files with context managers'
        => 'https://www.youtube.com/embed/Uh2ebFW8OYM',   // Corey Schafer — File Objects: Reading and Writing
    'Paths the right way with pathlib'
        => 'https://www.youtube.com/embed/yxa-DJuuTBI',   // Corey Schafer — Pathlib: Modern File Paths
    'Working with JSON and CSV'
        => 'https://www.youtube.com/embed/9N6a-VLBa2I',   // Corey Schafer — Working with JSON Data

    // ── Module 10: Modules, Packages, venv & pip ──
    'Modules, imports & the standard library'
        => 'https://www.youtube.com/embed/CqvZ3vGoGs0',   // Corey Schafer — Import Modules & the Standard Library
    'Virtual environments and pip'
        => 'https://www.youtube.com/embed/Kg1Yvry_Ydk',   // Corey Schafer — VENV: Virtual Environments
    'Packages and project structure'
        => 'https://www.youtube.com/embed/niMybnzmzqc',   // ZazenCodes — Basic Project Structure for a Python App

    // ── Module 11: Writing Clean, Testable Code ──
    'Why test? assert and your first tests'
        => 'https://www.youtube.com/embed/jjUgWvNxHys',   // Real Python — Starting With Python's assert Statement
    'unittest and pytest'
        => 'https://www.youtube.com/embed/6tNS--WetLI',   // Corey Schafer — Unit Testing with the unittest Module
    'TDD, testable design & debugging'
        => 'https://www.youtube.com/embed/vBJM5pzBfhY',   // Codemanship — TDD in Python: The 3 Steps of TDD

    // ── Module 12: Capstone — Build a Real CLI App ──
    'Project brief, setup & design'
        => 'https://www.youtube.com/embed/JwwlRkLKj7o',   // Laurențiu Andronache — How to start a command-line app
    'Implementing the core: models, storage, logic'
        => 'https://www.youtube.com/embed/CvQ7e6yUtnw',   // ArjanCodes — Why Python Data Classes Are Awesome
    'The CLI, tests & shipping it'
        => 'https://www.youtube.com/embed/0twL6MXCLdQ',   // sentdex — Argparse for CLI
];

echo '<h2>Attaching videos to: ' . htmlspecialchars($COURSE_TITLE) . '</h2>';

// Find the course (must exist — seeded by migrate_v7)
$stmt = $pdo->prepare('SELECT id FROM courses WHERE title = ?');
$stmt->execute([$COURSE_TITLE]);
$courseId = $stmt->fetchColumn();

if (!$courseId) {
    http_response_code(404);
    die('<h3 style="color:red">Course not found. Run migrate_v7.php first.</h3>');
}

$pdo->beginTransaction();
try {
    // Narrow, guarded UPDATE: only this course's lessons, only video_url.
    $upd = $pdo->prepare(
        'UPDATE lessons SET video_url = ?
         WHERE title = ?
           AND module_id IN (SELECT id FROM modules WHERE course_id = ?)'
    );

    $updated = 0; $missing = [];
    foreach ($VIDEOS as $lessonTitle => $embedUrl) {
        $upd->execute([$embedUrl, $lessonTitle, $courseId]);
        if ($upd->rowCount() > 0) {
            $updated += $upd->rowCount();
            echo '<p>&#9989; ' . htmlspecialchars($lessonTitle) . ' &rarr; ' . htmlspecialchars($embedUrl) . '</p>';
        } else {
            // rowCount() can be 0 on SQLite if the value is unchanged (re-run).
            // Distinguish "already set" from "lesson not found".
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
