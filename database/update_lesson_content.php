<?php
/**
 * HackathonAfrica LMS — Lesson Content & Exercise Migration
 * Run ONCE to update all lesson content and insert coding exercises.
 * Access: /database/update_lesson_content.php?key=hackafrica2026update
 * DELETE this file after running.
 */

define('UPDATE_KEY', 'hackafrica2026update');
if (($_GET['key'] ?? '') !== UPDATE_KEY) {
    http_response_code(403);
    die('<h2>403 — Access Denied</h2><p>Add ?key=hackafrica2026update to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();
$pdo->exec('PRAGMA foreign_keys = OFF');

$lessonsUpdated = 0;
$exercisesInserted = 0;

function updateLesson(PDO $pdo, string $title, string $content, int &$count): void {
    $stmt = $pdo->prepare('UPDATE lessons SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE title = ?');
    $stmt->execute([$content, $title]);
    if ($stmt->rowCount() > 0) $count++;
}

function addExercise(
    PDO $pdo, string $lessonTitle, string $title, string $description,
    string $instructions, string $starter, string $solution, string $hints,
    string $type, string $difficulty, int $points, int &$count
): void {
    $row = $pdo->prepare('SELECT id FROM lessons WHERE title = ? LIMIT 1');
    $row->execute([$lessonTitle]);
    $lesson = $row->fetch();
    if (!$lesson) { echo "<p style='color:orange'>&#9888; Lesson not found: $lessonTitle</p>"; return; }
    $stmt = $pdo->prepare(
        'INSERT INTO coding_exercises (lesson_id, title, description, instructions, starter_code, solution_code, hints, exercise_type, difficulty, points)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$lesson['id'], $title, $description, $instructions, $starter, $solution, $hints, $type, $difficulty, $points]);
    $count++;
}

// ── Schema patch: add columns that older DB setups may be missing ────────────
$existingCols = array_column($pdo->query('PRAGMA table_info(coding_exercises)')->fetchAll(), 'name');
if (!in_array('description', $existingCols)) {
    $pdo->exec("ALTER TABLE coding_exercises ADD COLUMN description TEXT NOT NULL DEFAULT ''");
    echo "<p>✅ Added column: coding_exercises.description</p>";
}
if (!in_array('difficulty', $existingCols)) {
    $pdo->exec("ALTER TABLE coding_exercises ADD COLUMN difficulty TEXT DEFAULT 'easy'");
    echo "<p>✅ Added column: coding_exercises.difficulty</p>";
}
if (!in_array('points', $existingCols)) {
    $pdo->exec("ALTER TABLE coding_exercises ADD COLUMN points INTEGER DEFAULT 10");
    echo "<p>✅ Added column: coding_exercises.points</p>";
}

// Also ensure lessons table has updated_at (older schemas may not have it)
$lessonCols = array_column($pdo->query('PRAGMA table_info(lessons)')->fetchAll(), 'name');
if (!in_array('updated_at', $lessonCols)) {
    $pdo->exec("ALTER TABLE lessons ADD COLUMN updated_at TEXT DEFAULT CURRENT_TIMESTAMP");
    echo "<p>✅ Added column: lessons.updated_at</p>";
}

// Clear all existing exercises — will be replaced with fresh data
$pdo->exec('DELETE FROM coding_exercises WHERE 1');

// =====================================================================
// HTML COURSE — Module 1
// =====================================================================

updateLesson($pdo, 'How the Web Works', <<<'HTML'
<h2>How the Web Works</h2>

<p>Every time you visit a website, your browser and a remote server perform a rapid back-and-forth conversation to deliver the page you see. Understanding this process is the foundation of all web development — it explains why code is structured the way it is and what actually happens when you hit Enter in your address bar.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Ordering food at a restaurant. You (the browser) tell the waiter (the internet) what you want. The waiter goes to the kitchen (the server), retrieves the food (HTML, CSS, JS files), and brings it back to your table. The restaurant has an address so you can find it — just like a website has a domain name.</p>
</div>

<h3>Clients and Servers</h3>
<p>The web is built on a simple two-party model. A <strong>client</strong> is any device that requests information — your laptop, phone, or tablet running a browser. A <strong>server</strong> is a computer that stores website files and responds to those requests. When you type a URL, your browser (client) sends a request across the internet, and the server sends back files your browser renders into a page.</p>
<div class="code-block">
<pre><code>You type: https://www.example.com/about

1. Browser asks DNS: "What's the IP for example.com?"
2. DNS replies: "It's 93.184.216.34"
3. Browser sends HTTP GET request to that IP
4. Server receives request, finds /about page
5. Server sends back HTML file (200 OK)
6. Browser reads HTML, requests linked CSS and JS files
7. Browser renders everything into the visual page</code></pre>
</div>

<h3>HTTP and HTTPS</h3>
<p>HTTP (HyperText Transfer Protocol) is the language browsers and servers use to communicate. Every request has a <strong>method</strong> (GET to fetch data, POST to send data), and every response has a <strong>status code</strong>. HTTPS is the secure version — it encrypts data in transit so no one can intercept it. Modern browsers flag non-HTTPS sites as "not secure," so always use HTTPS in production.</p>
<div class="code-block">
<pre><code>Common HTTP status codes:
200 OK           — Request succeeded, here's the content
301 Moved        — Page moved permanently (redirect)
404 Not Found    — Server can't find that page
500 Server Error — Something broke on the server side

A GET request looks like:
GET /about HTTP/1.1
Host: www.example.com
Accept: text/html</code></pre>
</div>

<h3>DNS — The Internet's Phone Book</h3>
<p>Domain Name System (DNS) translates human-readable domain names like <code>google.com</code> into numeric IP addresses like <code>142.250.80.46</code> that computers use to locate each other. This lookup happens automatically every time you visit a new site. DNS is why you can type a name instead of memorising a number — it's a global distributed directory maintained across thousands of servers worldwide.</p>
<div class="code-block">
<pre><code>Domain resolution chain:
www.example.com
  → Root DNS servers (know where .com lives)
  → .com nameservers (know where example.com lives)
  → example.com nameservers (return the actual IP)
  → Your browser caches the result for faster future visits</code></pre>
</div>

<h3>What the Browser Does With HTML</h3>
<p>Once the browser receives an HTML file, it parses it top to bottom, building a Document Object Model (DOM) — a tree-shaped representation of the page. When it encounters a link to a CSS file, it fetches that next. When it finds a script tag, it may pause to execute the JavaScript. Understanding this order matters because it affects page load speed and why developers put CSS in the <code>&lt;head&gt;</code> and scripts at the bottom of <code>&lt;body&gt;</code>.</p>

<h3>When to Use This</h3>
<p>This knowledge is foundational — it informs every decision you make as a web developer. When you're debugging why a page is slow, you're thinking about requests. When you're setting up hosting, you're configuring servers and DNS. When you use HTTPS, you're applying this knowledge.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Confusing client and server code:</strong> HTML/CSS/JS run in the browser (client-side). PHP/Python/Node run on the server. They're in different places and can't directly call each other.</li>
    <li><strong>Ignoring HTTPS:</strong> Building or testing on HTTP is fine locally, but always deploy with HTTPS. Browsers block certain features (like geolocation) on non-secure origins.</li>
    <li><strong>Thinking DNS changes are instant:</strong> DNS records can take up to 48 hours to propagate worldwide after you change them. Plan deployments accordingly.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>The web works through a request-response cycle between clients (browsers) and servers.</li>
    <li>HTTP/HTTPS is the protocol for communication; DNS translates domain names to IP addresses.</li>
    <li>The browser parses HTML into a DOM tree and fetches linked resources (CSS, JS, images) separately.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Setting Up Your Development Environment', <<<'HTML'
<h2>Setting Up Your Development Environment</h2>

<p>Before writing a single line of code, you need the right tools. A good development environment makes coding faster, catches errors early, and lets you preview your work instantly. Setting this up correctly from the start saves hours of frustration later.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A carpenter's workshop. You could build furniture with just a hammer, but having the right saws, measuring tools, and workbench makes every job faster and more precise. Your code editor, browser, and folder structure are your workshop — worth setting up properly once.</p>
</div>

<h3>Choosing a Code Editor</h3>
<p>A code editor is purpose-built for writing code. Unlike a word processor, it understands programming languages — it colour-codes your syntax, catches typos, and offers autocomplete suggestions. <strong>Visual Studio Code (VS Code)</strong> is the industry standard for web development: it's free, fast, and has thousands of extensions. Download it from <code>code.visualstudio.com</code>. Once installed, add the <strong>Live Server</strong> extension so your browser auto-refreshes whenever you save a file.</p>
<div class="code-block">
<pre><code>Recommended VS Code extensions for web development:
- Live Server          — auto-refresh browser on file save
- Prettier             — auto-formats your code consistently
- ESLint               — catches JavaScript errors as you type
- Auto Rename Tag      — renames closing HTML tag when you edit opening tag
- GitLens              — enhanced Git integration</code></pre>
</div>

<h3>Organising Your Project Folder</h3>
<p>A clean folder structure prevents confusion as your project grows. Start with a root folder named after your project, then create subfolders for different file types. Keeping files organised also makes collaboration and deployment much smoother.</p>
<div class="code-block">
<pre><code>my-website/
├── index.html          ← main HTML file (homepage)
├── about.html          ← other pages
├── css/
│   └── style.css       ← stylesheets
├── js/
│   └── main.js         ← JavaScript files
└── images/
    └── logo.png        ← images and media</code></pre>
</div>

<h3>Browser Developer Tools</h3>
<p>Every modern browser comes with built-in developer tools — one of the most powerful debugging aids available. Open them with <kbd>F12</kbd> or right-click any element and choose "Inspect". The key panels are: <strong>Elements</strong> (view and edit HTML/CSS live), <strong>Console</strong> (see JavaScript errors and run code), <strong>Network</strong> (watch every request the page makes), and <strong>Sources</strong> (debug JavaScript with breakpoints).</p>
<div class="code-block">
<pre><code>Browser DevTools quick reference:
F12 or Ctrl+Shift+I (Cmd+Option+I on Mac) — open DevTools

Elements panel  → Inspect and live-edit HTML/CSS
Console panel   → See errors, run JS snippets
Network panel   → Monitor file requests and load times
Application tab → View cookies, localStorage, cache</code></pre>
</div>

<h3>Your First HTML File</h3>
<p>Create a file called <code>index.html</code> in your project folder. In VS Code, type <code>!</code> and press <kbd>Tab</kbd> — Emmet (built into VS Code) generates a complete HTML boilerplate instantly. Right-click the file in VS Code's file explorer and choose "Open with Live Server" to see it in your browser. Every time you save, the browser updates automatically.</p>
<div class="code-block">
<pre><code>&lt;!-- The ! + Tab shortcut produces this in VS Code: --&gt;
&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Document&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;!-- Your content goes here --&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Every project starts with this setup. Invest time here: a good environment with Live Server and Prettier running will accelerate every lesson that follows. When something looks wrong, DevTools is always your first stop for diagnosis.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Opening HTML files directly in the browser:</strong> Double-clicking an HTML file uses the <code>file://</code> protocol, not <code>http://</code>. Some features won't work. Always use Live Server for an accurate preview.</li>
    <li><strong>Saving without checking the editor:</strong> Many beginners edit a file but forget to save before refreshing the browser. Enable VS Code's auto-save (File → Auto Save) to avoid this.</li>
    <li><strong>Naming files with spaces:</strong> Use hyphens or underscores in filenames — <code>my-page.html</code> not <code>my page.html</code>. Spaces in file names cause broken links.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>VS Code with the Live Server extension is the recommended setup for web development.</li>
    <li>Organise projects with separate folders for CSS, JS, and images from the start.</li>
    <li>Browser DevTools (F12) is your most powerful debugging tool — learn it early.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'HTML Document Structure', <<<'HTML'
<h2>HTML Document Structure</h2>

<p>Every HTML page follows a required structure — a skeleton that browsers expect to find. Without this structure, browsers have to guess what you mean, leading to inconsistent rendering. Learning this skeleton once means every page you ever write starts on solid ground.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A formal letter. It has a required structure: your address at the top, a date, a salutation, the body, and a closing. You wouldn't write a business letter starting in the middle — the structure exists so the reader knows what to expect where. HTML documents work the same way.</p>
</div>

<h3>The DOCTYPE Declaration</h3>
<p>The very first line of every HTML file must be <code>&lt;!DOCTYPE html&gt;</code>. This isn't an HTML tag — it's an instruction to the browser telling it which version of HTML to use. Without it, browsers enter "quirks mode" — a compatibility mode that emulates old, broken behaviour. Always include DOCTYPE. It's one line that prevents a world of rendering bugs.</p>
<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;
&lt;!-- This single line tells the browser: "Use modern HTML5 standards" --&gt;
&lt;!-- Everything else comes after this --&gt;</code></pre>
</div>

<h3>The html, head, and body Elements</h3>
<p>After DOCTYPE, the page is divided into two main sections wrapped in an <code>&lt;html&gt;</code> element. The <strong>&lt;head&gt;</strong> contains metadata — information about the page (title, character set, linked CSS) that doesn't appear in the visible page content. The <strong>&lt;body&gt;</strong> contains everything the user actually sees: text, images, buttons, and all visible elements. This separation is fundamental — anything in the head configures the page; anything in the body is the page.</p>
<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;      &lt;!-- lang attribute helps screen readers and search engines --&gt;

  &lt;head&gt;
    &lt;meta charset="UTF-8"&gt;                           &lt;!-- character encoding --&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My Page Title&lt;/title&gt;                    &lt;!-- appears in browser tab --&gt;
    &lt;link rel="stylesheet" href="css/style.css"&gt;    &lt;!-- link to CSS --&gt;
  &lt;/head&gt;

  &lt;body&gt;
    &lt;h1&gt;Hello, World!&lt;/h1&gt;           &lt;!-- this is visible on the page --&gt;
    &lt;p&gt;This content the user sees.&lt;/p&gt;
  &lt;/body&gt;

&lt;/html&gt;</code></pre>
</div>

<h3>Essential Meta Tags</h3>
<p>Meta tags live in the <code>&lt;head&gt;</code> and provide information to browsers, search engines, and social media platforms. Two are required on every page: the <strong>charset</strong> meta tag (tells the browser how to decode text — UTF-8 supports all languages and emojis) and the <strong>viewport</strong> meta tag (prevents mobile browsers from scaling the page down to desktop size, which would make text tiny). Skipping the viewport tag is why beginners often wonder why their page looks wrong on a phone.</p>
<div class="code-block">
<pre><code>&lt;head&gt;
  &lt;!-- Required: defines character encoding --&gt;
  &lt;meta charset="UTF-8"&gt;

  &lt;!-- Required for mobile: sets viewport to device width --&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;

  &lt;!-- Recommended: describes page for search engines --&gt;
  &lt;meta name="description" content="A beginner's guide to HTML structure."&gt;

  &lt;!-- The tab title and browser bookmark label --&gt;
  &lt;title&gt;HTML Basics | My Site&lt;/title&gt;
&lt;/head&gt;</code></pre>
</div>

<h3>Linking CSS and JavaScript</h3>
<p>External CSS files are linked using <code>&lt;link&gt;</code> in the <code>&lt;head&gt;</code> so styles are applied before the page renders (preventing a flash of unstyled content). JavaScript files are linked with <code>&lt;script&gt;</code> and are traditionally placed at the end of <code>&lt;body&gt;</code>, just before the closing tag. This ensures all HTML elements exist before JavaScript tries to interact with them. Modern JS can also use the <code>defer</code> attribute on script tags in the head for the same effect.</p>
<div class="code-block">
<pre><code>&lt;!-- CSS goes in the &lt;head&gt; --&gt;
&lt;link rel="stylesheet" href="css/style.css"&gt;

&lt;!-- JavaScript goes before &lt;/body&gt; --&gt;
&lt;script src="js/main.js"&gt;&lt;/script&gt;

&lt;!-- OR use defer attribute to load in &lt;head&gt; without blocking --&gt;
&lt;script src="js/main.js" defer&gt;&lt;/script&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>This structure is the starting point for every single HTML file you create. Use the VS Code <code>!</code> + Tab shortcut to generate it automatically, then customise the title and language attribute for your project.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Missing the viewport meta tag:</strong> Without it, mobile browsers shrink your desktop page to fit the screen. Your text becomes unreadably small. Always include it.</li>
    <li><strong>Putting visible content in &lt;head&gt;:</strong> The head is for metadata only. Images, paragraphs, and headings belong in the body — placing them in head causes them to be hidden or produce errors.</li>
    <li><strong>Forgetting the lang attribute:</strong> <code>&lt;html lang="en"&gt;</code> is important for screen readers and SEO. Use the two-letter language code that matches your page's content language.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Every HTML file must start with <code>&lt;!DOCTYPE html&gt;</code> followed by <code>&lt;html&gt;</code>, <code>&lt;head&gt;</code>, and <code>&lt;body&gt;</code>.</li>
    <li>The <code>&lt;head&gt;</code> holds metadata (charset, viewport, title, CSS links); the <code>&lt;body&gt;</code> holds visible content.</li>
    <li>Always include <code>charset="UTF-8"</code> and the viewport meta tag for correct rendering across all devices.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Headings, Paragraphs & Text', <<<'HTML'
<h2>Headings, Paragraphs &amp; Text</h2>

<p>Text is the backbone of most web pages. HTML provides a set of elements specifically for marking up text with meaning — headings that create hierarchy, paragraphs that group sentences, and inline elements that emphasise or annotate words. Using these elements correctly makes your content readable, accessible, and well-structured for search engines.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A newspaper. It has a big bold headline (h1), section headers (h2, h3), body paragraphs, and occasionally bold or italic text for emphasis. The structure isn't just visual — it tells readers (and screen readers) what's most important and how the content is organised.</p>
</div>

<h3>Headings: h1 Through h6</h3>
<p>HTML has six heading levels, <code>&lt;h1&gt;</code> through <code>&lt;h6&gt;</code>. They create a logical hierarchy, like an outline. <code>&lt;h1&gt;</code> is the most important — use it once per page for the main topic. <code>&lt;h2&gt;</code> marks major sections, <code>&lt;h3&gt;</code> marks subsections, and so on. Screen readers use heading levels to build a navigation menu for blind users, so never skip levels (don't jump from h1 to h4) and never use headings just to make text bigger — that's what CSS is for.</p>
<div class="code-block">
<pre><code>&lt;h1&gt;Introduction to Web Development&lt;/h1&gt;  &lt;!-- page title, used once --&gt;

  &lt;h2&gt;What is HTML?&lt;/h2&gt;                  &lt;!-- major section --&gt;
    &lt;h3&gt;HTML Elements&lt;/h3&gt;               &lt;!-- subsection --&gt;
    &lt;h3&gt;HTML Attributes&lt;/h3&gt;             &lt;!-- another subsection --&gt;

  &lt;h2&gt;What is CSS?&lt;/h2&gt;                   &lt;!-- next major section --&gt;</code></pre>
</div>

<h3>Paragraphs and Line Breaks</h3>
<p>The <code>&lt;p&gt;</code> element wraps a paragraph of text. Browsers automatically add spacing above and below paragraphs, making text readable without needing extra blank lines in your HTML. For a line break within a paragraph (like in an address or poem), use <code>&lt;br&gt;</code> — a void element with no closing tag. Don't use multiple <code>&lt;br&gt;</code> tags to create visual spacing between sections; use CSS margins instead.</p>
<div class="code-block">
<pre><code>&lt;p&gt;This is the first paragraph. It can be as long as needed
and will wrap naturally to the container width.&lt;/p&gt;

&lt;p&gt;This is a second paragraph. Notice the browser adds
space between paragraphs automatically.&lt;/p&gt;

&lt;p&gt;
  123 Main Street&lt;br&gt;       &lt;!-- line break within a paragraph --&gt;
  Nairobi, Kenya&lt;br&gt;
  KE-00100
&lt;/p&gt;</code></pre>
</div>

<h3>Inline Text Elements</h3>
<p>While headings and paragraphs are block elements (they take a full line), inline elements sit inside text and format individual words or phrases. <code>&lt;strong&gt;</code> marks important text (renders bold by default). <code>&lt;em&gt;</code> marks emphasised text (renders italic). Use these for meaning, not appearance — if you just want bold text for style, use CSS. Other useful inline elements include <code>&lt;code&gt;</code> for code snippets, <code>&lt;mark&gt;</code> for highlighted text, and <code>&lt;small&gt;</code> for fine print.</p>
<div class="code-block">
<pre><code>&lt;p&gt;
  &lt;strong&gt;Warning:&lt;/strong&gt; Do not skip the DOCTYPE declaration.
  It is &lt;em&gt;extremely&lt;/em&gt; important for correct rendering.
&lt;/p&gt;

&lt;p&gt;Use the &lt;code&gt;console.log()&lt;/code&gt; function to debug JavaScript.&lt;/p&gt;

&lt;p&gt;The sale price is &lt;mark&gt;50% off&lt;/mark&gt; this weekend only.&lt;/p&gt;

&lt;p&gt;Regular price: $100. &lt;small&gt;Terms and conditions apply.&lt;/small&gt;&lt;/p&gt;</code></pre>
</div>

<h3>Preformatted Text and Quotations</h3>
<p>The <code>&lt;pre&gt;</code> element preserves whitespace and line breaks exactly as written — ideal for code samples. <code>&lt;blockquote&gt;</code> marks a long quotation from another source, and <code>&lt;q&gt;</code> is for short inline quotes. These elements carry semantic meaning that helps both search engines and assistive technologies understand your content.</p>
<div class="code-block">
<pre><code>&lt;pre&gt;
  This text preserves
    all spaces   and
  line breaks exactly.
&lt;/pre&gt;

&lt;blockquote cite="https://example.com"&gt;
  &lt;p&gt;The best way to learn web development is to build things.&lt;/p&gt;
&lt;/blockquote&gt;

&lt;p&gt;He said &lt;q&gt;practice every day&lt;/q&gt; and it works.&lt;/p&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use semantic text elements on every page. Headings for structure and hierarchy, paragraphs for body text, strong/em for meaningful emphasis. Always think about what the text means, not just how you want it to look — appearance is CSS's job.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Using headings for visual size:</strong> Don't pick h3 because it looks like the size you want. Pick the heading level that matches the content hierarchy, then use CSS to resize it.</li>
    <li><strong>Multiple h1 elements:</strong> Each page should have exactly one h1 — the page's main topic. Search engines use it to understand what the page is about.</li>
    <li><strong>Using &lt;br&gt; for spacing:</strong> Break tags create line breaks, not spacing. Use CSS <code>margin</code> or <code>padding</code> to add space between elements.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use h1–h6 headings to create a logical document outline; never skip levels or use them purely for visual size.</li>
    <li>Wrap body text in <code>&lt;p&gt;</code> tags; use <code>&lt;br&gt;</code> only for intentional line breaks (like addresses), not for spacing.</li>
    <li>Inline elements like <code>&lt;strong&gt;</code> and <code>&lt;em&gt;</code> add semantic meaning — use them for importance/emphasis, not just appearance.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Links and Navigation', <<<'HTML'
<h2>Links and Navigation</h2>

<p>Links are what make the web a <em>web</em> — they connect pages, sites, and resources into an interconnected network. The anchor element <code>&lt;a&gt;</code> is one of the most important HTML elements you'll use. Understanding how to write links correctly, including when to use relative vs absolute URLs and how to make navigation accessible, is essential knowledge for every web developer.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Road signs. A sign saying "City Centre — 5km" is like an absolute URL (complete directions from where you are). A sign inside a building saying "Room 204 — upstairs" is like a relative URL (directions relative to your current location). Both get you somewhere, but you use them in different contexts.</p>
</div>

<h3>The Anchor Element</h3>
<p>The <code>&lt;a&gt;</code> (anchor) element creates a hyperlink. The <code>href</code> attribute (hypertext reference) specifies the destination. Anything between the opening and closing tags becomes the clickable link — text, images, or even other elements. The text inside the link should describe the destination meaningfully. "Click here" tells no one anything; "Download our pricing guide" tells users exactly where they're going.</p>
<div class="code-block">
<pre><code>&lt;!-- Basic text link --&gt;
&lt;a href="https://www.example.com"&gt;Visit Example.com&lt;/a&gt;

&lt;!-- Image link --&gt;
&lt;a href="https://www.example.com"&gt;
  &lt;img src="images/logo.png" alt="Example Company"&gt;
&lt;/a&gt;

&lt;!-- Link that opens in a new tab --&gt;
&lt;a href="https://www.example.com" target="_blank" rel="noopener noreferrer"&gt;
  Opens in new tab
&lt;/a&gt;</code></pre>
</div>

<h3>Relative vs Absolute URLs</h3>
<p>An <strong>absolute URL</strong> includes the full address: protocol, domain, and path. Use these when linking to other websites. A <strong>relative URL</strong> is a path relative to the current file's location. Use these when linking to pages within your own site — they keep working if you move your site to a different domain, and they're shorter to write. Understanding file paths is key: <code>../</code> means "go up one folder."</p>
<div class="code-block">
<pre><code>&lt;!-- Absolute URL — links to another website --&gt;
&lt;a href="https://www.google.com"&gt;Google&lt;/a&gt;

&lt;!-- Relative URLs — links within your own site --&gt;
&lt;a href="about.html"&gt;About&lt;/a&gt;               &lt;!-- same folder --&gt;
&lt;a href="pages/contact.html"&gt;Contact&lt;/a&gt;     &lt;!-- in a subfolder --&gt;
&lt;a href="../index.html"&gt;Home&lt;/a&gt;             &lt;!-- one folder up --&gt;
&lt;a href="/services.html"&gt;Services&lt;/a&gt;        &lt;!-- from site root --&gt;</code></pre>
</div>

<h3>Navigation with nav and ul</h3>
<p>A website's main navigation menu is built using the <code>&lt;nav&gt;</code> semantic element containing an unordered list of links. This structure is standard practice — it communicates purpose to browsers and screen readers, and it's easy to style with CSS. Each menu item is an <code>&lt;li&gt;</code> containing an <code>&lt;a&gt;</code> tag. CSS is then used to display the list horizontally (using flexbox) and style the visual appearance.</p>
<div class="code-block">
<pre><code>&lt;nav&gt;
  &lt;ul&gt;
    &lt;li&gt;&lt;a href="index.html"&gt;Home&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="about.html"&gt;About&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="services.html"&gt;Services&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="contact.html"&gt;Contact&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
&lt;/nav&gt;</code></pre>
</div>

<h3>Anchor Links and Special HREFs</h3>
<p>Links can also jump to sections within the same page (anchor links), trigger email clients, or initiate phone calls. Anchor links use an <code>id</code> attribute on the target element and a <code>#id</code> href. These are used for "back to top" buttons, table-of-contents links on long articles, and single-page website navigation.</p>
<div class="code-block">
<pre><code>&lt;!-- Jump to a section on the same page --&gt;
&lt;a href="#contact"&gt;Jump to Contact Section&lt;/a&gt;

&lt;!-- The target section needs a matching id --&gt;
&lt;section id="contact"&gt;
  &lt;h2&gt;Contact Us&lt;/h2&gt;
&lt;/section&gt;

&lt;!-- Open default email client --&gt;
&lt;a href="mailto:hello@example.com"&gt;Email Us&lt;/a&gt;

&lt;!-- Initiate a phone call on mobile --&gt;
&lt;a href="tel:+254712345678"&gt;Call Us&lt;/a&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use <code>&lt;a&gt;</code> for all navigation and cross-references. Use <code>&lt;nav&gt;</code> for your site's primary and secondary navigation menus. Use descriptive link text always — avoid "click here" or "read more" as standalone link text.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Missing rel="noopener noreferrer" on target="_blank" links:</strong> Opening links in new tabs without this attribute creates a security vulnerability (tab-napping). Always pair <code>target="_blank"</code> with <code>rel="noopener noreferrer"</code>.</li>
    <li><strong>Vague link text:</strong> Screen readers often list all links on a page. "Click here" repeated 10 times is useless. Use descriptive text that makes sense out of context.</li>
    <li><strong>Using wrong path separators:</strong> Web URLs use forward slashes <code>/</code>, not backslashes <code>\</code>. Backslashes break links on web servers even if they work on Windows locally.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>The <code>&lt;a href=""&gt;</code> element creates links; the href value is the destination URL or path.</li>
    <li>Use relative URLs for internal links, absolute URLs for external sites; always add <code>rel="noopener noreferrer"</code> with <code>target="_blank"</code>.</li>
    <li>Navigation menus use <code>&lt;nav&gt;&lt;ul&gt;&lt;li&gt;&lt;a&gt;</code> structure for semantics, accessibility, and easy CSS styling.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// HTML COURSE — Module 2
// =====================================================================

updateLesson($pdo, 'Introduction to HTML Forms', <<<'HTML'
<h2>Introduction to HTML Forms</h2>

<p>Forms are how users send information to a website — search queries, login credentials, orders, contact messages. Every form on the web is built with HTML's form elements. Understanding the structure of a form is the prerequisite to building any interactive page that collects user input.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A paper form at a doctor's office. There's a title telling you what the form is for, labelled fields where you fill in your name and date of birth, checkboxes for conditions, and a submit button at the end. HTML forms work exactly the same way — just digital.</p>
</div>

<h3>The form Element</h3>
<p>The <code>&lt;form&gt;</code> element is the container for all form controls. Its two most important attributes are <code>action</code> (the URL where form data is sent) and <code>method</code> (how data is sent — GET appends data to the URL, POST sends it in the request body). For anything involving passwords or sensitive data, always use <code>method="post"</code>. GET is suitable for search forms where you want the query to be bookmarkable.</p>
<div class="code-block">
<pre><code>&lt;!-- A contact form that submits to a server script --&gt;
&lt;form action="/send-message.php" method="post"&gt;
  &lt;!-- form controls go here --&gt;
&lt;/form&gt;

&lt;!-- A search form — GET is appropriate here --&gt;
&lt;form action="/search" method="get"&gt;
  &lt;input type="text" name="q" placeholder="Search..."&gt;
  &lt;button type="submit"&gt;Search&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Labels and Inputs</h3>
<p>Every form field needs a <code>&lt;label&gt;</code>. Labels serve two purposes: they tell users what to enter, and they associate text with a form control so clicking the label focuses the input (a big usability win on mobile). Connect a label to its input using matching <code>for</code> and <code>id</code> attributes. The <code>&lt;input&gt;</code> element is a void element (no closing tag) with a <code>type</code> attribute defining what kind of input it is.</p>
<div class="code-block">
<pre><code>&lt;form action="/signup" method="post"&gt;

  &lt;label for="username"&gt;Username&lt;/label&gt;
  &lt;input type="text" id="username" name="username" placeholder="Enter a username"&gt;

  &lt;label for="email"&gt;Email Address&lt;/label&gt;
  &lt;input type="email" id="email" name="email" placeholder="you@example.com"&gt;

  &lt;label for="password"&gt;Password&lt;/label&gt;
  &lt;input type="password" id="password" name="password"&gt;

  &lt;button type="submit"&gt;Create Account&lt;/button&gt;

&lt;/form&gt;</code></pre>
</div>

<h3>Textarea and Select</h3>
<p>For multi-line text (like a message body), use <code>&lt;textarea&gt;</code>. Unlike inputs, textareas have opening and closing tags, and the default content goes between them. For choosing from a list of options, use <code>&lt;select&gt;</code> with <code>&lt;option&gt;</code> children. The <code>value</code> attribute on each option is what gets submitted — the text between the tags is just what the user sees.</p>
<div class="code-block">
<pre><code>&lt;label for="message"&gt;Your Message&lt;/label&gt;
&lt;textarea id="message" name="message" rows="5" cols="40"&gt;
  Default text here (optional)
&lt;/textarea&gt;

&lt;label for="country"&gt;Country&lt;/label&gt;
&lt;select id="country" name="country"&gt;
  &lt;option value=""&gt;-- Select a country --&lt;/option&gt;
  &lt;option value="ke"&gt;Kenya&lt;/option&gt;
  &lt;option value="ng"&gt;Nigeria&lt;/option&gt;
  &lt;option value="gh"&gt;Ghana&lt;/option&gt;
&lt;/select&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use forms any time you need to collect user input: contact pages, login/signup, search, checkout, surveys. Always pair every input with a label, and always choose POST for sensitive data. Even if you're using JavaScript to process form data, start with proper HTML form structure.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Missing name attributes:</strong> The <code>name</code> attribute is what labels the data when it's submitted. An input without a name doesn't get included in the form submission at all.</li>
    <li><strong>Using GET for sensitive data:</strong> GET appends form data to the URL (visible in the browser bar and server logs). Never use GET for passwords, payment details, or personal information.</li>
    <li><strong>Labels not connected to inputs:</strong> A visual label placed next to an input is not the same as a properly associated label. Always use matching for/id or wrap the input inside the label.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>The <code>&lt;form&gt;</code> element needs <code>action</code> (where to send data) and <code>method</code> (GET or POST).</li>
    <li>Every input must have a <code>name</code> attribute to be submitted and a paired <code>&lt;label&gt;</code> for usability.</li>
    <li>Use <code>&lt;textarea&gt;</code> for multi-line text and <code>&lt;select&gt;</code>/<code>&lt;option&gt;</code> for dropdown choices.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Input Types', <<<'HTML'
<h2>Input Types</h2>

<p>HTML's <code>&lt;input&gt;</code> element does far more than display a text box. By changing the <code>type</code> attribute, you get specialised controls — date pickers, colour selectors, range sliders, file uploaders — all without any JavaScript. Each type also tells the browser and mobile devices how to behave: a phone keyboard appears for <code>type="tel"</code>, a numeric keypad for <code>type="number"</code>.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Different tools in a toolkit. A screwdriver and a hammer are both "tools," but you choose the right one for the job. Similarly, all input types are the same <code>&lt;input&gt;</code> element — you just pick the right type for the data you're collecting.</p>
</div>

<h3>Text-Based Input Types</h3>
<p>The most commonly used input types handle text in different ways. <code>type="text"</code> is the generic single-line input. <code>type="email"</code> validates that the user enters an email address format and shows an email keyboard on mobile. <code>type="password"</code> masks characters. <code>type="search"</code> is similar to text but may show a clear button. <code>type="url"</code> validates URL format. <code>type="tel"</code> shows a phone keypad on mobile without validating format (phone formats vary too much globally).</p>
<div class="code-block">
<pre><code>&lt;input type="text"     name="username"  placeholder="Your name"&gt;
&lt;input type="email"    name="email"     placeholder="you@example.com"&gt;
&lt;input type="password" name="password"  placeholder="Min. 8 characters"&gt;
&lt;input type="tel"      name="phone"     placeholder="+254 712 345 678"&gt;
&lt;input type="url"      name="website"   placeholder="https://yoursite.com"&gt;
&lt;input type="search"   name="q"         placeholder="Search..."&gt;</code></pre>
</div>

<h3>Number, Range, and Date Types</h3>
<p>For numerical data, <code>type="number"</code> shows increment/decrement arrows and validates numeric input. You can set <code>min</code>, <code>max</code>, and <code>step</code> attributes to constrain the range. <code>type="range"</code> creates a draggable slider for the same number range. For dates and times, <code>type="date"</code>, <code>type="time"</code>, <code>type="datetime-local"</code>, and <code>type="month"</code> provide native browser date pickers — no JavaScript library required.</p>
<div class="code-block">
<pre><code>&lt;!-- Number with constraints --&gt;
&lt;input type="number" name="age" min="0" max="120" step="1" value="25"&gt;

&lt;!-- Slider for a rating from 1-10 --&gt;
&lt;input type="range" name="rating" min="1" max="10" step="1" value="5"&gt;

&lt;!-- Native date picker --&gt;
&lt;input type="date" name="birthday"&gt;

&lt;!-- Time picker --&gt;
&lt;input type="time" name="meeting_time"&gt;

&lt;!-- Date + time together --&gt;
&lt;input type="datetime-local" name="event_start"&gt;</code></pre>
</div>

<h3>Checkboxes and Radio Buttons</h3>
<p>Checkboxes allow multiple selections from a group (yes/no questions, multi-select features). Radio buttons allow only one selection from a group — they're linked by sharing the same <code>name</code> attribute. The browser enforces the single-selection rule automatically. Both use the <code>value</code> attribute to define what's submitted when selected, and both benefit from being wrapped in or paired with labels.</p>
<div class="code-block">
<pre><code>&lt;!-- Checkboxes — multiple can be selected --&gt;
&lt;label&gt;&lt;input type="checkbox" name="interests" value="html"&gt; HTML&lt;/label&gt;
&lt;label&gt;&lt;input type="checkbox" name="interests" value="css"&gt; CSS&lt;/label&gt;
&lt;label&gt;&lt;input type="checkbox" name="interests" value="js"&gt; JavaScript&lt;/label&gt;

&lt;!-- Radio buttons — only one can be selected (same name groups them) --&gt;
&lt;label&gt;&lt;input type="radio" name="level" value="beginner"&gt; Beginner&lt;/label&gt;
&lt;label&gt;&lt;input type="radio" name="level" value="intermediate"&gt; Intermediate&lt;/label&gt;
&lt;label&gt;&lt;input type="radio" name="level" value="advanced"&gt; Advanced&lt;/label&gt;</code></pre>
</div>

<h3>File, Color, and Hidden Types</h3>
<p><code>type="file"</code> opens the system file picker — add the <code>accept</code> attribute to limit file types, and <code>multiple</code> to allow selecting several files. <code>type="color"</code> opens a native colour picker. <code>type="hidden"</code> is invisible to the user but submits a value with the form — useful for passing data like a record ID along with user input without displaying it.</p>
<div class="code-block">
<pre><code>&lt;!-- File upload --&gt;
&lt;input type="file" name="avatar" accept="image/*"&gt;
&lt;input type="file" name="documents" accept=".pdf,.doc" multiple&gt;

&lt;!-- Colour picker --&gt;
&lt;input type="color" name="theme_color" value="#ff6600"&gt;

&lt;!-- Hidden value submitted with form --&gt;
&lt;input type="hidden" name="user_id" value="42"&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always use the most specific input type for the data you're collecting. It improves mobile UX (right keyboard), enables built-in browser validation, and signals intent to assistive technologies. Never use <code>type="text"</code> for an email field just because it "looks the same."</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Using type="text" for everything:</strong> Email, number, date, and URL inputs all have specific types that provide validation and better mobile keyboards. Use them.</li>
    <li><strong>Radio buttons with different names:</strong> Radio buttons in the same group MUST share the same <code>name</code> attribute. Different names mean they won't be mutually exclusive.</li>
    <li><strong>Forgetting checked/selected defaults:</strong> For checkboxes and radios, add the <code>checked</code> attribute to pre-select a default. For select dropdowns, add <code>selected</code> to the default option.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>The <code>type</code> attribute transforms a basic input into a specialised control with built-in behaviour.</li>
    <li>Use specific types (email, number, date, tel) to get correct mobile keyboards and basic browser validation for free.</li>
    <li>Radio buttons sharing the same <code>name</code> form a group where only one can be selected; checkboxes allow multiple selections.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Form Validation', <<<'HTML'
<h2>Form Validation</h2>

<p>Form validation ensures users submit complete and correctly formatted data before it reaches your server. HTML5 introduced native browser validation that requires zero JavaScript — just a few attributes on your inputs. This catches obvious errors instantly, provides immediate feedback to users, and reduces the load on your backend. Server-side validation is still essential, but HTML validation is your first line of defence.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A bouncer at a club checking IDs at the door. They catch problems before anyone gets inside, saving everyone time and preventing bigger issues later. Your HTML validation is that first checkpoint — quick, automatic, and right at the point of entry.</p>
</div>

<h3>The required Attribute</h3>
<p>The simplest validation attribute is <code>required</code>. Add it to any input and the browser will prevent form submission if that field is empty, showing a native error message. It works on text inputs, email inputs, checkboxes, radio buttons, select dropdowns, and textareas. It requires no CSS, no JavaScript — the browser handles everything. This alone handles the most common validation need: making sure users don't leave important fields blank.</p>
<div class="code-block">
<pre><code>&lt;form action="/register" method="post"&gt;

  &lt;label for="name"&gt;Full Name *&lt;/label&gt;
  &lt;input type="text" id="name" name="name" required&gt;

  &lt;label for="email"&gt;Email *&lt;/label&gt;
  &lt;input type="email" id="email" name="email" required&gt;

  &lt;!-- Required select: first option has empty value --&gt;
  &lt;label for="country"&gt;Country *&lt;/label&gt;
  &lt;select id="country" name="country" required&gt;
    &lt;option value=""&gt;Choose a country...&lt;/option&gt;
    &lt;option value="ke"&gt;Kenya&lt;/option&gt;
  &lt;/select&gt;

  &lt;button type="submit"&gt;Register&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Length and Pattern Constraints</h3>
<p><code>minlength</code> and <code>maxlength</code> set minimum and maximum character counts for text inputs. <code>min</code> and <code>max</code> constrain number and date ranges. The <code>pattern</code> attribute accepts a regular expression — the input value must match it. For example, you can require a specific phone format, a postcode pattern, or alphanumeric usernames. The <code>title</code> attribute provides a custom error message explaining what pattern is expected.</p>
<div class="code-block">
<pre><code>&lt;!-- Password: at least 8 characters --&gt;
&lt;input type="password" name="password" minlength="8" required&gt;

&lt;!-- Username: 3-20 characters, letters and numbers only --&gt;
&lt;input type="text" name="username"
       minlength="3" maxlength="20"
       pattern="[A-Za-z0-9]+"
       title="Letters and numbers only, 3-20 characters"
       required&gt;

&lt;!-- Age between 18 and 99 --&gt;
&lt;input type="number" name="age" min="18" max="99" required&gt;

&lt;!-- Must be a future date --&gt;
&lt;input type="date" name="event_date" min="2025-01-01" required&gt;</code></pre>
</div>

<h3>CSS Validation States</h3>
<p>CSS provides pseudo-classes that let you style inputs based on their validation state. <code>:valid</code> matches inputs that pass validation, <code>:invalid</code> matches those that fail. This lets you show green borders for correctly filled fields and red borders for errors. Be careful: <code>:invalid</code> fires even before the user has typed anything (empty required fields are technically invalid). Use <code>:focus:invalid</code> to only show error styles after the user has interacted with a field.</p>
<div class="code-block">
<pre><code>/* In your CSS file */

/* Show green border when input is correctly filled */
input:valid {
  border-color: #28a745;
}

/* Show red border only after user has focused and left the field */
input:focus:invalid {
  border-color: #dc3545;
}

/* Using the :user-invalid pseudo-class (modern browsers) */
input:user-invalid {
  border-color: #dc3545;
  background-color: #fff5f5;
}</code></pre>
</div>

<h3>Disabling Native Validation</h3>
<p>Sometimes you want to handle validation entirely with JavaScript (for custom styled error messages). Add <code>novalidate</code> to the <code>&lt;form&gt;</code> element to disable all browser validation. This lets you control exactly when and how errors are shown while keeping HTML semantics for accessibility. Even with novalidate, keep your validation attributes — JavaScript can still read them to know the rules.</p>
<div class="code-block">
<pre><code>&lt;!-- novalidate disables browser popups but keeps attribute data --&gt;
&lt;form action="/register" method="post" novalidate&gt;
  &lt;input type="email" name="email" required id="emailInput"&gt;
  &lt;span id="emailError" class="error"&gt;&lt;/span&gt;
&lt;/form&gt;

&lt;script&gt;
  // Check validity with JavaScript using the Constraint Validation API
  const input = document.getElementById('emailInput');
  input.addEventListener('blur', () => {
    if (!input.validity.valid) {
      document.getElementById('emailError').textContent = 'Please enter a valid email.';
    }
  });
&lt;/script&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always add HTML validation as a baseline, even if you also validate with JavaScript. Remember: HTML and JS validation both run in the browser and can be bypassed. Never rely on client-side validation alone — always validate on the server too.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Relying only on HTML/JS validation:</strong> Client-side validation can always be bypassed. A user can disable JavaScript or send a raw HTTP request. Server-side validation is mandatory for security.</li>
    <li><strong>Using pattern without a title:</strong> When a pattern match fails, the browser shows a generic error. Add <code>title="explanation"</code> so users understand what format is required.</li>
    <li><strong>Styling :invalid on page load:</strong> Empty required fields are immediately <code>:invalid</code>, so naively styling them red means users see errors before they've typed anything. Use <code>:focus:invalid</code> or JavaScript to control timing.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>HTML5 provides free browser validation via <code>required</code>, <code>minlength</code>, <code>maxlength</code>, <code>min</code>, <code>max</code>, and <code>pattern</code> attributes.</li>
    <li>CSS pseudo-classes <code>:valid</code> and <code>:invalid</code> let you visually indicate field status; use <code>:focus:invalid</code> to avoid showing errors before users interact.</li>
    <li>HTML validation is a first line of defence only — always validate on the server for security.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Form Accessibility & UX', <<<'HTML'
<h2>Form Accessibility &amp; UX</h2>

<p>A form that works technically but frustrates users or excludes people with disabilities is not a good form. Accessible forms reach everyone — sighted users, keyboard-only users, screen reader users, and people on mobile devices. The good news is that accessible forms are also better UX for everyone: clearer labels, logical tab order, and helpful error messages benefit all users equally.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A well-designed physical checkout counter. It has clear signs, reachable controls, a card reader at the right height, and staff ready to help. It works for someone in a wheelchair, someone carrying bags, and someone in a hurry. Good form accessibility is the same concept — design for everyone and the result is better for everyone.</p>
</div>

<h3>Fieldset and Legend</h3>
<p><code>&lt;fieldset&gt;</code> groups related form controls together, and <code>&lt;legend&gt;</code> provides a caption for that group. This is especially useful for radio button groups and checkbox groups. Screen readers announce the legend text when entering the fieldset, giving users context for what the grouped controls are about. Without this grouping, a screen reader might just say "Male, radio button" without any context that you're choosing a gender.</p>
<div class="code-block">
<pre><code>&lt;fieldset&gt;
  &lt;legend&gt;Preferred Contact Method&lt;/legend&gt;

  &lt;label&gt;
    &lt;input type="radio" name="contact" value="email"&gt; Email
  &lt;/label&gt;
  &lt;label&gt;
    &lt;input type="radio" name="contact" value="phone"&gt; Phone
  &lt;/label&gt;
  &lt;label&gt;
    &lt;input type="radio" name="contact" value="whatsapp"&gt; WhatsApp
  &lt;/label&gt;
&lt;/fieldset&gt;</code></pre>
</div>

<h3>Descriptive Labels and Placeholders</h3>
<p>Labels are not optional. Every input needs one. A common mistake is using placeholder text as the only label — placeholders disappear when the user starts typing, leaving no reminder of what the field was for. Labels should describe what information is expected; placeholders can supplement with an example format. Make labels concise, visible, and permanently displayed. Mark required fields visually (an asterisk is conventional) and include a note explaining the convention.</p>
<div class="code-block">
<pre><code>&lt;p&gt;&lt;small&gt;* Required fields&lt;/small&gt;&lt;/p&gt;

&lt;label for="phone"&gt;Phone Number *&lt;/label&gt;
&lt;input type="tel" id="phone" name="phone"
       placeholder="+254 712 345 678"
       aria-describedby="phone-hint"
       required&gt;
&lt;span id="phone-hint" class="hint"&gt;Include country code&lt;/span&gt;

&lt;!-- aria-describedby links the hint text to the input for screen readers --&gt;</code></pre>
</div>

<h3>Error Messages and ARIA</h3>
<p>When a field has an error, the error message must be programmatically associated with the input — not just visually nearby. Use <code>aria-describedby</code> to point to the error message element, and set <code>aria-invalid="true"</code> on the invalid input. Screen readers then announce "Email field, invalid: Please enter a valid email address" when users navigate to it. Without this, blind users see the same form but never hear the error messages.</p>
<div class="code-block">
<pre><code>&lt;label for="email"&gt;Email Address *&lt;/label&gt;
&lt;input type="email" id="email" name="email"
       aria-invalid="true"
       aria-describedby="email-error"
       required&gt;
&lt;span id="email-error" role="alert" class="error-msg"&gt;
  Please enter a valid email address.
&lt;/span&gt;

&lt;!-- role="alert" makes screen readers announce the message immediately --&gt;</code></pre>
</div>

<h3>Tab Order and Keyboard Navigation</h3>
<p>Keyboard users navigate forms using the <kbd>Tab</kbd> key to move between fields and <kbd>Enter</kbd> to submit. The natural tab order follows the order elements appear in the HTML — so write your HTML in the visual reading order. Avoid using CSS to visually reorder elements in a way that conflicts with the tab sequence. The <code>tabindex</code> attribute can customise order when needed, but manipulating it is error-prone; keeping HTML order logical is the better approach.</p>
<div class="code-block">
<pre><code>&lt;!-- Good: HTML order matches visual/logical order --&gt;
&lt;form&gt;
  &lt;label for="first"&gt;First Name&lt;/label&gt;
  &lt;input type="text" id="first" name="first_name"&gt;    &lt;!-- Tab 1 --&gt;

  &lt;label for="last"&gt;Last Name&lt;/label&gt;
  &lt;input type="text" id="last" name="last_name"&gt;      &lt;!-- Tab 2 --&gt;

  &lt;label for="email"&gt;Email&lt;/label&gt;
  &lt;input type="email" id="email" name="email"&gt;         &lt;!-- Tab 3 --&gt;

  &lt;button type="submit"&gt;Submit&lt;/button&gt;               &lt;!-- Tab 4 --&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Accessibility is not optional — in many countries it's a legal requirement. Apply these techniques to every form you build, not just those for explicitly "accessibility-focused" projects. Start with correct HTML structure (fieldset, legend, labels), add aria attributes when native HTML isn't sufficient.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Placeholder as the only label:</strong> Placeholders have poor contrast, disappear on input, and are not always read by screen readers. Always use a visible <code>&lt;label&gt;</code>.</li>
    <li><strong>Error messages not associated with inputs:</strong> An error message styled in red near a field is invisible to screen readers unless it's linked via <code>aria-describedby</code>.</li>
    <li><strong>Disabling browser autofill:</strong> Adding <code>autocomplete="off"</code> everywhere is bad UX. Browser autofill helps users fill forms faster. Only disable it where there's a specific reason (e.g., OTP fields).</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use <code>&lt;fieldset&gt;</code> and <code>&lt;legend&gt;</code> to group related controls and provide context for screen readers.</li>
    <li>Always use visible <code>&lt;label&gt;</code> elements — never rely on placeholder text alone as the label.</li>
    <li>Associate error messages with inputs using <code>aria-describedby</code> and signal invalid state with <code>aria-invalid="true"</code>.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Advanced Form Features', <<<'HTML'
<h2>Advanced Form Features</h2>

<p>Beyond basic inputs and labels, HTML forms have powerful features that improve usability and reduce the amount of JavaScript you need to write. Datalists, output elements, form associations, and the various button types allow you to build sophisticated, interactive forms entirely in HTML — saving development time and improving performance.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Upgrading from a basic toolkit to a professional one. The basic tools do the job, but the advanced tools have features that make the same task faster, more precise, and more polished. Advanced form features let you build better forms with less code.</p>
</div>

<h3>The datalist Element</h3>
<p>The <code>&lt;datalist&gt;</code> element provides autocomplete suggestions for a text input. Unlike a <code>&lt;select&gt;</code>, users can still type freely — the datalist just offers suggestions as they type. This is perfect for situations where you want to guide users toward common values but don't want to restrict them. Connect a datalist to an input by matching the input's <code>list</code> attribute to the datalist's <code>id</code>.</p>
<div class="code-block">
<pre><code>&lt;label for="language"&gt;Favourite Programming Language&lt;/label&gt;
&lt;input type="text" id="language" name="language"
       list="languages" placeholder="Start typing..."&gt;

&lt;datalist id="languages"&gt;
  &lt;option value="HTML"&gt;
  &lt;option value="CSS"&gt;
  &lt;option value="JavaScript"&gt;
  &lt;option value="Python"&gt;
  &lt;option value="PHP"&gt;
&lt;/datalist&gt;</code></pre>
</div>

<h3>The output Element</h3>
<p>The <code>&lt;output&gt;</code> element displays the result of a calculation or user action. It's semantically correct for showing dynamic computed values. Combined with a range slider and a tiny JavaScript event listener, it creates a live preview of the selected value — no complex code required. The <code>for</code> attribute links the output to the inputs it depends on (similar to labels).</p>
<div class="code-block">
<pre><code>&lt;label for="price-range"&gt;Max Price: &lt;output id="price-display"&gt;$500&lt;/output&gt;&lt;/label&gt;
&lt;input type="range" id="price-range" name="max_price"
       min="0" max="1000" step="50" value="500"
       oninput="document.getElementById('price-display').textContent = '$' + this.value"&gt;</code></pre>
</div>

<h3>Button Types</h3>
<p>The <code>&lt;button&gt;</code> element has three types that behave very differently. <code>type="submit"</code> (the default) submits the form. <code>type="reset"</code> clears all fields back to their default values. <code>type="button"</code> does nothing by default — it's a hook for JavaScript. Always specify the type explicitly. Forgetting the type on a button inside a form means it defaults to submit, which can cause unexpected form submissions when users click buttons you intended as just UI elements.</p>
<div class="code-block">
<pre><code>&lt;form action="/order" method="post"&gt;

  &lt;!-- Submit: sends the form --&gt;
  &lt;button type="submit"&gt;Place Order&lt;/button&gt;

  &lt;!-- Reset: clears all inputs (use sparingly — frustrating if accidental) --&gt;
  &lt;button type="reset"&gt;Clear Form&lt;/button&gt;

  &lt;!-- Button: no default action, used with JavaScript --&gt;
  &lt;button type="button" onclick="previewOrder()"&gt;Preview Order&lt;/button&gt;

&lt;/form&gt;</code></pre>
</div>

<h3>Form Attributes on Inputs</h3>
<p>Normally, an input must be inside a form to be associated with it. But the <code>form</code> attribute lets you associate an input with a form by its <code>id</code>, even if the input is outside the form element in the HTML. The <code>autocomplete</code> attribute hints at what browsers should suggest; values like <code>name</code>, <code>email</code>, <code>street-address</code>, and <code>cc-number</code> trigger browser autofill. The <code>autofocus</code> attribute automatically focuses an input when the page loads.</p>
<div class="code-block">
<pre><code>&lt;form id="signup-form" action="/register" method="post"&gt;
  &lt;button type="submit"&gt;Sign Up&lt;/button&gt;
&lt;/form&gt;

&lt;!-- This input is outside the form but linked to it via form="signup-form" --&gt;
&lt;input type="email" name="email" form="signup-form"
       autocomplete="email"
       autofocus
       placeholder="Enter your email"&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use <code>&lt;datalist&gt;</code> whenever you have common values to suggest for a free-text field. Use <code>&lt;output&gt;</code> for computed results. Always explicitly set button types. These small choices make forms more polished and professional without adding complexity.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Untyped buttons inside forms:</strong> A <code>&lt;button&gt;</code> without a type defaults to <code>submit</code>. A "Cancel" or "Preview" button without <code>type="button"</code> will accidentally submit your form.</li>
    <li><strong>Overusing reset buttons:</strong> Reset buttons restore all fields to their defaults. Users who accidentally click them lose everything they typed. They're rarely helpful and often frustrating — omit them unless there's a clear use case.</li>
    <li><strong>Using datalist as a replacement for select:</strong> Datalist suggestions are hints, not restrictions. If you need users to pick only from a fixed list, use a <code>&lt;select&gt;</code> instead.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li><code>&lt;datalist&gt;</code> provides autocomplete suggestions for text inputs while still allowing free typing.</li>
    <li>Always specify <code>type="submit"</code>, <code>type="reset"</code>, or <code>type="button"</code> on buttons — the default type is submit.</li>
    <li>The <code>autocomplete</code> attribute with named values (email, name, tel) triggers browser autofill, improving form UX for returning users.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// HTML COURSE — Module 3
// =====================================================================

updateLesson($pdo, 'What is Semantic HTML?', <<<'HTML'
<h2>What is Semantic HTML?</h2>

<p>Semantic HTML means using elements that describe their content's meaning and purpose — not just how it looks. Instead of using <code>&lt;div&gt;</code> for everything, you use <code>&lt;article&gt;</code> for articles, <code>&lt;nav&gt;</code> for navigation, and <code>&lt;header&gt;</code> for headers. The difference matters enormously for accessibility, SEO, and code maintainability.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Labelling boxes when you move house. You could put everything in unmarked boxes and eventually find what you need. Or you could label them "Kitchen," "Books," "Clothing." The boxes hold the same things, but the labels make everything faster to find and understand — for you and for anyone else who needs to navigate your home.</p>
</div>

<h3>Semantic vs Non-Semantic Elements</h3>
<p>Non-semantic elements like <code>&lt;div&gt;</code> and <code>&lt;span&gt;</code> say nothing about their content. They're containers without meaning. Semantic elements carry inherent meaning: <code>&lt;article&gt;</code> says "this is a self-contained piece of content," <code>&lt;aside&gt;</code> says "this is tangentially related," <code>&lt;time&gt;</code> says "this is a date or time." This meaning is consumed by browsers, screen readers, and search engines — not just humans reading your code.</p>
<div class="code-block">
<pre><code>&lt;!-- Non-semantic: lots of divs, no meaning --&gt;
&lt;div class="header"&gt;
  &lt;div class="nav"&gt;...&lt;/div&gt;
&lt;/div&gt;
&lt;div class="main"&gt;
  &lt;div class="article"&gt;...&lt;/div&gt;
&lt;/div&gt;

&lt;!-- Semantic: elements describe their purpose --&gt;
&lt;header&gt;
  &lt;nav&gt;...&lt;/nav&gt;
&lt;/header&gt;
&lt;main&gt;
  &lt;article&gt;...&lt;/article&gt;
&lt;/main&gt;</code></pre>
</div>

<h3>Why Semantics Matter</h3>
<p>Semantic HTML benefits multiple audiences simultaneously. <strong>Screen readers</strong> use semantic elements to build a navigation structure — users can jump directly to the main content, skip navigation, or list all articles on a page. <strong>Search engines</strong> use semantics to understand page structure and weight content importance. <strong>Browsers</strong> apply default focus management and keyboard navigation to semantic landmarks. <strong>Developers</strong> reading your code understand intent immediately without deciphering class names.</p>
<div class="code-block">
<pre><code>&lt;!-- Search engines know this is the main content --&gt;
&lt;main&gt;

  &lt;!-- This is a standalone piece of content worth indexing --&gt;
  &lt;article&gt;
    &lt;header&gt;
      &lt;h2&gt;Why Semantic HTML Matters&lt;/h2&gt;
      &lt;time datetime="2025-01-15"&gt;January 15, 2025&lt;/time&gt;
    &lt;/header&gt;
    &lt;p&gt;Article content goes here...&lt;/p&gt;
  &lt;/article&gt;

  &lt;!-- Screen readers know this is supplementary content --&gt;
  &lt;aside&gt;
    &lt;h3&gt;Related Articles&lt;/h3&gt;
  &lt;/aside&gt;

&lt;/main&gt;</code></pre>
</div>

<h3>The Document Landmark Structure</h3>
<p>Well-structured pages use a set of landmark elements that form a navigable skeleton. <code>&lt;header&gt;</code> contains introductory content (site logo, main nav). <code>&lt;nav&gt;</code> contains navigation links. <code>&lt;main&gt;</code> contains the primary page content (use only once per page). <code>&lt;footer&gt;</code> contains closing information (copyright, secondary links). Screen reader users rely on these landmarks to skip to sections without reading everything — like keyboard shortcuts for a page.</p>
<div class="code-block">
<pre><code>&lt;body&gt;
  &lt;header&gt;                    &lt;!-- site-wide header --&gt;
    &lt;a href="/"&gt;My Site&lt;/a&gt;
    &lt;nav&gt;...navigation...&lt;/nav&gt;
  &lt;/header&gt;

  &lt;main&gt;                      &lt;!-- primary content (once per page) --&gt;
    &lt;h1&gt;Page Title&lt;/h1&gt;
    &lt;article&gt;...content...&lt;/article&gt;
  &lt;/main&gt;

  &lt;aside&gt;                     &lt;!-- related/supplementary content --&gt;
    &lt;h2&gt;Related Links&lt;/h2&gt;
  &lt;/aside&gt;

  &lt;footer&gt;                    &lt;!-- site-wide footer --&gt;
    &lt;p&gt;&amp;copy; 2025 My Site&lt;/p&gt;
  &lt;/footer&gt;
&lt;/body&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use semantic elements by default. Reach for <code>&lt;div&gt;</code> only when no semantic element fits — typically for purely layout/styling purposes. A good rule: if an element has meaningful content or plays a structural role, there's probably a semantic element for it.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Div-itis:</strong> Using <code>&lt;div&gt;</code> for everything because it works visually. This produces code that's impossible to navigate for screen reader users and harder for search engines to index correctly.</li>
    <li><strong>Multiple main elements:</strong> There should be only one <code>&lt;main&gt;</code> element per page. It marks the unique content of this page — multiple mains confuse landmarks.</li>
    <li><strong>Using section as a generic div:</strong> <code>&lt;section&gt;</code> should have a heading that describes what the section is about. If you wouldn't give it a heading, use <code>&lt;div&gt;</code> instead.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Semantic HTML uses meaningful elements (header, nav, main, article, footer) instead of generic divs for everything.</li>
    <li>Semantic elements benefit screen readers, search engines, and other developers reading your code.</li>
    <li>Use landmark elements (header, nav, main, aside, footer) to give pages a navigable skeleton structure.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Page Structure Elements', <<<'HTML'
<h2>Page Structure Elements</h2>

<p>HTML5 introduced a family of structural elements that replaced the endless divs of older HTML. These elements — section, article, aside, header, footer, main, and nav — describe the roles that different parts of a page play. Knowing when to use each one is the difference between good semantic HTML and just replacing <code>&lt;div class="article"&gt;</code> with <code>&lt;div class="article"&gt;</code> under a different name.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>The rooms of a house. A kitchen, bedroom, bathroom, and living room all serve different purposes. You wouldn't cook in the bathroom just because it's a room. Similarly, page structure elements have specific purposes: articles hold self-contained content, asides hold related side content, and so on.</p>
</div>

<h3>article vs section</h3>
<p><code>&lt;article&gt;</code> is for content that makes sense on its own, independent of the rest of the page — a blog post, a news story, a product card, a forum post, a comment. If you could syndicate it (copy it to an RSS feed or another site and it would still make sense), it's an article. <code>&lt;section&gt;</code> groups thematically related content that belongs to a larger whole. A section doesn't stand alone — it's part of the page. A common pairing: a page has sections, each section may contain articles.</p>
<div class="code-block">
<pre><code>&lt;!-- Article: self-contained blog post --&gt;
&lt;article&gt;
  &lt;h2&gt;Getting Started with CSS Grid&lt;/h2&gt;
  &lt;p&gt;Published by Jane on &lt;time datetime="2025-03-01"&gt;March 1, 2025&lt;/time&gt;&lt;/p&gt;
  &lt;p&gt;CSS Grid is a two-dimensional layout system...&lt;/p&gt;
&lt;/article&gt;

&lt;!-- Section: grouping related articles on the homepage --&gt;
&lt;section&gt;
  &lt;h2&gt;Latest Blog Posts&lt;/h2&gt;
  &lt;article&gt;...post 1...&lt;/article&gt;
  &lt;article&gt;...post 2...&lt;/article&gt;
&lt;/section&gt;</code></pre>
</div>

<h3>header and footer</h3>
<p><code>&lt;header&gt;</code> and <code>&lt;footer&gt;</code> can be used more than once on a page — they have meaning relative to their parent element. The <code>&lt;header&gt;</code> inside the <code>&lt;body&gt;</code> is the page-wide header. But an <code>&lt;article&gt;</code> can also have its own <code>&lt;header&gt;</code> containing the article title and byline, and its own <code>&lt;footer&gt;</code> for tags and comments links. This nesting gives you fine-grained semantic control.</p>
<div class="code-block">
<pre><code>&lt;!-- Page-level header --&gt;
&lt;header&gt;
  &lt;a href="/"&gt;&lt;img src="logo.svg" alt="MySite"&gt;&lt;/a&gt;
  &lt;nav&gt;...main navigation...&lt;/nav&gt;
&lt;/header&gt;

&lt;main&gt;
  &lt;article&gt;
    &lt;!-- Article-level header --&gt;
    &lt;header&gt;
      &lt;h1&gt;Article Title&lt;/h1&gt;
      &lt;p&gt;By &lt;a href="/author"&gt;Alex&lt;/a&gt;&lt;/p&gt;
    &lt;/header&gt;
    &lt;p&gt;Article content...&lt;/p&gt;
    &lt;!-- Article-level footer --&gt;
    &lt;footer&gt;
      &lt;p&gt;Tags: HTML, Web Development&lt;/p&gt;
    &lt;/footer&gt;
  &lt;/article&gt;
&lt;/main&gt;

&lt;!-- Page-level footer --&gt;
&lt;footer&gt;
  &lt;p&gt;&amp;copy; 2025 MySite&lt;/p&gt;
&lt;/footer&gt;</code></pre>
</div>

<h3>aside and nav</h3>
<p><code>&lt;aside&gt;</code> marks content that is tangentially related to the content around it — a pull quote, a related links sidebar, a biographical note about the article author, or an advertisement. It's not unrelated to the page; it supplements it. <code>&lt;nav&gt;</code> marks a block of navigation links. Not every group of links needs a nav — only major navigation blocks like the main site navigation, a table of contents, or breadcrumbs. Footer links can just be a list inside the footer.</p>
<div class="code-block">
<pre><code>&lt;main&gt;
  &lt;article&gt;
    &lt;h2&gt;The History of the Internet&lt;/h2&gt;
    &lt;p&gt;Main article content...&lt;/p&gt;

    &lt;!-- Aside within article: a related note --&gt;
    &lt;aside&gt;
      &lt;h3&gt;Did You Know?&lt;/h3&gt;
      &lt;p&gt;ARPANET, the predecessor to the internet, was created in 1969.&lt;/p&gt;
    &lt;/aside&gt;

    &lt;p&gt;More article content...&lt;/p&gt;
  &lt;/article&gt;
&lt;/main&gt;

&lt;!-- Aside at page level: a sidebar --&gt;
&lt;aside&gt;
  &lt;h2&gt;Related Articles&lt;/h2&gt;
  &lt;ul&gt;
    &lt;li&gt;&lt;a href="/article-2"&gt;How DNS Works&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
&lt;/aside&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Before writing a div, ask "does this have a semantic element?" Use this decision tree: Is it navigation? Use <code>&lt;nav&gt;</code>. Is it self-contained syndicated content? Use <code>&lt;article&gt;</code>. Is it a thematic grouping within a larger page? Use <code>&lt;section&gt;</code>. Is it supplementary/sidebar content? Use <code>&lt;aside&gt;</code>. Is it just a layout container? Use <code>&lt;div&gt;</code>.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>section without a heading:</strong> Every <code>&lt;section&gt;</code> should have a heading (h2-h6). A section without a heading is probably just a <code>&lt;div&gt;</code>.</li>
    <li><strong>Nesting article inside article incorrectly:</strong> Articles can nest — an article can contain comments as nested articles — but only if the nested content is genuinely a self-contained piece related to the parent article.</li>
    <li><strong>Wrapping the whole page in nav:</strong> Use <code>&lt;nav&gt;</code> only for major navigation blocks. A list of social media links in the footer doesn't need a nav wrapper.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li><code>&lt;article&gt;</code> is for self-contained content; <code>&lt;section&gt;</code> groups thematically related content that belongs to the broader page.</li>
    <li><code>&lt;header&gt;</code> and <code>&lt;footer&gt;</code> can be used multiple times per page, each relative to their parent element's context.</li>
    <li><code>&lt;aside&gt;</code> is supplementary/tangentially related content; <code>&lt;nav&gt;</code> wraps major navigation blocks only.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Lists and Tables', <<<'HTML'
<h2>Lists and Tables</h2>

<p>HTML provides three types of lists and a table element for presenting structured data. Using the right one for your content improves readability, accessibility, and semantics. Lists organise items; tables organise data with relationships across rows and columns. Knowing when to use each prevents two common mistakes: wrapping everything in tables for layout (wrong) or using lists for tabular data (also wrong).</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A shopping list vs a spreadsheet. Your grocery list is unordered — the sequence doesn't matter. A recipe's steps are ordered — they must be followed in sequence. A bus timetable is tabular — it has rows (stops) and columns (times) and the intersection is meaningful. Match the HTML element to the type of information.</p>
</div>

<h3>Unordered and Ordered Lists</h3>
<p><code>&lt;ul&gt;</code> (unordered list) is for items where order doesn't matter — navigation menus, feature lists, ingredients. Each item is an <code>&lt;li&gt;</code>. <code>&lt;ol&gt;</code> (ordered list) is for items where order matters — steps in a process, rankings, numbered instructions. Both can be nested: an <code>&lt;li&gt;</code> can contain another <code>&lt;ul&gt;</code> or <code>&lt;ol&gt;</code>, creating sub-lists for hierarchical content.</p>
<div class="code-block">
<pre><code>&lt;!-- Unordered: order doesn't matter --&gt;
&lt;ul&gt;
  &lt;li&gt;HTML&lt;/li&gt;
  &lt;li&gt;CSS&lt;/li&gt;
  &lt;li&gt;JavaScript&lt;/li&gt;
&lt;/ul&gt;

&lt;!-- Ordered: sequence matters --&gt;
&lt;ol&gt;
  &lt;li&gt;Open VS Code&lt;/li&gt;
  &lt;li&gt;Create a new file&lt;/li&gt;
  &lt;li&gt;Type your HTML&lt;/li&gt;
  &lt;li&gt;Save and open in browser&lt;/li&gt;
&lt;/ol&gt;

&lt;!-- Nested list --&gt;
&lt;ul&gt;
  &lt;li&gt;Frontend
    &lt;ul&gt;
      &lt;li&gt;HTML&lt;/li&gt;
      &lt;li&gt;CSS&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/li&gt;
  &lt;li&gt;Backend&lt;/li&gt;
&lt;/ul&gt;</code></pre>
</div>

<h3>Description Lists</h3>
<p><code>&lt;dl&gt;</code> (description list) is for term-definition pairs — glossaries, metadata, FAQ pages. Each term uses <code>&lt;dt&gt;</code> and each definition uses <code>&lt;dd&gt;</code>. One term can have multiple definitions, and multiple terms can share a definition. Description lists are underused but perfect for their purpose — more semantically correct than a regular list for name-value content.</p>
<div class="code-block">
<pre><code>&lt;dl&gt;
  &lt;dt&gt;HTML&lt;/dt&gt;
  &lt;dd&gt;HyperText Markup Language — the structure of web pages.&lt;/dd&gt;

  &lt;dt&gt;CSS&lt;/dt&gt;
  &lt;dd&gt;Cascading Style Sheets — the visual presentation of web pages.&lt;/dd&gt;

  &lt;dt&gt;JavaScript&lt;/dt&gt;
  &lt;dd&gt;A scripting language that adds interactivity to web pages.&lt;/dd&gt;
&lt;/dl&gt;</code></pre>
</div>

<h3>Tables for Tabular Data</h3>
<p>Tables are for data with meaningful row-column relationships — schedules, comparison charts, financial data. A proper table uses <code>&lt;thead&gt;</code> for the header row, <code>&lt;tbody&gt;</code> for data rows, and <code>&lt;tfoot&gt;</code> for totals/summary rows. Column headers use <code>&lt;th&gt;</code> with a <code>scope</code> attribute; data cells use <code>&lt;td&gt;</code>. The <code>&lt;caption&gt;</code> element provides a title for the table, which is important for screen readers.</p>
<div class="code-block">
<pre><code>&lt;table&gt;
  &lt;caption&gt;Course Completion Rates by Month&lt;/caption&gt;

  &lt;thead&gt;
    &lt;tr&gt;
      &lt;th scope="col"&gt;Month&lt;/th&gt;
      &lt;th scope="col"&gt;HTML&lt;/th&gt;
      &lt;th scope="col"&gt;CSS&lt;/th&gt;
      &lt;th scope="col"&gt;JavaScript&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;

  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;th scope="row"&gt;January&lt;/th&gt;
      &lt;td&gt;87%&lt;/td&gt;
      &lt;td&gt;72%&lt;/td&gt;
      &lt;td&gt;65%&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;th scope="row"&gt;February&lt;/th&gt;
      &lt;td&gt;91%&lt;/td&gt;
      &lt;td&gt;78%&lt;/td&gt;
      &lt;td&gt;70%&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;
&lt;/table&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use <code>&lt;ul&gt;</code> for most unordered content (menus, features). Use <code>&lt;ol&gt;</code> for steps and rankings. Use <code>&lt;dl&gt;</code> for term-definition content. Use <code>&lt;table&gt;</code> for genuinely tabular data. Never use tables for page layout — that's CSS's job.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Using tables for page layout:</strong> Tables were used for layout in the 1990s. Today, CSS Flexbox and Grid do this better. Table layout is slow, inaccessible, and not responsive.</li>
    <li><strong>Missing thead/tbody/tfoot:</strong> These elements help browsers optimise rendering (fixed headers when scrolling) and help screen readers understand table structure. Always include at minimum thead and tbody.</li>
    <li><strong>Missing scope on th elements:</strong> The <code>scope="col"</code> or <code>scope="row"</code> attribute on <code>&lt;th&gt;</code> tells screen readers whether this header applies to a column or a row, making complex tables navigable.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use <code>&lt;ul&gt;</code> for unordered items, <code>&lt;ol&gt;</code> for ordered steps, and <code>&lt;dl&gt;</code> for term-definition pairs.</li>
    <li>Tables are for tabular data only — never for page layout. Always include caption, thead, tbody, and scope attributes.</li>
    <li>Lists and tables both support nesting for hierarchical and complex data structures.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Images and Media', <<<'HTML'
<h2>Images and Media</h2>

<p>Images, videos, and audio make web content engaging and informative. HTML provides dedicated elements for each media type, with attributes that control dimensions, loading behaviour, accessibility, and fallback content. Getting media right means balancing visual quality with performance — and never forgetting that some users can't see or hear your media.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Framing and hanging a picture. The image is the content, but how you hang it (size, placement, mounting) affects how the room looks and feels. And just as a painting in a gallery has a title card for context, every web image needs alt text for users who can't see it.</p>
</div>

<h3>The img Element</h3>
<p>The <code>&lt;img&gt;</code> element embeds an image. It's a void element (no closing tag) with two required attributes: <code>src</code> (the image path or URL) and <code>alt</code> (alternative text). The alt attribute is critical for accessibility — screen readers announce it to blind users instead of the image. It also displays if the image fails to load. For decorative images that add no information, use an empty alt (<code>alt=""</code>) so screen readers skip it. Use <code>width</code> and <code>height</code> attributes to reserve space and prevent layout shift during load.</p>
<div class="code-block">
<pre><code>&lt;!-- Informative image — describe what it shows --&gt;
&lt;img src="images/team-photo.jpg"
     alt="HackathonAfrica team at the 2025 Nairobi summit"
     width="800" height="450"&gt;

&lt;!-- Decorative image — empty alt tells screen readers to skip it --&gt;
&lt;img src="images/decorative-wave.svg" alt="" width="100%" height="50"&gt;

&lt;!-- Responsive image using srcset for different screen densities --&gt;
&lt;img src="logo.png"
     srcset="logo.png 1x, logo@2x.png 2x"
     alt="MySite Logo"
     width="200" height="60"&gt;</code></pre>
</div>

<h3>The picture Element for Responsive Images</h3>
<p>The <code>&lt;picture&gt;</code> element lets browsers choose the most appropriate image source based on screen size or format support. Use it for serving modern formats (WebP, AVIF) with a fallback to JPEG/PNG, or for art direction — showing a cropped close-up on mobile and the full wide image on desktop. It wraps <code>&lt;source&gt;</code> elements and a fallback <code>&lt;img&gt;</code> element.</p>
<div class="code-block">
<pre><code>&lt;!-- Serve WebP to supporting browsers, JPEG as fallback --&gt;
&lt;picture&gt;
  &lt;source srcset="hero.avif" type="image/avif"&gt;
  &lt;source srcset="hero.webp" type="image/webp"&gt;
  &lt;img src="hero.jpg" alt="Web development workspace" width="1200" height="600"&gt;
&lt;/picture&gt;

&lt;!-- Art direction: different crops for different screens --&gt;
&lt;picture&gt;
  &lt;source media="(max-width: 600px)" srcset="hero-mobile.jpg"&gt;
  &lt;source media="(min-width: 601px)" srcset="hero-desktop.jpg"&gt;
  &lt;img src="hero-desktop.jpg" alt="Conference hall" width="1200" height="500"&gt;
&lt;/picture&gt;</code></pre>
</div>

<h3>Video and Audio</h3>
<p>The <code>&lt;video&gt;</code> and <code>&lt;audio&gt;</code> elements embed media with native browser controls. Provide multiple <code>&lt;source&gt;</code> elements in different formats (MP4 and WebM for video) for maximum browser compatibility. The fallback text between the tags displays if the browser doesn't support the element. Add <code>controls</code> to show play/pause buttons, <code>muted</code> for auto-playing videos (required by most browsers), and <code>loop</code> for repeating media.</p>
<div class="code-block">
<pre><code>&lt;video controls width="640" height="360" poster="thumbnail.jpg"&gt;
  &lt;source src="intro.mp4" type="video/mp4"&gt;
  &lt;source src="intro.webm" type="video/webm"&gt;
  &lt;p&gt;Your browser doesn't support video. &lt;a href="intro.mp4"&gt;Download it&lt;/a&gt;.&lt;/p&gt;
&lt;/video&gt;

&lt;audio controls&gt;
  &lt;source src="podcast.mp3" type="audio/mpeg"&gt;
  &lt;source src="podcast.ogg" type="audio/ogg"&gt;
  &lt;p&gt;Your browser doesn't support audio.&lt;/p&gt;
&lt;/audio&gt;</code></pre>
</div>

<h3>Lazy Loading</h3>
<p>Adding <code>loading="lazy"</code> to images and iframes tells the browser to defer loading them until they're near the viewport. This dramatically reduces initial page load time for image-heavy pages. Use <code>loading="eager"</code> (or omit the attribute) for images above the fold — images the user sees immediately on page load should load right away.</p>
<div class="code-block">
<pre><code>&lt;!-- Above the fold: load immediately --&gt;
&lt;img src="hero.jpg" alt="Hero image" loading="eager"&gt;

&lt;!-- Below the fold: defer until needed --&gt;
&lt;img src="gallery/photo-1.jpg" alt="Gallery photo 1" loading="lazy" width="400" height="300"&gt;
&lt;img src="gallery/photo-2.jpg" alt="Gallery photo 2" loading="lazy" width="400" height="300"&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always include alt text on images. Use picture for responsive/format-switching images. Add lazy loading to all below-fold images. Provide multiple source formats for video and audio. Remember: media accessibility isn't optional — videos should have captions, audio should have transcripts.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Missing alt text:</strong> An image without alt is announced as the filename by screen readers — often meaningless. Always write descriptive alt text (or empty alt for decorative images).</li>
    <li><strong>Missing width and height on images:</strong> Without dimensions, the browser doesn't know how much space to reserve, causing content to jump around as images load (layout shift). Always specify width and height.</li>
    <li><strong>Autoplay video with sound:</strong> Browsers block autoplay video with audio. If you need autoplay, add the <code>muted</code> attribute. Never autoplay audio — it's jarring and inaccessible.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Always include <code>alt</code>, <code>width</code>, and <code>height</code> on every <code>&lt;img&gt;</code> tag for accessibility and layout stability.</li>
    <li>Use <code>&lt;picture&gt;</code> with multiple <code>&lt;source&gt;</code> elements for responsive images and modern format support.</li>
    <li>Add <code>loading="lazy"</code> to below-fold images and iframes to improve initial page load performance.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'ARIA and Accessibility', <<<'HTML'
<h2>ARIA and Accessibility</h2>

<p>ARIA (Accessible Rich Internet Applications) is a set of HTML attributes that extend the semantics of your markup when native HTML elements aren't enough. It communicates to screen readers and other assistive technologies: what an element is, what state it's in, and what it does. ARIA doesn't change visual appearance — it only affects accessibility. Used correctly, it bridges gaps in HTML's native semantics; used incorrectly, it breaks experiences for users who depend on assistive technology.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Subtitles on a film. The film plays without them, and most viewers don't need them. But for viewers who are deaf or in a noisy environment, subtitles make the content fully accessible. ARIA is the subtitle system for web interactivity — it makes dynamic, visual information available to users who can't see the screen.</p>
</div>

<h3>The First Rule of ARIA</h3>
<p>The first rule of ARIA is: don't use ARIA if native HTML can do the job. A <code>&lt;button&gt;</code> is already fully accessible — it's keyboard focusable, announced as "button" by screen readers, and activatable with Enter or Space. Adding ARIA to a <code>&lt;div&gt;</code> to make it behave like a button requires you to recreate all of that behaviour manually. Always prefer semantic HTML; reach for ARIA only when semantic HTML falls short.</p>
<div class="code-block">
<pre><code>&lt;!-- Don't do this — requires ARIA + JS to be accessible --&gt;
&lt;div onclick="submitForm()"&gt;Submit&lt;/div&gt;

&lt;!-- Do this — fully accessible out of the box --&gt;
&lt;button type="submit"&gt;Submit&lt;/button&gt;

&lt;!-- ARIA role needed: custom dropdown built with divs --&gt;
&lt;div role="listbox" aria-label="Select a country" tabindex="0"&gt;
  &lt;div role="option" aria-selected="true"&gt;Kenya&lt;/div&gt;
  &lt;div role="option" aria-selected="false"&gt;Nigeria&lt;/div&gt;
&lt;/div&gt;</code></pre>
</div>

<h3>ARIA Roles</h3>
<p>The <code>role</code> attribute tells assistive technologies what type of element something is. Many roles correspond to native HTML elements (<code>role="button"</code>, <code>role="navigation"</code>) — use native HTML instead. But roles like <code>alert</code>, <code>dialog</code>, <code>tabpanel</code>, <code>tooltip</code>, and <code>progressbar</code> have no direct HTML equivalent and are where ARIA adds real value. An element with <code>role="alert"</code> is announced immediately by screen readers when it appears in the DOM.</p>
<div class="code-block">
<pre><code>&lt;!-- Success message: announced immediately when shown --&gt;
&lt;div role="alert" class="success-msg"&gt;
  Your message was sent successfully!
&lt;/div&gt;

&lt;!-- Modal dialog --&gt;
&lt;div role="dialog" aria-modal="true" aria-labelledby="dialog-title"&gt;
  &lt;h2 id="dialog-title"&gt;Confirm Delete&lt;/h2&gt;
  &lt;p&gt;Are you sure you want to delete this item?&lt;/p&gt;
  &lt;button&gt;Delete&lt;/button&gt;
  &lt;button&gt;Cancel&lt;/button&gt;
&lt;/div&gt;

&lt;!-- Progress bar --&gt;
&lt;div role="progressbar" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"&gt;
  65% Complete
&lt;/div&gt;</code></pre>
</div>

<h3>ARIA States and Properties</h3>
<p>ARIA attributes starting with <code>aria-</code> communicate element state and properties. <code>aria-expanded</code> tells whether a collapsible section is open or closed. <code>aria-hidden="true"</code> removes an element from the accessibility tree (useful for decorative icons). <code>aria-label</code> and <code>aria-labelledby</code> provide accessible names. <code>aria-live</code> marks regions where dynamic content updates should be announced to screen readers.</p>
<div class="code-block">
<pre><code>&lt;!-- Accordion: announce open/closed state --&gt;
&lt;button aria-expanded="false" aria-controls="panel1"&gt;
  What is HTML?
&lt;/button&gt;
&lt;div id="panel1" hidden&gt;
  Web pages are built with HTML.
&lt;/div&gt;

&lt;!-- Icon button: label the button (icon has no text) --&gt;
&lt;button aria-label="Close dialog"&gt;
  &lt;svg aria-hidden="true"&gt;&lt;!-- X icon --&gt;&lt;/svg&gt;
&lt;/button&gt;

&lt;!-- Live region: announce when count changes --&gt;
&lt;span aria-live="polite" id="cart-count"&gt;3 items in cart&lt;/span&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use native HTML semantics first. When you must build custom interactive components (tabs, modals, accordions, carousels) with divs and spans, apply appropriate ARIA roles, states, and properties. Test with a real screen reader (NVDA on Windows, VoiceOver on Mac) — reading the spec isn't a substitute for actual testing.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Redundant ARIA roles:</strong> <code>&lt;nav role="navigation"&gt;</code> is redundant — nav already has that role. Adding it again doesn't hurt, but it's unnecessary noise.</li>
    <li><strong>aria-hidden on focusable elements:</strong> If an element has <code>aria-hidden="true"</code> but is still keyboard-focusable, keyboard users land on invisible elements. Remove focusability (tabindex="-1") alongside aria-hidden.</li>
    <li><strong>Forgetting to update aria states with JS:</strong> If you use aria-expanded on a toggle button, you must update it with JavaScript when the state changes. ARIA doesn't auto-update — it reflects the current state you set.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use native semantic HTML before ARIA — most accessibility needs are covered by correct HTML element choice.</li>
    <li>ARIA roles name custom components; ARIA properties and states (aria-expanded, aria-label, aria-hidden) describe their current condition.</li>
    <li>Dynamic content changes use <code>aria-live</code> regions and <code>role="alert"</code> to be announced to screen reader users in real time.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// HTML COURSE — Module 4
// =====================================================================

updateLesson($pdo, 'HTML5 Media Elements', <<<'HTML'
<h2>HTML5 Media Elements</h2>

<p>HTML5 brought native multimedia to the browser — no plugins, no Flash, no third-party players required. The <code>&lt;video&gt;</code> and <code>&lt;audio&gt;</code> elements let you embed media directly, with a JavaScript API for programmatic control. Understanding these elements means you can build custom media players, implement autoplay policies correctly, and add captions for accessibility.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A built-in DVD player in a TV, versus buying a separate player and connecting it. Before HTML5, embedding video required Flash — a third-party "player" you had to install. HTML5 video is like the built-in player: it's already there, it works, and you control it from the same remote (your HTML and JavaScript).</p>
</div>

<h3>The video Element in Depth</h3>
<p>The <code>&lt;video&gt;</code> element supports several important attributes beyond the basics. <code>poster</code> displays a still image before the video plays. <code>preload</code> controls how much the browser downloads before play — <code>none</code> (download nothing), <code>metadata</code> (download just duration/dimensions), or <code>auto</code> (download entire file). <code>playsinline</code> prevents iOS Safari from going fullscreen automatically, important for background videos. <code>loop</code> replays the video endlessly.</p>
<div class="code-block">
<pre><code>&lt;video
  src="course-intro.mp4"
  poster="course-thumbnail.jpg"
  controls
  preload="metadata"
  width="800"
  height="450"&gt;
  &lt;!-- Subtitles/captions track --&gt;
  &lt;track kind="captions" src="captions-en.vtt" srclang="en" label="English" default&gt;
  &lt;track kind="subtitles" src="subtitles-fr.vtt" srclang="fr" label="Français"&gt;
  Your browser doesn't support HTML5 video.
&lt;/video&gt;</code></pre>
</div>

<h3>The track Element for Captions</h3>
<p>The <code>&lt;track&gt;</code> element adds time-coded text tracks to video and audio. Captions include both dialogue and sound descriptions (important for deaf users). Subtitles translate dialogue into another language. The track file format is WebVTT (.vtt) — a simple text file with timestamps and text. Adding captions is legally required in many contexts and benefits all users (loud environments, non-native speakers, learning while commuting).</p>
<div class="code-block">
<pre><code>&lt;!-- captions-en.vtt file content: --&gt;
WEBVTT

00:00:01.000 --> 00:00:04.000
Welcome to HackathonAfrica's web development course.

00:00:04.500 --> 00:00:08.000
In this lesson, we'll cover HTML5 media elements.

00:00:08.500 --> 00:00:12.000
[Keyboard typing sounds]
Let's open VS Code and get started.</code></pre>
</div>

<h3>The JavaScript Media API</h3>
<p>Every video and audio element exposes a JavaScript API for programmatic control. This lets you build custom controls, sync media with animations, implement autoplay-on-scroll behaviour, or track watch time. The API includes methods like <code>play()</code>, <code>pause()</code>, <code>load()</code> and properties like <code>currentTime</code>, <code>duration</code>, <code>volume</code>, <code>muted</code>, and <code>paused</code>. Events like <code>play</code>, <code>pause</code>, <code>ended</code>, and <code>timeupdate</code> let you react to playback state changes.</p>
<div class="code-block">
<pre><code>const video = document.getElementById('myVideo');

// Custom play/pause button
document.getElementById('playBtn').addEventListener('click', () => {
  if (video.paused) {
    video.play();
  } else {
    video.pause();
  }
});

// Show current time as video plays
video.addEventListener('timeupdate', () => {
  const percent = (video.currentTime / video.duration) * 100;
  document.getElementById('progress').style.width = percent + '%';
});

// Skip ahead 10 seconds
document.getElementById('skipBtn').addEventListener('click', () => {
  video.currentTime += 10;
});</code></pre>
</div>

<h3>Autoplay Policies</h3>
<p>Browsers heavily restrict autoplay to prevent annoying user experiences. The rule: video with audio cannot autoplay. Video with <code>muted</code> attribute usually can. Some browsers also require user interaction with the page before any autoplay. For background hero videos, always use <code>autoplay muted loop playsinline</code>. For content videos, use the <code>play()</code> API in response to user interaction, and handle the promise it returns (it might be rejected).</p>
<div class="code-block">
<pre><code>&lt;!-- Background hero video: autoplay allowed because muted --&gt;
&lt;video autoplay muted loop playsinline&gt;
  &lt;source src="hero-background.mp4" type="video/mp4"&gt;
&lt;/video&gt;

&lt;script&gt;
// Programmatic play returns a Promise — handle rejection
const video = document.querySelector('video');
const playPromise = video.play();

if (playPromise !== undefined) {
  playPromise.catch(error => {
    // Autoplay was prevented — show a play button to user
    document.getElementById('playOverlay').style.display = 'block';
  });
}
&lt;/script&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use native HTML5 video for self-hosted content. Use embedded iframes (YouTube, Vimeo) for platform-hosted video when you want the platform's CDN, analytics, and player. Always add captions for accessibility. Use poster images so the video area looks good before it plays.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>No captions on video content:</strong> Video without captions is inaccessible to deaf users and those in sound-sensitive environments. Add a <code>&lt;track kind="captions"&gt;</code> element to every content video.</li>
    <li><strong>Autoplay with sound:</strong> Browsers block this and users hate it. If you need autoplay, use the <code>muted</code> attribute. If you need sound, require a user gesture first.</li>
    <li><strong>Only providing one video format:</strong> MP4 (H.264) has the widest support, but WebM (VP9/AV1) is smaller and supported by modern browsers. Provide both via <code>&lt;source&gt;</code> elements for best compatibility and performance.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>HTML5 video supports poster images, preloading control, and caption tracks via <code>&lt;track kind="captions"&gt;</code>.</li>
    <li>Browsers block autoplay with sound — use <code>muted</code> for background videos and handle play() promise rejection for content videos.</li>
    <li>The JavaScript Media API exposes play(), pause(), currentTime, duration, and events like timeupdate for building custom players.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Canvas and SVG', <<<'HTML'
<h2>Canvas and SVG</h2>

<p>When you need graphics beyond what HTML and CSS can draw, HTML5 provides two approaches: Canvas and SVG. They are fundamentally different tools for different jobs. Canvas is an immediate-mode bitmap — you draw pixels directly with JavaScript and the browser forgets the drawing commands. SVG is a retained vector format — shapes are elements in the DOM that can be styled, animated, and manipulated independently. Knowing which to use, and when, is a key skill.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Canvas is like painting on a canvas with real paint — once you paint over something, the original is gone. SVG is like building with LEGO — every brick is a separate piece you can pick up, move, recolour, or remove at any time. Both create visual results, but the process is completely different.</p>
</div>

<h3>The canvas Element</h3>
<p>The <code>&lt;canvas&gt;</code> element creates a rectangular drawing surface. You set its dimensions with <code>width</code> and <code>height</code> attributes (not CSS — CSS just scales the bitmap, distorting it). All drawing happens through the JavaScript Canvas 2D API via a "context" object. Canvas excels at pixel manipulation, games, real-time data visualisations, and image processing. Its limitation: individual drawn elements aren't accessible or interactive without extra work.</p>
<div class="code-block">
<pre><code>&lt;canvas id="myCanvas" width="400" height="200"&gt;
  &lt;!-- Fallback for browsers without canvas support --&gt;
  &lt;p&gt;A bar chart showing sales data.&lt;/p&gt;
&lt;/canvas&gt;

&lt;script&gt;
const canvas = document.getElementById('myCanvas');
const ctx = canvas.getContext('2d');

// Draw a filled rectangle
ctx.fillStyle = '#ff6600';
ctx.fillRect(50, 50, 200, 100);   // x, y, width, height

// Draw text
ctx.fillStyle = '#ffffff';
ctx.font = 'bold 24px Arial';
ctx.fillText('Hello Canvas!', 75, 110);

// Draw a circle
ctx.beginPath();
ctx.arc(320, 100, 50, 0, Math.PI * 2);  // x, y, radius, start, end angle
ctx.fillStyle = '#0066cc';
ctx.fill();
&lt;/script&gt;</code></pre>
</div>

<h3>SVG — Scalable Vector Graphics</h3>
<p>SVG (Scalable Vector Graphics) is an XML-based vector format that can be embedded directly in HTML. SVG shapes are DOM elements — they can have classes, IDs, CSS styles, and event listeners. They scale perfectly at any size (great for logos and icons). SVG is ideal for logos, icons, infographics, charts, and any graphic that needs to stay sharp at all sizes. The basic shapes are <code>&lt;rect&gt;</code>, <code>&lt;circle&gt;</code>, <code>&lt;ellipse&gt;</code>, <code>&lt;line&gt;</code>, <code>&lt;polyline&gt;</code>, <code>&lt;polygon&gt;</code>, and <code>&lt;path&gt;</code>.</p>
<div class="code-block">
<pre><code>&lt;!-- Inline SVG embedded directly in HTML --&gt;
&lt;svg width="200" height="200" xmlns="http://www.w3.org/2000/svg"&gt;

  &lt;!-- Background rectangle --&gt;
  &lt;rect x="0" y="0" width="200" height="200" fill="#f0f0f0" rx="10"&gt;&lt;/rect&gt;

  &lt;!-- A circle (CSS can style SVG elements) --&gt;
  &lt;circle cx="100" cy="80" r="50" fill="#ff6600" class="logo-circle"&gt;&lt;/circle&gt;

  &lt;!-- Text inside SVG --&gt;
  &lt;text x="100" y="160" text-anchor="middle" font-size="18" fill="#333"&gt;
    Hello SVG!
  &lt;/text&gt;

  &lt;!-- Interactive element --&gt;
  &lt;rect x="60" y="170" width="80" height="20" fill="#0066cc" rx="5"
        onclick="alert('SVG clicked!')" style="cursor:pointer"&gt;&lt;/rect&gt;
&lt;/svg&gt;</code></pre>
</div>

<h3>When to Use Canvas vs SVG</h3>
<p>Choose Canvas for: games, real-time video/audio processing, pixel-level manipulation, charts with thousands of data points. Choose SVG for: logos, icons, simple charts, illustrations, interactive diagrams where individual elements need events or animation. SVG scales infinitely without pixellation — always use it for logos and icons that appear at variable sizes. Canvas degrades at scale — use it where you need raw pixel control and performance for large numbers of drawn elements.</p>
<div class="code-block">
<pre><code>&lt;!-- SVG icon — stays sharp at any size --&gt;
&lt;svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true"&gt;
  &lt;path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"
        stroke="#333" stroke-width="2" fill="none"&gt;&lt;/path&gt;
&lt;/svg&gt;

&lt;!-- Animating SVG with CSS --&gt;
&lt;style&gt;
  .spin { animation: rotate 2s linear infinite; }
  @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
&lt;/style&gt;
&lt;svg width="50" height="50" viewBox="0 0 50 50"&gt;
  &lt;circle class="spin" cx="25" cy="25" r="20" stroke="#ff6600" stroke-width="4" fill="none"
          style="transform-origin: center"&gt;&lt;/circle&gt;
&lt;/svg&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use SVG for icons, logos, and data visualisations where scalability and individual element interactivity matter. Use Canvas for games, real-time graphics, and image editing tools. For simple icons, consider using an SVG sprite system or an icon library like Bootstrap Icons rather than writing SVG from scratch.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Setting canvas size with CSS only:</strong> CSS width/height scales the canvas's pixels, distorting your drawing. Always set the actual drawing dimensions with HTML width/height attributes.</li>
    <li><strong>Using Canvas for everything:</strong> Canvas has no accessible element tree. Logos, icons, and charts that should be accessible to screen readers belong in SVG or HTML, not Canvas.</li>
    <li><strong>Inline SVG for large graphics:</strong> Very complex SVGs (detailed illustrations) make your HTML huge. Use <code>&lt;img src="illustration.svg"&gt;</code> or CSS background for large static SVGs.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Canvas is a bitmap drawing surface for JavaScript — great for games and real-time graphics but inaccessible by default.</li>
    <li>SVG is vector-based XML — elements live in the DOM, scale perfectly, and can be styled with CSS and made interactive with JavaScript.</li>
    <li>Use SVG for logos, icons, and accessible charts; use Canvas for pixel-heavy applications like games and image processing.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Meta Tags and SEO', <<<'HTML'
<h2>Meta Tags and SEO</h2>

<p>Search Engine Optimisation (SEO) is how you help search engines understand and rank your content. HTML plays a major role: the elements you use, the titles you write, and the meta tags you include directly influence how your page appears in search results and when shared on social media. Good HTML structure and meta tags don't guarantee top rankings, but missing them almost guarantees poor performance.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A book's cover, spine, and table of contents. You judge a book before opening it by those elements. Search engines judge your page by its meta tags and HTML structure before deeply indexing it. A well-labelled book is easier to find in a library; a well-marked page is easier for search engines to categorise and rank.</p>
</div>

<h3>Title and Description</h3>
<p>The <code>&lt;title&gt;</code> tag is the single most important SEO element. It appears as the clickable headline in search results and in the browser tab. Keep it 50-60 characters, include your primary keyword near the front, and make it unique for every page. The meta description (150-160 characters) appears below the title in search results. It doesn't directly affect ranking, but a compelling description improves click-through rate — how many people choose your result over others.</p>
<div class="code-block">
<pre><code>&lt;head&gt;
  &lt;title&gt;Learn HTML for Beginners — HackathonAfrica LMS&lt;/title&gt;

  &lt;meta name="description"
        content="Master HTML from the ground up. Hands-on lessons covering structure, forms, semantic elements, and modern HTML5 features. Start free today."&gt;

  &lt;!-- Keywords meta is largely ignored by modern search engines --&gt;
  &lt;!-- but it's still good documentation of your intent --&gt;
  &lt;meta name="keywords" content="HTML, web development, beginner, online course"&gt;

  &lt;!-- Prevent search engines from indexing this page --&gt;
  &lt;!-- (use on admin pages, duplicate content, etc.) --&gt;
  &lt;meta name="robots" content="noindex, nofollow"&gt;
&lt;/head&gt;</code></pre>
</div>

<h3>Open Graph Meta Tags</h3>
<p>Open Graph (OG) tags control how your page appears when shared on Facebook, LinkedIn, WhatsApp, and most other social platforms. Without them, platforms guess: they might pick the wrong image, show a truncated title, or display ugly previews. OG tags let you specify exactly the title, description, image, and URL that appear in social shares. Twitter has its own similar set called Twitter Cards. Always include both for maximum reach.</p>
<div class="code-block">
<pre><code>&lt;!-- Open Graph for Facebook, LinkedIn, WhatsApp --&gt;
&lt;meta property="og:title" content="Learn HTML for Beginners"&gt;
&lt;meta property="og:description" content="Master HTML from the ground up with hands-on lessons."&gt;
&lt;meta property="og:image" content="https://example.com/images/html-course-preview.jpg"&gt;
&lt;meta property="og:url" content="https://example.com/courses/html"&gt;
&lt;meta property="og:type" content="website"&gt;

&lt;!-- Twitter Cards --&gt;
&lt;meta name="twitter:card" content="summary_large_image"&gt;
&lt;meta name="twitter:title" content="Learn HTML for Beginners"&gt;
&lt;meta name="twitter:description" content="Master HTML from the ground up."&gt;
&lt;meta name="twitter:image" content="https://example.com/images/html-course-preview.jpg"&gt;</code></pre>
</div>

<h3>Canonical URLs and Structured Data</h3>
<p>The canonical link element prevents duplicate content penalties when the same content is accessible at multiple URLs (with/without trailing slash, HTTP/HTTPS, www/non-www). Point it to the "official" URL for that content. Structured data (Schema.org JSON-LD) goes a step further — it provides machine-readable metadata that enables rich search result features like star ratings, event dates, breadcrumbs, and FAQ accordions directly in search results.</p>
<div class="code-block">
<pre><code>&lt;!-- Tell search engines this is the canonical (authoritative) URL --&gt;
&lt;link rel="canonical" href="https://www.example.com/courses/html"&gt;

&lt;!-- Structured data: make Google show your course as a rich result --&gt;
&lt;script type="application/ld+json"&gt;
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Learn HTML for Beginners",
  "description": "A comprehensive beginner's HTML course.",
  "provider": {
    "@type": "Organization",
    "name": "HackathonAfrica"
  }
}
&lt;/script&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Every page should have a unique, descriptive title and meta description. Add Open Graph tags to any page users might share. Add a canonical link to avoid duplicate content issues. Invest in structured data for pages with structured content (courses, products, events, recipes, FAQs).</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Duplicate page titles:</strong> Every page must have a unique title. Sites where every page has the same title (or the site name only) are penalised by search engines and confuse users with multiple tabs open.</li>
    <li><strong>Missing OG image:</strong> Without an og:image tag, social platforms pick any image on the page — often a logo, icon, or nothing. Always specify a compelling 1200×630px image for social sharing.</li>
    <li><strong>Canonical pointing to wrong URL:</strong> Canonical tags pointing to 404 pages, redirects, or different content can confuse search engines and signal that your important pages are copies of other content.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Every page needs a unique, keyword-relevant title (50-60 chars) and a descriptive meta description (150-160 chars).</li>
    <li>Open Graph and Twitter Card meta tags control how your pages appear when shared on social media.</li>
    <li>Canonical links prevent duplicate content issues; structured data (JSON-LD) enables rich results in search.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Web Components Basics', <<<'HTML'
<h2>Web Components Basics</h2>

<p>Web Components is a set of browser APIs that lets you create custom, reusable HTML elements — like building your own HTML tags. Instead of copying a complex card UI across ten pages, you define it once as a <code>&lt;product-card&gt;</code> element and use it anywhere like a native HTML element. Web Components are native to the browser — no framework required, though they work alongside React, Vue, and others.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Inventing a new type of LEGO brick. Once you design and produce the custom brick, you can use it in any LEGO set you build — and anyone else can use it too. You define it once, then it works just like standard bricks everywhere. Custom HTML elements work the same way.</p>
</div>

<h3>Custom Elements</h3>
<p>The Custom Elements API lets you define new HTML tags using JavaScript. Custom element names must include a hyphen (to distinguish them from standard HTML elements). You define the element's behaviour in a class that extends <code>HTMLElement</code>, register it with <code>customElements.define()</code>, then use it in HTML like any native element. The class has lifecycle callbacks: <code>connectedCallback</code> (element added to DOM), <code>disconnectedCallback</code> (removed), and <code>attributeChangedCallback</code> (attribute changed).</p>
<div class="code-block">
<pre><code>&lt;!-- Using a custom element in HTML --&gt;
&lt;user-card name="Alex Kamau" role="Student" avatar="alex.jpg"&gt;&lt;/user-card&gt;

&lt;script&gt;
class UserCard extends HTMLElement {
  // Declare which attributes to observe for changes
  static get observedAttributes() { return ['name', 'role', 'avatar']; }

  connectedCallback() {
    // Called when element is added to the DOM
    const name = this.getAttribute('name') || 'Unknown';
    const role = this.getAttribute('role') || 'User';
    const avatar = this.getAttribute('avatar') || 'default.jpg';

    this.innerHTML = `
      &lt;div class="user-card"&gt;
        &lt;img src="${avatar}" alt="${name}"&gt;
        &lt;h3&gt;${name}&lt;/h3&gt;
        &lt;p&gt;${role}&lt;/p&gt;
      &lt;/div&gt;
    `;
  }
}

// Register the custom element
customElements.define('user-card', UserCard);
&lt;/script&gt;</code></pre>
</div>

<h3>Shadow DOM</h3>
<p>Shadow DOM creates an encapsulated DOM subtree attached to an element, isolated from the main document's CSS and JavaScript. Styles inside a Shadow DOM don't leak out to the page, and page styles don't leak in. This is how browser built-in elements like <code>&lt;video&gt;</code> and <code>&lt;input type="date"&gt;</code> have internal structure that you can't accidentally break with your CSS. Shadow DOM is what makes Web Components truly reusable and self-contained.</p>
<div class="code-block">
<pre><code>class MyButton extends HTMLElement {
  connectedCallback() {
    // Attach a shadow root — 'open' means JS can access it externally
    const shadow = this.attachShadow({ mode: 'open' });

    // Styles here are SCOPED to this component — won't affect page
    shadow.innerHTML = `
      &lt;style&gt;
        button {
          background: #ff6600;
          color: white;
          padding: 10px 20px;
          border: none;
          border-radius: 4px;
          cursor: pointer;
        }
        button:hover { background: #cc5200; }
      &lt;/style&gt;
      &lt;button&gt;&lt;slot&gt;&lt;/slot&gt;&lt;/button&gt;
    `;
    /* &lt;slot&gt; is where the element's children are projected into the shadow */
  }
}

customElements.define('my-button', MyButton);</code></pre>
</div>

<h3>HTML Templates</h3>
<p>The <code>&lt;template&gt;</code> element holds HTML that is parsed but not rendered. It's a blueprint you can clone and insert into the DOM multiple times. Templates are often used with custom elements — you define the structure in a template, then stamp it out for each instance. The content inside a template is inert: scripts don't run, images don't load, and it has no visual presence until cloned.</p>
<div class="code-block">
<pre><code>&lt;template id="card-template"&gt;
  &lt;div class="card"&gt;
    &lt;h3 class="card-title"&gt;&lt;/h3&gt;
    &lt;p class="card-body"&gt;&lt;/p&gt;
    &lt;a class="card-link" href="#"&gt;Learn more&lt;/a&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
function createCard(title, body, url) {
  const template = document.getElementById('card-template');
  const clone = template.content.cloneNode(true);  // deep clone

  clone.querySelector('.card-title').textContent = title;
  clone.querySelector('.card-body').textContent = body;
  clone.querySelector('.card-link').href = url;

  document.getElementById('cards-container').appendChild(clone);
}
&lt;/script&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Web Components are ideal for design systems and reusable UI components that need to work across frameworks. For individual projects, consider whether a simple function or include is enough — the full Web Components API has complexity. Templates are immediately useful even without custom elements for stamping out repeated HTML efficiently.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Custom element names without a hyphen:</strong> Custom element names must contain a hyphen (e.g., <code>my-card</code>, not <code>mycard</code>). This distinguishes them from current and future standard HTML elements.</li>
    <li><strong>Forgetting Shadow DOM encapsulation:</strong> Without Shadow DOM, your component's internal styles affect the page and the page's styles affect your component. Use shadow DOM when you need true encapsulation.</li>
    <li><strong>Using innerHTML with untrusted data:</strong> Setting <code>this.innerHTML</code> with unescaped user-provided data creates XSS vulnerabilities. Always sanitise or use textContent for text, and createElement for elements.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Custom Elements let you define new HTML tags with their own behaviour by extending HTMLElement and registering with customElements.define().</li>
    <li>Shadow DOM provides style and DOM encapsulation, preventing component internals from affecting (or being affected by) the page.</li>
    <li>The <code>&lt;template&gt;</code> element holds parsed-but-inert HTML that can be efficiently cloned and inserted into the DOM multiple times.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Performance Optimization', <<<'HTML'
<h2>Performance Optimization</h2>

<p>Web performance isn't a nice-to-have — slow pages lose users. Studies consistently show that even a one-second delay in load time increases bounce rates significantly, reduces conversions, and hurts search rankings. HTML itself is a major performance lever: how you structure your document, which resources you load, and in what order directly affects how fast users see content and can interact with the page.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Opening a new restaurant. If the kitchen has to prepare every ingredient from scratch when a customer orders, service is slow. If common ingredients are prepped in advance, popular dishes are ready to serve quickly. Web performance is the same — preloading critical resources, caching assets, and reducing unnecessary work means pages serve users faster.</p>
</div>

<h3>Resource Hints</h3>
<p>HTML provides link elements that hint to the browser about resources it will need soon, so it can start fetching them early. <code>preload</code> tells the browser to fetch a critical resource immediately at high priority (fonts, hero images, critical scripts). <code>prefetch</code> loads resources you'll need on the next page at low priority during idle time. <code>preconnect</code> establishes a connection to an origin ahead of time (eliminating DNS + TCP + TLS latency for first request to that origin).</p>
<div class="code-block">
<pre><code>&lt;head&gt;
  &lt;!-- Preload: fetch this critical font before the CSS requests it --&gt;
  &lt;link rel="preload" href="/fonts/inter.woff2" as="font" type="font/woff2" crossorigin&gt;

  &lt;!-- Preload: fetch the hero image early --&gt;
  &lt;link rel="preload" href="/images/hero.jpg" as="image"&gt;

  &lt;!-- Preconnect: establish connection to CDN early --&gt;
  &lt;link rel="preconnect" href="https://fonts.googleapis.com"&gt;
  &lt;link rel="preconnect" href="https://fonts.gstatic.com" crossorigin&gt;

  &lt;!-- Prefetch: load next page's resources during idle time --&gt;
  &lt;link rel="prefetch" href="/courses/css/intro"&gt;
&lt;/head&gt;</code></pre>
</div>

<h3>Script Loading Strategies</h3>
<p>By default, a <code>&lt;script&gt;</code> tag blocks HTML parsing — the browser stops building the page to download and execute the script. Three loading strategies change this. <strong>defer</strong>: downloads the script in parallel with HTML parsing, executes after parsing is complete, in document order. <strong>async</strong>: downloads in parallel, executes immediately when ready (may be out of order). <strong>module</strong>: ES6 module scripts defer by default and support import/export. Use defer for most scripts, async only for completely independent scripts like analytics.</p>
<div class="code-block">
<pre><code>&lt;!-- Blocks HTML parsing — avoid for large scripts --&gt;
&lt;script src="app.js"&gt;&lt;/script&gt;

&lt;!-- defer: parallel download, executes after HTML parsed, in order --&gt;
&lt;!-- Best for most application scripts --&gt;
&lt;script src="app.js" defer&gt;&lt;/script&gt;

&lt;!-- async: parallel download, executes immediately — order not guaranteed --&gt;
&lt;!-- Best for independent scripts like analytics --&gt;
&lt;script src="analytics.js" async&gt;&lt;/script&gt;

&lt;!-- module: deferred by default, supports import/export --&gt;
&lt;script type="module" src="main.js"&gt;&lt;/script&gt;</code></pre>
</div>

<h3>Image Optimization</h3>
<p>Images are typically the largest assets on a page. Optimise them by: using modern formats (WebP is 25-35% smaller than JPEG, AVIF even smaller), specifying dimensions to prevent layout shift, using <code>loading="lazy"</code> for below-fold images, and using srcset to serve appropriately sized images to different devices. Serving a 2000px image on a 400px mobile screen wastes bandwidth and slows load time.</p>
<div class="code-block">
<pre><code>&lt;!-- Responsive images: serve the right size for the screen --&gt;
&lt;img
  src="hero-800.jpg"
  srcset="hero-400.jpg 400w,
          hero-800.jpg 800w,
          hero-1200.jpg 1200w"
  sizes="(max-width: 600px) 400px,
         (max-width: 900px) 800px,
         1200px"
  alt="Development workshop"
  width="1200"
  height="600"
  loading="eager"&gt;

&lt;!-- Below-fold images: lazy load --&gt;
&lt;img src="section-image.webp" alt="..." loading="lazy" width="600" height="400"&gt;</code></pre>
</div>

<h3>Critical Path and Render Blocking</h3>
<p>The critical rendering path is the sequence of steps the browser takes to render the first visible frame. Anything that blocks this path delays when users see content. CSS is render-blocking (browsers wait for all CSS to download before painting). Reduce render-blocking by inlining critical CSS (the styles needed for above-fold content) in a <code>&lt;style&gt;</code> tag in the head, and loading the rest of your CSS asynchronously. Use browser DevTools → Performance tab to identify your critical path bottlenecks.</p>
<div class="code-block">
<pre><code>&lt;head&gt;
  &lt;!-- Inline critical CSS: loads instantly, no extra request --&gt;
  &lt;style&gt;
    /* Only the styles needed for above-the-fold content */
    body { margin: 0; font-family: sans-serif; }
    header { background: #333; color: white; padding: 1rem; }
    h1 { font-size: 2rem; margin: 0; }
  &lt;/style&gt;

  &lt;!-- Non-critical CSS loaded asynchronously --&gt;
  &lt;link rel="preload" href="styles.css" as="style" onload="this.onload=null;this.rel='stylesheet'"&gt;
  &lt;noscript&gt;&lt;link rel="stylesheet" href="styles.css"&gt;&lt;/noscript&gt;
&lt;/head&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Apply performance techniques from day one, not as an afterthought. The highest-impact, lowest-effort gains are: adding defer/async to scripts, lazy-loading images, using WebP format, and setting width/height on images. Measure with Lighthouse in Chrome DevTools to identify what matters most for your specific page.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Loading scripts in the head without defer/async:</strong> Scripts in the head block HTML parsing. Always use defer or async, or move scripts to the end of body.</li>
    <li><strong>Lazy-loading above-fold images:</strong> The hero image and any images visible without scrolling should load immediately (loading="eager" or no attribute). Lazy-loading them delays what the user sees first.</li>
    <li><strong>Not specifying image dimensions:</strong> Images without width and height cause Cumulative Layout Shift (CLS) — content jumping around as images load. This hurts both UX and Core Web Vitals scores.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use resource hints (preload, prefetch, preconnect) to start fetching critical assets before the browser discovers them.</li>
    <li>Add defer to application scripts so they don't block HTML parsing; use async only for completely independent scripts.</li>
    <li>Serve appropriately sized images with srcset, use modern formats (WebP/AVIF), and lazy-load below-fold images to reduce bandwidth.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// CSS COURSE — Module 5
// =====================================================================

updateLesson($pdo, 'CSS Selectors', <<<'HTML'
<h2>CSS Selectors</h2>

<p>CSS selectors are patterns that target HTML elements for styling. Mastering selectors means you can apply styles precisely — to exactly the elements you want, without touching anything else. Understanding selector specificity also explains one of CSS's most common frustrations: why a style you wrote isn't being applied.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Addressing a letter. "To the occupant" targets everyone. "To apartment 4B" targets one specific person. "To any resident with a red mailbox" targets a specific attribute. CSS selectors let you address your styles with exactly this level of precision.</p>
</div>

<h3>Basic Selectors</h3>
<p>The simplest selectors target elements by type, class, or ID. <strong>Type selectors</strong> match all elements of that tag. <strong>Class selectors</strong> (prefixed with <code>.</code>) match any element with that class — multiple elements can share a class. <strong>ID selectors</strong> (prefixed with <code>#</code>) match a single unique element — IDs must be unique per page. The <strong>universal selector</strong> (<code>*</code>) matches every element. Use type selectors for base styles, class selectors for components, and avoid ID selectors in CSS (use them for JavaScript instead).</p>
<div class="code-block">
<pre><code>/* Type selector: all paragraphs */
p { color: #333; line-height: 1.6; }

/* Class selector: any element with class="btn" */
.btn { padding: 10px 20px; border-radius: 4px; cursor: pointer; }

/* Class variants: .btn-primary, .btn-secondary */
.btn-primary { background: #0066cc; color: white; }
.btn-secondary { background: #6c757d; color: white; }

/* ID selector: the unique #main-header element */
#main-header { position: sticky; top: 0; background: white; }

/* Universal: every element (common use: box-sizing reset) */
*, *::before, *::after { box-sizing: border-box; }</code></pre>
</div>

<h3>Combinators</h3>
<p>Combinators describe the relationship between selectors. The <strong>descendant combinator</strong> (space) matches elements nested anywhere inside another. The <strong>child combinator</strong> (<code>&gt;</code>) matches only direct children. The <strong>adjacent sibling combinator</strong> (<code>+</code>) matches the immediately following sibling. The <strong>general sibling combinator</strong> (<code>~</code>) matches all following siblings. Combinators let you target elements based on their context in the document tree without adding extra classes.</p>
<div class="code-block">
<pre><code>/* Descendant: any &lt;a&gt; inside .nav (at any depth) */
.nav a { color: white; text-decoration: none; }

/* Child: only direct &lt;li&gt; children of .menu */
.menu > li { display: inline-block; }

/* Adjacent sibling: &lt;p&gt; immediately after &lt;h2&gt; */
h2 + p { font-size: 1.1rem; color: #555; }

/* General sibling: all &lt;p&gt; elements after an &lt;h2&gt; */
h2 ~ p { margin-left: 1rem; }</code></pre>
</div>

<h3>Attribute Selectors</h3>
<p>Attribute selectors match elements based on their attributes and values. This is powerful for styling form inputs by type, links by destination, or any element with a specific data attribute. Six variants exist: exact match (<code>[attr="value"]</code>), word match (<code>[attr~="word"]</code>), prefix match (<code>[attr^="start"]</code>), suffix match (<code>[attr$="end"]</code>), substring match (<code>[attr*="substring"]</code>), and attribute presence (<code>[attr]</code>).</p>
<div class="code-block">
<pre><code>/* Style inputs by type — no extra classes needed */
input[type="text"] { border: 1px solid #ccc; }
input[type="email"] { border: 1px solid #66afe9; }
input[type="submit"] { background: #28a745; color: white; }

/* Style external links with an arrow icon */
a[href^="https://"]::after { content: " ↗"; font-size: 0.8em; }

/* Style PDF download links */
a[href$=".pdf"] { color: #cc0000; }

/* Style elements with a specific data attribute */
[data-tooltip] { position: relative; cursor: help; }</code></pre>
</div>

<h3>Specificity</h3>
<p>Specificity determines which CSS rule wins when multiple rules target the same element. It's calculated as a three-part score (IDs, classes/attributes/pseudo-classes, elements/pseudo-elements). Higher specificity always wins, regardless of order in the file. This is why a class style might not override an ID style even if it comes later. Use specificity intentionally — avoid overly specific selectors that are hard to override, and almost never use <code>!important</code>.</p>
<div class="code-block">
<pre><code>/* Specificity scores (ID, Class, Element): */
p            { color: black; }    /* 0,0,1 — lowest */
.text        { color: blue; }     /* 0,1,0 */
#intro       { color: green; }    /* 1,0,0 — highest */
p.text       { color: purple; }   /* 0,1,1 */

/* This element: &lt;p class="text" id="intro"&gt; */
/* Green wins — #intro has highest specificity */

/* !important overrides all — use sparingly */
p { color: red !important; }      /* wins over everything */</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use classes for styling in almost all cases — they're reusable and have manageable specificity. Use type selectors for base/reset styles. Use attribute selectors for form elements and smart targeting. Avoid ID selectors in CSS to keep specificity low and styles easy to override.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Over-qualifying selectors:</strong> Writing <code>div.container p.text</code> when <code>.text</code> would work increases specificity unnecessarily, making styles harder to override later.</li>
    <li><strong>Abusing !important:</strong> Slapping !important on styles to "fix" override issues creates technical debt. Fix the root cause — usually a specificity conflict — instead.</li>
    <li><strong>Using IDs for styling:</strong> ID selectors have high specificity (1,0,0) and can only be used once per page. Reserve IDs for JavaScript targeting; use classes for CSS.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Selector types: element (p), class (.btn), ID (#header), attribute ([type="text"]), universal (*).</li>
    <li>Combinators (space, >, +, ~) describe hierarchical and sibling relationships between elements.</li>
    <li>Specificity (ID > class > element) determines which rule wins; use low-specificity class selectors to keep styles easy to maintain.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'The Box Model', <<<'HTML'
<h2>The Box Model</h2>

<p>Every element in CSS is treated as a rectangular box. The box model describes how that box is constructed: content, padding, border, and margin — four concentric layers, each adding size and space in different ways. Misunderstanding the box model is the source of many layout frustrations for beginners. Understand it once and layout clicks into place.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Wrapping a gift. The gift itself is the content. The tissue paper inside the box is padding — it protects the gift and gives it breathing room. The box is the border — the visible edge. The space between boxes on a shelf is the margin — the gap between elements. Each layer has its own size and purpose.</p>
</div>

<h3>Content, Padding, Border, Margin</h3>
<p>Working outward from centre: <strong>Content</strong> is where text and child elements live — sized by <code>width</code> and <code>height</code>. <strong>Padding</strong> is transparent space between the content and the border — it adds space inside the element and inherits the element's background colour. <strong>Border</strong> surrounds padding and content — it has thickness, style, and colour. <strong>Margin</strong> is transparent space outside the border — it creates gaps between elements and doesn't inherit background colour.</p>
<div class="code-block">
<pre><code>.card {
  /* Content area */
  width: 300px;
  height: 200px;

  /* Padding: space inside the box */
  padding: 20px;          /* all sides */
  padding: 10px 20px;     /* top/bottom  left/right */
  padding: 10px 20px 15px 25px;  /* top right bottom left */

  /* Border */
  border: 2px solid #ccc;
  border-radius: 8px;     /* rounds corners */

  /* Margin: space outside the box */
  margin: 16px;           /* all sides */
  margin: 0 auto;         /* centres block elements horizontally */
}</code></pre>
</div>

<h3>Box Sizing: border-box</h3>
<p>By default, <code>width</code> applies only to the content area. Adding padding and border makes the element visually larger. This "content-box" model is counterintuitive — setting <code>width: 300px</code> and <code>padding: 20px</code> actually renders a 340px element. The <code>box-sizing: border-box</code> alternative includes padding and border inside the specified width. This is so much more intuitive that virtually all modern CSS resets apply it globally. Always set it.</p>
<div class="code-block">
<pre><code>/* Apply to ALL elements globally — always do this */
*, *::before, *::after {
  box-sizing: border-box;
}

/* Now width: 300px means the total visual width is 300px */
/* including padding and border */
.box {
  width: 300px;
  padding: 20px;
  border: 2px solid #333;
  /* With border-box: content area = 300 - 40 - 4 = 256px */
  /* Total visual width: still exactly 300px */
}</code></pre>
</div>

<h3>Margin Collapsing</h3>
<p>Vertical margins between block elements collapse — the larger of the two margins applies, not the sum of both. If a paragraph has <code>margin-bottom: 20px</code> and the next has <code>margin-top: 16px</code>, the gap between them is 20px, not 36px. This intentional behaviour prevents double spacing between elements. However, it only applies vertically, only to block elements, and doesn't happen inside flex or grid containers. Understanding margin collapse explains mysterious spacing that seems wrong.</p>
<div class="code-block">
<pre><code>/* Margin collapsing example */
.paragraph-1 {
  margin-bottom: 20px;  /* bottom margin */
}

.paragraph-2 {
  margin-top: 16px;     /* top margin */
}
/* Gap between them = 20px (larger value), NOT 36px */

/* Margin collapse does NOT happen in flex/grid containers */
.flex-container {
  display: flex;
  flex-direction: column;
  /* Children's margins do NOT collapse here */
  gap: 16px;  /* use gap instead of margins in flex/grid */
}</code></pre>
</div>

<h3>Display and Block vs Inline</h3>
<p>The <code>display</code> property determines how an element participates in layout. <strong>Block</strong> elements (div, p, h1-h6, section) take the full width, start on a new line, and respect all box model properties. <strong>Inline</strong> elements (span, a, strong, em) flow with text, don't start new lines, and ignore top/bottom padding/margin. <strong>Inline-block</strong> flows with text but respects all box model properties. <code>display: none</code> hides the element completely, removing it from layout flow.</p>
<div class="code-block">
<pre><code>/* Block: takes full width, starts new line */
.card { display: block; }

/* Inline: flows with text, ignores vertical margin */
.highlight { display: inline; }

/* Inline-block: flows with text BUT respects width/height/margins */
.badge {
  display: inline-block;
  padding: 4px 8px;
  background: #0066cc;
  color: white;
  border-radius: 12px;
}

/* Hidden: removed from layout completely */
.tooltip { display: none; }   /* vs visibility: hidden which preserves space */</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always apply <code>box-sizing: border-box</code> globally as the first rule in your CSS reset. Use padding for internal spacing, margin for spacing between elements. Use margin: 0 auto to centre block elements horizontally within their container.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Not setting box-sizing: border-box:</strong> Working with the default content-box means your size calculations never match what renders. Set border-box globally and forget about this class of problem.</li>
    <li><strong>Using padding instead of margin for spacing between elements:</strong> Padding adds space inside an element; margin adds space outside (between elements). Mixing them up creates inconsistent spacing that's hard to control.</li>
    <li><strong>Expecting margin to centre an inline element:</strong> <code>margin: 0 auto</code> only centres block elements. Inline elements can't be centred this way — use <code>text-align: center</code> or flexbox instead.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Every element is a box with four layers: content → padding → border → margin.</li>
    <li>Set <code>box-sizing: border-box</code> globally so that width and height include padding and border.</li>
    <li>Vertical margins between block elements collapse to the larger value, not the sum — this is expected behaviour.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Typography', <<<'HTML'
<h2>Typography</h2>

<p>Typography in CSS controls how text looks and reads. It's more than just font size — it's the combination of font family, size, weight, line height, letter spacing, and colour that determines whether text is comfortable to read or strains the eye. Good typography is largely invisible; bad typography is immediately noticeable. Learning these properties gives you control over one of the most impactful aspects of visual design.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Choosing a typeface for a book. The same text printed in a clean serif font versus a cramped condensed font in tiny size with tight line spacing reads completely differently — even though the words are identical. Typography shapes the reading experience before a single word is processed.</p>
</div>

<h3>Font Family and Web Fonts</h3>
<p>The <code>font-family</code> property specifies which typeface to use, with fallbacks in case the first isn't available. System fonts (serif, sans-serif, monospace) always work but look different across operating systems. Web fonts — loaded via Google Fonts, Adobe Fonts, or self-hosted — give you consistent appearance everywhere. Load Google Fonts by adding a link to the head, then use the font name in CSS. Always include a generic fallback (sans-serif, serif, monospace) as the last option.</p>
<div class="code-block">
<pre><code>/* In HTML head: */
/* &lt;link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"&gt; */

body {
  /* First choice: Inter, then system sans-serif fonts, then any sans-serif */
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

h1, h2, h3 {
  font-family: 'Georgia', 'Times New Roman', serif;
}

code, pre {
  font-family: 'Fira Code', 'Consolas', 'Monaco', monospace;
}</code></pre>
</div>

<h3>Size, Weight, and Style</h3>
<p>Font size should use <code>rem</code> units (relative to the root element's font size, typically 16px) for accessibility — they scale correctly when users change their browser's default font size. <code>em</code> is relative to the parent element, which can compound unexpectedly in nested elements. <code>px</code> ignores user preferences and is inflexible. Font weight ranges from 100 (thin) to 900 (black); most fonts only support 400 (regular) and 700 (bold). Font style sets italic or oblique.</p>
<div class="code-block">
<pre><code>:root {
  font-size: 16px;  /* 1rem = 16px throughout the document */
}

body    { font-size: 1rem;    }  /* 16px */
h1      { font-size: 2.5rem;  }  /* 40px */
h2      { font-size: 2rem;    }  /* 32px */
h3      { font-size: 1.5rem;  }  /* 24px */
small   { font-size: 0.875rem; } /* 14px */

.bold       { font-weight: 700; }
.semi-bold  { font-weight: 600; }
.light      { font-weight: 300; }
.italic     { font-style: italic; }</code></pre>
</div>

<h3>Line Height and Letter Spacing</h3>
<p>Line height (also called leading) controls the vertical space between lines of text. A value of 1 means lines touch; 1.5 is comfortable for body text; 1.2 is tighter for headings. Use unitless values like 1.5 rather than pixels — they scale proportionally with the font size. Letter spacing (tracking) adds or removes space between characters; tight letter spacing improves headlines, wide spacing can improve readability for uppercase text.</p>
<div class="code-block">
<pre><code>body {
  line-height: 1.6;     /* comfortable for reading long paragraphs */
}

h1 {
  line-height: 1.2;     /* tighter for large display text */
  letter-spacing: -0.02em;  /* slightly tighter for big headlines */
}

.uppercase-label {
  text-transform: uppercase;
  letter-spacing: 0.1em;   /* wide spacing improves readability of all-caps */
  font-size: 0.875rem;
  font-weight: 600;
}</code></pre>
</div>

<h3>Text Properties</h3>
<p>CSS offers several properties for text alignment, decoration, and transformation. <code>text-align</code> aligns text horizontally. <code>text-decoration</code> adds underlines, overlines, or strikethroughs (or removes the default underline from links). <code>text-transform</code> changes case without editing the HTML. <code>text-overflow</code> with <code>overflow: hidden</code> and <code>white-space: nowrap</code> truncates long text with an ellipsis — useful for table cells and card titles.</p>
<div class="code-block">
<pre><code>.hero-title {
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Remove default underline from links, add on hover */
a { text-decoration: none; }
a:hover { text-decoration: underline; }

/* Truncate long text with ... */
.card-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use rem for all font sizes so they scale with user preferences. Set line-height: 1.5–1.6 on body text for readability. Load web fonts with display=swap to prevent invisible text during loading. Limit yourself to two font families per project (one for headings, one for body) for a clean, professional look.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Font sizes in px only:</strong> Using only px for font sizes ignores user accessibility preferences. Use rem so text scales when users increase their browser's base font size.</li>
    <li><strong>Line height too tight:</strong> Default browser line-height is around 1.2, which is too tight for body text. Set it to 1.5–1.6 on the body for comfortable reading, especially on mobile.</li>
    <li><strong>Too many font families:</strong> More than two font families usually looks inconsistent and unprofessional. One for headings, one for body text is the professional standard.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use <code>rem</code> for font sizes so they scale correctly with user preferences; set body line-height to 1.5–1.6 for readability.</li>
    <li>Load web fonts via Google Fonts or self-hosted; always include a system font stack as a fallback.</li>
    <li>Properties like text-transform, letter-spacing, and text-overflow give fine-grained control over text appearance and truncation.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Colors and Backgrounds', <<<'HTML'
<h2>Colors and Backgrounds</h2>

<p>Colour is one of the most powerful tools in visual design. In CSS, colour can be specified in many formats, each with different use cases. Backgrounds go beyond solid colours — gradients, images, and multiple layered backgrounds create rich visual effects. Understanding colour in CSS means knowing not just the syntax, but how to choose and apply colours consistently and accessibly.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A painter's palette. You have multiple ways to mix colours — by name, by formula (hex, RGB, HSL). A good painter knows which pigments mix reliably and stores frequently used colours in a palette they can reach for consistently. CSS custom properties (variables) are that palette — define your brand colours once, use them everywhere.</p>
</div>

<h3>Colour Formats</h3>
<p>CSS supports several colour syntaxes. <strong>Named colours</strong> (red, blue, cornflowerblue) are human-readable but limited. <strong>Hex</strong> (#rrggbb) is most common — concise and widely understood. <strong>RGB/RGBA</strong> lets you specify red, green, blue (0-255) and optional alpha (0-1 for transparency). <strong>HSL/HSLA</strong> (hue, saturation, lightness) is the most designer-friendly format — you can create colour variations by adjusting just the lightness value, keeping hue and saturation constant.</p>
<div class="code-block">
<pre><code>/* Named */
color: tomato;
color: cornflowerblue;

/* Hex (shorthand #rgb for #rrggbb when pairs match) */
color: #ff6600;
color: #f60;       /* same as #ff6600 */

/* RGB and RGBA (a = alpha, 0 transparent, 1 opaque) */
color: rgb(255, 102, 0);
color: rgba(255, 102, 0, 0.8);  /* 80% opaque */

/* HSL: hue (0-360°), saturation (%), lightness (%) */
color: hsl(24, 100%, 50%);       /* bright orange */
color: hsl(24, 100%, 70%);       /* lighter orange — same hue */
color: hsla(24, 100%, 50%, 0.5); /* 50% transparent */</code></pre>
</div>

<h3>Background Properties</h3>
<p>The background shorthand bundles several properties: colour, image, position, size, repeat, attachment, and origin. Use individual properties for clarity when setting multiple values. <code>background-size: cover</code> scales the image to cover the entire element without distortion. <code>background-size: contain</code> fits the image within the element. <code>background-position</code> controls where the image anchors. <code>background-attachment: fixed</code> creates a parallax effect where the background stays fixed as content scrolls.</p>
<div class="code-block">
<pre><code>.hero {
  /* Shorthand: url position/size repeat colour */
  background: url('hero.jpg') center/cover no-repeat #1a1a2e;

  /* Equivalent individual properties */
  background-image: url('hero.jpg');
  background-position: center center;
  background-size: cover;
  background-repeat: no-repeat;
  background-color: #1a1a2e;  /* fallback if image fails */
}

.profile-card {
  background-color: white;
  border: 1px solid #e0e0e0;
}</code></pre>
</div>

<h3>Gradients</h3>
<p>CSS gradients create smooth colour transitions without needing image files. Linear gradients flow in a straight line; radial gradients radiate from a point. Multiple colour stops create multicolour gradients. Gradients are treated as background images in CSS and can be layered with other backgrounds. They're perfect for button hover effects, hero section overlays, and decorative UI elements.</p>
<div class="code-block">
<pre><code>/* Linear gradient */
.button {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Overlay gradient on top of image */
.hero {
  background:
    linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.3)),
    url('hero.jpg') center/cover;
}

/* Radial gradient */
.glow-effect {
  background: radial-gradient(circle at center, #ff9a56 0%, #ff4040 60%, #b30000 100%);
}

/* Hard stop gradient (no fade) */
.striped {
  background: linear-gradient(
    90deg,
    #ff6600 0%, #ff6600 50%,
    #003399 50%, #003399 100%
  );
}</code></pre>
</div>

<h3>CSS Custom Properties for Colour Systems</h3>
<p>CSS custom properties (variables) let you define your colour palette once and reuse it throughout your stylesheet. If you need to change your brand colour, you change one value and it updates everywhere. This creates consistency, reduces errors, and makes maintaining a design system practical. Variables are defined with <code>--name: value</code> and used with <code>var(--name)</code>.</p>
<div class="code-block">
<pre><code>:root {
  /* Define colour palette */
  --color-primary:    #0066cc;
  --color-secondary:  #ff6600;
  --color-text:       #1a1a1a;
  --color-text-muted: #6b7280;
  --color-bg:         #ffffff;
  --color-bg-muted:   #f9fafb;
  --color-border:     #e5e7eb;
}

/* Use variables everywhere */
body         { color: var(--color-text); background: var(--color-bg); }
.btn-primary { background: var(--color-primary); color: white; }
.card        { border: 1px solid var(--color-border); background: var(--color-bg-muted); }</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always define your colour palette as CSS custom properties in <code>:root</code>. Use HSL for generating colour variations (lighten/darken by adjusting lightness). Check colour contrast ratios — text must meet WCAG minimum contrast ratios (4.5:1 for normal text, 3:1 for large text) to be readable for users with low vision.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>No fallback background colour:</strong> When using background images, always include a background-color fallback. If the image fails to load, the text must still be readable.</li>
    <li><strong>Insufficient colour contrast:</strong> Light grey text on white background may look stylish but fails accessibility standards. Use a contrast checker to verify your text/background combinations.</li>
    <li><strong>Hardcoding colours everywhere:</strong> Hardcoding <code>#0066cc</code> in 50 places means 50 changes if the brand colour updates. Define everything as a CSS variable and reference that.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>CSS supports named, hex, RGB(A), and HSL(A) colour formats; HSL is most designer-friendly for creating colour variations.</li>
    <li>Gradients (linear-gradient, radial-gradient) are background images — they can be layered with real images for overlay effects.</li>
    <li>Define your entire colour palette as CSS custom properties in <code>:root</code> for consistency and easy maintenance.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Pseudo-classes and Pseudo-elements', <<<'HTML'
<h2>Pseudo-classes and Pseudo-elements</h2>

<p>Pseudo-classes and pseudo-elements extend what you can select and what you can style without adding extra HTML markup. Pseudo-classes target elements in specific states (hovered, focused, checked, the first child). Pseudo-elements create virtual elements within or around your content (the first letter, generated content before or after an element). Together they're among CSS's most powerful features for reducing HTML clutter and creating sophisticated effects.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Pseudo-classes are like adjectives describing an element's current situation: "the button when it's being hovered." Pseudo-elements are like conjuring a new object that doesn't exist in the HTML: "place a decorative quotation mark before this blockquote." Both let you style without touching the HTML structure.</p>
</div>

<h3>Common Pseudo-classes</h3>
<p>Pseudo-classes use a single colon prefix. The most frequently used are user interaction states: <code>:hover</code> (mouse over element), <code>:focus</code> (element has keyboard/click focus), <code>:active</code> (element being clicked). For form inputs: <code>:checked</code>, <code>:disabled</code>, <code>:required</code>, <code>:valid</code>, <code>:invalid</code>. Never remove <code>:focus</code> styling — it's how keyboard users know where they are. You can restyle it, but don't remove it.</p>
<div class="code-block">
<pre><code>/* Interactive states */
.btn:hover   { background: #004fa3; transform: translateY(-1px); }
.btn:active  { transform: translateY(0); }
.btn:focus   { outline: 2px solid #80bdff; outline-offset: 2px; }

/* Form states */
input:focus    { border-color: #0066cc; box-shadow: 0 0 0 3px rgba(0,102,204,0.15); }
input:disabled { background: #f5f5f5; cursor: not-allowed; opacity: 0.6; }
input:valid    { border-color: #28a745; }

/* Links */
a:link    { color: #0066cc; }    /* unvisited */
a:visited { color: #6f42c1; }    /* visited — helps users track navigation */
a:hover   { color: #004fa3; }
a:active  { color: #cc0000; }</code></pre>
</div>

<h3>Structural Pseudo-classes</h3>
<p>Structural pseudo-classes select elements based on their position in the document tree. <code>:first-child</code>, <code>:last-child</code>, and <code>:nth-child(n)</code> select elements by position among siblings. <code>:first-of-type</code> and <code>:last-of-type</code> select by position among siblings of the same type. <code>:not(selector)</code> selects elements that don't match a selector. These are powerful for styling lists, tables, and grid items without adding classes like <code>first</code> or <code>last</code>.</p>
<div class="code-block">
<pre><code>/* Remove border from last item in a list */
.nav-item:last-child { border-bottom: none; }

/* Zebra stripe a table */
tr:nth-child(even) { background: #f8f9fa; }

/* Style every third card differently */
.card:nth-child(3n) { border-color: #ff6600; }

/* All paragraphs except the first one */
p:not(:first-child) { margin-top: 1rem; }

/* First paragraph after a heading */
h2 + p:first-of-type { font-size: 1.1rem; color: #555; }</code></pre>
</div>

<h3>Pseudo-elements</h3>
<p>Pseudo-elements use a double colon prefix (::) and create virtual elements. <code>::before</code> and <code>::after</code> insert generated content before or after an element's actual content — they require a <code>content</code> property (even empty: <code>content: ""</code>). <code>::first-letter</code> selects the first character. <code>::first-line</code> selects the first rendered line. <code>::placeholder</code> styles placeholder text. <code>::selection</code> styles text the user has selected. These enable decorative effects and UI patterns without extra HTML.</p>
<div class="code-block">
<pre><code>/* Decorative quote marks on blockquote */
blockquote::before {
  content: '"';
  font-size: 4rem;
  color: #ff6600;
  line-height: 0;
  vertical-align: -1rem;
  margin-right: 0.2em;
}

/* Required field asterisk — no HTML needed */
.required-label::after {
  content: ' *';
  color: #dc3545;
}

/* Drop cap effect */
article p:first-child::first-letter {
  font-size: 3rem;
  float: left;
  line-height: 1;
  margin-right: 0.1em;
  font-weight: bold;
}

/* Style placeholder text */
input::placeholder { color: #9ca3af; font-style: italic; }

/* Style selected text */
::selection { background: #ff6600; color: white; }</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use :hover, :focus, :active for interactive feedback — every clickable element needs visible states. Use structural pseudo-classes instead of adding "first" or "last" classes in HTML. Use ::before and ::after for purely decorative content that would clutter your HTML with empty elements.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Removing :focus styles:</strong> <code>outline: none</code> on focused elements makes the site unusable for keyboard navigation. Replace the outline with a custom style, but never remove all focus indication.</li>
    <li><strong>Forgetting content property on ::before/::after:</strong> Pseudo-elements won't render without a <code>content</code> property. Use <code>content: ""</code> for purely visual pseudo-elements.</li>
    <li><strong>Confusing :first-child and :first-of-type:</strong> <code>p:first-child</code> matches a <code>&lt;p&gt;</code> that is the first child of its parent (fails if the first child is a <code>&lt;h2&gt;</code>). <code>p:first-of-type</code> matches the first <code>&lt;p&gt;</code> regardless of what other elements precede it.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Pseudo-classes (:hover, :focus, :checked, :nth-child) target elements in specific states or structural positions.</li>
    <li>Pseudo-elements (::before, ::after, ::placeholder) create virtual styled content without adding HTML markup.</li>
    <li>Never remove :focus styles — always replace the default with a custom, visible focus indicator for keyboard accessibility.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// CSS COURSE — Module 6
// =====================================================================

updateLesson($pdo, 'Flexbox Fundamentals', <<<'HTML'
<h2>Flexbox Fundamentals</h2>

<p>Flexbox (the Flexible Box Layout) is a CSS layout model designed for distributing space among items in a single row or column. Before flexbox, creating even simple layouts like a centred element or an equal-height card row required hacks. Flexbox makes these trivially easy. It's now the go-to tool for component-level layout — navigation bars, card rows, button groups, and form layouts.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A flexible rubber ruler. You can stretch items to fill available space, compress them to fit, or distribute the space between and around them. You control the main direction (horizontal or vertical), and the ruler handles the math of fitting everything in.</p>
</div>

<h3>The Flex Container</h3>
<p>Flexbox is activated by setting <code>display: flex</code> on a parent element — this makes it a flex container. Its direct children become flex items. By default, flex items line up in a row (horizontal), don't wrap when they overflow, and stretch to match the tallest item's height. The <code>flex-direction</code> property sets the main axis: <code>row</code> (default), <code>column</code> (vertical), <code>row-reverse</code>, or <code>column-reverse</code>.</p>
<div class="code-block">
<pre><code>.navbar {
  display: flex;
  flex-direction: row;    /* default — items in a row */
  flex-wrap: wrap;        /* allow items to wrap to next line */
}

.sidebar-layout {
  display: flex;
  flex-direction: column; /* stack items vertically */
}

/* flex-flow is shorthand for flex-direction + flex-wrap */
.card-grid {
  display: flex;
  flex-flow: row wrap;    /* row direction, allow wrapping */
}</code></pre>
</div>

<h3>justify-content and align-items</h3>
<p><code>justify-content</code> aligns items along the main axis. <code>align-items</code> aligns items along the cross axis (perpendicular to main). Together they cover every positioning scenario. <code>justify-content: space-between</code> pushes first item to the start and last to the end with equal gaps between. <code>align-items: center</code> vertically centres items in a row layout — something that was notoriously difficult before flexbox. Centering both axes is just two properties.</p>
<div class="code-block">
<pre><code>/* Navigation bar */
.navbar {
  display: flex;
  justify-content: space-between;  /* logo left, links right */
  align-items: center;             /* vertically centred */
  padding: 0 2rem;
  height: 64px;
}

/* Perfect centring (horizontal + vertical) */
.hero {
  display: flex;
  justify-content: center;   /* centre on main axis */
  align-items: center;       /* centre on cross axis */
  min-height: 100vh;
}

/* Space around items (equal space around each item) */
.icon-row {
  display: flex;
  justify-content: space-around;
}</code></pre>
</div>

<h3>Gap, flex-wrap, and align-content</h3>
<p>The <code>gap</code> property adds space between flex items — cleaner than using margins because it only adds space between items, not on the outside edges. <code>flex-wrap: wrap</code> allows items to move to a new line when they don't fit. When items wrap onto multiple lines, <code>align-content</code> controls spacing between the lines (similar to justify-content but for wrapped lines). Use gap instead of margin on flex items whenever possible.</p>
<div class="code-block">
<pre><code>.card-row {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;          /* 24px between all items, all sides */
}

.card-row > .card {
  flex: 1 1 280px;      /* grow, shrink, basis — min 280px wide */
}

/* Align wrapped lines */
.tag-cloud {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-content: flex-start;  /* pack lines toward the start */
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use flexbox for one-dimensional layouts: navigation bars, button groups, card rows, form field rows, centering content. Use CSS Grid (next lesson) for two-dimensional layouts where you need control over both rows and columns simultaneously. Most UIs use both — flexbox for components, grid for page layout.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Applying flex to the wrong element:</strong> <code>display: flex</code> affects the direct children. If your layout isn't working, check that you're targeting the parent, not the items themselves.</li>
    <li><strong>Forgetting flex-wrap:</strong> By default, flex items squeeze into one line, potentially overflowing. Add <code>flex-wrap: wrap</code> when you want items to reflow to a new line.</li>
    <li><strong>Using margin for gaps between flex items:</strong> Margin on flex items adds space on all sides. Use the <code>gap</code> property instead — it only adds space between items.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li><code>display: flex</code> on a parent creates a flex container; direct children become flex items arranged in a row by default.</li>
    <li><code>justify-content</code> aligns items on the main axis; <code>align-items</code> aligns on the cross axis — together they handle all positioning scenarios.</li>
    <li>Use <code>gap</code> for spacing between items and <code>flex-wrap: wrap</code> to allow items to reflow to a new line.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Flex Item Properties', <<<'HTML'
<h2>Flex Item Properties</h2>

<p>While flex container properties control the overall layout, flex item properties control how each individual item behaves within that container. These properties let you specify how much an item grows, how much it can shrink, its preferred size, and even override the container's alignment for that specific item. Understanding flex item properties is what separates basic flexbox use from truly flexible layouts.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Employees at a company holiday party seating arrangement. The manager (container) says "spread out across the table." But one employee (item) says "I want to sit at the head" (order), another says "I can take more space if needed" (flex-grow), and another says "please don't squish me below my minimum" (flex-shrink). Each item has its own preferences within the container's rules.</p>
</div>

<h3>flex-grow, flex-shrink, flex-basis</h3>
<p>These three properties control how a flex item distributes extra space. <code>flex-grow</code> determines what proportion of available extra space the item takes (0 = don't grow). <code>flex-shrink</code> determines how much the item shrinks if there isn't enough space (0 = don't shrink). <code>flex-basis</code> sets the initial size before growing/shrinking — it's like a starting width. The shorthand <code>flex</code> combines all three. Common patterns: <code>flex: 1</code> (equal-width items that fill the container), <code>flex: none</code> (item keeps its intrinsic size).</p>
<div class="code-block">
<pre><code>.sidebar { flex: 0 0 250px; }   /* don't grow, don't shrink, always 250px */
.main    { flex: 1; }           /* grow to fill remaining space */

/* Cards that share space equally */
.card { flex: 1 1 0; }          /* grow and shrink equally from 0 base */

/* flex shorthand patterns */
.item-a { flex: 2; }    /* gets 2x more extra space than flex: 1 items */
.item-b { flex: 1; }
.item-c { flex: 1; }
/* If 300px available after min sizes, item-a gets 150px, b and c get 75px each */

/* Don't flex: keep intrinsic size */
.fixed-icon { flex: none; width: 24px; height: 24px; }</code></pre>
</div>

<h3>align-self and the Cross Axis</h3>
<p><code>align-self</code> overrides the container's <code>align-items</code> value for a specific item. This lets most items be centred vertically while one specific item aligns to the bottom, for example. Values: <code>flex-start</code>, <code>flex-end</code>, <code>center</code>, <code>stretch</code> (default), and <code>baseline</code> (aligns text baselines). <code>baseline</code> is particularly useful when items have different font sizes — it keeps the text line up neatly.</p>
<div class="code-block">
<pre><code>.card-container {
  display: flex;
  align-items: stretch;    /* all cards same height by default */
}

.card-featured {
  align-self: flex-start;  /* this card stays its natural height */
}

.icon-label-group {
  display: flex;
  align-items: baseline;   /* icon and text aligned by text baseline */
}

.btn-group {
  display: flex;
  align-items: center;
}

.btn-group .badge {
  align-self: flex-start;  /* badge sits at top, button centred */
}</code></pre>
</div>

<h3>order</h3>
<p>The <code>order</code> property changes the visual order of flex items without changing the HTML order. Items with lower order values appear first; default is 0. This is useful for responsive layouts where you want to reorder content for mobile vs desktop, or for putting a "featured" item first visually without moving it in the HTML (which would affect tab order and screen readers). Always be cautious: visual order and DOM order should match for keyboard and screen reader users.</p>
<div class="code-block">
<pre><code>.sidebar   { order: 1; }   /* default: 0 */
.main      { order: 0; }   /* appears before sidebar visually */
.footer    { order: 2; }

/* Mobile-first: stack vertically, sidebar below main */
@media (max-width: 768px) {
  .sidebar { order: 2; }   /* sidebar below main on mobile */
  .main    { order: 1; }
}</code></pre>
</div>

<h3>Practical Patterns</h3>
<p>Combining flex item properties creates powerful layout patterns. The "holy grail" layout (header, sidebar, main, another sidebar, footer) and sticky footers are classic examples. A common pattern: a card with an image, text content that grows, and a button that sticks to the bottom — achievable by making the card a flex column container and giving the content area <code>flex: 1</code>.</p>
<div class="code-block">
<pre><code>/* Card with sticky footer button */
.card {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.card-body {
  flex: 1;       /* grows to fill available space */
  padding: 1.5rem;
}

.card-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e0e0e0;
  /* flex-shrink: 0 by default — won't compress */
}

/* Sticky footer layout */
body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

main { flex: 1; }   /* main content fills space, pushing footer to bottom */</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use flex-grow to create fluid layouts where items share available space proportionally. Use flex: none on items that should keep their intrinsic size (icons, logos, fixed-width sidebars). Use align-self for one-off alignment overrides. Use order sparingly — it creates a disconnect between visual order and tab/reading order.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>flex: 1 vs flex: 1 1 0:</strong> <code>flex: 1</code> is shorthand for <code>flex: 1 1 0</code>. The 0 basis means items start from nothing and grow equally. Using <code>flex: 1 1 auto</code> starts from content size, which can produce unequal sizing.</li>
    <li><strong>Overusing order for reordering:</strong> The order property changes visual order but not DOM order. Screen readers and keyboard navigation follow DOM order. Use it only for cosmetic reordering where DOM order still makes logical sense.</li>
    <li><strong>Forgetting flex-shrink can cause overflow:</strong> Setting <code>flex-shrink: 0</code> prevents an item from shrinking, which can cause overflow if the container isn't wide enough. Ensure the container can either grow or scroll.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>flex-grow, flex-shrink, flex-basis control how items distribute space; the shorthand <code>flex: 1</code> creates equal-width items that fill the container.</li>
    <li><code>align-self</code> overrides the container's align-items for a specific item, allowing individual alignment exceptions.</li>
    <li>Make cards with equal-height buttons by setting flex-direction: column on the card and flex: 1 on the card body.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'CSS Grid Fundamentals', <<<'HTML'
<h2>CSS Grid Fundamentals</h2>

<p>CSS Grid is a two-dimensional layout system — it handles both rows and columns simultaneously. Where flexbox is designed for laying out items in a single row or column, Grid excels when you need to position items across both dimensions at once. Page layouts, dashboards, image galleries, and complex card grids all become straightforward with Grid. It's one of the most significant advances in CSS layout history.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Graph paper. You have a grid of cells defined by horizontal and vertical lines. You can place items in specific cells, or stretch items across multiple cells in either direction. Flexbox is a ruler for one line; Grid is a full sheet of graph paper.</p>
</div>

<h3>Defining a Grid</h3>
<p>Set <code>display: grid</code> on a container. <code>grid-template-columns</code> defines the column structure; <code>grid-template-rows</code> defines rows. The <code>fr</code> unit is the "fractional unit" — it distributes available space proportionally after fixed sizes are allocated. The <code>repeat()</code> function avoids repetitive declarations. <code>minmax()</code> sets minimum and maximum sizes for a track. <code>auto-fill</code> and <code>auto-fit</code> inside repeat() create responsive grids that adapt the number of columns to the container width.</p>
<div class="code-block">
<pre><code>/* 3-column grid: all columns equal width */
.grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  /* same as: repeat(3, 1fr) */
}

/* Sidebar layout: fixed sidebar, flexible main */
.page-layout {
  display: grid;
  grid-template-columns: 250px 1fr;  /* sidebar | main */
  gap: 2rem;
}

/* Responsive grid: columns auto-fill based on width */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}
/* This creates as many 250px+ columns as fit in the container */</code></pre>
</div>

<h3>Placing Items on the Grid</h3>
<p>Grid items can be explicitly placed using column and row lines. Lines are numbered starting at 1. <code>grid-column: 1 / 3</code> means "start at line 1, end at line 3" (spanning 2 columns). <code>span</code> keyword: <code>grid-column: span 2</code> spans 2 columns from wherever the item is automatically placed. By default, items flow into the next available cell — explicit placement overrides this.</p>
<div class="code-block">
<pre><code>.grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

/* Span across all 4 columns */
.featured-item {
  grid-column: 1 / 5;     /* from line 1 to line 5 = all 4 columns */
  /* or: grid-column: 1 / -1; — -1 means the last line */
}

/* Span 2 columns */
.wide-item {
  grid-column: span 2;    /* 2 columns wide from auto-placement position */
}

/* Explicitly place in a specific row and column */
.pinned-item {
  grid-column: 3 / 4;     /* column 3 */
  grid-row: 1 / 2;        /* row 1 */
}</code></pre>
</div>

<h3>Grid Gap and Alignment</h3>
<p>The <code>gap</code> property (formerly grid-gap) creates consistent gutters between all rows and columns. Use <code>column-gap</code> and <code>row-gap</code> separately if you want different horizontal and vertical spacing. Alignment in grid uses <code>justify-items</code> (align items in their cells horizontally), <code>align-items</code> (vertically), <code>justify-content</code> (the grid tracks as a whole), and <code>align-content</code> (the grid tracks vertically). Individual items use <code>justify-self</code> and <code>align-self</code>.</p>
<div class="code-block">
<pre><code>.grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;              /* same gap horizontal and vertical */
  row-gap: 1rem;          /* different gaps */
  column-gap: 2rem;
}

/* Centre all items in their cells */
.icon-grid {
  display: grid;
  grid-template-columns: repeat(4, 100px);
  justify-items: center;  /* horizontal */
  align-items: center;    /* vertical */
}

/* Centre one item in its cell */
.icon {
  justify-self: center;
  align-self: center;
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use Grid for page-level layouts and any component that needs explicit two-dimensional placement. Use flexbox for one-dimensional component layouts. The two work together perfectly — Grid for the page structure, flexbox for the navigation bar, card internals, and form layouts within grid areas.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Choosing between Grid and Flexbox:</strong> They're complementary, not competing. A common mistake is forcing Grid to do everything or forcing Flexbox to handle two-dimensional layouts. Use each for its strength.</li>
    <li><strong>Using pixel widths for all columns:</strong> Fixed pixel columns don't respond to viewport size changes. Use fr units, percentages, or minmax() with auto-fill for responsive grids.</li>
    <li><strong>Forgetting implicit grid rows:</strong> If you define column tracks but items overflow to more rows than you defined, the browser creates implicit rows. Control their size with <code>grid-auto-rows</code>.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>CSS Grid handles both rows and columns; set <code>display: grid</code> then define tracks with grid-template-columns/rows using fr, px, or repeat().</li>
    <li>The fr unit distributes available space proportionally; <code>repeat(auto-fill, minmax(250px, 1fr))</code> creates a responsive grid that adapts column count to container width.</li>
    <li>Items are placed by grid lines (grid-column: 1/3) or span notation (grid-column: span 2); the default is auto-placement in reading order.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Grid Template Areas', <<<'HTML'
<h2>Grid Template Areas</h2>

<p>Grid template areas let you name regions of your grid layout and assign elements to those regions using plain text. Instead of counting grid line numbers, you draw a visual ASCII map of your layout and assign elements by name. This produces the most readable, maintainable CSS layouts you'll write — especially for full page layouts where the structure needs to change between breakpoints.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A floor plan. An architect draws a blueprint with rooms labelled "Kitchen," "Living Room," "Bedroom." You can read it at a glance and understand the layout. Grid template areas are your CSS floor plan — you define regions by name and then place each element into its named room.</p>
</div>

<h3>Defining Named Areas</h3>
<p>The <code>grid-template-areas</code> property uses a string for each row, with space-separated area names for each cell. A period (<code>.</code>) represents an empty cell. The area names must form a rectangle — you can't create L-shaped or irregular areas. Then assign each element to an area using <code>grid-area: name</code>. The visual ASCII map in your CSS exactly represents the grid layout, making it self-documenting.</p>
<div class="code-block">
<pre><code>.page {
  display: grid;
  grid-template-areas:
    "header  header  header"
    "sidebar main    main  "
    "sidebar main    main  "
    "footer  footer  footer";
  grid-template-columns: 250px 1fr 1fr;
  grid-template-rows: 64px 1fr 1fr 80px;
  min-height: 100vh;
}

header  { grid-area: header;  }
.sidebar { grid-area: sidebar; }
main    { grid-area: main;    }
footer  { grid-area: footer;  }</code></pre>
</div>

<h3>Responsive Layout with Media Queries</h3>
<p>The real power of named areas appears in responsive design. To change the entire page layout at a breakpoint, you just redefine <code>grid-template-areas</code> — the element-to-area assignments stay the same. Switching from a sidebar layout to a stacked mobile layout is just a matter of rewriting the ASCII map, not moving HTML elements or adding classes. This is why grid areas are considered the most maintainable approach to responsive page layouts.</p>
<div class="code-block">
<pre><code>/* Mobile: stacked layout */
.page {
  display: grid;
  grid-template-areas:
    "header"
    "main"
    "sidebar"
    "footer";
  grid-template-columns: 1fr;
}

/* Tablet: sidebar alongside main */
@media (min-width: 768px) {
  .page {
    grid-template-areas:
      "header  header"
      "sidebar main"
      "footer  footer";
    grid-template-columns: 220px 1fr;
  }
}

/* Desktop: wider sidebar */
@media (min-width: 1200px) {
  .page {
    grid-template-areas:
      "header  header  header"
      "sidebar main    ads"
      "footer  footer  footer";
    grid-template-columns: 250px 1fr 200px;
  }
}</code></pre>
</div>

<h3>Combining Areas with Other Grid Features</h3>
<p>Named areas work alongside explicit grid line placement. You can use areas for the overall layout and line placement for fine-grained item positioning within those areas. The <code>grid-template</code> shorthand combines rows, columns, and areas into a single declaration. You can also use areas in combination with the implicit grid, <code>auto</code> sizing, and <code>minmax()</code> to create sophisticated, self-adapting layouts.</p>
<div class="code-block">
<pre><code>/* grid-template shorthand: rows / columns with inline area names */
.layout {
  display: grid;
  grid-template:
    "nav  nav " 64px
    "side main" 1fr
    "foot foot" 80px
    / 200px 1fr;   /* column sizes after the slash */
}

/* Dashboard: header spans all, cards below */
.dashboard {
  display: grid;
  grid-template-areas:
    "stats  stats  stats"
    "chart  chart  feed "
    "table  table  feed ";
  grid-template-columns: 1fr 1fr 300px;
  gap: 1.5rem;
}

.stats  { grid-area: stats;  }
.chart  { grid-area: chart;  }
.feed   { grid-area: feed;   }
.table  { grid-area: table;  }</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use grid template areas for any layout with named, distinct regions — page layouts, dashboards, and article layouts. The ASCII map makes the layout intent self-documenting and drastically simplifies responsive redesigns. Pair with numeric line placement only for fine details within an area.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Non-rectangular areas:</strong> Grid area names must form a perfect rectangle. An L-shaped or irregular area will cause the entire grid-template-areas declaration to be invalid and ignored.</li>
    <li><strong>Mismatched column/row counts:</strong> Every row in the template string must have the same number of area names as there are columns. Unequal rows invalidate the declaration.</li>
    <li><strong>Forgetting to define column sizes:</strong> <code>grid-template-areas</code> defines layout names but not sizes. Always pair it with <code>grid-template-columns</code> and <code>grid-template-rows</code> to control dimensions.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>grid-template-areas creates a visual ASCII map of your layout; assign elements to named regions with <code>grid-area: name</code>.</li>
    <li>Responsive layout changes are just a matter of rewriting the template-areas string in media queries — element assignments stay the same.</li>
    <li>Areas must form a rectangle; always pair grid-template-areas with column and row size definitions.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Responsive Design', <<<'HTML'
<h2>Responsive Design</h2>

<p>Responsive design makes websites look and work well on any device — from a 320px wide phone to a 2560px wide desktop monitor. More than half of all web traffic is on mobile devices. A website that works only on desktop excludes most of its potential audience. Responsive design is not a feature to add at the end — it's a fundamental approach to building for the web.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Water. Water doesn't have a fixed shape — it fills whatever container it's in perfectly, whether it's a tall glass or a wide bowl. Responsive design works the same way: your layout flows and adapts to whatever screen it's displayed on, rather than having a rigid fixed shape that only works for one container size.</p>
</div>

<h3>Media Queries</h3>
<p>Media queries apply CSS rules only when certain conditions are met — most commonly, when the viewport is a certain width. They're the foundational tool of responsive design. The modern approach is <strong>mobile-first</strong>: write base styles for mobile, then use <code>min-width</code> queries to add complexity for larger screens. This is more performant than desktop-first (which uses max-width) because mobile devices don't have to parse and override desktop styles.</p>
<div class="code-block">
<pre><code>/* Mobile-first base styles */
.card {
  width: 100%;
  padding: 1rem;
}

/* Tablet: min-width 768px */
@media (min-width: 768px) {
  .card {
    width: calc(50% - 1rem);
  }

  .page-layout {
    display: grid;
    grid-template-columns: 200px 1fr;
  }
}

/* Desktop: min-width 1200px */
@media (min-width: 1200px) {
  .card {
    width: calc(33.333% - 1.5rem);
  }
}</code></pre>
</div>

<h3>Fluid Units and Flexible Sizing</h3>
<p>Responsive design relies on relative units rather than fixed pixels. <code>%</code> is relative to the parent. <code>vw</code> and <code>vh</code> are percentages of the viewport width/height. <code>rem</code> is relative to the root font size. <code>clamp(min, preferred, max)</code> combines a preferred fluid size with minimum and maximum bounds — perfect for typography that scales smoothly with the viewport without jumping at breakpoints.</p>
<div class="code-block">
<pre><code>/* Fluid typography with clamp: min 1rem, scales with viewport, max 2rem */
h1 { font-size: clamp(1.5rem, 4vw, 3rem); }
p  { font-size: clamp(1rem, 2vw, 1.25rem); }

/* Full-width hero section */
.hero {
  width: 100%;
  min-height: 60vh;   /* at least 60% of viewport height */
}

/* Container with responsive max-width */
.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;   /* side padding on mobile */
}

/* Responsive image */
img {
  max-width: 100%;
  height: auto;       /* maintain aspect ratio */
}</code></pre>
</div>

<h3>Responsive Navigation Patterns</h3>
<p>Navigation is one of the most important responsive challenges. On desktop, a horizontal nav bar works well. On mobile, the same links take too much space. Common solutions: a hamburger menu (show/hide toggle with JavaScript), a bottom navigation bar, or accordion navigation. The pattern doesn't require a specific solution — just one that works at all sizes. CSS alone can do a lot (checkbox-based toggles), but JavaScript-based solutions are more accessible.</p>
<div class="code-block">
<pre><code>/* Mobile: hide nav, show toggle button */
.nav-links {
  display: none;
}

.nav-toggle {
  display: block;
}

/* Desktop: show nav, hide toggle */
@media (min-width: 768px) {
  .nav-links {
    display: flex;
    gap: 2rem;
  }

  .nav-toggle {
    display: none;
  }
}

/* When menu is open (class added by JS) */
.nav-links.is-open {
  display: flex;
  flex-direction: column;
  /* ... mobile menu styles */
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always design mobile-first. Test on real devices or browser DevTools device emulation. Use breakpoints based on your content breaking, not arbitrary device sizes. Common breakpoints: 480px (large phone), 768px (tablet), 1024px (laptop), 1200px (desktop). The viewport meta tag in HTML is required for media queries to work on mobile.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Missing the viewport meta tag:</strong> Without <code>&lt;meta name="viewport" content="width=device-width, initial-scale=1"&gt;</code> in the HTML head, mobile browsers ignore your media queries and zoom out to show a desktop view.</li>
    <li><strong>Fixed pixel widths in layouts:</strong> Setting <code>width: 960px</code> on a container breaks on smaller screens. Use max-width with 100% instead.</li>
    <li><strong>Testing only in browser DevTools:</strong> Device emulation is useful but not identical to real devices. Touch interactions, performance, and rendering differences can only be caught on actual hardware.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Mobile-first responsive design: write base styles for mobile, add complexity with min-width media queries for larger screens.</li>
    <li>Use relative units (%, vw, rem, clamp) rather than fixed pixels for responsive sizing of layouts and typography.</li>
    <li>The HTML viewport meta tag is required for media queries to work on mobile devices.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// CSS COURSE — Module 7
// =====================================================================

updateLesson($pdo, 'CSS Transitions', <<<'HTML'
<h2>CSS Transitions</h2>

<p>CSS transitions animate property changes smoothly over time instead of jumping instantly from one value to another. When you hover a button and it changes colour, a transition makes that colour change fade gradually rather than snap. Transitions require almost no code and add significant polish to any interface. They're the easiest, most impactful CSS feature for improving perceived quality.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A dimmer switch versus a light switch. A light switch snaps on and off instantly. A dimmer switch gradually brightens or fades. CSS transitions are the dimmer switch — they make state changes gradual and smooth instead of jarring and abrupt.</p>
</div>

<h3>The transition Property</h3>
<p>The <code>transition</code> property is a shorthand for four values: the property to animate, the duration, the timing function, and the delay. Apply it to the element in its default state — not in the :hover or other state. This way, the transition plays both when entering and leaving the state. Common convention: animate <code>all</code> properties, but for performance it's better to name specific properties. Only animatable properties can transition — you can't transition display from none to block.</p>
<div class="code-block">
<pre><code>/* Shorthand: property | duration | timing-function | delay */
.btn {
  background-color: #0066cc;
  color: white;
  padding: 12px 24px;
  border-radius: 4px;

  /* Transition background and transform over 200ms with ease-out */
  transition: background-color 200ms ease-out,
              transform 150ms ease-out;
}

.btn:hover {
  background-color: #004fa3;
  transform: translateY(-2px);  /* lift effect */
}

.btn:active {
  transform: translateY(0);     /* press effect */
}</code></pre>
</div>

<h3>Timing Functions</h3>
<p>The timing function controls the speed curve of the animation — how it accelerates and decelerates. <code>ease</code> (default) starts slow, speeds up, slows at end. <code>ease-in</code> starts slow, ends fast. <code>ease-out</code> starts fast, ends slow. <code>ease-in-out</code> slow at both ends. <code>linear</code> constant speed. <code>cubic-bezier()</code> lets you define a custom curve. For most UI transitions, <code>ease-out</code> feels most natural — things quickly respond then settle gently.</p>
<div class="code-block">
<pre><code>/* Common timing functions */
.card {
  transition: box-shadow 300ms ease-out;
}
.card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

/* Custom cubic-bezier for a "spring" feel */
.menu {
  transition: transform 400ms cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Linear for colour cycling or continuous animations */
.spinner {
  transition: transform 1s linear;
}

/* Steps: jump between values (like frame animation) */
.progress-bar {
  transition: width 500ms steps(10);
}</code></pre>
</div>

<h3>What Can Be Transitioned</h3>
<p>Not all CSS properties can be transitioned — only those that have numeric intermediate values. Animatable properties include: colours, dimensions (width, height), positions, opacity, transform, box-shadow, border-radius, and many more. Non-animatable properties include display, visibility (partially), font-family, and background-image. A common technique for showing/hiding elements is to transition opacity from 0 to 1, combined with pointer-events and visibility for accessibility.</p>
<div class="code-block">
<pre><code>/* Show/hide with opacity + visibility transition */
.tooltip {
  opacity: 0;
  visibility: hidden;
  transform: translateY(-8px);
  transition: opacity 200ms ease, visibility 200ms ease, transform 200ms ease;
}

.tooltip.is-visible {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

/* Transition multiple properties */
.card {
  transform: scale(1);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition:
    transform 200ms ease-out,
    box-shadow 200ms ease-out;
}

.card:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Add transitions to every interactive element: buttons, links, form inputs, cards on hover. Keep durations short — 150-300ms feels snappy and responsive. Avoid transitioning expensive properties like width, height, or top/left (they trigger layout recalculations). Prefer transitioning transform and opacity, which only affect the composite layer and are GPU-accelerated.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Putting transition on the hover state:</strong> If you put transition on <code>:hover</code> instead of the base element, the animation plays when entering the hover state but not when leaving. Always put transition on the default state.</li>
    <li><strong>Transitioning width/height for expand effects:</strong> Transitioning max-height (from 0 to a max value) or height is expensive and often janky. For expand/collapse animations, consider transitioning transform: scaleY or using the Web Animations API.</li>
    <li><strong>Transitions on display: none:</strong> You cannot transition to/from <code>display: none</code> — the browser treats it as an instant change. Use opacity + visibility, or combine with JavaScript class toggling.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>CSS transitions animate property changes over time; apply the transition property in the default state, not the :hover state.</li>
    <li>Use ease-out timing for most interactive transitions (responsive feel); keep durations 150-300ms for best UX.</li>
    <li>Prefer transitioning transform and opacity over layout properties (width, height) for smooth, GPU-accelerated animations.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'CSS Animations', <<<'HTML'
<h2>CSS Animations</h2>

<p>While transitions animate between two states triggered by a state change, CSS animations can run automatically, loop indefinitely, play multiple keyframes, and run without any user interaction. Loading spinners, pulsing notifications, bouncing icons, and animated backgrounds are all possible with pure CSS animations. They're defined in two steps: the keyframes (what values to animate between) and the animation property (how to apply those keyframes to an element).</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Choreographing a dance routine. The @keyframes rule is the choreography sheet — describing what pose the dancer should be in at what point in the routine (0%, 50%, 100%). The animation property is the instruction to a specific dancer (element) to perform that routine, at what speed, how many times, and whether to reverse at the end.</p>
</div>

<h3>Defining Keyframes</h3>
<p>The <code>@keyframes</code> rule defines an animation's stages. You name it anything you like. Inside, percentage values (or <code>from</code>/<code>to</code> for two-step animations) define the CSS values at that point in the animation. The browser interpolates between keyframes automatically. You can have as many keyframe stops as you need. The keyframe declaration can live anywhere in your CSS file.</p>
<div class="code-block">
<pre><code>/* Simple two-state animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0);    }
}

/* Multi-state animation */
@keyframes pulse {
  0%   { transform: scale(1);    opacity: 1;   }
  50%  { transform: scale(1.05); opacity: 0.8; }
  100% { transform: scale(1);    opacity: 1;   }
}

/* Infinite rotation */
@keyframes spin {
  from { transform: rotate(0deg);   }
  to   { transform: rotate(360deg); }
}</code></pre>
</div>

<h3>The animation Property</h3>
<p>The <code>animation</code> shorthand applies a named keyframe animation to an element. The key sub-properties: <code>animation-name</code>, <code>animation-duration</code>, <code>animation-timing-function</code>, <code>animation-delay</code>, <code>animation-iteration-count</code> (number or <code>infinite</code>), <code>animation-direction</code> (<code>alternate</code> plays forward then backward), <code>animation-fill-mode</code> (<code>forwards</code> keeps the final state after animation ends), <code>animation-play-state</code> (<code>paused</code> or <code>running</code>).</p>
<div class="code-block">
<pre><code>/* Apply fadeIn to a modal */
.modal {
  animation: fadeIn 300ms ease-out forwards;
  /* name | duration | timing | fill-mode */
}

/* Infinite loading spinner */
.spinner {
  animation: spin 1s linear infinite;
}

/* Pulsing notification badge */
.badge {
  animation: pulse 2s ease-in-out infinite;
}

/* Stagger animations with delay */
.card:nth-child(1) { animation: fadeIn 400ms ease-out 0ms   forwards; }
.card:nth-child(2) { animation: fadeIn 400ms ease-out 100ms forwards; }
.card:nth-child(3) { animation: fadeIn 400ms ease-out 200ms forwards; }</code></pre>
</div>

<h3>Practical Animation Examples</h3>
<p>A few commonly needed animation patterns: loading skeleton shimmer (animating a gradient across a placeholder), a typing cursor blink, a shake animation for form validation errors, and a slide-in notification. These cover the majority of UI animation needs without reaching for a JavaScript animation library.</p>
<div class="code-block">
<pre><code>/* Shake: error feedback */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%       { transform: translateX(-8px); }
  40%       { transform: translateX(8px); }
  60%       { transform: translateX(-8px); }
  80%       { transform: translateX(8px); }
}

.input-error {
  animation: shake 400ms ease-in-out;
  border-color: #dc3545;
}

/* Skeleton shimmer loading effect */
@keyframes shimmer {
  from { background-position: -400px 0; }
  to   { background-position: 400px 0;  }
}

.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 400px 100%;
  animation: shimmer 1.5s infinite linear;
  border-radius: 4px;
  height: 1em;
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use CSS animations for: loading states, entrance animations, continuous looping effects, and attention-directing pulses. Respect user preferences: some users experience motion sickness. Use the <code>prefers-reduced-motion</code> media query to disable or reduce animations for users who have requested this in their OS settings.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Not respecting prefers-reduced-motion:</strong> Always wrap animations in a <code>@media (prefers-reduced-motion: no-preference)</code> block, or use <code>prefers-reduced-motion: reduce</code> to disable them for users who need it.</li>
    <li><strong>Animating too many properties simultaneously:</strong> Animating layout properties (width, height, top, left) forces the browser to recalculate layout on every frame. Stick to transform and opacity for smooth 60fps animations.</li>
    <li><strong>Forgetting animation-fill-mode: forwards:</strong> Without <code>forwards</code>, the element snaps back to its original state when the animation ends. Use <code>forwards</code> to keep the final keyframe state.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>CSS animations need two parts: @keyframes (the choreography) and the animation property (the performer and timing).</li>
    <li>Use animation-iteration-count: infinite for looping animations; animation-fill-mode: forwards to keep the end state.</li>
    <li>Always add a prefers-reduced-motion media query to disable or reduce animations for users who need it.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Transforms', <<<'HTML'
<h2>Transforms</h2>

<p>CSS transforms let you move, rotate, scale, and skew elements without affecting the document flow. When you transform an element, other elements don't shift to accommodate it — it's moved in a separate layer. This makes transforms the ideal tool for interactive effects, animations, and position adjustments that shouldn't disturb surrounding content. Transforms are also GPU-accelerated, making them the smoothest CSS property to animate.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Placing a piece of tracing paper over a drawing and moving it around. The original drawing stays in place; you're just moving the overlay. CSS transforms work the same way — the element visually moves but its original space in the document flow is preserved.</p>
</div>

<h3>2D Transform Functions</h3>
<p>The <code>transform</code> property takes one or more transform functions. <code>translate(x, y)</code> moves an element. <code>scale(x, y)</code> resizes it. <code>rotate(angle)</code> spins it. <code>skew(x, y)</code> slants it. You can chain multiple transforms in one declaration — they're applied right to left. <code>translateX()</code>, <code>translateY()</code>, <code>scaleX()</code>, etc. are shorthand for single-axis transforms. The <code>translate(-50%, -50%)</code> trick for centring absolutely positioned elements is one of the most useful transform patterns.</p>
<div class="code-block">
<pre><code>/* Move: translate(x, y) */
.moved { transform: translate(50px, 20px); }

/* Scale: 1 = original, 1.2 = 20% bigger, 0.8 = 20% smaller */
.card:hover { transform: scale(1.03); }

/* Rotate */
.icon:hover { transform: rotate(180deg); }
.badge      { transform: rotate(-3deg); } /* slight tilt effect */

/* Skew */
.diagonal-bg { transform: skewY(-5deg); }

/* Multiple transforms: applied right-to-left */
.btn:hover {
  transform: translateY(-3px) scale(1.02);
}

/* Classic centring trick */
.centered-modal {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);  /* move back by half its own size */
}</code></pre>
</div>

<h3>Transform Origin</h3>
<p>By default, transforms happen relative to the element's centre (50% 50%). <code>transform-origin</code> changes this reference point. It accepts keywords (top, right, bottom, left, center), percentages, or pixel values. A rotation from the top-left corner vs the centre produces very different effects. This is essential for effects like a card flipping open from one edge, or a tooltip appearing to emerge from a specific corner.</p>
<div class="code-block">
<pre><code>/* Default: scales from centre */
.badge { transform: scale(1.2); }

/* Scale from top-left corner */
.corner-badge {
  transform-origin: top left;
  transform: scale(0.9);
}

/* Rotate around bottom centre (pendulum effect) */
.pendulum {
  transform-origin: top center;
  animation: swing 2s ease-in-out infinite alternate;
}

@keyframes swing {
  from { transform: rotate(-30deg); }
  to   { transform: rotate(30deg);  }
}

/* Hover grow from specific point */
.card-image {
  transform-origin: center bottom;
  transition: transform 300ms ease;
}
.card:hover .card-image {
  transform: scale(1.1);  /* grows upward from bottom */
}</code></pre>
</div>

<h3>3D Transforms</h3>
<p>CSS also supports 3D transforms — rotations and translations in three dimensions. <code>rotateX()</code> flips around the horizontal axis (like opening a laptop). <code>rotateY()</code> rotates around the vertical axis (like a spinning door). <code>translateZ()</code> moves toward or away from the viewer. To see 3D effects, the parent needs <code>perspective</code> to establish a viewpoint distance. The classic card flip effect uses 3D transforms with <code>backface-visibility: hidden</code>.</p>
<div class="code-block">
<pre><code>/* Card flip effect */
.card-container {
  perspective: 1000px;       /* 3D viewpoint distance */
}

.card {
  transform-style: preserve-3d;
  transition: transform 600ms ease;
}

.card:hover {
  transform: rotateY(180deg);
}

.card-front, .card-back {
  backface-visibility: hidden;  /* hide the back face of each side */
  position: absolute;
  inset: 0;
}

.card-back {
  transform: rotateY(180deg);   /* start the back face already flipped */
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use transforms for all visual positioning adjustments that shouldn't affect layout flow. Use translate for subtle hover movement effects. Use scale for focus/zoom effects on hover. Use rotate for icon state changes (like a chevron arrow toggling). Always animate transforms rather than top/left/margin for smooth, GPU-accelerated movement.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Animating top/left instead of transform: translate:</strong> Moving elements with top/left triggers layout recalculations each frame, causing janky animations. Use transform: translate for all motion animations.</li>
    <li><strong>Forgetting perspective for 3D transforms:</strong> 3D transforms on an element appear flat (orthographic) without a perspective value on the parent. Set perspective to see the 3D effect.</li>
    <li><strong>Multiple transforms overriding each other:</strong> Writing two separate transform rules on the same element — one overrides the other. Combine all transforms in a single declaration: <code>transform: translateY(-3px) scale(1.02)</code>.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Transform functions (translate, scale, rotate, skew) move elements visually without affecting document flow.</li>
    <li>transform-origin changes the reference point for transformations — default is 50% 50% (centre).</li>
    <li>3D transforms require perspective on the parent; use backface-visibility: hidden for card-flip effects.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Shadows and Effects', <<<'HTML'
<h2>Shadows and Effects</h2>

<p>Shadows, blurs, and visual effects add depth and polish to interfaces. CSS provides box shadows, text shadows, filter effects, and backdrop filters — all without image editing software. Used judiciously, these effects create a sense of hierarchy, depth, and interactivity. Used excessively, they create visual noise. The key is intentional, subtle application.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Lighting in photography. A single soft shadow under a card suggests it's floating above the page, creating depth. Multiple harsh shadows everywhere create visual chaos. Good lighting (in photography and CSS) is often only noticed when it's absent.</p>
</div>

<h3>box-shadow</h3>
<p>Box shadows cast shadows from element borders. The syntax is: <code>offset-x offset-y blur-radius spread-radius color</code>. Positive offset-x/y move the shadow right/down; negative values move it left/up. Larger blur radius creates a softer shadow. Spread radius expands or contracts the shadow size. Adding <code>inset</code> at the start makes the shadow appear inside the element. You can apply multiple shadows separated by commas — very useful for multi-layered "elevation" effects used in Material Design.</p>
<div class="code-block">
<pre><code>/* Subtle card shadow */
.card {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

/* Elevated shadow on hover */
.card:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
}

/* Inset shadow (pressed/sunken effect) */
.btn:active {
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Multiple shadows: depth + colour */
.button-primary {
  box-shadow:
    0 1px 3px rgba(0, 0, 0, 0.12),
    0 4px 16px rgba(0, 102, 204, 0.3);  /* coloured glow */
}

/* Elevation system (like Material Design) */
.elevation-1 { box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
.elevation-2 { box-shadow: 0 4px 8px rgba(0,0,0,0.14); }
.elevation-3 { box-shadow: 0 8px 24px rgba(0,0,0,0.16); }</code></pre>
</div>

<h3>text-shadow</h3>
<p>Text shadows follow the same syntax as box shadows but without the spread-radius. A subtle text shadow can make text readable against complex backgrounds. Stack multiple text shadows to create glow effects, embossed text, or outlined text. Text shadows are composited by the browser, not calculated per frame, so they're reasonably performant even with multiple shadows.</p>
<div class="code-block">
<pre><code>/* Subtle legibility shadow on hero text over images */
.hero-title {
  color: white;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

/* Glow effect */
.neon-text {
  color: #00ffaa;
  text-shadow:
    0 0 10px #00ffaa,
    0 0 20px #00ffaa,
    0 0 40px #00ffaa;
}

/* Embossed text effect */
.embossed {
  color: #666;
  text-shadow: 1px 1px 0 white, -1px -1px 0 rgba(0,0,0,0.2);
}</code></pre>
</div>

<h3>CSS filter</h3>
<p>The <code>filter</code> property applies visual effects to elements (including images and entire sections). Available filters: <code>blur()</code>, <code>brightness()</code>, <code>contrast()</code>, <code>grayscale()</code>, <code>hue-rotate()</code>, <code>invert()</code>, <code>opacity()</code>, <code>saturate()</code>, <code>sepia()</code>, and <code>drop-shadow()</code>. Combine multiple filters in one declaration. These are rendered by the GPU when possible. <code>drop-shadow</code> follows the actual shape of an element (including transparent areas), unlike box-shadow which follows the rectangular box.</p>
<div class="code-block">
<pre><code>/* Hover to reveal colour from grayscale */
.team-photo {
  filter: grayscale(100%);
  transition: filter 400ms ease;
}

.team-photo:hover {
  filter: grayscale(0%);
}

/* Blur background content */
.loading-overlay {
  filter: blur(4px);
}

/* Drop shadow follows PNG transparency */
.logo-svg {
  filter: drop-shadow(2px 4px 8px rgba(0, 0, 0, 0.3));
}

/* Combine filters */
.vintage-photo {
  filter: sepia(60%) contrast(1.1) brightness(1.05);
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use a consistent shadow scale (3-4 levels of elevation) rather than inventing new shadows for every element. Light shadows (1-2px blur) for subtle separation; larger shadows (8-24px) for elevated overlays and modals. Avoid excessive shadows — if everything has a shadow, nothing stands out. Use filter: grayscale for hover effects on images that draw the eye naturally.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Solid-colour box shadows:</strong> Black box shadows (<code>box-shadow: 0 4px 8px black</code>) look harsh and artificial. Use semi-transparent colours (<code>rgba(0,0,0,0.15)</code>) for natural-looking shadows.</li>
    <li><strong>Too many elements with large shadows:</strong> Large, dark shadows on many elements creates a cluttered, heavy interface. Reserve high-elevation shadows for modals, dropdowns, and important interactive elements.</li>
    <li><strong>Filter and box-shadow on the same element:</strong> Adding filter to an element creates a new stacking context. This can cause box-shadow or z-index to behave unexpectedly. Test carefully when combining these properties.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>box-shadow accepts x/y offset, blur, spread, and colour; multiple shadows can be stacked for layered depth effects.</li>
    <li>CSS filter applies visual effects (grayscale, blur, brightness) to elements; drop-shadow follows element shape unlike box-shadow.</li>
    <li>Use semi-transparent shadow colours and build a consistent elevation scale (3-4 levels) rather than arbitrary shadows on each element.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Filters and Blend Modes', <<<'HTML'
<h2>Filters and Blend Modes</h2>

<p>CSS filter and blend mode properties bring Photoshop-like effects directly to the browser, enabling sophisticated image treatments and colour compositing without any image editing software. These properties are used by designers to create unique visual identities, photo galleries with consistent colour treatments, and compelling visual effects that set a site apart.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Putting cellophane sheets over photographs. A blue sheet over a photo makes it cool-toned. A red sheet creates a warm vintage look. Putting two photos on top of each other with a transparent layer produces blended effects. CSS blend modes are exactly this — mathematical ways of combining a layer's colours with what's behind it.</p>
</div>

<h3>backdrop-filter</h3>
<p><code>backdrop-filter</code> applies filter effects to the area behind an element — the content the element sits on top of. This creates the popular "frosted glass" effect used in macOS and iOS interfaces. Unlike <code>filter</code> which affects the element itself, <code>backdrop-filter</code> only affects what's visible through the element (so the element must be at least partially transparent). Safari requires the <code>-webkit-backdrop-filter</code> prefix.</p>
<div class="code-block">
<pre><code>/* Frosted glass navigation */
.navbar {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px) saturate(180%);
  -webkit-backdrop-filter: blur(12px) saturate(180%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
}

/* Glassmorphism card */
.glass-card {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}</code></pre>
</div>

<h3>mix-blend-mode</h3>
<p><code>mix-blend-mode</code> defines how an element's content blends with the background behind it. It's applied to the element itself and affects how its pixels combine with what's behind. Common modes: <code>multiply</code> (dark areas blend, white becomes transparent), <code>screen</code> (light areas blend, black becomes transparent), <code>overlay</code> (increases contrast), <code>difference</code> (creates inverted colour effects). These are identical to Photoshop/Illustrator layer blending modes.</p>
<div class="code-block">
<pre><code>/* Text that merges with background image */
.hero-title {
  color: white;
  mix-blend-mode: overlay;
  /* Text takes on some of the image's colours */
}

/* Multiply: dark logo colour over any background */
.logo {
  mix-blend-mode: multiply;
  /* White areas of logo become transparent, dark areas show through */
}

/* Screen: light textures over images */
.texture-overlay {
  mix-blend-mode: screen;
  opacity: 0.7;
}

/* Duotone effect: two-colour photo treatment */
.duotone-img {
  position: relative;
}
.duotone-img::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(to right, #ff6600, #6633cc);
  mix-blend-mode: color;  /* applies gradient as a tinted colour effect */
}</code></pre>
</div>

<h3>background-blend-mode</h3>
<p><code>background-blend-mode</code> blends multiple background layers of the same element with each other. When an element has both a background-image and background-color, background-blend-mode controls how they combine. This creates sophisticated image treatments — tinting photos, creating texture effects — all in CSS, all without opening an image editor.</p>
<div class="code-block">
<pre><code>/* Tint a photo with a brand colour */
.tinted-hero {
  background-image: url('hero.jpg');
  background-color: #0066cc;  /* brand blue */
  background-blend-mode: multiply;
  /* Result: blue-tinted photo */
}

/* Create texture overlay */
.textured {
  background-image:
    url('noise-texture.png'),
    url('content-image.jpg');
  background-blend-mode: overlay;
}

/* Duotone with two gradients */
.duotone {
  background-image:
    linear-gradient(to right, #ff6600, transparent),
    url('photo.jpg');
  background-blend-mode: color;
  background-size: cover;
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use backdrop-filter for navigation bars and modal overlays on image-heavy sites. Use mix-blend-mode for logo treatments, text-over-image effects, and creative photography treatments. Always test blend modes in multiple browsers — support varies, and complex blends can impact performance. Provide a fallback (a solid or semi-transparent background) for non-supporting browsers using @supports.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>backdrop-filter without background transparency:</strong> backdrop-filter only shows through transparent areas. If the element has a solid background-color, the blur effect is invisible. Always use a semi-transparent background with backdrop-filter.</li>
    <li><strong>Overusing blend modes:</strong> Complex blend modes with many layers can be GPU-intensive. Apply them to static elements, not things that animate, and test performance on low-end devices.</li>
    <li><strong>No fallback for unsupported blend modes:</strong> Not all blend modes render identically across browsers. Use @supports to provide a fallback for browsers that don't support specific blend modes.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>backdrop-filter blurs/affects the content behind a semi-transparent element, creating frosted glass effects.</li>
    <li>mix-blend-mode blends an element with what's behind it; background-blend-mode blends an element's own background layers with each other.</li>
    <li>Blend modes replicate Photoshop blending in CSS — multiply, screen, and overlay are the most useful for photography effects.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// CSS COURSE — Module 8
// =====================================================================

updateLesson($pdo, 'CSS Variables', <<<'HTML'
<h2>CSS Variables</h2>

<p>CSS custom properties (commonly called CSS variables) let you store values in named properties that can be reused throughout your stylesheet. They differ from preprocessor variables (like Sass) in a fundamental way: they exist at runtime, in the browser, can be modified with JavaScript, and cascade like any other CSS property. This makes them far more powerful than compile-time variables for building dynamic, maintainable design systems.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A shared spreadsheet that multiple departments reference. When the brand colour changes, you update one cell in the spreadsheet and every report that references that cell updates automatically. CSS custom properties are that shared cell — define your brand colour once, and every element using that variable updates when you change it.</p>
</div>

<h3>Defining and Using Variables</h3>
<p>Custom properties are defined with a double-dash prefix (<code>--property-name</code>) and used with the <code>var()</code> function. Define global variables in <code>:root</code> (the document root, equivalent to the html element) so they're accessible everywhere. You can provide a fallback value as the second argument to var() in case the variable isn't defined. Variables can hold any CSS value: colours, lengths, numbers, strings, even complex values like shadows.</p>
<div class="code-block">
<pre><code>:root {
  /* Design tokens */
  --color-primary:    #0066cc;
  --color-secondary:  #ff6600;
  --color-text:       #1a1a1a;
  --color-muted:      #6b7280;
  --color-bg:         #ffffff;
  --color-bg-alt:     #f9fafb;
  --color-border:     #e5e7eb;

  --font-sans: 'Inter', -apple-system, sans-serif;
  --font-mono: 'Fira Code', monospace;

  --radius-sm:  4px;
  --radius-md:  8px;
  --radius-lg:  16px;
  --radius-full: 9999px;

  --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
  --shadow-md: 0 4px 12px rgba(0,0,0,0.12);
  --shadow-lg: 0 8px 30px rgba(0,0,0,0.14);
}

/* Using variables */
body {
  font-family: var(--font-sans);
  color: var(--color-text);
  background-color: var(--color-bg);
}

.btn-primary {
  background-color: var(--color-primary);
  color: white;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
}

/* Fallback: if --border-color isn't defined, use #ccc */
.card {
  border: 1px solid var(--border-color, #ccc);
}</code></pre>
</div>

<h3>Scoped Variables and Overrides</h3>
<p>CSS variables cascade and can be overridden in nested scopes. A variable defined on a specific component overrides the global value for that component and all its children. This makes CSS variables ideal for theming component variants and building dark mode. A component can "locally" change a value without needing to rewrite all its children's styles.</p>
<div class="code-block">
<pre><code>/* Component scope: override variables for a specific component */
.card-danger {
  --color-primary: #dc3545;   /* override just for this component */
  border-color: var(--color-primary);
}

/* Dark mode using a scoped variable override */
[data-theme="dark"] {
  --color-bg:     #1a1a2e;
  --color-text:   #e2e8f0;
  --color-border: #334155;
}

/* All components that use --color-bg and --color-text automatically adapt */</code></pre>
</div>

<h3>Variables with JavaScript</h3>
<p>CSS custom properties are accessible and modifiable at runtime via JavaScript, unlike Sass/LESS variables which are compiled away. This enables dynamic theming, user customisation (colour pickers that update the theme), and reactive design adjustments. You can read values with <code>getComputedStyle()</code> and set them with <code>style.setProperty()</code>.</p>
<div class="code-block">
<pre><code>// Read a CSS variable
const root = document.documentElement;
const primaryColor = getComputedStyle(root).getPropertyValue('--color-primary').trim();
console.log(primaryColor); // "#0066cc"

// Set a CSS variable (changes reflect immediately throughout the page)
root.style.setProperty('--color-primary', '#e91e63');

// Theme switcher
document.getElementById('themeToggle').addEventListener('click', () => {
  document.documentElement.setAttribute('data-theme',
    document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'
  );
});

// Reactive: update CSS variable from a range input
document.getElementById('fontSizeSlider').addEventListener('input', (e) => {
  root.style.setProperty('--base-font-size', e.target.value + 'px');
});</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use CSS variables for your entire design system: colours, spacing, typography, shadows, and border radii. Define them all in :root and never hardcode values directly in component styles. This makes global changes (brand refresh, dark mode, accessibility adjustments) a matter of changing variable values, not hunting through hundreds of CSS rules.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Forgetting the var() function:</strong> CSS variables can't be used directly — you must always wrap them: <code>color: var(--color-primary)</code>, not <code>color: --color-primary</code>.</li>
    <li><strong>Using variables for non-CSS values in calc():</strong> You can use variables inside <code>calc()</code>, but the variable must hold a valid unit value: <code>calc(var(--spacing) * 2)</code> works if --spacing is "16px". A bare number like "16" won't work in calc with other units.</li>
    <li><strong>Missing IE11 support (if required):</strong> CSS variables are not supported in Internet Explorer 11. If you must support IE11, use a CSS preprocessor as an alternative or provide fallback values.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>CSS custom properties use <code>--name: value</code> syntax and are accessed with <code>var(--name)</code>; define global tokens in :root.</li>
    <li>Unlike Sass variables, CSS variables cascade and can be scoped to components, and modified at runtime via JavaScript.</li>
    <li>Use variables for your entire design system (colours, spacing, shadows) to enable global changes with a single edit.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'CSS Methodologies (BEM)', <<<'HTML'
<h2>CSS Methodologies (BEM)</h2>

<p>As CSS projects grow, they become harder to maintain without a system. CSS is global by default — any rule can accidentally affect any element. Methodologies are naming and organisational conventions that prevent this chaos. BEM (Block, Element, Modifier) is the most widely adopted CSS naming convention, used by companies like Google, Yandex, and thousands of open source projects. Understanding BEM means you can read and contribute to any codebase that uses it.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Naming files on your computer. "document.docx" doesn't tell you much. "2025-03-budget-final.docx" is immediately clear — type, date, topic, status. BEM class names work the same way: <code>.card__title--featured</code> tells you immediately it's the title element inside a card component, in its featured variant.</p>
</div>

<h3>BEM Concepts</h3>
<p>BEM divides your UI into three types of entities. A <strong>Block</strong> is a standalone component that makes sense on its own — <code>.card</code>, <code>.navbar</code>, <code>.button</code>. An <strong>Element</strong> is a part of a block that has no standalone meaning — <code>.card__title</code>, <code>.card__image</code>, <code>.navbar__link</code>. A <strong>Modifier</strong> changes the appearance or state of a block or element — <code>.card--featured</code>, <code>.button--large</code>, <code>.navbar__link--active</code>. The separators are double underscore (block__element) and double dash (block--modifier).</p>
<div class="code-block">
<pre><code>&lt;!-- BEM HTML structure --&gt;
&lt;article class="card card--featured"&gt;
  &lt;img class="card__image" src="..." alt="..."&gt;
  &lt;div class="card__body"&gt;
    &lt;h2 class="card__title"&gt;Course Title&lt;/h2&gt;
    &lt;p class="card__description"&gt;Description text...&lt;/p&gt;
    &lt;a class="card__link" href="/course"&gt;Start Learning&lt;/a&gt;
  &lt;/div&gt;
  &lt;span class="card__badge card__badge--new"&gt;New&lt;/span&gt;
&lt;/article&gt;

/* BEM CSS */
.card { border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
.card--featured { border-color: #0066cc; box-shadow: 0 4px 16px rgba(0,102,204,0.2); }
.card__image { width: 100%; height: 200px; object-fit: cover; }
.card__body { padding: 1.5rem; }
.card__title { font-size: 1.25rem; margin-bottom: 0.5rem; }
.card__badge { background: #ff6600; color: white; padding: 4px 12px; }
.card__badge--new { background: #28a745; }</code></pre>
</div>

<h3>Why BEM Works</h3>
<p>The value of BEM isn't just naming — it's what the naming enforces architecturally. BEM discourages deep nesting in CSS (selectors like <code>.page .content .sidebar .widget .title</code>) which makes styles brittle and hard to reuse. Every block is a self-contained module. Every class name tells you immediately whether it's a component, a part of a component, or a variant. You can drop a BEM block anywhere in your page and it styles correctly regardless of where it appears.</p>
<div class="code-block">
<pre><code>/* ❌ Deep nesting: brittle, hard to reuse */
.page .main .card .card-title a { color: blue; }

/* ✅ BEM: flat, explicit, reusable */
.card__title-link { color: blue; }

/* Modifiers handle state and variants */
.btn { padding: 10px 20px; border-radius: 4px; }
.btn--primary   { background: #0066cc; color: white; }
.btn--secondary { background: #6c757d; color: white; }
.btn--large     { padding: 14px 28px; font-size: 1.1rem; }
.btn--small     { padding: 6px 12px; font-size: 0.875rem; }
.btn--disabled  { opacity: 0.5; cursor: not-allowed; }</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use BEM (or a similar methodology) on any project with more than a few CSS files or multiple developers. The convention pays dividends as projects grow. On small personal projects, even just following the principle of flat selectors and meaningful class names captures most of BEM's benefits without strict adherence to the naming format.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Overly long BEM chains:</strong> BEM elements should only have one level — <code>.card__button</code> is correct; <code>.card__body__footer__button</code> is not. If you need deeper nesting, the sub-component is probably a new block.</li>
    <li><strong>Using BEM and descendant selectors together:</strong> Mixing BEM class names with descendant selectors defeats the purpose. If you're using BEM, style <code>.card__title</code> directly, not <code>.card .card__title</code>.</li>
    <li><strong>Applying modifiers to children via the parent:</strong> In BEM, modifiers are applied to the specific element that changes, not cascaded down. <code>.card--featured .card__title</code> creates coupling; <code>.card__title--featured</code> is more modular.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>BEM organises CSS into Blocks (components), Elements (component parts), and Modifiers (variants/states) using double underscore and double dash separators.</li>
    <li>BEM enforces flat selectors and self-contained components, making styles predictable and reusable across a project.</li>
    <li>Modifier classes change appearance or state; elements are named relative to their block, never more than one level deep.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'CSS Architecture Patterns', <<<'HTML'
<h2>CSS Architecture Patterns</h2>

<p>CSS architecture is how you organise your CSS across files and folders. A poorly organised stylesheet starts manageable but becomes a source of bugs, duplication, and fear (the fear of changing anything because it might break something else). Good architecture creates a codebase where you can confidently make changes, understand where to find things, and onboard new developers without a lengthy introduction.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A library's filing system. Small libraries can get by with books on shelves. Large libraries need a cataloguing system — genres, authors, subjects. The Dewey Decimal System doesn't change what the books contain, but it makes any book findable in seconds. CSS architecture is your cataloguing system for styles.</p>
</div>

<h3>The 7-1 Pattern</h3>
<p>The 7-1 pattern organises CSS into 7 folders and 1 main file that imports everything. The folders are: <code>base/</code> (resets, typography, variables), <code>components/</code> (buttons, cards, forms), <code>layout/</code> (grid, navbar, footer), <code>pages/</code> (page-specific styles), <code>themes/</code> (dark mode, brand themes), <code>abstracts/</code> (variables, mixins, functions), and <code>vendors/</code> (third-party CSS). This structure is widely used with Sass but also applicable to plain CSS using @import or CSS layers.</p>
<div class="code-block">
<pre><code>/* Folder structure */
css/
├── abstracts/
│   ├── _variables.css     /* CSS custom properties */
│   └── _mixins.css        /* reusable patterns */
├── base/
│   ├── _reset.css         /* CSS reset */
│   └── _typography.css    /* base font styles */
├── components/
│   ├── _buttons.css
│   ├── _cards.css
│   └── _forms.css
├── layout/
│   ├── _navbar.css
│   ├── _grid.css
│   └── _footer.css
└── main.css               /* imports everything */

/* main.css */
@import 'abstracts/variables';
@import 'base/reset';
@import 'base/typography';
@import 'components/buttons';
@import 'components/cards';</code></pre>
</div>

<h3>CSS Layers (@layer)</h3>
<p>CSS Cascade Layers (<code>@layer</code>) are a modern mechanism for organising and controlling the cascade order of CSS rules. Rules in a lower-priority layer are overridden by rules in a higher-priority layer, regardless of specificity. This solves a major pain point: overriding third-party library styles without needing !important or high-specificity hacks. Declare your layer order at the top of your CSS, then assign rules to layers.</p>
<div class="code-block">
<pre><code>/* Declare layer order (earlier = lower priority) */
@layer reset, base, components, utilities;

@layer reset {
  *, *::before, *::after { box-sizing: border-box; }
  body { margin: 0; }
}

@layer base {
  :root { --color-primary: #0066cc; }
  body { font-family: var(--font-sans); }
}

@layer components {
  .btn { padding: 10px 20px; border-radius: 4px; }
  .btn-primary { background: var(--color-primary); }
}

@layer utilities {
  /* Utility classes override components, lower specificity */
  .mt-0 { margin-top: 0 !important; }
  .hidden { display: none; }
}</code></pre>
</div>

<h3>Utility-First CSS</h3>
<p>An alternative to component-based CSS, utility-first approaches (like Tailwind CSS) provide low-level utility classes that do exactly one thing — <code>.flex</code>, <code>.text-center</code>, <code>.bg-blue-500</code>. You build UIs by composing utilities in HTML rather than writing custom CSS for each component. This eliminates naming things, prevents CSS bloat, and makes styles predictable. The trade-off: HTML becomes verbose with many classes. Many teams use a hybrid approach: utilities for layout and spacing, component classes for complex repeated UI patterns.</p>
<div class="code-block">
<pre><code>&lt;!-- Component-based approach --&gt;
&lt;button class="btn btn-primary btn-large"&gt;Submit&lt;/button&gt;

&lt;!-- Utility-first approach (Tailwind-style) --&gt;
&lt;button class="bg-blue-600 hover:bg-blue-700 text-white font-medium
               px-6 py-3 rounded-lg shadow-sm transition-colors"&gt;
  Submit
&lt;/button&gt;

/* In pure CSS, you can create your own utility classes */
.flex { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.gap-4 { gap: 1rem; }
.p-4 { padding: 1rem; }
.rounded { border-radius: var(--radius-md); }
.shadow { box-shadow: var(--shadow-md); }</code></pre>
</div>

<h3>When to Use This</h3>
<p>For projects with one stylesheet, none of this is necessary. For projects with 1000+ lines of CSS across a team, pick a structure and stick to it. The 7-1 pattern works well with Sass. CSS layers work well with plain CSS. Utility-first is ideal for rapid prototyping and small teams. Document your chosen convention in a README so new developers can follow it.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>No architecture for small projects:</strong> It's easy to dismiss architecture as "only for large projects," then watch a project outgrow its flat structure and become unmaintainable. Adopt a simple structure from the start — it costs nothing when the project is small.</li>
    <li><strong>Mixing architectural approaches:</strong> Combining BEM class names with utility classes with inline styles creates inconsistency. Pick an approach and be consistent throughout the project.</li>
    <li><strong>Component CSS that depends on page context:</strong> A component's CSS should not rely on where it appears on the page. Styles like <code>.homepage .card</code> couple the component to a specific location, making it non-reusable.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>The 7-1 pattern organises CSS into folders by purpose (base, components, layout, etc.) and imports everything in one main file.</li>
    <li>CSS Cascade Layers (@layer) give explicit control over the cascade order, solving specificity conflicts with third-party CSS.</li>
    <li>Utility-first CSS uses single-purpose classes for rapid composition; component-based CSS uses semantic class names for reusable UI patterns.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Modern CSS Features', <<<'HTML'
<h2>Modern CSS Features</h2>

<p>CSS has evolved dramatically in recent years. Features that once required JavaScript, Sass, or workarounds are now native CSS. Container queries, the :has() selector, logical properties, and native nesting have transformed what's possible with plain CSS. Staying current with modern CSS means less JavaScript, simpler markup, and more maintainable stylesheets.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A new model of a car replacing the previous generation. The fundamentals (wheels, engine, steering) are the same, but new features (collision detection, adaptive cruise control) eliminate the need for manual workarounds you used to perform yourself. Modern CSS eliminates the manual workarounds web developers have used for years.</p>
</div>

<h3>Container Queries</h3>
<p>Container queries are one of the most impactful CSS features in a decade. Media queries respond to the viewport width; container queries respond to a container element's width. This means a card component can rearrange its layout based on how wide its parent container is, not the viewport. This makes truly reusable components possible — a card looks great at 300px wide in a sidebar and at 600px wide in a main column, without any code duplication or page-level media queries.</p>
<div class="code-block">
<pre><code>/* Define a container */
.card-container {
  container-type: inline-size;  /* enable container queries */
  container-name: card;          /* optional name */
}

/* Component changes based on its container width */
.card {
  display: flex;
  flex-direction: column;
}

@container card (min-width: 400px) {
  .card {
    flex-direction: row;  /* side-by-side when container is wide */
  }

  .card__image {
    width: 150px;
    flex-shrink: 0;
  }
}</code></pre>
</div>

<h3>The :has() Selector</h3>
<p>The <code>:has()</code> pseudo-class (the "parent selector" developers have wanted for decades) matches an element based on whether its descendants match a selector. "Select a card that has an image." "Select a form that has an invalid input." "Select a list item that has a nested list." Previously these required JavaScript to add parent classes; now it's pure CSS. It's a game-changer for conditional styling based on content.</p>
<div class="code-block">
<pre><code>/* Card with an image — different layout */
.card:has(img) {
  display: grid;
  grid-template-columns: auto 1fr;
}

/* Form with invalid input — show error state on the whole form */
form:has(input:invalid) .submit-btn {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Navigation item with a nested submenu */
.nav-item:has(.submenu) > a::after {
  content: " ▾";
}

/* Section without a heading — add visual warning in dev */
section:not(:has(h2, h3)) {
  outline: 2px dashed orange;
}</code></pre>
</div>

<h3>CSS Nesting</h3>
<p>Native CSS nesting (now available without Sass) allows writing nested rules inside parent rules. The <code>&amp;</code> refers to the parent selector. This reduces repetition, improves readability, and keeps related styles co-located. Previously you had to write <code>.card</code>, <code>.card__title</code>, <code>.card:hover</code> as separate rules. With nesting, they're all inside a single <code>.card</code> block.</p>
<div class="code-block">
<pre><code>/* CSS Nesting (native, no Sass required) */
.card {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 1.5rem;

  /* Nested: .card__title */
  & .card__title {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
  }

  /* Nested pseudo-class: .card:hover */
  &:hover {
    box-shadow: var(--shadow-md);
  }

  /* Nested media query */
  @media (min-width: 768px) {
    display: grid;
    grid-template-columns: auto 1fr;
  }
}</code></pre>
</div>

<h3>Logical Properties</h3>
<p>Logical properties use start/end/block/inline instead of top/bottom/left/right. This makes CSS automatically adapt to right-to-left languages (Arabic, Hebrew) and vertical writing modes (some East Asian languages) without writing separate styles. <code>margin-inline-start</code> is the margin on the start side of the writing direction — left in English, right in Arabic. Building internationalised sites with logical properties from the start is far easier than retrofitting them later.</p>
<div class="code-block">
<pre><code>/* Physical (direction-specific) */
.element {
  margin-left: 1rem;
  padding-right: 2rem;
  border-top: 1px solid;
}

/* Logical (adapts to writing direction) */
.element {
  margin-inline-start: 1rem;   /* left in LTR, right in RTL */
  padding-inline-end: 2rem;    /* right in LTR, left in RTL */
  border-block-start: 1px solid; /* top in horizontal, left in vertical */
}

/* Useful logical shorthand properties */
.box {
  margin-inline: auto;         /* margin-left + margin-right: auto */
  padding-block: 1rem;         /* padding-top + padding-bottom: 1rem */
  inset-inline: 0;             /* left: 0; right: 0 */
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Container queries are production-ready and have broad browser support (all modern browsers). Use them for any component that appears in varying container widths. :has() is similarly well-supported now. Check caniuse.com for the specific browsers you need to support before using very new features.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Missing container-type on the parent:</strong> Container queries only work if the parent element has <code>container-type: inline-size</code> (or <code>size</code>). Without it, @container rules are ignored.</li>
    <li><strong>Using :has() for complex nesting without testing:</strong> Deeply nested :has() selectors with multiple arguments can be slow. Test performance on large documents before using complex :has() selectors on frequently-rendered elements.</li>
    <li><strong>Mixing logical and physical properties on the same element:</strong> Combining margin-left and margin-inline-start on the same element creates confusing conflicts. Pick one approach and be consistent.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Container queries (@container) let components respond to their container's width, enabling truly reusable responsive components.</li>
    <li>:has() selects parents/ancestors based on their descendants — the long-awaited "parent selector" now in all modern browsers.</li>
    <li>CSS nesting and logical properties reduce repetition and make stylesheets more maintainable and internationalisation-ready.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'CSS Best Practices', <<<'HTML'
<h2>CSS Best Practices</h2>

<p>Writing CSS that works today is easy. Writing CSS that's still maintainable six months later, by you or a teammate, requires discipline and proven patterns. Best practices in CSS aren't rules for rules' sake — each one solves a real problem that developers encounter as codebases grow. Adopting them from the start saves significant refactoring time later.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Cooking in a shared kitchen. You could leave your dishes unwashed and ingredients scattered — it's faster in the moment. But when ten people share the kitchen, good habits (clean as you go, label your food, replace what you use) make the shared space functional. CSS best practices are the shared kitchen rules for your codebase.</p>
</div>

<h3>Start with a CSS Reset</h3>
<p>Different browsers have different default styles. Without a reset, your site looks slightly different in every browser. A minimal modern reset removes these inconsistencies and establishes a consistent baseline. The essential reset: box-sizing: border-box everywhere, remove default margins on common elements, and make images and media fluid. This 20-line reset prevents dozens of cross-browser issues.</p>
<div class="code-block">
<pre><code>/* Modern CSS Reset */
*, *::before, *::after { box-sizing: border-box; }
body, h1, h2, h3, h4, p, ul, ol, figure, blockquote { margin: 0; padding: 0; }
body { min-height: 100vh; line-height: 1.5; }
img, picture, video, canvas, svg { display: block; max-width: 100%; }
input, button, textarea, select { font: inherit; }
h1, h2, h3, h4 { line-height: 1.2; }
ul[class], ol[class] { list-style: none; }</code></pre>
</div>

<h3>Organise Properties Consistently</h3>
<p>Consistent property ordering within rules makes them scannable and predictable. A common convention groups properties by concern: positioning and layout first, box model second, typography third, visual last. Some teams use alphabetical order (easier to enforce with linters). The specific convention matters less than consistency — pick one and use a linter (stylelint) to enforce it automatically.</p>
<div class="code-block">
<pre><code>.card {
  /* 1. Positioning */
  position: relative;
  z-index: 1;

  /* 2. Display & Box Model */
  display: flex;
  flex-direction: column;
  width: 300px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  overflow: hidden;

  /* 3. Typography */
  font-family: var(--font-sans);
  font-size: 1rem;
  color: var(--color-text);

  /* 4. Visual */
  background-color: white;
  box-shadow: var(--shadow-sm);

  /* 5. Transitions */
  transition: box-shadow 200ms ease, transform 200ms ease;
}</code></pre>
</div>

<h3>Performance and Maintainability Rules</h3>
<p>Avoid deeply nested selectors (more than 3 levels deep) — they're hard to read and override. Avoid using <code>!important</code> except in utility classes. Use CSS custom properties for all design tokens. Write mobile-first responsive styles. Use semantic class names that describe purpose, not appearance (<code>.error-message</code> not <code>.red-text</code>). Comment complex logic or hacks. Remove dead code regularly — unused CSS slows page load.</p>
<div class="code-block">
<pre><code>/* ❌ Avoid */
.page .content .cards .card .card-header .title { color: blue; }  /* too nested */
.big-blue-text { color: blue; font-size: 2rem; }  /* describes appearance */
.btn { background: #0066cc !important; }  /* unnecessary !important */

/* ✅ Prefer */
.card__title { color: var(--color-primary); }
.section-heading { color: var(--color-primary); font-size: 2rem; }

/* Document why you're doing something non-obvious */
.modal {
  /* z-index: 1000 ensures modal appears above all page content */
  /* Update this if you add components with higher z-index */
  z-index: 1000;
}

/* Use @supports for progressive enhancement */
@supports (display: grid) {
  .layout { display: grid; }
}
.layout { display: flex; }  /* fallback */</code></pre>
</div>

<h3>When to Use This</h3>
<p>Apply these practices from the very beginning of a project. They're most valuable when followed consistently — one developer using BEM on half a project and utility classes on the other half creates more confusion than no methodology at all. Add stylelint to your project to automate enforcement of formatting and naming rules.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Writing CSS without a reset:</strong> Default browser styles cause cross-browser inconsistencies from the start. Always begin with a reset or normalise stylesheet.</li>
    <li><strong>Naming classes after appearance:</strong> Names like <code>.red-button</code> or <code>.large-font</code> become wrong when the design changes. Name after purpose: <code>.btn-danger</code>, <code>.section-title</code>.</li>
    <li><strong>No linting or formatting:</strong> Without a linter, CSS conventions decay over time as the team grows. Set up stylelint and Prettier CSS formatting in your project's tooling from the start.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Start every project with a CSS reset to eliminate cross-browser inconsistencies before writing any custom styles.</li>
    <li>Name classes by purpose (not appearance), keep selectors shallow (max 3 levels), and use CSS variables for all design tokens.</li>
    <li>Enforce conventions automatically with stylelint rather than relying on manual review — consistency requires tooling.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// JAVASCRIPT COURSE — Module 9
// =====================================================================

updateLesson($pdo, 'Variables and Data Types', <<<'HTML'
<h2>Variables and Data Types</h2>

<p>Variables are named containers for storing data. Data types are the categories of values that JavaScript understands — numbers, text, booleans, and more. These are the atoms of programming: every program you'll ever write is ultimately about storing, transforming, and displaying data. Understanding how JavaScript categorises and stores data is where every JavaScript journey begins.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Labelled boxes in a storage room. You can put different things in different boxes and retrieve them later by the label. A box labelled "userAge" holds a number. "userName" holds text. The type of content in the box matters — you can do maths with numbers but not with text names.</p>
</div>

<h3>let, const, and var</h3>
<p>Modern JavaScript has three ways to declare variables. <code>const</code> creates a constant — a variable whose binding can't be reassigned after creation (the preferred choice for most values). <code>let</code> creates a block-scoped variable that can be reassigned. <code>var</code> is the old way — function-scoped with confusing hoisting behaviour. The modern rule: use <code>const</code> by default; use <code>let</code> only when you know you'll reassign the value; avoid <code>var</code>.</p>
<div class="code-block">
<pre><code>// const: value can't be reassigned — use by default
const userName = 'Alex';
const maxAttempts = 3;
// userName = 'Sam';  // ❌ TypeError: Assignment to constant variable

// let: value can be reassigned — use when you need to change it
let score = 0;
let isLoggedIn = false;

score = 10;      // ✅ allowed
isLoggedIn = true; // ✅ allowed

// var: avoid in modern code
var oldStyle = 'avoid this'; // function-scoped, hoisted — confusing</code></pre>
</div>

<h3>Primitive Data Types</h3>
<p>JavaScript has 7 primitive types. <strong>Number</strong> represents all numeric values (integers and decimals). <strong>String</strong> represents text — use single quotes, double quotes, or template literals (backticks). <strong>Boolean</strong> is true or false. <strong>undefined</strong> means a variable has been declared but not assigned. <strong>null</strong> is an intentional absence of a value. <strong>BigInt</strong> handles integers too large for Number. <strong>Symbol</strong> creates unique identifiers. The <code>typeof</code> operator tells you a value's type.</p>
<div class="code-block">
<pre><code>// Number
const age = 25;
const price = 9.99;
const negativeTemp = -15;

// String
const firstName = 'Alex';
const greeting = "Hello, world!";
const message = `Welcome, ${firstName}!`;  // template literal with interpolation

// Boolean
const isActive = true;
const hasError = false;

// Undefined and null
let userAddress;                  // undefined — declared, not assigned
const emptyValue = null;          // null — intentionally empty

// Check types
console.log(typeof age);          // "number"
console.log(typeof firstName);    // "string"
console.log(typeof isActive);     // "boolean"
console.log(typeof userAddress);  // "undefined"
console.log(typeof null);         // "object" — a famous JS quirk!</code></pre>
</div>

<h3>Type Conversion</h3>
<p>JavaScript sometimes converts types automatically (implicit coercion) — a source of bugs. Understanding this prevents surprises. Explicit conversion is always clearer: use <code>Number()</code>, <code>String()</code>, and <code>Boolean()</code> to convert deliberately. Know your "falsy" values: <code>false</code>, <code>0</code>, <code>""</code>, <code>null</code>, <code>undefined</code>, and <code>NaN</code> are all falsy — everything else is truthy.</p>
<div class="code-block">
<pre><code>// Explicit type conversion
const numStr = "42";
const num = Number(numStr);    // 42 (number)
const str = String(123);       // "123"
const bool = Boolean(0);       // false

// Implicit coercion (avoid relying on this)
console.log("5" + 3);   // "53" — + triggers string concatenation
console.log("5" - 3);   // 2   — - triggers numeric conversion
console.log("5" * "3"); // 15  — both converted to numbers

// Falsy values
if (0)         console.log("never prints");
if ("")        console.log("never prints");
if (null)      console.log("never prints");
if (undefined) console.log("never prints");
if ("hello")   console.log("prints! — non-empty string is truthy");</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use const for everything by default, switch to let only when you need to reassign. Use template literals for strings that include variables — they're more readable than concatenation. Use typeof to debug unexpected type issues. Never use var in new code.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Using == instead of ===:</strong> The loose equality operator <code>==</code> performs type coercion (<code>"0" == 0</code> is true). Always use strict equality <code>===</code> which checks both value and type without coercion.</li>
    <li><strong>Not initialising variables:</strong> Declaring a variable with let without assigning it creates undefined. If you try to use it in arithmetic, you get NaN (Not a Number), which propagates silently.</li>
    <li><strong>Expecting const to make objects immutable:</strong> <code>const</code> prevents reassignment of the variable name, not mutation of the object's contents. <code>const obj = {}; obj.name = 'Alex';</code> is perfectly valid.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use <code>const</code> by default; use <code>let</code> only when the value needs to change; never use <code>var</code>.</li>
    <li>JavaScript has 7 primitive types; use typeof to inspect values. Be aware of the 6 falsy values: false, 0, "", null, undefined, NaN.</li>
    <li>Always use strict equality (===) to avoid type coercion surprises; use explicit type conversion (Number(), String()) instead of relying on implicit coercion.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Operators and Expressions', <<<'HTML'
<h2>Operators and Expressions</h2>

<p>Operators are symbols that perform operations on values. An expression is any code that produces a value. Together, operators and expressions are how you perform calculations, compare values, combine logic, and transform data. Every non-trivial line of JavaScript involves at least one operator. Knowing what each operator does — and what it might do unexpectedly — is fundamental programming knowledge.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Mathematical notation. In maths, + adds, = means equals, > means greater than. JavaScript operators work the same way, with a few additions: logical operators (and, or, not) and assignment operators (assign AND do something). The grammar rules — which operations happen first — are called operator precedence.</p>
</div>

<h3>Arithmetic and Assignment Operators</h3>
<p>Arithmetic operators perform maths: +, -, *, / (division), % (remainder/modulo), ** (exponentiation). The modulo operator (%) returns the remainder after division — useful for checking if a number is even/odd or cycling through values. Assignment operators combine assignment with an operation: += adds to the current value, -= subtracts, *= multiplies. The increment (<code>++</code>) and decrement (<code>--</code>) operators add or subtract 1.</p>
<div class="code-block">
<pre><code>let x = 10;
let y = 3;

console.log(x + y);   // 13
console.log(x - y);   // 7
console.log(x * y);   // 30
console.log(x / y);   // 3.333...
console.log(x % y);   // 1  (remainder of 10 / 3)
console.log(x ** y);  // 1000 (10 to the power of 3)

// Assignment operators
x += 5;   // x = x + 5 = 15
x -= 3;   // x = x - 3 = 12
x *= 2;   // x = x * 2 = 24
x /= 4;   // x = x / 4 = 6

// Increment/decrement
let count = 0;
count++;   // count = 1
count--;   // count = 0

// Modulo for even/odd check
const isEven = (n) => n % 2 === 0;
console.log(isEven(4));   // true
console.log(isEven(7));   // false</code></pre>
</div>

<h3>Comparison Operators</h3>
<p>Comparison operators produce boolean values. <code>===</code> (strict equal) checks value and type. <code>!==</code> (strict not equal). <code>&gt;</code>, <code>&lt;</code>, <code>&gt;=</code>, <code>&lt;=</code> for numeric/string ordering. Always use strict equality (<code>===</code>) in JavaScript — loose equality (<code>==</code>) performs type coercion and produces surprising results (<code>0 == false</code> is true, <code>"" == false</code> is true). Strict equality never coerces types.</p>
<div class="code-block">
<pre><code>console.log(5 === 5);       // true  — same value, same type
console.log(5 === "5");     // false — different types
console.log(5 !== "5");     // true  — they are NOT strictly equal

console.log(10 > 5);        // true
console.log(3 < 3);         // false
console.log(3 <= 3);        // true

// String comparison (lexicographic/alphabetical)
console.log("apple" < "banana");  // true
console.log("b" > "a");           // true

// Loose equality gotchas — why we use ===
console.log(0 == false);    // true  (confusing!)
console.log("" == false);   // true  (confusing!)
console.log(0 === false);   // false (correct — different types)</code></pre>
</div>

<h3>Logical Operators</h3>
<p>Logical operators combine or invert boolean expressions. <code>&amp;&amp;</code> (AND) returns true only if both sides are truthy. <code>||</code> (OR) returns true if at least one side is truthy. <code>!</code> (NOT) inverts a boolean. In JavaScript, <code>&amp;&amp;</code> and <code>||</code> actually return one of their operands (not necessarily a boolean) — this enables short-circuit evaluation patterns used widely in React and modern JS. The nullish coalescing operator <code>??</code> returns the right side only if the left is null or undefined.</p>
<div class="code-block">
<pre><code>// Boolean logic
console.log(true && true);    // true
console.log(true && false);   // false
console.log(false || true);   // true
console.log(!true);           // false

// Short-circuit evaluation
const name = user && user.name;    // only access .name if user is truthy
const display = name || 'Guest';   // use 'Guest' if name is falsy

// Nullish coalescing (only null/undefined, not 0 or "")
const count = userCount ?? 0;      // 0 if userCount is null/undefined
// vs:
const count2 = userCount || 0;     // 0 also if userCount is 0 (falsy!)

// Logical assignment
user &&= getUpdatedUser();     // assign only if user is truthy
config ??= defaultConfig;      // assign only if config is null/undefined</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use strict equality (===) always. Use short-circuit evaluation for default values and optional chaining. Use the nullish coalescing operator (??) instead of || when you want to preserve falsy values like 0 and "" but still handle null/undefined.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Using = instead of === in conditions:</strong> A single = is assignment, not comparison. <code>if (x = 5)</code> assigns 5 to x and is always truthy. Use <code>===</code> for comparisons.</li>
    <li><strong>Using || for defaults when 0 is valid:</strong> <code>const qty = userQty || 1</code> replaces 0 (a valid quantity) with 1 because 0 is falsy. Use <code>const qty = userQty ?? 1</code> to only replace null/undefined.</li>
    <li><strong>Confusing && and || return values:</strong> <code>&&</code> returns the first falsy value or the last value; <code>||</code> returns the first truthy value or the last value. They don't always return booleans.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use === and !== for all comparisons to avoid type coercion surprises; == is almost never the right choice.</li>
    <li>Short-circuit evaluation with && and || allows concise conditional assignment; ?? handles only null/undefined (not all falsy values).</li>
    <li>The % modulo operator returns the remainder after division — useful for cycles, even/odd detection, and pagination.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Control Flow', <<<'HTML'
<h2>Control Flow</h2>

<p>Control flow determines the order in which code executes. Without control flow, programs would run every line once, top to bottom, every time. With control flow — if/else decisions, switch statements, and ternary expressions — your code can respond to different conditions and situations. Control flow is where your programs start making decisions and behaving intelligently.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Road signs and traffic lights. They control which way traffic flows based on conditions: red light stops it, green lets it through, a fork gives drivers a choice. If/else statements are forks in your code's road — based on a condition, execution goes one way or another.</p>
</div>

<h3>if, else if, and else</h3>
<p>The <code>if</code> statement evaluates a condition. If it's truthy, the code block runs. If not, JavaScript checks <code>else if</code> conditions in order, then runs the <code>else</code> block as a fallback. Use braces {} even for single-line blocks — it's safer and more readable. The condition can be any expression that evaluates to a truthy or falsy value — not just strict booleans.</p>
<div class="code-block">
<pre><code>const score = 78;

if (score >= 90) {
  console.log('Grade: A');
} else if (score >= 80) {
  console.log('Grade: B');
} else if (score >= 70) {
  console.log('Grade: C');
} else if (score >= 60) {
  console.log('Grade: D');
} else {
  console.log('Grade: F');
}
// Output: "Grade: C"

// Truthy/falsy conditions
const username = '';

if (username) {
  console.log(`Welcome, ${username}!`);
} else {
  console.log('Please enter your name.');  // runs — empty string is falsy
}</code></pre>
</div>

<h3>switch Statements</h3>
<p>Switch statements match an expression against multiple cases using strict equality. They're cleaner than long else-if chains when comparing a single value against many possible values. Each case should end with <code>break</code> to prevent "fall-through" (where execution continues into the next case). The <code>default</code> case runs when no other case matches — like an else.</p>
<div class="code-block">
<pre><code>const day = 'Monday';

switch (day) {
  case 'Monday':
  case 'Tuesday':
  case 'Wednesday':
  case 'Thursday':
  case 'Friday':
    console.log('Weekday');
    break;
  case 'Saturday':
  case 'Sunday':
    console.log('Weekend');
    break;
  default:
    console.log('Invalid day');
}
// Output: "Weekday"

// Without break: fall-through (usually a bug)
const x = 1;
switch(x) {
  case 1:
    console.log('one');
    // No break — falls through!
  case 2:
    console.log('two');  // This also prints!
    break;
}
// Output: "one" then "two"</code></pre>
</div>

<h3>The Ternary Operator</h3>
<p>The ternary operator is a concise one-line if/else for simple conditions. Syntax: <code>condition ? valueIfTrue : valueIfFalse</code>. It's an expression (produces a value), unlike if/else which is a statement. This makes ternaries useful in JSX, template literals, and anywhere you need an inline conditional value. Don't nest ternaries — they become unreadable. For complex conditions, use a regular if/else or a helper function.</p>
<div class="code-block">
<pre><code>// Ternary: condition ? true-value : false-value
const age = 20;
const status = age >= 18 ? 'adult' : 'minor';
console.log(status);  // "adult"

// Useful in template literals
const cartCount = 3;
const message = `You have ${cartCount} item${cartCount !== 1 ? 's' : ''} in your cart.`;
console.log(message);  // "You have 3 items in your cart."

// In HTML rendering (React-style)
const isLoggedIn = true;
const display = isLoggedIn ? 'Welcome back!' : 'Please log in.';

// ❌ Don't nest ternaries (unreadable)
const grade = score >= 90 ? 'A' : score >= 80 ? 'B' : score >= 70 ? 'C' : 'F';

// ✅ Better: clear if/else or a function
function getGrade(score) {
  if (score >= 90) return 'A';
  if (score >= 80) return 'B';
  if (score >= 70) return 'C';
  return 'F';
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use if/else for conditional logic with complex branches. Use switch for matching one value against many cases. Use ternary for simple one-liner conditional values. Avoid deeply nested if/else — "early returns" (returning from a function as soon as a condition fails) keep code flat and readable.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Missing break in switch cases:</strong> Without break, execution falls through to the next case. Always add break after each case unless fall-through is intentional (grouping multiple cases together).</li>
    <li><strong>Assignment in if conditions:</strong> <code>if (x = 5)</code> assigns 5 (always truthy). Use <code>if (x === 5)</code> for comparison. Some editors warn about this — enable linting.</li>
    <li><strong>Deeply nested if/else:</strong> More than 2-3 levels of nesting makes code hard to follow. Use early returns to flatten: <code>if (!valid) return; // rest of code assumes valid</code>.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>if/else executes code blocks based on conditions; switch matches a value against many cases (use break to prevent fall-through).</li>
    <li>The ternary operator (condition ? a : b) is a concise inline if/else for simple one-value conditions; don't nest them.</li>
    <li>Use early returns to flatten nested if/else; assume conditions pass after the return check to keep code readable.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Loops', <<<'HTML'
<h2>Loops</h2>

<p>Loops repeat a block of code multiple times. Instead of writing the same code ten times for ten items, you write it once and loop. They're one of the most fundamental programming concepts — almost any program that processes a list, iterates over data, or repeats an action uses loops. JavaScript provides several types, each suited to different situations.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A factory assembly line. Instead of building each car completely from scratch each time, the same set of steps is repeated for each car that comes down the line. Loops are your assembly line — the code runs once per "car" (data item), as many times as needed.</p>
</div>

<h3>for Loop</h3>
<p>The classic <code>for</code> loop has three parts: initialisation (where the counter starts), condition (keep looping while true), and update (what happens after each iteration). It's most useful when you know exactly how many times to loop, or need the index number during iteration. Loop variable naming: <code>i</code> is conventional for the outer loop, <code>j</code> for nested loops.</p>
<div class="code-block">
<pre><code>// Basic for loop: count from 1 to 5
for (let i = 1; i <= 5; i++) {
  console.log(`Iteration ${i}`);
}
// Prints: Iteration 1, 2, 3, 4, 5

// Loop through an array by index
const fruits = ['apple', 'banana', 'cherry'];
for (let i = 0; i < fruits.length; i++) {
  console.log(`${i}: ${fruits[i]}`);
}
// 0: apple, 1: banana, 2: cherry

// Counting down
for (let i = 10; i > 0; i--) {
  console.log(i);
}
// 10, 9, 8, ... 1</code></pre>
</div>

<h3>while and do...while</h3>
<p>A <code>while</code> loop runs as long as a condition is true. Use it when you don't know in advance how many iterations are needed. <code>do...while</code> always runs the body at least once, then checks the condition. This is useful when you need to execute code before deciding whether to continue (like prompting a user for input until they enter something valid).</p>
<div class="code-block">
<pre><code>// while: runs while condition is true
let count = 0;
while (count < 5) {
  console.log(`Count: ${count}`);
  count++;
}

// do...while: always runs at least once
let attempts = 0;
do {
  console.log('Attempting connection...');
  attempts++;
} while (attempts < 3 && !connected);

// Infinite loop trap — always ensure termination
let x = 1;
while (x <= 10) {
  console.log(x);
  x++;  // ← without this increment, loop runs forever!
}</code></pre>
</div>

<h3>for...of and Array Methods</h3>
<p><code>for...of</code> is the modern way to iterate over arrays, strings, and other iterables. It's cleaner than the index-based for loop when you don't need the index. Even more powerful are array methods that accept callback functions: <code>forEach</code> (like for...of), <code>map</code> (transforms each item into a new array), <code>filter</code> (returns items matching a condition), <code>find</code> (returns first matching item), <code>reduce</code> (accumulates a single value). These are the tools of modern JavaScript.</p>
<div class="code-block">
<pre><code>const scores = [85, 92, 78, 95, 88];

// for...of: simple iteration
for (const score of scores) {
  console.log(score);
}

// forEach: like for...of but with index available
scores.forEach((score, index) => {
  console.log(`Student ${index + 1}: ${score}`);
});

// map: transform each item into a new array
const grades = scores.map(score => {
  if (score >= 90) return 'A';
  if (score >= 80) return 'B';
  return 'C';
});
// ['B', 'A', 'C', 'A', 'B']

// filter: keep only items that pass the test
const passing = scores.filter(score => score >= 80);
// [85, 92, 95, 88]

// find: first item matching condition
const topScore = scores.find(score => score >= 95);
// 95

// reduce: accumulate to single value
const average = scores.reduce((sum, score) => sum + score, 0) / scores.length;
// 87.6</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use <code>for...of</code> or array methods (map, filter, forEach) for iterating over arrays — they're cleaner and more expressive than index-based for loops. Use a traditional for loop when you need precise control over start, end, or step. Use while for indeterminate iterations. Prefer map/filter/reduce over manual array building in loops.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Off-by-one errors:</strong> A loop from 0 to length runs correctly; from 0 to length + 1 causes an out-of-bounds access. Always double-check loop boundaries (<code>i &lt; length</code> vs <code>i &lt;= length</code>).</li>
    <li><strong>Forgetting to break infinite loops:</strong> A while loop that never updates its condition variable will freeze the browser. Always ensure your loop has a guaranteed path to termination.</li>
    <li><strong>Using for...in on arrays:</strong> <code>for...in</code> iterates over property keys, not values — it can include prototype properties and indices as strings. Use <code>for...of</code> or array methods for arrays.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use for...of for clean array/iterable iteration; use traditional for loops when you need index control.</li>
    <li>Array methods (map, filter, find, reduce, forEach) are the modern way to process arrays — prefer them over manual loops.</li>
    <li>While loops run while a condition is true; always ensure a path to termination to prevent infinite loops.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Functions', <<<'HTML'
<h2>Functions</h2>

<p>Functions are reusable blocks of code that perform a specific task. Instead of copying the same logic to five different places, you write it once as a function and call it wherever needed. When the logic needs to change, you change it in one place. Functions are the primary tool for organising and reusing code — mastering them is mastering JavaScript.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A recipe. You write the recipe once: "to make a cup of tea, boil water, steep tea bag for 3 minutes, add milk to taste." Anyone can follow that recipe whenever they want tea. A function is your recipe — written once, used many times, with inputs (how much milk?) and an output (the cup of tea).</p>
</div>

<h3>Function Declarations and Expressions</h3>
<p>There are multiple ways to define functions. <strong>Function declarations</strong> are hoisted — you can call them before they're defined in the code. <strong>Function expressions</strong> assign a function to a variable — they're not hoisted. <strong>Arrow functions</strong> (the modern syntax) are more concise and have different <code>this</code> binding behaviour. For most purposes, arrow functions are the preferred modern syntax, especially for callbacks.</p>
<div class="code-block">
<pre><code>// Function declaration — can be called before definition
function greet(name) {
  return `Hello, ${name}!`;
}
console.log(greet('Alex'));  // "Hello, Alex!"

// Function expression — not hoisted
const add = function(a, b) {
  return a + b;
};
console.log(add(3, 4));  // 7

// Arrow function — concise, preferred for callbacks
const multiply = (a, b) => a * b;          // implicit return for one expression
const square = n => n ** 2;                 // single param, no parentheses needed
const sayHi = () => console.log('Hi!');    // no params

const getUser = (id) => {
  // multi-line: needs explicit return
  const user = findUser(id);
  return user;
};</code></pre>
</div>

<h3>Parameters, Arguments, and Return Values</h3>
<p>Parameters are the named placeholders in the function definition. Arguments are the actual values passed when calling the function. Functions can have default parameters (values used when the argument is undefined or omitted). A function without a return statement returns undefined. Functions should generally do one thing and return a value — this makes them testable and composable.</p>
<div class="code-block">
<pre><code>// Default parameters
function createUser(name, role = 'student', isActive = true) {
  return { name, role, isActive };
}

console.log(createUser('Alex'));
// { name: 'Alex', role: 'student', isActive: true }

console.log(createUser('Sam', 'admin'));
// { name: 'Sam', role: 'admin', isActive: true }

// Rest parameters: collect extra arguments into an array
function sum(...numbers) {
  return numbers.reduce((total, n) => total + n, 0);
}
console.log(sum(1, 2, 3, 4, 5));  // 15

// Functions returning objects
function getStats(scores) {
  const total = scores.reduce((sum, s) => sum + s, 0);
  return {
    count: scores.length,
    total,
    average: total / scores.length,
    max: Math.max(...scores),
    min: Math.min(...scores)
  };
}</code></pre>
</div>

<h3>Scope and Closures</h3>
<p>Scope determines where variables are accessible. Variables declared inside a function are local — they exist only within that function. Variables declared outside are global. Closures are functions that remember the variables from the scope in which they were created, even after that outer function has returned. Closures are a powerful pattern for creating private state, factory functions, and memoisation.</p>
<div class="code-block">
<pre><code>// Local scope: x only exists inside the function
function example() {
  const x = 10;   // local to this function
  console.log(x); // works
}
// console.log(x); // ❌ ReferenceError — x doesn't exist here

// Closure: inner function remembers outer function's variables
function makeCounter(start = 0) {
  let count = start;  // this variable is "closed over"

  return {
    increment() { return ++count; },
    decrement() { return --count; },
    reset()     { count = start; return count; },
    value()     { return count; }
  };
}

const counter = makeCounter(10);
console.log(counter.increment()); // 11
console.log(counter.increment()); // 12
console.log(counter.value());     // 12
console.log(counter.reset());     // 10</code></pre>
</div>

<h3>When to Use This</h3>
<p>Write small, single-purpose functions rather than large functions that do many things. Name functions with verbs that describe their action (getUser, calculateTotal, validateEmail). Pure functions (same input always produces same output, no side effects) are easiest to test and reason about. Use arrow functions for callbacks; use declarations or expressions for named, reusable functions.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Forgetting to return a value:</strong> A function without a return statement returns undefined. If you're calling a function and getting undefined unexpectedly, check that you have a return statement.</li>
    <li><strong>Global variable pollution:</strong> Declaring variables without const/let inside functions makes them accidentally global, affecting the whole program. Always use const or let.</li>
    <li><strong>Thinking arrow functions and regular functions are identical:</strong> Arrow functions don't have their own <code>this</code>, <code>arguments</code> object, or <code>prototype</code>. This matters for object methods and event handlers where <code>this</code> is important.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Functions are reusable code blocks; use declarations for named utilities, arrow functions for callbacks and concise expressions.</li>
    <li>Default parameters, rest parameters (...args), and destructured parameters make functions flexible without manual checks.</li>
    <li>Closures let inner functions access outer variables even after the outer function returns — used for private state and factory patterns.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// JAVASCRIPT COURSE — Module 10
// =====================================================================

updateLesson($pdo, 'DOM Selection', <<<'HTML'
<h2>DOM Selection</h2>

<p>The Document Object Model (DOM) is the browser's in-memory representation of an HTML page as a tree of objects. JavaScript uses the DOM to read and manipulate page content. Before you can change anything, you need to select the element you want to work with. DOM selection methods let you find exactly the element(s) you need — by ID, class, tag, attribute, or any CSS selector.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Searching a spreadsheet. You wouldn't edit every cell manually — you'd use Find to locate the specific cell you want. DOM selection methods are your Find function: you specify what you're looking for, and JavaScript returns it so you can work with it.</p>
</div>

<h3>getElementById and querySelector</h3>
<p><code>document.getElementById()</code> is the fastest way to select a single element by its unique ID. <code>document.querySelector()</code> accepts any CSS selector and returns the first matching element. It's the most versatile selection method — it can target by ID, class, attribute, relationship, or any combination. <code>document.querySelectorAll()</code> returns all matching elements as a NodeList. Both query methods return null if nothing is found.</p>
<div class="code-block">
<pre><code>// By ID (fastest)
const header = document.getElementById('main-header');

// By CSS selector — first match
const firstCard = document.querySelector('.card');
const submitBtn = document.querySelector('button[type="submit"]');
const nav = document.querySelector('nav');

// By CSS selector — all matches
const allCards = document.querySelectorAll('.card');
const allLinks = document.querySelectorAll('a[href^="https"]');

// Check if found before using
if (header) {
  console.log(header.textContent);
} else {
  console.log('Header not found');
}

// querySelectorAll returns NodeList — convert to Array for array methods
const cardsArray = Array.from(allCards);
cardsArray.forEach(card => console.log(card.className));</code></pre>
</div>

<h3>Other Selection Methods</h3>
<p><code>getElementsByClassName()</code> returns a live HTMLCollection — it updates automatically when the DOM changes. <code>getElementsByTagName()</code> selects by tag name. These are older methods — <code>querySelector</code> and <code>querySelectorAll</code> are generally preferred because they accept full CSS selectors. However, the live collection behaviour of getElementsBy* can be useful in specific situations.</p>
<div class="code-block">
<pre><code>// getElementsByClassName — live collection
const cards = document.getElementsByClassName('card');
// Note: cards.length updates if cards are added/removed from DOM

// getElementsByTagName
const paragraphs = document.getElementsByTagName('p');
const images = document.getElementsByTagName('img');

// Scoped selection: search within a specific element
const nav = document.querySelector('nav');
const navLinks = nav.querySelectorAll('a');  // only links inside nav

// Relationship traversal
const firstCard = document.querySelector('.card');
const parent = firstCard.parentElement;        // one level up
const siblings = firstCard.parentElement.children; // all siblings
const nextSibling = firstCard.nextElementSibling;
const prevSibling = firstCard.previousElementSibling;
const firstChild = firstCard.firstElementChild;</code></pre>
</div>

<h3>Reading Element Properties</h3>
<p>Once you have an element reference, you can read its properties. <code>textContent</code> gets the text content (all text, no HTML). <code>innerHTML</code> gets the HTML content as a string. <code>getAttribute()</code> reads attribute values. <code>classList</code> gives access to the element's CSS classes. <code>dataset</code> accesses <code>data-*</code> attributes. <code>getBoundingClientRect()</code> returns the element's size and position relative to the viewport.</p>
<div class="code-block">
<pre><code>const card = document.querySelector('.card');

// Text content (no HTML)
console.log(card.textContent);

// HTML content
console.log(card.innerHTML);

// Attribute
const link = document.querySelector('a');
console.log(link.getAttribute('href'));    // "/about"
console.log(link.href);                    // full URL: "https://site.com/about"

// CSS classes
console.log(card.classList.contains('featured'));   // true/false
console.log(card.className);                         // "card card--featured"

// Data attributes: <div data-user-id="42" data-role="admin">
const user = document.querySelector('[data-user-id]');
console.log(user.dataset.userId);   // "42"
console.log(user.dataset.role);     // "admin"

// Position and dimensions
const rect = card.getBoundingClientRect();
console.log(rect.top, rect.left, rect.width, rect.height);</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use getElementById for the fastest single-element lookup by ID. Use querySelector for flexible single-element lookup by any CSS selector. Use querySelectorAll when you need all matching elements. Scope your searches to a known parent element (parent.querySelector) to avoid accidental matches elsewhere on the page.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Not checking for null:</strong> If an element doesn't exist, selection methods return null. Calling properties on null throws a TypeError. Always check if the element was found before using it.</li>
    <li><strong>Running DOM code before the DOM loads:</strong> JavaScript in the &lt;head&gt; runs before the HTML is parsed. Wrap DOM code in DOMContentLoaded event or place scripts at the end of body (or use defer).</li>
    <li><strong>Using innerHTML to read text:</strong> innerHTML includes HTML tags in the returned string. Use textContent for plain text to avoid unintentional HTML parsing.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>querySelector accepts any CSS selector and returns the first match; querySelectorAll returns all matches as a NodeList.</li>
    <li>Scope selections to a parent element (parent.querySelector) to avoid unintended matches in other page areas.</li>
    <li>Check for null before accessing properties; ensure DOM code runs after the page has loaded.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'DOM Manipulation', <<<'HTML'
<h2>DOM Manipulation</h2>

<p>DOM manipulation means changing the content, attributes, styles, and structure of an HTML page from JavaScript. This is how web pages become interactive and dynamic — showing and hiding content, updating text, changing styles in response to user actions, and adding or removing elements. Understanding DOM manipulation is what separates someone who can style a static page from someone who can build a real application.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A stage crew backstage. While the audience watches the play, the crew moves props, changes backdrops, and repositions furniture between scenes. DOM manipulation is your stage crew — making changes to the page's content and layout without the user ever navigating away.</p>
</div>

<h3>Changing Content and Attributes</h3>
<p>Once you've selected an element, modify its content with <code>textContent</code> (sets plain text — safe, no HTML parsing) or <code>innerHTML</code> (sets HTML content — powerful but risky with user-provided data). Set attributes with <code>setAttribute()</code> or direct property access. Change input values with the <code>value</code> property. Change image sources with <code>src</code>. Toggle classes with <code>classList</code> methods.</p>
<div class="code-block">
<pre><code>const title = document.querySelector('.page-title');
const counter = document.querySelector('#counter');
const avatar = document.querySelector('.user-avatar');

// Set text content (safe — no HTML parsing)
title.textContent = 'Welcome, Alex!';

// Set HTML content (use carefully)
counter.innerHTML = '<strong>42</strong> items';

// Change attributes
avatar.setAttribute('src', 'new-avatar.jpg');
avatar.setAttribute('alt', 'Alex profile photo');
avatar.src = 'new-avatar.jpg';  // direct property (same result)

// CSS class manipulation
const card = document.querySelector('.card');
card.classList.add('featured');         // add a class
card.classList.remove('hidden');        // remove a class
card.classList.toggle('expanded');      // add if absent, remove if present
card.classList.replace('old', 'new');   // replace one class with another
console.log(card.classList.contains('featured'));  // true/false</code></pre>
</div>

<h3>Changing Styles</h3>
<p>Inline styles can be set via the <code>style</code> property — CSS property names become camelCase in JavaScript (background-color → backgroundColor). However, adding/removing CSS classes is better practice than setting inline styles directly, because classes keep your styling in CSS where it belongs. Use inline styles for dynamic, computed values (like a positioning calculation) and classes for everything else.</p>
<div class="code-block">
<pre><code>const box = document.querySelector('.box');

// ❌ Setting many inline styles (hard to override later)
box.style.backgroundColor = '#0066cc';
box.style.padding = '20px';
box.style.borderRadius = '8px';

// ✅ Better: toggle classes that contain these styles
box.classList.add('box--highlighted');

// ✅ Use inline styles only for dynamic/computed values
const progressBar = document.querySelector('.progress-fill');
progressBar.style.width = `${percentage}%`;  // dynamic calculation

// Read a computed style (accounts for all CSS sources)
const computedColor = window.getComputedStyle(box).backgroundColor;
console.log(computedColor);  // "rgb(0, 102, 204)"

// Set a CSS custom property on an element
box.style.setProperty('--accent-color', '#ff6600');</code></pre>
</div>

<h3>Adding and Removing Elements</h3>
<p>You can create new elements with <code>document.createElement()</code>, populate them with content and attributes, then insert them into the DOM. <code>appendChild()</code> and <code>insertBefore()</code> are traditional methods. The modern <code>append()</code>, <code>prepend()</code>, <code>before()</code>, <code>after()</code>, and <code>replaceWith()</code> methods accept both elements and text strings, and support multiple arguments. Remove elements with <code>element.remove()</code>.</p>
<div class="code-block">
<pre><code>// Create and configure a new element
const newCard = document.createElement('article');
newCard.className = 'card';
newCard.innerHTML = `
  &lt;h3 class="card__title"&gt;New Course&lt;/h3&gt;
  &lt;p class="card__body"&gt;Start learning today.&lt;/p&gt;
  &lt;a class="card__link" href="/course"&gt;Enroll&lt;/a&gt;
`;

// Insert into the DOM
const container = document.querySelector('.cards-grid');
container.appendChild(newCard);                // at the end
container.prepend(newCard);                    // at the beginning
container.append('Some text', newCard);        // append text or elements

// Insert relative to existing elements
const thirdCard = container.children[2];
thirdCard.before(newCard);      // insert before the third card
thirdCard.after(newCard);       // insert after the third card

// Remove an element
const oldCard = document.querySelector('.card--outdated');
oldCard.remove();  // remove from DOM</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use classList methods instead of inline styles for state-based visual changes. Use textContent for setting plain text (prevents XSS). Use innerHTML only for trusted, controlled content — never with user-provided strings. Create elements with createElement when building dynamic content; use template literals to populate innerHTML for complex HTML structures that don't come from user input.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Using innerHTML with user input:</strong> Setting innerHTML to user-provided content is a Cross-Site Scripting (XSS) vulnerability. A user can inject <code>&lt;script&gt;</code> tags. Use textContent for user input, or sanitise HTML before inserting.</li>
    <li><strong>Modifying styles directly instead of classes:</strong> Direct style manipulation creates specificity problems and makes styles hard to maintain. Toggle CSS classes instead; reserve direct styles for computed/dynamic values.</li>
    <li><strong>Losing references after removing elements:</strong> If you hold a reference to an element and then remove it from the DOM, the reference still exists in JavaScript memory. The element is disconnected from the page but not garbage-collected until all references are cleared.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use textContent for setting plain text; use innerHTML carefully for trusted HTML content only — never for user-provided strings.</li>
    <li>classList methods (add, remove, toggle, contains) are the preferred way to change element appearance; use inline styles only for computed dynamic values.</li>
    <li>Create elements with createElement, configure them, then insert with append/prepend/before/after; remove with element.remove().</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Event Handling', <<<'HTML'
<h2>Event Handling</h2>

<p>Events are actions that happen in the browser — clicks, key presses, mouse movements, form submissions, page loads, scroll events, and more. Event handling is how JavaScript responds to these actions. Every interactive element on a modern website — buttons that do things, forms that validate, menus that open — is powered by event handlers. This is the mechanism that brings web pages to life.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A smoke detector. It sits quietly doing nothing until something triggers it (smoke). Then it performs its response (alarm). You don't check manually every minute whether there's smoke — the detector listens continuously and responds when the event occurs. Event listeners work exactly the same way.</p>
</div>

<h3>addEventListener</h3>
<p><code>addEventListener</code> is the standard way to attach event handlers. It takes the event name (as a string) and a callback function. Multiple listeners can be attached to the same element for the same event. Listeners can be removed with <code>removeEventListener</code> (requires a reference to the same function). Unlike older <code>onclick</code> attribute approaches, <code>addEventListener</code> separates HTML structure from JavaScript behaviour.</p>
<div class="code-block">
<pre><code>const button = document.querySelector('#submit-btn');

// Basic click handler
button.addEventListener('click', function(event) {
  console.log('Button clicked!');
  console.log(event);  // the Event object with details
});

// Arrow function shorthand
button.addEventListener('click', (event) => {
  console.log('Clicked at:', event.clientX, event.clientY);
});

// Multiple listeners on same element — both run
button.addEventListener('click', () => console.log('Handler 1'));
button.addEventListener('click', () => console.log('Handler 2'));

// Remove a listener (must use same function reference)
function handleClick() { console.log('clicked'); }
button.addEventListener('click', handleClick);
button.removeEventListener('click', handleClick);  // removed</code></pre>
</div>

<h3>The Event Object</h3>
<p>Every event handler receives an Event object as its first argument. This object contains details about what happened: where the mouse was, which key was pressed, what element triggered the event, and more. <code>event.target</code> is the element that triggered the event. <code>event.currentTarget</code> is the element the listener is attached to. <code>event.preventDefault()</code> stops the default browser action (like form submission or link navigation). <code>event.stopPropagation()</code> prevents the event from bubbling up to parent elements.</p>
<div class="code-block">
<pre><code>// Prevent form default submit (page reload)
const form = document.querySelector('#contact-form');
form.addEventListener('submit', (event) => {
  event.preventDefault();  // stops page reload
  const data = new FormData(form);
  console.log('Name:', data.get('name'));
  console.log('Email:', data.get('email'));
});

// Keyboard events
document.addEventListener('keydown', (event) => {
  console.log('Key:', event.key);         // "Enter", "a", "ArrowLeft", etc.
  console.log('Code:', event.code);       // "KeyA", "Space", "ArrowLeft"
  console.log('Ctrl held:', event.ctrlKey);

  if (event.key === 'Escape') {
    closeModal();
  }
});

// event.target: which element was actually clicked
document.querySelector('.btn-group').addEventListener('click', (event) => {
  console.log('Clicked:', event.target);           // the actual button
  console.log('Listener on:', event.currentTarget); // .btn-group
});</code></pre>
</div>

<h3>Event Delegation</h3>
<p>Event delegation attaches a single listener to a parent element to handle events from all its children — including children added dynamically after the listener was attached. Instead of attaching listeners to each of 100 buttons, you attach one listener to their parent and check which button was clicked with <code>event.target</code>. This is more performant and automatically handles dynamically added elements.</p>
<div class="code-block">
<pre><code>// ❌ Attaching listeners to every item individually
document.querySelectorAll('.menu-item').forEach(item => {
  item.addEventListener('click', handleItemClick);
});
// Problem: new items added later won't have a listener

// ✅ Event delegation: one listener on the parent
const menu = document.querySelector('.menu');
menu.addEventListener('click', (event) => {
  // event.target is the element that was actually clicked
  const item = event.target.closest('.menu-item');  // climb up to find item
  if (!item) return;  // clicked outside an item

  // Handle the click
  menu.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
  item.classList.add('active');
});</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always use addEventListener rather than HTML event attributes (onclick="..."). Use event delegation when you have many similar interactive elements or when elements are dynamically added. Always call event.preventDefault() on form submit handlers to prevent page reload. Use event.target.closest() to handle events on child elements within a delegated parent listener.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Forgetting event.preventDefault() on forms:</strong> A form submit event causes the page to reload by default. Always call event.preventDefault() if you're handling the submission with JavaScript.</li>
    <li><strong>Adding listeners in a loop without capturing variables:</strong> Due to closures, a loop variable in an event listener often captures the final value of the loop, not the value at iteration time. Use let (block-scoped) or event delegation instead.</li>
    <li><strong>Not removing listeners on cleanup:</strong> Event listeners attached to DOM elements keep the element in memory as long as the listener exists. For components that are frequently created and destroyed, always remove listeners in cleanup code.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>addEventListener attaches event handlers; the callback receives an Event object with details about what happened.</li>
    <li>event.preventDefault() stops default browser actions (form submit, link navigate); event.stopPropagation() prevents bubbling to parent elements.</li>
    <li>Event delegation attaches one listener to a parent to handle events from all current and future children — more efficient than individual listeners.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Creating Elements', <<<'HTML'
<h2>Creating Elements</h2>

<p>Dynamic web applications build and modify their own HTML structure at runtime. Rather than hardcoding every possible state in HTML, you create elements with JavaScript when you need them. Building to-do list items, rendering API data as cards, creating modal dialogs, adding notification banners — all of these are patterns of creating elements dynamically. This lesson brings together DOM selection, manipulation, and events into practical real-world patterns.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A 3D printer. You define a blueprint (a template or function), feed it materials (data), and it fabricates new objects on demand. You can print one object or a thousand — same blueprint, different data each time. Dynamic element creation is your 3D printer for web content.</p>
</div>

<h3>createElement and Template Literals</h3>
<p>The most common pattern: create a container element, populate its innerHTML using a template literal with your data, then append to the DOM. For security, ensure any user-provided data is escaped before going into innerHTML. An alternative is using createElement for each element and building the tree manually — more verbose but safer for user-provided content.</p>
<div class="code-block">
<pre><code>// Create a card from data
function createCourseCard(course) {
  const card = document.createElement('article');
  card.className = 'card';
  card.dataset.id = course.id;

  // Template literal for the inner HTML (trusted data only)
  card.innerHTML = `
    &lt;img class="card__image" src="${course.thumbnail}" alt="${course.title}"&gt;
    &lt;div class="card__body"&gt;
      &lt;span class="card__tag"&gt;${course.level}&lt;/span&gt;
      &lt;h3 class="card__title"&gt;${course.title}&lt;/h3&gt;
      &lt;p class="card__description"&gt;${course.description}&lt;/p&gt;
    &lt;/div&gt;
    &lt;footer class="card__footer"&gt;
      &lt;button class="btn btn-primary" data-course-id="${course.id}"&gt;
        Enroll Now
      &lt;/button&gt;
    &lt;/footer&gt;
  `;

  return card;
}

// Use it
const courses = [
  { id: 1, title: 'HTML Basics', level: 'Beginner', thumbnail: 'html.jpg', description: '...' },
  { id: 2, title: 'CSS Fundamentals', level: 'Beginner', thumbnail: 'css.jpg', description: '...' }
];

const grid = document.querySelector('.courses-grid');
courses.forEach(course => {
  grid.appendChild(createCourseCard(course));
});</code></pre>
</div>

<h3>Building Lists Dynamically</h3>
<p>A common pattern is building or updating a list from data — a to-do list, search results, a comment feed. The key steps: clear existing content, iterate over data, create elements for each item with event listeners attached, then append all items at once using a DocumentFragment for performance. DocumentFragment is a lightweight container that doesn't trigger reflow until you insert it into the DOM.</p>
<div class="code-block">
<pre><code>function renderTodoList(todos) {
  const list = document.querySelector('#todo-list');
  const fragment = document.createDocumentFragment();

  // Clear existing
  list.innerHTML = '';

  if (todos.length === 0) {
    const empty = document.createElement('li');
    empty.className = 'todo-empty';
    empty.textContent = 'No tasks yet. Add one above!';
    list.appendChild(empty);
    return;
  }

  todos.forEach(todo => {
    const item = document.createElement('li');
    item.className = `todo-item${todo.done ? ' todo-item--done' : ''}`;
    item.dataset.id = todo.id;

    item.innerHTML = `
      &lt;input type="checkbox" ${todo.done ? 'checked' : ''}&gt;
      &lt;span class="todo-text"&gt;&lt;/span&gt;
      &lt;button class="todo-delete" aria-label="Delete"&gt;&times;&lt;/button&gt;
    `;

    // Set text safely (no innerHTML for user content)
    item.querySelector('.todo-text').textContent = todo.text;

    fragment.appendChild(item);
  });

  list.appendChild(fragment);  // single DOM insertion
}</code></pre>
</div>

<h3>The HTML template Element</h3>
<p>The HTML <code>&lt;template&gt;</code> element provides a cleaner way to define reusable HTML structures. Template content is parsed but not rendered — it's a dormant blueprint. Clone it with <code>cloneNode(true)</code> to create a copy, fill in the data, then append. This keeps your HTML templates in HTML (where designers can see them) rather than embedded in JavaScript strings.</p>
<div class="code-block">
<pre><code>&lt;!-- In HTML: --&gt;
&lt;template id="notification-template"&gt;
  &lt;div class="notification"&gt;
    &lt;span class="notification__icon"&gt;&lt;/span&gt;
    &lt;p class="notification__message"&gt;&lt;/p&gt;
    &lt;button class="notification__close" aria-label="Close"&gt;&times;&lt;/button&gt;
  &lt;/div&gt;
&lt;/template&gt;

&lt;script&gt;
function showNotification(message, type = 'info') {
  const template = document.getElementById('notification-template');
  const clone = template.content.cloneNode(true);

  const notif = clone.querySelector('.notification');
  notif.classList.add(`notification--${type}`);
  clone.querySelector('.notification__message').textContent = message;

  // Add dismiss functionality
  clone.querySelector('.notification__close').addEventListener('click', () => {
    notif.remove();
  });

  document.getElementById('notifications').appendChild(clone);

  // Auto-dismiss after 5 seconds
  setTimeout(() => notif.remove(), 5000);
}
&lt;/script&gt;</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use factory functions that return elements for reusable components. Use DocumentFragment when inserting multiple elements at once. Use the HTML template element to keep HTML structures in HTML. Always use textContent (not innerHTML) for any user-provided text to prevent XSS vulnerabilities.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Rebuilding entire lists on every update:</strong> Clearing the DOM and rebuilding from scratch on every change loses focus, scroll position, and is slow for large lists. For simple cases it's fine; for complex UIs, update only what changed.</li>
    <li><strong>Inserting user text with innerHTML:</strong> Even if it looks like a small data point, inserting user-provided strings with innerHTML creates XSS vulnerabilities. Always use textContent or createTextNode for user input.</li>
    <li><strong>Adding event listeners inside loops:</strong> Attaching individual listeners to each created element works but can be memory-intensive for large lists. Use event delegation on the container instead.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Factory functions that create and return elements make dynamic content creation reusable and testable.</li>
    <li>Use DocumentFragment to batch multiple DOM insertions into one, minimising layout recalculations.</li>
    <li>The HTML template element keeps HTML blueprints in HTML; clone with cloneNode(true) and fill in data before inserting.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Forms and Validation', <<<'HTML'
<h2>Forms and Validation</h2>

<p>JavaScript form handling combines everything you've learned — DOM selection, event handling, element manipulation — into one of the most practical skills in web development. Virtually every web application needs to collect, validate, and process user input. Understanding how to handle forms with JavaScript means you can build login systems, search interfaces, shopping carts, contact forms, and any other data-collection feature.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A customs officer at an airport. They check that your documents are complete, the information is consistent, nothing is missing, and everything looks legitimate. JavaScript form validation is your customs check — reviewing what the user submitted before it goes anywhere, providing clear feedback when something is wrong.</p>
</div>

<h3>Reading Form Data</h3>
<p>Access form field values via DOM properties. Text inputs and textareas use <code>.value</code>. Checkboxes and radio buttons use <code>.checked</code>. Select dropdowns use <code>.value</code> (or <code>.options[selectedIndex]</code> for more detail). The <code>FormData</code> API extracts all values from a form at once — it's the cleanest way to read a complete form submission without selecting each field individually.</p>
<div class="code-block">
<pre><code>// Reading individual field values
const nameInput = document.querySelector('#name');
const emailInput = document.querySelector('#email');
const newsletterCheckbox = document.querySelector('#newsletter');

console.log(nameInput.value);            // "Alex"
console.log(emailInput.value);           // "alex@example.com"
console.log(newsletterCheckbox.checked); // true or false

// FormData: collect all form data at once
const form = document.querySelector('#signup-form');
form.addEventListener('submit', (e) => {
  e.preventDefault();

  const data = new FormData(form);

  // Access individual values
  const name  = data.get('name');
  const email = data.get('email');

  // Convert to a plain object
  const formObject = Object.fromEntries(data.entries());
  console.log(formObject);  // { name: "Alex", email: "alex@example.com", ... }

  // Submit as JSON to an API
  fetch('/api/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formObject)
  });
});</code></pre>
</div>

<h3>Custom Validation with JavaScript</h3>
<p>While HTML5 provides basic validation, JavaScript lets you implement custom rules, real-time feedback, and styled error messages. The pattern: listen for submit event, validate each field, display errors if invalid and prevent submission, or proceed if all valid. Show errors adjacent to their field, linked via aria-describedby, and clear them when the user corrects the input.</p>
<div class="code-block">
<pre><code>function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function showError(inputId, message) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(inputId + '-error');
  input.classList.add('is-invalid');
  input.setAttribute('aria-invalid', 'true');
  if (error) error.textContent = message;
}

function clearError(inputId) {
  const input = document.getElementById(inputId);
  const error = document.getElementById(inputId + '-error');
  input.classList.remove('is-invalid');
  input.removeAttribute('aria-invalid');
  if (error) error.textContent = '';
}

document.querySelector('#login-form').addEventListener('submit', (e) => {
  e.preventDefault();
  let isValid = true;

  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;

  clearError('email');
  clearError('password');

  if (!email) {
    showError('email', 'Email is required.');
    isValid = false;
  } else if (!validateEmail(email)) {
    showError('email', 'Please enter a valid email address.');
    isValid = false;
  }

  if (password.length < 8) {
    showError('password', 'Password must be at least 8 characters.');
    isValid = false;
  }

  if (isValid) {
    submitLogin(email, password);
  }
});</code></pre>
</div>

<h3>Real-Time Validation Feedback</h3>
<p>Better UX validates as the user types or moves away from a field, not only on submit. Use the <code>input</code> event for live feedback and the <code>blur</code> event to validate when a field loses focus. Avoid showing errors before the user has interacted with a field — only show them on blur or after a first invalid submission attempt.</p>
<div class="code-block">
<pre><code>const emailInput = document.getElementById('email');

// Validate on blur (when user leaves field)
emailInput.addEventListener('blur', () => {
  clearError('email');
  if (!emailInput.value.trim()) {
    showError('email', 'Email is required.');
  } else if (!validateEmail(emailInput.value)) {
    showError('email', 'Enter a valid email address.');
  }
});

// Live password strength indicator
const passwordInput = document.getElementById('password');
const strengthBar = document.getElementById('strength-bar');

passwordInput.addEventListener('input', () => {
  const val = passwordInput.value;
  let strength = 0;
  if (val.length >= 8) strength++;
  if (/[A-Z]/.test(val)) strength++;
  if (/[0-9]/.test(val)) strength++;
  if (/[^A-Za-z0-9]/.test(val)) strength++;

  const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
  const colours = ['', '#dc3545', '#fd7e14', '#ffc107', '#28a745'];
  strengthBar.textContent = labels[strength];
  strengthBar.style.color = colours[strength];
});</code></pre>
</div>

<h3>When to Use This</h3>
<p>Always handle the submit event with preventDefault to control form submission. Always validate on the server too — client-side validation can be bypassed. Show errors inline next to their fields, not in an alert() dialog. Use aria-invalid and aria-describedby to make error messages accessible to screen readers.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Relying only on JavaScript validation:</strong> JavaScript can be disabled. Always validate on the server side — JavaScript validation is UX improvement, not security.</li>
    <li><strong>Showing errors before user interaction:</strong> Showing all errors on page load before the user has typed anything is frustrating. Only show field errors after the user has interacted with that specific field.</li>
    <li><strong>Using alert() for validation messages:</strong> alert() blocks the page and provides no useful styling or context. Display inline error messages next to the relevant field instead.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Use FormData to collect all form values at once; read individual fields with .value and .checked properties.</li>
    <li>Custom validation shows styled, accessible errors inline; validate on blur for real-time feedback and on submit as a final check.</li>
    <li>Client-side validation improves UX but is not a security measure — always validate on the server.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// JAVASCRIPT COURSE — Module 11
// =====================================================================

updateLesson($pdo, 'Callbacks', <<<'HTML'
<h2>Callbacks</h2>

<p>A callback is a function passed as an argument to another function, to be called at a later time. Callbacks are how JavaScript handles operations that take time — file reads, network requests, timers. Since JavaScript is single-threaded and can't pause and wait, it provides a callback to call when the operation finishes. Understanding callbacks is the foundation for understanding all asynchronous JavaScript.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Leaving your phone number at a restaurant when there's a wait for a table. You don't stand at the door waiting — you go about your evening. When the table is ready, they call you back. That callback (your phone number) is how they notify you when the event is complete. You provided the action to take when the operation finished.</p>
</div>

<h3>Callbacks in Synchronous Context</h3>
<p>Callbacks aren't only for async code — you've been using them all along. Array methods like forEach, map, and filter all accept callback functions. Every event listener's second argument is a callback. Understanding that "a callback is just a function passed to another function" demystifies a lot of JavaScript code you'll encounter.</p>
<div class="code-block">
<pre><code>// forEach takes a callback — called once per array item
const names = ['Alex', 'Sam', 'Jordan'];
names.forEach(function(name) {
  console.log(`Hello, ${name}!`);
});

// Same with arrow function callback
names.forEach(name => console.log(`Hello, ${name}!`));

// map: callback transforms each item, returns a new array
const lengths = names.map(name => name.length);
// [4, 3, 6]

// sort: callback defines sort order
const numbers = [3, 1, 4, 1, 5, 9, 2, 6];
numbers.sort((a, b) => a - b);    // ascending
numbers.sort((a, b) => b - a);    // descending

// Custom higher-order function accepting a callback
function doTwice(action) {
  action();
  action();
}
doTwice(() => console.log('Hello!'));  // prints twice</code></pre>
</div>

<h3>Asynchronous Callbacks</h3>
<p>Asynchronous callbacks execute after an event or delay, not immediately. <code>setTimeout</code> calls a callback after a specified delay. <code>setInterval</code> calls it repeatedly. XMLHttpRequest (the old way to make network requests) used callbacks for when data arrived. The pattern: start the async operation, provide a callback, continue doing other work, and JavaScript calls your callback when the operation completes.</p>
<div class="code-block">
<pre><code>// setTimeout: call callback after 2000ms (2 seconds)
console.log('Start');
setTimeout(() => {
  console.log('This runs after 2 seconds');
}, 2000);
console.log('End');
// Output: "Start", "End", (2 seconds later) "This runs after 2 seconds"

// setInterval: repeat every 1000ms
let count = 0;
const intervalId = setInterval(() => {
  count++;
  console.log(`Tick: ${count}`);
  if (count >= 5) {
    clearInterval(intervalId);  // stop after 5 ticks
  }
}, 1000);

// Node.js file system callback (conceptual)
// fs.readFile('data.json', 'utf8', (error, data) => {
//   if (error) { console.error(error); return; }
//   console.log(data);
// });</code></pre>
</div>

<h3>Callback Hell and Its Problems</h3>
<p>When multiple async operations depend on each other, callbacks nest inside callbacks. This "callback hell" (also called the "pyramid of doom") is hard to read, hard to debug, and hard to handle errors in. Error handling requires manual checks at every level. This is the problem that Promises were designed to solve — we'll see that in the next lesson. Understanding why callbacks become problematic motivates why Promises exist.</p>
<div class="code-block">
<pre><code>// Callback hell: nested async operations
login(username, password, (error, token) => {
  if (error) {
    handleError(error);
    return;
  }
  fetchProfile(token, (error, profile) => {
    if (error) {
      handleError(error);
      return;
    }
    fetchCourses(profile.id, (error, courses) => {
      if (error) {
        handleError(error);
        return;
      }
      // Finally do something with courses
      renderCourses(courses);
      // Still need to handle MORE nested operations...
    });
  });
});

// Problems:
// 1. Deep nesting is hard to read
// 2. Error handling repeated at every level
// 3. Can't use try/catch
// 4. Hard to run operations in parallel</code></pre>
</div>

<h3>When to Use This</h3>
<p>Callbacks are still appropriate for: event listeners (addEventListener), array methods (map/filter/forEach), simple one-time timers, and when you're given a callback-based API. For anything involving multiple sequential async operations or error propagation, use Promises or async/await instead.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Calling the callback instead of passing it:</strong> <code>setTimeout(doSomething(), 1000)</code> calls doSomething immediately and passes its return value to setTimeout. Use <code>setTimeout(doSomething, 1000)</code> to pass the function reference.</li>
    <li><strong>Not handling errors in callbacks:</strong> Async callbacks often receive an error as the first argument (Node.js convention). Always check and handle the error before proceeding — ignoring it leads to silent failures.</li>
    <li><strong>Using callbacks for sequential async operations:</strong> More than two levels of async callback nesting signals you should refactor to Promises or async/await for maintainability.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>A callback is a function passed to another function to be called later — used in array methods, event listeners, and async operations.</li>
    <li>setTimeout and setInterval use callbacks to execute code after a delay or repeatedly at intervals.</li>
    <li>Nested async callbacks create "callback hell" — hard to read and maintain; this problem motivated the creation of Promises.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Promises', <<<'HTML'
<h2>Promises</h2>

<p>A Promise is an object representing the eventual completion or failure of an asynchronous operation. Promises solve the callback hell problem by providing a clean, chainable way to sequence async operations. They also make error handling easier — a single .catch() handles errors from any step in a chain. Promises are the foundation of modern JavaScript asynchronous code.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Ordering something online. You don't get the item immediately — you get a tracking number (a Promise). That tracking number represents a future delivery. The package is either pending (not arrived), fulfilled (delivered successfully), or rejected (lost/damaged). You can say "when delivered, do this; if problem, do that" — without sitting at the door waiting.</p>
</div>

<h3>Promise States and Creation</h3>
<p>A Promise has three states: <strong>pending</strong> (initial state), <strong>fulfilled</strong> (operation succeeded), and <strong>rejected</strong> (operation failed). Create a Promise with <code>new Promise()</code>, passing a function that receives <code>resolve</code> and <code>reject</code> callbacks. Call <code>resolve(value)</code> when the operation succeeds; call <code>reject(reason)</code> when it fails. In practice, you rarely create Promises from scratch — you consume them from APIs like fetch().</p>
<div class="code-block">
<pre><code>// Creating a Promise
const myPromise = new Promise((resolve, reject) => {
  // Simulate an async operation (network request, file read, etc.)
  setTimeout(() => {
    const success = Math.random() > 0.5;  // 50% chance of success
    if (success) {
      resolve({ data: 'Here is your data', code: 200 });
    } else {
      reject(new Error('Operation failed'));
    }
  }, 1000);
});

// Consuming the Promise
myPromise
  .then(result => {
    console.log('Success:', result.data);  // "Here is your data"
  })
  .catch(error => {
    console.error('Error:', error.message);  // "Operation failed"
  })
  .finally(() => {
    console.log('Always runs, success or failure');
  });</code></pre>
</div>

<h3>Promise Chaining</h3>
<p>Promise chains replace nested callbacks with a flat, readable sequence of steps. Each <code>.then()</code> receives the return value of the previous step. If a <code>.then()</code> callback returns a Promise, the chain waits for it to resolve before proceeding. A single <code>.catch()</code> at the end handles errors from any step — you don't need error handling at every level. This is the primary advantage over callbacks.</p>
<div class="code-block">
<pre><code>// Callback hell equivalent — now flat with chaining
login(username, password)
  .then(token => fetchProfile(token))
  .then(profile => fetchCourses(profile.id))
  .then(courses => renderCourses(courses))
  .catch(error => {
    // Handles errors from ANY step above
    console.error('Something failed:', error.message);
    showErrorMessage('Something went wrong. Please try again.');
  })
  .finally(() => {
    hideLoadingSpinner();
  });

// Transform data in then()
fetch('/api/users')
  .then(response => response.json())     // parse JSON (returns a Promise)
  .then(users => users.filter(u => u.active))  // filter to active users
  .then(activeUsers => {
    console.log(`${activeUsers.length} active users`);
    renderUserList(activeUsers);
  })
  .catch(err => console.error('Failed to load users:', err));</code></pre>
</div>

<h3>Promise.all and Promise.race</h3>
<p><code>Promise.all()</code> runs multiple Promises in parallel and waits for all to complete. If any one rejects, the whole thing rejects. <code>Promise.allSettled()</code> waits for all to settle regardless of success/failure, useful when you want results from all operations even if some failed. <code>Promise.race()</code> resolves/rejects as soon as the first Promise settles. <code>Promise.any()</code> resolves with the first successful one.</p>
<div class="code-block">
<pre><code>// Run three API calls in PARALLEL (not sequential)
const [userProfile, courses, notifications] = await Promise.all([
  fetch('/api/profile').then(r => r.json()),
  fetch('/api/courses').then(r => r.json()),
  fetch('/api/notifications').then(r => r.json())
]);
// All three fetch simultaneously — faster than sequential

// allSettled: get results even if some fail
const results = await Promise.allSettled([
  fetch('/api/fast-endpoint').then(r => r.json()),
  fetch('/api/slow-endpoint').then(r => r.json()),
  fetch('/api/broken-endpoint').then(r => r.json())  // this might fail
]);

results.forEach(result => {
  if (result.status === 'fulfilled') {
    console.log('Success:', result.value);
  } else {
    console.log('Failed:', result.reason.message);
  }
});</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use Promises any time you're dealing with async operations — API requests (fetch returns a Promise), file operations, timers wrapped in Promises, or any API that returns a Promise. Chain multiple sequential async operations with .then(). Run multiple independent operations simultaneously with Promise.all(). Always add a .catch() to prevent unhandled rejection warnings.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Forgetting to return in then():</strong> If you don't return a value or Promise from a .then() callback, the next .then() receives undefined. Always return the value or the next Promise explicitly.</li>
    <li><strong>Creating a Promise inside a then():</strong> Wrapping fetch() in a new Promise is redundant — fetch already returns a Promise. This "Promise constructor anti-pattern" is unnecessary complexity.</li>
    <li><strong>Missing .catch():</strong> An unhandled Promise rejection fails silently (or crashes in newer Node.js versions). Always add .catch() to every Promise chain.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Promises have three states (pending, fulfilled, rejected); use .then() for success, .catch() for errors, .finally() for cleanup.</li>
    <li>Promise chains replace nested callbacks with flat, readable sequential async logic; one .catch() handles errors from the entire chain.</li>
    <li>Promise.all() runs multiple Promises in parallel and waits for all; Promise.allSettled() waits for all regardless of success/failure.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Async/Await', <<<'HTML'
<h2>Async/Await</h2>

<p>Async/await is syntax built on top of Promises that makes asynchronous code look and read like synchronous code. Instead of chaining .then() calls, you write code in a natural top-to-bottom sequence and use <code>await</code> to pause until a Promise resolves. The result is dramatically more readable code for complex async flows. Async/await is the modern, preferred way to write async JavaScript.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A recipe written in plain language vs. a flowchart. Both describe the same cooking process, but the plain-language recipe ("first, boil water; then, add pasta; when tender, drain") is much easier to follow. Async/await writes your Promise chains in plain-language sequential steps rather than flowchart-style .then() branches.</p>
</div>

<h3>async and await Keywords</h3>
<p>The <code>async</code> keyword declares a function as asynchronous — it always returns a Promise. Inside an async function, use <code>await</code> before a Promise to pause execution until it resolves, then return its value. The key point: only the async function pauses; the rest of your code continues running. Try/catch handles errors from awaited Promises, just like synchronous errors.</p>
<div class="code-block">
<pre><code>// Traditional Promise chain
function loadUserData(userId) {
  return fetch(`/api/users/${userId}`)
    .then(res => res.json())
    .then(user => fetchCourses(user.enrollments))
    .then(courses => ({ user, courses }))
    .catch(err => console.error(err));
}

// Same logic with async/await — much clearer
async function loadUserData(userId) {
  try {
    const response = await fetch(`/api/users/${userId}`);
    const user = await response.json();

    const courses = await fetchCourses(user.enrollments);

    return { user, courses };

  } catch (error) {
    console.error('Failed to load user data:', error.message);
    throw error;  // re-throw so the caller can also handle it
  }
}

// Using the async function
loadUserData(42).then(data => renderProfile(data.user, data.courses));</code></pre>
</div>

<h3>Error Handling with try/catch</h3>
<p>With async/await, use try/catch blocks just like synchronous error handling. This is much more natural than chaining .catch() methods. You can have multiple try/catch blocks to handle errors at different levels — wrapping all steps for overall errors, or wrapping specific steps for granular recovery. If an awaited Promise rejects and there's no try/catch, the error becomes an unhandled Promise rejection.</p>
<div class="code-block">
<pre><code>async function submitForm(formData) {
  // Show loading state
  setLoading(true);

  try {
    // Each await can reject — all caught by catch
    const token = await authenticate(formData.credentials);
    const profile = await createProfile(token, formData.profile);
    const response = await sendWelcomeEmail(profile.email);

    showSuccess(`Account created! Check ${profile.email} for confirmation.`);
    redirectToDashboard();

  } catch (error) {
    // Handle different error types
    if (error.status === 409) {
      showError('Email already in use. Please sign in instead.');
    } else if (error.status === 422) {
      showError('Invalid details. Please check your information.');
    } else {
      showError('Something went wrong. Please try again.');
    }
    console.error('Registration failed:', error);

  } finally {
    setLoading(false);  // always hide loading, success or fail
  }
}</code></pre>
</div>

<h3>Parallel Execution with async/await</h3>
<p>Be careful with sequential await — it's easy to accidentally write operations that could run in parallel as sequential ones, making them slower. If two operations don't depend on each other, run them simultaneously with Promise.all(). The syntax for this with async/await is to start both operations first (getting the Promises), then await them together.</p>
<div class="code-block">
<pre><code>// ❌ Sequential: waits for each to finish before starting the next
async function loadDashboard(userId) {
  const profile  = await fetchProfile(userId);   // 300ms
  const courses  = await fetchCourses(userId);   // 400ms
  const stats    = await fetchStats(userId);     // 200ms
  // Total: ~900ms
  return { profile, courses, stats };
}

// ✅ Parallel: all three start at the same time
async function loadDashboard(userId) {
  const [profile, courses, stats] = await Promise.all([
    fetchProfile(userId),
    fetchCourses(userId),
    fetchStats(userId)
  ]);
  // Total: ~400ms (longest single request)
  return { profile, courses, stats };
}

// Await individual Promises started simultaneously
async function loadPartial(userId) {
  const profilePromise = fetchProfile(userId);  // starts immediately
  const coursesPromise = fetchCourses(userId);  // starts immediately

  // Now await them
  const profile = await profilePromise;
  const courses = await coursesPromise;
  return { profile, courses };
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use async/await for all new async code — it's more readable than Promise chains for complex flows. Use Promise.all() inside async functions when operations can run in parallel. Top-level await is available in ES modules. For simple one-step Promises in utility functions, chaining .then() is still acceptable.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Using await inside a non-async function:</strong> The <code>await</code> keyword only works inside functions declared with <code>async</code>. Using it outside causes a syntax error.</li>
    <li><strong>Sequential awaits for independent operations:</strong> Awaiting operations one at a time when they could run in parallel wastes time. Use Promise.all() when operations don't depend on each other.</li>
    <li><strong>Not handling async function errors at the call site:</strong> If an async function throws and the caller doesn't await it or catch its returned Promise, the error becomes an unhandled rejection. Always await async calls and use try/catch or .catch() at the call site.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>async functions always return Promises; await pauses execution inside the async function until a Promise resolves.</li>
    <li>try/catch handles errors from awaited Promises naturally, just like synchronous error handling.</li>
    <li>Use Promise.all() inside async functions to run independent operations in parallel rather than sequentially.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Fetch API', <<<'HTML'
<h2>Fetch API</h2>

<p>The Fetch API is the modern way to make HTTP requests from JavaScript. It provides a clean, Promise-based interface for fetching data from APIs, submitting forms, uploading files, and communicating with any server. Virtually every modern web application uses Fetch to connect its frontend to a backend or third-party service. Understanding Fetch is essential for building anything beyond static pages.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Sending a letter and waiting for a reply. You write the letter (request), specify the address (URL) and what you want (method and body), send it, and eventually receive a reply (response). Fetch handles this postal system between your browser and a server — packaging your request, sending it, and delivering the response.</p>
</div>

<h3>Basic fetch() Usage</h3>
<p><code>fetch(url)</code> makes a GET request and returns a Promise that resolves with a Response object. The Response object contains metadata about the reply (status code, headers). To get the actual data, call <code>response.json()</code> (for JSON) or <code>response.text()</code> — these also return Promises. A crucial point: fetch only rejects on network failure, NOT on HTTP error codes (404, 500). You must check <code>response.ok</code> manually.</p>
<div class="code-block">
<pre><code>// Basic GET request
async function getUsers() {
  try {
    const response = await fetch('https://jsonplaceholder.typicode.com/users');

    // Check for HTTP errors (404, 500, etc.)
    // fetch() does NOT throw on these — you must check manually
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const users = await response.json();
    return users;

  } catch (error) {
    if (error.name === 'TypeError') {
      // Network failure (no internet, CORS issue)
      console.error('Network error:', error.message);
    } else {
      // HTTP error we threw above
      console.error('Request failed:', error.message);
    }
    throw error;
  }
}</code></pre>
</div>

<h3>POST, PUT, DELETE Requests</h3>
<p>To send data, pass a configuration object as the second argument to fetch(). Set the <code>method</code> (POST, PUT, DELETE, PATCH), <code>headers</code> (Content-Type for JSON), and <code>body</code> (the data to send, serialised with JSON.stringify for JSON APIs). Most REST APIs expect and return JSON, so setting <code>Content-Type: application/json</code> in headers and sending JSON.stringify(data) as the body is the standard pattern.</p>
<div class="code-block">
<pre><code>// POST: create a new resource
async function createCourse(courseData) {
  const response = await fetch('/api/courses', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${getAuthToken()}`
    },
    body: JSON.stringify(courseData)
  });

  if (!response.ok) throw new Error(`Failed: ${response.status}`);
  return response.json();
}

// PUT: update an existing resource
async function updateProfile(userId, updates) {
  const response = await fetch(`/api/users/${userId}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(updates)
  });

  if (!response.ok) throw new Error(`Update failed: ${response.status}`);
  return response.json();
}

// DELETE: remove a resource
async function deleteCourse(courseId) {
  const response = await fetch(`/api/courses/${courseId}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${getAuthToken()}` }
  });

  if (!response.ok) throw new Error(`Delete failed: ${response.status}`);
  // DELETE often returns 204 No Content — no body to parse
}</code></pre>
</div>

<h3>Practical Fetch Pattern with Loading and Error States</h3>
<p>Real-world fetch usage includes managing loading states, error states, and updating the UI accordingly. A reusable async helper function that handles common error cases, shows/hides loading indicators, and processes the response makes API calls DRY and consistent across your application.</p>
<div class="code-block">
<pre><code>async function loadCourses() {
  const container = document.getElementById('courses-container');
  const error = document.getElementById('error-message');
  const loading = document.getElementById('loading-indicator');

  // Show loading, hide previous error
  loading.hidden = false;
  error.hidden = true;

  try {
    const response = await fetch('/api/courses');

    if (!response.ok) {
      throw new Error(`Server returned ${response.status}`);
    }

    const courses = await response.json();

    // Render courses
    container.innerHTML = '';
    courses.forEach(course => {
      container.appendChild(createCourseCard(course));
    });

  } catch (err) {
    error.hidden = false;
    error.textContent = 'Failed to load courses. Please refresh.';
    console.error(err);
  } finally {
    loading.hidden = true;  // always hide loading
  }
}

// Call it
loadCourses();</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use fetch for all API communication in modern web applications. Create a reusable wrapper around fetch that handles authentication headers, error checking, and response parsing consistently. For complex API interactions, consider using the axios library which has better error handling defaults — but fetch is sufficient for most projects.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Not checking response.ok:</strong> fetch() resolves (not rejects) on 404 and 500 errors. Always check response.ok and throw an error if false.</li>
    <li><strong>Forgetting await before response.json():</strong> response.json() returns a Promise. Without await (or .then()), you get the Promise object, not the data.</li>
    <li><strong>Not setting Content-Type for POST requests:</strong> Sending JSON without <code>Content-Type: application/json</code> means the server may not know how to parse the body, resulting in errors or empty data.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>fetch() returns a Promise of a Response; call response.json() to get the data (also a Promise); always check response.ok for HTTP errors.</li>
    <li>POST/PUT/DELETE requests need a config object with method, headers (Content-Type), and body (JSON.stringify(data)).</li>
    <li>Manage loading, success, and error states in your UI around every fetch call; use finally to always hide the loading indicator.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Error Handling', <<<'HTML'
<h2>Error Handling</h2>

<p>Error handling is the practice of anticipating what can go wrong and responding gracefully. Without error handling, one unexpected failure crashes your entire application, leaves users stuck on broken screens, and makes debugging difficult. Good error handling means: errors are caught, users see helpful messages, and you have enough information in your logs to diagnose what went wrong.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A circuit breaker in your home's electrical panel. When something shorts out, the breaker trips — isolating the problem so only one circuit is affected, not the whole house. Error handling is your circuit breaker: isolating failures so one problem doesn't take down everything, and providing a safe state to recover from.</p>
</div>

<h3>try/catch/finally</h3>
<p>The <code>try</code> block contains code that might throw an error. If an error occurs, execution jumps to the <code>catch</code> block, which receives the Error object. The <code>finally</code> block runs regardless — useful for cleanup like closing connections or hiding loading states. You can nest try/catch for granular error handling. You can also rethrow errors when you've partially handled them and want the caller to also handle them.</p>
<div class="code-block">
<pre><code>function divideNumbers(a, b) {
  try {
    if (typeof a !== 'number' || typeof b !== 'number') {
      throw new TypeError('Both arguments must be numbers');
    }
    if (b === 0) {
      throw new RangeError('Cannot divide by zero');
    }
    return a / b;

  } catch (error) {
    if (error instanceof TypeError) {
      console.error('Type error:', error.message);
      return null;
    }
    if (error instanceof RangeError) {
      console.error('Range error:', error.message);
      return Infinity;
    }
    // Re-throw unexpected errors
    throw error;
  } finally {
    console.log('Division attempted');  // always runs
  }
}

console.log(divideNumbers(10, 2));    // 5
console.log(divideNumbers(10, 0));    // Infinity (caught RangeError)
console.log(divideNumbers('a', 2));   // null (caught TypeError)</code></pre>
</div>

<h3>Custom Error Classes</h3>
<p>JavaScript's Error class can be extended to create custom error types. This lets you distinguish between different categories of errors and handle them differently — a validation error is different from a network error which is different from an authentication error. Custom errors preserve the stack trace and can carry additional data alongside the message.</p>
<div class="code-block">
<pre><code>class ValidationError extends Error {
  constructor(message, field) {
    super(message);
    this.name = 'ValidationError';
    this.field = field;  // extra data
  }
}

class ApiError extends Error {
  constructor(message, statusCode) {
    super(message);
    this.name = 'ApiError';
    this.statusCode = statusCode;
  }
}

// Throw custom errors
function validateAge(age) {
  if (isNaN(age)) throw new ValidationError('Age must be a number', 'age');
  if (age < 0)    throw new ValidationError('Age cannot be negative', 'age');
  if (age > 150)  throw new ValidationError('Age seems unrealistic', 'age');
  return true;
}

// Handle by type
try {
  validateAge('abc');
} catch (error) {
  if (error instanceof ValidationError) {
    console.log(`Validation failed on field: ${error.field}`);
    showFieldError(error.field, error.message);
  } else {
    throw error;  // unexpected error
  }
}</code></pre>
</div>

<h3>Async Error Handling Patterns</h3>
<p>For async/await code, wrap awaited calls in try/catch. For Promise chains, add .catch(). For global unhandled Promise rejections, listen for the unhandledrejection event. It's good practice to have both local (per-function) error handling for recovery and a global handler as a safety net for anything that slips through.</p>
<div class="code-block">
<pre><code>// Global safety net for any missed Promise rejections
window.addEventListener('unhandledrejection', (event) => {
  console.error('Unhandled rejection:', event.reason);
  // Report to error tracking service (e.g., Sentry)
  trackError(event.reason);
  // Show a generic error message to the user
  showGlobalError('Something unexpected happened.');
  event.preventDefault();  // suppress browser default console warning
});

// Per-function error handling
async function saveUserPreferences(prefs) {
  try {
    await fetch('/api/preferences', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(prefs)
    }).then(r => { if (!r.ok) throw new ApiError('Save failed', r.status); });
    showSuccess('Preferences saved!');
  } catch (error) {
    if (error instanceof ApiError && error.statusCode === 401) {
      redirectToLogin();
    } else {
      showError('Could not save preferences. Try again.');
    }
    console.error(error);
  }
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Wrap every async operation in try/catch. Create custom error classes for domain-specific errors. Always log errors with enough context to diagnose them. Show user-friendly messages that don't expose implementation details. Never silently swallow errors — at minimum, log them, even if you handle them gracefully.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Empty catch blocks:</strong> Catching an error and doing nothing (<code>catch (e) {}</code>) hides bugs and makes debugging impossible. Always at least log the error, even if the user-facing behaviour is graceful.</li>
    <li><strong>Catching then ignoring the error type:</strong> Treating all errors the same leads to unhelpful messages. A 401 (Unauthorised) should redirect to login; a 500 should show a retry button. Check the error type and respond appropriately.</li>
    <li><strong>Not re-throwing unexpected errors:</strong> If your catch block only handles specific error types, always re-throw anything else. Catching all errors and handling them all the same swallows bugs you didn't anticipate.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>try/catch/finally handles synchronous and async errors; finally always runs regardless of success or failure.</li>
    <li>Extend the Error class to create custom error types (ValidationError, ApiError) that carry additional context.</li>
    <li>Never silently swallow errors; always log them and show appropriate user-facing messages based on the error type.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// JAVASCRIPT COURSE — Module 12
// =====================================================================

updateLesson($pdo, 'Destructuring', <<<'HTML'
<h2>Destructuring</h2>

<p>Destructuring is a convenient syntax for unpacking values from arrays and objects into individual variables. Instead of accessing each property one by one (<code>const name = user.name; const age = user.age;</code>), you write one statement that does it all. Destructuring appears everywhere in modern JavaScript — in function parameters, import statements, API responses, and configuration objects. Learning it makes code dramatically more concise and readable.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Unpacking a delivery box. Instead of carefully removing each item one at a time, labelling and placing them individually, destructuring lets you "tip the box" and everything lands exactly where it should go in one motion. Same items, much faster process.</p>
</div>

<h3>Object Destructuring</h3>
<p>Object destructuring uses curly braces to extract named properties. The variable name must match the property name by default, but you can alias it with <code>: newName</code>. Default values with <code>= default</code> are used when the property is undefined. Nested objects can be destructured inline. This pattern is extremely common for function parameters — instead of receiving a large object and accessing properties manually, destructure directly in the parameter list.</p>
<div class="code-block">
<pre><code>const user = {
  id: 42,
  name: 'Alex Kamau',
  email: 'alex@example.com',
  role: 'admin',
  location: { city: 'Nairobi', country: 'Kenya' }
};

// Basic destructuring
const { name, email, role } = user;
console.log(name);   // "Alex Kamau"
console.log(email);  // "alex@example.com"

// Alias (rename): property "name" stored as "userName"
const { name: userName, id: userId } = user;
console.log(userName);  // "Alex Kamau"

// Default values
const { theme = 'light', language = 'en' } = user;
console.log(theme);     // 'light' (not on user, uses default)

// Nested destructuring
const { location: { city, country } } = user;
console.log(city);   // "Nairobi"

// In function parameters (very common pattern)
function displayUser({ name, email, role = 'student' }) {
  console.log(`${name} (${role}) — ${email}`);
}
displayUser(user);</code></pre>
</div>

<h3>Array Destructuring</h3>
<p>Array destructuring uses square brackets and assigns elements by position. You can skip elements with commas, collect remaining elements with rest syntax (<code>...rest</code>), and swap variables without a temporary variable. Array destructuring is commonly used with functions that return multiple values (as an array), with hooks in React, and with the entries() and items from regex matches.</p>
<div class="code-block">
<pre><code>const scores = [95, 88, 72, 91, 85];

// Basic: first and second
const [first, second] = scores;
console.log(first);   // 95
console.log(second);  // 88

// Skip elements with commas
const [top, , third] = scores;  // skip index 1
console.log(third);  // 72

// Rest: collect remaining into an array
const [highest, ...rest] = scores;
console.log(rest);  // [88, 72, 91, 85]

// Swap variables (no temp variable needed)
let a = 1, b = 2;
[a, b] = [b, a];
console.log(a, b);  // 2, 1

// Function returning multiple values
function getMinMax(arr) {
  return [Math.min(...arr), Math.max(...arr)];
}
const [min, max] = getMinMax(scores);
console.log(min, max);  // 72, 95

// With Object.entries()
const config = { host: 'localhost', port: 3000 };
for (const [key, value] of Object.entries(config)) {
  console.log(`${key}: ${value}`);
}</code></pre>
</div>

<h3>Destructuring in Practice</h3>
<p>Real-world code uses destructuring extensively when working with API responses, configuration objects, and React state. Destructuring makes it clear exactly which properties a function uses, serving as implicit documentation. Combined with default values, it makes functions robust against missing data. Destructuring also works with computed property names using square bracket syntax.</p>
<div class="code-block">
<pre><code>// API response destructuring
async function loadCourse(id) {
  const response = await fetch(`/api/courses/${id}`);
  const {
    title,
    description,
    instructor: { name: instructorName, avatar },
    modules,
    enrollmentCount = 0
  } = await response.json();

  return { title, description, instructorName, avatar, modules, enrollmentCount };
}

// Destructure in forEach
const courses = [
  { id: 1, title: 'HTML', enrolled: 120 },
  { id: 2, title: 'CSS', enrolled: 95 }
];

courses.forEach(({ id, title, enrolled }) => {
  console.log(`Course ${id}: ${title} (${enrolled} students)`);
});</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use object destructuring for function parameters when the function takes an options object — it makes the accepted keys explicit and provides easy defaults. Use array destructuring for functions that return multiple values. Use rest destructuring to separate the first item from the remainder of a list.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Destructuring undefined:</strong> If the object or array doesn't exist, destructuring throws a TypeError. Always ensure the source exists: <code>const { name } = user || {}</code> provides an empty object fallback.</li>
    <li><strong>Mixing up array and object destructuring syntax:</strong> Object destructuring uses curly braces ({}), array destructuring uses square brackets ([]). Swapping them causes unexpected errors or empty values.</li>
    <li><strong>Deep destructuring brittle code:</strong> Very deep destructuring (4+ levels) becomes hard to read and breaks if the structure changes. Consider pulling out an intermediate reference for readability.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Object destructuring extracts named properties; alias with <code>: newName</code> and set defaults with <code>= value</code>.</li>
    <li>Array destructuring extracts by position; skip elements with commas; collect remaining with rest syntax (...rest).</li>
    <li>Destructure function parameters to make accepted keys explicit and enable defaults without manual checks inside the function.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Spread and Rest', <<<'HTML'
<h2>Spread and Rest</h2>

<p>The spread operator (<code>...</code>) and rest syntax both use three dots but serve opposite purposes. Spread expands an iterable (array, object, string) into individual elements — useful for combining arrays, cloning objects, and passing array items as function arguments. Rest collects multiple individual values into an array or object — used in function parameters and destructuring. These two features are among the most useful additions to modern JavaScript.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A suitcase. Spread is unpacking the suitcase — opening it and taking out each item individually. Rest is packing the suitcase — gathering a bunch of loose items and putting them together into one container. Same three dots, context determines direction.</p>
</div>

<h3>Spread with Arrays</h3>
<p>The spread operator in array contexts "spreads" array elements. This enables: copying arrays without mutation, merging multiple arrays, inserting array elements into another array, and passing array elements as individual function arguments. This is far cleaner than older approaches using concat() and apply().</p>
<div class="code-block">
<pre><code>const frontend = ['HTML', 'CSS', 'JavaScript'];
const backend  = ['Node.js', 'PHP', 'Python'];

// Combine arrays
const allTech = [...frontend, ...backend];
// ['HTML', 'CSS', 'JavaScript', 'Node.js', 'PHP', 'Python']

// Copy an array (not a reference — independent copy)
const copy = [...frontend];
copy.push('TypeScript');
console.log(frontend);  // unchanged

// Insert in the middle
const withNew = [...frontend.slice(0, 2), 'TypeScript', ...frontend.slice(2)];
// ['HTML', 'CSS', 'TypeScript', 'JavaScript']

// Pass array as function arguments
const numbers = [5, 2, 8, 1, 9, 3];
console.log(Math.max(...numbers));   // 9
console.log(Math.min(...numbers));   // 1

// Spread a string into characters
const chars = [..."Hello"];  // ['H', 'e', 'l', 'l', 'o']</code></pre>
</div>

<h3>Spread with Objects</h3>
<p>Object spread creates a shallow copy of an object, or merges properties from multiple objects. Later properties override earlier ones when the same key exists. This is the standard pattern for creating updated copies of objects (common in React state updates) without mutating the original. Use it to add or override properties while preserving all others.</p>
<div class="code-block">
<pre><code>const defaults = { theme: 'light', language: 'en', fontSize: 16 };
const userPrefs = { theme: 'dark', notifications: true };

// Merge: userPrefs overrides defaults for matching keys
const config = { ...defaults, ...userPrefs };
// { theme: 'dark', language: 'en', fontSize: 16, notifications: true }

// Copy an object (shallow)
const original = { name: 'Alex', scores: [90, 85] };
const copy = { ...original };
copy.name = 'Sam';           // doesn't affect original
// BUT: copy.scores is still same array reference (shallow copy)

// Update one property, keep the rest (common in state management)
const user = { id: 1, name: 'Alex', role: 'student' };
const updatedUser = { ...user, role: 'admin' };
// { id: 1, name: 'Alex', role: 'admin' }  — original user unchanged

// Remove a property
const { role, ...userWithoutRole } = user;
// userWithoutRole = { id: 1, name: 'Alex' }</code></pre>
</div>

<h3>Rest Parameters and Destructuring</h3>
<p>Rest parameters collect the remaining function arguments into an array. This replaces the old <code>arguments</code> object (which wasn't a real array and didn't work in arrow functions). Rest must be the last parameter. In destructuring, rest collects everything not explicitly destructured into a new array or object — useful for separating the "head" from the "tail" of a list, or pulling specific properties while keeping the rest.</p>
<div class="code-block">
<pre><code>// Rest parameters: collect remaining args into array
function sum(first, ...rest) {
  return rest.reduce((total, n) => total + n, first);
}
console.log(sum(1, 2, 3, 4, 5));   // 15
console.log(sum(10));               // 10

// Generic logger: first arg is level, rest is data
function log(level, ...messages) {
  console.log(`[${level.toUpperCase()}]`, ...messages);
}
log('info', 'User logged in', { userId: 42 });

// Rest in array destructuring
const [head, ...tail] = [1, 2, 3, 4, 5];
console.log(head);  // 1
console.log(tail);  // [2, 3, 4, 5]

// Rest in object destructuring
const { id, name, ...metadata } = { id: 1, name: 'Alex', role: 'admin', active: true };
console.log(metadata);  // { role: 'admin', active: true }</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use spread to create array/object copies when you need to avoid mutation. Use spread for merging objects and arrays. Use rest parameters instead of the old arguments object in variadic functions. These patterns appear constantly in modern JavaScript — especially in React and state management patterns.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Shallow copy vs deep copy:</strong> Spread creates a shallow copy — nested objects and arrays are still shared references. Changes to nested objects in the copy affect the original. For deep copies, use structuredClone(obj) or JSON.parse(JSON.stringify(obj)).</li>
    <li><strong>Rest parameter not last:</strong> The rest parameter must be the final parameter in a function signature. Writing <code>function f(...a, b)</code> is a syntax error.</li>
    <li><strong>Spreading large objects unnecessarily:</strong> Spreading huge objects to update one property is fine for small objects. For large objects or frequent updates (high-frequency UI), consider more efficient update strategies.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Spread (...) expands arrays/objects into individual elements for merging, copying, and passing as function arguments.</li>
    <li>Object spread copies and merges properties; later properties override earlier ones — the standard pattern for immutable updates.</li>
    <li>Rest collects remaining values; in parameters it replaces the arguments object; in destructuring it gathers remaining properties.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Modules', <<<'HTML'
<h2>Modules</h2>

<p>JavaScript modules let you split code across multiple files and explicitly control what each file exports and imports. Before modules, all JavaScript shared a single global scope — a source of conflicts, naming collisions, and spaghetti code. With modules, each file has its own scope. You choose what to expose with <code>export</code> and what to consume from other files with <code>import</code>. This is the foundation of all modern JavaScript application architecture.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A library with separate rooms for different topics. Each room (module) has its own collection of books (functions, classes, constants). You can bring books from one room into another by checking them out (import). Nobody reads from everyone's pile at once — each room is its own organised space.</p>
</div>

<h3>Named Exports and Imports</h3>
<p>Named exports let you export multiple values from a module. Each exported name must be explicitly imported by name at the import site. You can alias imports with the <code>as</code> keyword. You can import everything from a module as a namespace object with <code>import * as name</code>. Named exports are best for modules that provide several utilities.</p>
<div class="code-block">
<pre><code>// utils.js — named exports
export const PI = 3.14159265;

export function add(a, b) {
  return a + b;
}

export function formatCurrency(amount, currency = 'KES') {
  return `${currency} ${amount.toFixed(2)}`;
}

export class EventEmitter {
  // class implementation
}

// ─────────────────────────────────

// main.js — named imports
import { add, formatCurrency } from './utils.js';

console.log(add(3, 4));                   // 7
console.log(formatCurrency(1500));        // "KES 1500.00"

// Alias to avoid name conflicts
import { add as addNumbers } from './utils.js';

// Import everything as a namespace
import * as utils from './utils.js';
console.log(utils.PI);
console.log(utils.add(1, 2));</code></pre>
</div>

<h3>Default Exports</h3>
<p>A module can have one default export — the primary thing the module provides. Imported without braces, and the import name can be anything. Best used for modules that have one main thing to export: a class, a single function, or a React component. Many style guides prefer named exports for better tooling support (tree-shaking, autocompletion knows what's exported).</p>
<div class="code-block">
<pre><code>// CourseCard.js — default export
export default class CourseCard {
  constructor(title, instructor) {
    this.title = title;
    this.instructor = instructor;
  }

  render() {
    const el = document.createElement('article');
    el.innerHTML = `&lt;h3&gt;${this.title}&lt;/h3&gt;&lt;p&gt;by ${this.instructor}&lt;/p&gt;`;
    return el;
  }
}

// ─────────────────────────────────

// app.js — import default (name can be anything)
import CourseCard from './CourseCard.js';
import Card from './CourseCard.js';  // also valid — same thing

// Mix default and named in one import
import CourseCard, { formatDate, CARD_TYPES } from './CourseCard.js';</code></pre>
</div>

<h3>Dynamic Imports</h3>
<p>Static imports at the top of a file are loaded when the module loads. Dynamic imports (<code>import()</code>) load modules on demand — at the time you need them. This enables code splitting: only load the code for a feature when the user actually uses it. Dynamic import returns a Promise, so use it with async/await. It's the key technique for keeping initial page load fast in large applications.</p>
<div class="code-block">
<pre><code>// Static import: loads when the script loads (always)
import { renderChart } from './chart.js';

// Dynamic import: loads only when the user opens the charts section
document.getElementById('charts-tab').addEventListener('click', async () => {
  try {
    // Module is loaded on demand here
    const { renderChart } = await import('./chart.js');
    renderChart(document.getElementById('chart-container'), data);
  } catch (error) {
    console.error('Failed to load chart module:', error);
  }
});

// In browser script tags — enable modules with type="module"
// &lt;script type="module" src="main.js"&gt;&lt;/script&gt;
// Module scripts are deferred by default, have their own scope,
// use strict mode automatically, and can use import/export</code></pre>
</div>

<h3>When to Use This</h3>
<p>Organise every project with modules from the start. One module per logical unit: utility functions, API calls, UI components, constants. Use named exports for utility libraries; default exports for primary components/classes. Use dynamic imports for large optional features. Always use <code>&lt;script type="module"&gt;</code> for module scripts in the browser.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Circular imports:</strong> Module A importing from B while B imports from A creates circular dependencies, causing hard-to-debug undefined values. Restructure to eliminate cycles — extract shared code to a third module.</li>
    <li><strong>Forgetting type="module" on script tag:</strong> Without <code>type="module"</code>, the browser treats the script as a classic script and import/export statements throw syntax errors.</li>
    <li><strong>Mixing default and named exports inconsistently:</strong> A module should have one clear identity — either a default export or named exports. Mixing them makes imports confusing for consumers of the module.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Named exports allow multiple exports per module; import with matching names (or aliases) in curly braces.</li>
    <li>Default exports provide one primary export per module; imported without braces, name can be anything.</li>
    <li>Dynamic import() loads modules on demand (returning a Promise), enabling code splitting for large applications.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Classes', <<<'HTML'
<h2>Classes</h2>

<p>JavaScript classes are syntactic sugar over the prototype-based inheritance model, introduced in ES6 to make object-oriented programming more readable. Classes define blueprints for creating objects with shared behaviour. They group related data (properties) and behaviour (methods) together, enable inheritance through <code>extends</code>, and support private fields. Understanding classes is important for reading modern JavaScript libraries, working with frameworks, and organising complex application state.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>A cookie cutter. The class is the cutter — it defines the shape. Each cookie you make (instance) has that shape, but with different decorations (different property values). All cookies from the same cutter share the same fundamental shape (methods defined on the class).</p>
</div>

<h3>Defining and Instantiating Classes</h3>
<p>A class is defined with the <code>class</code> keyword. The <code>constructor</code> method initialises instances — it runs when you call <code>new ClassName()</code>. Methods are defined directly inside the class body (no function keyword needed). Properties are set on <code>this</code> inside the constructor. Class instances are created with <code>new</code>.</p>
<div class="code-block">
<pre><code>class Course {
  constructor(title, instructor, maxStudents = 100) {
    this.title = title;
    this.instructor = instructor;
    this.maxStudents = maxStudents;
    this.enrolledStudents = [];
    this.createdAt = new Date();
  }

  enroll(student) {
    if (this.enrolledStudents.length >= this.maxStudents) {
      throw new Error(`${this.title} is full`);
    }
    this.enrolledStudents.push(student);
    return this;  // enables method chaining
  }

  getInfo() {
    return `${this.title} by ${this.instructor} (${this.enrolledStudents.length} enrolled)`;
  }

  get enrollment() {
    return this.enrolledStudents.length;  // getter: accessed as a property
  }
}

// Create instances
const htmlCourse = new Course('HTML Basics', 'Alex');
htmlCourse.enroll('Sam').enroll('Jordan');  // method chaining
console.log(htmlCourse.getInfo());
console.log(htmlCourse.enrollment);  // 2 — via getter</code></pre>
</div>

<h3>Inheritance with extends</h3>
<p>Classes can inherit from other classes using <code>extends</code>. The child class gets all the parent's methods. Override a parent method by defining a new method with the same name. Call the parent's version with <code>super.methodName()</code>. In the constructor, call <code>super()</code> before using <code>this</code> — this initialises the parent class first. This enables a hierarchy of related classes sharing common behaviour.</p>
<div class="code-block">
<pre><code>// Base class
class Animal {
  constructor(name, sound) {
    this.name = name;
    this.sound = sound;
  }

  speak() {
    return `${this.name} says ${this.sound}!`;
  }

  toString() {
    return `Animal: ${this.name}`;
  }
}

// Child class: inherits from Animal
class Dog extends Animal {
  constructor(name, breed) {
    super(name, 'Woof');   // must call super() before using this
    this.breed = breed;
  }

  // Override parent method
  speak() {
    return `${super.speak()} *wags tail*`;  // call parent version
  }

  fetch(item) {
    return `${this.name} fetches the ${item}!`;
  }
}

const myDog = new Dog('Buddy', 'Labrador');
console.log(myDog.speak());      // "Buddy says Woof! *wags tail*"
console.log(myDog.fetch('ball')); // "Buddy fetches the ball!"
console.log(myDog instanceof Dog);    // true
console.log(myDog instanceof Animal); // true (inherited)</code></pre>
</div>

<h3>Static Methods and Private Fields</h3>
<p>Static methods and properties belong to the class itself, not instances. Call them on the class name, not on an instance. Useful for factory methods, utility functions, and configuration. Private fields (prefixed with <code>#</code>) are only accessible inside the class — true encapsulation. They're not accessible from outside or from subclasses. Private fields must be declared at the top of the class body.</p>
<div class="code-block">
<pre><code>class UserAccount {
  // Private fields — only accessible inside the class
  #password;
  #loginAttempts = 0;

  constructor(username, password) {
    this.username = username;
    this.#password = this.#hashPassword(password);
  }

  #hashPassword(pwd) {
    // Private method — internal only
    return btoa(pwd);  // simple demo — use bcrypt in real apps
  }

  login(password) {
    if (this.#loginAttempts >= 3) {
      throw new Error('Account locked');
    }
    if (btoa(password) === this.#password) {
      this.#loginAttempts = 0;
      return true;
    }
    this.#loginAttempts++;
    return false;
  }

  // Static factory method
  static createAdmin(username, password) {
    const account = new UserAccount(username, password);
    account.role = 'admin';
    return account;
  }
}

const admin = UserAccount.createAdmin('alex', 'secret123');
console.log(admin.username);     // "alex"
// console.log(admin.#password); // ❌ SyntaxError — private</code></pre>
</div>

<h3>When to Use This</h3>
<p>Use classes for complex objects with shared behaviour and state — UI components, data models, service classes. For simple data containers, plain objects are fine. Prefer composition (objects containing other objects) over deep inheritance chains — inherit only when there's a genuine "is-a" relationship. Use private fields for internal state that shouldn't be modified externally.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>Forgetting new keyword:</strong> Calling a class without new (e.g., Course('title')) throws a TypeError. Classes are not callable as regular functions.</li>
    <li><strong>Forgetting super() in extended constructors:</strong> If you define a constructor in a subclass, you must call super() before accessing this. Forgetting it throws a ReferenceError.</li>
    <li><strong>Deep inheritance hierarchies:</strong> More than 2-3 levels of inheritance creates code that's hard to follow and change. Favour composition (an object that has other objects) over deep inheritance.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Classes define blueprints for objects; the constructor initialises instances; methods are shared across all instances.</li>
    <li>extends enables inheritance; super() in the constructor calls the parent; super.method() calls an overridden parent method.</li>
    <li>Private fields (#name) provide true encapsulation; static methods belong to the class, not instances, and are called on the class name.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

updateLesson($pdo, 'Best Practices', <<<'HTML'
<h2>Best Practices</h2>

<p>JavaScript best practices are the conventions and patterns that experienced developers have converged on after years of building and maintaining real applications. Following them produces code that's readable, debuggable, secure, and performant. They're not arbitrary rules — each one solves a specific recurring problem. Internalising them as habits means better code from the first line.</p>

<div class="analogy-box">
  <h4><i class="bi bi-lightbulb"></i> Think of it like...</h4>
  <p>Traffic rules. You could drive without them, and you might be fine most of the time. But the rules exist because of real accidents that happened without them — they encode lessons learned the hard way. JavaScript best practices are traffic rules for your codebase: follow them to avoid the categories of problems that have damaged real applications.</p>
</div>

<h3>Code Clarity and Naming</h3>
<p>Code is read far more than it's written. The most impactful practice is meaningful naming. Variables should describe what they hold; functions should describe what they do (verb + noun). Avoid single-letter variable names outside of loop counters and short arrow function arguments. Use consistent naming conventions: camelCase for variables and functions, PascalCase for classes and constructors, UPPER_SNAKE_CASE for constants that are truly constant across the application.</p>
<div class="code-block">
<pre><code>// ❌ Unclear
const x = users.filter(u => u.s > 3);
const t = new Date().getTime();
function proc(d) { return d.map(i => i * 2); }

// ✅ Descriptive
const activeUsers = users.filter(user => user.subscriptionLevel > 3);
const currentTimestamp = Date.now();
function doubleValues(numbers) { return numbers.map(n => n * 2); }

// Boolean naming: use is/has/can/should prefix
const isLoading = true;
const hasPermission = false;
const canEdit = user.role === 'admin';

// Constants
const MAX_RETRY_ATTEMPTS = 3;
const API_BASE_URL = 'https://api.example.com';</code></pre>
</div>

<h3>Guard Clauses and Pure Functions</h3>
<p>Guard clauses return early from a function when preconditions aren't met, keeping the main logic "happy path" flat and readable. Pure functions always return the same output for the same input, and have no side effects — they don't modify external state, make API calls, or produce different results on different runs. Pure functions are easy to test, reason about, and reuse.</p>
<div class="code-block">
<pre><code>// ❌ Nested conditions
function processOrder(order) {
  if (order) {
    if (order.items && order.items.length > 0) {
      if (order.userId) {
        // actual logic buried 3 levels deep
      }
    }
  }
}

// ✅ Guard clauses: return early, keep happy path flat
function processOrder(order) {
  if (!order) throw new TypeError('Order is required');
  if (!order.items?.length) throw new Error('Order has no items');
  if (!order.userId) throw new Error('Order must have a user');

  // Happy path — no nesting
  return calculateTotal(order.items);
}

// ✅ Pure function: same input = same output, no side effects
function calculateTotal(items) {
  return items.reduce((total, item) => total + item.price * item.quantity, 0);
}

// ❌ Impure: modifies external state
function calculateTotalImpure(items) {
  lastTotal = items.reduce(...);  // modifies external variable
  return lastTotal;
}</code></pre>
</div>

<h3>Security Practices</h3>
<p>Web applications are targets for attacks. Key JavaScript security practices: never inject user input into innerHTML (XSS vulnerability), validate and sanitise all data before using it, don't store sensitive data (passwords, tokens) in localStorage where any JS can access it, use HTTPS, and never expose API keys in client-side code. The XSS and injection attack categories have compromised millions of websites — one innerHTML assignment with user data is enough.</p>
<div class="code-block">
<pre><code>// ❌ XSS vulnerability: user input in innerHTML
const comment = userInput;  // could be: &lt;script&gt;stealCookies()&lt;/script&gt;
element.innerHTML = comment;  // executes the script!

// ✅ Safe: textContent never executes scripts
element.textContent = comment;

// ✅ Or sanitise HTML if you need to render some markup
// Use DOMPurify library: element.innerHTML = DOMPurify.sanitize(comment);

// Don't store sensitive data in localStorage
// ❌ Bad
localStorage.setItem('password', userPassword);
localStorage.setItem('api_key', secretKey);

// ✅ Store auth tokens in httpOnly cookies (server sets these)
// Use sessionStorage for temporary session data (cleared on tab close)

// Validate user input before processing
function safeParseAge(input) {
  const age = parseInt(input, 10);
  if (isNaN(age) || age < 0 || age > 150) {
    throw new RangeError('Invalid age');
  }
  return age;
}</code></pre>
</div>

<h3>When to Use This</h3>
<p>Apply these practices from the first line of every project. Use a linter (ESLint) to automatically enforce many of them. Write small, single-purpose functions. Handle all errors. Test your code. These habits compound — code written with good practices from the start is far easier to extend and debug months later.</p>

<div class="mistakes-box">
  <h4><i class="bi bi-exclamation-triangle"></i> Common Mistakes</h4>
  <ul>
    <li><strong>No linting:</strong> Relying on manual review to catch errors is unreliable. Install ESLint and a style guide (like eslint-config-airbnb or standard) — let the tool catch problems automatically on every save.</li>
    <li><strong>Console.log left in production:</strong> Debug console.logs slow down performance, expose implementation details, and clutter the console for real users. Remove them or use a proper logging library that can be toggled off.</li>
    <li><strong>Mutating function arguments:</strong> Modifying objects or arrays passed to a function causes unexpected side effects at the call site. Treat function arguments as read-only; create copies with spread if you need to modify them.</li>
  </ul>
</div>

<div class="takeaways-box">
  <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
  <ul>
    <li>Meaningful naming, guard clauses, and pure functions produce readable, testable, predictable code.</li>
    <li>Never inject user input into innerHTML — use textContent or a sanitisation library to prevent XSS attacks.</li>
    <li>Use ESLint to automatically enforce code quality rules; treat security practices as mandatory, not optional.</li>
  </ul>
</div>
HTML, $lessonsUpdated);

// =====================================================================
// CODING EXERCISES — HTML Course (20 exercises)
// =====================================================================

addExercise($pdo, 'How the Web Works',
    'Draw the Request-Response Cycle',
    'Demonstrate your understanding of the client-server model by building a simple annotated HTML diagram.',
    "1. Create an HTML page with a heading: 'How the Web Works'\n2. Add a numbered ordered list describing the 5 steps from typing a URL to seeing a page\n3. Each list item should be wrapped in a <strong> tag for the step name, followed by a short explanation\n4. Add a paragraph at the bottom explaining what HTTP status 200 means\n5. Give the page a proper title tag that reads 'The Request-Response Cycle'",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><!-- Your title here --></title>
</head>
<body>
  <h1>How the Web Works</h1>
  <!-- Add your ordered list here -->
  <!-- Add your paragraph about HTTP 200 here -->
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>The Request-Response Cycle</title>
</head>
<body>
  <h1>How the Web Works</h1>
  <ol>
    <li><strong>You type a URL:</strong> The browser receives your request and looks up the domain.</li>
    <li><strong>DNS Lookup:</strong> The domain name is translated to an IP address by a DNS server.</li>
    <li><strong>Browser sends HTTP request:</strong> The browser connects to the server and requests the page.</li>
    <li><strong>Server responds:</strong> The server sends back an HTML file along with a status code.</li>
    <li><strong>Browser renders the page:</strong> The browser parses the HTML and displays the content.</li>
  </ol>
  <p><strong>HTTP 200 OK</strong> means the server successfully found and returned the requested resource.</p>
</body>
</html>',
    'An ordered list uses the <ol> tag|Each step is a <li> (list item) inside the <ol>|The <strong> tag makes text bold and signals importance',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Setting Up Your Development Environment',
    'Build a Project Welcome Page',
    'Create a well-structured HTML project page that demonstrates proper document setup and folder awareness.',
    "1. Create an HTML page with the correct DOCTYPE and all required meta tags (charset and viewport)\n2. Set the page title to 'My Web Project'\n3. Add an h1 heading: 'Welcome to My Project'\n4. Add three paragraphs: one describing what the project is, one describing what you'll learn, one with a note about the file structure\n5. Add a comment inside the HTML explaining where your CSS file would be linked",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Add required meta tags here -->
  <title><!-- Your title here --></title>
</head>
<body>
  <!-- Add your content here -->
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Web Project</title>
  <!-- CSS would be linked here: <link rel="stylesheet" href="css/style.css"> -->
</head>
<body>
  <h1>Welcome to My Project</h1>
  <p>This project is a personal portfolio website showcasing my web development skills.</p>
  <p>I will learn HTML structure, CSS styling, and JavaScript interactivity by building this project.</p>
  <p>The project uses a standard folder structure: HTML files in the root, CSS in a css/ folder, and JavaScript in a js/ folder.</p>
</body>
</html>',
    'The charset meta tag belongs inside the <head> element|The viewport meta tag is required for mobile devices|HTML comments use <!-- comment --> syntax',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'HTML Document Structure',
    'Complete a Skeleton HTML Document',
    'Fill in a partially-complete HTML document with the required structural elements.',
    "1. Add the correct DOCTYPE declaration at the very top\n2. Add the html element with lang=\"en\"\n3. Complete the head with: charset meta tag, viewport meta tag, and a descriptive title\n4. Add a visible h1 heading and a paragraph inside the body\n5. Link a CSS file called 'style.css' from a 'css/' folder in the head",
    '<!-- Your DOCTYPE here -->
<!-- Your html tag here -->
  <!-- head section -->
    <!-- meta charset -->
    <!-- meta viewport -->
    <!-- title: My First Page -->
    <!-- link to css/style.css -->
  <!-- end head -->
  <!-- body section -->
    <!-- h1: Hello, World! -->
    <!-- p: This is my first HTML page. -->
  <!-- end body -->
<!-- end html -->',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My First Page</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <h1>Hello, World!</h1>
  <p>This is my first HTML page.</p>
</body>
</html>',
    'DOCTYPE goes before the html tag, not inside it|The <link> element for CSS uses rel="stylesheet" and href for the file path|Everything visible goes inside <body>, not <head>',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Headings, Paragraphs & Text',
    'Write a Mini Blog Post',
    'Create a properly structured blog article using appropriate heading levels, paragraphs, and inline text elements.',
    "1. Create a blog post about any topic you like with an h1 for the article title\n2. Add two sections each with an h2 heading\n3. Write at least one paragraph under each h2\n4. Use <strong> at least once for something important\n5. Use <em> at least once for something that should be stressed\n6. Include a <blockquote> with a short quote related to your topic",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Blog Post</title>
</head>
<body>
  <!-- h1: Your article title -->

  <!-- h2: First section title -->
  <!-- p: First paragraph -->

  <!-- h2: Second section title -->
  <!-- p: Second paragraph with strong and em -->

  <!-- blockquote: a relevant quote -->
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Blog Post</title>
</head>
<body>
  <h1>Why Learning HTML is the Best First Step</h1>

  <h2>The Foundation of the Web</h2>
  <p>Every website you have ever visited is built on HTML. It is the <strong>fundamental building block</strong> of web content, providing the structure that browsers understand and render.</p>

  <h2>It Is Easier Than You Think</h2>
  <p>Many beginners are intimidated by coding, but HTML is <em>remarkably readable</em>. Tags are descriptive words in angle brackets, and the structure mirrors the content hierarchy you already use in documents.</p>

  <blockquote>
    <p>"The best time to learn HTML was yesterday. The second best time is today."</p>
  </blockquote>
</body>
</html>',
    'Use only one h1 per page — it is the main title|Strong means importance; em means emphasis (stress)|Headings should follow logical order: h1 then h2 then h3',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Links and Navigation',
    'Build a Navigation Menu',
    'Create a website navigation bar with working links using semantic HTML.',
    "1. Create a page with a <nav> element containing an unordered list\n2. Add 4 navigation links: Home, About, Courses, Contact\n3. The Home link should use href=\"index.html\" (relative)\n4. The About link should open to href=\"about.html\"\n5. Add a 5th link to an external site (like https://developer.mozilla.org) that opens in a new tab — include rel=\"noopener noreferrer\"\n6. Below the nav, add a section with a heading and an anchor link that jumps to an id on the same page",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Navigation Exercise</title>
</head>
<body>
  <!-- nav with ul and li and a elements here -->

  <!-- A section with an id="about" for anchor link testing -->
  <section id="about">
    <h2>About Section</h2>
    <p>This is the about section.</p>
  </section>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Navigation Exercise</title>
</head>
<body>
  <nav>
    <ul>
      <li><a href="index.html">Home</a></li>
      <li><a href="about.html">About</a></li>
      <li><a href="courses.html">Courses</a></li>
      <li><a href="contact.html">Contact</a></li>
      <li><a href="https://developer.mozilla.org" target="_blank" rel="noopener noreferrer">MDN Docs</a></li>
    </ul>
  </nav>

  <a href="#about">Jump to About Section</a>

  <section id="about">
    <h2>About Section</h2>
    <p>This is the about section.</p>
  </section>
</body>
</html>',
    'The <nav> element wraps the navigation; <ul> contains the list of links|External links in new tabs need rel="noopener noreferrer" for security|Anchor links use href="#id-name" and the target needs a matching id attribute',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Introduction to HTML Forms',
    'Build a Contact Form',
    'Create a complete contact form with proper labels, inputs, and a submit button.',
    "1. Create a form with action=\"#\" and method=\"post\"\n2. Add a text input for Full Name with a connected label\n3. Add an email input for Email Address with a connected label\n4. Add a textarea for Message (5 rows) with a connected label\n5. Add a select dropdown for Subject with 4 options: General, Support, Billing, Other\n6. Add a submit button labelled 'Send Message'\n7. Mark the Name and Email fields as required",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Form</title>
</head>
<body>
  <h1>Contact Us</h1>
  <form action="#" method="post">
    <!-- name field with label -->
    <!-- email field with label -->
    <!-- subject select with label -->
    <!-- message textarea with label -->
    <!-- submit button -->
  </form>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Form</title>
</head>
<body>
  <h1>Contact Us</h1>
  <form action="#" method="post">
    <label for="name">Full Name *</label>
    <input type="text" id="name" name="name" required placeholder="Your full name">

    <label for="email">Email Address *</label>
    <input type="email" id="email" name="email" required placeholder="you@example.com">

    <label for="subject">Subject</label>
    <select id="subject" name="subject">
      <option value="general">General</option>
      <option value="support">Support</option>
      <option value="billing">Billing</option>
      <option value="other">Other</option>
    </select>

    <label for="message">Message</label>
    <textarea id="message" name="message" rows="5" placeholder="Your message..."></textarea>

    <button type="submit">Send Message</button>
  </form>
</body>
</html>',
    'Connect label to input using matching for and id attributes|The name attribute is what gets submitted — do not forget it|required attribute triggers browser validation',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Input Types',
    'Build a User Registration Form',
    'Create a registration form using at least 6 different input types.',
    "1. Create a registration form with: text input for username, email input, password input, number input for age (min 13, max 120), date input for date of birth\n2. Add a group of radio buttons for skill level: Beginner, Intermediate, Advanced (use the same name attribute)\n3. Add checkboxes for interests: HTML, CSS, JavaScript (users can select multiple)\n4. Add a range input for hours available per week (1-40, default 10)\n5. Add an output element next to the range to display its current value\n6. Add a submit button",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Form</title>
</head>
<body>
  <h1>Create Account</h1>
  <form>
    <!-- username text input -->
    <!-- email input -->
    <!-- password input -->
    <!-- age number input -->
    <!-- dob date input -->
    <!-- skill level radio group -->
    <!-- interests checkboxes -->
    <!-- hours range + output -->
    <!-- submit button -->
  </form>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Form</title>
</head>
<body>
  <h1>Create Account</h1>
  <form>
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" minlength="8" required>

    <label for="age">Age</label>
    <input type="number" id="age" name="age" min="13" max="120">

    <label for="dob">Date of Birth</label>
    <input type="date" id="dob" name="dob">

    <fieldset>
      <legend>Skill Level</legend>
      <label><input type="radio" name="level" value="beginner"> Beginner</label>
      <label><input type="radio" name="level" value="intermediate"> Intermediate</label>
      <label><input type="radio" name="level" value="advanced"> Advanced</label>
    </fieldset>

    <fieldset>
      <legend>Interests</legend>
      <label><input type="checkbox" name="interests" value="html"> HTML</label>
      <label><input type="checkbox" name="interests" value="css"> CSS</label>
      <label><input type="checkbox" name="interests" value="js"> JavaScript</label>
    </fieldset>

    <label for="hours">Hours available per week: <output id="hours-display">10</output></label>
    <input type="range" id="hours" name="hours" min="1" max="40" value="10"
           oninput="document.getElementById(\'hours-display\').textContent = this.value">

    <button type="submit">Create Account</button>
  </form>
</body>
</html>',
    'Radio buttons in the same group must share the same name attribute|Checkboxes allow multiple selections; radios allow only one|The output element displays computed values — connect it to a range with oninput',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Form Validation',
    'Add HTML5 Validation to a Signup Form',
    'Apply built-in HTML5 validation attributes to prevent incomplete form submission.',
    "1. Create a signup form with: username (required, minlength 3, maxlength 20, pattern [A-Za-z0-9]+), email (required), password (required, minlength 8), age (required, min 13, max 120)\n2. Add a title attribute to the username input explaining the allowed characters\n3. Add a placeholder to each input as a format hint\n4. Add a note above the form: 'Fields marked * are required'\n5. Make sure the submit button says 'Sign Up'",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup Validation</title>
</head>
<body>
  <h1>Sign Up</h1>
  <!-- required note -->
  <form action="#" method="post">
    <!-- username with validation -->
    <!-- email with validation -->
    <!-- password with validation -->
    <!-- age with validation -->
    <!-- submit button -->
  </form>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup Validation</title>
</head>
<body>
  <h1>Sign Up</h1>
  <p><small>Fields marked * are required</small></p>
  <form action="#" method="post">
    <label for="username">Username *</label>
    <input type="text" id="username" name="username"
           required minlength="3" maxlength="20"
           pattern="[A-Za-z0-9]+"
           title="Only letters and numbers, 3-20 characters"
           placeholder="e.g. alex123">

    <label for="email">Email *</label>
    <input type="email" id="email" name="email" required placeholder="you@example.com">

    <label for="password">Password *</label>
    <input type="password" id="password" name="password" required minlength="8" placeholder="At least 8 characters">

    <label for="age">Age *</label>
    <input type="number" id="age" name="age" required min="13" max="120" placeholder="Your age">

    <button type="submit">Sign Up</button>
  </form>
</body>
</html>',
    'The pattern attribute uses a regular expression — [A-Za-z0-9]+ means letters and numbers only|The title attribute shows as a tooltip and in validation error messages|minlength and maxlength control character count; min and max control number range',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Form Accessibility & UX',
    'Make a Form Accessible',
    'Improve an existing form to meet basic accessibility standards.',
    "1. Start with the starter code below — it has accessibility issues\n2. Connect each label to its input using matching for and id attributes\n3. Wrap the radio buttons in a fieldset with an appropriate legend\n4. Add aria-describedby to the password input pointing to a hint span with id='password-hint'\n5. Add the hint text 'Must be at least 8 characters' in that span\n6. Add autocomplete attributes: name='name', email='email', password='new-password'",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Accessible Form</title>
</head>
<body>
  <h1>Sign Up</h1>
  <form>
    <!-- Missing: for/id connection -->
    <label>Full Name</label>
    <input type="text" name="name">

    <label>Email</label>
    <input type="email" name="email">

    <label>Password</label>
    <input type="password" name="password">

    <!-- Missing: fieldset and legend -->
    <label><input type="radio" name="role" value="student"> Student</label>
    <label><input type="radio" name="role" value="teacher"> Teacher</label>

    <button type="submit">Register</button>
  </form>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Accessible Form</title>
</head>
<body>
  <h1>Sign Up</h1>
  <form>
    <label for="name">Full Name</label>
    <input type="text" id="name" name="name" autocomplete="name">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" autocomplete="email">

    <label for="password">Password</label>
    <input type="password" id="password" name="password"
           autocomplete="new-password"
           aria-describedby="password-hint">
    <span id="password-hint">Must be at least 8 characters</span>

    <fieldset>
      <legend>I am a:</legend>
      <label><input type="radio" name="role" value="student"> Student</label>
      <label><input type="radio" name="role" value="teacher"> Teacher</label>
    </fieldset>

    <button type="submit">Register</button>
  </form>
</body>
</html>',
    'Connect label to input with for on label and matching id on input|aria-describedby links a hint/error message to an input for screen readers|fieldset and legend group related controls — essential for radio/checkbox groups',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Advanced Form Features',
    'Build a Smart Search Form',
    'Use datalist, output, and correct button types to build a feature-rich search form.',
    "1. Create a search input with a datalist offering 5 programming language suggestions: HTML, CSS, JavaScript, Python, PHP\n2. Add a range input for difficulty filter (1-5, default 3) with a connected output element showing the current value\n3. Add two buttons: one submit button labelled 'Search' and one type='button' labelled 'Clear'\n4. Add an onchange handler on the range input that updates the output element\n5. Give each element a proper label",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Smart Search</title>
</head>
<body>
  <h1>Course Search</h1>
  <form action="#" method="get">
    <!-- search input with datalist -->
    <!-- difficulty range with output -->
    <!-- Search submit button -->
    <!-- Clear type=button -->
  </form>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Smart Search</title>
</head>
<body>
  <h1>Course Search</h1>
  <form action="#" method="get">
    <label for="topic">Topic</label>
    <input type="text" id="topic" name="topic" list="topics" placeholder="Search courses...">
    <datalist id="topics">
      <option value="HTML">
      <option value="CSS">
      <option value="JavaScript">
      <option value="Python">
      <option value="PHP">
    </datalist>

    <label for="difficulty">
      Difficulty: <output id="difficulty-display">3</output>
    </label>
    <input type="range" id="difficulty" name="difficulty" min="1" max="5" value="3"
           oninput="document.getElementById(\'difficulty-display\').textContent = this.value">

    <button type="submit">Search</button>
    <button type="button" onclick="document.querySelector(\'form\').reset()">Clear</button>
  </form>
</body>
</html>',
    'Connect datalist to input using the list attribute on input and matching id on datalist|type="button" does NOT submit the form; always specify button type explicitly|output displays computed results — connect to a range with oninput',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'What is Semantic HTML?',
    'Rewrite a Page Using Semantic Elements',
    'Convert a div-heavy page into one that uses appropriate semantic HTML5 elements.',
    "1. The starter code uses divs for everything — replace them with semantic elements\n2. Replace the header div with <header>\n3. Replace the navigation div with <nav>\n4. Replace the main content div with <main>\n5. Replace the blog post div with <article>\n6. Replace the sidebar div with <aside>\n7. Replace the footer div with <footer>",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Semantic Rewrite</title>
</head>
<body>
  <div class="header">
    <h1>My Blog</h1>
    <div class="nav">
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">About</a></li>
      </ul>
    </div>
  </div>
  <div class="main">
    <div class="article">
      <h2>My First Post</h2>
      <p>Welcome to my blog!</p>
    </div>
    <div class="sidebar">
      <h3>Recent Posts</h3>
    </div>
  </div>
  <div class="footer">
    <p>&copy; 2025 My Blog</p>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Semantic Rewrite</title>
</head>
<body>
  <header>
    <h1>My Blog</h1>
    <nav>
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">About</a></li>
      </ul>
    </nav>
  </header>
  <main>
    <article>
      <h2>My First Post</h2>
      <p>Welcome to my blog!</p>
    </article>
    <aside>
      <h3>Recent Posts</h3>
    </aside>
  </main>
  <footer>
    <p>&copy; 2025 My Blog</p>
  </footer>
</body>
</html>',
    'header, nav, main, article, aside, and footer are the six main landmark elements|Semantic elements have the same visual output as divs by default — the difference is meaning|main should appear only once per page',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Page Structure Elements',
    'Build a Blog Page Layout',
    'Create a blog page using correct semantic structure elements for page layout.',
    "1. Create a page with a site <header> containing a logo (any text) and a <nav> with 3 links\n2. Add a <main> section containing one <article> with its own <header> (title + time element), body paragraphs, and a <footer> with tags\n3. Add an <aside> with a 'Related Articles' section containing a short list of 3 links\n4. Add a page-level <footer> with copyright text\n5. Use the <time> element with a datetime attribute for the article's publish date",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blog Page</title>
</head>
<body>
  <!-- site header with nav -->
  <!-- main with article and aside -->
  <!-- site footer -->
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blog Page</title>
</head>
<body>
  <header>
    <a href="/"><strong>DevBlog</strong></a>
    <nav>
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/about">About</a></li>
        <li><a href="/contact">Contact</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <article>
      <header>
        <h1>Getting Started with HTML</h1>
        <p>Published on <time datetime="2025-03-15">March 15, 2025</time></p>
      </header>
      <p>HTML is the foundation of every website you visit. Learning it opens doors to the entire world of web development.</p>
      <p>In this article, we cover the basics of document structure and semantic elements.</p>
      <footer>
        <p>Tags: HTML, Web Development, Beginner</p>
      </footer>
    </article>

    <aside>
      <h2>Related Articles</h2>
      <ul>
        <li><a href="#">Introduction to CSS</a></li>
        <li><a href="#">JavaScript Basics</a></li>
        <li><a href="#">Web Development Roadmap</a></li>
      </ul>
    </aside>
  </main>

  <footer>
    <p>&copy; 2025 DevBlog. All rights reserved.</p>
  </footer>
</body>
</html>',
    'header and footer can be used multiple times — each one is relative to its parent element|The time element takes a datetime attribute for machine-readable dates (YYYY-MM-DD)|article is for standalone content; aside is for supplementary content',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Lists and Tables',
    'Build a Course Comparison Table',
    'Create a properly structured comparison table and accompanying lists.',
    "1. Create an unordered list of 3 course categories (Frontend, Backend, Database)\n2. Create an ordered list of 5 steps to enroll in a course\n3. Create a table comparing 3 courses: HTML Basics, CSS Fundamentals, JS Essentials — with columns: Course Name, Duration, Level, Price\n4. Include a caption on the table: 'Available Courses'\n5. Use thead with th elements (with scope='col') and tbody with td elements",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lists and Tables</title>
</head>
<body>
  <h2>Course Categories</h2>
  <!-- unordered list here -->

  <h2>How to Enroll</h2>
  <!-- ordered list here -->

  <h2>Course Comparison</h2>
  <!-- table with caption, thead, tbody here -->
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lists and Tables</title>
</head>
<body>
  <h2>Course Categories</h2>
  <ul>
    <li>Frontend Development</li>
    <li>Backend Development</li>
    <li>Database Management</li>
  </ul>

  <h2>How to Enroll</h2>
  <ol>
    <li>Create an account on our platform</li>
    <li>Browse the course catalogue</li>
    <li>Click "Enroll" on your chosen course</li>
    <li>Complete the payment process</li>
    <li>Start learning immediately!</li>
  </ol>

  <h2>Course Comparison</h2>
  <table>
    <caption>Available Courses</caption>
    <thead>
      <tr>
        <th scope="col">Course Name</th>
        <th scope="col">Duration</th>
        <th scope="col">Level</th>
        <th scope="col">Price</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>HTML Basics</td>
        <td>4 weeks</td>
        <td>Beginner</td>
        <td>Free</td>
      </tr>
      <tr>
        <td>CSS Fundamentals</td>
        <td>6 weeks</td>
        <td>Beginner</td>
        <td>KES 999</td>
      </tr>
      <tr>
        <td>JS Essentials</td>
        <td>8 weeks</td>
        <td>Intermediate</td>
        <td>KES 1,499</td>
      </tr>
    </tbody>
  </table>
</body>
</html>',
    'table > caption > thead > tbody is the correct structure for accessible tables|th with scope="col" tells screen readers this is a column header|ul is for unordered items; ol is for sequential steps',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Images and Media',
    'Build an Image Gallery Card',
    'Create a media card with a properly set up image and video element.',
    "1. Add an img element with: a real image URL (use https://picsum.photos/400/250), a descriptive alt text, width=400, height=250, and loading='lazy'\n2. Below the image, add a figure element wrapping a second image with a figcaption describing it\n3. Add a video element with controls, width=400, a poster image (you can reuse the picsum URL), and an mp4 source (you can use a placeholder URL)\n4. Add fallback text inside the video element for browsers that don't support it",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Media Gallery</title>
</head>
<body>
  <h1>Media Gallery</h1>

  <!-- img with alt, width, height, loading=lazy -->

  <!-- figure with img and figcaption -->

  <!-- video with controls, poster, source, and fallback text -->
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Media Gallery</title>
</head>
<body>
  <h1>Media Gallery</h1>

  <img src="https://picsum.photos/400/250"
       alt="A randomly selected nature photograph from Picsum Photos"
       width="400"
       height="250"
       loading="lazy">

  <figure>
    <img src="https://picsum.photos/400/200?random=2"
         alt="An abstract texture photograph"
         width="400"
         height="200">
    <figcaption>Abstract textures from the Picsum photo library.</figcaption>
  </figure>

  <video controls width="400" poster="https://picsum.photos/400/225">
    <source src="sample-video.mp4" type="video/mp4">
    <p>Your browser does not support HTML5 video. <a href="sample-video.mp4">Download the video</a>.</p>
  </video>
</body>
</html>',
    'Always include alt text on images — empty alt (alt="") for decorative images|Specify width and height to prevent layout shift while loading|video needs controls attribute for the play/pause buttons to show',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'ARIA and Accessibility',
    'Add ARIA to Interactive Components',
    'Improve the accessibility of a custom accordion component using ARIA attributes.',
    "1. Start with the provided accordion HTML\n2. Add role='button' to the toggle divs (or better: convert them to button elements)\n3. Add aria-expanded='false' to each toggle button\n4. Add aria-controls pointing to the matching panel id\n5. Add id attributes to each panel div\n6. Add role='region' and aria-labelledby to each panel, pointing to its button\n7. Add a visually-hidden class span inside one button that says '(click to expand)'",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ARIA Accordion</title>
</head>
<body>
  <h1>FAQ</h1>

  <div class="accordion">
    <div class="toggle">What is HTML?</div>
    <div class="panel">
      <p>HTML is the standard markup language for creating web pages.</p>
    </div>

    <div class="toggle">What is CSS?</div>
    <div class="panel">
      <p>CSS is the language used to style HTML documents.</p>
    </div>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ARIA Accordion</title>
</head>
<body>
  <h1>FAQ</h1>

  <div class="accordion">
    <button class="toggle"
            aria-expanded="false"
            aria-controls="panel-1"
            id="btn-1">
      What is HTML?
      <span class="visually-hidden">(click to expand)</span>
    </button>
    <div id="panel-1"
         role="region"
         aria-labelledby="btn-1"
         hidden>
      <p>HTML is the standard markup language for creating web pages.</p>
    </div>

    <button class="toggle"
            aria-expanded="false"
            aria-controls="panel-2"
            id="btn-2">
      What is CSS?
    </button>
    <div id="panel-2"
         role="region"
         aria-labelledby="btn-2"
         hidden>
      <p>CSS is the language used to style HTML documents.</p>
    </div>
  </div>
</body>
</html>',
    'Use a real button element instead of a div for keyboard and screen reader access|aria-expanded tells screen readers whether the panel is open or closed|aria-controls links a button to the panel it controls',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'HTML5 Media Elements',
    'Build a Video Player Card',
    'Create a styled video card with caption tracks and custom attributes.',
    "1. Create a video element with: controls, width=640, preload='metadata', and a poster image URL (use picsum)\n2. Add two source elements: one mp4 and one webm (placeholder URLs are fine)\n3. Add a track element with kind='captions', srclang='en', label='English', and default attribute\n4. Add a paragraph fallback inside the video\n5. Wrap everything in a figure element with a figcaption describing the video content",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Video Player</title>
</head>
<body>
  <h1>Course Video</h1>
  <!-- figure wrapping video, source elements, track, and fallback -->
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Video Player</title>
</head>
<body>
  <h1>Course Video</h1>

  <figure>
    <video controls
           width="640"
           preload="metadata"
           poster="https://picsum.photos/640/360">

      <source src="intro-lesson.mp4"  type="video/mp4">
      <source src="intro-lesson.webm" type="video/webm">

      <track kind="captions"
             src="captions-en.vtt"
             srclang="en"
             label="English"
             default>

      <p>Your browser does not support HTML5 video.
         <a href="intro-lesson.mp4">Download the video</a> instead.</p>
    </video>

    <figcaption>Introduction to HTML — Module 1, Lesson 1 (12 minutes)</figcaption>
  </figure>
</body>
</html>',
    'Provide multiple source formats (mp4 + webm) for cross-browser compatibility|preload="metadata" loads only the duration and dimensions, not the full video|caption tracks (kind="captions") are required for accessibility',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Canvas and SVG',
    'Draw a Simple SVG Chart',
    'Create an inline SVG bar chart showing course enrollment data.',
    "1. Create an inline SVG element (width=400, height=200) inside an HTML page\n2. Draw a baseline (a horizontal line across the bottom)\n3. Draw 3 rectangular bars representing enrollment numbers for HTML (150 students), CSS (90 students), JavaScript (120 students)\n4. Add text labels below each bar with the course name\n5. Add a title element inside the SVG for accessibility",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SVG Chart</title>
</head>
<body>
  <h1>Course Enrollment</h1>
  <svg width="400" height="200" xmlns="http://www.w3.org/2000/svg">
    <!-- baseline line -->
    <!-- three bars (rect elements) -->
    <!-- text labels -->
    <!-- title for accessibility -->
  </svg>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SVG Chart</title>
</head>
<body>
  <h1>Course Enrollment</h1>
  <svg width="400" height="220" xmlns="http://www.w3.org/2000/svg">
    <title>Course enrollment bar chart: HTML 150, CSS 90, JavaScript 120</title>

    <!-- Baseline -->
    <line x1="30" y1="180" x2="390" y2="180" stroke="#333" stroke-width="2"/>

    <!-- HTML bar: 150 students → height 150px -->
    <rect x="50"  y="30"  width="70" height="150" fill="#e34c26"/>
    <text x="85"  y="200" text-anchor="middle" font-size="12" fill="#333">HTML</text>
    <text x="85"  y="22"  text-anchor="middle" font-size="11" fill="#333">150</text>

    <!-- CSS bar: 90 students → height 90px -->
    <rect x="170" y="90"  width="70" height="90"  fill="#264de4"/>
    <text x="205" y="200" text-anchor="middle" font-size="12" fill="#333">CSS</text>
    <text x="205" y="82"  text-anchor="middle" font-size="11" fill="#333">90</text>

    <!-- JS bar: 120 students → height 120px -->
    <rect x="290" y="60"  width="70" height="120" fill="#f0db4f"/>
    <text x="325" y="200" text-anchor="middle" font-size="12" fill="#333">JS</text>
    <text x="325" y="52"  text-anchor="middle" font-size="11" fill="#333">120</text>
  </svg>
</body>
</html>',
    'SVG rect uses x, y for position and width, height for size|text-anchor="middle" centres text around the x coordinate|Add a title element as the first child of SVG for screen reader accessibility',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Meta Tags and SEO',
    'Write Complete Head Meta Tags',
    'Add a full set of SEO and social media meta tags to a page.',
    "1. Create an HTML page about a fictional web development course\n2. Add a descriptive title tag (50-60 characters)\n3. Add a meta description (150-160 characters)\n4. Add Open Graph tags: og:title, og:description, og:image (any URL), og:url, og:type\n5. Add Twitter Card tags: twitter:card (summary_large_image), twitter:title, twitter:description, twitter:image\n6. Add a canonical link element",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- title tag -->
  <!-- meta description -->
  <!-- Open Graph tags -->
  <!-- Twitter Card tags -->
  <!-- canonical link -->
</head>
<body>
  <h1>Complete HTML Course for Beginners</h1>
  <p>Course content would go here.</p>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Complete HTML Course for Beginners — HackathonAfrica</title>

  <meta name="description" content="Master HTML from scratch with hands-on lessons. Learn structure, forms, semantic elements, and accessibility. Start free today.">

  <!-- Open Graph -->
  <meta property="og:title" content="Complete HTML Course for Beginners">
  <meta property="og:description" content="Master HTML from scratch with hands-on lessons covering structure, forms, and accessibility.">
  <meta property="og:image" content="https://example.com/images/html-course-preview.jpg">
  <meta property="og:url" content="https://hackafrica.com/courses/html">
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Complete HTML Course for Beginners">
  <meta name="twitter:description" content="Master HTML from scratch with hands-on lessons.">
  <meta name="twitter:image" content="https://example.com/images/html-course-preview.jpg">

  <link rel="canonical" href="https://hackafrica.com/courses/html">
</head>
<body>
  <h1>Complete HTML Course for Beginners</h1>
  <p>Course content would go here.</p>
</body>
</html>',
    'og: tags use property= not name=|Every page needs a unique, descriptive title|The canonical link prevents duplicate content penalties',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Web Components Basics',
    'Create a Custom Badge Element',
    'Build a simple custom HTML element using the Web Components API.',
    "1. Create a custom element called <status-badge> that accepts a type attribute (success, warning, error)\n2. In the connectedCallback, read the type attribute and the slot text content\n3. Render an appropriate emoji (✅ for success, ⚠️ for warning, ❌ for error) before the text\n4. Register the element with customElements.define\n5. Use it three times on the page with different type values and text",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Custom Element</title>
</head>
<body>
  <h1>Status Dashboard</h1>
  <!-- Use <status-badge type="success">All systems operational</status-badge> -->
  <!-- Use <status-badge type="warning">High traffic detected</status-badge> -->
  <!-- Use <status-badge type="error">Payment service down</status-badge> -->

  <script>
    // Define the custom element here
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Custom Element</title>
  <style>
    status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.875rem; margin: 4px; }
    status-badge[type="success"] { background: #d4edda; color: #155724; }
    status-badge[type="warning"] { background: #fff3cd; color: #856404; }
    status-badge[type="error"]   { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
  <h1>Status Dashboard</h1>
  <status-badge type="success">All systems operational</status-badge>
  <status-badge type="warning">High traffic detected</status-badge>
  <status-badge type="error">Payment service down</status-badge>

  <script>
    class StatusBadge extends HTMLElement {
      connectedCallback() {
        const type = this.getAttribute("type") || "success";
        const icons = { success: "✅", warning: "⚠️", error: "❌" };
        const text = this.textContent;
        this.textContent = "";
        const icon = document.createElement("span");
        icon.textContent = (icons[type] || "ℹ️") + " ";
        this.prepend(icon);
        this.append(text);
      }
    }
    customElements.define("status-badge", StatusBadge);
  </script>
</body>
</html>',
    'Custom element names must contain a hyphen (status-badge, not statusbadge)|connectedCallback runs when the element is inserted into the DOM|getAttribute reads the current HTML attribute value',
    'html', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Performance Optimization',
    'Apply Performance Best Practices to a Page',
    'Take a slow-loading page template and apply HTML-level performance optimisations.',
    "1. Add loading='lazy' to all below-fold images (any image after the hero)\n2. Add width and height attributes to all img elements\n3. Add defer to the script tag in the head\n4. Add a preconnect link for https://fonts.googleapis.com\n5. Add a preload link for the hero image (use any image URL)\n6. Move the non-critical script from the head to just before the closing body tag and add defer",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Missing: preconnect, preload -->
  <!-- This script blocks rendering: -->
  <script src="app.js"></script>
  <title>Fast Page</title>
</head>
<body>
  <!-- Hero image — should load eagerly -->
  <img src="https://picsum.photos/1200/500" alt="Hero image">

  <!-- Below fold images — should lazy load -->
  <img src="https://picsum.photos/400/300?1" alt="Feature 1">
  <img src="https://picsum.photos/400/300?2" alt="Feature 2">
  <img src="https://picsum.photos/400/300?3" alt="Feature 3">
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fast Page</title>

  <!-- Preconnect to font CDN to establish connection early -->
  <link rel="preconnect" href="https://fonts.googleapis.com">

  <!-- Preload the hero image so it starts fetching immediately -->
  <link rel="preload" href="https://picsum.photos/1200/500" as="image">
</head>
<body>
  <!-- Hero image: above fold, load eagerly with correct dimensions -->
  <img src="https://picsum.photos/1200/500"
       alt="Hero image"
       width="1200"
       height="500">

  <!-- Below fold images: lazy load with dimensions -->
  <img src="https://picsum.photos/400/300?1" alt="Feature 1" width="400" height="300" loading="lazy">
  <img src="https://picsum.photos/400/300?2" alt="Feature 2" width="400" height="300" loading="lazy">
  <img src="https://picsum.photos/400/300?3" alt="Feature 3" width="400" height="300" loading="lazy">

  <!-- Script at end of body with defer: does not block rendering -->
  <script src="app.js" defer></script>
</body>
</html>',
    'loading="lazy" defers image loading until near the viewport|Always specify width and height on images to prevent layout shift|defer on scripts allows parallel download without blocking HTML parsing',
    'html', 'easy', 10, $exercisesInserted);

// =====================================================================
// CODING EXERCISES — CSS Course (20 exercises)
// =====================================================================

addExercise($pdo, 'CSS Selectors',
    'Practice CSS Selector Types',
    'Style a page using all major selector types without touching the HTML.',
    "1. Using only the starter CSS section, add a type selector to make all paragraphs grey (#555)\n2. Add a class selector to make .highlight elements have a yellow background\n3. Add a descendant combinator to make links inside nav bold\n4. Add an attribute selector to make links opening in a new tab (target='_blank') show a different colour\n5. Add a :hover pseudo-class to change the button background on hover",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Selectors</title>
  <style>
    /* Add your selectors here */

    /* 1. Type selector: all paragraphs grey */

    /* 2. Class selector: .highlight yellow background */

    /* 3. Descendant: nav links bold */

    /* 4. Attribute: target=_blank links different colour */

    /* 5. Pseudo-class: button hover state */
  </style>
</head>
<body>
  <nav>
    <a href="/">Home</a>
    <a href="/about">About</a>
    <a href="https://google.com" target="_blank">Google</a>
  </nav>
  <p>This is a regular paragraph with grey text.</p>
  <p>This paragraph has a <span class="highlight">highlighted word</span> in it.</p>
  <button>Hover over me</button>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Selectors</title>
  <style>
    /* 1. Type selector */
    p { color: #555; }

    /* 2. Class selector */
    .highlight { background-color: #ffeb3b; padding: 2px 4px; }

    /* 3. Descendant combinator */
    nav a { font-weight: bold; }

    /* 4. Attribute selector */
    a[target="_blank"] { color: #e91e63; }

    /* 5. Pseudo-class */
    button { padding: 10px 20px; background: #0066cc; color: white; border: none; cursor: pointer; }
    button:hover { background: #004fa3; }
  </style>
</head>
<body>
  <nav>
    <a href="/">Home</a>
    <a href="/about">About</a>
    <a href="https://google.com" target="_blank">Google</a>
  </nav>
  <p>This is a regular paragraph with grey text.</p>
  <p>This paragraph has a <span class="highlight">highlighted word</span> in it.</p>
  <button>Hover over me</button>
</body>
</html>',
    'Type selectors match by tag name (p, a, h1)|Class selectors start with a dot (.highlight)|Attribute selectors use square brackets: [target="_blank"]',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'The Box Model',
    'Build a Profile Card Using the Box Model',
    'Create a card component that demonstrates padding, border, and margin.',
    "1. Set box-sizing: border-box globally\n2. Create a .card with: width 300px, padding 24px, a 2px solid border (#e0e0e0), border-radius 12px, and margin 20px auto to centre it\n3. Add an .avatar (a div) inside the card: 80px wide, 80px tall, border-radius 50%, background #0066cc, centred with margin auto\n4. Add a .card-title (h2) with font-size 1.25rem and margin-bottom 8px\n5. Add a .card-text (p) with colour #666 and line-height 1.6",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Box Model Card</title>
  <style>
    /* box-sizing reset */

    /* .card styles */

    /* .avatar styles */

    /* .card-title styles */

    /* .card-text styles */
  </style>
</head>
<body>
  <div class="card">
    <div class="avatar"></div>
    <h2 class="card-title">Alex Kamau</h2>
    <p class="card-text">Frontend Developer passionate about building accessible and performant web experiences.</p>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Box Model Card</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: sans-serif; background: #f5f5f5; }

    .card {
      width: 300px;
      padding: 24px;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      margin: 20px auto;
      background: white;
      text-align: center;
    }

    .avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: #0066cc;
      margin: 0 auto 16px;
    }

    .card-title {
      font-size: 1.25rem;
      margin-bottom: 8px;
      color: #1a1a1a;
    }

    .card-text {
      color: #666;
      line-height: 1.6;
      margin: 0;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="avatar"></div>
    <h2 class="card-title">Alex Kamau</h2>
    <p class="card-text">Frontend Developer passionate about building accessible and performant web experiences.</p>
  </div>
</body>
</html>',
    'box-sizing: border-box makes width include padding and border|border-radius: 50% on an equal-width-height element creates a circle|margin: auto on a block element centres it horizontally',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Typography',
    'Style Article Typography',
    'Apply comprehensive typography styles to make an article readable and visually polished.',
    "1. Set a sans-serif system font stack on the body with font-size 1rem and line-height 1.6\n2. Style h1 with font-size 2.5rem and line-height 1.2\n3. Style h2 with font-size 1.75rem\n4. Make the .lead paragraph class have font-size 1.2rem and colour #444\n5. Style code elements with a monospace font, background #f4f4f4, padding 2px 6px, and border-radius 3px\n6. Style blockquote with a left border 4px solid #0066cc, padding-left 1.5rem, font-style italic",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Typography Styles</title>
  <style>
    /* body font stack */
    /* h1 styles */
    /* h2 styles */
    /* .lead paragraph */
    /* code element */
    /* blockquote */
  </style>
</head>
<body>
  <h1>Introduction to CSS Typography</h1>
  <p class="lead">Typography is the art of arranging type to make language visible and readable.</p>
  <h2>Why Typography Matters</h2>
  <p>Good typography improves readability. Use the <code>font-family</code> property to set typefaces.</p>
  <blockquote>Typography is what language looks like. — Ellen Lupton</blockquote>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Typography Styles</title>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      font-size: 1rem;
      line-height: 1.6;
      color: #1a1a1a;
      max-width: 700px;
      margin: 40px auto;
      padding: 0 20px;
    }

    h1 {
      font-size: 2.5rem;
      line-height: 1.2;
      margin-bottom: 0.5rem;
    }

    h2 {
      font-size: 1.75rem;
      line-height: 1.3;
      margin-top: 2rem;
    }

    .lead {
      font-size: 1.2rem;
      color: #444;
      margin-bottom: 2rem;
    }

    code {
      font-family: "Consolas", "Monaco", monospace;
      background: #f4f4f4;
      padding: 2px 6px;
      border-radius: 3px;
      font-size: 0.9em;
    }

    blockquote {
      border-left: 4px solid #0066cc;
      padding-left: 1.5rem;
      font-style: italic;
      color: #555;
      margin: 1.5rem 0;
    }
  </style>
</head>
<body>
  <h1>Introduction to CSS Typography</h1>
  <p class="lead">Typography is the art of arranging type to make language visible and readable.</p>
  <h2>Why Typography Matters</h2>
  <p>Good typography improves readability. Use the <code>font-family</code> property to set typefaces.</p>
  <blockquote>Typography is what language looks like. — Ellen Lupton</blockquote>
</body>
</html>',
    'System font stacks use -apple-system and BlinkMacSystemFont for native look on each OS|Use rem for font sizes to respect user preferences|line-height: 1.6 is the comfortable standard for body text',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Colors and Backgrounds',
    'Build a Hero Section with Gradient Background',
    'Create a visually striking hero section using CSS colours, gradients, and custom properties.',
    "1. Define a :root with custom properties: --color-primary (#0066cc), --color-secondary (#ff6600), --color-text-light (#ffffff)\n2. Create a .hero with: min-height 60vh, a linear gradient from primary to secondary at 135 degrees, centred content using flexbox\n3. Style .hero h1 with the light text colour, font-size 3rem, and a text-shadow for depth\n4. Add a semi-transparent white .hero-card inside with backdrop-filter blur\n5. Make the card have white text and rounded corners",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hero Section</title>
  <style>
    :root {
      /* define your custom properties */
    }
    /* .hero styles */
    /* .hero h1 styles */
    /* .hero-card styles */
  </style>
</head>
<body>
  <section class="hero">
    <div class="hero-card">
      <h1>Learn Web Development</h1>
      <p>Start your journey today with hands-on courses.</p>
    </div>
  </section>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hero Section</title>
  <style>
    :root {
      --color-primary: #0066cc;
      --color-secondary: #ff6600;
      --color-text-light: #ffffff;
    }

    * { box-sizing: border-box; margin: 0; }

    .hero {
      min-height: 60vh;
      background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .hero h1 {
      color: var(--color-text-light);
      font-size: 3rem;
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
      margin-bottom: 1rem;
    }

    .hero p {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.2rem;
    }

    .hero-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 16px;
      padding: 3rem;
      text-align: center;
      max-width: 600px;
    }
  </style>
</head>
<body>
  <section class="hero">
    <div class="hero-card">
      <h1>Learn Web Development</h1>
      <p>Start your journey today with hands-on courses.</p>
    </div>
  </section>
</body>
</html>',
    'CSS variables use --name: value in :root and var(--name) to consume|linear-gradient takes an angle and colour stops|backdrop-filter needs a semi-transparent background to show through',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Pseudo-classes and Pseudo-elements',
    'Style an Interactive Navigation',
    'Use pseudo-classes and pseudo-elements to create a polished navigation with hover effects and indicators.',
    "1. Style the nav links to remove default underline and set colour #333\n2. Add :hover to change colour to #0066cc and add an underline\n3. Add :focus with a visible outline (no outline: none)\n4. Use the :first-child pseudo-class to remove the left border from the first nav item\n5. Add a ::before pseudo-element to add '→ ' before each nav link on hover",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pseudo Selectors</title>
  <style>
    body { font-family: sans-serif; }
    nav { display: flex; gap: 0; }
    nav a {
      display: block;
      padding: 12px 20px;
      border-left: 1px solid #ddd;
      /* base styles here */
    }
    /* :hover */
    /* :focus */
    /* :first-child */
    /* ::before */
  </style>
</head>
<body>
  <nav>
    <a href="#">Home</a>
    <a href="#">Courses</a>
    <a href="#">About</a>
    <a href="#">Contact</a>
  </nav>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pseudo Selectors</title>
  <style>
    body { font-family: sans-serif; }
    nav { display: flex; gap: 0; }

    nav a {
      display: block;
      padding: 12px 20px;
      border-left: 1px solid #ddd;
      color: #333;
      text-decoration: none;
      position: relative;
      transition: color 200ms ease, padding-left 200ms ease;
    }

    nav a:hover {
      color: #0066cc;
      text-decoration: underline;
      padding-left: 28px;
    }

    nav a:focus {
      outline: 2px solid #0066cc;
      outline-offset: 2px;
    }

    nav a:first-child {
      border-left: none;
    }

    nav a::before {
      content: "→ ";
      opacity: 0;
      transition: opacity 200ms ease;
    }

    nav a:hover::before {
      opacity: 1;
    }
  </style>
</head>
<body>
  <nav>
    <a href="#">Home</a>
    <a href="#">Courses</a>
    <a href="#">About</a>
    <a href="#">Contact</a>
  </nav>
</body>
</html>',
    'Pseudo-classes use single colon (:hover) — pseudo-elements use double colon (::before)|::before requires a content property even if it is an empty string|Never use outline: none without providing an alternative focus style',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Flexbox Fundamentals',
    'Build a Flexbox Navigation Bar',
    'Create a responsive navigation bar using flexbox container properties.',
    "1. Create a .navbar with display: flex, space-between justification, and centre-aligned items\n2. Give the navbar a dark background (#1a1a2e), height 64px, and horizontal padding 2rem\n3. Style the .brand as white bold text\n4. Style .nav-links as a flex row with gap 2rem\n5. Style nav links as white with no underline, adding :hover colour change\n6. Add a .nav-cta button with the orange brand colour (#ff6600)",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flexbox Nav</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; }
    /* .navbar */
    /* .brand */
    /* .nav-links */
    /* nav a */
    /* .nav-cta */
  </style>
</head>
<body>
  <nav class="navbar">
    <span class="brand">HackAfrica</span>
    <ul class="nav-links">
      <li><a href="#">Courses</a></li>
      <li><a href="#">Community</a></li>
      <li><a href="#">Blog</a></li>
      <li><a href="#" class="nav-cta">Get Started</a></li>
    </ul>
  </nav>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flexbox Nav</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: sans-serif; }

    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #1a1a2e;
      height: 64px;
      padding: 0 2rem;
    }

    .brand {
      color: white;
      font-weight: bold;
      font-size: 1.25rem;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 2rem;
      list-style: none;
    }

    .nav-links a {
      color: rgba(255, 255, 255, 0.85);
      text-decoration: none;
      transition: color 200ms;
    }

    .nav-links a:hover { color: white; }

    .nav-cta {
      background: #ff6600;
      color: white !important;
      padding: 8px 16px;
      border-radius: 6px;
    }

    .nav-cta:hover { background: #e65c00; }
  </style>
</head>
<body>
  <nav class="navbar">
    <span class="brand">HackAfrica</span>
    <ul class="nav-links">
      <li><a href="#">Courses</a></li>
      <li><a href="#">Community</a></li>
      <li><a href="#">Blog</a></li>
      <li><a href="#" class="nav-cta">Get Started</a></li>
    </ul>
  </nav>
</body>
</html>',
    'justify-content: space-between pushes children to opposite ends|align-items: center vertically centres all children in the row|gap on the flex container adds space between flex items',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Flex Item Properties',
    'Build an Equal-Height Card Row with Sticky Footer',
    'Use flex item properties to create cards with equal height and buttons that stick to the bottom.',
    "1. Create a .cards row using display: flex with gap 1.5rem\n2. Each .card should use flex: 1 1 250px so they share space equally\n3. Make each .card a flex column container\n4. Give .card-body flex: 1 so it grows to fill available space\n5. Style the .card-footer with a top border and ensure it stays at the bottom\n6. Set min-height on the cards row to show the effect",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flex Card Row</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
    /* .cards — flex row */
    /* .card — flex column container */
    /* .card-body — flex: 1 */
    /* .card-footer */
  </style>
</head>
<body>
  <div class="cards">
    <div class="card">
      <div class="card-body">
        <h3>HTML Basics</h3>
        <p>A short description.</p>
      </div>
      <div class="card-footer"><button>Enroll</button></div>
    </div>
    <div class="card">
      <div class="card-body">
        <h3>CSS Fundamentals</h3>
        <p>A much longer description that takes up more vertical space than the other cards in this row.</p>
      </div>
      <div class="card-footer"><button>Enroll</button></div>
    </div>
    <div class="card">
      <div class="card-body">
        <h3>JavaScript</h3>
        <p>Medium length description here.</p>
      </div>
      <div class="card-footer"><button>Enroll</button></div>
    </div>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flex Card Row</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }

    .cards {
      display: flex;
      gap: 1.5rem;
      align-items: stretch;
    }

    .card {
      flex: 1 1 250px;
      display: flex;
      flex-direction: column;
      background: white;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      overflow: hidden;
    }

    .card-body {
      flex: 1;
      padding: 1.5rem;
    }

    .card-body h3 { margin-bottom: 0.5rem; }

    .card-footer {
      padding: 1rem 1.5rem;
      border-top: 1px solid #e0e0e0;
    }

    .card-footer button {
      width: 100%;
      padding: 10px;
      background: #0066cc;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="cards">
    <div class="card">
      <div class="card-body">
        <h3>HTML Basics</h3>
        <p>A short description.</p>
      </div>
      <div class="card-footer"><button>Enroll</button></div>
    </div>
    <div class="card">
      <div class="card-body">
        <h3>CSS Fundamentals</h3>
        <p>A much longer description that takes up more vertical space than the other cards in this row.</p>
      </div>
      <div class="card-footer"><button>Enroll</button></div>
    </div>
    <div class="card">
      <div class="card-body">
        <h3>JavaScript</h3>
        <p>Medium length description here.</p>
      </div>
      <div class="card-footer"><button>Enroll</button></div>
    </div>
  </div>
</body>
</html>',
    'flex: 1 on cards makes them share available space equally|Making the card a flex column with flex: 1 on the body pushes the footer to the bottom|align-items: stretch (default) makes all cards the same height',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'CSS Grid Fundamentals',
    'Build a 3-Column Responsive Card Grid',
    'Create a responsive card grid using CSS Grid with auto-fill columns.',
    "1. Create a .grid container using display: grid\n2. Use grid-template-columns with repeat(auto-fill, minmax(250px, 1fr)) for a responsive column count\n3. Add gap: 1.5rem between cards\n4. Style each .card with a white background, padding 1.5rem, rounded corners, and box shadow\n5. Add a featured card that spans all columns using grid-column: 1 / -1",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Grid</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f0f0f0; }
    /* .grid */
    /* .card */
    /* .card.featured */
  </style>
</head>
<body>
  <div class="grid">
    <div class="card featured">
      <h2>Featured: Full Stack Bootcamp</h2>
      <p>Our most popular course — spans all columns to highlight its importance.</p>
    </div>
    <div class="card"><h3>HTML Basics</h3><p>Learn the structure of the web.</p></div>
    <div class="card"><h3>CSS Layouts</h3><p>Master flexbox and grid.</p></div>
    <div class="card"><h3>JavaScript</h3><p>Add interactivity to pages.</p></div>
    <div class="card"><h3>Node.js</h3><p>Build backend APIs.</p></div>
    <div class="card"><h3>Databases</h3><p>Store and query data.</p></div>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Grid</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f0f0f0; }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1.5rem;
    }

    .card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .card h3 { margin-bottom: 0.5rem; color: #1a1a1a; }
    .card p { color: #666; font-size: 0.9rem; }

    .card.featured {
      grid-column: 1 / -1;
      background: linear-gradient(135deg, #0066cc, #ff6600);
      color: white;
    }

    .card.featured h2 { color: white; margin-bottom: 0.5rem; }
    .card.featured p  { color: rgba(255,255,255,0.9); }
  </style>
</head>
<body>
  <div class="grid">
    <div class="card featured">
      <h2>Featured: Full Stack Bootcamp</h2>
      <p>Our most popular course — spans all columns to highlight its importance.</p>
    </div>
    <div class="card"><h3>HTML Basics</h3><p>Learn the structure of the web.</p></div>
    <div class="card"><h3>CSS Layouts</h3><p>Master flexbox and grid.</p></div>
    <div class="card"><h3>JavaScript</h3><p>Add interactivity to pages.</p></div>
    <div class="card"><h3>Node.js</h3><p>Build backend APIs.</p></div>
    <div class="card"><h3>Databases</h3><p>Store and query data.</p></div>
  </div>
</body>
</html>',
    'repeat(auto-fill, minmax(250px, 1fr)) creates as many 250px+ columns as fit the container|grid-column: 1 / -1 spans from the first to the last grid line (all columns)|gap sets the gutter between both rows and columns',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Grid Template Areas',
    'Build a Page Layout with Named Areas',
    'Create a complete page layout using grid-template-areas.',
    "1. Create a .page-layout grid with named areas: header, nav, main, sidebar, footer\n2. Define the layout: header spans full width, nav is a sidebar on the left, main is the large content area, sidebar is a right column, footer spans full width\n3. Use grid-template-columns: 200px 1fr 250px and grid-template-rows: auto auto 1fr auto\n4. Assign each element (header, nav, main, aside, footer) to its named area\n5. Add min-height: 100vh to the layout",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grid Areas</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; }
    .page-layout {
      /* define grid with template areas */
    }
    /* assign each area */
    header, nav, main, aside, footer {
      padding: 1rem;
    }
    header { background: #1a1a2e; color: white; }
    nav    { background: #f8f9fa; border-right: 1px solid #e0e0e0; }
    main   { background: white; }
    aside  { background: #f0f4ff; border-left: 1px solid #e0e0e0; }
    footer { background: #333; color: white; text-align: center; }
  </style>
</head>
<body>
  <div class="page-layout">
    <header><h1>HackathonAfrica</h1></header>
    <nav><p>Navigation</p></nav>
    <main><h2>Main Content</h2><p>Article text here.</p></main>
    <aside><h3>Sidebar</h3><p>Related links.</p></aside>
    <footer><p>&copy; 2025</p></footer>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grid Areas</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; }

    .page-layout {
      display: grid;
      grid-template-areas:
        "header  header  header"
        "nav     main    sidebar"
        "footer  footer  footer";
      grid-template-columns: 200px 1fr 250px;
      grid-template-rows: auto 1fr auto;
      min-height: 100vh;
    }

    header { grid-area: header; background: #1a1a2e; color: white; padding: 1rem 2rem; }
    nav    { grid-area: nav;    background: #f8f9fa; border-right: 1px solid #e0e0e0; padding: 1rem; }
    main   { grid-area: main;   background: white; padding: 2rem; }
    aside  { grid-area: sidebar; background: #f0f4ff; border-left: 1px solid #e0e0e0; padding: 1rem; }
    footer { grid-area: footer; background: #333; color: white; text-align: center; padding: 1rem; }
  </style>
</head>
<body>
  <div class="page-layout">
    <header><h1>HackathonAfrica</h1></header>
    <nav><p>Navigation</p></nav>
    <main><h2>Main Content</h2><p>Article text here.</p></main>
    <aside><h3>Sidebar</h3><p>Related links.</p></aside>
    <footer><p>&copy; 2025</p></footer>
  </div>
</body>
</html>',
    'grid-template-areas uses quoted strings, one per row|Each cell name forms a rectangle — cannot be L-shaped|Assign elements to areas with grid-area: name',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Responsive Design',
    'Build a Mobile-First Responsive Layout',
    'Create a layout that stacks on mobile and uses a sidebar on desktop.',
    "1. Start with mobile-first styles: .layout is a single column (no grid yet)\n2. The .sidebar comes after .main in the HTML but should appear first visually on desktop\n3. At min-width: 768px, switch .layout to a grid with sidebar 250px and main 1fr\n4. Use a min-width: 1200px breakpoint to increase the sidebar to 300px\n5. Use clamp() for the main heading font-size: minimum 1.5rem, preferred 4vw, maximum 3rem",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsive Layout</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; }
    /* Mobile styles first */
    /* Tablet: 768px */
    /* Desktop: 1200px */
    /* Fluid heading */
  </style>
</head>
<body>
  <div class="layout">
    <main>
      <h1>Main Content Area</h1>
      <p>This is the main content. On mobile it stacks. On desktop it sits next to the sidebar.</p>
    </main>
    <aside class="sidebar">
      <h2>Sidebar</h2>
      <p>Related links and widgets.</p>
    </aside>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsive Layout</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; }

    /* Mobile: stacked */
    .layout {
      padding: 1rem;
    }

    main, .sidebar {
      padding: 1.5rem;
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      margin-bottom: 1rem;
    }

    h1 {
      font-size: clamp(1.5rem, 4vw, 3rem);
      margin-bottom: 1rem;
    }

    /* Tablet: 2 columns */
    @media (min-width: 768px) {
      .layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        grid-template-areas: "sidebar main";
        gap: 1.5rem;
      }
      main    { grid-area: main; margin-bottom: 0; }
      .sidebar { grid-area: sidebar; margin-bottom: 0; }
    }

    /* Desktop: wider sidebar */
    @media (min-width: 1200px) {
      .layout {
        grid-template-columns: 300px 1fr;
        max-width: 1200px;
        margin: 0 auto;
      }
    }
  </style>
</head>
<body>
  <div class="layout">
    <main>
      <h1>Main Content Area</h1>
      <p>This is the main content. On mobile it stacks. On desktop it sits next to the sidebar.</p>
    </main>
    <aside class="sidebar">
      <h2>Sidebar</h2>
      <p>Related links and widgets.</p>
    </aside>
  </div>
</body>
</html>',
    'Mobile-first: write base styles for small screens, add complexity with min-width media queries|clamp(min, preferred, max) creates fluid sizing that scales with viewport|The viewport meta tag is required for media queries to work on mobile',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'CSS Transitions',
    'Add Smooth Transitions to Interactive Elements',
    'Apply CSS transitions to buttons, cards, and form inputs for polished interactions.',
    "1. Style .btn with background #0066cc, white text, padding, and transition for background-color and transform (200ms ease-out each)\n2. On .btn:hover: change background to a darker blue, translate up 2px\n3. On .btn:active: translate back to 0\n4. Add transition on .card for box-shadow (300ms ease-out)\n5. On .card:hover add an elevated box shadow\n6. Add focus transition to input: smooth border-color and box-shadow change",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transitions</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; display: flex; gap: 1rem; flex-direction: column; max-width: 400px; }
    /* .btn with transition */
    /* .card with transition */
    /* input with transition */
    input { padding: 10px; border: 2px solid #ccc; border-radius: 4px; font-size: 1rem; width: 100%; outline: none; }
  </style>
</head>
<body>
  <button class="btn">Click Me</button>
  <div class="card" style="padding:1.5rem;border:1px solid #e0e0e0;border-radius:8px;background:white">
    <h3>Hover over this card</h3>
  </div>
  <input type="text" placeholder="Focus on this input">
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transitions</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; display: flex; gap: 1rem; flex-direction: column; max-width: 400px; }

    .btn {
      padding: 12px 24px;
      background: #0066cc;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 200ms ease-out, transform 200ms ease-out;
    }

    .btn:hover  { background: #004fa3; transform: translateY(-2px); }
    .btn:active { transform: translateY(0); }
    .btn:focus  { outline: 2px solid #80bdff; outline-offset: 2px; }

    .card {
      padding: 1.5rem;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      background: white;
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
      transition: box-shadow 300ms ease-out, transform 300ms ease-out;
    }

    .card:hover {
      box-shadow: 0 8px 24px rgba(0,0,0,0.14);
      transform: translateY(-2px);
    }

    input {
      padding: 10px;
      border: 2px solid #ccc;
      border-radius: 4px;
      font-size: 1rem;
      width: 100%;
      outline: none;
      transition: border-color 200ms ease, box-shadow 200ms ease;
    }

    input:focus {
      border-color: #0066cc;
      box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15);
    }
  </style>
</head>
<body>
  <button class="btn">Click Me</button>
  <div class="card"><h3>Hover over this card</h3></div>
  <input type="text" placeholder="Focus on this input">
</body>
</html>',
    'Put transition on the base element, not the :hover state, so it plays both entering and leaving|transform: translateY(-2px) creates a lift effect on hover|Use semi-transparent box-shadow colours for natural-looking shadows',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'CSS Animations',
    'Create a Loading Spinner and Fade-In Animation',
    'Build a loading spinner and entrance animation using @keyframes.',
    "1. Create @keyframes spin that rotates from 0 to 360 degrees\n2. Apply it to .spinner (a 40px circle with orange top-border) with 1s linear infinite\n3. Create @keyframes fadeInUp that animates from opacity 0 and translateY(30px) to opacity 1 and translateY(0)\n4. Apply fadeInUp to .card with 400ms ease-out and animation-fill-mode: forwards\n5. Add 3 cards and stagger their animations with animation-delay: 0ms, 150ms, 300ms",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Animations</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
    /* @keyframes spin */
    /* @keyframes fadeInUp */
    /* .spinner */
    /* .card */
    /* staggered delays */
  </style>
</head>
<body>
  <div class="spinner"></div>
  <div style="display:flex;gap:1rem;margin-top:2rem">
    <div class="card"><h3>Card 1</h3></div>
    <div class="card"><h3>Card 2</h3></div>
    <div class="card"><h3>Card 3</h3></div>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Animations</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .spinner {
      width: 40px;
      height: 40px;
      border: 4px solid #e0e0e0;
      border-top-color: #ff6600;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    .card {
      flex: 1;
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      opacity: 0;
      animation: fadeInUp 400ms ease-out forwards;
    }

    .card:nth-child(1) { animation-delay: 0ms; }
    .card:nth-child(2) { animation-delay: 150ms; }
    .card:nth-child(3) { animation-delay: 300ms; }
  </style>
</head>
<body>
  <div class="spinner"></div>
  <div style="display:flex;gap:1rem;margin-top:2rem">
    <div class="card"><h3>Card 1</h3></div>
    <div class="card"><h3>Card 2</h3></div>
    <div class="card"><h3>Card 3</h3></div>
  </div>
</body>
</html>',
    '@keyframes defines animation stages; the animation property applies it to an element|animation-fill-mode: forwards keeps the final keyframe state after animation ends|Stagger delays with nth-child and increasing animation-delay values',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Transforms',
    'Build Interactive Card with Transform Effects',
    'Apply 2D transforms to create hover effects and a tilted label.',
    "1. Create a .card that scales to 1.03 on hover\n2. Add a .badge on the card that is rotated -5 degrees\n3. Create a .flip-btn that rotates 180 degrees on hover (around the Y axis using rotateY)\n4. Centre an absolutely positioned element using the translate(-50%, -50%) technique\n5. Add transitions to all transforms for smooth effects",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Transforms</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 3rem; display: flex; gap: 2rem; align-items: flex-start; }
    /* .card with scale hover */
    /* .badge with rotate */
    /* .flip-btn with rotateY hover */
    /* .centred-label technique */
  </style>
</head>
<body>
  <div class="card" style="position:relative;width:200px;padding:1.5rem;background:white;border:1px solid #e0e0e0;border-radius:8px">
    <div class="badge" style="position:absolute;top:12px;right:-8px;background:#ff6600;color:white;padding:4px 10px;font-size:0.75rem">NEW</div>
    <h3>Transform Card</h3>
    <p>Hover to scale me up.</p>
  </div>

  <button class="flip-btn" style="padding:12px 24px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer;font-size:1rem">
    Hover to Flip
  </button>

  <div style="position:relative;width:200px;height:150px;background:#f0f4ff;border-radius:8px">
    <span class="centred-label" style="position:absolute;top:50%;left:50%;background:#0066cc;color:white;padding:6px 12px;border-radius:4px;white-space:nowrap">
      I am centred
    </span>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Transforms</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 3rem; display: flex; gap: 2rem; align-items: flex-start; }

    .card {
      position: relative;
      width: 200px;
      padding: 1.5rem;
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      transition: transform 200ms ease-out, box-shadow 200ms ease-out;
    }
    .card:hover {
      transform: scale(1.03);
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .badge {
      position: absolute;
      top: 12px;
      right: -8px;
      background: #ff6600;
      color: white;
      padding: 4px 10px;
      font-size: 0.75rem;
      transform: rotate(-5deg);
    }

    .flip-btn {
      padding: 12px 24px;
      background: #0066cc;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
      transition: transform 400ms ease;
    }
    .flip-btn:hover { transform: rotateY(180deg); }

    .centred-label {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #0066cc;
      color: white;
      padding: 6px 12px;
      border-radius: 4px;
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="badge">NEW</div>
    <h3>Transform Card</h3>
    <p>Hover to scale me up.</p>
  </div>
  <button class="flip-btn">Hover to Flip</button>
  <div style="position:relative;width:200px;height:150px;background:#f0f4ff;border-radius:8px">
    <span class="centred-label">I am centred</span>
  </div>
</body>
</html>',
    'transform: scale(1.03) grows the element 3% on hover|translate(-50%, -50%) moves an absolutely positioned element back by half its own size to centre it|Combine multiple transforms in one declaration: transform: scale(1.1) rotate(3deg)',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Shadows and Effects',
    'Build an Elevation System',
    'Create a set of elevation levels using box shadows and apply them to components.',
    "1. Define 4 elevation levels as CSS custom properties in :root: --shadow-0 (no shadow), --shadow-1 (very subtle), --shadow-2 (medium), --shadow-3 (prominent modal shadow)\n2. Create 4 .card elements with classes card-level-0 through card-level-3\n3. Apply the corresponding shadow variable to each\n4. Add a text-shadow to the page h1 for depth\n5. On .card-level-2:hover and .card-level-3:hover, increase the shadow",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shadow System</title>
  <style>
    :root {
      /* Define shadow variables */
    }
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
    .cards { display: flex; gap: 1.5rem; margin-top: 2rem; }
    /* card elevation classes */
    /* h1 text-shadow */
    /* hover effects */
  </style>
</head>
<body>
  <h1>Shadow Elevation System</h1>
  <div class="cards">
    <div class="card card-level-0"><p>Level 0</p><p>No shadow</p></div>
    <div class="card card-level-1"><p>Level 1</p><p>Subtle</p></div>
    <div class="card card-level-2"><p>Level 2</p><p>Medium</p></div>
    <div class="card card-level-3"><p>Level 3</p><p>Prominent</p></div>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shadow System</title>
  <style>
    :root {
      --shadow-0: none;
      --shadow-1: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
      --shadow-2: 0 4px 12px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08);
      --shadow-3: 0 10px 30px rgba(0,0,0,0.16), 0 4px 8px rgba(0,0,0,0.10);
    }
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; }
    .cards { display: flex; gap: 1.5rem; margin-top: 2rem; flex-wrap: wrap; }

    h1 {
      font-size: 2rem;
      text-shadow: 0 2px 4px rgba(0,0,0,0.15);
      color: #1a1a1a;
    }

    .card {
      flex: 1 1 150px;
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      transition: box-shadow 300ms ease;
    }
    .card p:first-child { font-weight: bold; margin-bottom: 4px; }
    .card p:last-child  { color: #666; font-size: 0.875rem; }

    .card-level-0 { box-shadow: var(--shadow-0); border: 1px solid #e0e0e0; }
    .card-level-1 { box-shadow: var(--shadow-1); }
    .card-level-2 { box-shadow: var(--shadow-2); }
    .card-level-3 { box-shadow: var(--shadow-3); }

    .card-level-2:hover { box-shadow: var(--shadow-3); }
    .card-level-3:hover { box-shadow: var(--shadow-3), 0 0 0 4px rgba(0,102,204,0.15); }
  </style>
</head>
<body>
  <h1>Shadow Elevation System</h1>
  <div class="cards">
    <div class="card card-level-0"><p>Level 0</p><p>No shadow</p></div>
    <div class="card card-level-1"><p>Level 1</p><p>Subtle</p></div>
    <div class="card card-level-2"><p>Level 2</p><p>Medium</p></div>
    <div class="card card-level-3"><p>Level 3</p><p>Prominent</p></div>
  </div>
</body>
</html>',
    'Use semi-transparent rgba shadow colours for natural results — avoid pure black shadows|Multiple comma-separated shadows create layered depth|Define shadows as CSS variables for a consistent system',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Filters and Blend Modes',
    'Create a Frosted Glass Card and Duotone Image',
    'Apply backdrop-filter and blend modes for visual effects.',
    "1. Create a colourful gradient background section\n2. Inside it, create a .glass-card using background rgba(255,255,255,0.15), backdrop-filter blur(16px), and a subtle white border\n3. In a second section, create a .duotone-image: add a photo (picsum), then overlay a gradient using a pseudo-element with mix-blend-mode: color\n4. Add a grayscale(100%) filter to an img and add :hover to reveal colour\n5. Add appropriate transitions to all effects",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Filters and Blend Modes</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; }
    /* gradient background section */
    /* .glass-card */
    /* .duotone-image */
    /* grayscale photo with hover */
  </style>
</head>
<body>
  <section style="background:linear-gradient(135deg,#0066cc,#ff6600);min-height:50vh;display:flex;align-items:center;justify-content:center;padding:2rem">
    <div class="glass-card">
      <h2>Glass Card</h2>
      <p>Frosted glass effect using backdrop-filter.</p>
    </div>
  </section>

  <section style="padding:2rem;background:#f5f5f5">
    <h2>Image Effects</h2>
    <div style="display:flex;gap:2rem;margin-top:1rem">
      <div class="duotone-image">
        <img src="https://picsum.photos/300/200" alt="Sample">
      </div>
      <img class="grayscale-photo" src="https://picsum.photos/300/200?random=2" alt="Grayscale photo">
    </div>
  </section>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Filters and Blend Modes</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; }

    .glass-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 16px;
      padding: 2.5rem;
      color: white;
      max-width: 350px;
      text-align: center;
    }
    .glass-card h2 { margin-bottom: 0.5rem; }

    .duotone-image {
      position: relative;
      display: inline-block;
    }
    .duotone-image img { display: block; }
    .duotone-image::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, #0066cc, #ff6600);
      mix-blend-mode: color;
    }

    .grayscale-photo {
      display: block;
      filter: grayscale(100%);
      transition: filter 400ms ease;
      border-radius: 4px;
    }
    .grayscale-photo:hover { filter: grayscale(0%); }
  </style>
</head>
<body>
  <section style="background:linear-gradient(135deg,#0066cc,#ff6600);min-height:50vh;display:flex;align-items:center;justify-content:center;padding:2rem">
    <div class="glass-card">
      <h2>Glass Card</h2>
      <p>Frosted glass effect using backdrop-filter.</p>
    </div>
  </section>
  <section style="padding:2rem;background:#f5f5f5">
    <h2>Image Effects</h2>
    <div style="display:flex;gap:2rem;margin-top:1rem;align-items:flex-start">
      <div class="duotone-image">
        <img src="https://picsum.photos/300/200" alt="Sample">
      </div>
      <img class="grayscale-photo" src="https://picsum.photos/300/200?random=2" alt="Grayscale photo" width="300" height="200">
    </div>
  </section>
</body>
</html>',
    'backdrop-filter needs a semi-transparent background to show the blur effect|mix-blend-mode: color applies colour without affecting brightness — perfect for duotone|filter: grayscale(100%) desaturates; 0% restores full colour',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'CSS Variables',
    'Build a Themeable Component System',
    'Create a set of components that can be entirely recoloured by changing CSS variable values.',
    "1. Define a :root with variables for primary, secondary, text, background, border colours, and spacing\n2. Build a .btn-primary, .btn-secondary, and a .card component all using only the CSS variables (no hardcoded colours)\n3. Create a [data-theme='dark'] overrides section that changes the colour variables\n4. Add a JavaScript click handler to a toggle button that switches the data-theme attribute on the documentElement\n5. Verify all components change colour automatically",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Variables Theme</title>
  <style>
    :root { /* define variables */ }
    [data-theme="dark"] { /* dark overrides */ }
    /* components using variables */
  </style>
</head>
<body>
  <button id="toggle">Toggle Dark Mode</button>
  <div class="card">
    <h3>Sample Card</h3>
    <p>All colours come from CSS variables.</p>
    <div style="margin-top:1rem;display:flex;gap:0.5rem">
      <button class="btn-primary">Primary</button>
      <button class="btn-secondary">Secondary</button>
    </div>
  </div>
  <script>
    /* add toggle logic here */
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Variables Theme</title>
  <style>
    :root {
      --color-primary:   #0066cc;
      --color-secondary: #ff6600;
      --color-text:      #1a1a1a;
      --color-bg:        #ffffff;
      --color-bg-alt:    #f9fafb;
      --color-border:    #e5e7eb;
      --space-md:        1rem;
      --space-lg:        1.5rem;
      --radius:          8px;
    }

    [data-theme="dark"] {
      --color-primary:   #4d9fff;
      --color-secondary: #ff8533;
      --color-text:      #e2e8f0;
      --color-bg:        #1a1a2e;
      --color-bg-alt:    #16213e;
      --color-border:    #334155;
    }

    * { box-sizing: border-box; margin: 0; }
    body {
      font-family: sans-serif;
      background: var(--color-bg);
      color: var(--color-text);
      padding: 2rem;
      transition: background 300ms, color 300ms;
    }

    #toggle {
      margin-bottom: 1.5rem;
      padding: 8px 16px;
      cursor: pointer;
      border: 1px solid var(--color-border);
      background: var(--color-bg-alt);
      color: var(--color-text);
      border-radius: var(--radius);
    }

    .card {
      background: var(--color-bg-alt);
      border: 1px solid var(--color-border);
      border-radius: var(--radius);
      padding: var(--space-lg);
      max-width: 400px;
    }
    .card h3 { margin-bottom: 0.5rem; color: var(--color-text); }

    .btn-primary, .btn-secondary {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.9rem;
    }
    .btn-primary   { background: var(--color-primary);   color: white; }
    .btn-secondary { background: var(--color-secondary); color: white; }
  </style>
</head>
<body>
  <button id="toggle">Toggle Dark Mode</button>
  <div class="card">
    <h3>Sample Card</h3>
    <p>All colours come from CSS variables.</p>
    <div style="margin-top:1rem;display:flex;gap:0.5rem">
      <button class="btn-primary">Primary</button>
      <button class="btn-secondary">Secondary</button>
    </div>
  </div>
  <script>
    document.getElementById("toggle").addEventListener("click", () => {
      const html = document.documentElement;
      html.setAttribute("data-theme",
        html.getAttribute("data-theme") === "dark" ? "light" : "dark");
    });
  </script>
</body>
</html>',
    'CSS variables are defined with --name: value and used with var(--name)|Scoped overrides like [data-theme="dark"] cascade to all children|JavaScript can toggle themes by changing an attribute on the html element',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'CSS Methodologies (BEM)',
    'Build a BEM-Named Card Component',
    'Create a card component strictly following BEM naming conventions.',
    "1. Create a card block with class .card\n2. Add an image element: .card__image\n3. Add a body container: .card__body\n4. Inside body: .card__title, .card__description, .card__meta (for date/category)\n5. Add a footer: .card__footer with a .card__link\n6. Create a modifier .card--featured that changes the border and adds a badge\n7. Use only BEM classes — no descendant selectors in CSS",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>BEM Card</title>
  <style>
    /* BEM styles — use .card, .card__element, .card--modifier only */
  </style>
</head>
<body>
  <!-- Normal card -->
  <article class="card">
    <img class="card__image" src="https://picsum.photos/400/200" alt="Course thumbnail">
    <div class="card__body">
      <span class="card__meta">Web Development</span>
      <h3 class="card__title">HTML Basics</h3>
      <p class="card__description">Learn the fundamentals of HTML structure.</p>
    </div>
    <footer class="card__footer">
      <a class="card__link" href="#">Enroll Now</a>
    </footer>
  </article>

  <!-- Featured card -->
  <article class="card card--featured">
    <!-- same structure -->
    <img class="card__image" src="https://picsum.photos/400/200?2" alt="Course thumbnail">
    <div class="card__body">
      <span class="card__meta">Most Popular</span>
      <h3 class="card__title">Full Stack Bootcamp</h3>
      <p class="card__description">Complete web development from start to finish.</p>
    </div>
    <footer class="card__footer">
      <a class="card__link" href="#">Enroll Now</a>
    </footer>
  </article>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>BEM Card</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; display: flex; gap: 1.5rem; }

    .card {
      width: 320px;
      background: white;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      overflow: hidden;
    }

    .card--featured {
      border-color: #0066cc;
      box-shadow: 0 4px 16px rgba(0,102,204,0.2);
    }

    .card__image {
      width: 100%;
      height: 180px;
      object-fit: cover;
      display: block;
    }

    .card__body {
      padding: 1.25rem;
    }

    .card__meta {
      display: inline-block;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #0066cc;
      margin-bottom: 0.5rem;
    }

    .card--featured .card__meta {
      color: #ff6600;
      font-weight: 700;
    }

    .card__title {
      font-size: 1.1rem;
      color: #1a1a1a;
      margin-bottom: 0.5rem;
    }

    .card__description {
      font-size: 0.9rem;
      color: #666;
      line-height: 1.5;
    }

    .card__footer {
      padding: 1rem 1.25rem;
      border-top: 1px solid #e0e0e0;
    }

    .card__link {
      color: #0066cc;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
    }

    .card__link:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <article class="card">
    <img class="card__image" src="https://picsum.photos/400/200" alt="Course thumbnail">
    <div class="card__body">
      <span class="card__meta">Web Development</span>
      <h3 class="card__title">HTML Basics</h3>
      <p class="card__description">Learn the fundamentals of HTML structure.</p>
    </div>
    <footer class="card__footer">
      <a class="card__link" href="#">Enroll Now</a>
    </footer>
  </article>

  <article class="card card--featured">
    <img class="card__image" src="https://picsum.photos/400/200?2" alt="Course thumbnail">
    <div class="card__body">
      <span class="card__meta">Most Popular</span>
      <h3 class="card__title">Full Stack Bootcamp</h3>
      <p class="card__description">Complete web development from start to finish.</p>
    </div>
    <footer class="card__footer">
      <a class="card__link" href="#">Enroll Now</a>
    </footer>
  </article>
</body>
</html>',
    'BEM: Block (card), Element (card__title), Modifier (card--featured)|Double underscore separates block from element; double dash marks a modifier|Modifiers are added alongside the base class: class="card card--featured"',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'CSS Architecture Patterns',
    'Organise CSS with Utility Classes and Components',
    'Build a page that uses a mix of utility classes and component classes in an organised structure.',
    "1. Create a set of utility classes: .flex, .items-center, .justify-between, .gap-4, .p-4, .p-8, .rounded, .shadow, .text-center, .hidden\n2. Create component classes: .btn, .btn-primary, .card following BEM-lite style\n3. Build a simple page layout using only the utility and component classes — no custom styles on the individual elements\n4. Include a comment block at the top labelling sections: Utilities and Components\n5. Demonstrate that the button works via utility composition AND component class",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Architecture</title>
  <style>
    /* === Utilities === */

    /* === Components === */
  </style>
</head>
<body>
  <!-- Layout using utility classes -->
  <header class="flex items-center justify-between p-4 shadow">
    <span>Logo</span>
    <nav class="flex gap-4">
      <a href="#">Home</a>
      <a href="#">About</a>
    </nav>
  </header>

  <!-- Component using class -->
  <main class="p-8">
    <div class="card p-4 rounded shadow">
      <h2>Hello World</h2>
      <button class="btn btn-primary">Get Started</button>
    </div>
  </main>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Architecture</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; background: #f5f5f5; }
    a { color: inherit; text-decoration: none; }

    /* === Utilities === */
    .flex           { display: flex; }
    .items-center   { align-items: center; }
    .justify-between { justify-content: space-between; }
    .gap-4          { gap: 1rem; }
    .p-4            { padding: 1rem; }
    .p-8            { padding: 2rem; }
    .rounded        { border-radius: 8px; }
    .shadow         { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .text-center    { text-align: center; }
    .hidden         { display: none; }
    .bg-white       { background: white; }

    /* === Components === */
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 1rem;
    }

    .btn-primary {
      background: #0066cc;
      color: white;
    }
    .btn-primary:hover { background: #004fa3; }

    .card {
      background: white;
      max-width: 400px;
    }

    header { background: white; }
  </style>
</head>
<body>
  <header class="flex items-center justify-between p-4 shadow bg-white">
    <span style="font-weight:bold">Logo</span>
    <nav class="flex gap-4">
      <a href="#">Home</a>
      <a href="#">About</a>
    </nav>
  </header>

  <main class="p-8">
    <div class="card p-4 rounded shadow">
      <h2>Hello World</h2>
      <p>This card is built with component and utility classes.</p>
      <button class="btn btn-primary">Get Started</button>
    </div>
  </main>
</body>
</html>',
    'Utilities do exactly one thing — they are reusable atoms|Component classes bundle multiple styles for a specific UI pattern|Utilities and components work together — components for complex parts, utilities for layout',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Modern CSS Features',
    'Use Container Queries for a Responsive Card',
    'Build a card component that responds to its container width using container queries.',
    "1. Create two containers: .small-container (max-width 300px) and .large-container (max-width 700px)\n2. Mark both as container-type: inline-size with container-name: card-wrapper\n3. Create a .product-card with horizontal layout (image + text side by side) inside large containers, and vertical layout (stacked) inside small containers\n4. Use @container card-wrapper (min-width: 400px) to switch the layout\n5. Change the image dimensions based on container size using container queries",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Container Queries</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; display: flex; flex-direction: column; gap: 2rem; }
    /* container setup */
    /* .product-card base (mobile/small) */
    /* @container rule for large containers */
  </style>
</head>
<body>
  <div class="small-container">
    <div class="product-card">
      <img src="https://picsum.photos/300/200" alt="Product">
      <div class="product-card__info">
        <h3>Course Title</h3>
        <p>Learn web development from scratch.</p>
        <button>Enroll</button>
      </div>
    </div>
  </div>

  <div class="large-container">
    <div class="product-card">
      <img src="https://picsum.photos/300/200?2" alt="Product">
      <div class="product-card__info">
        <h3>Course Title</h3>
        <p>Learn web development from scratch with comprehensive lessons.</p>
        <button>Enroll</button>
      </div>
    </div>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Container Queries</title>
  <style>
    * { box-sizing: border-box; margin: 0; }
    body { font-family: sans-serif; padding: 2rem; background: #f5f5f5; display: flex; flex-direction: column; gap: 2rem; }

    .small-container {
      max-width: 300px;
      container-type: inline-size;
      container-name: card-wrapper;
    }

    .large-container {
      max-width: 700px;
      container-type: inline-size;
      container-name: card-wrapper;
    }

    /* Base (small container): vertical layout */
    .product-card {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .product-card img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      display: block;
    }

    .product-card__info {
      padding: 1rem;
    }

    .product-card__info h3 { margin-bottom: 0.5rem; }
    .product-card__info p  { color: #666; margin-bottom: 1rem; font-size: 0.9rem; }
    .product-card__info button {
      padding: 8px 16px;
      background: #0066cc;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    /* Large container: horizontal layout */
    @container card-wrapper (min-width: 400px) {
      .product-card {
        display: flex;
        align-items: stretch;
      }

      .product-card img {
        width: 200px;
        height: auto;
        flex-shrink: 0;
      }

      .product-card__info {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <div class="small-container">
    <div class="product-card">
      <img src="https://picsum.photos/300/200" alt="Product">
      <div class="product-card__info">
        <h3>Course Title</h3>
        <p>Learn web development from scratch.</p>
        <button>Enroll</button>
      </div>
    </div>
  </div>
  <div class="large-container">
    <div class="product-card">
      <img src="https://picsum.photos/300/200?2" alt="Product">
      <div class="product-card__info">
        <h3>Course Title</h3>
        <p>Learn web development from scratch with comprehensive lessons.</p>
        <button>Enroll</button>
      </div>
    </div>
  </div>
</body>
</html>',
    'container-type: inline-size must be set on the PARENT before @container queries work on its children|@container queries respond to container width, not viewport width|The same component can look different in different containers without any extra classes',
    'css', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'CSS Best Practices',
    'Apply a CSS Reset and Organised Stylesheet',
    'Write a well-organised stylesheet that starts with a reset and follows property ordering conventions.',
    "1. Write a modern CSS reset (box-sizing, body margin, image max-width, input font inherit)\n2. Define CSS custom properties in :root for colours, font sizes, and spacing\n3. Write base element styles: body font, h1-h3 sizes, link colour\n4. Write a .container utility with max-width, margin auto, and responsive padding\n5. Write a .btn component with all properties in this order: position → display → box model → typography → visual → transitions\n6. Add a comment header block identifying each section",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Best Practices</title>
  <style>
    /* =================================
       1. CSS Reset
       ================================= */

    /* =================================
       2. Design Tokens (Custom Properties)
       ================================= */

    /* =================================
       3. Base Element Styles
       ================================= */

    /* =================================
       4. Utilities
       ================================= */

    /* =================================
       5. Components
       ================================= */
  </style>
</head>
<body>
  <div class="container">
    <h1>Best Practices Demo</h1>
    <p>A well-organised stylesheet makes maintenance much easier.</p>
    <a href="#" class="btn">Learn More</a>
  </div>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>CSS Best Practices</title>
  <style>
    /* =================================
       1. CSS Reset
       ================================= */
    *, *::before, *::after { box-sizing: border-box; }
    body, h1, h2, h3, p, ul { margin: 0; padding: 0; }
    img, video { max-width: 100%; height: auto; display: block; }
    input, button, textarea, select { font: inherit; }

    /* =================================
       2. Design Tokens
       ================================= */
    :root {
      --color-primary:  #0066cc;
      --color-text:     #1a1a1a;
      --color-bg:       #ffffff;
      --font-size-base: 1rem;
      --font-size-lg:   1.25rem;
      --space-4:        1rem;
      --space-8:        2rem;
      --radius:         6px;
    }

    /* =================================
       3. Base Element Styles
       ================================= */
    body {
      font-family: -apple-system, BlinkMacSystemFont, sans-serif;
      font-size: var(--font-size-base);
      line-height: 1.6;
      color: var(--color-text);
      background: var(--color-bg);
    }

    h1 { font-size: 2.5rem; line-height: 1.2; }
    h2 { font-size: 2rem;   line-height: 1.2; }
    h3 { font-size: 1.5rem; line-height: 1.3; }

    a { color: var(--color-primary); }
    a:hover { text-decoration: none; }

    /* =================================
       4. Utilities
       ================================= */
    .container {
      width: 100%;
      max-width: 1200px;
      margin-inline: auto;
      padding-inline: var(--space-4);
      padding-block: var(--space-8);
    }

    /* =================================
       5. Components
       ================================= */
    .btn {
      /* Display & Box Model */
      display: inline-block;
      padding: 10px 20px;
      /* Typography */
      font-size: var(--font-size-base);
      font-weight: 600;
      text-decoration: none;
      /* Visual */
      background: var(--color-primary);
      color: white;
      border: none;
      border-radius: var(--radius);
      /* Interaction */
      cursor: pointer;
      transition: background-color 200ms ease;
    }

    .btn:hover { background: #004fa3; }
  </style>
</head>
<body>
  <div class="container">
    <h1>Best Practices Demo</h1>
    <p>A well-organised stylesheet makes maintenance much easier.</p>
    <a href="#" class="btn">Learn More</a>
  </div>
</body>
</html>',
    'A CSS reset eliminates cross-browser default style differences|Comment blocks clearly separate sections for maintainability|Always define a design token system in :root before writing component styles',
    'css', 'easy', 10, $exercisesInserted);

// =====================================================================
// CODING EXERCISES — JavaScript Course (20 exercises)
// =====================================================================

addExercise($pdo, 'Variables and Data Types',
    'Declare Variables and Check Types',
    'Practice declaring variables with const and let, and using typeof to inspect values.',
    "1. Declare a const for your name (string), a const for your age (number), a const for isStudent (boolean)\n2. Declare a let variable called score and assign it 0\n3. Use typeof to check and display the type of each variable in the output div\n4. Reassign score to 85 and display it\n5. Show the result of typeof null (note the famous quirk)",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Variables</title></head>
<body>
  <h1>Variables and Data Types</h1>
  <div id="output"></div>
  <script>
    const output = document.getElementById("output");

    // 1. Declare const variables
    // const name = ...
    // const age = ...
    // const isStudent = ...

    // 2. Declare let variable
    // let score = 0;

    // 3. Use typeof to display types
    // output.innerHTML += `<p>name: "${name}" — type: ${typeof name}</p>`;
    // (add similar lines for age, isStudent, score)

    // 4. Reassign score and display
    // score = 85;

    // 5. Show typeof null quirk
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Variables</title></head>
<body>
  <h1>Variables and Data Types</h1>
  <div id="output"></div>
  <script>
    const output = document.getElementById("output");

    const name     = "Alex Kamau";
    const age      = 25;
    const isStudent = true;
    let score      = 0;

    output.innerHTML += `<p><strong>name:</strong> "${name}" — type: <em>${typeof name}</em></p>`;
    output.innerHTML += `<p><strong>age:</strong> ${age} — type: <em>${typeof age}</em></p>`;
    output.innerHTML += `<p><strong>isStudent:</strong> ${isStudent} — type: <em>${typeof isStudent}</em></p>`;
    output.innerHTML += `<p><strong>score (initial):</strong> ${score} — type: <em>${typeof score}</em></p>`;

    score = 85;
    output.innerHTML += `<p><strong>score (updated):</strong> ${score}</p>`;

    output.innerHTML += `<p><strong>typeof null:</strong> "${typeof null}" — This is a famous JavaScript quirk!</p>`;
  </script>
</body>
</html>',
    'Use const for values that will not change; use let when the value will be reassigned|typeof returns a string: "string", "number", "boolean", "undefined", "object", "function"|typeof null returns "object" — this is a historical bug in JavaScript that was never fixed',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Operators and Expressions',
    'Build a Simple Calculator',
    'Use arithmetic, comparison, and logical operators to build a calculator.',
    "1. Declare two number variables: a = 15 and b = 4\n2. Calculate and display: sum, difference, product, quotient, remainder, and power\n3. Show the result of three comparison expressions using ===, >, <=\n4. Show a logical AND and OR expression with the results\n5. Use the ternary operator to display 'even' or 'odd' for both a and b",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Operators</title></head>
<body>
  <h1>Operator Expressions</h1>
  <div id="output"></div>
  <script>
    const out = document.getElementById("output");
    const a = 15, b = 4;

    // Arithmetic
    // Comparisons
    // Logical
    // Ternary for even/odd
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Operators</title></head>
<body>
  <h1>Operator Expressions</h1>
  <div id="output"></div>
  <script>
    const out = document.getElementById("output");
    const a = 15, b = 4;

    out.innerHTML += `<h3>Arithmetic</h3>`;
    out.innerHTML += `<p>${a} + ${b} = ${a + b}</p>`;
    out.innerHTML += `<p>${a} - ${b} = ${a - b}</p>`;
    out.innerHTML += `<p>${a} * ${b} = ${a * b}</p>`;
    out.innerHTML += `<p>${a} / ${b} = ${a / b}</p>`;
    out.innerHTML += `<p>${a} % ${b} = ${a % b} (remainder)</p>`;
    out.innerHTML += `<p>${a} ** ${b} = ${a ** b} (power)</p>`;

    out.innerHTML += `<h3>Comparisons</h3>`;
    out.innerHTML += `<p>${a} === ${b}: ${a === b}</p>`;
    out.innerHTML += `<p>${a} > ${b}: ${a > b}</p>`;
    out.innerHTML += `<p>${b} <= 4: ${b <= 4}</p>`;

    out.innerHTML += `<h3>Logical</h3>`;
    out.innerHTML += `<p>(a > 10) && (b > 3): ${(a > 10) && (b > 3)}</p>`;
    out.innerHTML += `<p>(a > 20) || (b === 4): ${(a > 20) || (b === 4)}</p>`;

    out.innerHTML += `<h3>Ternary (even/odd)</h3>`;
    out.innerHTML += `<p>${a} is ${a % 2 === 0 ? "even" : "odd"}</p>`;
    out.innerHTML += `<p>${b} is ${b % 2 === 0 ? "even" : "odd"}</p>`;
  </script>
</body>
</html>',
    'The % operator returns the remainder — if n % 2 === 0, the number is even|Always use === not == for comparisons to avoid type coercion|Ternary syntax: condition ? valueIfTrue : valueIfFalse',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Control Flow',
    'Grade Classifier',
    'Build a function that classifies exam scores into grades using if/else.',
    "1. Write a getGrade(score) function that returns 'A' (90+), 'B' (80+), 'C' (70+), 'D' (60+), 'F' (below 60)\n2. Write a getFeedback(grade) function using a switch statement that returns appropriate feedback for each grade\n3. Test with scores: 95, 83, 71, 65, 45\n4. Display each score, its grade, and its feedback in the output\n5. Add input validation: if score is not between 0-100, return 'Invalid score'",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Control Flow</title></head>
<body>
  <h1>Grade Classifier</h1>
  <div id="output"></div>
  <script>
    function getGrade(score) {
      // Add validation and if/else chain here
    }

    function getFeedback(grade) {
      // Add switch statement here
    }

    const scores = [95, 83, 71, 65, 45, 110];
    const output = document.getElementById("output");

    scores.forEach(score => {
      const grade = getGrade(score);
      const feedback = grade !== "Invalid score" ? getFeedback(grade) : "";
      output.innerHTML += `<p>Score: ${score} → Grade: <strong>${grade}</strong> ${feedback ? "— " + feedback : ""}</p>`;
    });
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Control Flow</title></head>
<body>
  <h1>Grade Classifier</h1>
  <div id="output"></div>
  <script>
    function getGrade(score) {
      if (typeof score !== "number" || score < 0 || score > 100) {
        return "Invalid score";
      }
      if (score >= 90) return "A";
      if (score >= 80) return "B";
      if (score >= 70) return "C";
      if (score >= 60) return "D";
      return "F";
    }

    function getFeedback(grade) {
      switch (grade) {
        case "A": return "Outstanding! Excellent work.";
        case "B": return "Great job! Above average.";
        case "C": return "Good effort. Keep it up.";
        case "D": return "Needs improvement. Review the material.";
        case "F": return "Did not pass. Seek extra help.";
        default:  return "";
      }
    }

    const scores = [95, 83, 71, 65, 45, 110];
    const output = document.getElementById("output");

    scores.forEach(score => {
      const grade    = getGrade(score);
      const feedback = grade !== "Invalid score" ? getFeedback(grade) : "Please provide a valid score (0-100).";
      output.innerHTML += `<p>Score: ${score} → Grade: <strong>${grade}</strong> — ${feedback}</p>`;
    });
  </script>
</body>
</html>',
    'Guard clauses (early returns) at the top keep main logic flat and readable|Each case in a switch needs a break to prevent fall-through|Use if/else chains when values must be compared with ranges; use switch for exact value matching',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Loops',
    'Build a Times Table Generator',
    'Use loops and array methods to generate and display a times table.',
    "1. Write a function timesTable(n) that returns an array of 12 multiplication results for n (n*1 through n*12)\n2. Write a function displayTable(n) that builds a formatted HTML string and puts it in the output div\n3. Use a for loop in timesTable and a forEach in displayTable\n4. Use Array.from({length: 12}, ...) as an alternative to loop for generating the array\n5. Automatically display the times table for 7 on page load",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Times Tables</title></head>
<body>
  <h1 id="table-title">Times Table</h1>
  <div id="output"></div>
  <script>
    function timesTable(n) {
      // return array [n*1, n*2, ..., n*12]
    }

    function displayTable(n) {
      // build HTML and put in #output
    }

    displayTable(7);
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Times Tables</title></head>
<body>
  <h1 id="table-title">Times Table</h1>
  <div id="output"></div>
  <script>
    function timesTable(n) {
      const results = [];
      for (let i = 1; i <= 12; i++) {
        results.push(n * i);
      }
      return results;
    }

    // Alternative using Array.from:
    // const timesTable = n => Array.from({ length: 12 }, (_, i) => n * (i + 1));

    function displayTable(n) {
      const output = document.getElementById("output");
      const title  = document.getElementById("table-title");

      title.textContent = `${n} Times Table`;
      output.innerHTML = "";

      const results = timesTable(n);
      results.forEach((result, index) => {
        const row = document.createElement("p");
        row.textContent = `${n} × ${index + 1} = ${result}`;
        if (result % 2 === 0) row.style.color = "#0066cc";
        output.appendChild(row);
      });
    }

    displayTable(7);
  </script>
</body>
</html>',
    'for loops use an index variable; forEach provides value and index as arguments|Array.from({length: n}, (_, i) => i+1) is a concise way to create sequences|The forEach callback receives (element, index, array) — use what you need',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Functions',
    'Build a Temperature Converter',
    'Write pure functions for temperature conversion and a closure-based unit preference.',
    "1. Write a pure function celsiusToFahrenheit(c) that converts Celsius to Fahrenheit\n2. Write a pure function fahrenheitToCelsius(f) that does the reverse\n3. Write a function makeConverter(preferredUnit) that returns a closure — a function that converts any temperature to the preferred unit\n4. Create two converters: one for Celsius, one for Fahrenheit\n5. Test both converters with temperatures: 0, 100, 37, -40 and display results",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Temperature Converter</title></head>
<body>
  <h1>Temperature Converter</h1>
  <div id="output"></div>
  <script>
    function celsiusToFahrenheit(c) {
      // formula: (c * 9/5) + 32
    }

    function fahrenheitToCelsius(f) {
      // formula: (f - 32) * 5/9
    }

    function makeConverter(preferredUnit) {
      // return a closure
    }

    const toFahrenheit = makeConverter("F");
    const toCelsius    = makeConverter("C");

    const output = document.getElementById("output");
    const temps  = [0, 100, 37, -40];
    // display conversions
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Temperature Converter</title></head>
<body>
  <h1>Temperature Converter</h1>
  <div id="output"></div>
  <script>
    function celsiusToFahrenheit(c) {
      return (c * 9 / 5) + 32;
    }

    function fahrenheitToCelsius(f) {
      return (f - 32) * 5 / 9;
    }

    function makeConverter(preferredUnit) {
      return function(temp, inputUnit) {
        if (inputUnit === preferredUnit) return temp;
        if (preferredUnit === "F") return celsiusToFahrenheit(temp);
        return fahrenheitToCelsius(temp);
      };
    }

    const toFahrenheit = makeConverter("F");
    const toCelsius    = makeConverter("C");

    const output = document.getElementById("output");
    const temps  = [0, 100, 37, -40];

    output.innerHTML += "<h3>Celsius to Fahrenheit</h3>";
    temps.forEach(t => {
      const result = toFahrenheit(t, "C");
      output.innerHTML += `<p>${t}°C = ${result.toFixed(1)}°F</p>`;
    });

    output.innerHTML += "<h3>Fahrenheit to Celsius</h3>";
    temps.forEach(t => {
      const result = toCelsius(t, "F");
      output.innerHTML += `<p>${t}°F = ${result.toFixed(1)}°C</p>`;
    });
  </script>
</body>
</html>',
    'Pure functions always return the same output for the same input and have no side effects|A closure is a function that remembers variables from its outer scope even after the outer function has returned|toFixed(n) rounds to n decimal places and returns a string',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'DOM Selection',
    'Explore the DOM',
    'Practice all DOM selection methods and read element properties.',
    "1. Select the #main-heading by ID and log its textContent\n2. Select the first .card using querySelector\n3. Select ALL .card elements using querySelectorAll and count them\n4. Select the nav and then use scoped selection to find all links inside it only\n5. Read and display: the first card's className, dataset.id, and getBoundingClientRect().width",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>DOM Selection</title></head>
<body>
  <h1 id="main-heading">DOM Selection Practice</h1>
  <nav id="main-nav">
    <a href="#">Home</a>
    <a href="#">About</a>
    <a href="#">Courses</a>
  </nav>
  <div class="cards">
    <div class="card" data-id="1">Card 1</div>
    <div class="card" data-id="2">Card 2</div>
    <div class="card" data-id="3">Card 3</div>
  </div>
  <div id="output" style="margin-top:1rem;padding:1rem;background:#f5f5f5;border-radius:8px"></div>
  <script>
    const output = document.getElementById("output");
    // 1. Select heading by ID

    // 2. Select first .card

    // 3. Select ALL .card elements

    // 4. Scoped nav link selection

    // 5. Read card properties
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>DOM Selection</title></head>
<body>
  <h1 id="main-heading">DOM Selection Practice</h1>
  <nav id="main-nav">
    <a href="#">Home</a>
    <a href="#">About</a>
    <a href="#">Courses</a>
  </nav>
  <div class="cards">
    <div class="card" data-id="1">Card 1</div>
    <div class="card" data-id="2">Card 2</div>
    <div class="card" data-id="3">Card 3</div>
  </div>
  <div id="output" style="margin-top:1rem;padding:1rem;background:#f5f5f5;border-radius:8px"></div>
  <script>
    const output = document.getElementById("output");

    // 1. Select by ID
    const heading = document.getElementById("main-heading");
    output.innerHTML += `<p>Heading text: "${heading.textContent}"</p>`;

    // 2. First .card
    const firstCard = document.querySelector(".card");
    output.innerHTML += `<p>First card text: "${firstCard.textContent}"</p>`;

    // 3. All .card elements
    const allCards = document.querySelectorAll(".card");
    output.innerHTML += `<p>Total cards: ${allCards.length}</p>`;

    // 4. Scoped nav selection
    const nav = document.getElementById("main-nav");
    const navLinks = nav.querySelectorAll("a");
    output.innerHTML += `<p>Nav links: ${navLinks.length}</p>`;
    navLinks.forEach(link => output.innerHTML += `<p>  Link: "${link.textContent}"</p>`);

    // 5. Card properties
    output.innerHTML += `<p>First card className: "${firstCard.className}"</p>`;
    output.innerHTML += `<p>First card data-id: "${firstCard.dataset.id}"</p>`;
    const rect = firstCard.getBoundingClientRect();
    output.innerHTML += `<p>First card width: ${rect.width.toFixed(0)}px</p>`;
  </script>
</body>
</html>',
    'getElementById is the fastest single-element lookup|querySelectorAll returns a NodeList — use forEach to iterate it|Scoped selection: parent.querySelector searches only within that parent element',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'DOM Manipulation',
    'Build a Theme Toggle',
    'Use DOM manipulation to toggle a dark/light theme on a page.',
    "1. Create a button that toggles between dark and light mode\n2. When clicked, add/remove a 'dark-mode' class on the body using classList.toggle\n3. Update the button text to show 'Switch to Light Mode' in dark mode and 'Switch to Dark Mode' in light mode\n4. Change a .theme-indicator paragraph's textContent to show the current mode\n5. Store the preference — update a CSS custom property on document.documentElement for the background colour",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Theme Toggle</title>
  <style>
    :root { --bg: #ffffff; --text: #1a1a1a; }
    body  { background: var(--bg); color: var(--text); font-family: sans-serif; padding: 2rem; transition: background 300ms, color 300ms; }
    .dark-mode { --bg: #1a1a2e; --text: #e2e8f0; }
    button { padding: 10px 20px; cursor: pointer; }
  </style>
</head>
<body>
  <h1>Theme Toggle Demo</h1>
  <p class="theme-indicator">Current mode: Light</p>
  <button id="theme-btn">Switch to Dark Mode</button>
  <script>
    // Your code here
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Theme Toggle</title>
  <style>
    :root { --bg: #ffffff; --text: #1a1a1a; }
    body  { background: var(--bg); color: var(--text); font-family: sans-serif; padding: 2rem; transition: background 300ms, color 300ms; }
    .dark-mode { --bg: #1a1a2e; --text: #e2e8f0; }
    button { padding: 10px 20px; cursor: pointer; border-radius: 6px; border: 1px solid #ccc; }
  </style>
</head>
<body>
  <h1>Theme Toggle Demo</h1>
  <p class="theme-indicator">Current mode: Light</p>
  <button id="theme-btn">Switch to Dark Mode</button>
  <script>
    const btn       = document.getElementById("theme-btn");
    const indicator = document.querySelector(".theme-indicator");

    btn.addEventListener("click", () => {
      const isDark = document.body.classList.toggle("dark-mode");

      btn.textContent       = isDark ? "Switch to Light Mode" : "Switch to Dark Mode";
      indicator.textContent = `Current mode: ${isDark ? "Dark" : "Light"}`;
    });
  </script>
</body>
</html>',
    'classList.toggle adds the class if absent and removes it if present — returns true if class is now present|Update button text and indicators immediately when state changes|CSS custom properties on :root update everywhere the variable is used',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Event Handling',
    'Build a Click Counter with Keyboard Support',
    'Create an interactive counter that responds to both click and keyboard events.',
    "1. Create buttons: +1, -1, and Reset\n2. Display the current count in a large number element\n3. Add click event listeners to all three buttons\n4. Also listen for keyboard events: ArrowUp increments, ArrowDown decrements, r resets, and prevent default for arrow keys\n5. Change the count display colour: green if positive, red if negative, black if zero",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Click Counter</title>
  <style>
    body { font-family: sans-serif; text-align: center; padding: 2rem; }
    #count { font-size: 5rem; font-weight: bold; margin: 1rem; }
    .controls { display: flex; gap: 1rem; justify-content: center; }
    button { padding: 12px 24px; font-size: 1rem; cursor: pointer; border-radius: 6px; border: none; }
  </style>
</head>
<body>
  <h1>Counter</h1>
  <div id="count">0</div>
  <p>Use buttons or keyboard: ↑ increment, ↓ decrement, R reset</p>
  <div class="controls">
    <button id="decrement" style="background:#dc3545;color:white">-1</button>
    <button id="reset"     style="background:#6c757d;color:white">Reset</button>
    <button id="increment" style="background:#28a745;color:white">+1</button>
  </div>
  <script>
    let count = 0;
    // implement counter logic here
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Click Counter</title>
  <style>
    body { font-family: sans-serif; text-align: center; padding: 2rem; }
    #count { font-size: 5rem; font-weight: bold; margin: 1rem; transition: color 200ms; }
    .controls { display: flex; gap: 1rem; justify-content: center; }
    button { padding: 12px 24px; font-size: 1rem; cursor: pointer; border-radius: 6px; border: none; }
  </style>
</head>
<body>
  <h1>Counter</h1>
  <div id="count">0</div>
  <p>Use buttons or keyboard: ↑ increment, ↓ decrement, R reset</p>
  <div class="controls">
    <button id="decrement" style="background:#dc3545;color:white">-1</button>
    <button id="reset"     style="background:#6c757d;color:white">Reset</button>
    <button id="increment" style="background:#28a745;color:white">+1</button>
  </div>
  <script>
    let count = 0;
    const display = document.getElementById("count");

    function updateDisplay() {
      display.textContent = count;
      display.style.color = count > 0 ? "#28a745" : count < 0 ? "#dc3545" : "#1a1a1a";
    }

    document.getElementById("increment").addEventListener("click", () => { count++; updateDisplay(); });
    document.getElementById("decrement").addEventListener("click", () => { count--; updateDisplay(); });
    document.getElementById("reset").addEventListener("click",     () => { count = 0; updateDisplay(); });

    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowUp")   { e.preventDefault(); count++; updateDisplay(); }
      if (e.key === "ArrowDown") { e.preventDefault(); count--; updateDisplay(); }
      if (e.key === "r" || e.key === "R") { count = 0; updateDisplay(); }
    });
  </script>
</body>
</html>',
    'event.key contains the key name as a string ("ArrowUp", "Enter", "r")|event.preventDefault() stops the default browser action — needed for arrow keys to prevent page scroll|Extract shared logic into a function (updateDisplay) called by all event handlers',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Creating Elements',
    'Build a Dynamic To-Do List',
    'Create a to-do list that adds and removes items dynamically using createElement.',
    "1. Create an addTodo(text) function that creates a new li element with: the todo text, a checkbox, and a delete button\n2. When the form is submitted, call addTodo with the input value, then clear the input\n3. The checkbox should strike through the text when checked (toggle a .done class)\n4. The delete button should remove the li from the list\n5. Use textContent (not innerHTML) for the todo text to prevent XSS",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Todo List</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 0 1rem; }
    .todo-item { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee; }
    .todo-item.done span { text-decoration: line-through; color: #999; }
    .todo-item span { flex: 1; }
    button { padding: 4px 10px; cursor: pointer; }
  </style>
</head>
<body>
  <h1>To-Do List</h1>
  <form id="todo-form">
    <input id="todo-input" type="text" placeholder="Add a task..." required style="padding:8px;width:70%">
    <button type="submit">Add</button>
  </form>
  <ul id="todo-list" style="list-style:none;padding:0;margin-top:1rem"></ul>
  <script>
    // Implement addTodo and form submission here
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Todo List</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 0 1rem; }
    .todo-item { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #eee; }
    .todo-item.done span { text-decoration: line-through; color: #999; }
    .todo-item span { flex: 1; }
    .delete-btn { background: #dc3545; color: white; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer; }
  </style>
</head>
<body>
  <h1>To-Do List</h1>
  <form id="todo-form">
    <input id="todo-input" type="text" placeholder="Add a task..." required style="padding:8px;width:70%">
    <button type="submit" style="padding:8px 16px">Add</button>
  </form>
  <ul id="todo-list" style="list-style:none;padding:0;margin-top:1rem"></ul>
  <script>
    function addTodo(text) {
      const list = document.getElementById("todo-list");

      const li       = document.createElement("li");
      const checkbox = document.createElement("input");
      const label    = document.createElement("span");
      const delBtn   = document.createElement("button");

      li.className       = "todo-item";
      checkbox.type      = "checkbox";
      label.textContent  = text;  // textContent prevents XSS
      delBtn.textContent = "Delete";
      delBtn.className   = "delete-btn";

      checkbox.addEventListener("change", () => {
        li.classList.toggle("done", checkbox.checked);
      });

      delBtn.addEventListener("click", () => li.remove());

      li.append(checkbox, label, delBtn);
      list.appendChild(li);
    }

    document.getElementById("todo-form").addEventListener("submit", (e) => {
      e.preventDefault();
      const input = document.getElementById("todo-input");
      const text  = input.value.trim();
      if (text) {
        addTodo(text);
        input.value = "";
        input.focus();
      }
    });
  </script>
</body>
</html>',
    'Always use textContent for user-provided text to prevent XSS — never innerHTML|element.remove() removes it from the DOM; no parent reference needed|classList.toggle("class", condition) adds when true, removes when false',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Forms and Validation',
    'Build a Registration Form with Live Validation',
    'Implement client-side validation with inline error messages and real-time feedback.',
    "1. Create a form with: username, email, password, and confirm-password fields\n2. Validate username on blur: required, 3-20 chars, letters/numbers only\n3. Validate email on blur: required and must match email regex\n4. Validate password on blur: required, minimum 8 chars\n5. Validate confirm-password: must match the password field\n6. On submit: show all errors at once; if all valid, show a success message",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Form Validation</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: sans-serif; max-width: 400px; margin: 2rem auto; padding: 1rem; }
    .form-group { margin-bottom: 1rem; }
    label { display: block; margin-bottom: 4px; font-weight: 600; }
    input { width: 100%; padding: 10px; border: 2px solid #ccc; border-radius: 6px; font-size: 1rem; }
    input.invalid { border-color: #dc3545; }
    input.valid   { border-color: #28a745; }
    .error { color: #dc3545; font-size: 0.85rem; margin-top: 4px; }
    .success { color: #28a745; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; }
  </style>
</head>
<body>
  <h1>Create Account</h1>
  <form id="reg-form" novalidate>
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username">
      <p class="error" id="username-error"></p>
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email">
      <p class="error" id="email-error"></p>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password">
      <p class="error" id="password-error"></p>
    </div>
    <div class="form-group">
      <label for="confirm">Confirm Password</label>
      <input type="password" id="confirm" name="confirm">
      <p class="error" id="confirm-error"></p>
    </div>
    <button type="submit" style="padding:12px 24px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer;font-size:1rem">Create Account</button>
    <p class="success" id="success-msg" style="display:none">Account created successfully!</p>
  </form>
  <script>
    // implement validation here
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Form Validation</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: sans-serif; max-width: 400px; margin: 2rem auto; padding: 1rem; }
    .form-group { margin-bottom: 1rem; }
    label { display: block; margin-bottom: 4px; font-weight: 600; }
    input { width: 100%; padding: 10px; border: 2px solid #ccc; border-radius: 6px; font-size: 1rem; outline: none; transition: border-color 200ms; }
    input.invalid { border-color: #dc3545; }
    input.valid   { border-color: #28a745; }
    .error { color: #dc3545; font-size: 0.85rem; margin-top: 4px; min-height: 1.2em; }
    .success { color: #28a745; font-size: 1.1rem; font-weight: bold; margin-top: 1rem; }
  </style>
</head>
<body>
  <h1>Create Account</h1>
  <form id="reg-form" novalidate>
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username">
      <p class="error" id="username-error"></p>
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email">
      <p class="error" id="email-error"></p>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password">
      <p class="error" id="password-error"></p>
    </div>
    <div class="form-group">
      <label for="confirm">Confirm Password</label>
      <input type="password" id="confirm" name="confirm">
      <p class="error" id="confirm-error"></p>
    </div>
    <button type="submit" style="padding:12px 24px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer;font-size:1rem">Create Account</button>
    <p class="success" id="success-msg" style="display:none">Account created successfully!</p>
  </form>
  <script>
    function setError(id, msg) {
      const input = document.getElementById(id);
      const error = document.getElementById(id + "-error");
      input.classList.add("invalid");
      input.classList.remove("valid");
      error.textContent = msg;
      return false;
    }

    function setValid(id) {
      const input = document.getElementById(id);
      const error = document.getElementById(id + "-error");
      input.classList.add("valid");
      input.classList.remove("invalid");
      error.textContent = "";
      return true;
    }

    function validateUsername() {
      const val = document.getElementById("username").value.trim();
      if (!val) return setError("username", "Username is required.");
      if (val.length < 3 || val.length > 20) return setError("username", "Username must be 3-20 characters.");
      if (!/^[A-Za-z0-9]+$/.test(val)) return setError("username", "Letters and numbers only.");
      return setValid("username");
    }

    function validateEmail() {
      const val = document.getElementById("email").value.trim();
      if (!val) return setError("email", "Email is required.");
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) return setError("email", "Please enter a valid email.");
      return setValid("email");
    }

    function validatePassword() {
      const val = document.getElementById("password").value;
      if (!val) return setError("password", "Password is required.");
      if (val.length < 8) return setError("password", "Password must be at least 8 characters.");
      return setValid("password");
    }

    function validateConfirm() {
      const pass    = document.getElementById("password").value;
      const confirm = document.getElementById("confirm").value;
      if (!confirm) return setError("confirm", "Please confirm your password.");
      if (confirm !== pass) return setError("confirm", "Passwords do not match.");
      return setValid("confirm");
    }

    document.getElementById("username").addEventListener("blur", validateUsername);
    document.getElementById("email").addEventListener("blur", validateEmail);
    document.getElementById("password").addEventListener("blur", validatePassword);
    document.getElementById("confirm").addEventListener("blur", validateConfirm);

    document.getElementById("reg-form").addEventListener("submit", (e) => {
      e.preventDefault();
      const valid = [validateUsername(), validateEmail(), validatePassword(), validateConfirm()];
      if (valid.every(v => v === true)) {
        document.getElementById("success-msg").style.display = "block";
      }
    });
  </script>
</body>
</html>',
    'Validate on blur (when field loses focus) for real-time feedback without interrupting typing|novalidate on form disables browser validation popups so you can show custom errors|Array.every() returns true only if ALL items are truthy — perfect for checking all validations passed',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Callbacks',
    'Build a Timed Quiz',
    'Use setTimeout and setInterval callbacks to build a quiz with a countdown timer.',
    "1. Define an array of 3 quiz questions, each with a question, options array, and correct answer index\n2. Show questions one at a time — only show the next question after the user picks an answer\n3. When an answer is selected, show feedback: 'Correct!' or 'Wrong! The answer was X'\n4. After 3 seconds (setTimeout callback), automatically show the next question\n5. At the end, show the score out of 3",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Timed Quiz</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    .option { display: block; padding: 10px; margin: 6px 0; border: 2px solid #e0e0e0; border-radius: 6px; cursor: pointer; background: white; text-align: left; font-size: 1rem; width: 100%; }
    .option:hover { background: #f0f4ff; }
    #feedback { font-weight: bold; margin: 1rem 0; min-height: 1.5em; }
  </style>
</head>
<body>
  <h1>Quick Quiz</h1>
  <div id="question-container">
    <p id="question-text"></p>
    <div id="options-container"></div>
  </div>
  <p id="feedback"></p>
  <p id="progress"></p>
  <script>
    const questions = [
      { q: "What does HTML stand for?", options: ["Hyper Text Markup Language", "High Tech Modern Language", "Hyperlink Text Method Language"], correct: 0 },
      { q: "Which CSS property adds space inside an element?", options: ["margin", "padding", "border"], correct: 1 },
      { q: "What does === check in JavaScript?", options: ["Only value", "Value and type", "Only type"], correct: 1 }
    ];

    // Implement quiz logic here
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Timed Quiz</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    .option { display: block; padding: 10px; margin: 6px 0; border: 2px solid #e0e0e0; border-radius: 6px; cursor: pointer; background: white; text-align: left; font-size: 1rem; width: 100%; }
    .option:hover { background: #f0f4ff; }
    .option:disabled { cursor: default; opacity: 0.7; }
    #feedback { font-weight: bold; margin: 1rem 0; min-height: 1.5em; }
  </style>
</head>
<body>
  <h1>Quick Quiz</h1>
  <div id="question-container">
    <p id="question-text"></p>
    <div id="options-container"></div>
  </div>
  <p id="feedback"></p>
  <p id="progress"></p>
  <script>
    const questions = [
      { q: "What does HTML stand for?", options: ["Hyper Text Markup Language", "High Tech Modern Language", "Hyperlink Text Method Language"], correct: 0 },
      { q: "Which CSS property adds space inside an element?", options: ["margin", "padding", "border"], correct: 1 },
      { q: "What does === check in JavaScript?", options: ["Only value", "Value and type", "Only type"], correct: 1 }
    ];

    let currentQuestion = 0;
    let score = 0;

    function showQuestion(index) {
      if (index >= questions.length) {
        document.getElementById("question-container").innerHTML =
          `<h2>Quiz Complete! Score: ${score}/${questions.length}</h2>`;
        document.getElementById("feedback").textContent = "";
        document.getElementById("progress").textContent = "";
        return;
      }

      const q = questions[index];
      document.getElementById("question-text").textContent = q.q;
      document.getElementById("progress").textContent = `Question ${index + 1} of ${questions.length}`;
      document.getElementById("feedback").textContent = "";

      const container = document.getElementById("options-container");
      container.innerHTML = "";

      q.options.forEach((option, i) => {
        const btn = document.createElement("button");
        btn.className = "option";
        btn.textContent = option;
        btn.addEventListener("click", () => handleAnswer(i, q.correct, q.options[q.correct]));
        container.appendChild(btn);
      });
    }

    function handleAnswer(chosen, correct, correctText) {
      const isCorrect = chosen === correct;
      if (isCorrect) score++;

      const feedback = document.getElementById("feedback");
      feedback.textContent    = isCorrect ? "✅ Correct!" : `❌ Wrong! The answer was: ${correctText}`;
      feedback.style.color    = isCorrect ? "#28a745" : "#dc3545";

      // Disable all buttons
      document.querySelectorAll(".option").forEach(btn => btn.disabled = true);

      // Move to next question after 3 seconds
      setTimeout(() => {
        currentQuestion++;
        showQuestion(currentQuestion);
      }, 3000);
    }

    showQuestion(0);
  </script>
</body>
</html>',
    'setTimeout(callback, ms) calls the callback once after the delay in milliseconds|Disable buttons after an answer is chosen to prevent multiple submissions|Closures in event listeners can capture the correct answer index from the enclosing forEach scope',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Promises',
    'Build a Simulated Data Loader',
    'Practice creating and consuming Promises to simulate async data loading.',
    "1. Write a fetchUser(id) function that returns a Promise — it resolves after 1 second with a fake user object if id > 0, or rejects with an error if id <= 0\n2. Chain .then() to fetchUser to also 'load' the user's courses (another fake Promise)\n3. Use .catch() to handle errors and display them in the UI\n4. Use .finally() to always hide a loading indicator\n5. Add buttons to 'load' valid user (id=1) and trigger an error (id=-1)",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Promises</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    #loading { color: #666; font-style: italic; display: none; }
    #result  { margin-top: 1rem; padding: 1rem; background: #f0f4ff; border-radius: 8px; }
    #error   { color: #dc3545; margin-top: 1rem; }
    button   { padding: 10px 20px; margin-right: 10px; cursor: pointer; }
  </style>
</head>
<body>
  <h1>Promise Data Loader</h1>
  <button id="load-ok">Load User (id=1)</button>
  <button id="load-err">Trigger Error (id=-1)</button>
  <p id="loading">Loading...</p>
  <div id="result"></div>
  <p id="error"></p>
  <script>
    function fetchUser(id) {
      return new Promise((resolve, reject) => {
        // simulate async: resolve after 1 second, or reject if id <= 0
      });
    }

    function loadUserData(id) {
      // show loading, call fetchUser, chain .then .catch .finally
    }

    document.getElementById("load-ok").addEventListener("click", () => loadUserData(1));
    document.getElementById("load-err").addEventListener("click", () => loadUserData(-1));
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Promises</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    #loading { color: #666; font-style: italic; display: none; }
    #result  { margin-top: 1rem; padding: 1rem; background: #f0f4ff; border-radius: 8px; display: none; }
    #error   { color: #dc3545; margin-top: 1rem; }
    button   { padding: 10px 20px; margin-right: 10px; cursor: pointer; border-radius: 6px; border: none; background: #0066cc; color: white; }
  </style>
</head>
<body>
  <h1>Promise Data Loader</h1>
  <button id="load-ok">Load User (id=1)</button>
  <button id="load-err" style="background:#dc3545">Trigger Error (id=-1)</button>
  <p id="loading">Loading...</p>
  <div id="result"></div>
  <p id="error"></p>
  <script>
    function fetchUser(id) {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          if (id > 0) {
            resolve({ id, name: "Alex Kamau", email: "alex@example.com", role: "student" });
          } else {
            reject(new Error(`User with id ${id} not found`));
          }
        }, 1000);
      });
    }

    function fetchCourses(userId) {
      return new Promise(resolve => {
        setTimeout(() => {
          resolve(["HTML Basics", "CSS Fundamentals", "JavaScript Essentials"]);
        }, 500);
      });
    }

    function loadUserData(id) {
      const loading = document.getElementById("loading");
      const result  = document.getElementById("result");
      const error   = document.getElementById("error");

      loading.style.display = "block";
      result.style.display  = "none";
      error.textContent     = "";

      fetchUser(id)
        .then(user => fetchCourses(user.id).then(courses => ({ user, courses })))
        .then(({ user, courses }) => {
          result.style.display = "block";
          result.innerHTML = `
            <h3>${user.name}</h3>
            <p>${user.email} — ${user.role}</p>
            <p><strong>Enrolled courses:</strong> ${courses.join(", ")}</p>
          `;
        })
        .catch(err => {
          error.textContent = `Error: ${err.message}`;
        })
        .finally(() => {
          loading.style.display = "none";
        });
    }

    document.getElementById("load-ok").addEventListener("click",  () => loadUserData(1));
    document.getElementById("load-err").addEventListener("click", () => loadUserData(-1));
  </script>
</body>
</html>',
    'Promises represent a future value — resolve() fulfils, reject() fails the promise|Chain .then() to handle success, .catch() for errors, .finally() for cleanup|Return a Promise from .then() to chain async operations sequentially',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Async/Await',
    'Fetch and Display JSON Data',
    'Use async/await to load data from a public API and render it to the page.',
    "1. Write an async function loadPosts() that fetches from https://jsonplaceholder.typicode.com/posts?_limit=5\n2. Check response.ok and throw an error if the response failed\n3. Parse the JSON response\n4. Display each post as a card with title and body\n5. Show a loading message while fetching and an error message if it fails\n6. Use a try/catch/finally block",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Async/Await Fetch</title>
  <style>
    body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; padding: 1rem; }
    .post-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
    .post-card h3 { margin-bottom: 0.5rem; text-transform: capitalize; }
    .post-card p  { color: #666; font-size: 0.9rem; }
    #loading { color: #666; font-style: italic; }
    #error   { color: #dc3545; }
  </style>
</head>
<body>
  <h1>Latest Posts</h1>
  <p id="loading">Loading posts...</p>
  <p id="error" style="display:none"></p>
  <div id="posts-container"></div>
  <script>
    async function loadPosts() {
      // implement using try/catch/finally
    }
    loadPosts();
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Async/Await Fetch</title>
  <style>
    body { font-family: sans-serif; max-width: 600px; margin: 2rem auto; padding: 1rem; }
    .post-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background: white; }
    .post-card h3 { margin-bottom: 0.5rem; text-transform: capitalize; color: #1a1a1a; }
    .post-card p  { color: #666; font-size: 0.9rem; line-height: 1.5; }
    #loading { color: #666; font-style: italic; }
    #error   { color: #dc3545; }
  </style>
</head>
<body>
  <h1>Latest Posts</h1>
  <p id="loading">Loading posts...</p>
  <p id="error" style="display:none"></p>
  <div id="posts-container"></div>
  <script>
    async function loadPosts() {
      const loading   = document.getElementById("loading");
      const errorEl   = document.getElementById("error");
      const container = document.getElementById("posts-container");

      try {
        const response = await fetch("https://jsonplaceholder.typicode.com/posts?_limit=5");

        if (!response.ok) {
          throw new Error(`Server error: ${response.status} ${response.statusText}`);
        }

        const posts = await response.json();

        container.innerHTML = "";
        posts.forEach(post => {
          const card    = document.createElement("div");
          card.className = "post-card";

          const title = document.createElement("h3");
          const body  = document.createElement("p");

          title.textContent = post.title;
          body.textContent  = post.body;

          card.append(title, body);
          container.appendChild(card);
        });

      } catch (error) {
        errorEl.style.display = "block";
        errorEl.textContent   = `Failed to load posts: ${error.message}`;
        console.error(error);

      } finally {
        loading.style.display = "none";
      }
    }

    loadPosts();
  </script>
</body>
</html>',
    'async functions always return a Promise; await pauses until the Promise resolves|fetch() does NOT reject on HTTP errors — check response.ok and throw manually|try/catch/finally: try runs the code, catch handles errors, finally always runs',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Fetch API',
    'Build a Search Interface with Live API Results',
    'Use fetch to build a live search that queries an API as the user types.',
    "1. Create a search input that calls an API with the user's query after a 300ms debounce\n2. Fetch from https://jsonplaceholder.typicode.com/users and filter client-side by name/email matching the query\n3. Display matching results as a list\n4. Show 'No results found' when there are no matches\n5. Show a loading indicator while the request is in progress\n6. Handle fetch errors gracefully",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Live Search</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    input { width: 100%; padding: 12px; font-size: 1rem; border: 2px solid #ccc; border-radius: 8px; }
    input:focus { outline: none; border-color: #0066cc; }
    #results { margin-top: 1rem; }
    .result-item { padding: 10px; border-bottom: 1px solid #eee; }
    .result-item strong { display: block; }
    .result-item span   { color: #666; font-size: 0.9rem; }
  </style>
</head>
<body>
  <h1>User Search</h1>
  <input type="search" id="search" placeholder="Search users by name or email...">
  <div id="loading" style="display:none;color:#666;font-style:italic;margin-top:8px">Searching...</div>
  <div id="results"></div>
  <script>
    // Implement debounced search with fetch
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Live Search</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    input { width: 100%; padding: 12px; font-size: 1rem; border: 2px solid #ccc; border-radius: 8px; box-sizing: border-box; }
    input:focus { outline: none; border-color: #0066cc; }
    #results { margin-top: 1rem; }
    .result-item { padding: 10px; border-bottom: 1px solid #eee; }
    .result-item strong { display: block; color: #1a1a1a; }
    .result-item span   { color: #666; font-size: 0.9rem; }
    .no-results { color: #666; font-style: italic; padding: 10px 0; }
  </style>
</head>
<body>
  <h1>User Search</h1>
  <input type="search" id="search" placeholder="Search users by name or email...">
  <div id="loading" style="display:none;color:#666;font-style:italic;margin-top:8px">Searching...</div>
  <div id="results"></div>
  <script>
    let allUsers  = [];
    let debounce  = null;
    const loading = document.getElementById("loading");
    const results = document.getElementById("results");

    async function loadUsers() {
      try {
        const res   = await fetch("https://jsonplaceholder.typicode.com/users");
        if (!res.ok) throw new Error("Failed to load users");
        allUsers = await res.json();
      } catch (err) {
        results.innerHTML = `<p style="color:#dc3545">Failed to load users: ${err.message}</p>`;
      }
    }

    function displayResults(users) {
      if (users.length === 0) {
        results.innerHTML = `<p class="no-results">No users found.</p>`;
        return;
      }

      results.innerHTML = users.map(u => `
        <div class="result-item">
          <strong>${u.name}</strong>
          <span>${u.email} — ${u.company.name}</span>
        </div>
      `).join("");
    }

    document.getElementById("search").addEventListener("input", (e) => {
      clearTimeout(debounce);
      const query = e.target.value.trim().toLowerCase();

      if (!query) { results.innerHTML = ""; return; }

      loading.style.display = "block";

      debounce = setTimeout(() => {
        const filtered = allUsers.filter(u =>
          u.name.toLowerCase().includes(query) ||
          u.email.toLowerCase().includes(query)
        );
        displayResults(filtered);
        loading.style.display = "none";
      }, 300);
    });

    loadUsers();
  </script>
</body>
</html>',
    'Debouncing delays execution until the user stops typing — clearTimeout + setTimeout|Load data once and filter client-side for a fast search experience|Always check response.ok after fetch — a 404 does not throw automatically',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Error Handling',
    'Build a Robust Data Loader with Error Recovery',
    'Implement comprehensive error handling for a data-fetching function.',
    "1. Create a custom ApiError class extending Error that stores a statusCode\n2. Write a fetchWithRetry(url, maxRetries) function that retries failed requests up to maxRetries times\n3. After max retries, display an error message with a 'Try Again' button\n4. Log all errors to the console with the error type\n5. Add a global unhandledrejection listener that shows a fallback error message",
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Error Handling</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    .error-box { background: #fff5f5; border: 1px solid #dc3545; border-radius: 8px; padding: 1rem; color: #dc3545; }
    button { padding: 10px 20px; cursor: pointer; border-radius: 6px; border: none; background: #0066cc; color: white; margin-top: 1rem; }
    #status { margin-bottom: 1rem; color: #666; font-style: italic; }
  </style>
</head>
<body>
  <h1>Error Handling Demo</h1>
  <p id="status">Click a button to load data.</p>
  <div id="result"></div>
  <div>
    <button onclick="loadData(\'https://jsonplaceholder.typicode.com/todos/1\')">Load Valid URL</button>
    <button onclick="loadData(\'https://jsonplaceholder.typicode.com/invalid-endpoint\')">Load 404 URL</button>
    <button onclick="loadData(\'https://this-url-does-not-exist.example.com\')">Cause Network Error</button>
  </div>
  <script>
    class ApiError extends Error {
      // implement
    }

    async function fetchWithRetry(url, maxRetries = 2) {
      // implement with retry logic
    }

    async function loadData(url) {
      // use fetchWithRetry, handle errors
    }

    window.addEventListener("unhandledrejection", (event) => {
      // global fallback
    });
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Error Handling</title>
  <style>
    body { font-family: sans-serif; max-width: 500px; margin: 2rem auto; padding: 1rem; }
    .error-box { background: #fff5f5; border: 1px solid #dc3545; border-radius: 8px; padding: 1rem; color: #dc3545; margin-top: 1rem; }
    .success-box { background: #f0fff4; border: 1px solid #28a745; border-radius: 8px; padding: 1rem; margin-top: 1rem; }
    button { padding: 10px 20px; cursor: pointer; border-radius: 6px; border: none; background: #0066cc; color: white; margin: 4px; }
    #status { color: #666; font-style: italic; margin-bottom: 1rem; }
  </style>
</head>
<body>
  <h1>Error Handling Demo</h1>
  <p id="status">Click a button to load data.</p>
  <div id="result"></div>
  <div>
    <button onclick="loadData(\'https://jsonplaceholder.typicode.com/todos/1\')">Load Valid URL</button>
    <button onclick="loadData(\'https://jsonplaceholder.typicode.com/todos/99999\')">Load 404</button>
    <button onclick="loadData(\'https://this-url-does-not-exist.example.com\')">Network Error</button>
  </div>
  <script>
    class ApiError extends Error {
      constructor(message, statusCode) {
        super(message);
        this.name       = "ApiError";
        this.statusCode = statusCode;
      }
    }

    async function fetchWithRetry(url, maxRetries = 2, attempt = 1) {
      try {
        document.getElementById("status").textContent = `Attempt ${attempt}...`;
        const response = await fetch(url);
        if (!response.ok) {
          throw new ApiError(`HTTP ${response.status}: ${response.statusText}`, response.status);
        }
        return await response.json();
      } catch (err) {
        console.error(`[${err.name}] Attempt ${attempt} failed:`, err.message);
        if (attempt < maxRetries && !(err instanceof ApiError)) {
          await new Promise(r => setTimeout(r, 500 * attempt));
          return fetchWithRetry(url, maxRetries, attempt + 1);
        }
        throw err;
      }
    }

    async function loadData(url) {
      const resultEl = document.getElementById("result");
      const statusEl = document.getElementById("status");

      resultEl.innerHTML = "";

      try {
        const data = await fetchWithRetry(url, 2);
        statusEl.textContent = "Loaded successfully!";
        resultEl.innerHTML = `<div class="success-box"><pre>${JSON.stringify(data, null, 2)}</pre></div>`;
      } catch (err) {
        statusEl.textContent = "Request failed.";
        const msg = err instanceof ApiError
          ? `API Error (${err.statusCode}): ${err.message}`
          : `Network Error: ${err.message}`;
        resultEl.innerHTML = `
          <div class="error-box">
            <strong>${err.name}</strong>: ${err.message}
            <br><button onclick="loadData(\'${url}\')">Try Again</button>
          </div>`;
        console.error("[Error Handler]", err);
      }
    }

    window.addEventListener("unhandledrejection", (event) => {
      console.error("Unhandled rejection caught:", event.reason);
      document.getElementById("result").innerHTML +=
        `<div class="error-box">Unhandled error: ${event.reason?.message || event.reason}</div>`;
      event.preventDefault();
    });
  </script>
</body>
</html>',
    'Extend Error with a custom class to carry additional data (statusCode, field)|Retry logic checks attempt count and only retries on network errors, not API errors|window.addEventListener("unhandledrejection") is a global safety net for missed Promise errors',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Destructuring',
    'Destructure API Response Data',
    'Practice object and array destructuring to extract and display API data.',
    "1. Define a mock API response object with nested user, courses array, and settings\n2. Use object destructuring to extract name, email from the user object (with alias for name)\n3. Use array destructuring to get the first and second courses from the courses array, and collect the rest\n4. Use destructuring with defaults for a missing 'theme' property (default 'light')\n5. Write a function displayUser that uses destructured parameters and shows the data",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Destructuring</title></head>
<body>
  <h1>Destructuring Practice</h1>
  <div id="output" style="font-family:sans-serif;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem"></div>
  <script>
    const apiResponse = {
      status: "success",
      user: {
        id: 42,
        name: "Alex Kamau",
        email: "alex@example.com",
        location: { city: "Nairobi", country: "Kenya" }
      },
      courses: ["HTML Basics", "CSS Fundamentals", "JavaScript", "Node.js", "React"],
      settings: { notifications: true }
      // note: no "theme" property — test default
    };

    // 1. Object destructuring
    // 2. Array destructuring
    // 3. Defaults
    // 4. Function with destructured params
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Destructuring</title></head>
<body>
  <h1>Destructuring Practice</h1>
  <div id="output" style="font-family:sans-serif;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem"></div>
  <script>
    const apiResponse = {
      status: "success",
      user: {
        id: 42,
        name: "Alex Kamau",
        email: "alex@example.com",
        location: { city: "Nairobi", country: "Kenya" }
      },
      courses: ["HTML Basics", "CSS Fundamentals", "JavaScript", "Node.js", "React"],
      settings: { notifications: true }
    };

    const out = document.getElementById("output");

    // 1. Object destructuring with alias and nested
    const {
      user: { name: userName, email, location: { city } },
      courses,
      settings: { notifications, theme = "light" }  // default for missing key
    } = apiResponse;

    out.innerHTML += `<p><strong>Name:</strong> ${userName}</p>`;
    out.innerHTML += `<p><strong>Email:</strong> ${email}</p>`;
    out.innerHTML += `<p><strong>City:</strong> ${city}</p>`;
    out.innerHTML += `<p><strong>Theme (default):</strong> ${theme}</p>`;

    // 2. Array destructuring
    const [firstCourse, secondCourse, ...otherCourses] = courses;
    out.innerHTML += `<p><strong>First course:</strong> ${firstCourse}</p>`;
    out.innerHTML += `<p><strong>Second course:</strong> ${secondCourse}</p>`;
    out.innerHTML += `<p><strong>Other courses:</strong> ${otherCourses.join(", ")}</p>`;

    // 3. Function with destructured parameters
    function displayUser({ name, email, location: { city, country } }) {
      return `${name} (${email}) lives in ${city}, ${country}`;
    }

    out.innerHTML += `<p><strong>displayUser:</strong> ${displayUser(apiResponse.user)}</p>`;
  </script>
</body>
</html>',
    'Alias in destructuring: { name: userName } extracts name and stores it as userName|Default values: { theme = "light" } uses "light" if theme is undefined|Rest in array destructuring collects remaining elements: [first, ...rest]',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Spread and Rest',
    'Merge and Update Objects with Spread',
    'Use spread and rest operators to work with arrays and objects immutably.',
    "1. Start with a userProfile object and a preferences object\n2. Use spread to merge them into a combined object\n3. Create an updated version of the user that changes only the email — without mutating the original\n4. Use rest to create a version of the user without the 'password' field\n5. Use spread with arrays: combine two course arrays, then add a new course at position 2",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Spread and Rest</title></head>
<body>
  <h1>Spread and Rest</h1>
  <div id="output" style="font-family:monospace;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem;white-space:pre-wrap"></div>
  <script>
    const out = document.getElementById("output");
    const log = (label, value) => out.textContent += label + ": " + JSON.stringify(value, null, 2) + "\n\n";

    const userProfile = { id: 1, name: "Alex", email: "alex@old.com", password: "hashed123" };
    const preferences = { theme: "dark", language: "en", notifications: true };

    // 1. Merge objects with spread

    // 2. Update email without mutation

    // 3. Remove password with rest destructuring

    // 4. Spread with arrays
    const frontendCourses = ["HTML", "CSS"];
    const backendCourses  = ["Node.js", "PHP"];
    // combine and insert "JavaScript" at position 2
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Spread and Rest</title></head>
<body>
  <h1>Spread and Rest</h1>
  <div id="output" style="font-family:monospace;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem;white-space:pre-wrap"></div>
  <script>
    const out = document.getElementById("output");
    const log = (label, value) => out.textContent += label + ": " + JSON.stringify(value) + "\n\n";

    const userProfile = { id: 1, name: "Alex", email: "alex@old.com", password: "hashed123" };
    const preferences = { theme: "dark", language: "en", notifications: true };

    // 1. Merge objects
    const merged = { ...userProfile, ...preferences };
    log("Merged", merged);

    // 2. Update email (immutable) — original unchanged
    const updatedUser = { ...userProfile, email: "alex@new.com" };
    log("Updated user", updatedUser);
    log("Original user (unchanged)", userProfile);

    // 3. Remove password with rest destructuring
    const { password, ...safeUser } = userProfile;
    log("User without password", safeUser);

    // 4. Array spread: combine and insert
    const frontendCourses = ["HTML", "CSS"];
    const backendCourses  = ["Node.js", "PHP"];

    const allCourses = [...frontendCourses, "JavaScript", ...backendCourses];
    log("All courses", allCourses);

    // Spread to pass array as args
    const numbers = [5, 3, 9, 1, 7];
    log("Max value", Math.max(...numbers));
  </script>
</body>
</html>',
    'Spread creates a shallow copy — nested objects still share references|Later properties in spread override earlier ones: { ...defaults, ...overrides }|Rest in destructuring collects everything not explicitly named: { password, ...rest }',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Modules',
    'Organise Code into Modules',
    'Refactor inline code into separate modules with named and default exports.',
    "1. Create an inline module using a script type='module'\n2. Define (inline) a math utilities module with named exports: add, subtract, multiply, formatCurrency\n3. Define a User class with a default export\n4. Import and use both in the main script\n5. Use a dynamic import() to load a 'heavy' module only when a button is clicked",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Modules</title></head>
<body>
  <h1>JavaScript Modules</h1>
  <div id="output" style="font-family:sans-serif;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem"></div>
  <button id="load-heavy">Load Heavy Module (Dynamic Import)</button>

  <script type="module">
    // In a real project these would be separate files.
    // Here we demonstrate the syntax inline using blob URLs.

    // Define and use named exports
    const add            = (a, b) => a + b;
    const subtract       = (a, b) => a - b;
    const multiply       = (a, b) => a * b;
    const formatCurrency = (n, c = "KES") => `${c} ${n.toFixed(2)}`;

    // Define a class (default export equivalent)
    class User {
      constructor(name, email) {
        this.name  = name;
        this.email = email;
      }
      greet() { return `Hello, I am ${this.name}`; }
    }

    // Use them
    const out = document.getElementById("output");
    // 1. Show arithmetic results
    // 2. Create a User and show greeting
    // 3. Show formatted currency

    // Dynamic import simulation
    document.getElementById("load-heavy").addEventListener("click", async () => {
      // Simulate dynamic import with a short delay
      await new Promise(r => setTimeout(r, 500));
      out.innerHTML += `<p>Heavy module loaded on demand! (simulated)</p>`;
    });
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Modules</title></head>
<body>
  <h1>JavaScript Modules</h1>
  <div id="output" style="font-family:sans-serif;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem"></div>
  <button id="load-heavy" style="padding:10px 20px;cursor:pointer;border-radius:6px;border:none;background:#0066cc;color:white">Load Heavy Module (Dynamic Import)</button>

  <script type="module">
    // Normally these would be in separate files:
    // import { add, formatCurrency } from "./math.js";
    // import User from "./User.js";

    const add            = (a, b) => a + b;
    const subtract       = (a, b) => a - b;
    const multiply       = (a, b) => a * b;
    const formatCurrency = (n, c = "KES") => `${c} ${n.toFixed(2)}`;

    class User {
      constructor(name, email) {
        this.name  = name;
        this.email = email;
      }
      greet() { return `Hello, I am ${this.name} (${this.email})`; }
      toString() { return `User: ${this.name}`; }
    }

    const out = document.getElementById("output");

    // 1. Arithmetic
    out.innerHTML += `<p>add(10, 5) = ${add(10, 5)}</p>`;
    out.innerHTML += `<p>subtract(10, 5) = ${subtract(10, 5)}</p>`;
    out.innerHTML += `<p>multiply(10, 5) = ${multiply(10, 5)}</p>`;

    // 2. User class
    const alex = new User("Alex Kamau", "alex@example.com");
    out.innerHTML += `<p>${alex.greet()}</p>`;

    // 3. Currency
    out.innerHTML += `<p>formatCurrency(1500) = ${formatCurrency(1500)}</p>`;
    out.innerHTML += `<p>formatCurrency(99.9, "USD") = ${formatCurrency(99.9, "USD")}</p>`;

    // Dynamic import simulation
    document.getElementById("load-heavy").addEventListener("click", async () => {
      out.innerHTML += `<p style="color:#666;font-style:italic">Loading heavy module...</p>`;
      await new Promise(r => setTimeout(r, 800));
      // In real code: const { heavyFunction } = await import("./heavy-module.js");
      out.innerHTML += `<p style="color:#28a745">Heavy module loaded on demand!</p>`;
    });
  </script>
</body>
</html>',
    'script type="module" is required for ES6 import/export syntax in the browser|Named exports use export const/function/class; default exports use export default|Dynamic import() returns a Promise — use with async/await to load modules on demand',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Classes',
    'Build a BankAccount Class',
    'Create a BankAccount class with private fields, methods, and inheritance.',
    "1. Create a BankAccount class with private #balance and #transactionHistory\n2. Add deposit(amount) and withdraw(amount) methods — withdraw should fail if insufficient funds\n3. Add a getBalance() getter and getHistory() method\n4. Create a SavingsAccount subclass that extends BankAccount and adds: an interestRate property and addInterest() method\n5. Test both classes and display the results",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Classes</title></head>
<body>
  <h1>Bank Account</h1>
  <div id="output" style="font-family:monospace;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem;white-space:pre-wrap"></div>
  <script>
    class BankAccount {
      // private fields
      #balance = 0;
      #transactionHistory = [];

      constructor(owner, initialBalance = 0) {
        // implement
      }

      deposit(amount) {
        // implement
      }

      withdraw(amount) {
        // implement
      }

      get balance() {
        // implement
      }

      getHistory() {
        // implement
      }
    }

    class SavingsAccount extends BankAccount {
      // implement with interestRate and addInterest()
    }

    // Test code here
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Classes</title></head>
<body>
  <h1>Bank Account</h1>
  <div id="output" style="font-family:monospace;padding:1rem;background:#f5f5f5;border-radius:8px;margin:1rem;white-space:pre-wrap"></div>
  <script>
    class BankAccount {
      #balance = 0;
      #transactionHistory = [];

      constructor(owner, initialBalance = 0) {
        this.owner = owner;
        if (initialBalance > 0) {
          this.#balance = initialBalance;
          this.#transactionHistory.push({ type: "open", amount: initialBalance, balance: initialBalance });
        }
      }

      deposit(amount) {
        if (amount <= 0) throw new RangeError("Deposit amount must be positive");
        this.#balance += amount;
        this.#transactionHistory.push({ type: "deposit", amount, balance: this.#balance });
        return this;
      }

      withdraw(amount) {
        if (amount <= 0) throw new RangeError("Withdrawal amount must be positive");
        if (amount > this.#balance) throw new Error("Insufficient funds");
        this.#balance -= amount;
        this.#transactionHistory.push({ type: "withdrawal", amount, balance: this.#balance });
        return this;
      }

      get balance() {
        return this.#balance;
      }

      getHistory() {
        return [...this.#transactionHistory];
      }
    }

    class SavingsAccount extends BankAccount {
      constructor(owner, initialBalance, interestRate) {
        super(owner, initialBalance);
        this.interestRate = interestRate;
      }

      addInterest() {
        const interest = this.balance * this.interestRate;
        this.deposit(interest);
        return interest;
      }
    }

    const out = document.getElementById("output");

    const acc = new BankAccount("Alex", 1000);
    acc.deposit(500).deposit(250).withdraw(200);

    out.textContent += `=== BankAccount ===\n`;
    out.textContent += `Owner: ${acc.owner}\n`;
    out.textContent += `Balance: KES ${acc.balance}\n`;
    out.textContent += `History:\n${acc.getHistory().map(t => `  [${t.type}] ${t.amount} → ${t.balance}`).join("\n")}\n\n`;

    const savings = new SavingsAccount("Sam", 5000, 0.05);
    const interest = savings.addInterest();

    out.textContent += `=== SavingsAccount ===\n`;
    out.textContent += `Owner: ${savings.owner}\n`;
    out.textContent += `Interest earned: KES ${interest}\n`;
    out.textContent += `New balance: KES ${savings.balance}\n`;
  </script>
</body>
</html>',
    'Private fields (#name) are only accessible inside the class — not from outside or subclasses|Call super(args) in a subclass constructor before using this|Returning this from methods enables method chaining: acc.deposit(100).withdraw(50)',
    'javascript', 'easy', 10, $exercisesInserted);

addExercise($pdo, 'Best Practices',
    'Refactor Bad Code with Best Practices',
    'Identify and fix bad JavaScript code by applying naming, error handling, and security best practices.',
    "1. The starter code has intentional bad practices — refactor it\n2. Replace var with const/let, fix misleading variable names\n3. Add input validation with a guard clause\n4. Use textContent instead of innerHTML for user input\n5. Add try/catch error handling\n6. Replace alert() with inline feedback",
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Refactor Me</title></head>
<body>
  <h1>User Lookup</h1>
  <input id="i" type="text" placeholder="Enter username">
  <button onclick="x()">Search</button>
  <div id="d"></div>

  <script>
    // BAD CODE — refactor this:
    var data = [
      { n: "alex", e: "alex@example.com" },
      { n: "sam", e: "sam@example.com" },
      { n: "jordan", e: "jordan@example.com" }
    ];

    function x() {
      var q = document.getElementById("i").value;
      var r = data.find(u => u.n === q);
      if (r) {
        document.getElementById("d").innerHTML = "<p>Found: " + q + " — " + r.e + "</p>";
      } else {
        alert("User not found!");
      }
    }
  </script>
</body>
</html>',
    '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>User Lookup</title></head>
<body>
  <h1>User Lookup</h1>
  <div style="display:flex;gap:0.5rem;margin-bottom:1rem">
    <input id="search-input" type="text" placeholder="Enter username" style="padding:10px;border:2px solid #ccc;border-radius:6px;font-size:1rem">
    <button onclick="searchUser()" style="padding:10px 20px;background:#0066cc;color:white;border:none;border-radius:6px;cursor:pointer">Search</button>
  </div>
  <div id="result-container"></div>
  <p id="error-message" style="color:#dc3545;display:none"></p>

  <script>
    // GOOD CODE — after refactoring:
    const users = [
      { username: "alex",   email: "alex@example.com" },
      { username: "sam",    email: "sam@example.com" },
      { username: "jordan", email: "jordan@example.com" }
    ];

    function findUser(query) {
      if (!query || typeof query !== "string") {
        throw new TypeError("Search query must be a non-empty string");
      }

      const normalised = query.trim().toLowerCase();
      if (!normalised) throw new Error("Search query cannot be empty");

      return users.find(user => user.username === normalised) || null;
    }

    function displayResult(user, query) {
      const container = document.getElementById("result-container");
      const errorEl   = document.getElementById("error-message");

      container.innerHTML = "";
      errorEl.style.display = "none";

      if (!user) {
        errorEl.textContent   = `No user found with username "${query}"`;
        errorEl.style.display = "block";
        return;
      }

      // Safe: textContent prevents XSS
      const p = document.createElement("p");
      p.style.cssText = "padding:10px;background:#f0f4ff;border-radius:6px";
      p.textContent   = `Found: ${user.username} — ${user.email}`;
      container.appendChild(p);
    }

    function searchUser() {
      const searchInput = document.getElementById("search-input");

      try {
        const query = searchInput.value;
        const user  = findUser(query);
        displayResult(user, query.trim());
      } catch (error) {
        const errorEl         = document.getElementById("error-message");
        errorEl.textContent   = error.message;
        errorEl.style.display = "block";
        console.error("[searchUser]", error);
      }
    }
  </script>
</body>
</html>',
    'Use const for unchanging values, let for values that will change|Use descriptive names: "users" not "data", "searchUser" not "x"|Use textContent for user-provided values to prevent XSS injection attacks',
    'javascript', 'easy', 10, $exercisesInserted);

// =====================================================================
// Final output
// =====================================================================

$pdo->exec('PRAGMA foreign_keys = ON');

echo "<h2 style='color:green'>&#10003; Migration Complete</h2>";
echo "<p><strong>$lessonsUpdated</strong> lessons updated</p>";
echo "<p><strong>$exercisesInserted</strong> exercises inserted</p>";
echo "<p style='color:red'><strong>&#9888; Delete this file from the server now!</strong></p>";
