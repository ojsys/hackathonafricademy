<?php
/**
 * Migration: Add 3 more questions to each of the 12 quizzes
 * Quizzes 1-4: HTML | 5-8: CSS | 9-12: JavaScript
 * Run once: php database/add_quiz_questions_v2.php
 */
$pdo = new PDO('sqlite:' . __DIR__ . '/lms.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$addQ = function(int $id, int $quizId, string $text, array $opts, int $correctIdx) use ($pdo) {
    $pdo->prepare("INSERT OR IGNORE INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES (?, ?, ?, ?)")
        ->execute([$id, $quizId, $text, $id]);
    // Only insert options if none exist yet for this question (prevents duplicates on re-run)
    $existing = $pdo->prepare("SELECT COUNT(*) FROM quiz_options WHERE question_id = ?");
    $existing->execute([$id]);
    if ((int)$existing->fetchColumn() === 0) {
        foreach ($opts as $i => $opt) {
            $pdo->prepare("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?, ?, ?)")
                ->execute([$id, $opt, $i === $correctIdx ? 1 : 0]);
        }
    }
};

// ── Quiz 1: HTML Foundations / Document Structure ─────────────
$addQ(64, 1, 'What attribute on <html> declares the document language?',
    ['lang', 'language', 'locale', 'charset'], 0);
$addQ(65, 1, 'Which HTML tag creates a clickable hyperlink?',
    ['<href>', '<link>', '<a>', '<url>'], 2);
$addQ(66, 1, 'What does <meta charset="UTF-8"> control?',
    ['Page colors', 'Character encoding', 'Font size', 'Script type'], 1);

// ── Quiz 2: Content Elements / Links / Lists ──────────────────
$addQ(67, 2, 'Which HTML tag is used for a definition list?',
    ['<ol>', '<ul>', '<dl>', '<list>'], 2);
$addQ(68, 2, 'What attribute on <img> provides descriptive text for screen readers?',
    ['title', 'alt', 'src', 'caption'], 1);
$addQ(69, 2, 'Which HTML5 tag embeds a video natively in the browser?',
    ['<media>', '<embed>', '<video>', '<movie>'], 2);

// ── Quiz 3: Forms & Semantic HTML ────────────────────────────
$addQ(70, 3, 'Which element groups related form fields with a visible border?',
    ['<group>', '<fieldset>', '<formgroup>', '<section>'], 1);
$addQ(71, 3, 'What does the "method" attribute on a <form> control?',
    ['Where data is sent', 'How data is sent (GET or POST)', 'Input validation rules', 'The form layout'], 1);
$addQ(72, 3, 'Which input type lets users pick from a drop-down list?',
    ['<input type="dropdown">', '<select>', '<datalist>', '<input type="list">'], 1);

// ── Quiz 4: Semantic / Advanced HTML ─────────────────────────
$addQ(73, 4, 'What is the purpose of the <main> element?',
    ['Creates the main menu', 'Wraps the primary content of the page', 'Defines the page header', 'Adds a sidebar'], 1);
$addQ(74, 4, 'What does the data-* attribute allow you to do?',
    ['Fetch data from an API', 'Embed custom data directly on HTML elements', 'Validate form inputs', 'Create CSS variables'], 1);
$addQ(75, 4, 'Which attribute makes any HTML element non-interactive?',
    ['hidden', 'inactive', 'disabled', 'readonly'], 2);

// ── Quiz 5: CSS Selectors & Box Model ────────────────────────
$addQ(76, 5, 'What does box-sizing: border-box do?',
    ['Adds a visible border', 'Includes padding and border in the element\'s total width', 'Removes margins automatically', 'Centers the element'], 1);
$addQ(77, 5, 'Which CSS selector targets an element on mouse hover?',
    ['.hover', ':hover', '#hover', '@hover'], 1);
$addQ(78, 5, 'What does margin: auto do on a block element with a set width?',
    ['Removes margins entirely', 'Centers the element horizontally', 'Adds equal padding', 'Stretches to fill available space'], 1);

// ── Quiz 6: CSS Layout (Flexbox & Grid) ──────────────────────
$addQ(79, 6, 'Which property controls whether flex items wrap to the next line?',
    ['flex-direction', 'flex-wrap', 'flex-flow', 'flex-grow'], 1);
$addQ(80, 6, 'What is the default value of the CSS position property?',
    ['relative', 'absolute', 'fixed', 'static'], 3);
$addQ(81, 6, 'What does position: sticky do?',
    ['Fixes the element to the viewport always', 'Sticks within its scrollable container at a threshold', 'Removes the element from the document flow', 'Overlaps other elements'], 1);

// ── Quiz 7: CSS Animations & Transitions ─────────────────────
$addQ(82, 7, 'Which property sets the speed curve of a CSS transition?',
    ['transition-duration', 'transition-property', 'transition-timing-function', 'animation-name'], 2);
$addQ(83, 7, 'What does animation-iteration-count: infinite do?',
    ['Plays the animation once then stops', 'Reverses the animation', 'Plays the animation indefinitely', 'Delays the animation start'], 2);
$addQ(84, 7, 'Which shorthand property sets all transition sub-properties at once?',
    ['animate', 'transition', 'keyframe', 'effect'], 1);

// ── Quiz 8: CSS Architecture (Variables, BEM) ────────────────
$addQ(85, 8, 'What is the correct syntax to declare a CSS custom property?',
    ['--color: red', '$color: red', '@color: red', 'var-color: red'], 0);
$addQ(86, 8, 'What does an @media rule allow you to do?',
    ['Import external CSS files', 'Apply styles based on screen size or device conditions', 'Animate elements', 'Define CSS variables globally'], 1);
$addQ(87, 8, 'In BEM methodology, how is an Element written?',
    ['.block--element', '.block__element', '.block.element', '.block-element'], 1);

// ── Quiz 9: JavaScript Fundamentals ──────────────────────────
$addQ(88, 9, 'What is the difference between === and == in JavaScript?',
    ['No difference', '=== checks value only', '=== checks both value and type', '== checks type only'], 2);
$addQ(89, 9, 'Which array method removes and returns the last element?',
    ['.shift()', '.splice()', '.pop()', '.delete()'], 2);
$addQ(90, 9, 'What is a JavaScript closure?',
    ['A function that is never called', 'A function that retains access to its outer scope variables', 'A class constructor method', 'A Promise that resolves immediately'], 1);

// ── Quiz 10: DOM Manipulation ────────────────────────────────
$addQ(91, 10, 'Which method returns a NodeList of ALL elements matching a selector?',
    ['querySelector()', 'getElementById()', 'querySelectorAll()', 'getElement()'], 2);
$addQ(92, 10, 'What does element.textContent do compared to innerHTML?',
    ['Same as innerHTML', 'Gets or sets text only — strips HTML tags', 'Applies CSS styles', 'Removes the element from the DOM'], 1);
$addQ(93, 10, 'Which event fires when the DOM is fully parsed, before images load?',
    ['window.onload', 'DOMContentLoaded', 'document.ready()', 'pageLoad'], 1);

// ── Quiz 11: Async JavaScript ─────────────────────────────────
$addQ(94, 11, 'What does Promise.all() do?',
    ['Runs promises sequentially', 'Resolves only when ALL given promises resolve', 'Resolves when the first promise resolves', 'Silently ignores rejected promises'], 1);
$addQ(95, 11, 'What happens when you use await on a non-Promise value?',
    ['Throws a TypeError', 'It is automatically wrapped in a resolved Promise', 'Returns undefined', 'The function pauses forever'], 1);
$addQ(96, 11, 'What is the correct way to handle errors in async/await?',
    ['.catch() chained to the call', 'A try/catch block wrapping the await', '.error() handler', 'onerror global handler'], 1);

// ── Quiz 12: Modern JavaScript (ES6+) ────────────────────────
$addQ(97, 12, 'What does Array.from() do?',
    ['Creates an array from an iterable or array-like object', 'Makes a deep copy of an array', 'Converts an array to a string', 'Filters array items by type'], 0);
$addQ(98, 12, 'What is the key difference between let and var?',
    ['let is global-scoped; var is block-scoped', 'let is block-scoped; var is function/global-scoped', 'There is no difference', 'let cannot be reassigned'], 1);
$addQ(99, 12, 'Which array method returns a new array of transformed items?',
    ['.filter()', '.reduce()', '.map()', '.forEach()'], 2);

echo "Done! Added 3 questions to each of the 12 quizzes (36 total, IDs 64-99).\n";

// Verify
$rows = $pdo->query('SELECT quiz_id, COUNT(*) as cnt FROM quiz_questions GROUP BY quiz_id ORDER BY quiz_id')->fetchAll(PDO::FETCH_ASSOC);
echo "\nQuestions per quiz after migration:\n";
foreach ($rows as $r) {
    echo "  Quiz {$r['quiz_id']}: {$r['cnt']} questions\n";
}
