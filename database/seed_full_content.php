<?php
/**
 * HackathonAfrica LMS - Complete Course Content
 * 3 Courses × 4 Modules × 5 Lessons = 60 Lessons Total
 */

require_once __DIR__ . '/../config/database.php';
$pdo = db();
$pdo->exec('PRAGMA foreign_keys = OFF');

echo "=== HackathonAfrica LMS Content Seeder ===\n\n";

// ╔═══════════════════════════════════════════════════════════════╗
// ║                    COURSES                                      ║
// ╚═══════════════════════════════════════════════════════════════╝

echo "Creating courses...\n";

$pdo->exec("INSERT INTO courses (id, title, description, status, order_index, estimated_hours, difficulty) VALUES 
(1, 'HTML — Mastering the Web''s Backbone', 'A deep dive into HTML from document structure to semantic markup, forms, accessibility, and HTML5 APIs. Master the foundation that every website is built upon.', 'published', 1, 12, 'beginner'),
(2, 'CSS — Styling the Modern Web', 'Selectors, box model, Flexbox, Grid, animations, and modern CSS architecture. Transform plain HTML into beautiful, responsive designs.', 'published', 2, 14, 'beginner'),
(3, 'JavaScript — From Fundamentals to Mastery', 'Closures, async patterns, DOM APIs, functional programming, and modern ES6+ features. Bring your websites to life with interactivity.', 'published', 3, 18, 'beginner')");

// ╔═══════════════════════════════════════════════════════════════╗
// ║                HTML COURSE - 4 MODULES                          ║
// ╚═══════════════════════════════════════════════════════════════╝

echo "Creating HTML modules...\n";

$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(1, 1, 'Document Structure & Core Elements', 'Learn the anatomy of HTML documents, essential tags, and how browsers interpret your code.', 1),
(2, 1, 'Forms & User Input', 'Master HTML forms, input types, validation, and creating interactive user experiences.', 2),
(3, 1, 'Semantic HTML & Accessibility', 'Write meaningful HTML that search engines love and screen readers understand.', 3),
(4, 1, 'Advanced HTML & Performance', 'HTML5 APIs, media elements, performance optimization, and modern HTML features.', 4)");

// HTML Module 1 Lessons
$lessons = [];

$lessons[1] = <<<'CONTENT'
<h2>How the Web Works</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of it like ordering food delivery...</h4>
    <p>You (the <strong>client/browser</strong>) open an app and place an order. The restaurant (the <strong>server</strong>) receives your request, prepares your food (the <strong>website files</strong>), and the delivery driver (the <strong>internet</strong>) brings it to you. When you type a URL, you're placing an order for a webpage!</p>
</div>

<p>Every time you visit a website, an amazing process happens in milliseconds. Understanding this will make you a better developer.</p>

<h3>The Client-Server Model</h3>
<p>The web operates on a simple but powerful principle:</p>

<ul>
    <li><strong>Client (Browser)</strong> — Chrome, Firefox, Safari, or Edge. It requests and displays websites.</li>
    <li><strong>Server</strong> — A computer that stores website files and sends them when requested.</li>
    <li><strong>Request</strong> — Your browser asking for a webpage.</li>
    <li><strong>Response</strong> — The server sending back HTML, CSS, and JavaScript files.</li>
</ul>

<h3>What Happens When You Visit a Website</h3>

<div class="code-block">
<pre><code>1. You type: www.google.com
2. Browser asks DNS: "Where is google.com?"
3. DNS replies: "It's at IP 142.250.185.78"
4. Browser connects to that IP address
5. Browser sends: "GET /index.html please"
6. Server sends back: HTML, CSS, JS files
7. Browser renders the beautiful page you see</code></pre>
</div>

<h3>What is HTML?</h3>
<p><strong>HTML</strong> = <em>HyperText Markup Language</em></p>

<ul>
    <li><strong>HyperText</strong> — Text with links to other text (that's the "web" in World Wide Web!)</li>
    <li><strong>Markup</strong> — A system of tags that annotate content</li>
    <li><strong>Language</strong> — A set of rules computers understand</li>
</ul>

<p>HTML is the <strong>skeleton</strong> of every website. CSS is the skin and clothes. JavaScript is the muscles and brain.</p>

<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html&gt;
  &lt;head&gt;
    &lt;title&gt;My First Page&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;h1&gt;Hello, Africa!&lt;/h1&gt;
    &lt;p&gt;Welcome to web development.&lt;/p&gt;
  &lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting closing tags</strong> — Every <code>&lt;tag&gt;</code> needs a <code>&lt;/tag&gt;</code></li>
        <li><strong>Using Word/Google Docs</strong> — These add hidden formatting. Use VS Code!</li>
        <li><strong>Not understanding the request/response cycle</strong> — Your browser always asks, servers always respond</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>The web uses a client-server model</li>
        <li>HTML provides structure, CSS provides style, JavaScript provides interactivity</li>
        <li>Every website starts as an HTML file</li>
    </ul>
</div>
CONTENT;

$lessons[2] = <<<'CONTENT'
<h2>Setting Up Your Development Environment</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of it like a carpenter's workshop...</h4>
    <p>A carpenter needs a workbench, saw, and hammer before building furniture. You need a <strong>code editor</strong> (your workbench), a <strong>browser</strong> (to see your work), and <strong>developer tools</strong> (to debug). Let's set up your workshop!</p>
</div>

<h3>Step 1: Install VS Code</h3>
<p>Visual Studio Code is the most popular code editor — free, powerful, and beginner-friendly.</p>

<ol>
    <li>Visit <a href="https://code.visualstudio.com" target="_blank">code.visualstudio.com</a></li>
    <li>Download for your operating system</li>
    <li>Run the installer</li>
    <li>Launch VS Code — you're ready!</li>
</ol>

<h3>Step 2: Create Your First HTML File</h3>

<div class="code-block">
<pre><code>1. Open VS Code
2. File → New File (or Ctrl+N / Cmd+N)
3. Save As → "index.html" (the .html is important!)
4. Type your HTML code
5. Save (Ctrl+S / Cmd+S)
6. Find the file and double-click to open in browser</code></pre>
</div>

<h3>Your First HTML File</h3>

<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My First Webpage&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;h1&gt;Hello, World!&lt;/h1&gt;
    &lt;p&gt;I just created my first webpage!&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<h3>Browser Developer Tools</h3>
<p>Every browser has built-in tools for developers. Press <strong>F12</strong> to open them.</p>

<ul>
    <li><strong>Elements tab</strong> — See and edit the HTML live</li>
    <li><strong>Console tab</strong> — See JavaScript errors and messages</li>
    <li><strong>Network tab</strong> — Watch files being loaded</li>
</ul>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Wrong file extension</strong> — Save as <code>.html</code> not <code>.txt</code></li>
        <li><strong>Using Notepad on Windows</strong> — It might save as .txt secretly. Use VS Code!</li>
        <li><strong>Not saving before refreshing</strong> — Always Ctrl+S before checking your browser</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>VS Code is the recommended editor for web development</li>
        <li>HTML files must end with .html</li>
        <li>F12 opens developer tools in any browser</li>
        <li><code>index.html</code> is the standard name for homepage files</li>
    </ul>
</div>
CONTENT;

$lessons[3] = <<<'CONTENT'
<h2>HTML Document Structure</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of HTML like a letter...</h4>
    <p>A formal letter has a specific structure: envelope (<code>&lt;html&gt;</code>), header with date/address (<code>&lt;head&gt;</code>), and the actual letter content (<code>&lt;body&gt;</code>). Skip any part and the post office (browser) gets confused!</p>
</div>

<h3>The Required Structure</h3>

<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;           &lt;!-- Declaration: "This is HTML5" --&gt;
&lt;html lang="en"&gt;          &lt;!-- Root element, contains everything --&gt;
&lt;head&gt;                    &lt;!-- Metadata, not visible on page --&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Page Title&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;                    &lt;!-- Visible content goes here --&gt;
    &lt;h1&gt;Hello!&lt;/h1&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<h3>Understanding Each Part</h3>

<table>
    <tr><th>Element</th><th>Purpose</th><th>Required?</th></tr>
    <tr><td><code>&lt;!DOCTYPE html&gt;</code></td><td>Tells browser this is HTML5</td><td>Yes</td></tr>
    <tr><td><code>&lt;html&gt;</code></td><td>Root container for all content</td><td>Yes</td></tr>
    <tr><td><code>&lt;head&gt;</code></td><td>Contains metadata (invisible)</td><td>Yes</td></tr>
    <tr><td><code>&lt;title&gt;</code></td><td>Text shown in browser tab</td><td>Yes</td></tr>
    <tr><td><code>&lt;body&gt;</code></td><td>All visible page content</td><td>Yes</td></tr>
</table>

<h3>Essential Meta Tags</h3>

<div class="code-block">
<pre><code>&lt;head&gt;
    &lt;!-- Character encoding (supports all languages) --&gt;
    &lt;meta charset="UTF-8"&gt;
    
    &lt;!-- Responsive design (mobile-friendly) --&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    
    &lt;!-- Page description for search engines --&gt;
    &lt;meta name="description" content="Learn HTML at HackathonAfrica"&gt;
    
    &lt;!-- Page title --&gt;
    &lt;title&gt;Learn HTML | HackathonAfrica&lt;/title&gt;
    
    &lt;!-- Link to CSS file --&gt;
    &lt;link rel="stylesheet" href="styles.css"&gt;
&lt;/head&gt;</code></pre>
</div>

<h3>Comments in HTML</h3>

<div class="code-block">
<pre><code>&lt;!-- This is a comment. Browsers ignore this. --&gt;

&lt;!-- 
    Multi-line comments
    work like this
--&gt;

&lt;h1&gt;Welcome&lt;/h1&gt; &lt;!-- This appears on the page --&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting DOCTYPE</strong> — Browser enters "quirks mode" and things look wrong</li>
        <li><strong>Putting content in head</strong> — Only metadata goes in head, content goes in body</li>
        <li><strong>Missing charset</strong> — Special characters (é, ñ, 中文) won't display correctly</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Every HTML page needs DOCTYPE, html, head, and body</li>
        <li>head = invisible metadata, body = visible content</li>
        <li>Always include charset and viewport meta tags</li>
    </ul>
</div>
CONTENT;

$lessons[4] = <<<'CONTENT'
<h2>Headings, Paragraphs & Text</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of it like a newspaper...</h4>
    <p>Newspapers have big headlines (h1), section headers (h2), subsection headers (h3), and body text (p). This hierarchy helps readers scan content quickly. HTML works the same way!</p>
</div>

<h3>The Six Heading Levels</h3>

<div class="code-block">
<pre><code>&lt;h1&gt;Main Page Title&lt;/h1&gt;     &lt;!-- Only ONE per page! --&gt;
&lt;h2&gt;Major Section&lt;/h2&gt;       &lt;!-- Chapter titles --&gt;
&lt;h3&gt;Subsection&lt;/h3&gt;          &lt;!-- Topics within chapters --&gt;
&lt;h4&gt;Minor heading&lt;/h4&gt;       &lt;!-- Subtopics --&gt;
&lt;h5&gt;Small heading&lt;/h5&gt;       &lt;!-- Rarely used --&gt;
&lt;h6&gt;Smallest heading&lt;/h6&gt;    &lt;!-- Rarely used --&gt;</code></pre>
</div>

<h3>Paragraphs and Line Breaks</h3>

<div class="code-block">
<pre><code>&lt;!-- Paragraphs have automatic spacing --&gt;
&lt;p&gt;This is the first paragraph. It can be long.&lt;/p&gt;
&lt;p&gt;This is the second paragraph.&lt;/p&gt;

&lt;!-- Line breaks for addresses, poetry --&gt;
&lt;p&gt;
    123 Main Street&lt;br&gt;
    Lagos, Nigeria&lt;br&gt;
    Africa
&lt;/p&gt;

&lt;!-- Horizontal rule (divider line) --&gt;
&lt;hr&gt;</code></pre>
</div>

<h3>Text Formatting</h3>

<div class="code-block">
<pre><code>&lt;!-- Semantic formatting (meaningful) --&gt;
&lt;strong&gt;Important text&lt;/strong&gt;     &lt;!-- Bold + importance --&gt;
&lt;em&gt;Emphasized text&lt;/em&gt;           &lt;!-- Italic + emphasis --&gt;
&lt;mark&gt;Highlighted text&lt;/mark&gt;      &lt;!-- Yellow highlight --&gt;
&lt;del&gt;Deleted text&lt;/del&gt;            &lt;!-- Strikethrough --&gt;
&lt;ins&gt;Inserted text&lt;/ins&gt;           &lt;!-- Underlined --&gt;

&lt;!-- Technical text --&gt;
&lt;code&gt;console.log()&lt;/code&gt;         &lt;!-- Inline code --&gt;
&lt;kbd&gt;Ctrl + C&lt;/kbd&gt;               &lt;!-- Keyboard input --&gt;

&lt;!-- Subscript and Superscript --&gt;
H&lt;sub&gt;2&lt;/sub&gt;O                    &lt;!-- Water --&gt;
E=mc&lt;sup&gt;2&lt;/sup&gt;                   &lt;!-- Einstein's formula --&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Multiple h1 tags</strong> — Only ONE h1 per page for SEO</li>
        <li><strong>Skipping heading levels</strong> — Don't go from h1 to h4; use h1 → h2 → h3</li>
        <li><strong>Using br for spacing</strong> — Use CSS margins instead</li>
        <li><strong>Using b/i instead of strong/em</strong> — Semantic tags have meaning</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>ONE h1 per page — it's your main title</li>
        <li>Headings create hierarchy: h1 → h2 → h3</li>
        <li>Use strong for importance, em for emphasis</li>
        <li>br is for line breaks, not spacing</li>
    </ul>
</div>
CONTENT;

$lessons[5] = <<<'CONTENT'
<h2>Links and Navigation</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of links as doors...</h4>
    <p>Each link is a door to somewhere else. The <code>href</code> is the address where the door leads. The link text is the sign on the door. You can have doors to other rooms (pages), other buildings (websites), or even specific spots in the same room (anchor links)!</p>
</div>

<h3>Basic Links</h3>

<div class="code-block">
<pre><code>&lt;!-- Link to another website --&gt;
&lt;a href="https://www.google.com"&gt;Visit Google&lt;/a&gt;

&lt;!-- Link to a page on your site --&gt;
&lt;a href="about.html"&gt;About Us&lt;/a&gt;
&lt;a href="pages/contact.html"&gt;Contact&lt;/a&gt;

&lt;!-- Link to parent folder --&gt;
&lt;a href="../index.html"&gt;Back to Home&lt;/a&gt;</code></pre>
</div>

<h3>Opening Links in New Tabs</h3>

<div class="code-block">
<pre><code>&lt;!-- Opens in new tab (add security attributes!) --&gt;
&lt;a href="https://google.com" 
   target="_blank" 
   rel="noopener noreferrer"&gt;
    Google (new tab)
&lt;/a&gt;</code></pre>
</div>

<h3>Email and Phone Links</h3>

<div class="code-block">
<pre><code>&lt;!-- Email link --&gt;
&lt;a href="mailto:hello@example.com"&gt;Email Us&lt;/a&gt;

&lt;!-- Email with subject --&gt;
&lt;a href="mailto:hello@example.com?subject=Question"&gt;Ask a Question&lt;/a&gt;

&lt;!-- Phone link (great for mobile!) --&gt;
&lt;a href="tel:+2341234567890"&gt;Call Us&lt;/a&gt;</code></pre>
</div>

<h3>Anchor Links (Jump to Section)</h3>

<div class="code-block">
<pre><code>&lt;!-- Navigation --&gt;
&lt;nav&gt;
    &lt;a href="#about"&gt;About&lt;/a&gt;
    &lt;a href="#services"&gt;Services&lt;/a&gt;
    &lt;a href="#contact"&gt;Contact&lt;/a&gt;
&lt;/nav&gt;

&lt;!-- Sections with IDs --&gt;
&lt;section id="about"&gt;
    &lt;h2&gt;About Us&lt;/h2&gt;
&lt;/section&gt;

&lt;section id="services"&gt;
    &lt;h2&gt;Our Services&lt;/h2&gt;
&lt;/section&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting https://</strong> — External links need full URLs</li>
        <li><strong>"Click here" links</strong> — Write descriptive link text for accessibility</li>
        <li><strong>Missing rel="noopener"</strong> — Security issue with target="_blank"</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>href specifies where the link goes</li>
        <li>Use target="_blank" with rel="noopener noreferrer"</li>
        <li>mailto: and tel: create email and phone links</li>
        <li>Anchor links use # and element IDs</li>
    </ul>
</div>
CONTENT;

// Insert HTML Module 1 lessons
echo "Creating HTML Module 1 lessons...\n";
for ($i = 1; $i <= 5; $i++) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 1, ?, ?, ?, 15, 1)");
    $titles = ['', 'How the Web Works', 'Setting Up Your Development Environment', 'HTML Document Structure', 'Headings, Paragraphs & Text', 'Links and Navigation'];
    $stmt->execute([$i, $titles[$i], $lessons[$i], $i]);
}

// HTML Module 1 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (1, 1, 'Document Structure Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(1, 1, 'What does HTML stand for?', 1),
(2, 1, 'How many h1 tags should a page have?', 2),
(3, 1, 'What attribute specifies where a link goes?', 3),
(4, 1, 'Which tag contains page metadata?', 4),
(5, 1, 'What opens browser developer tools?', 5)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(1, 'HyperText Markup Language', 1),(1, 'High Tech Modern Language', 0),(1, 'Home Tool Markup Language', 0),(1, 'Hyperlink Text Management', 0),
(2, 'Exactly one', 1),(2, 'As many as needed', 0),(2, 'At least three', 0),(2, 'None', 0),
(3, 'href', 1),(3, 'src', 0),(3, 'link', 0),(3, 'url', 0),
(4, 'head', 1),(4, 'body', 0),(4, 'meta', 0),(4, 'title', 0),
(5, 'F12', 1),(5, 'F1', 0),(5, 'F5', 0),(5, 'F8', 0)");

// HTML Module 2 Lessons (Forms)
$lessons[6] = <<<'CONTENT'
<h2>Introduction to HTML Forms</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of forms like paper forms...</h4>
    <p>A paper form has fields to fill out (inputs), labels explaining each field, and a submit button to send it. HTML forms work exactly the same way, but the data goes to a server instead of a mailbox!</p>
</div>

<h3>Basic Form Structure</h3>

<div class="code-block">
<pre><code>&lt;form action="/submit" method="POST"&gt;
    &lt;label for="name"&gt;Your Name:&lt;/label&gt;
    &lt;input type="text" id="name" name="name"&gt;
    
    &lt;label for="email"&gt;Email:&lt;/label&gt;
    &lt;input type="email" id="email" name="email"&gt;
    
    &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Form Attributes</h3>
<table>
    <tr><th>Attribute</th><th>Purpose</th></tr>
    <tr><td>action</td><td>Where to send the form data (URL)</td></tr>
    <tr><td>method</td><td>GET (in URL) or POST (hidden)</td></tr>
    <tr><td>name</td><td>Identifies data when sent to server</td></tr>
    <tr><td>id</td><td>Connects label to input</td></tr>
</table>

<h3>The Label Element</h3>

<div class="code-block">
<pre><code>&lt;!-- Method 1: for/id connection --&gt;
&lt;label for="email"&gt;Email:&lt;/label&gt;
&lt;input type="email" id="email" name="email"&gt;

&lt;!-- Method 2: Wrapping --&gt;
&lt;label&gt;
    Email:
    &lt;input type="email" name="email"&gt;
&lt;/label&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Missing name attribute</strong> — Data won't be sent without it!</li>
        <li><strong>No labels</strong> — Bad for accessibility; screen readers need labels</li>
        <li><strong>Using GET for sensitive data</strong> — Passwords appear in URL!</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Forms need action (where) and method (how)</li>
        <li>Every input needs a name attribute</li>
        <li>Labels improve accessibility and usability</li>
        <li>Use POST for sensitive data, GET for searches</li>
    </ul>
</div>
CONTENT;

$lessons[7] = <<<'CONTENT'
<h2>Input Types</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of input types like specialized tools...</h4>
    <p>You wouldn't use a hammer to cut wood. Similarly, don't use a text input for email — the email type validates automatically and shows the right keyboard on mobile!</p>
</div>

<h3>Text Input Types</h3>

<div class="code-block">
<pre><code>&lt;input type="text" placeholder="Regular text"&gt;
&lt;input type="email" placeholder="email@example.com"&gt;
&lt;input type="password" placeholder="********"&gt;
&lt;input type="tel" placeholder="+234 123 456 7890"&gt;
&lt;input type="url" placeholder="https://..."&gt;
&lt;input type="search" placeholder="Search..."&gt;</code></pre>
</div>

<h3>Number and Date Types</h3>

<div class="code-block">
<pre><code>&lt;input type="number" min="0" max="100" step="1"&gt;
&lt;input type="range" min="0" max="100"&gt;
&lt;input type="date"&gt;
&lt;input type="time"&gt;
&lt;input type="datetime-local"&gt;
&lt;input type="month"&gt;
&lt;input type="week"&gt;</code></pre>
</div>

<h3>Selection Types</h3>

<div class="code-block">
<pre><code>&lt;!-- Checkbox (multiple selections) --&gt;
&lt;label&gt;&lt;input type="checkbox" name="skills" value="html"&gt; HTML&lt;/label&gt;
&lt;label&gt;&lt;input type="checkbox" name="skills" value="css"&gt; CSS&lt;/label&gt;

&lt;!-- Radio (single selection) --&gt;
&lt;label&gt;&lt;input type="radio" name="level" value="beginner"&gt; Beginner&lt;/label&gt;
&lt;label&gt;&lt;input type="radio" name="level" value="advanced"&gt; Advanced&lt;/label&gt;

&lt;!-- Dropdown --&gt;
&lt;select name="country"&gt;
    &lt;option value=""&gt;Select country&lt;/option&gt;
    &lt;option value="ng"&gt;Nigeria&lt;/option&gt;
    &lt;option value="ke"&gt;Kenya&lt;/option&gt;
    &lt;option value="za"&gt;South Africa&lt;/option&gt;
&lt;/select&gt;</code></pre>
</div>

<h3>Other Input Types</h3>

<div class="code-block">
<pre><code>&lt;input type="file" accept="image/*"&gt;
&lt;input type="color"&gt;
&lt;input type="hidden" name="user_id" value="123"&gt;
&lt;textarea name="message" rows="4"&gt;&lt;/textarea&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use the right input type — email, tel, number, date</li>
        <li>Mobile shows appropriate keyboard for each type</li>
        <li>Checkboxes for multiple, radio for single selection</li>
        <li>textarea for multi-line text</li>
    </ul>
</div>
CONTENT;

$lessons[8] = <<<'CONTENT'
<h2>Form Validation</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of validation like a bouncer...</h4>
    <p>A bouncer checks IDs before letting people into a club. Form validation checks data before letting it reach your server. HTML5 gives you a bouncer for free!</p>
</div>

<h3>Built-in Validation Attributes</h3>

<div class="code-block">
<pre><code>&lt;!-- Required field --&gt;
&lt;input type="text" required&gt;

&lt;!-- Minimum/maximum length --&gt;
&lt;input type="text" minlength="3" maxlength="50"&gt;

&lt;!-- Number range --&gt;
&lt;input type="number" min="18" max="99"&gt;

&lt;!-- Pattern (regex) --&gt;
&lt;input type="text" pattern="[A-Za-z]{3,}" title="At least 3 letters"&gt;

&lt;!-- Email validation (automatic) --&gt;
&lt;input type="email" required&gt;</code></pre>
</div>

<h3>Complete Validated Form</h3>

<div class="code-block">
<pre><code>&lt;form action="/register" method="POST"&gt;
    &lt;label for="username"&gt;Username (3-20 characters):&lt;/label&gt;
    &lt;input type="text" id="username" name="username"
           required minlength="3" maxlength="20"&gt;
    
    &lt;label for="email"&gt;Email:&lt;/label&gt;
    &lt;input type="email" id="email" name="email" required&gt;
    
    &lt;label for="age"&gt;Age (18+):&lt;/label&gt;
    &lt;input type="number" id="age" name="age" min="18" required&gt;
    
    &lt;label for="password"&gt;Password (8+ characters):&lt;/label&gt;
    &lt;input type="password" id="password" name="password"
           required minlength="8"&gt;
    
    &lt;button type="submit"&gt;Register&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>HTML5 validation is free and automatic</li>
        <li>Use required, minlength, maxlength, min, max</li>
        <li>pattern allows custom validation with regex</li>
        <li>Always validate on server too — client validation can be bypassed</li>
    </ul>
</div>
CONTENT;

$lessons[9] = <<<'CONTENT'
<h2>Form Accessibility & UX</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think about users with disabilities...</h4>
    <p>Some users navigate with keyboards only. Some use screen readers. Some have motor difficulties. Good form design helps everyone, including people in a hurry or on small screens!</p>
</div>

<h3>Accessible Form Structure</h3>

<div class="code-block">
<pre><code>&lt;form&gt;
    &lt;!-- Group related fields --&gt;
    &lt;fieldset&gt;
        &lt;legend&gt;Personal Information&lt;/legend&gt;
        
        &lt;label for="name"&gt;Full Name *&lt;/label&gt;
        &lt;input type="text" id="name" name="name" required
               aria-describedby="name-help"&gt;
        &lt;small id="name-help"&gt;Enter your full legal name&lt;/small&gt;
    &lt;/fieldset&gt;
    
    &lt;fieldset&gt;
        &lt;legend&gt;Contact Details&lt;/legend&gt;
        &lt;!-- More fields... --&gt;
    &lt;/fieldset&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Helpful Attributes</h3>

<div class="code-block">
<pre><code>&lt;!-- Placeholder (hint text) --&gt;
&lt;input type="email" placeholder="you@example.com"&gt;

&lt;!-- Autocomplete (browser fills automatically) --&gt;
&lt;input type="text" name="name" autocomplete="name"&gt;
&lt;input type="email" name="email" autocomplete="email"&gt;
&lt;input type="tel" name="phone" autocomplete="tel"&gt;

&lt;!-- Autofocus (cursor starts here) --&gt;
&lt;input type="text" autofocus&gt;

&lt;!-- Disabled and readonly --&gt;
&lt;input type="text" disabled value="Can't change"&gt;
&lt;input type="text" readonly value="Read only"&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use fieldset and legend to group related fields</li>
        <li>aria-describedby connects help text to inputs</li>
        <li>autocomplete speeds up form filling</li>
        <li>Placeholder is a hint, not a label replacement</li>
    </ul>
</div>
CONTENT;

$lessons[10] = <<<'CONTENT'
<h2>Advanced Form Features</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Level up your forms...</h4>
    <p>HTML5 brought powerful features that used to require JavaScript. Datalists, output elements, progress bars, and more — all built into HTML!</p>
</div>

<h3>Datalist (Autocomplete Suggestions)</h3>

<div class="code-block">
<pre><code>&lt;label for="country"&gt;Country:&lt;/label&gt;
&lt;input type="text" id="country" list="countries"&gt;
&lt;datalist id="countries"&gt;
    &lt;option value="Nigeria"&gt;
    &lt;option value="Kenya"&gt;
    &lt;option value="South Africa"&gt;
    &lt;option value="Ghana"&gt;
    &lt;option value="Egypt"&gt;
&lt;/datalist&gt;</code></pre>
</div>

<h3>Output Element</h3>

<div class="code-block">
<pre><code>&lt;form oninput="result.value = parseInt(a.value) + parseInt(b.value)"&gt;
    &lt;input type="number" id="a" value="0"&gt; +
    &lt;input type="number" id="b" value="0"&gt; =
    &lt;output name="result" for="a b"&gt;0&lt;/output&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Progress and Meter</h3>

<div class="code-block">
<pre><code>&lt;!-- Progress bar (loading, completion) --&gt;
&lt;label&gt;Course Progress:&lt;/label&gt;
&lt;progress value="70" max="100"&gt;70%&lt;/progress&gt;

&lt;!-- Meter (measurement within range) --&gt;
&lt;label&gt;Disk Usage:&lt;/label&gt;
&lt;meter value="0.7" min="0" max="1" low="0.3" high="0.8" optimum="0.5"&gt;
    70%
&lt;/meter&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>datalist provides autocomplete suggestions</li>
        <li>output displays calculated results</li>
        <li>progress shows task completion</li>
        <li>meter displays values within a known range</li>
    </ul>
</div>
CONTENT;

// Insert HTML Module 2 lessons
echo "Creating HTML Module 2 lessons...\n";
$module2Titles = ['', '', '', '', '', '', 'Introduction to HTML Forms', 'Input Types', 'Form Validation', 'Form Accessibility & UX', 'Advanced Form Features'];
for ($i = 6; $i <= 10; $i++) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 2, ?, ?, ?, 15, 1)");
    $stmt->execute([$i, $module2Titles[$i], $lessons[$i], $i - 5]);
}

// HTML Module 2 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (2, 2, 'Forms & Input Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(6, 2, 'Which attribute is required for form data to be sent?', 1),
(7, 2, 'What input type should be used for passwords?', 2),
(8, 2, 'Which attribute makes a field mandatory?', 3),
(9, 2, 'What groups related form fields together?', 4),
(10, 2, 'Which provides autocomplete suggestions?', 5)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(6, 'name', 1),(6, 'id', 0),(6, 'class', 0),(6, 'value', 0),
(7, 'password', 1),(7, 'text', 0),(7, 'hidden', 0),(7, 'secret', 0),
(8, 'required', 1),(8, 'mandatory', 0),(8, 'needed', 0),(8, 'must', 0),
(9, 'fieldset', 1),(9, 'div', 0),(9, 'section', 0),(9, 'group', 0),
(10, 'datalist', 1),(10, 'select', 0),(10, 'options', 0),(10, 'autocomplete', 0)");

// Continue with more modules...
// HTML Module 3 - Semantic HTML
echo "Creating HTML Module 3 lessons...\n";

$lessons[11] = <<<'CONTENT'
<h2>What is Semantic HTML?</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of semantic HTML as meaningful labels...</h4>
    <p>Imagine labeling boxes "Kitchen Stuff" vs "Box 1". The first tells you what's inside! Semantic HTML uses tags like &lt;nav&gt;, &lt;article&gt;, &lt;footer&gt; instead of generic &lt;div&gt; tags, telling browsers and screen readers what the content means.</p>
</div>

<h3>Non-Semantic vs Semantic</h3>

<div class="code-block">
<pre><code>&lt;!-- Non-Semantic (meaningless) --&gt;
&lt;div class="header"&gt;...&lt;/div&gt;
&lt;div class="nav"&gt;...&lt;/div&gt;
&lt;div class="content"&gt;...&lt;/div&gt;
&lt;div class="footer"&gt;...&lt;/div&gt;

&lt;!-- Semantic (meaningful) --&gt;
&lt;header&gt;...&lt;/header&gt;
&lt;nav&gt;...&lt;/nav&gt;
&lt;main&gt;...&lt;/main&gt;
&lt;footer&gt;...&lt;/footer&gt;</code></pre>
</div>

<h3>Why Semantic HTML Matters</h3>
<ul>
    <li><strong>Accessibility</strong> — Screen readers understand page structure</li>
    <li><strong>SEO</strong> — Search engines understand content better</li>
    <li><strong>Maintainability</strong> — Code is self-documenting</li>
    <li><strong>Consistency</strong> — Standard tags across all websites</li>
</ul>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Semantic tags describe their content's purpose</li>
        <li>Use header, nav, main, article, section, aside, footer</li>
        <li>Benefits: accessibility, SEO, maintainability</li>
    </ul>
</div>
CONTENT;

$lessons[12] = <<<'CONTENT'
<h2>Page Structure Elements</h2>

<h3>Complete Semantic Page</h3>

<div class="code-block">
<pre><code>&lt;body&gt;
    &lt;header&gt;
        &lt;h1&gt;Site Title&lt;/h1&gt;
        &lt;nav&gt;
            &lt;ul&gt;
                &lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;
                &lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;
                &lt;li&gt;&lt;a href="/contact"&gt;Contact&lt;/a&gt;&lt;/li&gt;
            &lt;/ul&gt;
        &lt;/nav&gt;
    &lt;/header&gt;
    
    &lt;main&gt;
        &lt;article&gt;
            &lt;h2&gt;Article Title&lt;/h2&gt;
            &lt;p&gt;Article content...&lt;/p&gt;
        &lt;/article&gt;
        
        &lt;aside&gt;
            &lt;h3&gt;Related Links&lt;/h3&gt;
        &lt;/aside&gt;
    &lt;/main&gt;
    
    &lt;footer&gt;
        &lt;p&gt;&amp;copy; 2024 HackathonAfrica&lt;/p&gt;
    &lt;/footer&gt;
&lt;/body&gt;</code></pre>
</div>

<h3>Element Reference</h3>
<table>
    <tr><th>Element</th><th>Purpose</th></tr>
    <tr><td>header</td><td>Introductory content, navigation</td></tr>
    <tr><td>nav</td><td>Navigation links</td></tr>
    <tr><td>main</td><td>Main content (one per page)</td></tr>
    <tr><td>article</td><td>Self-contained content</td></tr>
    <tr><td>section</td><td>Thematic grouping</td></tr>
    <tr><td>aside</td><td>Sidebar, related content</td></tr>
    <tr><td>footer</td><td>Footer content</td></tr>
</table>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Only ONE main element per page</li>
        <li>article is for standalone content (could be shared)</li>
        <li>section groups related content with a heading</li>
        <li>aside is for tangentially related content</li>
    </ul>
</div>
CONTENT;

$lessons[13] = <<<'CONTENT'
<h2>Lists and Tables</h2>

<h3>List Types</h3>

<div class="code-block">
<pre><code>&lt;!-- Unordered (bullet points) --&gt;
&lt;ul&gt;
    &lt;li&gt;HTML&lt;/li&gt;
    &lt;li&gt;CSS&lt;/li&gt;
    &lt;li&gt;JavaScript&lt;/li&gt;
&lt;/ul&gt;

&lt;!-- Ordered (numbered) --&gt;
&lt;ol&gt;
    &lt;li&gt;First step&lt;/li&gt;
    &lt;li&gt;Second step&lt;/li&gt;
    &lt;li&gt;Third step&lt;/li&gt;
&lt;/ol&gt;

&lt;!-- Description list --&gt;
&lt;dl&gt;
    &lt;dt&gt;HTML&lt;/dt&gt;
    &lt;dd&gt;Markup language for structure&lt;/dd&gt;
    &lt;dt&gt;CSS&lt;/dt&gt;
    &lt;dd&gt;Stylesheet language for design&lt;/dd&gt;
&lt;/dl&gt;</code></pre>
</div>

<h3>Accessible Tables</h3>

<div class="code-block">
<pre><code>&lt;table&gt;
    &lt;caption&gt;Course Completion Rates&lt;/caption&gt;
    &lt;thead&gt;
        &lt;tr&gt;
            &lt;th scope="col"&gt;Course&lt;/th&gt;
            &lt;th scope="col"&gt;Students&lt;/th&gt;
            &lt;th scope="col"&gt;Completion&lt;/th&gt;
        &lt;/tr&gt;
    &lt;/thead&gt;
    &lt;tbody&gt;
        &lt;tr&gt;
            &lt;td&gt;HTML&lt;/td&gt;
            &lt;td&gt;150&lt;/td&gt;
            &lt;td&gt;85%&lt;/td&gt;
        &lt;/tr&gt;
    &lt;/tbody&gt;
&lt;/table&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>ul for unordered, ol for ordered lists</li>
        <li>dl/dt/dd for term-definition pairs</li>
        <li>Tables need caption, thead, tbody for accessibility</li>
        <li>Use scope="col" or scope="row" on th elements</li>
    </ul>
</div>
CONTENT;

$lessons[14] = <<<'CONTENT'
<h2>Images and Media</h2>

<h3>Accessible Images</h3>

<div class="code-block">
<pre><code>&lt;!-- Basic image with alt text --&gt;
&lt;img src="photo.jpg" 
     alt="Team working at HackathonAfrica 2024"
     width="800" height="600"&gt;

&lt;!-- Decorative image (empty alt) --&gt;
&lt;img src="decoration.png" alt=""&gt;

&lt;!-- Figure with caption --&gt;
&lt;figure&gt;
    &lt;img src="chart.png" alt="Bar chart showing 80% completion rate"&gt;
    &lt;figcaption&gt;Course completion rates for Q4 2024&lt;/figcaption&gt;
&lt;/figure&gt;</code></pre>
</div>

<h3>Responsive Images</h3>

<div class="code-block">
<pre><code>&lt;!-- Different images for different screens --&gt;
&lt;picture&gt;
    &lt;source media="(min-width: 800px)" srcset="large.jpg"&gt;
    &lt;source media="(min-width: 400px)" srcset="medium.jpg"&gt;
    &lt;img src="small.jpg" alt="Responsive image example"&gt;
&lt;/picture&gt;

&lt;!-- Lazy loading (load when visible) --&gt;
&lt;img src="photo.jpg" alt="..." loading="lazy"&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Always include descriptive alt text</li>
        <li>Use empty alt="" for decorative images</li>
        <li>figure/figcaption for images with captions</li>
        <li>picture element for responsive images</li>
        <li>loading="lazy" improves performance</li>
    </ul>
</div>
CONTENT;

$lessons[15] = <<<'CONTENT'
<h2>ARIA and Accessibility</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of ARIA as translation...</h4>
    <p>ARIA helps translate your visual interface into information that assistive technologies can understand. It's like providing audio descriptions for a movie!</p>
</div>

<h3>Common ARIA Attributes</h3>

<div class="code-block">
<pre><code>&lt;!-- Label for icons/images --&gt;
&lt;button aria-label="Close menu"&gt;
    &lt;span class="icon"&gt;×&lt;/span&gt;
&lt;/button&gt;

&lt;!-- Describe element with another element --&gt;
&lt;input type="password" aria-describedby="pwd-help"&gt;
&lt;p id="pwd-help"&gt;Must be at least 8 characters&lt;/p&gt;

&lt;!-- Hide decorative content from screen readers --&gt;
&lt;span aria-hidden="true"&gt;🎉&lt;/span&gt;

&lt;!-- Live regions (announce changes) --&gt;
&lt;div aria-live="polite"&gt;
    Item added to cart!
&lt;/div&gt;</code></pre>
</div>

<h3>Accessibility Checklist</h3>
<ul>
    <li>All images have alt text (or alt="" if decorative)</li>
    <li>Form inputs have labels</li>
    <li>Color is not the only indicator</li>
    <li>Focus states are visible</li>
    <li>Page has proper heading hierarchy</li>
    <li>Interactive elements are keyboard accessible</li>
</ul>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>ARIA supplements HTML semantics</li>
        <li>aria-label provides accessible names</li>
        <li>aria-describedby links descriptions</li>
        <li>aria-hidden removes from accessibility tree</li>
        <li>Test with keyboard navigation and screen readers</li>
    </ul>
</div>
CONTENT;

// Insert Module 3 lessons
$module3Titles = ['', '', '', '', '', '', '', '', '', '', '', 'What is Semantic HTML?', 'Page Structure Elements', 'Lists and Tables', 'Images and Media', 'ARIA and Accessibility'];
for ($i = 11; $i <= 15; $i++) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 3, ?, ?, ?, 15, 1)");
    $stmt->execute([$i, $module3Titles[$i], $lessons[$i], $i - 10]);
}

// HTML Module 3 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (3, 3, 'Semantic HTML Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(11, 3, 'Which tag defines the main content area?', 1),
(12, 3, 'What provides accessible names for elements?', 2),
(13, 3, 'Which list type shows definitions?', 3)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(11, 'main', 1),(11, 'content', 0),(11, 'div', 0),(11, 'body', 0),
(12, 'aria-label', 1),(12, 'title', 0),(12, 'name', 0),(12, 'alt', 0),
(13, 'dl', 1),(13, 'ul', 0),(13, 'ol', 0),(13, 'list', 0)");

// HTML Module 4 - Advanced HTML
echo "Creating HTML Module 4 lessons...\n";

$lessons[16] = <<<'CONTENT'
<h2>HTML5 Media Elements</h2>

<h3>Video Element</h3>

<div class="code-block">
<pre><code>&lt;video controls width="640" height="360" poster="thumbnail.jpg"&gt;
    &lt;source src="video.mp4" type="video/mp4"&gt;
    &lt;source src="video.webm" type="video/webm"&gt;
    &lt;track src="captions.vtt" kind="captions" srclang="en" label="English"&gt;
    Your browser doesn't support video.
&lt;/video&gt;</code></pre>
</div>

<h3>Audio Element</h3>

<div class="code-block">
<pre><code>&lt;audio controls&gt;
    &lt;source src="audio.mp3" type="audio/mpeg"&gt;
    &lt;source src="audio.ogg" type="audio/ogg"&gt;
    Your browser doesn't support audio.
&lt;/audio&gt;</code></pre>
</div>

<h3>Video Attributes</h3>
<table>
    <tr><th>Attribute</th><th>Purpose</th></tr>
    <tr><td>controls</td><td>Show play/pause/volume</td></tr>
    <tr><td>autoplay</td><td>Start automatically (muted required)</td></tr>
    <tr><td>muted</td><td>Start with no sound</td></tr>
    <tr><td>loop</td><td>Repeat when finished</td></tr>
    <tr><td>poster</td><td>Image shown before playing</td></tr>
</table>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Provide multiple source formats for compatibility</li>
        <li>Always include controls for user accessibility</li>
        <li>Add captions with track element</li>
        <li>Use poster for video thumbnails</li>
    </ul>
</div>
CONTENT;

$lessons[17] = <<<'CONTENT'
<h2>Canvas and SVG</h2>

<h3>Canvas (Pixel Graphics)</h3>

<div class="code-block">
<pre><code>&lt;canvas id="myCanvas" width="400" height="300"&gt;
    Your browser doesn't support canvas.
&lt;/canvas&gt;

&lt;script&gt;
const canvas = document.getElementById('myCanvas');
const ctx = canvas.getContext('2d');
ctx.fillStyle = '#00FF66';
ctx.fillRect(50, 50, 200, 100);
&lt;/script&gt;</code></pre>
</div>

<h3>SVG (Vector Graphics)</h3>

<div class="code-block">
<pre><code>&lt;!-- Inline SVG --&gt;
&lt;svg width="100" height="100"&gt;
    &lt;circle cx="50" cy="50" r="40" fill="#00FF66"/&gt;
    &lt;text x="50" y="55" text-anchor="middle" fill="white"&gt;SVG&lt;/text&gt;
&lt;/svg&gt;

&lt;!-- External SVG --&gt;
&lt;img src="logo.svg" alt="Logo"&gt;</code></pre>
</div>

<h3>When to Use Each</h3>
<table>
    <tr><th>Canvas</th><th>SVG</th></tr>
    <tr><td>Games, animations</td><td>Icons, logos</td></tr>
    <tr><td>Many objects</td><td>Few objects</td></tr>
    <tr><td>Pixel manipulation</td><td>Scalable graphics</td></tr>
    <tr><td>Not accessible</td><td>Accessible with ARIA</td></tr>
</table>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Canvas is for complex, dynamic graphics</li>
        <li>SVG is for scalable, accessible graphics</li>
        <li>SVG can be styled with CSS</li>
        <li>Canvas requires JavaScript to draw</li>
    </ul>
</div>
CONTENT;

$lessons[18] = <<<'CONTENT'
<h2>Meta Tags and SEO</h2>

<h3>Essential Meta Tags</h3>

<div class="code-block">
<pre><code>&lt;head&gt;
    &lt;!-- Character encoding --&gt;
    &lt;meta charset="UTF-8"&gt;
    
    &lt;!-- Responsive viewport --&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    
    &lt;!-- SEO --&gt;
    &lt;title&gt;Page Title | Brand Name&lt;/title&gt;
    &lt;meta name="description" content="155 characters describing the page"&gt;
    &lt;meta name="robots" content="index, follow"&gt;
    
    &lt;!-- Open Graph (Social sharing) --&gt;
    &lt;meta property="og:title" content="Page Title"&gt;
    &lt;meta property="og:description" content="Description for social"&gt;
    &lt;meta property="og:image" content="https://example.com/image.jpg"&gt;
    &lt;meta property="og:url" content="https://example.com/page"&gt;
    
    &lt;!-- Twitter Card --&gt;
    &lt;meta name="twitter:card" content="summary_large_image"&gt;
&lt;/head&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Title should be 50-60 characters</li>
        <li>Description should be 150-160 characters</li>
        <li>Open Graph controls how links appear on social media</li>
        <li>Every page should have unique title and description</li>
    </ul>
</div>
CONTENT;

$lessons[19] = <<<'CONTENT'
<h2>Web Components Basics</h2>

<h3>Custom Elements</h3>

<div class="code-block">
<pre><code>&lt;!-- Using a custom element --&gt;
&lt;user-card name="Amara" role="Developer"&gt;&lt;/user-card&gt;

&lt;script&gt;
class UserCard extends HTMLElement {
    connectedCallback() {
        const name = this.getAttribute('name');
        const role = this.getAttribute('role');
        this.innerHTML = `
            &lt;div class="card"&gt;
                &lt;h3&gt;${name}&lt;/h3&gt;
                &lt;p&gt;${role}&lt;/p&gt;
            &lt;/div&gt;
        `;
    }
}
customElements.define('user-card', UserCard);
&lt;/script&gt;</code></pre>
</div>

<h3>Template Element</h3>

<div class="code-block">
<pre><code>&lt;template id="card-template"&gt;
    &lt;div class="card"&gt;
        &lt;h3&gt;&lt;slot name="title"&gt;Default Title&lt;/slot&gt;&lt;/h3&gt;
        &lt;p&gt;&lt;slot name="content"&gt;Default content&lt;/slot&gt;&lt;/p&gt;
    &lt;/div&gt;
&lt;/template&gt;</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Custom elements let you create reusable components</li>
        <li>Template element holds markup that's not rendered</li>
        <li>Slots allow content projection</li>
        <li>Great for component-based architecture</li>
    </ul>
</div>
CONTENT;

$lessons[20] = <<<'CONTENT'
<h2>Performance Optimization</h2>

<h3>Resource Loading</h3>

<div class="code-block">
<pre><code>&lt;!-- Preload critical resources --&gt;
&lt;link rel="preload" href="critical.css" as="style"&gt;
&lt;link rel="preload" href="hero.jpg" as="image"&gt;

&lt;!-- Prefetch resources for next page --&gt;
&lt;link rel="prefetch" href="next-page.html"&gt;

&lt;!-- DNS prefetch for external domains --&gt;
&lt;link rel="dns-prefetch" href="https://fonts.googleapis.com"&gt;

&lt;!-- Async/defer for scripts --&gt;
&lt;script src="analytics.js" async&gt;&lt;/script&gt;
&lt;script src="app.js" defer&gt;&lt;/script&gt;</code></pre>
</div>

<h3>Image Optimization</h3>

<div class="code-block">
<pre><code>&lt;!-- Lazy load images below the fold --&gt;
&lt;img src="photo.jpg" loading="lazy" alt="..."&gt;

&lt;!-- Responsive images --&gt;
&lt;img srcset="small.jpg 400w, medium.jpg 800w, large.jpg 1200w"
     sizes="(max-width: 600px) 400px, (max-width: 1000px) 800px, 1200px"
     src="medium.jpg" alt="..."&gt;</code></pre>
</div>

<h3>async vs defer</h3>
<table>
    <tr><th>Attribute</th><th>When Executes</th><th>Order</th></tr>
    <tr><td>None</td><td>Blocks HTML parsing</td><td>In order</td></tr>
    <tr><td>async</td><td>When downloaded</td><td>Any order</td></tr>
    <tr><td>defer</td><td>After HTML parsed</td><td>In order</td></tr>
</table>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Preload critical resources</li>
        <li>Use loading="lazy" for below-fold images</li>
        <li>Use defer for most scripts</li>
        <li>Use async only for independent scripts</li>
        <li>Optimize images with srcset for responsive sizes</li>
    </ul>
</div>
CONTENT;

// Insert Module 4 lessons
$module4Titles = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'HTML5 Media Elements', 'Canvas and SVG', 'Meta Tags and SEO', 'Web Components Basics', 'Performance Optimization'];
for ($i = 16; $i <= 20; $i++) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 4, ?, ?, ?, 15, 1)");
    $stmt->execute([$i, $module4Titles[$i], $lessons[$i], $i - 15]);
}

// HTML Module 4 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (4, 4, 'Advanced HTML Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(14, 4, 'Which attribute delays script execution until HTML is parsed?', 1),
(15, 4, 'What improves image loading performance?', 2)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(14, 'defer', 1),(14, 'async', 0),(14, 'delay', 0),(14, 'wait', 0),
(15, 'loading=\"lazy\"', 1),(15, 'load=\"later\"', 0),(15, 'lazy=\"true\"', 0),(15, 'defer', 0)");

// Final Exams are created at the end of this file with full questions

echo "HTML course complete! (4 modules, 20 lessons)\n\n";

// ╔═══════════════════════════════════════════════════════════════╗
// ║                CSS COURSE - 4 MODULES                           ║
// ╚═══════════════════════════════════════════════════════════════╝

echo "Creating CSS modules...\n";

$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(5, 2, 'Selectors, Box Model & Typography', 'Master CSS selectors, understand the box model, and create beautiful typography.', 1),
(6, 2, 'Layout — Flexbox & Grid', 'Modern CSS layout techniques for responsive, flexible designs.', 2),
(7, 2, 'Animations, Transitions & Effects', 'Bring your designs to life with CSS animations and visual effects.', 3),
(8, 2, 'Advanced CSS Architecture', 'CSS variables, methodologies, and scalable architecture patterns.', 4)");

// CSS Module 1 Lessons
$cssLessons = [];

$cssLessons[21] = <<<'CONTENT'
<h2>CSS Selectors</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of selectors as addresses...</h4>
    <p>Selectors tell CSS which elements to style. Like addressing a letter: "Everyone in Lagos" (element selector), "Person at 123 Main St" (ID), or "All doctors" (class).</p>
</div>

<h3>Basic Selectors</h3>

<div class="code-block">
<pre><code>/* Element selector */
p { color: blue; }

/* Class selector (reusable) */
.highlight { background: yellow; }

/* ID selector (unique) */
#header { height: 80px; }

/* Universal selector */
* { box-sizing: border-box; }</code></pre>
</div>

<h3>Combinators</h3>

<div class="code-block">
<pre><code>/* Descendant (space) - any nested */
nav a { color: white; }

/* Child (>) - direct children only */
ul > li { margin: 10px; }

/* Adjacent sibling (+) - immediately after */
h2 + p { margin-top: 0; }

/* General sibling (~) - any sibling after */
h2 ~ p { color: gray; }</code></pre>
</div>

<h3>Attribute Selectors</h3>

<div class="code-block">
<pre><code>[type="email"] { border-color: blue; }
[href^="https"] { color: green; }  /* starts with */
[href$=".pdf"] { color: red; }     /* ends with */
[class*="btn"] { cursor: pointer; } /* contains */</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Classes (.class) for reusable styles</li>
        <li>IDs (#id) for unique elements</li>
        <li>Combinators connect selectors</li>
        <li>Attribute selectors target by attributes</li>
    </ul>
</div>
CONTENT;

$cssLessons[22] = <<<'CONTENT'
<h2>The Box Model</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Every element is a box...</h4>
    <p>Like a picture frame: content (the picture), padding (matting), border (the frame), margin (space between frames on the wall).</p>
</div>

<h3>Box Model Properties</h3>

<div class="code-block">
<pre><code>.box {
    /* Content size */
    width: 300px;
    height: 200px;
    
    /* Padding (inside border) */
    padding: 20px;
    padding: 10px 20px;           /* vertical | horizontal */
    padding: 10px 20px 30px 40px; /* top right bottom left */
    
    /* Border */
    border: 2px solid #00FF66;
    border-radius: 8px;
    
    /* Margin (outside border) */
    margin: 20px;
    margin: 0 auto; /* center horizontally */
}</code></pre>
</div>

<h3>Box Sizing</h3>

<div class="code-block">
<pre><code>/* Default: padding/border ADD to width */
.box { width: 300px; padding: 20px; }
/* Total width: 300 + 20 + 20 = 340px */

/* Better: padding/border INCLUDED in width */
*, *::before, *::after {
    box-sizing: border-box;
}
.box { width: 300px; padding: 20px; }
/* Total width: 300px exactly */</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Content + Padding + Border + Margin = Total space</li>
        <li>Always use box-sizing: border-box</li>
        <li>margin: 0 auto centers block elements</li>
        <li>Vertical margins collapse</li>
    </ul>
</div>
CONTENT;

$cssLessons[23] = <<<'CONTENT'
<h2>Typography</h2>

<h3>Font Properties</h3>

<div class="code-block">
<pre><code>body {
    /* Font family with fallbacks */
    font-family: 'Inter', -apple-system, sans-serif;
    
    /* Size */
    font-size: 16px;      /* absolute */
    font-size: 1rem;      /* relative to root */
    font-size: 1.2em;     /* relative to parent */
    
    /* Weight */
    font-weight: 400;     /* normal */
    font-weight: 700;     /* bold */
    
    /* Style */
    font-style: italic;
    
    /* Line height */
    line-height: 1.6;     /* unitless recommended */
}

h1 {
    /* Letter and word spacing */
    letter-spacing: -0.02em;
    word-spacing: 0.1em;
    
    /* Transform */
    text-transform: uppercase;
}</code></pre>
</div>

<h3>Text Properties</h3>

<div class="code-block">
<pre><code>p {
    text-align: left;     /* left, right, center, justify */
    text-decoration: none; /* underline, line-through */
    text-indent: 2em;     /* first line indent */
    white-space: nowrap;  /* prevent wrapping */
    overflow: hidden;
    text-overflow: ellipsis; /* ... when truncated */
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use rem for font sizes (scales with user preferences)</li>
        <li>Unitless line-height (1.5-1.7) for readability</li>
        <li>Always include fallback fonts</li>
        <li>text-overflow: ellipsis needs overflow: hidden</li>
    </ul>
</div>
CONTENT;

$cssLessons[24] = <<<'CONTENT'
<h2>Colors and Backgrounds</h2>

<h3>Color Formats</h3>

<div class="code-block">
<pre><code>/* Named colors */
color: red;
color: rebeccapurple;

/* Hexadecimal */
color: #FF0000;         /* red */
color: #F00;            /* shorthand */
color: #FF000080;       /* with alpha */

/* RGB / RGBA */
color: rgb(255, 0, 0);
color: rgba(255, 0, 0, 0.5);

/* HSL (Hue, Saturation, Lightness) */
color: hsl(0, 100%, 50%);    /* red */
color: hsla(0, 100%, 50%, 0.5);

/* Modern: oklch (better for gradients) */
color: oklch(70% 0.15 150);</code></pre>
</div>

<h3>Backgrounds</h3>

<div class="code-block">
<pre><code>.hero {
    background-color: #0A0A0A;
    background-image: url('bg.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    
    /* Shorthand */
    background: #0A0A0A url('bg.jpg') center/cover no-repeat fixed;
    
    /* Gradient */
    background: linear-gradient(to right, #00FF66, #0066FF);
    background: radial-gradient(circle, #00FF66, transparent);
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>HSL is intuitive for color adjustments</li>
        <li>Use alpha channel for transparency</li>
        <li>background-size: cover fills container</li>
        <li>Gradients can replace images for performance</li>
    </ul>
</div>
CONTENT;

$cssLessons[25] = <<<'CONTENT'
<h2>Pseudo-classes and Pseudo-elements</h2>

<h3>Pseudo-classes (State)</h3>

<div class="code-block">
<pre><code>/* Interactive states */
a:hover { color: #00FF66; }
a:active { color: #00CC52; }
button:focus { outline: 2px solid #00FF66; }
input:disabled { opacity: 0.5; }

/* Structural */
li:first-child { font-weight: bold; }
li:last-child { border: none; }
tr:nth-child(even) { background: #f5f5f5; }
tr:nth-child(odd) { background: white; }
p:nth-of-type(2) { color: blue; }

/* Form states */
input:valid { border-color: green; }
input:invalid { border-color: red; }
input:required { border-left: 3px solid red; }</code></pre>
</div>

<h3>Pseudo-elements (Content)</h3>

<div class="code-block">
<pre><code>/* First letter/line */
p::first-letter { font-size: 2em; }
p::first-line { font-weight: bold; }

/* Before/after (generated content) */
.required::after {
    content: " *";
    color: red;
}

.quote::before {
    content: open-quote;
}

/* Selection styling */
::selection {
    background: #00FF66;
    color: black;
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Pseudo-classes use single colon (:hover)</li>
        <li>Pseudo-elements use double colon (::before)</li>
        <li>::before and ::after need content property</li>
        <li>:nth-child is powerful for patterns</li>
    </ul>
</div>
CONTENT;

// Insert CSS Module 1 lessons
echo "Creating CSS Module 1 lessons...\n";
$cssModule1Titles = [21 => 'CSS Selectors', 22 => 'The Box Model', 23 => 'Typography', 24 => 'Colors and Backgrounds', 25 => 'Pseudo-classes and Pseudo-elements'];
foreach ($cssModule1Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 5, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $cssLessons[$id], $id - 20]);
}

// CSS Module 1 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (5, 5, 'Selectors & Box Model Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(16, 5, 'Which selector targets elements with a specific class?', 1),
(17, 5, 'What property includes padding in the element width?', 2),
(18, 5, 'Which pseudo-class styles an element on mouse hover?', 3)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(16, '.classname', 1),(16, '#classname', 0),(16, '*classname', 0),(16, '@classname', 0),
(17, 'box-sizing: border-box', 1),(17, 'padding: include', 0),(17, 'width: total', 0),(17, 'box-model: border', 0),
(18, ':hover', 1),(18, '::hover', 0),(18, ':mouse', 0),(18, ':over', 0)");

// CSS Module 2 - Layout
echo "Creating CSS Module 2 lessons...\n";

$cssLessons[26] = <<<'CONTENT'
<h2>Flexbox Fundamentals</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of Flexbox like arranging books on a shelf...</h4>
    <p>The shelf is the flex container, books are flex items. You can spread them out, push to one side, center them, or even reorder them — all without touching the books individually!</p>
</div>

<h3>Container Properties</h3>

<div class="code-block">
<pre><code>.container {
    display: flex;
    
    /* Direction */
    flex-direction: row;        /* default */
    flex-direction: column;
    flex-direction: row-reverse;
    
    /* Main axis alignment */
    justify-content: flex-start;
    justify-content: flex-end;
    justify-content: center;
    justify-content: space-between;
    justify-content: space-evenly;
    
    /* Cross axis alignment */
    align-items: stretch;       /* default */
    align-items: flex-start;
    align-items: center;
    align-items: flex-end;
    
    /* Wrapping */
    flex-wrap: wrap;
    
    /* Gap between items */
    gap: 20px;
}</code></pre>
</div>

<h3>Perfect Centering</h3>

<div class="code-block">
<pre><code>.centered {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>display: flex creates a flex container</li>
        <li>justify-content = main axis</li>
        <li>align-items = cross axis</li>
        <li>gap is cleaner than margins</li>
    </ul>
</div>
CONTENT;

$cssLessons[27] = <<<'CONTENT'
<h2>Flex Item Properties</h2>

<h3>Controlling Individual Items</h3>

<div class="code-block">
<pre><code>.item {
    /* Grow to fill space */
    flex-grow: 1;
    
    /* Shrink if needed */
    flex-shrink: 1;
    
    /* Initial size */
    flex-basis: 200px;
    
    /* Shorthand: grow shrink basis */
    flex: 1;           /* grow equally */
    flex: 0 0 200px;   /* don't grow/shrink, fixed width */
    flex: 1 1 auto;    /* default */
    
    /* Override alignment */
    align-self: flex-end;
    
    /* Change order */
    order: -1;  /* move to front */
}</code></pre>
</div>

<h3>Common Patterns</h3>

<div class="code-block">
<pre><code>/* Navigation with logo left, links right */
nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Equal width cards */
.card {
    flex: 1 1 300px;
    min-width: 0; /* prevent overflow */
}

/* Sticky footer */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
main { flex: 1; }</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>flex: 1 makes items grow equally</li>
        <li>flex-basis sets the ideal size</li>
        <li>order changes visual order (not DOM)</li>
        <li>min-width: 0 prevents flex item overflow</li>
    </ul>
</div>
CONTENT;

$cssLessons[28] = <<<'CONTENT'
<h2>CSS Grid Fundamentals</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of Grid like a spreadsheet...</h4>
    <p>Grid creates rows and columns like Excel. You can place items in specific cells, span multiple cells, and create complex layouts that Flexbox can't easily achieve.</p>
</div>

<h3>Creating a Grid</h3>

<div class="code-block">
<pre><code>.container {
    display: grid;
    
    /* Define columns */
    grid-template-columns: 200px 1fr 200px;
    grid-template-columns: repeat(3, 1fr);
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    
    /* Define rows */
    grid-template-rows: auto 1fr auto;
    
    /* Gap */
    gap: 20px;
    row-gap: 20px;
    column-gap: 10px;
}</code></pre>
</div>

<h3>Placing Items</h3>

<div class="code-block">
<pre><code>.item {
    /* Span columns */
    grid-column: 1 / 3;      /* start / end */
    grid-column: span 2;     /* span 2 columns */
    
    /* Span rows */
    grid-row: 1 / 3;
    
    /* Shorthand */
    grid-area: 1 / 1 / 3 / 3; /* row-start/col-start/row-end/col-end */
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Grid is for 2D layouts (rows AND columns)</li>
        <li>fr unit distributes free space</li>
        <li>minmax() creates responsive columns</li>
        <li>auto-fit/auto-fill for responsive grids</li>
    </ul>
</div>
CONTENT;

$cssLessons[29] = <<<'CONTENT'
<h2>Grid Template Areas</h2>

<h3>Named Areas</h3>

<div class="code-block">
<pre><code>.layout {
    display: grid;
    grid-template-areas:
        "header header header"
        "sidebar main aside"
        "footer footer footer";
    grid-template-columns: 200px 1fr 200px;
    grid-template-rows: auto 1fr auto;
    min-height: 100vh;
}

header { grid-area: header; }
.sidebar { grid-area: sidebar; }
main { grid-area: main; }
aside { grid-area: aside; }
footer { grid-area: footer; }</code></pre>
</div>

<h3>Responsive with Areas</h3>

<div class="code-block">
<pre><code>/* Mobile: stack everything */
@media (max-width: 768px) {
    .layout {
        grid-template-areas:
            "header"
            "main"
            "sidebar"
            "aside"
            "footer";
        grid-template-columns: 1fr;
    }
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Template areas create visual layout maps</li>
        <li>Use . for empty cells</li>
        <li>Great for responsive layout changes</li>
        <li>Names make CSS self-documenting</li>
    </ul>
</div>
CONTENT;

$cssLessons[30] = <<<'CONTENT'
<h2>Responsive Design</h2>

<h3>Media Queries</h3>

<div class="code-block">
<pre><code>/* Mobile first approach */
.container {
    padding: 1rem;
}

/* Tablet */
@media (min-width: 768px) {
    .container {
        padding: 2rem;
        max-width: 720px;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .container {
        max-width: 960px;
    }
}

/* Large desktop */
@media (min-width: 1280px) {
    .container {
        max-width: 1200px;
    }
}</code></pre>
</div>

<h3>Responsive Units</h3>

<div class="code-block">
<pre><code>/* Viewport units */
width: 100vw;    /* viewport width */
height: 100vh;   /* viewport height */
font-size: 5vw;  /* scales with viewport */

/* Clamped values */
font-size: clamp(1rem, 2.5vw, 2rem);
width: clamp(300px, 50%, 600px);

/* Container queries (modern) */
@container (min-width: 400px) {
    .card { flex-direction: row; }
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Mobile-first: start small, add breakpoints</li>
        <li>clamp() creates fluid typography</li>
        <li>Use rem for scalable sizing</li>
        <li>Container queries respond to parent size</li>
    </ul>
</div>
CONTENT;

// Insert CSS Module 2 lessons
$cssModule2Titles = [26 => 'Flexbox Fundamentals', 27 => 'Flex Item Properties', 28 => 'CSS Grid Fundamentals', 29 => 'Grid Template Areas', 30 => 'Responsive Design'];
foreach ($cssModule2Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 6, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $cssLessons[$id], $id - 25]);
}

// CSS Module 2 Quiz
$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (6, 6, 'Layout Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(19, 6, 'Which property aligns flex items on the main axis?', 1),
(20, 6, 'What unit distributes free space in Grid?', 2)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(19, 'justify-content', 1),(19, 'align-items', 0),(19, 'align-content', 0),(19, 'justify-items', 0),
(20, 'fr', 1),(20, 'px', 0),(20, 'em', 0),(20, '%', 0)");

// CSS Module 3 - Animations (simplified for brevity)
echo "Creating CSS Module 3 lessons...\n";

$cssLessons[31] = <<<'CONTENT'
<h2>CSS Transitions</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of transitions as slow-motion replays...</h4>
    <p>Without transitions, changes happen instantly — like flipping a light switch. With transitions, changes animate smoothly — like a dimmer switch that gradually adjusts brightness.</p>
</div>

<p>Transitions let you animate property changes over time. When a property changes (e.g., on hover), the browser smoothly animates from old value to new value.</p>

<h3>The Transition Shorthand</h3>

<div class="code-block">
<pre><code>/* transition: property duration timing-function delay */
.button {
    background: #333;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.button:hover {
    background: #00FF66;
    color: #0A0A0A;
    transform: scale(1.05);
}</code></pre>
</div>

<h3>Targeting Specific Properties</h3>

<div class="code-block">
<pre><code>/* Better performance: only transition what changes */
.card {
    transition: transform 0.3s ease, 
                box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}</code></pre>
</div>

<h3>Timing Functions</h3>

<div class="code-block">
<pre><code>transition: all 0.3s ease;        /* slow start & end */
transition: all 0.3s ease-in;     /* slow start */
transition: all 0.3s ease-out;    /* slow end */
transition: all 0.3s linear;      /* constant speed */
transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55); /* bounce */</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Using "all" for everything</strong> — Transition only the properties that change for better performance</li>
        <li><strong>Transitioning display</strong> — <code>display: none</code> to <code>display: block</code> cannot be transitioned. Use opacity + visibility instead</li>
        <li><strong>Too slow transitions</strong> — Anything over 0.5s feels sluggish. Keep it between 0.2s-0.4s</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>transition: property duration timing-function delay</li>
        <li>Target specific properties for better performance</li>
        <li>ease is the best default timing function</li>
        <li>Keep durations between 0.2s-0.4s for UI interactions</li>
    </ul>
</div>
CONTENT;

$cssLessons[32] = <<<'CONTENT'
<h2>CSS Animations</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of animations as a movie script...</h4>
    <p>Transitions are like two photos (before and after). Animations are like a full movie — you define keyframes (scenes) and the browser plays through them in order.</p>
</div>

<p>CSS animations give you more control than transitions. You define keyframes with intermediate steps, and the browser interpolates between them.</p>

<h3>@keyframes — Defining the Animation</h3>

<div class="code-block">
<pre><code>/* Simple two-step animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Multi-step animation */
@keyframes pulse {
    0%   { transform: scale(1); }
    50%  { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* Apply the animation */
.element {
    animation: fadeIn 0.5s ease forwards;
}

.heart {
    animation: pulse 1s ease infinite;
}</code></pre>
</div>

<h3>Animation Properties</h3>

<div class="code-block">
<pre><code>.element {
    animation-name: fadeIn;
    animation-duration: 0.5s;
    animation-timing-function: ease;
    animation-delay: 0.2s;
    animation-iteration-count: 1;     /* or infinite */
    animation-direction: normal;       /* or reverse, alternate */
    animation-fill-mode: forwards;     /* keeps end state */
    
    /* Shorthand */
    animation: fadeIn 0.5s ease 0.2s 1 normal forwards;
}</code></pre>
</div>

<h3>Practical Example: Loading Spinner</h3>

<div class="code-block">
<pre><code>@keyframes spin {
    to { transform: rotate(360deg); }
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #333;
    border-top-color: #00FF66;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Forgetting fill-mode</strong> — Without <code>forwards</code>, the element snaps back to its original state after the animation ends</li>
        <li><strong>Too many animations</strong> — Excessive animations cause performance issues and distract users</li>
        <li><strong>Animating expensive properties</strong> — Stick to <code>transform</code> and <code>opacity</code> for smooth 60fps animations</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>@keyframes defines animation steps (from/to or percentages)</li>
        <li>animation property applies it to elements</li>
        <li>Use forwards fill-mode to keep the end state</li>
        <li>Only animate transform and opacity for performance</li>
    </ul>
</div>
CONTENT;

$cssLessons[33] = <<<'CONTENT'
<h2>Transforms</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of transforms as photo editing...</h4>
    <p>Just like you can rotate, zoom, or move a photo in an editor, CSS transforms let you rotate, scale, translate, and skew HTML elements — without affecting the page layout around them.</p>
</div>

<h3>Transform Functions</h3>

<div class="code-block">
<pre><code>/* Move element */
transform: translateX(50px);       /* right */
transform: translateY(-20px);      /* up */
transform: translate(50px, -20px); /* both */

/* Rotate */
transform: rotate(45deg);    /* clockwise */
transform: rotate(-90deg);   /* counterclockwise */

/* Scale */
transform: scale(1.5);       /* 150% size */
transform: scale(0.5);       /* 50% size */
transform: scaleX(2);        /* stretch horizontally */

/* Skew */
transform: skew(10deg, 5deg);</code></pre>
</div>

<h3>Combining Transforms</h3>

<div class="code-block">
<pre><code>/* Chain multiple transforms */
.card:hover {
    transform: translateY(-10px) rotate(2deg) scale(1.02);
}

/* Order matters! */
transform: rotate(45deg) translateX(100px);
/* vs */
transform: translateX(100px) rotate(45deg);
/* These produce DIFFERENT results! */</code></pre>
</div>

<h3>Transform Origin</h3>

<div class="code-block">
<pre><code>/* Change the pivot point */
.element {
    transform-origin: center;     /* default */
    transform-origin: top left;   /* rotate from corner */
    transform-origin: 50% 100%;   /* bottom center */
}</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Forgetting transform order matters</strong> — Transforms apply right to left. rotate then translate is different from translate then rotate</li>
        <li><strong>Using transforms on inline elements</strong> — Transforms only work on block or inline-block elements</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>translate, rotate, scale, skew are the 4 transform functions</li>
        <li>transform-origin sets the pivot point</li>
        <li>Transforms are GPU-accelerated — great for performance</li>
        <li>Order of transforms matters!</li>
    </ul>
</div>
CONTENT;

$cssLessons[34] = <<<'CONTENT'
<h2>Shadows and Effects</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Shadows create depth...</h4>
    <p>In the real world, objects cast shadows when light hits them. In CSS, shadows make flat elements look 3D — cards appear to float above the page, buttons look pressable.</p>
</div>

<h3>Box Shadow</h3>

<div class="code-block">
<pre><code>/* box-shadow: x-offset y-offset blur spread color */
.card {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);  /* subtle */
}

.card-elevated {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); /* elevated */
}

.card-floating {
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); /* dramatic */
}

/* Multiple shadows for realistic depth */
.button {
    box-shadow: 
        0 1px 3px rgba(0,0,0,0.12),
        0 4px 12px rgba(0,0,0,0.08);
}

/* Inner shadow (inset) */
.input:focus {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}</code></pre>
</div>

<h3>Text Shadow</h3>

<div class="code-block">
<pre><code>h1 {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

/* Neon glow effect */
.neon {
    text-shadow: 
        0 0 7px #00FF66,
        0 0 20px #00FF66,
        0 0 40px #00FF66;
}</code></pre>
</div>

<h3>Backdrop Filter (Glassmorphism)</h3>

<div class="code-block">
<pre><code>/* The trendy frosted glass effect */
.glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>box-shadow: x y blur spread color</li>
        <li>Multiple shadows create realistic depth</li>
        <li>backdrop-filter: blur() creates glassmorphism</li>
        <li>Use rgba() for semi-transparent shadow colors</li>
    </ul>
</div>
CONTENT;

$cssLessons[35] = <<<'CONTENT'
<h2>Filters and Blend Modes</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of Instagram filters...</h4>
    <p>CSS filters work just like Instagram filters — grayscale, blur, brightness, contrast. Blend modes are like Photoshop layer blending — they control how overlapping elements combine visually.</p>
</div>

<h3>CSS Filters</h3>

<div class="code-block">
<pre><code>/* Individual filters */
img { filter: grayscale(100%); }      /* black & white */
img { filter: blur(5px); }            /* blur effect */
img { filter: brightness(1.3); }      /* brighter */
img { filter: contrast(1.5); }        /* more contrast */
img { filter: saturate(2); }          /* vivid colors */
img { filter: sepia(80%); }           /* vintage look */
img { filter: hue-rotate(90deg); }    /* shift colors */

/* Combine filters */
img {
    filter: grayscale(50%) brightness(1.1) contrast(1.2);
}

/* Hover to reveal color */
img {
    filter: grayscale(100%);
    transition: filter 0.3s ease;
}
img:hover {
    filter: grayscale(0%);
}</code></pre>
</div>

<h3>Blend Modes</h3>

<div class="code-block">
<pre><code>/* mix-blend-mode: how element blends with background */
.overlay-text {
    mix-blend-mode: multiply;    /* darken */
    mix-blend-mode: screen;      /* lighten */
    mix-blend-mode: overlay;     /* contrast boost */
    mix-blend-mode: difference;  /* invert colors */
}

/* background-blend-mode: for background layers */
.hero {
    background-image: url(photo.jpg);
    background-color: #00FF66;
    background-blend-mode: multiply;
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>filter: blur, grayscale, brightness, contrast, saturate, sepia</li>
        <li>Combine filters for creative effects</li>
        <li>mix-blend-mode for element layer effects</li>
        <li>background-blend-mode for background layer effects</li>
    </ul>
</div>
CONTENT;

$cssModule3Titles = [31 => 'CSS Transitions', 32 => 'CSS Animations', 33 => 'Transforms', 34 => 'Shadows and Effects', 35 => 'Filters and Blend Modes'];
foreach ($cssModule3Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 7, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $cssLessons[$id], $id - 30]);
}

$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (7, 7, 'Animations Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES (21, 7, 'Which defines animation steps?', 1)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (21, '@keyframes', 1),(21, '@animation', 0),(21, '@steps', 0),(21, '@transition', 0)");

// CSS Module 4 - Architecture
echo "Creating CSS Module 4 lessons...\n";

$cssLessons[36] = <<<'CONTENT'
<h2>CSS Variables (Custom Properties)</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Variables are like paint swatches...</h4>
    <p>Instead of memorizing the exact hex code (#00FF66) every time, you give it a name (--primary). Now you reference the name everywhere. Change the swatch once, and every room that uses it updates automatically!</p>
</div>

<h3>Defining and Using Variables</h3>

<div class="code-block">
<pre><code>/* Define variables in :root (global scope) */
:root {
    --primary: #00FF66;
    --bg-dark: #0A0A0A;
    --bg-card: #1A1A1A;
    --text: #FFFFFF;
    --text-muted: #999999;
    --radius: 12px;
    --shadow: 0 4px 20px rgba(0,0,0,0.3);
}

/* Use with var() */
.button {
    background: var(--primary);
    color: var(--bg-dark);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

/* Fallback values */
.element {
    color: var(--accent, #FF6600); /* uses #FF6600 if --accent is not defined */
}</code></pre>
</div>

<h3>Theming with Variables</h3>

<div class="code-block">
<pre><code>/* Dark theme (default) */
:root {
    --bg: #0A0A0A;
    --text: #FFFFFF;
}

/* Light theme override */
[data-theme="light"] {
    --bg: #FFFFFF;
    --text: #0A0A0A;
}

/* Components automatically adapt */
body {
    background: var(--bg);
    color: var(--text);
}</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Forgetting the double dash</strong> — Variables must start with <code>--</code></li>
        <li><strong>Not using :root</strong> — Variables defined on specific elements are not available globally</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Define in :root for global access</li>
        <li>Use var(--name) to apply</li>
        <li>var(--name, fallback) provides defaults</li>
        <li>Perfect for theming (dark/light modes)</li>
    </ul>
</div>
CONTENT;

$cssLessons[37] = <<<'CONTENT'
<h2>CSS Methodologies (BEM)</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of BEM as a naming convention for a school...</h4>
    <p>Block = the school itself (card). Element = rooms inside the school (card__title, card__image). Modifier = versions of the school (card--featured, card--compact). This naming system prevents confusion when the school gets bigger!</p>
</div>

<p>As your CSS grows, class names become chaotic. BEM (Block Element Modifier) is a naming convention that keeps things organized and predictable.</p>

<h3>BEM Structure</h3>

<div class="code-block">
<pre><code>/* Block: standalone component */
.card {}

/* Element: part of the block (double underscore) */
.card__title {}
.card__image {}
.card__body {}
.card__button {}

/* Modifier: variation (double dash) */
.card--featured {}
.card--compact {}
.card__button--primary {}
.card__button--disabled {}</code></pre>
</div>

<h3>BEM in Practice</h3>

<div class="code-block">
<pre><code>&lt;div class="card card--featured"&gt;
    &lt;img class="card__image" src="photo.jpg"&gt;
    &lt;div class="card__body"&gt;
        &lt;h3 class="card__title"&gt;Learn CSS&lt;/h3&gt;
        &lt;p class="card__text"&gt;Master modern styling&lt;/p&gt;
        &lt;button class="card__button card__button--primary"&gt;
            Enroll Now
        &lt;/button&gt;
    &lt;/div&gt;
&lt;/div&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Nesting elements too deep</strong> — Never do <code>.card__body__title</code>. Keep it flat: <code>.card__title</code></li>
        <li><strong>Using BEM for everything</strong> — Utility classes (margin, padding helpers) do not need BEM naming</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>BEM = Block__Element--Modifier</li>
        <li>Creates predictable, conflict-free class names</li>
        <li>Avoids specificity wars and deep nesting</li>
        <li>Keep element names flat (never nest BEM elements)</li>
    </ul>
</div>
CONTENT;

$cssLessons[38] = <<<'CONTENT'
<h2>CSS Architecture Patterns</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of CSS architecture like organizing a wardrobe...</h4>
    <p>You would not throw all your clothes in one drawer. You separate by type: shirts, pants, accessories. CSS architecture organizes your styles the same way — base styles, components, layouts, and utilities each get their own space.</p>
</div>

<h3>File Structure (ITCSS Pattern)</h3>

<div class="code-block">
<pre><code>styles/
  base/           /* Reset, typography, body defaults */
    _reset.css
    _typography.css
  components/     /* Reusable UI components */
    _button.css
    _card.css
    _modal.css
  layouts/        /* Page structure */
    _grid.css
    _header.css
    _sidebar.css
  utilities/      /* Helper classes */
    _spacing.css
    _visibility.css
  main.css        /* Imports everything */</code></pre>
</div>

<h3>Utility Classes</h3>

<div class="code-block">
<pre><code>/* Create reusable utility classes */
.text-center { text-align: center; }
.text-left { text-align: left; }
.mt-1 { margin-top: 0.5rem; }
.mt-2 { margin-top: 1rem; }
.mt-3 { margin-top: 1.5rem; }
.flex { display: flex; }
.flex-center { 
    display: flex; 
    align-items: center; 
    justify-content: center; 
}
.hidden { display: none; }
.sr-only { /* screen reader only */
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0,0,0,0);
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Separate concerns into organized files</li>
        <li>Base -> Components -> Layouts -> Utilities (increasing specificity)</li>
        <li>Utility classes reduce repetition</li>
        <li>A clear structure makes large projects maintainable</li>
    </ul>
</div>
CONTENT;

$cssLessons[39] = <<<'CONTENT'
<h2>Modern CSS Features</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> CSS keeps evolving...</h4>
    <p>Like smartphones getting new features each year, CSS continues to add powerful capabilities. Native nesting, container queries, and the :has() selector are game changers that used to require JavaScript or preprocessors.</p>
</div>

<h3>CSS Nesting (Native)</h3>

<div class="code-block">
<pre><code>/* Old way: repeat parent selector */
.card { background: #1A1A1A; }
.card .title { font-size: 1.5rem; }
.card:hover { transform: scale(1.02); }

/* New way: native nesting */
.card {
    background: #1A1A1A;
    
    & .title { font-size: 1.5rem; }
    &:hover { transform: scale(1.02); }
    
    @media (max-width: 768px) {
        padding: 1rem;
    }
}</code></pre>
</div>

<h3>The :has() Parent Selector</h3>

<div class="code-block">
<pre><code>/* Select parent based on children */
.card:has(img) {
    padding: 0;   /* remove padding if card has an image */
}

.form:has(:invalid) {
    border-color: red;   /* highlight form with invalid inputs */
}

/* Select sibling relationships */
h2:has(+ p) {
    margin-bottom: 0.5rem;  /* less margin if followed by paragraph */
}</code></pre>
</div>

<h3>Container Queries</h3>

<div class="code-block">
<pre><code>/* Respond to parent container size instead of viewport */
.card-wrapper {
    container-type: inline-size;
}

@container (min-width: 400px) {
    .card {
        flex-direction: row;  /* side-by-side on wide containers */
    }
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Native nesting reduces repetition (use & for parent reference)</li>
        <li>:has() selects parents based on children — a long-awaited feature</li>
        <li>Container queries respond to parent size, not viewport</li>
        <li>Check browser support before using in production</li>
    </ul>
</div>
CONTENT;

$cssLessons[40] = <<<'CONTENT'
<h2>CSS Best Practices</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Good CSS is like good handwriting...</h4>
    <p>Anyone can write — but neat, organized handwriting is easier for everyone to read. Good CSS practices ensure your code is readable, maintainable, and performs well as your project grows.</p>
</div>

<h3>The Golden Rules</h3>

<div class="code-block">
<pre><code>/* 1. Use CSS variables for theming */
:root {
    --primary: #00FF66;
    --font-body: 'IBM Plex Sans', sans-serif;
}

/* 2. Mobile-first media queries */
.container { padding: 1rem; }
@media (min-width: 768px) { 
    .container { padding: 2rem; } 
}

/* 3. Keep specificity low */
.card-title { }        /* Good: 1 class */
div.card .title { }    /* Bad: unnecessarily specific */

/* 4. Avoid !important (almost always) */
.hidden { display: none !important; }  /* Only for utilities */

/* 5. Use modern layout */
.grid { display: grid; }     /* Use Grid for 2D layouts */
.flex { display: flex; }     /* Use Flexbox for 1D layouts */

/* 6. Optimize for performance */
.fast { 
    transform: translateX(0);   /* GPU accelerated */
    will-change: transform;     /* hint to browser */
}</code></pre>
</div>

<h3>CSS Checklist for Production</h3>
<ul>
    <li>All colors use CSS variables</li>
    <li>Mobile-first responsive design</li>
    <li>No unused CSS (clean up dead code)</li>
    <li>Consistent naming convention (BEM or similar)</li>
    <li>Animations use transform/opacity only</li>
    <li>Accessibility: focus styles visible, sufficient color contrast</li>
</ul>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>CSS variables for consistency and theming</li>
        <li>Mobile-first approach with min-width breakpoints</li>
        <li>Keep specificity low, avoid !important</li>
        <li>Modern layout: Grid for pages, Flexbox for components</li>
        <li>Consistency and organization beat cleverness</li>
    </ul>
</div>
CONTENT;

$cssModule4Titles = [36 => 'CSS Variables', 37 => 'CSS Methodologies (BEM)', 38 => 'CSS Architecture Patterns', 39 => 'Modern CSS Features', 40 => 'CSS Best Practices'];
foreach ($cssModule4Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 8, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $cssLessons[$id], $id - 35]);
}

$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (8, 8, 'CSS Architecture Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES (22, 8, 'How do you use a CSS variable?', 1)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (22, 'var(--name)', 1),(22, '\$name', 0),(22, '@name', 0),(22, 'variable(name)', 0)");

echo "CSS course complete! (4 modules, 20 lessons)\n\n";

// ╔═══════════════════════════════════════════════════════════════╗
// ║                JAVASCRIPT COURSE - 4 MODULES                    ║
// ╚═══════════════════════════════════════════════════════════════╝

echo "Creating JavaScript modules...\n";

$pdo->exec("INSERT INTO modules (id, course_id, title, description, order_index) VALUES 
(9, 3, 'JavaScript Fundamentals', 'Variables, data types, operators, control flow, and functions.', 1),
(10, 3, 'DOM & Browser APIs', 'Manipulate web pages, handle events, and work with browser features.', 2),
(11, 3, 'Asynchronous JavaScript', 'Callbacks, promises, async/await, and fetching data.', 3),
(12, 3, 'Modern JavaScript & Patterns', 'ES6+ features, modules, and common design patterns.', 4)");

// JS Module 1 Lessons
$jsLessons = [];

$jsLessons[41] = <<<'CONTENT'
<h2>Variables and Data Types</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Variables are like labeled boxes...</h4>
    <p>A box labeled "name" contains "Amara". A box labeled "age" contains 25. You can look inside, change contents, or use what's inside!</p>
</div>

<h3>Declaring Variables</h3>

<div class="code-block">
<pre><code>// let - can be reassigned
let score = 0;
score = 10;

// const - cannot be reassigned
const PI = 3.14159;
const API_URL = 'https://api.example.com';

// var - old way, avoid it
var oldWay = 'dont use this';</code></pre>
</div>

<h3>Data Types</h3>

<div class="code-block">
<pre><code>// String
let name = "Amara";
let greeting = 'Hello';
let message = `Hello, ${name}!`;  // template literal

// Number
let age = 25;
let price = 99.99;

// Boolean
let isActive = true;
let hasPermission = false;

// Undefined & Null
let unknown;        // undefined
let empty = null;   // intentionally empty

// Check type
console.log(typeof name);  // "string"</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use const by default, let when reassigning</li>
        <li>Template literals use backticks and ${}</li>
        <li>typeof checks data type</li>
    </ul>
</div>
CONTENT;

$jsLessons[42] = <<<'CONTENT'
<h2>Operators and Expressions</h2>

<h3>Arithmetic Operators</h3>

<div class="code-block">
<pre><code>let a = 10, b = 3;

a + b   // 13 (addition)
a - b   // 7 (subtraction)
a * b   // 30 (multiplication)
a / b   // 3.33 (division)
a % b   // 1 (remainder)
a ** b  // 1000 (exponent)

// Increment/Decrement
let count = 5;
count++;  // 6
count--;  // 5

// Compound assignment
score += 10;  // score = score + 10</code></pre>
</div>

<h3>Comparison Operators</h3>

<div class="code-block">
<pre><code>// Always use strict equality (===)
5 === 5     // true (same value and type)
5 === '5'   // false (different types)
5 == '5'    // true (loose, avoid!)

5 !== 3     // true
5 > 3       // true
5 >= 5      // true</code></pre>
</div>

<h3>Logical Operators</h3>

<div class="code-block">
<pre><code>true && true   // AND - both must be true
true || false  // OR - at least one true
!true          // NOT - reverses boolean

// Short-circuit evaluation
const name = user && user.name;  // safe access
const value = input || 'default'; // fallback</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Always use === (strict equality)</li>
        <li>&& and || for logical conditions</li>
        <li>Short-circuit for safe access and defaults</li>
    </ul>
</div>
CONTENT;

$jsLessons[43] = <<<'CONTENT'
<h2>Control Flow</h2>

<h3>If Statements</h3>

<div class="code-block">
<pre><code>const age = 18;

if (age >= 18) {
    console.log('Adult');
} else if (age >= 13) {
    console.log('Teenager');
} else {
    console.log('Child');
}

// Ternary operator (short if/else)
const status = age >= 18 ? 'Adult' : 'Minor';</code></pre>
</div>

<h3>Switch Statement</h3>

<div class="code-block">
<pre><code>const day = 'Monday';

switch (day) {
    case 'Monday':
    case 'Tuesday':
        console.log('Weekday');
        break;
    case 'Saturday':
    case 'Sunday':
        console.log('Weekend');
        break;
    default:
        console.log('Unknown');
}</code></pre>
</div>

<h3>Truthy and Falsy</h3>

<div class="code-block">
<pre><code>// Falsy values (evaluate to false)
false, 0, '', null, undefined, NaN

// Everything else is truthy
if (username) {
    // runs if username exists and isn't empty
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use === in conditions</li>
        <li>Ternary for simple if/else</li>
        <li>Don't forget break in switch</li>
    </ul>
</div>
CONTENT;

$jsLessons[44] = <<<'CONTENT'
<h2>Loops</h2>

<h3>For Loop</h3>

<div class="code-block">
<pre><code>// Classic for loop
for (let i = 0; i < 5; i++) {
    console.log(i);  // 0, 1, 2, 3, 4
}

// For...of (arrays)
const fruits = ['apple', 'banana', 'orange'];
for (const fruit of fruits) {
    console.log(fruit);
}

// For...in (objects)
const person = { name: 'Amara', age: 25 };
for (const key in person) {
    console.log(`${key}: ${person[key]}`);
}</code></pre>
</div>

<h3>While Loop</h3>

<div class="code-block">
<pre><code>let count = 0;
while (count < 5) {
    console.log(count);
    count++;
}

// Do...while (runs at least once)
do {
    console.log('Runs once');
} while (false);</code></pre>
</div>

<h3>Array Methods (Better than loops!)</h3>

<div class="code-block">
<pre><code>const numbers = [1, 2, 3, 4, 5];

// forEach
numbers.forEach(n => console.log(n));

// map (transform)
const doubled = numbers.map(n => n * 2);

// filter
const evens = numbers.filter(n => n % 2 === 0);

// find
const found = numbers.find(n => n > 3);</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>for...of for arrays</li>
        <li>for...in for objects</li>
        <li>Array methods are often cleaner than loops</li>
    </ul>
</div>
CONTENT;

$jsLessons[45] = <<<'CONTENT'
<h2>Functions</h2>

<h3>Function Declaration</h3>

<div class="code-block">
<pre><code>// Function declaration
function greet(name) {
    return `Hello, ${name}!`;
}

// Function expression
const greet = function(name) {
    return `Hello, ${name}!`;
};

// Arrow function
const greet = (name) => `Hello, ${name}!`;

// Arrow with body
const greet = (name) => {
    const message = `Hello, ${name}!`;
    return message;
};</code></pre>
</div>

<h3>Parameters</h3>

<div class="code-block">
<pre><code>// Default parameters
function greet(name = 'Guest') {
    return `Hello, ${name}!`;
}

// Rest parameters
function sum(...numbers) {
    return numbers.reduce((a, b) => a + b, 0);
}
sum(1, 2, 3, 4);  // 10

// Destructuring parameters
function createUser({ name, age }) {
    return { name, age, id: Date.now() };
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Arrow functions for short syntax</li>
        <li>Default parameters prevent undefined</li>
        <li>Rest parameters for variable arguments</li>
    </ul>
</div>
CONTENT;

// Insert JS Module 1 lessons
echo "Creating JavaScript Module 1 lessons...\n";
$jsModule1Titles = [41 => 'Variables and Data Types', 42 => 'Operators and Expressions', 43 => 'Control Flow', 44 => 'Loops', 45 => 'Functions'];
foreach ($jsModule1Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 9, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $jsLessons[$id], $id - 40]);
}

$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (9, 9, 'JS Fundamentals Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES 
(23, 9, 'Which keyword declares a constant?', 1),
(24, 9, 'What does === check?', 2)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES 
(23, 'const', 1),(23, 'let', 0),(23, 'var', 0),(23, 'constant', 0),
(24, 'Value and type', 1),(24, 'Value only', 0),(24, 'Type only', 0),(24, 'Reference', 0)");

// JS Module 2 - DOM & Browser APIs
echo "Creating JavaScript Module 2 lessons...\n";

$jsLessons[46] = <<<'CONTENT'
<h2>DOM Selection</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> The DOM is like a family tree...</h4>
    <p>The Document Object Model (DOM) is a tree of every element on the page. Selecting elements is like finding people in the family tree — you can search by name (tag), nickname (class), or ID number (id).</p>
</div>

<h3>Modern Selectors</h3>

<div class="code-block">
<pre><code>// querySelector — selects the FIRST match
const hero = document.querySelector('.hero');
const title = document.querySelector('#main-title');
const firstButton = document.querySelector('button');

// querySelectorAll — selects ALL matches (NodeList)
const allCards = document.querySelectorAll('.card');
const allLinks = document.querySelectorAll('a');

// Loop through NodeList
allCards.forEach(card => {
    console.log(card.textContent);
});</code></pre>
</div>

<h3>Older Methods (Still Used)</h3>

<div class="code-block">
<pre><code>// By ID (fastest)
const header = document.getElementById('header');

// By class (returns live HTMLCollection)
const buttons = document.getElementsByClassName('btn');

// By tag
const paragraphs = document.getElementsByTagName('p');</code></pre>
</div>

<h3>Traversing the DOM</h3>

<div class="code-block">
<pre><code>const card = document.querySelector('.card');

card.parentElement;          // parent
card.children;               // child elements
card.firstElementChild;      // first child
card.lastElementChild;       // last child
card.nextElementSibling;     // next sibling
card.previousElementSibling; // previous sibling
card.closest('.container');  // nearest ancestor matching selector</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>querySelector returns null if not found</strong> — Always check if the element exists before using it</li>
        <li><strong>querySelectorAll is not an Array</strong> — It is a NodeList. Use forEach or spread [...nodeList] to convert</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>querySelector for single elements, querySelectorAll for multiple</li>
        <li>Use CSS selector syntax (.class, #id, tag)</li>
        <li>closest() traverses up the DOM tree</li>
        <li>Always null-check before manipulating elements</li>
    </ul>
</div>
CONTENT;

$jsLessons[47] = <<<'CONTENT'
<h2>DOM Manipulation</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Manipulating the DOM is like editing a document...</h4>
    <p>Just like you can change text, formatting, and images in a Word document, JavaScript lets you change any part of the webpage in real-time — text, styles, attributes, and structure.</p>
</div>

<h3>Changing Content</h3>

<div class="code-block">
<pre><code>const title = document.querySelector('h1');

// textContent — safe, no HTML parsing
title.textContent = 'Welcome to HackathonAfrica';

// innerHTML — parses HTML (be careful with user input!)
title.innerHTML = 'Welcome to <strong>HackathonAfrica</strong>';

// innerText — respects CSS visibility
console.log(title.innerText);</code></pre>
</div>

<h3>Changing Attributes</h3>

<div class="code-block">
<pre><code>const link = document.querySelector('a');

link.setAttribute('href', 'https://hackathon.africa');
link.getAttribute('href');  // read attribute
link.removeAttribute('target');

// Direct property access (common attributes)
link.href = 'https://hackathon.africa';
link.id = 'main-link';</code></pre>
</div>

<h3>Changing Classes</h3>

<div class="code-block">
<pre><code>const card = document.querySelector('.card');

card.classList.add('active');        // add class
card.classList.remove('hidden');     // remove class
card.classList.toggle('expanded');   // toggle on/off
card.classList.contains('active');   // check if has class
card.classList.replace('old', 'new'); // swap classes</code></pre>
</div>

<h3>Changing Styles</h3>

<div class="code-block">
<pre><code>// Inline styles (use sparingly)
el.style.color = '#00FF66';
el.style.backgroundColor = '#1A1A1A';
el.style.display = 'none';

// Better: toggle classes instead
el.classList.add('hidden');  // .hidden { display: none; }</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>textContent is safer than innerHTML (no XSS risk)</li>
        <li>classList methods are the modern way to manage classes</li>
        <li>Prefer toggling classes over direct style changes</li>
        <li>Use setAttribute for non-standard or data attributes</li>
    </ul>
</div>
CONTENT;

$jsLessons[48] = <<<'CONTENT'
<h2>Event Handling</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Events are like notifications...</h4>
    <p>Your phone sends you notifications when something happens (new message, alarm). DOM events work the same way — the browser notifies your code when the user clicks, types, scrolls, or interacts with the page.</p>
</div>

<h3>Adding Event Listeners</h3>

<div class="code-block">
<pre><code>const button = document.querySelector('.btn');

// addEventListener(event, callback)
button.addEventListener('click', function(event) {
    console.log('Button clicked!');
    console.log(event.target);  // the element that was clicked
});

// Arrow function version
button.addEventListener('click', (e) => {
    e.preventDefault();  // stop default behavior (links, forms)
    console.log('Clicked!');
});</code></pre>
</div>

<h3>Common Events</h3>

<div class="code-block">
<pre><code>// Mouse events
element.addEventListener('click', handler);
element.addEventListener('dblclick', handler);
element.addEventListener('mouseenter', handler);  // hover in
element.addEventListener('mouseleave', handler);  // hover out

// Keyboard events
input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') submitForm();
});

// Form events
form.addEventListener('submit', handler);
input.addEventListener('input', handler);    // real-time typing
input.addEventListener('change', handler);   // value changed + blur

// Page events
window.addEventListener('scroll', handler);
window.addEventListener('resize', handler);
document.addEventListener('DOMContentLoaded', handler);</code></pre>
</div>

<h3>Event Delegation</h3>

<div class="code-block">
<pre><code>// Instead of adding listener to EVERY button...
// Add ONE listener to the parent!
document.querySelector('.button-container').addEventListener('click', (e) => {
    if (e.target.matches('.btn')) {
        console.log('Button clicked:', e.target.textContent);
    }
    if (e.target.matches('.delete-btn')) {
        e.target.closest('.card').remove();
    }
});</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Calling the function instead of passing it</strong> — Write <code>addEventListener('click', handleClick)</code> NOT <code>addEventListener('click', handleClick())</code></li>
        <li><strong>Forgetting e.preventDefault()</strong> — Forms will reload the page without it</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>addEventListener is the modern way to handle events</li>
        <li>event.target gives you the element that triggered the event</li>
        <li>Event delegation: one listener on parent instead of many on children</li>
        <li>Always preventDefault() on form submissions you handle with JS</li>
    </ul>
</div>
CONTENT;

$jsLessons[49] = <<<'CONTENT'
<h2>Creating Elements</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Creating elements is like building with LEGO...</h4>
    <p>You create a brick (createElement), customize it (add text, classes, attributes), then snap it onto the structure (appendChild). You can also remove bricks or rearrange them.</p>
</div>

<h3>Creating and Adding Elements</h3>

<div class="code-block">
<pre><code>// 1. Create the element
const card = document.createElement('div');

// 2. Customize it
card.className = 'card';
card.id = 'new-card';
card.innerHTML = `
    &lt;h3 class="card__title"&gt;New Card&lt;/h3&gt;
    &lt;p class="card__text"&gt;Created with JavaScript!&lt;/p&gt;
`;

// 3. Add it to the page
document.querySelector('.container').appendChild(card);

// Add before another element
const container = document.querySelector('.container');
const firstCard = container.firstElementChild;
container.insertBefore(card, firstCard);</code></pre>
</div>

<h3>Removing and Replacing</h3>

<div class="code-block">
<pre><code>// Remove an element
card.remove();

// Replace an element
const newElement = document.createElement('div');
oldElement.replaceWith(newElement);

// Clone an element
const clone = card.cloneNode(true);  // true = deep clone (with children)</code></pre>
</div>

<h3>Building Lists Dynamically</h3>

<div class="code-block">
<pre><code>const students = ['Amara', 'Kwame', 'Fatima', 'Chidi'];

const list = document.querySelector('.student-list');
students.forEach(name => {
    const li = document.createElement('li');
    li.textContent = name;
    li.classList.add('student-item');
    list.appendChild(li);
});</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>createElement + appendChild is the standard pattern</li>
        <li>Template literals make innerHTML cleaner for complex HTML</li>
        <li>remove() deletes elements, cloneNode() copies them</li>
        <li>Build lists dynamically from data arrays</li>
    </ul>
</div>
CONTENT;

$jsLessons[50] = <<<'CONTENT'
<h2>Forms and Validation</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Form validation is like a security checkpoint...</h4>
    <p>Before letting someone through (submitting data), you check their documents. Is the name filled in? Is the email valid? Is the password strong enough? JavaScript validation catches problems before the data leaves the browser.</p>
</div>

<h3>Handling Form Submissions</h3>

<div class="code-block">
<pre><code>const form = document.querySelector('#register-form');

form.addEventListener('submit', (e) => {
    e.preventDefault();  // stop page reload
    
    // Get form data
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    console.log(data);
    // { name: 'Amara', email: 'amara@example.com', ... }
});</code></pre>
</div>

<h3>Reading Input Values</h3>

<div class="code-block">
<pre><code>const nameInput = document.querySelector('#name');
const emailInput = document.querySelector('#email');

// Get values
const name = nameInput.value;
const email = emailInput.value;

// Real-time validation
emailInput.addEventListener('input', (e) => {
    const isValid = e.target.value.includes('@');
    e.target.classList.toggle('invalid', !isValid);
});</code></pre>
</div>

<h3>Custom Validation</h3>

<div class="code-block">
<pre><code>function validateForm(data) {
    const errors = [];
    
    if (!data.name || data.name.length < 2) {
        errors.push('Name must be at least 2 characters');
    }
    if (!data.email || !data.email.includes('@')) {
        errors.push('Please enter a valid email');
    }
    if (!data.password || data.password.length < 8) {
        errors.push('Password must be at least 8 characters');
    }
    
    return errors;
}

// Use it
const errors = validateForm(data);
if (errors.length > 0) {
    showErrors(errors);
} else {
    submitToServer(data);
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Always preventDefault() on form submit</li>
        <li>FormData + Object.fromEntries gets all form values</li>
        <li>Validate on the client AND the server</li>
        <li>Show clear error messages to the user</li>
    </ul>
</div>
CONTENT;

$jsModule2Titles = [46 => 'DOM Selection', 47 => 'DOM Manipulation', 48 => 'Event Handling', 49 => 'Creating Elements', 50 => 'Forms and Validation'];
foreach ($jsModule2Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 10, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $jsLessons[$id], $id - 45]);
}

$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (10, 10, 'DOM Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES (25, 10, 'Which selects a single element?', 1)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (25, 'querySelector', 1),(25, 'querySelectorAll', 0),(25, 'getElements', 0),(25, 'select', 0)");

// JS Module 3 - Asynchronous JavaScript
echo "Creating JavaScript Module 3 lessons...\n";

$jsLessons[51] = <<<'CONTENT'
<h2>Callbacks</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Callbacks are like leaving a phone number...</h4>
    <p>When you order food, you leave your phone number so they can CALL you BACK when it is ready. In JavaScript, a callback is a function you pass to another function, saying "call this when you are done."</p>
</div>

<h3>What is a Callback?</h3>

<div class="code-block">
<pre><code>// A callback is just a function passed as an argument
function greet(name, callback) {
    console.log('Hello, ' + name);
    callback();  // call the function when done
}

greet('Amara', function() {
    console.log('Greeting complete!');
});</code></pre>
</div>

<h3>Async Callbacks</h3>

<div class="code-block">
<pre><code>// setTimeout — runs code after a delay
setTimeout(() => {
    console.log('This runs after 2 seconds');
}, 2000);

// Simulating data fetching
function fetchData(callback) {
    setTimeout(() => {
        const data = { name: 'Amara', score: 95 };
        callback(data);  // call back with the data
    }, 1000);
}

fetchData((data) => {
    console.log('Got data:', data.name);
});</code></pre>
</div>

<h3>Callback Hell (The Problem)</h3>

<div class="code-block">
<pre><code>// Nested callbacks become unreadable
fetchUser(userId, (user) => {
    fetchCourses(user.id, (courses) => {
        fetchGrades(courses[0].id, (grades) => {
            fetchCertificate(grades, (cert) => {
                // This is "callback hell" or "pyramid of doom"
                // Promises solve this!
            });
        });
    });
});</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Callback hell</strong> — Deeply nested callbacks are hard to read and debug. Use Promises or async/await instead</li>
        <li><strong>Forgetting error handling</strong> — Always handle errors in callbacks: <code>callback(error, data)</code></li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>A callback is a function passed to another function</li>
        <li>setTimeout and setInterval use callbacks</li>
        <li>Nested callbacks create "callback hell"</li>
        <li>Promises and async/await are modern alternatives</li>
    </ul>
</div>
CONTENT;

$jsLessons[52] = <<<'CONTENT'
<h2>Promises</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> A Promise is like a food order receipt...</h4>
    <p>When you place an order, you get a receipt (the Promise). The order is either fulfilled (resolve — food arrives) or rejected (reject — out of stock). You do not wait at the counter; you continue your day and check back when notified.</p>
</div>

<h3>Creating a Promise</h3>

<div class="code-block">
<pre><code>const myPromise = new Promise((resolve, reject) => {
    const success = true;
    
    setTimeout(() => {
        if (success) {
            resolve('Data loaded successfully!');
        } else {
            reject('Something went wrong');
        }
    }, 1000);
});</code></pre>
</div>

<h3>Using Promises</h3>

<div class="code-block">
<pre><code>myPromise
    .then(data => {
        console.log('Success:', data);
        return data.toUpperCase();  // chain another .then
    })
    .then(upper => {
        console.log('Uppercase:', upper);
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        console.log('Done — success or fail');
    });</code></pre>
</div>

<h3>Promise.all — Multiple Promises</h3>

<div class="code-block">
<pre><code>// Wait for ALL promises to complete
const [users, courses, grades] = await Promise.all([
    fetch('/api/users').then(r => r.json()),
    fetch('/api/courses').then(r => r.json()),
    fetch('/api/grades').then(r => r.json())
]);
// All three requests run in parallel!</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Promise states: pending, fulfilled (resolved), rejected</li>
        <li>.then() handles success, .catch() handles errors</li>
        <li>Promises can be chained (.then().then())</li>
        <li>Promise.all runs multiple promises in parallel</li>
    </ul>
</div>
CONTENT;

$jsLessons[53] = <<<'CONTENT'
<h2>Async/Await</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Async/await makes promises read like normal code...</h4>
    <p>Promises with .then() chains can still be confusing. Async/await is syntactic sugar — it lets you write asynchronous code that looks and reads like synchronous code. Same power, much cleaner.</p>
</div>

<h3>The async Keyword</h3>

<div class="code-block">
<pre><code>// async makes a function return a Promise automatically
async function getUser() {
    return { name: 'Amara', score: 95 };
}

// This is equivalent to:
function getUser() {
    return Promise.resolve({ name: 'Amara', score: 95 });
}</code></pre>
</div>

<h3>The await Keyword</h3>

<div class="code-block">
<pre><code>// await pauses execution until the Promise resolves
async function fetchUser() {
    const response = await fetch('/api/user');  // waits here
    const data = await response.json();          // then waits here
    return data;                                  // then returns
}

// Using it
const user = await fetchUser();
console.log(user.name);</code></pre>
</div>

<h3>Error Handling with try/catch</h3>

<div class="code-block">
<pre><code>async function loadDashboard() {
    try {
        const user = await fetch('/api/user').then(r => r.json());
        const courses = await fetch('/api/courses').then(r => r.json());
        
        displayDashboard(user, courses);
    } catch (error) {
        console.error('Failed to load:', error);
        showErrorMessage('Could not load dashboard. Please try again.');
    }
}</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
    <ul>
        <li><strong>Forgetting await</strong> — Without await, you get a Promise object instead of the actual data</li>
        <li><strong>Using await outside async</strong> — await can only be used inside async functions (or at the top level of modules)</li>
        <li><strong>Sequential when parallel is possible</strong> — Use Promise.all for independent requests</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>async functions always return a Promise</li>
        <li>await pauses execution until the Promise resolves</li>
        <li>Use try/catch for error handling</li>
        <li>Use Promise.all when requests are independent</li>
    </ul>
</div>
CONTENT;

$jsLessons[54] = <<<'CONTENT'
<h2>Fetch API</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Fetch is like sending letters to a server...</h4>
    <p>GET is reading a letter from the server. POST is sending a letter to the server. PUT is updating a letter. DELETE is asking them to throw one away. The Fetch API is your mailman.</p>
</div>

<h3>GET Request (Read Data)</h3>

<div class="code-block">
<pre><code>// Basic GET request
const response = await fetch('https://api.example.com/courses');
const courses = await response.json();
console.log(courses);

// Check if request succeeded
if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
}</code></pre>
</div>

<h3>POST Request (Send Data)</h3>

<div class="code-block">
<pre><code>const response = await fetch('/api/register', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        name: 'Amara',
        email: 'amara@hackathon.africa',
        course: 'JavaScript'
    })
});

const result = await response.json();
console.log(result);</code></pre>
</div>

<h3>Full CRUD Example</h3>

<div class="code-block">
<pre><code>// UPDATE (PUT)
await fetch('/api/users/1', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ name: 'Updated Name' })
});

// DELETE
await fetch('/api/users/1', {
    method: 'DELETE'
});</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>fetch() returns a Promise</li>
        <li>response.json() parses the JSON body</li>
        <li>Always check response.ok for error handling</li>
        <li>POST/PUT need method, headers, and body</li>
    </ul>
</div>
CONTENT;

$jsLessons[55] = <<<'CONTENT'
<h2>Error Handling</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Error handling is like a safety net...</h4>
    <p>Trapeze artists always have a safety net below. If they fall, the net catches them. try/catch is your code's safety net — if something goes wrong inside try, the catch block catches the error instead of crashing your entire program.</p>
</div>

<h3>try/catch/finally</h3>

<div class="code-block">
<pre><code>try {
    // Code that might fail
    const data = JSON.parse(userInput);
    console.log(data.name);
} catch (error) {
    // Runs only if try block throws an error
    console.error('Invalid JSON:', error.message);
    showNotification('Invalid data format');
} finally {
    // ALWAYS runs — success or failure
    hideLoadingSpinner();
}</code></pre>
</div>

<h3>Throwing Custom Errors</h3>

<div class="code-block">
<pre><code>function validateAge(age) {
    if (typeof age !== 'number') {
        throw new TypeError('Age must be a number');
    }
    if (age < 0 || age > 150) {
        throw new RangeError('Age must be between 0 and 150');
    }
    return true;
}

try {
    validateAge('twenty');
} catch (error) {
    console.log(error.name);     // "TypeError"
    console.log(error.message);  // "Age must be a number"
}</code></pre>
</div>

<h3>Async Error Handling</h3>

<div class="code-block">
<pre><code>// With async/await
async function loadData() {
    try {
        const res = await fetch('/api/data');
        if (!res.ok) throw new Error('Server error: ' + res.status);
        return await res.json();
    } catch (error) {
        console.error('Load failed:', error);
        return null;  // return fallback value
    }
}</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>try/catch prevents crashes from unexpected errors</li>
        <li>finally always runs (cleanup code goes here)</li>
        <li>throw creates custom errors for validation</li>
        <li>Always wrap async operations in try/catch</li>
    </ul>
</div>
CONTENT;

$jsModule3Titles = [51 => 'Callbacks', 52 => 'Promises', 53 => 'Async/Await', 54 => 'Fetch API', 55 => 'Error Handling'];
foreach ($jsModule3Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 11, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $jsLessons[$id], $id - 50]);
}

$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (11, 11, 'Async Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES (26, 11, 'Which keyword pauses async function execution?', 1)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (26, 'await', 1),(26, 'pause', 0),(26, 'wait', 0),(26, 'yield', 0)");

// JS Module 4 - Modern JavaScript & Patterns
echo "Creating JavaScript Module 4 lessons...\n";

$jsLessons[56] = <<<'CONTENT'
<h2>Destructuring</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Destructuring is like unpacking a suitcase...</h4>
    <p>Instead of rummaging through the suitcase every time you need something, you unpack items into labeled spots on the shelf. Destructuring "unpacks" values from arrays and objects into individual variables.</p>
</div>

<h3>Object Destructuring</h3>

<div class="code-block">
<pre><code>const student = {
    name: 'Amara',
    age: 22,
    course: 'JavaScript',
    country: 'Nigeria'
};

// Old way
const name = student.name;
const age = student.age;

// Destructuring (much cleaner!)
const { name, age, course, country } = student;
console.log(name);    // 'Amara'
console.log(course);  // 'JavaScript'

// With renaming
const { name: studentName, age: studentAge } = student;

// With defaults
const { scholarship = false } = student;  // false (not in object)</code></pre>
</div>

<h3>Array Destructuring</h3>

<div class="code-block">
<pre><code>const scores = [95, 88, 72, 60];

const [first, second, ...rest] = scores;
console.log(first);  // 95
console.log(rest);   // [72, 60]

// Skip items
const [, , third] = scores;
console.log(third);  // 72

// Swap variables
let a = 1, b = 2;
[a, b] = [b, a];  // a=2, b=1</code></pre>
</div>

<h3>In Function Parameters</h3>

<div class="code-block">
<pre><code>// Destructure directly in parameters
function displayStudent({ name, course, country }) {
    console.log(`${name} is studying ${course} in ${country}`);
}

displayStudent(student);
// "Amara is studying JavaScript in Nigeria"</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Object destructuring: { name, age } = object</li>
        <li>Array destructuring: [first, second] = array</li>
        <li>Use ...rest to collect remaining items</li>
        <li>Destructure in function parameters for cleaner code</li>
    </ul>
</div>
CONTENT;

$jsLessons[57] = <<<'CONTENT'
<h2>Spread and Rest Operators</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Spread unpacks, Rest packs...</h4>
    <p>Spread (...) is like pouring marbles out of a bag — it spreads items out. Rest (...) is like sweeping remaining marbles into a bag — it collects leftovers. Same syntax, opposite actions!</p>
</div>

<h3>Spread Operator (Expanding)</h3>

<div class="code-block">
<pre><code>// Spread arrays
const htmlTopics = ['tags', 'forms', 'semantic'];
const cssTopics = ['selectors', 'flexbox', 'grid'];
const allTopics = [...htmlTopics, ...cssTopics];
// ['tags', 'forms', 'semantic', 'selectors', 'flexbox', 'grid']

// Spread objects (shallow copy + merge)
const defaults = { theme: 'dark', lang: 'en' };
const userPrefs = { theme: 'light' };
const settings = { ...defaults, ...userPrefs };
// { theme: 'light', lang: 'en' }  — userPrefs overrides defaults

// Copy an array (without reference)
const original = [1, 2, 3];
const copy = [...original];
copy.push(4);  // original is unchanged!</code></pre>
</div>

<h3>Rest Operator (Collecting)</h3>

<div class="code-block">
<pre><code>// Rest in function parameters
function sum(...numbers) {
    return numbers.reduce((total, n) => total + n, 0);
}
sum(1, 2, 3, 4);  // 10

// Rest in destructuring
const [winner, ...losers] = ['Amara', 'Kwame', 'Fatima'];
// winner = 'Amara', losers = ['Kwame', 'Fatima']

const { name, ...otherProps } = student;
// name = 'Amara', otherProps = { age: 22, course: 'JS' }</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Spread (...): expands arrays/objects into individual items</li>
        <li>Rest (...): collects remaining items into array/object</li>
        <li>Spread creates shallow copies (no reference sharing)</li>
        <li>Use spread to merge objects (later values override earlier)</li>
    </ul>
</div>
CONTENT;

$jsLessons[58] = <<<'CONTENT'
<h2>Modules</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Modules are like departments in a company...</h4>
    <p>A company does not have everyone in one room. There is a finance department, marketing department, etc. Each has specific responsibilities. JavaScript modules split your code into separate files, each responsible for one thing.</p>
</div>

<h3>Named Exports</h3>

<div class="code-block">
<pre><code>// utils.js — exporting multiple things
export const API_URL = 'https://api.hackathon.africa';
export const MAX_SCORE = 100;

export function formatDate(date) {
    return new Date(date).toLocaleDateString();
}

export function calculateGrade(score) {
    if (score >= 90) return 'A';
    if (score >= 80) return 'B';
    if (score >= 70) return 'C';
    return 'F';
}</code></pre>
</div>

<h3>Default Exports</h3>

<div class="code-block">
<pre><code>// Student.js — one main export per file
export default class Student {
    constructor(name, email) {
        this.name = name;
        this.email = email;
    }
    
    enroll(course) {
        console.log(`${this.name} enrolled in ${course}`);
    }
}</code></pre>
</div>

<h3>Importing</h3>

<div class="code-block">
<pre><code>// Import named exports (curly braces)
import { formatDate, calculateGrade } from './utils.js';

// Import default export (no curly braces)
import Student from './Student.js';

// Import both
import Student, { API_URL, formatDate } from './Student.js';

// Rename imports
import { formatDate as fmtDate } from './utils.js';</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Named exports: multiple per file, imported with { curly braces }</li>
        <li>Default export: one per file, imported without braces</li>
        <li>Modules keep code organized and reusable</li>
        <li>Use type="module" in your script tag to enable modules</li>
    </ul>
</div>
CONTENT;

$jsLessons[59] = <<<'CONTENT'
<h2>Classes</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> A class is like a blueprint for a house...</h4>
    <p>A blueprint defines what a house looks like — rooms, doors, windows. From one blueprint, you can build many houses. A class defines properties and behaviors. From one class, you can create many objects (instances).</p>
</div>

<h3>Defining a Class</h3>

<div class="code-block">
<pre><code>class Student {
    constructor(name, email) {
        this.name = name;
        this.email = email;
        this.courses = [];
        this.score = 0;
    }
    
    enroll(course) {
        this.courses.push(course);
        console.log(`${this.name} enrolled in ${course}`);
    }
    
    getProgress() {
        return `${this.name}: ${this.courses.length} courses, score: ${this.score}`;
    }
}

// Create instances
const amara = new Student('Amara', 'amara@hackathon.africa');
amara.enroll('JavaScript');
console.log(amara.getProgress());</code></pre>
</div>

<h3>Inheritance</h3>

<div class="code-block">
<pre><code>class Admin extends Student {
    constructor(name, email) {
        super(name, email);   // call parent constructor
        this.role = 'admin';
    }
    
    reviewCandidate(student) {
        console.log(`${this.name} is reviewing ${student.name}`);
    }
}

const admin = new Admin('Admin', 'admin@hackathon.africa');
admin.enroll('Leadership');     // inherited from Student
admin.reviewCandidate(amara);  // Admin-specific method</code></pre>
</div>

<h3>Static Methods and Getters</h3>

<div class="code-block">
<pre><code>class MathHelper {
    static add(a, b) { return a + b; }
    static PI = 3.14159;
}
// Called on the class, not instances
MathHelper.add(5, 3);  // 8

class Temperature {
    constructor(celsius) {
        this._celsius = celsius;
    }
    get fahrenheit() {
        return this._celsius * 1.8 + 32;
    }
}
const temp = new Temperature(30);
console.log(temp.fahrenheit);  // 86</code></pre>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>class defines a template for creating objects</li>
        <li>constructor initializes new instances</li>
        <li>extends enables inheritance (child gets parent methods)</li>
        <li>static methods belong to the class, not instances</li>
    </ul>
</div>
CONTENT;

$jsLessons[60] = <<<'CONTENT'
<h2>JavaScript Best Practices</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Good code is like good cooking...</h4>
    <p>Anyone can throw ingredients together, but a good chef follows recipes, keeps the kitchen clean, and labels everything. These best practices are your JavaScript "chef rules" — they make your code reliable, readable, and professional.</p>
</div>

<h3>Variable Declarations</h3>

<div class="code-block">
<pre><code>// Use const by default
const API_URL = '/api';
const maxRetries = 3;

// Use let only when reassignment is needed
let count = 0;
count++;

// NEVER use var (function-scoped, hoisted, bug-prone)
// var x = 'avoid this';</code></pre>
</div>

<h3>Modern Patterns</h3>

<div class="code-block">
<pre><code>// Arrow functions for callbacks
const scores = [95, 72, 88];
const high = scores.filter(s => s >= 80);

// Template literals over concatenation
const msg = `Hello, ${user.name}! Score: ${user.score}`;

// Destructure for cleaner code
const { name, email } = user;

// Optional chaining (safe property access)
const city = user?.address?.city;  // undefined if any part is null

// Nullish coalescing (fallback for null/undefined)
const theme = user.theme ?? 'dark';  // 'dark' only if null/undefined

// Async/await over .then() chains
const data = await fetchData();</code></pre>
</div>

<h3>Code Organization Checklist</h3>
<ul>
    <li>One responsibility per function</li>
    <li>Descriptive variable and function names</li>
    <li>Use modules to split code into files</li>
    <li>Handle errors with try/catch</li>
    <li>Comment the "why", not the "what"</li>
    <li>Use consistent naming: camelCase for variables, PascalCase for classes</li>
</ul>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>const by default, let when needed, never var</li>
        <li>Arrow functions, template literals, destructuring</li>
        <li>Optional chaining (?.) and nullish coalescing (??)</li>
        <li>Async/await over .then() chains</li>
        <li>Clean code = readable, maintainable, testable</li>
    </ul>
</div>
CONTENT;

$jsModule4Titles = [56 => 'Destructuring', 57 => 'Spread and Rest', 58 => 'Modules', 59 => 'Classes', 60 => 'Best Practices'];
foreach ($jsModule4Titles as $id => $title) {
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes, video_placeholder) VALUES (?, 12, ?, ?, ?, 15, 1)");
    $stmt->execute([$id, $title, $jsLessons[$id], $id - 55]);
}

$pdo->exec("INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (12, 12, 'Modern JS Quiz', 70)");
$pdo->exec("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES (27, 12, 'Which extracts values from arrays/objects?', 1)");
$pdo->exec("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (27, 'Destructuring', 1),(27, 'Spreading', 0),(27, 'Mapping', 0),(27, 'Filtering', 0)");

echo "JavaScript course complete! (4 modules, 20 lessons)\n\n";

// Re-enable foreign keys (SQLite only)
if (DB_DRIVER === 'sqlite') {
    $pdo->exec('PRAGMA foreign_keys = ON');
}

// ══════════════════════════════════════════════════════
// ADDITIONAL QUIZ QUESTIONS (4-5 per module total)
// ══════════════════════════════════════════════════════
echo "Adding additional quiz questions...\n";

$nextQId = 28;
$nextOptBase = 0; // Options auto-increment

// Helper to add a quiz question with 4 options
$addQuestion = function(int $quizId, string $questionText, array $options, int $correctIdx) use ($pdo, &$nextQId) {
    $qId = $nextQId++;
    $stmt = $pdo->prepare("INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES (?, ?, ?, ?)");
    $stmt->execute([$qId, $quizId, $questionText, $qId]);
    
    foreach ($options as $i => $opt) {
        $isCorrect = ($i === $correctIdx) ? 1 : 0;
        $stmt2 = $pdo->prepare("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
        $stmt2->execute([$qId, $opt, $isCorrect]);
    }
    return $qId;
};

// HTML Module 1 - Foundations (Quiz 1 already has Q1-Q2)
$addQuestion(1, 'What does the <title> tag define?', ['The browser tab title', 'A heading on the page', 'Bold text', 'A link'], 0);
$addQuestion(1, 'Which tag is used for the largest heading?', ['<h6>', '<heading>', '<h1>', '<big>'], 2);
$addQuestion(1, 'Where should the <link> tag for CSS go?', ['<body>', '<head>', '<footer>', '<div>'], 1);

// HTML Module 2 - Content Elements (Quiz 2)
$addQuestion(2, 'Which is an inline element?', ['<div>', '<p>', '<span>', '<section>'], 2);
$addQuestion(2, 'What attribute makes a link open in a new tab?', ['target="_new"', 'target="_blank"', 'newtab="true"', 'open="new"'], 1);
$addQuestion(2, 'Which tag creates a numbered list?', ['<ul>', '<dl>', '<ol>', '<nl>'], 2);

// HTML Module 3 - Forms (Quiz 3)
$addQuestion(3, 'Which input type shows a date picker?', ['type="calendar"', 'type="date"', 'type="datetime"', 'type="picker"'], 1);
$addQuestion(3, 'What does the <label> for attribute do?', ['Styles the label', 'Links the label to an input by id', 'Creates a form group', 'Makes text bold'], 1);
$addQuestion(3, 'Which attribute prevents form submission without a value?', ['validate', 'mandatory', 'required', 'notnull'], 2);

// HTML Module 4 - Semantic HTML (Quiz 4)
$addQuestion(4, 'Which is NOT a semantic element?', ['<article>', '<div>', '<nav>', '<aside>'], 1);
$addQuestion(4, 'What does <figure> typically contain?', ['Navigation links', 'An image with a caption', 'A form', 'A heading'], 1);
$addQuestion(4, 'Why is semantic HTML important for accessibility?', ['It loads faster', 'Screen readers can understand the page structure', 'It adds colors', 'It validates forms'], 1);

// CSS Module 1 - Fundamentals (Quiz 5)
$addQuestion(5, 'What does the "C" in CSS stand for?', ['Creative', 'Cascading', 'Colorful', 'Computed'], 1);
$addQuestion(5, 'Which has the highest specificity?', ['Class selector', 'ID selector', 'Element selector', 'Universal selector'], 1);
$addQuestion(5, 'What unit is relative to the parent font size?', ['px', 'rem', 'em', 'vh'], 2);

// CSS Module 2 - Layout (Quiz 6)
$addQuestion(6, 'Which property makes a flex container?', ['display: flex', 'position: flex', 'layout: flex', 'flex: true'], 0);
$addQuestion(6, 'What does grid-template-columns define?', ['Row heights', 'Column widths', 'Gap sizes', 'Border widths'], 1);
$addQuestion(6, 'Which property centers items vertically in flexbox?', ['justify-content', 'align-items', 'text-align', 'vertical-align'], 1);

// CSS Module 3 - Visual Effects (Quiz 7)
$addQuestion(7, 'Which timing function creates a bounce effect?', ['linear', 'ease-in', 'cubic-bezier()', 'step-end'], 2);
$addQuestion(7, 'What does animation-fill-mode: forwards do?', ['Repeats animation', 'Keeps the end state', 'Reverses animation', 'Pauses animation'], 1);
$addQuestion(7, 'Which properties are GPU-accelerated for animations?', ['width and height', 'transform and opacity', 'margin and padding', 'color and background'], 1);

// CSS Module 4 - Architecture (Quiz 8)
$addQuestion(8, 'What does BEM stand for?', ['Block Element Modifier', 'Base Effect Module', 'Border Edge Margin', 'Build Export Merge'], 0);
$addQuestion(8, 'What does :has() do in CSS?', ['Selects all children', 'Selects parent based on children', 'Checks for attributes', 'Validates input'], 1);
$addQuestion(8, 'Which is the correct BEM naming?', ['.card-title', '.card__title', '.card.title', '.card>title'], 1);

// JS Module 1 - Fundamentals (Quiz 9)
$addQuestion(9, 'What is the correct way to declare a constant?', ['var x = 5', 'let x = 5', 'const x = 5', 'constant x = 5'], 2);
$addQuestion(9, 'What does typeof null return?', ['"null"', '"undefined"', '"object"', '"none"'], 2);
$addQuestion(9, 'Which array method adds to the end?', ['.unshift()', '.push()', '.append()', '.add()'], 1);

// JS Module 2 - DOM (Quiz 10)
$addQuestion(10, 'What does querySelector return if nothing matches?', ['undefined', 'false', 'null', 'empty string'], 2);
$addQuestion(10, 'Which method adds a CSS class?', ['el.class = "name"', 'el.classList.add("name")', 'el.addStyle("name")', 'el.setClass("name")'], 1);
$addQuestion(10, 'What is event delegation?', ['Adding events to all elements', 'Listening on a parent for child events', 'Removing events after use', 'Creating custom events'], 1);

// JS Module 3 - Async (Quiz 11)
$addQuestion(11, 'What are the three Promise states?', ['start, run, end', 'pending, fulfilled, rejected', 'open, closed, error', 'sync, async, done'], 1);
$addQuestion(11, 'What keyword pauses until a Promise resolves?', ['wait', 'pause', 'await', 'hold'], 2);
$addQuestion(11, 'What does fetch() return?', ['JSON data', 'An HTML string', 'A Promise', 'An array'], 2);

// JS Module 4 - Modern JS (Quiz 12)
$addQuestion(12, 'What does the ... spread operator do?', ['Concatenates strings', 'Expands arrays/objects', 'Creates loops', 'Defines types'], 1);
$addQuestion(12, 'Which import syntax is for named exports?', ['import X from "./file"', 'import { X } from "./file"', 'require("./file")', 'include "./file"'], 1);
$addQuestion(12, 'What does optional chaining (?.) prevent?', ['Syntax errors', 'Type coercion', 'Errors accessing properties of null/undefined', 'Memory leaks'], 2);

echo "Added " . ($nextQId - 28) . " additional quiz questions!\n\n";

// ══════════════════════════════════════════════════════
// FINAL EXAMS (one per course)
// ══════════════════════════════════════════════════════
echo "Creating final exams...\n";

// HTML Final Exam
$pdo->exec("INSERT INTO final_exams (course_id, title, description, pass_mark, time_limit, mcq_count, coding_count) VALUES (1, 'HTML Final Assessment', 'Test your knowledge of HTML fundamentals, semantic elements, forms, and accessibility.', 70, 30, 8, 2)");
$htmlExamId = $pdo->lastInsertId();

$htmlExamQuestions = [
    ['mcq', 'Which HTML5 element is used for navigation links?', '["<nav>","<menu>","<links>","<directory>"]', '0', 10],
    ['mcq', 'What is the purpose of the alt attribute on images?', '["To set image size","To provide alternative text for screen readers","To add a tooltip","To set image alignment"]', '1', 10],
    ['mcq', 'Which input type is best for email addresses?', '["type=\"text\"","type=\"email\"","type=\"url\"","type=\"contact\""]', '1', 10],
    ['mcq', 'What does the <meta charset=\"UTF-8\"> tag do?', '["Sets the page title","Defines the character encoding","Links a stylesheet","Imports a script"]', '1', 10],
    ['mcq', 'Which tag pair creates a table row?', '["<tr>...</tr>","<td>...</td>","<row>...</row>","<trow>...</trow>"]', '0', 10],
    ['mcq', 'What is the correct HTML5 doctype?', '["<!DOCTYPE html>","<!DOCTYPE HTML5>","<doctype html>","<!html>"]', '0', 10],
    ['mcq', 'Which attribute makes a form field read-only?', '["disabled","readonly","locked","static"]', '1', 10],
    ['mcq', 'What does the <aside> element represent?', '["A sidebar or tangential content","A header section","A footer section","A main content area"]', '0', 10],
    ['coding', 'Create a complete HTML form with: a text input for "Full Name" (required), an email input (required), a select dropdown with 3 country options, and a submit button. Use proper labels linked to inputs.', null, null, 10, '<!-- Write your HTML form here -->', '<form>\n  <label for="name">Full Name</label>\n  <input type="text" id="name" required>\n  <label for="email">Email</label>\n  <input type="email" id="email" required>\n  <label for="country">Country</label>\n  <select id="country">\n    <option>Nigeria</option>\n    <option>Kenya</option>\n    <option>Ghana</option>\n  </select>\n  <button type="submit">Submit</button>\n</form>'],
    ['coding', 'Create a semantic HTML page structure with: header (containing a nav with 3 links), main (containing an article with h1, two paragraphs, and a figure with an image and figcaption), and a footer.', null, null, 10, '<!-- Write your semantic HTML here -->', '<header>\n  <nav>\n    <a href="#">Home</a>\n    <a href="#">About</a>\n    <a href="#">Contact</a>\n  </nav>\n</header>\n<main>\n  <article>\n    <h1>Title</h1>\n    <p>Paragraph 1</p>\n    <p>Paragraph 2</p>\n    <figure>\n      <img src="photo.jpg" alt="Description">\n      <figcaption>Caption</figcaption>\n    </figure>\n  </article>\n</main>\n<footer>Footer content</footer>'],
];

foreach ($htmlExamQuestions as $idx => $q) {
    $stmt = $pdo->prepare("INSERT INTO final_exam_questions (exam_id, question_type, question_text, options_json, correct_answer, starter_code, solution_code, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($q[0] === 'mcq') {
        $stmt->execute([$htmlExamId, 'mcq', $q[1], $q[2], $q[3], null, null, $q[4], $idx + 1]);
    } else {
        $stmt->execute([$htmlExamId, 'coding', $q[1], null, null, $q[5], $q[6], $q[4], $idx + 1]);
    }
}
echo "HTML Final Exam created (8 MCQ + 2 Coding)\n";

// CSS Final Exam
$pdo->exec("INSERT INTO final_exams (course_id, title, description, pass_mark, time_limit, mcq_count, coding_count) VALUES (2, 'CSS Final Assessment', 'Test your mastery of CSS layout, animations, responsive design, and modern features.', 70, 30, 8, 2)");
$cssExamId = $pdo->lastInsertId();

$cssExamQuestions = [
    ['mcq', 'Which display value creates a flex container?', '["display: block","display: flex","display: grid","display: inline"]', '1', 10],
    ['mcq', 'What does z-index control?', '["Font size","Element stacking order","Margin","Line height"]', '1', 10],
    ['mcq', 'Which media query targets screens wider than 768px?', '["@media (max-width: 768px)","@media (min-width: 768px)","@media (width: 768px)","@screen (768px)"]', '1', 10],
    ['mcq', 'What property creates smooth transitions?', '["animation","transform","transition","keyframe"]', '2', 10],
    ['mcq', 'Which CSS function references a custom property?', '["custom()","prop()","var()","ref()"]', '2', 10],
    ['mcq', 'What does position: sticky do?', '["Removes from flow","Scrolls with page then sticks","Centers element","Floats element"]', '1', 10],
    ['mcq', 'Which property controls flex item growth?', '["flex-size","flex-grow","flex-expand","flex-width"]', '1', 10],
    ['mcq', 'What does backdrop-filter: blur() create?', '["Text shadow","Box shadow","Glassmorphism effect","Image filter"]', '2', 10],
    ['coding', 'Create a CSS flexbox layout: a .navbar with space-between alignment, centered items vertically, 1rem padding, dark background (#0D1117), and a hover effect on .nav-link that changes color to gold (#F8B526) with a smooth transition.', null, null, 10, '.navbar {\n  /* Your flexbox styles */\n}\n\n.nav-link {\n  /* Your link styles */\n}\n\n.nav-link:hover {\n  /* Your hover styles */\n}', '.navbar { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #0D1117; }\n.nav-link { color: #fff; transition: color 0.3s ease; }\n.nav-link:hover { color: #F8B526; }'],
    ['coding', 'Create a CSS Grid layout for a .dashboard with: 3 equal columns on desktop, 1 column on mobile (below 768px), 1.5rem gap, and a .card component with dark background, border, padding, and a hover transform effect.', null, null, 10, '.dashboard {\n  /* Your grid styles */\n}\n\n.card {\n  /* Your card styles */\n}\n\n.card:hover {\n  /* Your hover effect */\n}\n\n@media (max-width: 768px) {\n  .dashboard {\n    /* Mobile styles */\n  }\n}', '.dashboard { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }\n.card { background: #151B23; border: 1px solid #2A3040; padding: 1.5rem; border-radius: 8px; transition: transform 0.3s; }\n.card:hover { transform: translateY(-4px); }\n@media (max-width: 768px) { .dashboard { grid-template-columns: 1fr; } }'],
];

foreach ($cssExamQuestions as $idx => $q) {
    $stmt = $pdo->prepare("INSERT INTO final_exam_questions (exam_id, question_type, question_text, options_json, correct_answer, starter_code, solution_code, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($q[0] === 'mcq') {
        $stmt->execute([$cssExamId, 'mcq', $q[1], $q[2], $q[3], null, null, $q[4], $idx + 1]);
    } else {
        $stmt->execute([$cssExamId, 'coding', $q[1], null, null, $q[5], $q[6], $q[4], $idx + 1]);
    }
}
echo "CSS Final Exam created (8 MCQ + 2 Coding)\n";

// JavaScript Final Exam
$pdo->exec("INSERT INTO final_exams (course_id, title, description, pass_mark, time_limit, mcq_count, coding_count) VALUES (3, 'JavaScript Final Assessment', 'Test your JavaScript skills: variables, DOM manipulation, async programming, and modern patterns.', 70, 45, 8, 2)");
$jsExamId = $pdo->lastInsertId();

$jsExamQuestions = [
    ['mcq', 'What is the difference between let and const?', '["No difference","let can be reassigned, const cannot","const is faster","let is global"]', '1', 10],
    ['mcq', 'What does Array.prototype.map() return?', '["A modified original array","A new array with transformed elements","A boolean","undefined"]', '1', 10],
    ['mcq', 'How do you stop a form from reloading the page?', '["return false","event.preventDefault()","event.stopSubmit()","form.cancel()"]', '1', 10],
    ['mcq', 'What does document.querySelector() return?', '["All matching elements","The first matching element or null","An array","A NodeList"]', '1', 10],
    ['mcq', 'What does async/await simplify?', '["Loops","Promise chains","DOM manipulation","CSS animations"]', '1', 10],
    ['mcq', 'Which operator checks value AND type?', '["==","===","!=","=>"]', '1', 10],
    ['mcq', 'What is the output of typeof []?', '["\"array\"","\"list\"","\"object\"","\"undefined\""]', '2', 10],
    ['mcq', 'What does the spread operator (...) do?', '["Creates a loop","Expands an iterable into individual elements","Defines a rest parameter","Concatenates strings"]', '1', 10],
    ['coding', 'Write a JavaScript function called filterStudents that takes an array of student objects (each with name and score properties) and returns only students with a score of 70 or higher. Use the array filter method.', null, null, 10, 'function filterStudents(students) {\n  // Your code here\n}\n\n// Example usage:\n// filterStudents([{name: "Amara", score: 95}, {name: "Kwame", score: 60}])\n// Should return: [{name: "Amara", score: 95}]', 'function filterStudents(students) {\n  return students.filter(s => s.score >= 70);\n}'],
    ['coding', 'Write an async function called fetchAndDisplay that: 1) Fetches data from a given URL using fetch(), 2) Parses the JSON response, 3) Returns the data, 4) Handles errors with try/catch and returns null on failure.', null, null, 10, 'async function fetchAndDisplay(url) {\n  // Your code here\n}', 'async function fetchAndDisplay(url) {\n  try {\n    const response = await fetch(url);\n    if (!response.ok) throw new Error("HTTP error");\n    const data = await response.json();\n    return data;\n  } catch (error) {\n    console.error(error);\n    return null;\n  }\n}'],
];

foreach ($jsExamQuestions as $idx => $q) {
    $stmt = $pdo->prepare("INSERT INTO final_exam_questions (exam_id, question_type, question_text, options_json, correct_answer, starter_code, solution_code, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($q[0] === 'mcq') {
        $stmt->execute([$jsExamId, 'mcq', $q[1], $q[2], $q[3], null, null, $q[4], $idx + 1]);
    } else {
        $stmt->execute([$jsExamId, 'coding', $q[1], null, null, $q[5], $q[6], $q[4], $idx + 1]);
    }
}
echo "JavaScript Final Exam created (8 MCQ + 2 Coding)\n\n";

echo "================================================\n";
echo "ALL CONTENT SEEDED SUCCESSFULLY!\n";
echo "================================================\n";
echo "Courses: 3\n";
echo "Modules: 12 (4 per course)\n";
echo "Lessons: 60 (20 per course)\n";
echo "Quizzes: 12 (4-5 questions each)\n";
echo "Final Exams: 3 (10 questions each)\n";
echo "================================================\n";
