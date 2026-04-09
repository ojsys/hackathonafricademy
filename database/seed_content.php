<?php
/**
 * HackathonAfrica LMS - Complete Course Content Seeder
 * Run after setup_enhanced.php to add comprehensive course content
 */

require_once __DIR__ . '/../config/database.php';

$pdo = db();

// Disable foreign key checks during seeding
$pdo->exec('PRAGMA foreign_keys = OFF');

echo "Seeding comprehensive course content...\n\n";

// =====================================================
// CREATE COURSES FIRST
// =====================================================
echo "Creating courses...\n";

$pdo->exec("INSERT INTO courses (id, title, description, status, order_index, estimated_hours, difficulty) VALUES 
(1, 'HTML Fundamentals', 'Learn the building blocks of the web. HTML gives structure to every page you visit online. In this beginner-friendly course, you will go from zero to confidently writing HTML documents with practical exercises and real-world examples.', 'published', 1, 8, 'beginner')");

$pdo->exec("INSERT INTO courses (id, title, description, status, order_index, estimated_hours, difficulty) VALUES 
(2, 'CSS Fundamentals', 'Transform plain HTML into beautiful, professional websites. CSS controls colors, layouts, fonts, spacing, and animations. This course takes you from basic styling to modern responsive design.', 'published', 2, 10, 'beginner')");

$pdo->exec("INSERT INTO courses (id, title, description, status, order_index, estimated_hours, difficulty) VALUES 
(3, 'JavaScript Fundamentals', 'Bring your websites to life with interactivity. JavaScript is the programming language of the web — it handles user input, animations, data processing, and much more.', 'published', 3, 15, 'beginner')");

// Create HTML Course Modules
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(1, 1, 'Introduction to the Web', 'Understand how the internet works and where HTML fits in. Perfect for absolute beginners.', 1)");

$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(2, 1, 'HTML Document Structure', 'Master the anatomy of a valid HTML document. Every tag has a purpose!', 2)");

// Create CSS Course Module
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(3, 2, 'CSS Basics', 'Learn how to add style to your HTML. Understand selectors, properties, and the cascade.', 1)");

// Create JavaScript Course Module
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(4, 3, 'JavaScript Basics', 'Your first steps into programming. Learn variables, data types, and basic operations.', 1)");

// Create Final Exams
$pdo->exec("INSERT INTO final_exams (id, course_id, title, description, pass_mark, time_limit, mcq_count, coding_count) VALUES 
(1, 1, 'HTML Final Assessment', 'Comprehensive test of your HTML knowledge including document structure, semantic elements, forms, and best practices.', 70, 45, 15, 2)");

$pdo->exec("INSERT INTO final_exams (id, course_id, title, description, pass_mark, time_limit, mcq_count, coding_count) VALUES 
(2, 2, 'CSS Final Assessment', 'Test your CSS skills including selectors, box model, flexbox, responsive design, and styling best practices.', 70, 45, 15, 2)");

$pdo->exec("INSERT INTO final_exams (id, course_id, title, description, pass_mark, time_limit, mcq_count, coding_count) VALUES 
(3, 3, 'JavaScript Final Assessment', 'Evaluate your JavaScript fundamentals including variables, functions, DOM manipulation, and problem solving.', 70, 60, 15, 3)");

// Create basic lessons for HTML Module 1 & 2
$htmlLesson1 = 'Learn how the web works, client-server model, and what HTML is.';
$pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (1, 1, 'How the Web Works', ?, 1, 15, 1)")->execute([$htmlLesson1]);

$htmlLesson2 = 'Set up VS Code and create your first HTML file.';
$pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (2, 1, 'Setting Up Your Environment', ?, 2, 20, 1)")->execute([$htmlLesson2]);

$htmlLesson3 = 'Understand DOCTYPE, html, head, and body tags.';
$pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (3, 2, 'Anatomy of an HTML Document', ?, 1, 20, 1)")->execute([$htmlLesson3]);

// Create basic lesson for CSS Module
$cssLesson1 = 'Introduction to CSS and how to add styles to HTML.';
$pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (4, 3, 'Introduction to CSS', ?, 1, 20, 1)")->execute([$cssLesson1]);

// Create basic lesson for JavaScript Module
$jsLesson1 = 'What is JavaScript and how to add it to your pages.';
$pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (5, 4, 'What is JavaScript?', ?, 1, 20, 1)")->execute([$jsLesson1]);

// Create Quizzes for each module
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (1, 1, 'Web Basics Quiz', 70)");
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (2, 2, 'HTML Structure Quiz', 70)");
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (3, 3, 'CSS Basics Quiz', 70)");
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (4, 4, 'JavaScript Basics Quiz', 70)");

// Quiz 1 Questions
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(1, 1, 'What does HTML stand for?', 1),
(2, 1, 'In the client-server model, what is the client?', 2),
(3, 1, 'Which is NOT a valid code editor?', 3)");

$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(1, 'Hyper Text Markup Language', 1),
(1, 'High Tech Modern Language', 0),
(1, 'Home Tool Markup Language', 0),
(1, 'Hyperlink and Text Markup Language', 0),
(2, 'Your web browser', 1),
(2, 'The website server', 0),
(2, 'The internet connection', 0),
(2, 'The database', 0),
(3, 'Microsoft Word', 1),
(3, 'VS Code', 0),
(3, 'Sublime Text', 0),
(3, 'Notepad++', 0)");

// Quiz 2 Questions  
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(4, 2, 'How many h1 tags should a page have?', 1),
(5, 2, 'What goes inside the head tag?', 2)");

$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(4, 'Exactly one', 1),
(4, 'As many as you want', 0),
(4, 'At least three', 0),
(4, 'None', 0),
(5, 'Metadata and title', 1),
(5, 'Visible content', 0),
(5, 'Images and links', 0),
(5, 'Paragraphs', 0)");

// Quiz 3 Questions
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(6, 3, 'What does CSS stand for?', 1),
(7, 3, 'What symbol starts a class selector?', 2)");

$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(6, 'Cascading Style Sheets', 1),
(6, 'Creative Style Sheets', 0),
(6, 'Computer Style Sheets', 0),
(6, 'Colorful Style Sheets', 0),
(7, 'A dot (.)', 1),
(7, 'A hash (#)', 0),
(7, 'An at sign (@)', 0),
(7, 'A dollar sign ($)', 0)");

// Quiz 4 Questions
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(8, 4, 'What keyword declares a variable that can change?', 1),
(9, 4, 'What does console.log() do?', 2)");

$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(8, 'let', 1),
(8, 'const', 0),
(8, 'var', 0),
(8, 'variable', 0),
(9, 'Prints to the browser console', 1),
(9, 'Shows an alert', 0),
(9, 'Writes to the page', 0),
(9, 'Creates a log file', 0)");

echo "Basic course structure created!\n\n";

// =====================================================
// HELPER FUNCTION
// =====================================================
function insertLesson($pdo, $id, $moduleId, $title, $content, $order, $minutes = 15, $videoPlaceholder = 1) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $moduleId, $title, $content, $order, $minutes, $videoPlaceholder]);
}

function insertExercise($pdo, $id, $lessonId, $title, $desc, $instructions, $starter, $solution, $hints, $type = 'html', $difficulty = 'easy', $points = 10) {
    $stmt = $pdo->prepare("INSERT INTO coding_exercises (id, lesson_id, title, description, instructions, starter_code, solution_code, hints, exercise_type, difficulty, points) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $lessonId, $title, $desc, $instructions, $starter, $solution, $hints, $type, $difficulty, $points]);
}

// =====================================================
// HTML COURSE - COMPLETE CONTENT
// =====================================================
echo "Creating HTML course content...\n";

// Module 3: Text and Typography
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(5, 1, 'Text and Typography', 'Master headings, paragraphs, text formatting, and semantic text elements. Learn to structure content professionally.', 3)");

$lesson6 = <<<'HTML'
<h2>Headings and Paragraphs</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of it like a newspaper...</h4>
    <p>A newspaper has big headlines for main stories, smaller subheadings for sections, and body text for details. HTML headings work exactly the same way — <code>&lt;h1&gt;</code> is your front-page headline, while <code>&lt;h2&gt;</code> through <code>&lt;h6&gt;</code> are progressively smaller section headers.</p>
</div>

<p>Text is the foundation of web content. Understanding how to properly structure text makes your pages readable, accessible, and SEO-friendly.</p>

<h3>The Six Heading Levels</h3>
<p>HTML provides six levels of headings. <code>&lt;h1&gt;</code> is the most important (and largest), while <code>&lt;h6&gt;</code> is the least important (and smallest).</p>

<div class="code-block">
<pre><code>&lt;!-- Heading hierarchy demonstration --&gt;
&lt;h1&gt;Main Page Title&lt;/h1&gt;        &lt;!-- Only ONE per page! --&gt;
&lt;h2&gt;Major Section&lt;/h2&gt;          &lt;!-- Like chapter titles --&gt;
&lt;h3&gt;Subsection&lt;/h3&gt;             &lt;!-- Subdivisions --&gt;
&lt;h4&gt;Minor Heading&lt;/h4&gt;          &lt;!-- Smaller topics --&gt;
&lt;h5&gt;Small Heading&lt;/h5&gt;          &lt;!-- Rarely used --&gt;
&lt;h6&gt;Smallest Heading&lt;/h6&gt;       &lt;!-- Rarely used --&gt;</code></pre>
</div>

<h3>The Golden Rule: One H1 Per Page</h3>
<p>Every page should have <strong>exactly one</strong> <code>&lt;h1&gt;</code> tag. This tells search engines and screen readers what your page is about. Think of it as the title of a book — you only have one!</p>

<div class="code-block">
<pre><code>&lt;!-- ✅ CORRECT: One h1 per page --&gt;
&lt;h1&gt;Learn Web Development&lt;/h1&gt;
&lt;h2&gt;HTML Basics&lt;/h2&gt;
&lt;h2&gt;CSS Fundamentals&lt;/h2&gt;
&lt;h2&gt;JavaScript Introduction&lt;/h2&gt;

&lt;!-- ❌ WRONG: Multiple h1 tags --&gt;
&lt;h1&gt;Welcome&lt;/h1&gt;
&lt;h1&gt;About Us&lt;/h1&gt;
&lt;h1&gt;Contact&lt;/h1&gt;</code></pre>
</div>

<h3>Paragraphs</h3>
<p>The <code>&lt;p&gt;</code> tag defines a paragraph. Browsers automatically add vertical space (margin) above and below paragraphs.</p>

<div class="code-block">
<pre><code>&lt;p&gt;This is my first paragraph. It contains several sentences 
that explain an idea. Even if you press Enter in your code, 
the text continues on the same line in the browser.&lt;/p&gt;

&lt;p&gt;This is my second paragraph. Notice how the browser 
automatically adds space between paragraphs.&lt;/p&gt;</code></pre>
</div>

<h3>Line Breaks vs Paragraphs</h3>
<p>Sometimes you need a new line without starting a whole new paragraph. Use <code>&lt;br&gt;</code> for this:</p>

<div class="code-block">
<pre><code>&lt;!-- Using line breaks for an address --&gt;
&lt;p&gt;
    HackathonAfrica Headquarters&lt;br&gt;
    123 Innovation Street&lt;br&gt;
    Lagos, Nigeria&lt;br&gt;
    Africa
&lt;/p&gt;

&lt;!-- Line breaks are also good for poetry --&gt;
&lt;p&gt;
    Roses are red,&lt;br&gt;
    Violets are blue,&lt;br&gt;
    I'm learning HTML,&lt;br&gt;
    And so can you!
&lt;/p&gt;</code></pre>
</div>

<h3>Horizontal Rules</h3>
<p>The <code>&lt;hr&gt;</code> tag creates a horizontal line, useful for separating sections of content:</p>

<div class="code-block">
<pre><code>&lt;h2&gt;Chapter 1: The Beginning&lt;/h2&gt;
&lt;p&gt;Once upon a time...&lt;/p&gt;

&lt;hr&gt;

&lt;h2&gt;Chapter 2: The Journey&lt;/h2&gt;
&lt;p&gt;Our hero set off on an adventure...&lt;/p&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Using headings for size</strong> — Don't use <code>&lt;h3&gt;</code> just because you want smaller text. Use CSS for sizing!</li>
        <li><strong>Skipping heading levels</strong> — Don't jump from <code>&lt;h1&gt;</code> to <code>&lt;h4&gt;</code>. Go in order: h1 → h2 → h3</li>
        <li><strong>Multiple h1 tags</strong> — Only ONE <code>&lt;h1&gt;</code> per page. Period.</li>
        <li><strong>Using &lt;br&gt; for spacing</strong> — If you need more space, use CSS margins, not multiple <code>&lt;br&gt;</code> tags</li>
        <li><strong>Empty paragraphs for spacing</strong> — <code>&lt;p&gt;&lt;/p&gt;</code> is wrong. Use CSS!</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use ONE <code>&lt;h1&gt;</code> per page — it's your main title</li>
        <li>Headings create a hierarchy: h1 → h2 → h3 (don't skip levels)</li>
        <li><code>&lt;p&gt;</code> creates paragraphs with automatic spacing</li>
        <li><code>&lt;br&gt;</code> creates a line break without starting a new paragraph</li>
        <li><code>&lt;hr&gt;</code> creates a horizontal dividing line</li>
    </ul>
</div>

<div class="video-placeholder">
    <i class="bi bi-play-btn"></i>
    <p>Video: "HTML Text Structure Best Practices" — Coming Soon</p>
</div>
HTML;

insertLesson($pdo, 6, 5, 'Headings and Paragraphs', $lesson6, 1, 20);

// Lesson 7: Text Formatting
$lesson7 = <<<'HTML'
<h2>Text Formatting Tags</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of it like handwriting styles...</h4>
    <p>When taking notes by hand, you might <strong>bold</strong> important terms, <em>underline</em> titles, or <s>cross out</s> mistakes. HTML gives you tags to do all of this digitally — and screen readers understand these tags to help visually impaired users!</p>
</div>

<p>HTML provides several tags for formatting text. Some are <strong>semantic</strong> (they convey meaning) while others are purely <strong>presentational</strong>.</p>

<h3>Semantic Text Tags (Use These!)</h3>
<p>These tags tell browsers AND screen readers what the text means:</p>

<div class="code-block">
<pre><code>&lt;!-- Strong importance (displays bold) --&gt;
&lt;p&gt;Warning: &lt;strong&gt;Do not drink this liquid!&lt;/strong&gt;&lt;/p&gt;

&lt;!-- Emphasis (displays italic) --&gt;
&lt;p&gt;I &lt;em&gt;really&lt;/em&gt; need to finish this project.&lt;/p&gt;

&lt;!-- Marked/highlighted text --&gt;
&lt;p&gt;The answer is &lt;mark&gt;42&lt;/mark&gt;.&lt;/p&gt;

&lt;!-- Deleted text (strikethrough) --&gt;
&lt;p&gt;Price: &lt;del&gt;$100&lt;/del&gt; $75 (25% off!)&lt;/p&gt;

&lt;!-- Inserted text (usually underlined) --&gt;
&lt;p&gt;We now offer &lt;ins&gt;free shipping&lt;/ins&gt; on all orders.&lt;/p&gt;

&lt;!-- Small print (legal text, copyright, etc.) --&gt;
&lt;p&gt;&lt;small&gt;Terms and conditions apply.&lt;/small&gt;&lt;/p&gt;</code></pre>
</div>

<h3>Scientific and Technical Text</h3>

<div class="code-block">
<pre><code>&lt;!-- Subscript (below the line) --&gt;
&lt;p&gt;Water is H&lt;sub&gt;2&lt;/sub&gt;O&lt;/p&gt;
&lt;p&gt;Carbon dioxide is CO&lt;sub&gt;2&lt;/sub&gt;&lt;/p&gt;

&lt;!-- Superscript (above the line) --&gt;
&lt;p&gt;E = mc&lt;sup&gt;2&lt;/sup&gt;&lt;/p&gt;
&lt;p&gt;The area is 100m&lt;sup&gt;2&lt;/sup&gt;&lt;/p&gt;
&lt;p&gt;Published on March 5&lt;sup&gt;th&lt;/sup&gt;&lt;/p&gt;

&lt;!-- Inline code --&gt;
&lt;p&gt;Use the &lt;code&gt;console.log()&lt;/code&gt; function to debug.&lt;/p&gt;

&lt;!-- Keyboard input --&gt;
&lt;p&gt;Press &lt;kbd&gt;Ctrl&lt;/kbd&gt; + &lt;kbd&gt;C&lt;/kbd&gt; to copy.&lt;/p&gt;

&lt;!-- Sample output --&gt;
&lt;p&gt;The program displays: &lt;samp&gt;Hello, World!&lt;/samp&gt;&lt;/p&gt;

&lt;!-- Variable names --&gt;
&lt;p&gt;The variable &lt;var&gt;x&lt;/var&gt; represents the user's age.&lt;/p&gt;</code></pre>
</div>

<h3>Quotations</h3>

<div class="code-block">
<pre><code>&lt;!-- Block quote (for longer quotes) --&gt;
&lt;blockquote&gt;
    &lt;p&gt;"The only way to do great work is to love what you do."&lt;/p&gt;
    &lt;footer&gt;— Steve Jobs&lt;/footer&gt;
&lt;/blockquote&gt;

&lt;!-- Inline quote --&gt;
&lt;p&gt;Nelson Mandela said, &lt;q&gt;Education is the most powerful weapon 
which you can use to change the world.&lt;/q&gt;&lt;/p&gt;

&lt;!-- Citation (title of a work) --&gt;
&lt;p&gt;I just finished reading &lt;cite&gt;Things Fall Apart&lt;/cite&gt; by Chinua Achebe.&lt;/p&gt;

&lt;!-- Abbreviation with full form --&gt;
&lt;p&gt;We build websites using &lt;abbr title="HyperText Markup Language"&gt;HTML&lt;/abbr&gt;.&lt;/p&gt;</code></pre>
</div>

<h3>Preformatted Text</h3>
<p>The <code>&lt;pre&gt;</code> tag preserves whitespace and line breaks exactly as written:</p>

<div class="code-block">
<pre><code>&lt;pre&gt;
    This text
        preserves     spaces
    and line breaks
        exactly as written.
&lt;/pre&gt;

&lt;!-- Great for ASCII art! --&gt;
&lt;pre&gt;
  /\_/\  
 ( o.o ) 
  &gt; ^ &lt;
&lt;/pre&gt;</code></pre>
</div>

<h3>Presentational Tags (Avoid These)</h3>
<p>These tags only change appearance without conveying meaning. Use CSS instead:</p>

<div class="code-block">
<pre><code>&lt;!-- ❌ OLD WAY (avoid) --&gt;
&lt;b&gt;Bold text&lt;/b&gt;
&lt;i&gt;Italic text&lt;/i&gt;
&lt;u&gt;Underlined text&lt;/u&gt;

&lt;!-- ✅ BETTER WAY (semantic) --&gt;
&lt;strong&gt;Important text&lt;/strong&gt;
&lt;em&gt;Emphasized text&lt;/em&gt;
&lt;span style="text-decoration: underline;"&gt;Underlined&lt;/span&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Using &lt;b&gt; instead of &lt;strong&gt;</strong> — <code>&lt;strong&gt;</code> has meaning; <code>&lt;b&gt;</code> is just visual</li>
        <li><strong>Using &lt;i&gt; instead of &lt;em&gt;</strong> — <code>&lt;em&gt;</code> means emphasis; <code>&lt;i&gt;</code> is just italic styling</li>
        <li><strong>Underlining links</strong> — Users expect underlined text to be clickable. Don't underline non-links!</li>
        <li><strong>Nesting incorrectly</strong> — Tags must close in reverse order: <code>&lt;strong&gt;&lt;em&gt;text&lt;/em&gt;&lt;/strong&gt;</code></li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use <code>&lt;strong&gt;</code> for important text, <code>&lt;em&gt;</code> for emphasis</li>
        <li><code>&lt;mark&gt;</code> highlights text, <code>&lt;del&gt;</code> shows deleted content</li>
        <li><code>&lt;sub&gt;</code> and <code>&lt;sup&gt;</code> for subscripts and superscripts</li>
        <li><code>&lt;code&gt;</code> for inline code snippets</li>
        <li><code>&lt;blockquote&gt;</code> for long quotes, <code>&lt;q&gt;</code> for short inline quotes</li>
        <li>Prefer semantic tags over presentational ones for accessibility</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 7, 5, 'Text Formatting Tags', $lesson7, 2, 25);

// Module 3 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (5, 5, 'Text and Typography Quiz', 70)");

$pdo->exec("DELETE FROM quiz_questions WHERE quiz_id = 5");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(13, 5, 'How many h1 tags should a page have?', 1),
(14, 5, 'Which tag is used for important text that should be bold?', 2),
(15, 5, 'What does the <br> tag do?', 3),
(16, 5, 'Which tag is used for chemical formulas like H2O?', 4),
(17, 5, 'What tag should you use for a long quotation?', 5)");

$pdo->exec("DELETE FROM quiz_options WHERE question_id IN (13,14,15,16,17)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(13, 'Exactly one', 1),
(13, 'As many as needed', 0),
(13, 'At least three', 0),
(13, 'None, use h2 instead', 0),
(14, '<strong>', 1),
(14, '<b>', 0),
(14, '<bold>', 0),
(14, '<important>', 0),
(15, 'Creates a line break', 1),
(15, 'Creates a new paragraph', 0),
(15, 'Makes text bold', 0),
(15, 'Creates a horizontal line', 0),
(16, '<sub> for subscript', 1),
(16, '<sup> for superscript', 0),
(16, '<small>', 0),
(16, '<down>', 0),
(17, '<blockquote>', 1),
(17, '<quote>', 0),
(17, '<q>', 0),
(17, '<longquote>', 0)");

// Code exercise for text formatting
insertExercise($pdo, 2, 7, 'Format a Recipe Card', 
'Create a properly formatted recipe card using semantic HTML tags.',
'Create an HTML snippet for a recipe that includes:
1. A main heading (h2) for the recipe name
2. A paragraph with the word "delicious" emphasized
3. An ingredient that has been crossed out (deleted) and replaced
4. The cooking temperature with a superscript degree symbol',
'<!-- Create your recipe card here -->
<h2></h2>
<p></p>
',
'<h2>Grandma\'\'s Chocolate Cake</h2>
<p>This is a <em>delicious</em> family recipe passed down for generations.</p>
<p>Ingredients: <del>2 cups sugar</del> <ins>1.5 cups sugar</ins> (healthier version)</p>
<p>Bake at 350<sup>°</sup>F for 30 minutes.</p>',
'Use <em> for emphasis|Use <del> for deleted text and <ins> for inserted|Use <sup> for the degree symbol',
'html', 'easy', 15);

// =====================================================
// MODULE 4: Links and Images
// =====================================================
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(6, 1, 'Links and Images', 'Connect pages together with hyperlinks and make your content visual with images. Essential skills for any web developer.', 4)");

$lesson8 = <<<'HTML'
<h2>Creating Hyperlinks</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of links like doors...</h4>
    <p>Every link is like a door that takes visitors somewhere else. The <code>href</code> attribute is the address where the door leads, and the link text is the sign on the door telling people where they'll go if they click it.</p>
</div>

<p>Hyperlinks are what make the "web" a web! They connect pages together, letting users navigate from one resource to another with a single click.</p>

<h3>Basic Link Syntax</h3>
<p>The anchor tag <code>&lt;a&gt;</code> creates links. The <code>href</code> attribute specifies the destination:</p>

<div class="code-block">
<pre><code>&lt;!-- Basic link structure --&gt;
&lt;a href="destination"&gt;Link Text&lt;/a&gt;

&lt;!-- Link to another website --&gt;
&lt;a href="https://www.google.com"&gt;Visit Google&lt;/a&gt;

&lt;!-- Link to a page on your site --&gt;
&lt;a href="about.html"&gt;About Us&lt;/a&gt;

&lt;!-- Link to a specific section --&gt;
&lt;a href="#contact"&gt;Jump to Contact Section&lt;/a&gt;</code></pre>
</div>

<h3>Types of URLs</h3>

<div class="code-block">
<pre><code>&lt;!-- Absolute URL (full web address) --&gt;
&lt;a href="https://www.hackathon.africa/courses"&gt;Our Courses&lt;/a&gt;

&lt;!-- Relative URL (same website) --&gt;
&lt;a href="courses.html"&gt;Courses&lt;/a&gt;           &lt;!-- Same folder --&gt;
&lt;a href="pages/about.html"&gt;About&lt;/a&gt;         &lt;!-- Subfolder --&gt;
&lt;a href="../index.html"&gt;Home&lt;/a&gt;             &lt;!-- Parent folder --&gt;

&lt;!-- Root-relative URL (from website root) --&gt;
&lt;a href="/pages/contact.html"&gt;Contact&lt;/a&gt;</code></pre>
</div>

<h3>Opening Links in New Tabs</h3>
<p>Use <code>target="_blank"</code> to open links in a new browser tab. Always add <code>rel="noopener noreferrer"</code> for security:</p>

<div class="code-block">
<pre><code>&lt;!-- Opens in new tab (secure way) --&gt;
&lt;a href="https://www.google.com" 
   target="_blank" 
   rel="noopener noreferrer"&gt;
    Visit Google (opens in new tab)
&lt;/a&gt;</code></pre>
</div>

<h3>Email and Phone Links</h3>

<div class="code-block">
<pre><code>&lt;!-- Email link (opens email client) --&gt;
&lt;a href="mailto:hello@hackathon.africa"&gt;Email Us&lt;/a&gt;

&lt;!-- Email with subject line --&gt;
&lt;a href="mailto:hello@hackathon.africa?subject=Course%20Question"&gt;
    Ask a Question
&lt;/a&gt;

&lt;!-- Phone link (opens phone dialer on mobile) --&gt;
&lt;a href="tel:+2341234567890"&gt;Call Us: +234 123 456 7890&lt;/a&gt;</code></pre>
</div>

<h3>Linking to Page Sections (Anchors)</h3>

<div class="code-block">
<pre><code>&lt;!-- Create a target section with an ID --&gt;
&lt;h2 id="about"&gt;About Us&lt;/h2&gt;
&lt;p&gt;We are HackathonAfrica...&lt;/p&gt;

&lt;h2 id="contact"&gt;Contact&lt;/h2&gt;
&lt;p&gt;Reach out to us...&lt;/p&gt;

&lt;!-- Link to those sections --&gt;
&lt;nav&gt;
    &lt;a href="#about"&gt;About&lt;/a&gt;
    &lt;a href="#contact"&gt;Contact&lt;/a&gt;
&lt;/nav&gt;

&lt;!-- Link to section on another page --&gt;
&lt;a href="about.html#team"&gt;Meet Our Team&lt;/a&gt;</code></pre>
</div>

<h3>Download Links</h3>

<div class="code-block">
<pre><code>&lt;!-- Force file download instead of opening --&gt;
&lt;a href="files/brochure.pdf" download&gt;
    Download Our Brochure (PDF)
&lt;/a&gt;

&lt;!-- Specify download filename --&gt;
&lt;a href="files/doc123.pdf" download="HackathonAfrica-Guide.pdf"&gt;
    Download Guide
&lt;/a&gt;</code></pre>
</div>

<h3>Link Accessibility</h3>

<div class="code-block">
<pre><code>&lt;!-- ❌ BAD: Meaningless link text --&gt;
&lt;p&gt;To learn more, &lt;a href="courses.html"&gt;click here&lt;/a&gt;.&lt;/p&gt;

&lt;!-- ✅ GOOD: Descriptive link text --&gt;
&lt;p&gt;&lt;a href="courses.html"&gt;View our available courses&lt;/a&gt; to start learning.&lt;/p&gt;

&lt;!-- ✅ GOOD: Add title for extra context --&gt;
&lt;a href="courses.html" title="Browse HTML, CSS, and JavaScript courses"&gt;
    Our Courses
&lt;/a&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting https://</strong> — External links need the full URL including <code>https://</code></li>
        <li><strong>"Click here" links</strong> — Screen readers read links out of context. Make link text descriptive!</li>
        <li><strong>Missing target security</strong> — Always use <code>rel="noopener noreferrer"</code> with <code>target="_blank"</code></li>
        <li><strong>Broken links</strong> — Always test your links! 404 errors frustrate users</li>
        <li><strong>Linking to non-existent IDs</strong> — Make sure the <code>#section</code> you're linking to exists</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use <code>&lt;a href="..."&gt;</code> to create links</li>
        <li>Absolute URLs for external sites, relative URLs for your own site</li>
        <li>Add <code>target="_blank"</code> to open links in new tabs</li>
        <li>Use <code>mailto:</code> for email links, <code>tel:</code> for phone links</li>
        <li>Link to page sections using <code>#id</code></li>
        <li>Write descriptive link text for accessibility</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 8, 6, 'Creating Hyperlinks', $lesson8, 1, 25);

// Lesson 9: Images
$lesson9 = <<<'HTML'
<h2>Adding Images</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of images like picture frames...</h4>
    <p>The <code>&lt;img&gt;</code> tag is like a picture frame on your wall. The <code>src</code> tells the browser where to find the picture, and the <code>alt</code> is like a label on the back describing what's in the frame — essential if someone can't see the image!</p>
</div>

<p>Images make websites engaging and help communicate ideas visually. The <code>&lt;img&gt;</code> tag is a self-closing tag (no closing tag needed).</p>

<h3>Basic Image Syntax</h3>

<div class="code-block">
<pre><code>&lt;!-- Basic image --&gt;
&lt;img src="photo.jpg" alt="Description of the image"&gt;

&lt;!-- Image from another website --&gt;
&lt;img src="https://example.com/images/logo.png" alt="Company Logo"&gt;

&lt;!-- Image from a subfolder --&gt;
&lt;img src="images/hero-banner.jpg" alt="Welcome to HackathonAfrica"&gt;</code></pre>
</div>

<h3>The Essential Attributes</h3>

<table>
    <thead>
        <tr><th>Attribute</th><th>Purpose</th><th>Required?</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><code>src</code></td>
            <td>The path/URL to the image file</td>
            <td><strong>Yes</strong></td>
        </tr>
        <tr>
            <td><code>alt</code></td>
            <td>Alternative text describing the image (for accessibility and when image fails to load)</td>
            <td><strong>Yes</strong></td>
        </tr>
        <tr>
            <td><code>width</code></td>
            <td>Width in pixels or percentage</td>
            <td>Recommended</td>
        </tr>
        <tr>
            <td><code>height</code></td>
            <td>Height in pixels or percentage</td>
            <td>Recommended</td>
        </tr>
        <tr>
            <td><code>loading</code></td>
            <td>"lazy" to delay loading until needed</td>
            <td>Optional</td>
        </tr>
    </tbody>
</table>

<h3>Writing Good Alt Text</h3>

<div class="code-block">
<pre><code>&lt;!-- ❌ BAD: No alt or unhelpful alt --&gt;
&lt;img src="team.jpg"&gt;
&lt;img src="team.jpg" alt="image"&gt;
&lt;img src="team.jpg" alt="photo.jpg"&gt;

&lt;!-- ✅ GOOD: Descriptive alt text --&gt;
&lt;img src="team.jpg" alt="The HackathonAfrica team at the 2024 Lagos hackathon"&gt;

&lt;!-- ✅ Decorative images: empty alt --&gt;
&lt;img src="decorative-border.png" alt=""&gt;

&lt;!-- ✅ Functional images (like buttons) --&gt;
&lt;img src="search-icon.png" alt="Search"&gt;</code></pre>
</div>

<h3>Setting Image Dimensions</h3>
<p>Always specify width and height to prevent layout shift while loading:</p>

<div class="code-block">
<pre><code>&lt;!-- Fixed pixel dimensions --&gt;
&lt;img src="photo.jpg" 
     alt="Portrait photo" 
     width="400" 
     height="300"&gt;

&lt;!-- Responsive: CSS handles actual sizing, attributes prevent layout shift --&gt;
&lt;img src="photo.jpg" 
     alt="Portrait photo" 
     width="800" 
     height="600"
     style="max-width: 100%; height: auto;"&gt;</code></pre>
</div>

<h3>Lazy Loading</h3>
<p>Lazy loading delays image loading until the user scrolls near them, improving page speed:</p>

<div class="code-block">
<pre><code>&lt;!-- Browser handles lazy loading --&gt;
&lt;img src="large-photo.jpg" 
     alt="Beautiful landscape" 
     loading="lazy"
     width="1200" 
     height="800"&gt;</code></pre>
</div>

<h3>Common Image Formats</h3>

<table>
    <thead>
        <tr><th>Format</th><th>Best For</th><th>Features</th></tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>JPEG/JPG</strong></td>
            <td>Photos, complex images</td>
            <td>Good compression, no transparency</td>
        </tr>
        <tr>
            <td><strong>PNG</strong></td>
            <td>Graphics, logos, screenshots</td>
            <td>Supports transparency, larger file size</td>
        </tr>
        <tr>
            <td><strong>GIF</strong></td>
            <td>Simple animations, icons</td>
            <td>Animated, limited colors (256)</td>
        </tr>
        <tr>
            <td><strong>WebP</strong></td>
            <td>Modern websites</td>
            <td>Best compression, supports transparency and animation</td>
        </tr>
        <tr>
            <td><strong>SVG</strong></td>
            <td>Icons, logos, illustrations</td>
            <td>Scalable (never pixelates), small file size</td>
        </tr>
    </tbody>
</table>

<h3>Images as Links</h3>

<div class="code-block">
<pre><code>&lt;!-- Wrap image in an anchor tag --&gt;
&lt;a href="https://www.hackathon.africa"&gt;
    &lt;img src="logo.png" alt="HackathonAfrica - Click to visit homepage"&gt;
&lt;/a&gt;</code></pre>
</div>

<h3>Figure and Figcaption</h3>
<p>For images with captions, use the semantic <code>&lt;figure&gt;</code> and <code>&lt;figcaption&gt;</code> elements:</p>

<div class="code-block">
<pre><code>&lt;figure&gt;
    &lt;img src="hackathon-2024.jpg" 
         alt="Participants coding at HackathonAfrica 2024"&gt;
    &lt;figcaption&gt;
        Over 500 developers participated in HackathonAfrica 2024
    &lt;/figcaption&gt;
&lt;/figure&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Missing alt attribute</strong> — Every image MUST have an alt attribute (even if empty for decorative images)</li>
        <li><strong>Wrong file path</strong> — Check that your image path is correct. Paths are case-sensitive!</li>
        <li><strong>Huge image files</strong> — Optimize images before uploading. A 5MB photo kills page speed!</li>
        <li><strong>Missing dimensions</strong> — Without width/height, the page "jumps" as images load</li>
        <li><strong>Broken image links</strong> — Test all images after uploading to your server</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><code>&lt;img&gt;</code> is a self-closing tag</li>
        <li><code>src</code> and <code>alt</code> are required attributes</li>
        <li>Always write meaningful alt text for accessibility</li>
        <li>Specify width and height to prevent layout shift</li>
        <li>Use <code>loading="lazy"</code> for images below the fold</li>
        <li>Choose the right format: JPEG for photos, PNG for transparency, SVG for icons</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 9, 6, 'Adding Images', $lesson9, 2, 25);

// Code exercise for images
insertExercise($pdo, 3, 9, 'Create an Image Gallery', 
'Build a simple image gallery with proper accessibility.',
'Create a gallery section with:
1. A heading "Our Gallery"
2. Three images using placeholder URLs (use https://via.placeholder.com/300x200)
3. Each image must have descriptive alt text
4. Wrap one image in a link
5. Use figure/figcaption for at least one image',
'<!-- Create your image gallery here -->
<section>
    
</section>',
'<section>
    <h2>Our Gallery</h2>
    
    <img src="https://via.placeholder.com/300x200" alt="Sunset over the ocean" width="300" height="200">
    
    <a href="https://example.com">
        <img src="https://via.placeholder.com/300x200" alt="Mountain landscape - click to learn more" width="300" height="200">
    </a>
    
    <figure>
        <img src="https://via.placeholder.com/300x200" alt="City skyline at night" width="300" height="200">
        <figcaption>Beautiful city lights at midnight</figcaption>
    </figure>
</section>',
'Use h2 for the section heading|Remember alt text for every image|Wrap the anchor tag around the img tag|figure contains both img and figcaption',
'html', 'medium', 20);

// Module 4 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (6, 6, 'Links and Images Quiz', 70)");

$pdo->exec("DELETE FROM quiz_questions WHERE quiz_id = 6");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(18, 6, 'What attribute specifies where a link goes?', 1),
(19, 6, 'How do you open a link in a new tab?', 2),
(20, 6, 'Which attribute is required on every image?', 3),
(21, 6, 'What does the alt attribute do?', 4),
(22, 6, 'Which image format is best for photographs?', 5)");

$pdo->exec("DELETE FROM quiz_options WHERE question_id IN (18,19,20,21,22)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(18, 'href', 1),
(18, 'src', 0),
(18, 'link', 0),
(18, 'url', 0),
(19, 'target=\"_blank\"', 1),
(19, 'new=\"true\"', 0),
(19, 'open=\"tab\"', 0),
(19, 'window=\"new\"', 0),
(20, 'alt', 1),
(20, 'title', 0),
(20, 'name', 0),
(20, 'description', 0),
(21, 'Provides alternative text description for accessibility', 1),
(21, 'Changes the image color', 0),
(21, 'Makes the image larger', 0),
(21, 'Adds a border to the image', 0),
(22, 'JPEG/JPG', 1),
(22, 'PNG', 0),
(22, 'GIF', 0),
(22, 'SVG', 0)");

// =====================================================
// MODULE 5: Lists and Tables
// =====================================================
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(7, 1, 'Lists and Tables', 'Organize information effectively using ordered lists, unordered lists, and data tables.', 5)");

$lesson10 = <<<'HTML'
<h2>Creating Lists</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of lists like shopping or to-do lists...</h4>
    <p>When you write a shopping list, you don't care about order — you just need milk, bread, and eggs. That's an <strong>unordered list</strong>. But a recipe with steps needs to be followed in order — that's an <strong>ordered list</strong>. HTML gives you both!</p>
</div>

<h3>Unordered Lists (Bullet Points)</h3>
<p>Use <code>&lt;ul&gt;</code> when the order doesn't matter:</p>

<div class="code-block">
<pre><code>&lt;h3&gt;Skills I'm Learning&lt;/h3&gt;
&lt;ul&gt;
    &lt;li&gt;HTML&lt;/li&gt;
    &lt;li&gt;CSS&lt;/li&gt;
    &lt;li&gt;JavaScript&lt;/li&gt;
    &lt;li&gt;React&lt;/li&gt;
&lt;/ul&gt;</code></pre>
</div>

<h3>Ordered Lists (Numbered)</h3>
<p>Use <code>&lt;ol&gt;</code> when sequence matters:</p>

<div class="code-block">
<pre><code>&lt;h3&gt;How to Make Jollof Rice&lt;/h3&gt;
&lt;ol&gt;
    &lt;li&gt;Blend tomatoes, peppers, and onions&lt;/li&gt;
    &lt;li&gt;Fry the blended mixture in oil&lt;/li&gt;
    &lt;li&gt;Add seasoning and spices&lt;/li&gt;
    &lt;li&gt;Add washed rice and water&lt;/li&gt;
    &lt;li&gt;Cook until rice is done&lt;/li&gt;
    &lt;li&gt;Serve hot and enjoy!&lt;/li&gt;
&lt;/ol&gt;</code></pre>
</div>

<h3>Ordered List Options</h3>

<div class="code-block">
<pre><code>&lt;!-- Start from a different number --&gt;
&lt;ol start="5"&gt;
    &lt;li&gt;Fifth item&lt;/li&gt;
    &lt;li&gt;Sixth item&lt;/li&gt;
&lt;/ol&gt;

&lt;!-- Count backwards --&gt;
&lt;ol reversed&gt;
    &lt;li&gt;Third place&lt;/li&gt;
    &lt;li&gt;Second place&lt;/li&gt;
    &lt;li&gt;First place!&lt;/li&gt;
&lt;/ol&gt;

&lt;!-- Different numbering styles --&gt;
&lt;ol type="A"&gt; &lt;!-- A, B, C --&gt;
&lt;ol type="a"&gt; &lt;!-- a, b, c --&gt;
&lt;ol type="I"&gt; &lt;!-- I, II, III --&gt;
&lt;ol type="i"&gt; &lt;!-- i, ii, iii --&gt;</code></pre>
</div>

<h3>Nested Lists</h3>
<p>Lists can contain other lists:</p>

<div class="code-block">
<pre><code>&lt;h3&gt;Web Development Skills&lt;/h3&gt;
&lt;ul&gt;
    &lt;li&gt;Frontend
        &lt;ul&gt;
            &lt;li&gt;HTML&lt;/li&gt;
            &lt;li&gt;CSS&lt;/li&gt;
            &lt;li&gt;JavaScript&lt;/li&gt;
        &lt;/ul&gt;
    &lt;/li&gt;
    &lt;li&gt;Backend
        &lt;ul&gt;
            &lt;li&gt;Python&lt;/li&gt;
            &lt;li&gt;Node.js&lt;/li&gt;
            &lt;li&gt;PHP&lt;/li&gt;
        &lt;/ul&gt;
    &lt;/li&gt;
    &lt;li&gt;Database
        &lt;ul&gt;
            &lt;li&gt;MySQL&lt;/li&gt;
            &lt;li&gt;MongoDB&lt;/li&gt;
        &lt;/ul&gt;
    &lt;/li&gt;
&lt;/ul&gt;</code></pre>
</div>

<h3>Description Lists</h3>
<p>Use <code>&lt;dl&gt;</code> for term-definition pairs:</p>

<div class="code-block">
<pre><code>&lt;h3&gt;Glossary&lt;/h3&gt;
&lt;dl&gt;
    &lt;dt&gt;HTML&lt;/dt&gt;
    &lt;dd&gt;HyperText Markup Language - the structure of web pages&lt;/dd&gt;
    
    &lt;dt&gt;CSS&lt;/dt&gt;
    &lt;dd&gt;Cascading Style Sheets - the styling of web pages&lt;/dd&gt;
    
    &lt;dt&gt;JavaScript&lt;/dt&gt;
    &lt;dd&gt;A programming language for web interactivity&lt;/dd&gt;
&lt;/dl&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Putting text directly in ul/ol</strong> — Only <code>&lt;li&gt;</code> elements go directly inside lists</li>
        <li><strong>Using lists for layout</strong> — Lists are for content, not page layout. Use CSS for layout!</li>
        <li><strong>Forgetting closing tags</strong> — Always close your <code>&lt;li&gt;</code>, <code>&lt;ul&gt;</code>, and <code>&lt;ol&gt;</code> tags</li>
        <li><strong>Using ordered lists when order doesn't matter</strong> — If sequence isn't important, use <code>&lt;ul&gt;</code></li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><code>&lt;ul&gt;</code> for unordered (bullet) lists</li>
        <li><code>&lt;ol&gt;</code> for ordered (numbered) lists</li>
        <li><code>&lt;li&gt;</code> for list items (goes inside ul or ol)</li>
        <li>Lists can be nested inside other lists</li>
        <li><code>&lt;dl&gt;</code>, <code>&lt;dt&gt;</code>, <code>&lt;dd&gt;</code> for definition/description lists</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 10, 7, 'Creating Lists', $lesson10, 1, 20);

// Lesson 11: Tables
$lesson11 = <<<'HTML'
<h2>Creating Tables</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of tables like spreadsheets...</h4>
    <p>Just like Excel or Google Sheets, HTML tables organize data into rows and columns. The <code>&lt;table&gt;</code> is the spreadsheet, <code>&lt;tr&gt;</code> is each row, and <code>&lt;td&gt;</code> is each cell. <code>&lt;th&gt;</code> is like the header row with bold column names.</p>
</div>

<p>Tables are perfect for displaying structured data like schedules, prices, statistics, or comparisons. <strong>Important:</strong> Tables should only be used for tabular data, NOT for page layout!</p>

<h3>Basic Table Structure</h3>

<div class="code-block">
<pre><code>&lt;table&gt;
    &lt;tr&gt;
        &lt;th&gt;Name&lt;/th&gt;
        &lt;th&gt;Country&lt;/th&gt;
        &lt;th&gt;Score&lt;/th&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
        &lt;td&gt;Amara&lt;/td&gt;
        &lt;td&gt;Nigeria&lt;/td&gt;
        &lt;td&gt;95&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
        &lt;td&gt;Kwame&lt;/td&gt;
        &lt;td&gt;Ghana&lt;/td&gt;
        &lt;td&gt;88&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
        &lt;td&gt;Fatima&lt;/td&gt;
        &lt;td&gt;Kenya&lt;/td&gt;
        &lt;td&gt;92&lt;/td&gt;
    &lt;/tr&gt;
&lt;/table&gt;</code></pre>
</div>

<h3>Table Elements Explained</h3>

<table>
    <thead>
        <tr><th>Element</th><th>Meaning</th><th>Purpose</th></tr>
    </thead>
    <tbody>
        <tr><td><code>&lt;table&gt;</code></td><td>Table</td><td>Container for the entire table</td></tr>
        <tr><td><code>&lt;tr&gt;</code></td><td>Table Row</td><td>A horizontal row of cells</td></tr>
        <tr><td><code>&lt;th&gt;</code></td><td>Table Header</td><td>Header cell (bold, centered by default)</td></tr>
        <tr><td><code>&lt;td&gt;</code></td><td>Table Data</td><td>Regular data cell</td></tr>
        <tr><td><code>&lt;thead&gt;</code></td><td>Table Head</td><td>Groups header rows</td></tr>
        <tr><td><code>&lt;tbody&gt;</code></td><td>Table Body</td><td>Groups body content</td></tr>
        <tr><td><code>&lt;tfoot&gt;</code></td><td>Table Footer</td><td>Groups footer rows</td></tr>
        <tr><td><code>&lt;caption&gt;</code></td><td>Caption</td><td>Table title/description</td></tr>
    </tbody>
</table>

<h3>Complete Semantic Table</h3>

<div class="code-block">
<pre><code>&lt;table&gt;
    &lt;caption&gt;HackathonAfrica 2024 Leaderboard&lt;/caption&gt;
    
    &lt;thead&gt;
        &lt;tr&gt;
            &lt;th&gt;Rank&lt;/th&gt;
            &lt;th&gt;Team&lt;/th&gt;
            &lt;th&gt;Country&lt;/th&gt;
            &lt;th&gt;Score&lt;/th&gt;
        &lt;/tr&gt;
    &lt;/thead&gt;
    
    &lt;tbody&gt;
        &lt;tr&gt;
            &lt;td&gt;1&lt;/td&gt;
            &lt;td&gt;Tech Titans&lt;/td&gt;
            &lt;td&gt;Nigeria&lt;/td&gt;
            &lt;td&gt;980&lt;/td&gt;
        &lt;/tr&gt;
        &lt;tr&gt;
            &lt;td&gt;2&lt;/td&gt;
            &lt;td&gt;Code Warriors&lt;/td&gt;
            &lt;td&gt;Kenya&lt;/td&gt;
            &lt;td&gt;945&lt;/td&gt;
        &lt;/tr&gt;
        &lt;tr&gt;
            &lt;td&gt;3&lt;/td&gt;
            &lt;td&gt;Binary Stars&lt;/td&gt;
            &lt;td&gt;South Africa&lt;/td&gt;
            &lt;td&gt;920&lt;/td&gt;
        &lt;/tr&gt;
    &lt;/tbody&gt;
    
    &lt;tfoot&gt;
        &lt;tr&gt;
            &lt;td colspan="3"&gt;Total Participants&lt;/td&gt;
            &lt;td&gt;150 teams&lt;/td&gt;
        &lt;/tr&gt;
    &lt;/tfoot&gt;
&lt;/table&gt;</code></pre>
</div>

<h3>Spanning Rows and Columns</h3>

<div class="code-block">
<pre><code>&lt;!-- Colspan: cell spans multiple columns --&gt;
&lt;tr&gt;
    &lt;td colspan="3"&gt;This cell spans 3 columns&lt;/td&gt;
&lt;/tr&gt;

&lt;!-- Rowspan: cell spans multiple rows --&gt;
&lt;tr&gt;
    &lt;td rowspan="2"&gt;This cell spans 2 rows&lt;/td&gt;
    &lt;td&gt;Row 1, Column 2&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
    &lt;td&gt;Row 2, Column 2&lt;/td&gt;
&lt;/tr&gt;</code></pre>
</div>

<h3>Table Accessibility</h3>

<div class="code-block">
<pre><code>&lt;!-- Use scope to associate headers with data --&gt;
&lt;table&gt;
    &lt;tr&gt;
        &lt;th scope="col"&gt;Name&lt;/th&gt;
        &lt;th scope="col"&gt;Age&lt;/th&gt;
        &lt;th scope="col"&gt;City&lt;/th&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
        &lt;th scope="row"&gt;Amara&lt;/th&gt;
        &lt;td&gt;25&lt;/td&gt;
        &lt;td&gt;Lagos&lt;/td&gt;
    &lt;/tr&gt;
&lt;/table&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Using tables for layout</strong> — Tables are for DATA only, not for arranging page elements. Use CSS!</li>
        <li><strong>Forgetting thead/tbody</strong> — While optional, they improve accessibility and enable better styling</li>
        <li><strong>Mismatched columns</strong> — Every row should have the same number of cells (accounting for colspan)</li>
        <li><strong>No table headers</strong> — Always use <code>&lt;th&gt;</code> for column/row headers</li>
        <li><strong>Missing caption</strong> — Add a <code>&lt;caption&gt;</code> to describe what the table contains</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><code>&lt;table&gt;</code> contains the entire table</li>
        <li><code>&lt;tr&gt;</code> creates rows, <code>&lt;th&gt;</code> for headers, <code>&lt;td&gt;</code> for data</li>
        <li>Use <code>&lt;thead&gt;</code>, <code>&lt;tbody&gt;</code>, <code>&lt;tfoot&gt;</code> for semantic structure</li>
        <li><code>colspan</code> and <code>rowspan</code> let cells span multiple columns/rows</li>
        <li>Add <code>&lt;caption&gt;</code> and <code>scope</code> attributes for accessibility</li>
        <li><strong>Never</strong> use tables for page layout — CSS only!</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 11, 7, 'Creating Tables', $lesson11, 2, 25);

// Module 5 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (7, 7, 'Lists and Tables Quiz', 70)");

$pdo->exec("DELETE FROM quiz_questions WHERE quiz_id = 7");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(23, 7, 'Which tag creates an unordered (bullet) list?', 1),
(24, 7, 'What element goes directly inside <ul> or <ol>?', 2),
(25, 7, 'What does <th> represent in a table?', 3),
(26, 7, 'How do you make a cell span 2 columns?', 4)");

$pdo->exec("DELETE FROM quiz_options WHERE question_id IN (23,24,25,26)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(23, '<ul>', 1),
(23, '<ol>', 0),
(23, '<list>', 0),
(23, '<bullet>', 0),
(24, '<li>', 1),
(24, '<item>', 0),
(24, '<list-item>', 0),
(24, '<td>', 0),
(25, 'Table header cell', 1),
(25, 'Table height', 0),
(25, 'Table horizontal', 0),
(25, 'Table text', 0),
(26, 'colspan=\"2\"', 1),
(26, 'span=\"2\"', 0),
(26, 'columns=\"2\"', 0),
(26, 'merge=\"2\"', 0)");

echo "HTML course content complete!\n\n";

// =====================================================
// CSS COURSE - COMPLETE CONTENT
// =====================================================
echo "Creating CSS course content...\n";

// Already have Module 3 (CSS Basics) with 1 lesson, add more
$lesson12 = <<<'HTML'
<h2>CSS Selectors</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of selectors like addresses...</h4>
    <p>Selectors are how CSS finds HTML elements to style. It's like addressing a letter — you can address it to "Everyone in Lagos" (element selector), "The person at 123 Main St" (ID selector), or "All doctors" (class selector).</p>
</div>

<p>Selectors are the foundation of CSS. They determine which HTML elements your styles will affect.</p>

<h3>Element Selectors</h3>
<p>Target all instances of an HTML element:</p>

<div class="code-block">
<pre><code>/* All paragraphs */
p {
    color: #333;
    line-height: 1.6;
}

/* All headings */
h1 {
    font-size: 2.5rem;
    color: #00FF66;
}

/* All links */
a {
    color: blue;
    text-decoration: none;
}</code></pre>
</div>

<h3>Class Selectors</h3>
<p>Target elements with a specific class (starts with a dot):</p>

<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;p class="intro"&gt;This is an intro paragraph.&lt;/p&gt;
&lt;p class="highlight"&gt;This is highlighted.&lt;/p&gt;
&lt;p&gt;This is a regular paragraph.&lt;/p&gt;

/* CSS */
.intro {
    font-size: 1.25rem;
    font-weight: bold;
}

.highlight {
    background-color: yellow;
    padding: 0.5rem;
}</code></pre>
</div>

<h3>ID Selectors</h3>
<p>Target ONE specific element (starts with #). IDs must be unique!</p>

<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;header id="main-header"&gt;...&lt;/header&gt;
&lt;nav id="primary-nav"&gt;...&lt;/nav&gt;

/* CSS */
#main-header {
    background: #0A0A0A;
    padding: 1rem;
}

#primary-nav {
    display: flex;
    gap: 1rem;
}</code></pre>
</div>

<h3>Combining Selectors</h3>

<div class="code-block">
<pre><code>/* Multiple selectors (comma = OR) */
h1, h2, h3 {
    font-family: 'Outfit', sans-serif;
}

/* Element with class */
p.intro {
    font-size: 1.2rem;
}

/* Multiple classes */
.btn.primary {
    background: #00FF66;
}

/* Descendant selector (space = inside) */
nav a {
    color: white;
}

/* Direct child (>) */
ul > li {
    margin-bottom: 0.5rem;
}

/* Adjacent sibling (+) */
h2 + p {
    margin-top: 0;
}</code></pre>
</div>

<h3>Attribute Selectors</h3>

<div class="code-block">
<pre><code>/* Has attribute */
[disabled] {
    opacity: 0.5;
}

/* Exact value */
[type="email"] {
    border-color: blue;
}

/* Starts with */
[href^="https"] {
    color: green;
}

/* Ends with */
[href$=".pdf"] {
    color: red;
}

/* Contains */
[class*="btn"] {
    cursor: pointer;
}</code></pre>
</div>

<h3>Pseudo-classes</h3>

<div class="code-block">
<pre><code>/* Mouse states */
a:hover {
    color: #00FF66;
}

a:active {
    color: #00CC52;
}

/* Focus (keyboard navigation) */
input:focus {
    border-color: #00FF66;
    outline: none;
}

/* First/last child */
li:first-child {
    font-weight: bold;
}

li:last-child {
    border-bottom: none;
}

/* Nth child */
tr:nth-child(even) {
    background: #f5f5f5;
}

tr:nth-child(odd) {
    background: white;
}</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Using IDs for styling</strong> — IDs are too specific. Use classes for styling, IDs for JavaScript</li>
        <li><strong>Forgetting the dot/hash</strong> — <code>.class</code> needs a dot, <code>#id</code> needs a hash</li>
        <li><strong>Over-specific selectors</strong> — <code>div.container ul.nav li a</code> is too long. Simplify!</li>
        <li><strong>Duplicate IDs</strong> — Each ID must be unique on the page</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Element selectors: <code>p</code>, <code>h1</code>, <code>a</code></li>
        <li>Class selectors: <code>.classname</code> (most common for styling)</li>
        <li>ID selectors: <code>#idname</code> (use sparingly)</li>
        <li>Combine with comma (,), space, or ></li>
        <li>Pseudo-classes like <code>:hover</code>, <code>:first-child</code> add interactivity</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 12, 3, 'CSS Selectors', $lesson12, 2, 25);

// Module 4: Box Model
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(8, 2, 'The Box Model', 'Understand how every HTML element is a box with content, padding, border, and margin.', 2)");

$lesson13 = <<<'HTML'
<h2>Understanding the Box Model</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of it like a picture frame...</h4>
    <p>Every HTML element is like a framed picture. The <strong>content</strong> is the picture itself. The <strong>padding</strong> is the matting around the picture. The <strong>border</strong> is the actual frame. The <strong>margin</strong> is the space between frames on your wall.</p>
</div>

<p>The CSS box model is fundamental to understanding layout. Every element on a page is a rectangular box with four areas: content, padding, border, and margin.</p>

<h3>The Four Parts</h3>

<div class="code-block">
<pre><code>.box {
    /* Content: The actual text/images inside */
    width: 300px;
    height: 200px;
    
    /* Padding: Space INSIDE the border */
    padding: 20px;
    
    /* Border: The line around the element */
    border: 2px solid #00FF66;
    
    /* Margin: Space OUTSIDE the border */
    margin: 30px;
}</code></pre>
</div>

<h3>Padding Properties</h3>

<div class="code-block">
<pre><code>/* Individual sides */
padding-top: 10px;
padding-right: 20px;
padding-bottom: 10px;
padding-left: 20px;

/* Shorthand: all sides */
padding: 20px;

/* Shorthand: vertical | horizontal */
padding: 10px 20px;

/* Shorthand: top | horizontal | bottom */
padding: 10px 20px 30px;

/* Shorthand: top | right | bottom | left (clockwise) */
padding: 10px 20px 30px 40px;</code></pre>
</div>

<h3>Margin Properties</h3>

<div class="code-block">
<pre><code>/* Same shorthand patterns as padding */
margin: 20px;                  /* All sides */
margin: 10px 20px;             /* Vertical | Horizontal */
margin: 10px 20px 30px 40px;   /* Top Right Bottom Left */

/* Auto margins for centering */
.centered-box {
    width: 600px;
    margin: 0 auto;  /* Centers horizontally */
}

/* Negative margins (use carefully!) */
margin-top: -20px;</code></pre>
</div>

<h3>Border Properties</h3>

<div class="code-block">
<pre><code>/* Full border shorthand */
border: 2px solid #00FF66;

/* Individual properties */
border-width: 2px;
border-style: solid;  /* solid, dashed, dotted, double, none */
border-color: #00FF66;

/* Individual sides */
border-top: 3px dashed red;
border-bottom: 1px solid #ccc;

/* Border radius (rounded corners) */
border-radius: 8px;           /* All corners */
border-radius: 50%;           /* Perfect circle */
border-radius: 10px 0 10px 0; /* Alternating corners */</code></pre>
</div>

<h3>Box Sizing</h3>
<p>By default, width/height only set the content size. Padding and border add to the total size!</p>

<div class="code-block">
<pre><code>/* Default behavior (content-box) */
.box {
    width: 300px;
    padding: 20px;
    border: 5px solid black;
    /* Total width: 300 + 20 + 20 + 5 + 5 = 350px! */
}

/* Better: include padding and border in width */
.box {
    box-sizing: border-box;
    width: 300px;
    padding: 20px;
    border: 5px solid black;
    /* Total width: exactly 300px */
}

/* Apply to all elements (recommended!) */
*, *::before, *::after {
    box-sizing: border-box;
}</code></pre>
</div>

<h3>Margin Collapse</h3>
<p>Vertical margins between elements collapse (only the larger one applies):</p>

<div class="code-block">
<pre><code>&lt;style&gt;
    .box1 { margin-bottom: 30px; }
    .box2 { margin-top: 20px; }
    /* The gap between them is 30px, NOT 50px! */
&lt;/style&gt;

&lt;div class="box1"&gt;Box 1&lt;/div&gt;
&lt;div class="box2"&gt;Box 2&lt;/div&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting box-sizing: border-box</strong> — Always add this to your CSS reset</li>
        <li><strong>Confusing padding and margin</strong> — Padding is inside, margin is outside</li>
        <li><strong>Not understanding margin collapse</strong> — Vertical margins don't stack, they overlap</li>
        <li><strong>Using pixels for everything</strong> — Consider rem/em for better scalability</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Every element is a box: content + padding + border + margin</li>
        <li>Padding is space inside the border</li>
        <li>Margin is space outside the border</li>
        <li>Use <code>box-sizing: border-box</code> for predictable sizing</li>
        <li>Margins collapse vertically</li>
        <li><code>margin: 0 auto</code> centers block elements horizontally</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 13, 8, 'Understanding the Box Model', $lesson13, 1, 25);

// CSS exercise
insertExercise($pdo, 4, 13, 'Style a Card Component', 
'Create a styled card using box model properties.',
'Create CSS for a card that has:
1. Fixed width of 300px
2. Padding of 20px on all sides
3. A 2px solid border with color #27272A
4. Border radius of 8px
5. Margin of 16px on all sides
6. Use box-sizing: border-box',
'.card {
    /* Add your styles here */
    
}',
'.card {
    box-sizing: border-box;
    width: 300px;
    padding: 20px;
    border: 2px solid #27272A;
    border-radius: 8px;
    margin: 16px;
}',
'Start with box-sizing: border-box|Width comes before padding|Border needs width, style, and color|Border-radius rounds the corners',
'css', 'easy', 15);

// Module 4 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (8, 8, 'Box Model Quiz', 70)");

$pdo->exec("DELETE FROM quiz_questions WHERE quiz_id = 8");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(27, 8, 'Which property creates space INSIDE an element''s border?', 1),
(28, 8, 'Which property creates space OUTSIDE an element''s border?', 2),
(29, 8, 'What does box-sizing: border-box do?', 3),
(30, 8, 'How do you center a block element horizontally?', 4)");

$pdo->exec("DELETE FROM quiz_options WHERE question_id IN (27,28,29,30)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(27, 'padding', 1),
(27, 'margin', 0),
(27, 'border', 0),
(27, 'spacing', 0),
(28, 'margin', 1),
(28, 'padding', 0),
(28, 'border', 0),
(28, 'gap', 0),
(29, 'Includes padding and border in the element''s total width/height', 1),
(29, 'Removes the border from the element', 0),
(29, 'Sets the element to a fixed size', 0),
(29, 'Adds a box shadow', 0),
(30, 'margin: 0 auto', 1),
(30, 'text-align: center', 0),
(30, 'align: center', 0),
(30, 'position: center', 0)");

// Module 5: Flexbox
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(9, 2, 'Flexbox Layout', 'Master the flexible box layout for creating responsive, one-dimensional layouts.', 3)");

$lesson14 = <<<'HTML'
<h2>Introduction to Flexbox</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of flexbox like arranging books on a shelf...</h4>
    <p>The shelf is the <strong>flex container</strong> and the books are <strong>flex items</strong>. You can decide how to arrange them: spread out evenly, push them to one side, stack them vertically, or even change their order — all without touching the books individually!</p>
</div>

<p>Flexbox is a CSS layout method that makes it easy to align and distribute space among items in a container, even when their sizes are unknown.</p>

<h3>Creating a Flex Container</h3>

<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;div class="container"&gt;
    &lt;div class="item"&gt;1&lt;/div&gt;
    &lt;div class="item"&gt;2&lt;/div&gt;
    &lt;div class="item"&gt;3&lt;/div&gt;
&lt;/div&gt;

/* CSS */
.container {
    display: flex;  /* This makes it a flex container! */
}</code></pre>
</div>

<h3>Main Axis vs Cross Axis</h3>
<p>Flexbox works along two axes:</p>
<ul>
    <li><strong>Main axis</strong> — The primary direction (horizontal by default)</li>
    <li><strong>Cross axis</strong> — Perpendicular to main (vertical by default)</li>
</ul>

<h3>Flex Direction</h3>

<div class="code-block">
<pre><code>/* Default: items in a row (left to right) */
.container {
    display: flex;
    flex-direction: row;
}

/* Reverse row (right to left) */
flex-direction: row-reverse;

/* Column (top to bottom) */
flex-direction: column;

/* Column reverse (bottom to top) */
flex-direction: column-reverse;</code></pre>
</div>

<h3>Justify Content (Main Axis)</h3>

<div class="code-block">
<pre><code>.container {
    display: flex;
    justify-content: flex-start;   /* Default: items at start */
    justify-content: flex-end;     /* Items at end */
    justify-content: center;       /* Items centered */
    justify-content: space-between; /* First/last at edges, equal space between */
    justify-content: space-around;  /* Equal space around each item */
    justify-content: space-evenly;  /* Truly equal spacing */
}</code></pre>
</div>

<h3>Align Items (Cross Axis)</h3>

<div class="code-block">
<pre><code>.container {
    display: flex;
    height: 200px; /* Need height to see effect */
    align-items: stretch;    /* Default: items fill container height */
    align-items: flex-start; /* Items at top */
    align-items: flex-end;   /* Items at bottom */
    align-items: center;     /* Items centered vertically */
    align-items: baseline;   /* Items aligned by text baseline */
}</code></pre>
</div>

<h3>Perfect Centering</h3>
<p>Centering both horizontally and vertically in ONE line:</p>

<div class="code-block">
<pre><code>.container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh; /* Full viewport height */
}

/* Even shorter with place-items (newer) */
.container {
    display: flex;
    place-items: center;
}</code></pre>
</div>

<h3>Flex Wrap</h3>

<div class="code-block">
<pre><code>/* Default: all items on one line (may overflow) */
flex-wrap: nowrap;

/* Wrap to multiple lines */
flex-wrap: wrap;

/* Wrap in reverse */
flex-wrap: wrap-reverse;</code></pre>
</div>

<h3>Gap (Spacing Between Items)</h3>

<div class="code-block">
<pre><code>.container {
    display: flex;
    gap: 20px;           /* Both row and column gap */
    gap: 20px 10px;      /* Row gap | Column gap */
    row-gap: 20px;       /* Space between rows */
    column-gap: 10px;    /* Space between columns */
}</code></pre>
</div>

<h3>Flex Item Properties</h3>

<div class="code-block">
<pre><code>/* flex-grow: How much item should grow */
.item {
    flex-grow: 1;  /* All items grow equally */
}
.item:first-child {
    flex-grow: 2;  /* First item grows twice as much */
}

/* flex-shrink: How much item should shrink */
.item {
    flex-shrink: 0;  /* Don't shrink */
}

/* flex-basis: Initial size before growing/shrinking */
.item {
    flex-basis: 200px;
}

/* Shorthand: flex: grow shrink basis */
.item {
    flex: 1;          /* Grow equally, shrink, basis 0 */
    flex: 0 0 200px;  /* Don't grow/shrink, fixed 200px */
}</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Applying flex properties to wrong element</strong> — Container properties go on parent, item properties go on children</li>
        <li><strong>Forgetting display: flex</strong> — Nothing works without this on the container</li>
        <li><strong>Confusing justify vs align</strong> — Justify = main axis, Align = cross axis</li>
        <li><strong>Not setting height for vertical centering</strong> — The container needs height to center vertically</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><code>display: flex</code> creates a flex container</li>
        <li><code>justify-content</code> aligns items on the main axis</li>
        <li><code>align-items</code> aligns items on the cross axis</li>
        <li><code>flex-direction</code> changes the main axis direction</li>
        <li><code>gap</code> creates space between items</li>
        <li><code>flex: 1</code> makes items grow to fill space equally</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 14, 9, 'Introduction to Flexbox', $lesson14, 1, 30);

// Flexbox exercise
insertExercise($pdo, 5, 14, 'Create a Navigation Bar', 
'Build a horizontal navigation bar using Flexbox.',
'Create CSS for a navigation that:
1. Uses display: flex
2. Has space-between to push logo left and links right
3. Centers items vertically
4. Has a gap of 20px between nav links
5. Has padding of 16px',
'.navbar {
    /* Add your flexbox styles */
    
}',
'.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    gap: 20px;
}',
'Start with display: flex|Use justify-content: space-between|Use align-items: center for vertical centering|Gap creates space between flex items',
'css', 'medium', 20);

// Module 5 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (9, 9, 'Flexbox Quiz', 70)");

$pdo->exec("DELETE FROM quiz_questions WHERE quiz_id = 9");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(31, 9, 'What CSS property creates a flex container?', 1),
(32, 9, 'Which property aligns items along the main axis?', 2),
(33, 9, 'Which property aligns items along the cross axis?', 3),
(34, 9, 'How do you make items wrap to multiple lines?', 4)");

$pdo->exec("DELETE FROM quiz_options WHERE question_id IN (31,32,33,34)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(31, 'display: flex', 1),
(31, 'display: flexbox', 0),
(31, 'flex: true', 0),
(31, 'position: flex', 0),
(32, 'justify-content', 1),
(32, 'align-items', 0),
(32, 'align-content', 0),
(32, 'flex-align', 0),
(33, 'align-items', 1),
(33, 'justify-content', 0),
(33, 'vertical-align', 0),
(33, 'align-self', 0),
(34, 'flex-wrap: wrap', 1),
(34, 'flex-flow: wrap', 0),
(34, 'overflow: wrap', 0),
(34, 'wrap: true', 0)");

echo "CSS course content complete!\n\n";

// =====================================================
// JAVASCRIPT COURSE - COMPLETE CONTENT
// =====================================================
echo "Creating JavaScript course content...\n";

// Update Module 4 with more lessons
$lesson15 = <<<'HTML'
<h2>Variables and Data Types</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of variables like labeled boxes...</h4>
    <p>Imagine you have storage boxes. One box is labeled "name" and contains "Amara". Another is labeled "age" and contains 25. You can look inside any box, change its contents, or use what's inside. Variables work exactly like this — they're named containers for data!</p>
</div>

<p>Variables are the building blocks of programming. They store data that your program can use and modify.</p>

<h3>Declaring Variables</h3>

<div class="code-block">
<pre><code>// let - for values that will change
let score = 0;
score = 10;      // ✅ Can be updated
score = score + 5; // Now it's 15

// const - for values that won't change
const PI = 3.14159;
const SITE_NAME = "HackathonAfrica";
// PI = 3.14; // ❌ Error! Can't reassign const

// var - the OLD way (avoid in modern code)
var oldWay = "Don't use this";</code></pre>
</div>

<h3>Data Types</h3>

<div class="code-block">
<pre><code>// STRING - Text (in quotes)
let name = "Kwame";
let greeting = 'Hello!';
let message = `Welcome, ${name}!`;  // Template literal

// NUMBER - Integers and decimals
let age = 25;
let price = 99.99;
let temperature = -5;
let infinity = Infinity;

// BOOLEAN - True or false
let isLoggedIn = true;
let hasPermission = false;

// UNDEFINED - Not yet assigned
let unknown;
console.log(unknown);  // undefined

// NULL - Intentionally empty
let emptyValue = null;

// Check the type
console.log(typeof name);      // "string"
console.log(typeof age);       // "number"
console.log(typeof isLoggedIn); // "boolean"
console.log(typeof unknown);   // "undefined"
console.log(typeof null);      // "object" (JavaScript quirk!)</code></pre>
</div>

<h3>Working with Strings</h3>

<div class="code-block">
<pre><code>let firstName = "Amara";
let lastName = "Okafor";

// Concatenation (old way)
let fullName = firstName + " " + lastName;
console.log(fullName);  // "Amara Okafor"

// Template literals (modern way - use backticks!)
let intro = `My name is ${firstName} ${lastName}`;
let calc = `2 + 2 = ${2 + 2}`;  // Can include expressions!

// String properties and methods
console.log(firstName.length);        // 5
console.log(firstName.toUpperCase()); // "AMARA"
console.log(firstName.toLowerCase()); // "amara"
console.log(firstName[0]);            // "A" (first character)
console.log(firstName.includes("ma")); // true
console.log(firstName.startsWith("Am")); // true</code></pre>
</div>

<h3>Working with Numbers</h3>

<div class="code-block">
<pre><code>let a = 10;
let b = 3;

// Basic math
console.log(a + b);   // 13 (addition)
console.log(a - b);   // 7  (subtraction)
console.log(a * b);   // 30 (multiplication)
console.log(a / b);   // 3.333... (division)
console.log(a % b);   // 1  (remainder/modulo)
console.log(a ** b);  // 1000 (exponent: 10³)

// Increment and decrement
let count = 5;
count++;  // count is now 6
count--;  // count is now 5

// Shorthand operators
let score = 100;
score += 10;   // score = score + 10  → 110
score -= 5;    // score = score - 5   → 105
score *= 2;    // score = score * 2   → 210

// Converting strings to numbers
let str = "42";
let num = parseInt(str);     // 42 (integer)
let decimal = parseFloat("3.14"); // 3.14
let quick = Number("123");   // 123</code></pre>
</div>

<h3>Naming Rules</h3>

<div class="code-block">
<pre><code>// ✅ Valid variable names
let userName = "Amara";     // camelCase (recommended)
let user_name = "Amara";    // snake_case
let $price = 19.99;         // Can start with $
let _private = "secret";    // Can start with _
let user2 = "Second user";  // Can contain numbers

// ❌ Invalid variable names
// let 2user = "Bad";       // Can't start with number
// let user-name = "Bad";   // Can't use hyphens
// let let = 5;             // Can't use reserved words
// let my name = "Bad";     // Can't have spaces</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Using var</strong> — Always use <code>let</code> or <code>const</code> instead</li>
        <li><strong>Reassigning const</strong> — Use <code>let</code> if the value will change</li>
        <li><strong>Forgetting quotes for strings</strong> — <code>let name = Amara</code> causes an error; need <code>"Amara"</code></li>
        <li><strong>Confusing = and ==</strong> — Single <code>=</code> assigns, double <code>==</code> compares</li>
        <li><strong>String + Number confusion</strong> — <code>"5" + 5</code> equals <code>"55"</code> (string), not <code>10</code>!</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use <code>let</code> for variables that change, <code>const</code> for constants</li>
        <li>Main types: string, number, boolean, undefined, null</li>
        <li>Template literals (<code>`Hello ${name}`</code>) are great for building strings</li>
        <li>Use <code>typeof</code> to check a variable's data type</li>
        <li>Variable names are case-sensitive and can't start with numbers</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 15, 4, 'Variables and Data Types', $lesson15, 2, 30);

// Module 5: Control Flow
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(10, 3, 'Control Flow', 'Make decisions in your code with conditionals and loops.', 2)");

$lesson16 = <<<'HTML'
<h2>Conditionals: If, Else, and Switch</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of conditionals like traffic lights...</h4>
    <p>At a traffic light, you follow different instructions based on the color: If green, go. If yellow, slow down. If red, stop. Conditionals in programming work the same way — they let your code make decisions based on conditions!</p>
</div>

<p>Conditionals allow your program to make decisions and execute different code based on different conditions.</p>

<h3>The if Statement</h3>

<div class="code-block">
<pre><code>let age = 18;

// Basic if
if (age >= 18) {
    console.log("You can vote!");
}

// if...else
if (age >= 18) {
    console.log("You are an adult.");
} else {
    console.log("You are a minor.");
}

// if...else if...else
let score = 85;

if (score >= 90) {
    console.log("Grade: A");
} else if (score >= 80) {
    console.log("Grade: B");
} else if (score >= 70) {
    console.log("Grade: C");
} else if (score >= 60) {
    console.log("Grade: D");
} else {
    console.log("Grade: F");
}</code></pre>
</div>

<h3>Comparison Operators</h3>

<div class="code-block">
<pre><code>let x = 10;

// Comparison operators
x == 10   // Equal to (loose - avoid!)
x === 10  // Strictly equal (same type and value) ✅
x != 10   // Not equal (loose)
x !== 10  // Strictly not equal ✅
x > 5     // Greater than
x < 20    // Less than
x >= 10   // Greater than or equal
x <= 10   // Less than or equal

// Always use === and !== (strict comparison)
console.log(5 == "5");   // true (loose, converts type)
console.log(5 === "5");  // false (strict, different types) ✅</code></pre>
</div>

<h3>Logical Operators</h3>

<div class="code-block">
<pre><code>let age = 25;
let hasLicense = true;

// AND (&&) - both must be true
if (age >= 18 && hasLicense) {
    console.log("You can drive!");
}

// OR (||) - at least one must be true
let isWeekend = true;
let isHoliday = false;

if (isWeekend || isHoliday) {
    console.log("No work today!");
}

// NOT (!) - reverses boolean
let isLoggedIn = false;

if (!isLoggedIn) {
    console.log("Please log in.");
}

// Combining operators
if ((age >= 18 && hasLicense) || isEmergency) {
    console.log("You may drive.");
}</code></pre>
</div>

<h3>Truthy and Falsy Values</h3>

<div class="code-block">
<pre><code>// Falsy values (evaluate to false)
if (false) { }      // false
if (0) { }          // 0
if ("") { }         // Empty string
if (null) { }       // null
if (undefined) { }  // undefined
if (NaN) { }        // NaN

// Everything else is truthy!
if (true) { }       // true
if (1) { }          // Any non-zero number
if ("hello") { }    // Any non-empty string
if ([]) { }         // Empty array (truthy!)
if ({}) { }         // Empty object (truthy!)

// Practical example
let username = "";

if (username) {
    console.log(`Welcome, ${username}!`);
} else {
    console.log("Please enter a username.");
}</code></pre>
</div>

<h3>Ternary Operator</h3>
<p>A shorthand for simple if/else:</p>

<div class="code-block">
<pre><code>// condition ? valueIfTrue : valueIfFalse

let age = 20;
let status = age >= 18 ? "adult" : "minor";
console.log(status);  // "adult"

// Equivalent to:
let status2;
if (age >= 18) {
    status2 = "adult";
} else {
    status2 = "minor";
}</code></pre>
</div>

<h3>Switch Statement</h3>

<div class="code-block">
<pre><code>let day = "Monday";

switch (day) {
    case "Monday":
        console.log("Start of work week");
        break;
    case "Tuesday":
    case "Wednesday":
    case "Thursday":
        console.log("Midweek");
        break;
    case "Friday":
        console.log("TGIF!");
        break;
    case "Saturday":
    case "Sunday":
        console.log("Weekend!");
        break;
    default:
        console.log("Invalid day");
}</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Using = instead of ===</strong> — <code>if (x = 5)</code> assigns, <code>if (x === 5)</code> compares</li>
        <li><strong>Using == instead of ===</strong> — Always use strict equality <code>===</code></li>
        <li><strong>Forgetting break in switch</strong> — Without break, execution "falls through" to next case</li>
        <li><strong>Not using braces</strong> — Always use <code>{ }</code> even for single-line if statements</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use <code>if</code>, <code>else if</code>, <code>else</code> for conditional logic</li>
        <li>Always use <code>===</code> (strict equality) instead of <code>==</code></li>
        <li><code>&&</code> is AND, <code>||</code> is OR, <code>!</code> is NOT</li>
        <li>The ternary operator <code>? :</code> is a shorthand for simple if/else</li>
        <li>Use <code>switch</code> for multiple conditions on the same value</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 16, 10, 'Conditionals: If, Else, and Switch', $lesson16, 1, 30);

// Lesson: Loops
$lesson17 = <<<'HTML'
<h2>Loops: For, While, and ForEach</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of loops like repetitive tasks...</h4>
    <p>Imagine you need to stamp 100 documents. You wouldn't write instructions for each stamp — you'd say "stamp a document, repeat 100 times." Loops let your code repeat tasks without writing the same code over and over!</p>
</div>

<h3>The For Loop</h3>
<p>Best when you know how many times to repeat:</p>

<div class="code-block">
<pre><code>// Structure: for (start; condition; update)
for (let i = 0; i < 5; i++) {
    console.log(i);
}
// Output: 0, 1, 2, 3, 4

// Counting down
for (let i = 10; i > 0; i--) {
    console.log(i);
}
console.log("Liftoff!");

// Stepping by 2
for (let i = 0; i <= 10; i += 2) {
    console.log(i);  // 0, 2, 4, 6, 8, 10
}</code></pre>
</div>

<h3>Looping Through Arrays</h3>

<div class="code-block">
<pre><code>const fruits = ["apple", "banana", "orange", "mango"];

// Traditional for loop
for (let i = 0; i < fruits.length; i++) {
    console.log(fruits[i]);
}

// for...of loop (cleaner!)
for (const fruit of fruits) {
    console.log(fruit);
}

// forEach method
fruits.forEach(function(fruit) {
    console.log(fruit);
});

// forEach with arrow function
fruits.forEach(fruit => console.log(fruit));

// forEach with index
fruits.forEach((fruit, index) => {
    console.log(`${index + 1}. ${fruit}`);
});</code></pre>
</div>

<h3>The While Loop</h3>
<p>Best when you don't know how many iterations:</p>

<div class="code-block">
<pre><code>// While loop
let count = 0;

while (count < 5) {
    console.log(count);
    count++;
}

// Practical example: random number game
let target = 7;
let guess = 0;

while (guess !== target) {
    guess = Math.floor(Math.random() * 10) + 1;
    console.log(`Guessed: ${guess}`);
}
console.log("Got it!");</code></pre>
</div>

<h3>The Do...While Loop</h3>
<p>Runs at least once, then checks condition:</p>

<div class="code-block">
<pre><code>let input;

do {
    input = prompt("Enter 'quit' to exit:");
    console.log(`You entered: ${input}`);
} while (input !== "quit");</code></pre>
</div>

<h3>Break and Continue</h3>

<div class="code-block">
<pre><code>// break - exit the loop entirely
for (let i = 0; i < 10; i++) {
    if (i === 5) {
        break;  // Stop at 5
    }
    console.log(i);
}
// Output: 0, 1, 2, 3, 4

// continue - skip current iteration
for (let i = 0; i < 5; i++) {
    if (i === 2) {
        continue;  // Skip 2
    }
    console.log(i);
}
// Output: 0, 1, 3, 4</code></pre>
</div>

<h3>Nested Loops</h3>

<div class="code-block">
<pre><code>// Multiplication table
for (let i = 1; i <= 3; i++) {
    for (let j = 1; j <= 3; j++) {
        console.log(`${i} x ${j} = ${i * j}`);
    }
}

// Output:
// 1 x 1 = 1
// 1 x 2 = 2
// 1 x 3 = 3
// 2 x 1 = 2
// ... and so on</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Infinite loops</strong> — Forgetting to update the counter causes endless loops. Always ensure the condition will eventually be false!</li>
        <li><strong>Off-by-one errors</strong> — Using <code>&lt;=</code> vs <code>&lt;</code>. Remember: arrays start at index 0</li>
        <li><strong>Modifying array while looping</strong> — This can cause unexpected behavior. Create a copy first!</li>
        <li><strong>Using wrong loop type</strong> — Use <code>for</code> when you know iterations, <code>while</code> when you don't</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><code>for</code> loop: Best when you know how many iterations</li>
        <li><code>for...of</code>: Best for iterating arrays</li>
        <li><code>forEach</code>: Array method, clean syntax</li>
        <li><code>while</code>: Best when iterations are unknown</li>
        <li><code>break</code> exits the loop, <code>continue</code> skips to next iteration</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 17, 10, 'Loops: For, While, and ForEach', $lesson17, 2, 30);

// JavaScript exercise
insertExercise($pdo, 6, 17, 'FizzBuzz Challenge', 
'Solve the classic FizzBuzz problem using loops and conditionals.',
'Write a loop that prints numbers from 1 to 20, but:
- For multiples of 3, print "Fizz" instead of the number
- For multiples of 5, print "Buzz" instead of the number
- For multiples of both 3 and 5, print "FizzBuzz"',
'// Write your FizzBuzz solution here
for (let i = 1; i <= 20; i++) {
    // Add your logic here
    
}',
'for (let i = 1; i <= 20; i++) {
    if (i % 3 === 0 && i % 5 === 0) {
        console.log("FizzBuzz");
    } else if (i % 3 === 0) {
        console.log("Fizz");
    } else if (i % 5 === 0) {
        console.log("Buzz");
    } else {
        console.log(i);
    }
}',
'Use the modulo operator (%) to check divisibility|Check for FizzBuzz first (both 3 AND 5)|Use else if for the remaining conditions',
'javascript', 'medium', 25);

// Module 5 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (10, 10, 'Control Flow Quiz', 70)");

$pdo->exec("DELETE FROM quiz_questions WHERE quiz_id = 10");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(35, 10, 'Which operator should you use for strict equality comparison?', 1),
(36, 10, 'What does the && operator mean?', 2),
(37, 10, 'Which loop is best when you know how many iterations you need?', 3),
(38, 10, 'What does the break keyword do in a loop?', 4)");

$pdo->exec("DELETE FROM quiz_options WHERE question_id IN (35,36,37,38)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(35, '=== (triple equals)', 1),
(35, '== (double equals)', 0),
(35, '= (single equals)', 0),
(35, '!= (not equals)', 0),
(36, 'AND - both conditions must be true', 1),
(36, 'OR - at least one condition must be true', 0),
(36, 'NOT - reverses the condition', 0),
(36, 'XOR - exactly one must be true', 0),
(37, 'for loop', 1),
(37, 'while loop', 0),
(37, 'do...while loop', 0),
(37, 'foreach loop', 0),
(38, 'Exits the loop immediately', 1),
(38, 'Skips to the next iteration', 0),
(38, 'Pauses the loop temporarily', 0),
(38, 'Restarts the loop from the beginning', 0)");

// Module 6: Functions
$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(11, 3, 'Functions', 'Create reusable blocks of code with functions. Learn parameters, return values, and arrow functions.', 3)");

$lesson18 = <<<'HTML'
<h2>Introduction to Functions</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of functions like recipes...</h4>
    <p>A recipe is a set of instructions you can follow any time you want to make that dish. You can make it once or a hundred times without rewriting the instructions. Functions work the same way — write the code once, use it whenever you need!</p>
</div>

<p>Functions are reusable blocks of code that perform specific tasks. They help keep your code organized, readable, and DRY (Don't Repeat Yourself).</p>

<h3>Declaring Functions</h3>

<div class="code-block">
<pre><code>// Function declaration
function greet() {
    console.log("Hello, World!");
}

// Calling (invoking) the function
greet();  // Output: Hello, World!
greet();  // Can call it multiple times!</code></pre>
</div>

<h3>Parameters and Arguments</h3>

<div class="code-block">
<pre><code>// Parameters are placeholders in the definition
function greet(name) {
    console.log(`Hello, ${name}!`);
}

// Arguments are actual values when calling
greet("Amara");   // Hello, Amara!
greet("Kwame");   // Hello, Kwame!

// Multiple parameters
function introduce(name, age, country) {
    console.log(`I'm ${name}, ${age} years old, from ${country}.`);
}

introduce("Fatima", 22, "Kenya");
// Output: I'm Fatima, 22 years old, from Kenya.</code></pre>
</div>

<h3>Default Parameters</h3>

<div class="code-block">
<pre><code>// Set default values for parameters
function greet(name = "Guest") {
    console.log(`Hello, ${name}!`);
}

greet("Amara");  // Hello, Amara!
greet();         // Hello, Guest!

function createUser(name, role = "student", active = true) {
    return { name, role, active };
}

createUser("Kwame");
// { name: "Kwame", role: "student", active: true }</code></pre>
</div>

<h3>Return Values</h3>

<div class="code-block">
<pre><code>// Functions can return values
function add(a, b) {
    return a + b;
}

let sum = add(5, 3);
console.log(sum);  // 8

// Using return value directly
console.log(add(10, 20));  // 30

// Return stops function execution
function checkAge(age) {
    if (age < 0) {
        return "Invalid age";  // Exits here if age is negative
    }
    if (age >= 18) {
        return "Adult";
    }
    return "Minor";
}

console.log(checkAge(25));  // Adult
console.log(checkAge(-5));  // Invalid age</code></pre>
</div>

<h3>Arrow Functions</h3>
<p>A shorter syntax for functions (ES6+):</p>

<div class="code-block">
<pre><code>// Traditional function
function add(a, b) {
    return a + b;
}

// Arrow function equivalent
const add = (a, b) => {
    return a + b;
};

// Even shorter for single expressions
const add = (a, b) => a + b;

// Single parameter doesn't need parentheses
const double = n => n * 2;

// No parameters need empty parentheses
const sayHello = () => console.log("Hello!");

// Examples
console.log(add(5, 3));    // 8
console.log(double(4));    // 8
sayHello();                // Hello!</code></pre>
</div>

<h3>Function Expressions</h3>

<div class="code-block">
<pre><code>// Storing a function in a variable
const greet = function(name) {
    console.log(`Hello, ${name}!`);
};

greet("Amara");

// Arrow function expression
const multiply = (a, b) => a * b;

console.log(multiply(4, 5));  // 20</code></pre>
</div>

<h3>Practical Examples</h3>

<div class="code-block">
<pre><code>// Calculate area of a rectangle
const calculateArea = (width, height) => width * height;

console.log(calculateArea(5, 10));  // 50

// Check if number is even
const isEven = num => num % 2 === 0;

console.log(isEven(4));   // true
console.log(isEven(7));   // false

// Convert Celsius to Fahrenheit
const celsiusToFahrenheit = celsius => (celsius * 9/5) + 32;

console.log(celsiusToFahrenheit(0));   // 32
console.log(celsiusToFahrenheit(100)); // 212

// Find the largest number
function findMax(a, b, c) {
    if (a >= b && a >= c) return a;
    if (b >= a && b >= c) return b;
    return c;
}

console.log(findMax(5, 9, 3));  // 9</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting to call the function</strong> — <code>greet</code> vs <code>greet()</code>. Parentheses are required!</li>
        <li><strong>Forgetting to return</strong> — Functions without return give <code>undefined</code></li>
        <li><strong>Wrong parameter order</strong> — Arguments must match parameter order</li>
        <li><strong>Modifying global variables</strong> — Functions should ideally use parameters and return values</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Functions are reusable blocks of code</li>
        <li>Parameters receive values, arguments pass values</li>
        <li>Use <code>return</code> to send back a value</li>
        <li>Arrow functions (<code>=></code>) are shorter syntax</li>
        <li>Default parameters provide fallback values</li>
    </ul>
</div>
HTML;

insertLesson($pdo, 18, 11, 'Introduction to Functions', $lesson18, 1, 30);

// Module 6 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (11, 11, 'Functions Quiz', 70)");

$pdo->exec("DELETE FROM quiz_questions WHERE quiz_id = 11");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(39, 11, 'What keyword is used to send back a value from a function?', 1),
(40, 11, 'What is the arrow function syntax for: function add(a, b) { return a + b; }', 2),
(41, 11, 'What happens if you call a function without parentheses?', 3)");

$pdo->exec("DELETE FROM quiz_options WHERE question_id IN (39,40,41)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(39, 'return', 1),
(39, 'output', 0),
(39, 'send', 0),
(39, 'give', 0),
(40, 'const add = (a, b) => a + b', 1),
(40, 'const add = a, b => a + b', 0),
(40, 'const add => (a, b) = a + b', 0),
(40, 'const add = (a, b) -> a + b', 0),
(41, 'It returns a reference to the function, not the result', 1),
(41, 'It throws an error', 0),
(41, 'It calls the function normally', 0),
(41, 'It returns undefined', 0)");

echo "JavaScript course content complete!\n\n";

// Re-enable foreign keys
$pdo->exec('PRAGMA foreign_keys = ON');

echo "✅ All course content has been seeded successfully!\n";
echo "================================================\n";
echo "Courses: 3 (HTML, CSS, JavaScript)\n";
echo "Modules: 11\n";
echo "Lessons: 18\n";
echo "Quizzes: 11\n";
echo "Code Exercises: 6\n";
echo "================================================\n";
