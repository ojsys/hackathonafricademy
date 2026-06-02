<?php
/**
 * HackathonAfrica LMS — Debugging Exercises Migration & Seed
 *
 * Adds the `mode` column to coding_exercises ('build' | 'debug') and seeds a set
 * of "fix the buggy code" challenges across the HTML, CSS and JavaScript lessons.
 * Debug exercises load broken code into the same Monaco editor + live browser
 * preview already used for build exercises — candidates fix the code until the
 * expected behaviour is met.
 *
 * Run ONCE: /database/add_debug_exercises.php?key=hackafrica2026debug
 * Re-running is safe (it removes previously seeded debug exercises first).
 * DELETE this file after running in production.
 */

define('DEBUG_SEED_KEY', 'hackafrica2026debug');
if (($_GET['key'] ?? '') !== DEBUG_SEED_KEY) {
    http_response_code(403);
    die('<h2>403 — Access Denied</h2><p>Add ?key=hackafrica2026debug to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();
$pdo->exec('PRAGMA foreign_keys = OFF');

header('Content-Type: text/html; charset=utf-8');
echo '<body style="font-family:system-ui;max-width:760px;margin:2rem auto;line-height:1.5">';
echo '<h2>Debugging Exercises Setup</h2>';

// ── Schema patch: add the `mode` column if it is missing ─────────────────────
$cols = array_column($pdo->query('PRAGMA table_info(coding_exercises)')->fetchAll(), 'name');
if (!in_array('mode', $cols)) {
    $pdo->exec("ALTER TABLE coding_exercises ADD COLUMN mode TEXT NOT NULL DEFAULT 'build'");
    echo "<p>✅ Added column: coding_exercises.mode (default 'build')</p>";
} else {
    echo "<p>↪ Column coding_exercises.mode already present</p>";
}

// ── Idempotency: clear previously seeded debug exercises ─────────────────────
$removed = $pdo->exec("DELETE FROM coding_exercises WHERE mode = 'debug'");
echo "<p>🧹 Removed {$removed} existing debug exercise(s)</p>";

$inserted = 0;
function addDebug(
    PDO $pdo, string $lessonTitle, string $title, string $description,
    string $instructions, string $buggyCode, string $solution, string $hints,
    string $type, string $difficulty, int $points, int &$count
): void {
    $row = $pdo->prepare('SELECT id FROM lessons WHERE title = ? LIMIT 1');
    $row->execute([$lessonTitle]);
    $lesson = $row->fetch();
    if (!$lesson) { echo "<p style='color:#c00'>&#9888; Lesson not found: " . htmlspecialchars($lessonTitle) . "</p>"; return; }

    // Append after any existing exercises for this lesson
    $ord = $pdo->prepare('SELECT COALESCE(MAX(order_index), 0) + 1 FROM coding_exercises WHERE lesson_id = ?');
    $ord->execute([$lesson['id']]);
    $orderIndex = (int)$ord->fetchColumn();

    $stmt = $pdo->prepare(
        'INSERT INTO coding_exercises
            (lesson_id, title, description, instructions, starter_code, solution_code, hints, exercise_type, difficulty, points, order_index, mode)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $lesson['id'], $title, $description, $instructions,
        $buggyCode, $solution, $hints, $type, $difficulty, $points, $orderIndex, 'debug',
    ]);
    $count++;
    echo "<p>✅ Added debug exercise: <strong>" . htmlspecialchars($title) . "</strong> → " . htmlspecialchars($lessonTitle) . "</p>";
}

// =====================================================================
//  HTML — Fix a broken document structure
// =====================================================================
$buggy = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Page</title>
<body>
  <h1>Welcome</h1>
  <p>This is my first page.
  <ul>
    <li>Home</li>
    <li>About</li>
  </ul>
</body>
</html>
EOT;
$fixed = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Page</title>
</head>
<body>
  <h1>Welcome</h1>
  <p>This is my first page.</p>
  <ul>
    <li>Home</li>
    <li>About</li>
  </ul>
</body>
</html>
EOT;
addDebug($pdo, 'HTML Document Structure',
    'Fix the Broken HTML Page',
    'This page has malformed structure. Repair the tags so it renders cleanly.',
    "1. The head section is properly closed with </head> before the body begins\n2. The paragraph is properly closed with </p>\n3. The page shows one heading and a list of two items\n4. The document renders without broken or overlapping tags",
    $buggy, $fixed,
    'The <head> opens but never closes|A <p> without a closing tag swallows the content after it|Browsers try to guess at broken tags — fix them so the structure is explicit',
    'html', 'easy', 10, $inserted);

// =====================================================================
//  CSS — Fix broken style rules (syntax bugs)
// =====================================================================
$buggy = <<<'EOT'
.card {
  paddng: 1rem;
  border-radius 8px;
  background: #f5f5f5
  color: #222;
}

button {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: .5rem 1rem;
  cursor pointer;
}
EOT;
$fixed = <<<'EOT'
.card {
  padding: 1rem;
  border-radius: 8px;
  background: #f5f5f5;
  color: #222;
}

button {
  background: #2563eb;
  color: #fff;
  border: none;
  padding: .5rem 1rem;
  cursor: pointer;
}
EOT;
addDebug($pdo, 'The Box Model',
    'Fix the Broken CSS Rules',
    'Several style rules are malformed so the browser silently ignores them. Fix the syntax.',
    "1. The card has internal padding\n2. The card has rounded corners\n3. The button text is white on a blue background\n4. The button shows a pointer cursor",
    $buggy, $fixed,
    'Every declaration needs property: value; — check each colon and semicolon|One property name is misspelled|A missing semicolon makes CSS drop the rest of the rule',
    'css', 'easy', 10, $inserted);

// =====================================================================
//  JS — Fix the grade classifier (operators / control flow)
// =====================================================================
$buggy = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Grade Classifier</title></head>
<body>
  <h1>Grade Classifier</h1>
  <div id="output"></div>
  <script>
    function classify(score) {
      if (score = 90) {
        return "A";
      } else if (score > 80) {
        return "B";
      } else if (score >= 70) {
        return "C";
      }
    }

    const output = document.getElementById("output");
    [95, 85, 80, 72, 50].forEach(function (s) {
      output.innerHTML += "<p>Score " + s + " => Grade " + classify(s) + "</p>";
    });
  </script>
</body>
</html>
EOT;
$fixed = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Grade Classifier</title></head>
<body>
  <h1>Grade Classifier</h1>
  <div id="output"></div>
  <script>
    function classify(score) {
      if (score >= 90) {
        return "A";
      } else if (score >= 80) {
        return "B";
      } else if (score >= 70) {
        return "C";
      } else {
        return "F";
      }
    }

    const output = document.getElementById("output");
    [95, 85, 80, 72, 50].forEach(function (s) {
      output.innerHTML += "<p>Score " + s + " => Grade " + classify(s) + "</p>";
    });
  </script>
</body>
</html>
EOT;
addDebug($pdo, 'Control Flow',
    'Fix the Grade Classifier',
    'The grade boundaries are wrong and one comparison is actually an assignment. Make the grades correct.',
    "1. A score of 90 or above returns grade A\n2. A score of 80 to 89 returns grade B\n3. A score of 70 to 79 returns grade C\n4. Any score below 70 returns grade F\n5. The page shows the correct grade for every test score",
    $buggy, $fixed,
    'A single = assigns a value; use >= to compare|score > 80 misses a score of exactly 80|There is no branch for failing scores',
    'combined', 'medium', 15, $inserted);

// =====================================================================
//  JS — Fix the array sum (loop off-by-one + init bug)
// =====================================================================
$buggy = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Array Sum</title></head>
<body>
  <h1>Sum of Numbers</h1>
  <div id="output"></div>
  <script>
    function sumAll(numbers) {
      let total;
      for (let i = 1; i <= numbers.length; i++) {
        total += numbers[i];
      }
      return total;
    }

    const data = [4, 8, 15, 16, 23, 42];
    document.getElementById("output").innerHTML =
      "<p>Total = " + sumAll(data) + "</p>";
  </script>
</body>
</html>
EOT;
$fixed = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Array Sum</title></head>
<body>
  <h1>Sum of Numbers</h1>
  <div id="output"></div>
  <script>
    function sumAll(numbers) {
      let total = 0;
      for (let i = 0; i < numbers.length; i++) {
        total += numbers[i];
      }
      return total;
    }

    const data = [4, 8, 15, 16, 23, 42];
    document.getElementById("output").innerHTML =
      "<p>Total = " + sumAll(data) + "</p>";
  </script>
</body>
</html>
EOT;
addDebug($pdo, 'Loops',
    'Fix the Array Sum',
    'The total comes out wrong (NaN). Fix how the loop starts and ends.',
    "1. The running total starts at 0\n2. The loop visits every number including the first one\n3. The loop does not read past the end of the array\n4. The total for [4, 8, 15, 16, 23, 42] displays as 108",
    $buggy, $fixed,
    'What value does total hold before the first addition?|Array indexes start at 0, not 1|numbers.length is one past the last valid index, so i <= length reads undefined',
    'combined', 'medium', 15, $inserted);

// =====================================================================
//  JS — Fix the click counter (event handling)
// =====================================================================
$buggy = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Counter</title></head>
<body>
  <h1>Click Counter</h1>
  <p>Count: <span id="count">0</span></p>
  <button id="btn">Click me</button>
  <script>
    let count = 0;
    const span = document.getElementById("count");
    const button = document.getElementById("btn");

    button.addEventListener("click", function () {
      count + 1;
      span.textContent = count;
    });
  </script>
</body>
</html>
EOT;
$fixed = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Counter</title></head>
<body>
  <h1>Click Counter</h1>
  <p>Count: <span id="count">0</span></p>
  <button id="btn">Click me</button>
  <script>
    let count = 0;
    const span = document.getElementById("count");
    const button = document.getElementById("btn");

    button.addEventListener("click", function () {
      count = count + 1;
      span.textContent = count;
    });
  </script>
</body>
</html>
EOT;
addDebug($pdo, 'Event Handling',
    'Fix the Click Counter',
    'The button does nothing when clicked — the count never changes. Fix the handler.',
    "1. Clicking the button increases the count by 1 each time\n2. The number shown on the page updates after every click",
    $buggy, $fixed,
    'count + 1 calculates a value but throws it away|You need to store the new value back into count|count++ or count = count + 1 both work',
    'combined', 'easy', 10, $inserted);

echo "<hr><p><strong>Done.</strong> Inserted {$inserted} debugging exercise(s).</p>";
echo '<p style="color:#c00">⚠ Delete this file (database/add_debug_exercises.php) after running in production.</p>';
echo '</body>';
