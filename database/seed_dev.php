<?php
/**
 * HackathonAfrica LMS — Comprehensive Beginner-Friendly Seeder
 */
require_once __DIR__ . '/../config/database.php';
$db = db();

echo "Seeding HackathonAfrica LMS...\n";

foreach (['quiz_attempts','user_lesson_progress','quiz_options','quiz_questions',
          'quizzes','lessons','modules','user_enrollments','courses','users'] as $t) {
    $db->exec("DELETE FROM $t");
    $db->exec("DELETE FROM sqlite_sequence WHERE name='$t'");
}

$ins  = fn(string $sql, array $p=[]) => $db->prepare($sql)->execute($p);
$quiz = fn(int $id,int $mid,string $t) =>
    $ins('INSERT INTO quizzes(id,module_id,title,pass_mark)VALUES(?,?,?,?)',[$id,$mid,$t,70]);
$q    = fn(int $id,int $qid,string $t,int $o) =>
    $ins('INSERT INTO quiz_questions(id,quiz_id,question_text,order_index)VALUES(?,?,?,?)',[$id,$qid,$t,$o]);
$o    = fn(int $qId,string $t,bool $c) =>
    $ins('INSERT INTO quiz_options(question_id,option_text,is_correct)VALUES(?,?,?)',[$qId,$t,$c?1:0]);

$ins('INSERT INTO users(id,name,email,password,role)VALUES(?,?,?,?,?)',
    [1,'Admin','admin@hackathon.africa',password_hash('password',PASSWORD_BCRYPT),'admin']);
echo "  [✓] Admin\n";

// ══════════════════════════════════════════════════════════
// COURSE 1: HTML
// ══════════════════════════════════════════════════════════
$ins('INSERT INTO courses(id,title,description,status,order_index)VALUES(?,?,?,?,?)',
    [1,'HTML — Building the Structure of the Web',
     'Start from zero and learn how every webpage is built. You will understand HTML document structure, semantic elements, forms, and how browsers render your code.',
     'published',1]);

$ins('INSERT INTO modules(id,course_id,title,description,order_index)VALUES(?,?,?,?,?)',
    [1,1,'Document Structure & Core Elements',
     'Learn how a web page is put together from scratch — from the very first line of code to working with text, links, and images.',1]);

// ── HTML M1 L1 ───────────────────────────────────────────
$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[1,1,'How the Web Works & What HTML Does',<<<'HTML'
<h2>How the Web Works &amp; What HTML Does</h2>

<p>Before writing a single line of code, it helps to understand the big picture. When you type a web address into your browser and press Enter, a whole chain of events happens in milliseconds — and at the heart of it all is HTML.</p>

<h3>The Journey of a Web Page</h3>
<p>Think of the web like a postal system. You (the browser) send a letter (a <strong>request</strong>) to a specific address (the <strong>server</strong>). The server reads your letter, finds the right information, and sends back a reply (the <strong>response</strong>) — which is usually an HTML file.</p>

<ol>
  <li><strong>You type a URL</strong> like <code>https://hackathon.africa</code> into your browser.</li>
  <li>Your browser asks a <strong>DNS server</strong> "what is the IP address for this domain?" — like looking up a phone number in a directory.</li>
  <li>Your browser connects to that IP address (the web server) and sends an <strong>HTTP request</strong>: "Please give me the homepage."</li>
  <li>The server responds with an <strong>HTML file</strong> and a status code like <code>200 OK</code> (meaning "here it is!").</li>
  <li>Your browser reads the HTML from top to bottom and builds the page — this is called <strong>parsing</strong>.</li>
  <li>If the HTML mentions CSS or image files, the browser fetches those too.</li>
  <li>Finally, the browser <strong>paints</strong> everything you see on screen.</li>
</ol>

<h3>What Is HTML?</h3>
<p><strong>HTML</strong> stands for <em>HyperText Markup Language</em>. Let's break that down:</p>
<ul>
  <li><strong>HyperText</strong> — text that contains links to other pages (the "hyper" means it goes beyond ordinary text).</li>
  <li><strong>Markup</strong> — you wrap content in special tags to give it meaning, like putting a label on a box.</li>
  <li><strong>Language</strong> — it has a defined set of rules and vocabulary (though it is NOT a programming language — it has no logic or calculations).</li>
</ul>

<h3>HTML, CSS, and JavaScript — The Three Pillars</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Language</th><th>Role</th><th>Analogy</th></tr></thead>
  <tbody>
    <tr><td><strong>HTML</strong></td><td>Structure and content</td><td>The walls, rooms, and doors of a house</td></tr>
    <tr><td><strong>CSS</strong></td><td>Visual style and layout</td><td>The paint, furniture, and decoration</td></tr>
    <tr><td><strong>JavaScript</strong></td><td>Behaviour and interactivity</td><td>The electricity — lights, appliances, everything that moves</td></tr>
  </tbody>
</table>
<p>In this course, you focus on HTML — the foundation. Without good HTML, CSS and JavaScript have nothing solid to work with.</p>

<h3>A First Look at HTML Tags</h3>
<p>HTML uses <strong>tags</strong> to mark up content. Most tags come in pairs: an opening tag and a closing tag. The content goes in between.</p>
<pre><code>&lt;!-- This is a paragraph tag --&gt;
&lt;p&gt;This is a paragraph of text.&lt;/p&gt;

&lt;!-- This is a heading tag --&gt;
&lt;h1&gt;Welcome to HackathonAfrica&lt;/h1&gt;

&lt;!-- This tag stands alone (self-closing) --&gt;
&lt;img src="logo.png" alt="Logo"&gt;</code></pre>

<p>The opening tag is <code>&lt;p&gt;</code>, the closing tag is <code>&lt;/p&gt;</code> (note the forward slash). The browser reads these tags and knows how to display the content inside them.</p>

<h3>Common Mistakes Beginners Make</h3>
<ul>
  <li><strong>Forgetting the closing tag</strong> — <code>&lt;p&gt;Hello</code> without <code>&lt;/p&gt;</code>. Browsers will try to fix this, but it can cause unexpected layout problems.</li>
  <li><strong>Confusing HTML with a programming language</strong> — HTML cannot do calculations, make decisions, or store data. That is JavaScript's job.</li>
  <li><strong>Thinking HTML controls appearance</strong> — HTML defines what content <em>is</em>. CSS controls how it <em>looks</em>.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>The browser sends a request to a server and receives HTML in return.</li>
  <li>HTML gives structure and meaning to content using tags.</li>
  <li>HTML works together with CSS (style) and JavaScript (behaviour).</li>
  <li>HTML is not a programming language — it is a markup language.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Open any website, right-click anywhere on the page, and choose "View Page Source". You will see the raw HTML the browser received. Scroll through it — you will recognise some of the tags already!</div>
HTML,1]);

// ── HTML M1 L2 ───────────────────────────────────────────
$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[2,1,'Document Structure: DOCTYPE, Head & Body',<<<'HTML'
<h2>Document Structure: DOCTYPE, Head &amp; Body</h2>

<p>Every valid HTML page follows a specific structure — like a form that must be filled out in the right order. In this lesson you will learn what every HTML file must contain and why each part exists.</p>

<h3>The Skeleton of Every Web Page</h3>
<p>Here is the minimum structure every HTML file must have. Read through it carefully — we will explain every single line below.</p>

<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
  &lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;My First Web Page&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;h1&gt;Hello, World!&lt;/h1&gt;
    &lt;p&gt;This is my first web page.&lt;/p&gt;
  &lt;/body&gt;
&lt;/html&gt;</code></pre>

<h3>Line-by-Line Explanation</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Line</th><th>What It Does</th><th>Why It Matters</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;!DOCTYPE html&gt;</code></td><td>Declares this is an HTML5 document</td><td>Without this, some browsers enter "quirks mode" and display things differently — always include it as the very first line.</td></tr>
    <tr><td><code>&lt;html lang="en"&gt;</code></td><td>The root element wrapping everything</td><td>The <code>lang</code> attribute tells screen readers and search engines what language the page is in.</td></tr>
    <tr><td><code>&lt;head&gt;</code></td><td>Contains page information (metadata)</td><td>Content here is NOT shown on the page — it is instructions for the browser and search engines.</td></tr>
    <tr><td><code>&lt;meta charset="UTF-8"&gt;</code></td><td>Sets the character encoding</td><td>UTF-8 supports all world languages, emojis, and special characters. Without this, characters may display incorrectly.</td></tr>
    <tr><td><code>&lt;meta name="viewport" ...&gt;</code></td><td>Controls how the page scales on mobile</td><td>Without this, mobile browsers zoom out and show a tiny desktop version of your page.</td></tr>
    <tr><td><code>&lt;title&gt;</code></td><td>The page title shown in the browser tab</td><td>Also used by search engines and bookmarks. Make it descriptive.</td></tr>
    <tr><td><code>&lt;body&gt;</code></td><td>Everything visible on the page goes here</td><td>All your content — text, images, buttons — lives inside the body tag.</td></tr>
  </tbody>
</table>

<h3>Head vs Body — What Goes Where?</h3>
<p>A common source of confusion for beginners is knowing what belongs in <code>&lt;head&gt;</code> and what belongs in <code>&lt;body&gt;</code>.</p>
<pre><code>&lt;head&gt;
  &lt;!-- Things the BROWSER needs to know --&gt;
  &lt;meta charset="UTF-8"&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
  &lt;meta name="description" content="Learn web development for free"&gt;
  &lt;title&gt;HackathonAfrica | Learn to Code&lt;/title&gt;
  &lt;link rel="stylesheet" href="styles.css"&gt;  &lt;!-- Link to your CSS file --&gt;
&lt;/head&gt;

&lt;body&gt;
  &lt;!-- Things the USER sees --&gt;
  &lt;h1&gt;Welcome&lt;/h1&gt;
  &lt;p&gt;Start your coding journey today.&lt;/p&gt;
  &lt;script src="app.js" defer&gt;&lt;/script&gt;  &lt;!-- Link to your JS file --&gt;
&lt;/body&gt;</code></pre>

<h3>Where to Put Your JavaScript File</h3>
<p>You may wonder why the <code>&lt;script&gt;</code> tag is at the <em>bottom</em> of <code>&lt;body&gt;</code> instead of in <code>&lt;head&gt;</code>. Here is why:</p>
<ul>
  <li>The browser reads HTML from top to bottom. If it finds a <code>&lt;script&gt;</code> tag early, it <strong>stops building the page</strong> while it downloads and runs the script.</li>
  <li>By placing scripts at the bottom (or using the <code>defer</code> attribute), the user sees the page content faster.</li>
  <li><code>defer</code> means: "Download the script in the background but only run it after the whole page is built."</li>
</ul>

<h3>Creating Your First File</h3>
<p>To create an HTML file, all you need is a text editor (like VS Code) and a web browser. Save the file with a <code>.html</code> extension:</p>
<pre><code>index.html   ← The homepage is always named index.html
about.html
contact.html</code></pre>
<p>Double-click the file to open it in your browser — you do not need a server for basic HTML!</p>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Forgetting <code>&lt;!DOCTYPE html&gt;</code></strong> — Always include it as the very first line.</li>
  <li><strong>Putting visible content in <code>&lt;head&gt;</code></strong> — Content in head is not displayed. All visible content goes in <code>&lt;body&gt;</code>.</li>
  <li><strong>Not closing the <code>&lt;html&gt;</code> tag</strong> — Every page should end with <code>&lt;/html&gt;</code>.</li>
  <li><strong>Naming files with spaces</strong> — Use hyphens instead: <code>my-page.html</code> not <code>my page.html</code>.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>Every HTML file starts with <code>&lt;!DOCTYPE html&gt;</code>.</li>
  <li>The <code>&lt;head&gt;</code> contains metadata — instructions for the browser, not visible content.</li>
  <li>The <code>&lt;body&gt;</code> contains everything the user sees.</li>
  <li>Always set <code>charset="UTF-8"</code> and the viewport meta tag.</li>
  <li>JavaScript files go at the bottom of <code>&lt;body&gt;</code> or use <code>defer</code>.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Create a file called <code>index.html</code> on your computer. Copy the skeleton structure above, change the title and the h1 text to something personal, and open it in your browser. You have just built your first web page!</div>
HTML,2]);

// ── HTML M1 L3 ───────────────────────────────────────────
$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[3,1,'Headings, Paragraphs & Text Formatting',<<<'HTML'
<h2>Headings, Paragraphs &amp; Text Formatting</h2>

<p>Text is the most fundamental content on any web page. In this lesson you will learn how to structure text with headings and paragraphs, and how to apply meaning to specific words using inline elements.</p>

<h3>Headings — Creating a Content Hierarchy</h3>
<p>HTML provides six levels of headings, from <code>&lt;h1&gt;</code> (the most important) to <code>&lt;h6&gt;</code> (the least important). Think of headings like the structure of a book:</p>
<ul>
  <li><code>&lt;h1&gt;</code> = The book title (use only <strong>once</strong> per page)</li>
  <li><code>&lt;h2&gt;</code> = Chapter titles</li>
  <li><code>&lt;h3&gt;</code> = Sections within a chapter</li>
  <li><code>&lt;h4&gt;</code>–<code>&lt;h6&gt;</code> = Sub-sections (use sparingly)</li>
</ul>

<pre><code>&lt;h1&gt;Learn Web Development&lt;/h1&gt;       &lt;!-- Page title — only one --&gt;

&lt;h2&gt;Getting Started&lt;/h2&gt;            &lt;!-- Major section --&gt;
&lt;h3&gt;Setting Up Your Tools&lt;/h3&gt;      &lt;!-- Sub-section --&gt;
&lt;h3&gt;Writing Your First HTML&lt;/h3&gt;    &lt;!-- Another sub-section --&gt;

&lt;h2&gt;Core HTML Elements&lt;/h2&gt;         &lt;!-- Next major section --&gt;
&lt;h3&gt;Text Elements&lt;/h3&gt;</code></pre>

<p class="alert alert-warning mt-2"><strong>Important:</strong> Use headings for document structure, NOT to make text bigger or bold. If you want larger text, use CSS. Screen readers use headings to help visually impaired users navigate the page — misusing them causes real accessibility problems.</p>

<h3>Paragraphs</h3>
<p>The <code>&lt;p&gt;</code> tag wraps a block of text into a paragraph. Browsers automatically add space above and below each paragraph.</p>
<pre><code>&lt;p&gt;HTML is the foundation of every website on the internet.
It gives content structure and meaning.&lt;/p&gt;

&lt;p&gt;This is a second paragraph. Notice how the browser
adds a gap between them automatically.&lt;/p&gt;</code></pre>

<h3>Inline Text Elements — Adding Meaning to Words</h3>
<p>Sometimes you need to highlight or emphasise a specific word or phrase within a paragraph. HTML has several <strong>inline elements</strong> for this:</p>

<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Tag</th><th>Meaning</th><th>Default Appearance</th><th>Use When...</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;strong&gt;</code></td><td>Important/critical</td><td>Bold</td><td>This information is crucial — don't miss it</td></tr>
    <tr><td><code>&lt;em&gt;</code></td><td>Stressed emphasis</td><td>Italic</td><td>Changing the emphasis changes the meaning</td></tr>
    <tr><td><code>&lt;mark&gt;</code></td><td>Highlighted/relevant</td><td>Yellow background</td><td>Highlighting search results or key terms</td></tr>
    <tr><td><code>&lt;code&gt;</code></td><td>Computer code</td><td>Monospace font</td><td>Showing code, filenames, commands inline</td></tr>
    <tr><td><code>&lt;abbr&gt;</code></td><td>Abbreviation</td><td>Dotted underline</td><td>Explaining acronyms on first use</td></tr>
    <tr><td><code>&lt;small&gt;</code></td><td>Small print / fine print</td><td>Smaller font</td><td>Copyright notices, disclaimers</td></tr>
    <tr><td><code>&lt;del&gt;</code></td><td>Deleted text</td><td>Strikethrough</td><td>Showing price reductions, corrections</td></tr>
    <tr><td><code>&lt;ins&gt;</code></td><td>Inserted text</td><td>Underline</td><td>Showing additions in documents</td></tr>
  </tbody>
</table>

<h3>Practical Example — A Product Description</h3>
<pre><code>&lt;h1&gt;Web Development Bootcamp&lt;/h1&gt;

&lt;p&gt;This course teaches you &lt;strong&gt;professional web development&lt;/strong&gt;
from scratch. No prior experience needed — if you can use a computer,
you can learn to code.&lt;/p&gt;

&lt;p&gt;We use &lt;abbr title="HyperText Markup Language"&gt;HTML&lt;/abbr&gt;,
&lt;abbr title="Cascading Style Sheets"&gt;CSS&lt;/abbr&gt;, and JavaScript.&lt;/p&gt;

&lt;p&gt;Price: &lt;del&gt;$200&lt;/del&gt; &lt;strong&gt;Free&lt;/strong&gt; — limited time offer!&lt;/p&gt;

&lt;p&gt;&lt;em&gt;Note:&lt;/em&gt; You will need a &lt;code&gt;.html&lt;/code&gt; file to follow along.&lt;/p&gt;</code></pre>

<h3>Block vs Inline Elements</h3>
<p>This is a concept you will see repeatedly. Understanding it now will save you confusion later:</p>
<ul>
  <li><strong>Block elements</strong> — Take up the full width of the page. They always start on a new line. Examples: <code>&lt;h1&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;div&gt;</code>.</li>
  <li><strong>Inline elements</strong> — Sit within the flow of text. They only take up as much space as their content. Examples: <code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>, <code>&lt;code&gt;</code>.</li>
</ul>
<pre><code>&lt;!-- Block elements stack vertically --&gt;
&lt;p&gt;First paragraph.&lt;/p&gt;
&lt;p&gt;Second paragraph. (starts on a new line)&lt;/p&gt;

&lt;!-- Inline elements flow within text --&gt;
&lt;p&gt;Learn &lt;strong&gt;HTML&lt;/strong&gt;, &lt;em&gt;CSS&lt;/em&gt;, and &lt;code&gt;JavaScript&lt;/code&gt;.&lt;/p&gt;</code></pre>

<h3>The Line Break and Horizontal Rule</h3>
<pre><code>&lt;!-- Force a line break within a paragraph (use sparingly) --&gt;
&lt;p&gt;HackathonAfrica&lt;br&gt;
Accra, Ghana&lt;/p&gt;

&lt;!-- A thematic divider between content sections --&gt;
&lt;hr&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using <code>&lt;b&gt;</code> instead of <code>&lt;strong&gt;</code></strong> — <code>&lt;b&gt;</code> just makes text bold visually with no semantic meaning. <code>&lt;strong&gt;</code> says "this is important content."</li>
  <li><strong>Using <code>&lt;i&gt;</code> instead of <code>&lt;em&gt;</code></strong> — Same issue. <code>&lt;em&gt;</code> means "emphasise this." <code>&lt;i&gt;</code> is just italic.</li>
  <li><strong>Skipping heading levels</strong> — Don't go from <code>&lt;h1&gt;</code> straight to <code>&lt;h4&gt;</code>. Keep the hierarchy logical.</li>
  <li><strong>Using multiple <code>&lt;h1&gt;</code> tags</strong> — There should be exactly one <code>&lt;h1&gt;</code> per page.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>Use headings (<code>h1</code>–<code>h6</code>) to create a logical document outline, not to control text size.</li>
  <li>Use <code>&lt;p&gt;</code> for all paragraphs of text.</li>
  <li>Use semantic inline elements (<code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code>) rather than visual ones (<code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>).</li>
  <li>Block elements stack vertically; inline elements flow within text.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Write a short "About Me" page using at least one <code>&lt;h1&gt;</code>, two <code>&lt;h2&gt;</code> headings, three paragraphs, and use <code>&lt;strong&gt;</code> and <code>&lt;em&gt;</code> to highlight key words.</div>
HTML,3]);

// ── HTML M1 L4 ───────────────────────────────────────────
$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[4,1,'Lists, Links & Navigation',<<<'HTML'
<h2>Lists, Links &amp; Navigation</h2>

<p>Lists and links are two of the most-used features in HTML. Lists organise information clearly; links are what make the web a <em>web</em> — they connect every page to every other page. In this lesson you will master both.</p>

<h3>Three Types of Lists</h3>

<h4>1. Unordered List — When Order Doesn't Matter</h4>
<p>Use this for items where the sequence is not important, like a shopping list or a list of features.</p>
<pre><code>&lt;ul&gt;
  &lt;li&gt;HTML — structure&lt;/li&gt;
  &lt;li&gt;CSS — style&lt;/li&gt;
  &lt;li&gt;JavaScript — interactivity&lt;/li&gt;
&lt;/ul&gt;</code></pre>
<p>This renders as a bullet-point list. Each <code>&lt;li&gt;</code> is a list item.</p>

<h4>2. Ordered List — When Sequence Matters</h4>
<p>Use this for steps, rankings, or anything where the order is important.</p>
<pre><code>&lt;ol&gt;
  &lt;li&gt;Download VS Code&lt;/li&gt;
  &lt;li&gt;Create a new file called index.html&lt;/li&gt;
  &lt;li&gt;Write your HTML code&lt;/li&gt;
  &lt;li&gt;Open the file in your browser&lt;/li&gt;
&lt;/ol&gt;</code></pre>
<p>This renders as a numbered list (1, 2, 3, 4).</p>

<h4>3. Description List — Term and Definition Pairs</h4>
<p>Use this for glossaries, FAQs, or any key-value pair of information.</p>
<pre><code>&lt;dl&gt;
  &lt;dt&gt;HTML&lt;/dt&gt;
  &lt;dd&gt;HyperText Markup Language — defines the structure of web pages.&lt;/dd&gt;

  &lt;dt&gt;CSS&lt;/dt&gt;
  &lt;dd&gt;Cascading Style Sheets — controls the visual appearance.&lt;/dd&gt;
&lt;/dl&gt;</code></pre>
<p><code>&lt;dt&gt;</code> is the term; <code>&lt;dd&gt;</code> is the description/definition.</p>

<h4>Nested Lists</h4>
<p>You can put a list inside a list item to create sub-items:</p>
<pre><code>&lt;ul&gt;
  &lt;li&gt;Frontend
    &lt;ul&gt;
      &lt;li&gt;HTML&lt;/li&gt;
      &lt;li&gt;CSS&lt;/li&gt;
      &lt;li&gt;JavaScript&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/li&gt;
  &lt;li&gt;Backend
    &lt;ul&gt;
      &lt;li&gt;PHP&lt;/li&gt;
      &lt;li&gt;Python&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/li&gt;
&lt;/ul&gt;</code></pre>

<h3>Links — The Heart of the Web</h3>
<p>Links are created with the <code>&lt;a&gt;</code> (anchor) tag. The <code>href</code> attribute tells the browser where to go.</p>

<pre><code>&lt;!-- Basic link --&gt;
&lt;a href="https://hackathon.africa"&gt;Visit HackathonAfrica&lt;/a&gt;</code></pre>

<h4>Types of Links</h4>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Link Type</th><th>href Value</th><th>Example</th></tr></thead>
  <tbody>
    <tr><td>External website</td><td>Full URL starting with https://</td><td><code>href="https://google.com"</code></td></tr>
    <tr><td>Another page in your site</td><td>Relative path</td><td><code>href="about.html"</code> or <code>href="/pages/about.html"</code></td></tr>
    <tr><td>Same-page section</td><td># followed by element ID</td><td><code>href="#contact"</code></td></tr>
    <tr><td>Email</td><td>mailto: prefix</td><td><code>href="mailto:hello@example.com"</code></td></tr>
    <tr><td>Phone number</td><td>tel: prefix</td><td><code>href="tel:+233201234567"</code></td></tr>
    <tr><td>Download a file</td><td>Any URL + download attribute</td><td><code>href="cv.pdf" download</code></td></tr>
  </tbody>
</table>

<h4>Opening Links in a New Tab</h4>
<pre><code>&lt;!-- target="_blank" opens the link in a new browser tab --&gt;
&lt;!-- rel="noopener noreferrer" is a SECURITY requirement — always add it --&gt;
&lt;a href="https://github.com" target="_blank" rel="noopener noreferrer"&gt;
  View on GitHub
&lt;/a&gt;</code></pre>
<p>Without <code>rel="noopener noreferrer"</code>, the new tab can access your page through a browser security vulnerability. Always include it when using <code>target="_blank"</code>.</p>

<h4>Jump Links (In-Page Navigation)</h4>
<pre><code>&lt;!-- The link --&gt;
&lt;a href="#about"&gt;Jump to About section&lt;/a&gt;

&lt;!-- The target section (must have a matching id) --&gt;
&lt;section id="about"&gt;
  &lt;h2&gt;About Us&lt;/h2&gt;
  &lt;p&gt;We train African developers...&lt;/p&gt;
&lt;/section&gt;</code></pre>

<h3>Building a Navigation Menu</h3>
<p>Navigation menus are almost always built with a <code>&lt;nav&gt;</code> element containing an unordered list of links:</p>
<pre><code>&lt;nav&gt;
  &lt;ul&gt;
    &lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/courses.html"&gt;Courses&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/about.html"&gt;About&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/contact.html"&gt;Contact&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
&lt;/nav&gt;</code></pre>
<p>The <code>&lt;nav&gt;</code> element tells browsers and screen readers "this group of links is for navigating the site." We will style this into a proper horizontal navigation bar with CSS later.</p>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Forgetting <code>href</code></strong> — A link without <code>href</code> is not clickable. <code>&lt;a&gt;Click me&lt;/a&gt;</code> does nothing.</li>
  <li><strong>Not using descriptive link text</strong> — Avoid "click here". Say <em>what</em> the link leads to: "Download the course syllabus". This helps accessibility and SEO.</li>
  <li><strong>Forgetting <code>rel="noopener noreferrer"</code></strong> with <code>target="_blank"</code> — a security risk.</li>
  <li><strong>Putting block elements inside <code>&lt;a&gt;</code></strong> — In HTML5, you <em>can</em> wrap a block element (like a <code>&lt;div&gt;</code>) in an <code>&lt;a&gt;</code>, but be careful — it can cause confusing behaviour.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>Use <code>&lt;ul&gt;</code> for unordered (bullet) lists, <code>&lt;ol&gt;</code> for ordered (numbered) lists, and <code>&lt;dl&gt;</code> for term-definition pairs.</li>
  <li>Every link needs an <code>href</code> attribute to work.</li>
  <li>Always add <code>rel="noopener noreferrer"</code> when using <code>target="_blank"</code>.</li>
  <li>Navigation menus should use <code>&lt;nav&gt;</code> with a list of links inside.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a simple navigation with 4 links. Then add a "Features" section with an unordered list of 5 features, and a "How to Sign Up" section with an ordered list of 4 steps. Link the navigation items to these sections using anchor links (<code>#</code>).</div>
HTML,4]);

// ── HTML M1 L5 ───────────────────────────────────────────
$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[5,1,'Images & Media',<<<'HTML'
<h2>Images &amp; Media</h2>

<p>Images make websites come alive. In this lesson you will learn how to add images to your pages correctly — including the attributes that make images accessible to visually impaired users and fast-loading on slow connections.</p>

<h3>Adding an Image</h3>
<p>Images use the <code>&lt;img&gt;</code> tag. Unlike most HTML elements, it does not need a closing tag. It is <strong>self-closing</strong>.</p>

<pre><code>&lt;img src="team-photo.jpg" alt="The HackathonAfrica team at the 2025 bootcamp"&gt;</code></pre>

<p>There are two attributes you must always include:</p>
<ul>
  <li><code>src</code> (source) — The path to the image file. It can be a relative path (a file on your own server) or a full URL.</li>
  <li><code>alt</code> (alternative text) — A text description of the image used by screen readers for visually impaired users and displayed when the image fails to load.</li>
</ul>

<h3>Image Paths: Relative vs Absolute</h3>
<pre><code>&lt;!-- Absolute path — full URL to an image anywhere on the web --&gt;
&lt;img src="https://example.com/images/logo.png" alt="Logo"&gt;

&lt;!-- Relative path — image file in the same folder as your HTML --&gt;
&lt;img src="logo.png" alt="Logo"&gt;

&lt;!-- Relative path — image in a subfolder called "images" --&gt;
&lt;img src="images/logo.png" alt="Logo"&gt;

&lt;!-- Going up one folder level --&gt;
&lt;img src="../images/logo.png" alt="Logo"&gt;</code></pre>

<p class="alert alert-warning mt-2"><strong>Tip:</strong> Organise your images in an <code>images/</code> or <code>assets/</code> folder inside your project. Keep filenames lowercase with hyphens, not spaces: <code>team-photo.jpg</code> not <code>Team Photo.jpg</code>.</p>

<h3>Writing Good Alt Text</h3>
<p>Alt text is one of the most important accessibility features in HTML. Here are the rules:</p>

<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Situation</th><th>Alt Text Rule</th><th>Example</th></tr></thead>
  <tbody>
    <tr><td>Informative image</td><td>Describe what the image shows and its purpose</td><td><code>alt="Bar chart showing website traffic growth from January to June 2025"</code></td></tr>
    <tr><td>Image used as a link</td><td>Describe where the link goes</td><td><code>alt="Go to HackathonAfrica homepage"</code></td></tr>
    <tr><td>Pure decoration</td><td>Empty alt (screen readers will skip it)</td><td><code>alt=""</code></td></tr>
    <tr><td>Complex image (chart/diagram)</td><td>Short alt + detailed description in nearby text</td><td><code>alt="Sales funnel diagram — details below"</code></td></tr>
  </tbody>
</table>

<h3>Setting Image Dimensions</h3>
<pre><code>&lt;!-- Always specify width and height to prevent layout shifts --&gt;
&lt;img src="hero.jpg"
     alt="African developers collaborating at a hackathon"
     width="1200"
     height="600"&gt;</code></pre>
<p>Setting <code>width</code> and <code>height</code> tells the browser how much space to reserve before the image downloads. Without these attributes, the page "jumps" as images load — this is called <strong>Cumulative Layout Shift (CLS)</strong> and it annoys users.</p>

<h3>Performance — Loading Images Efficiently</h3>
<pre><code>&lt;!-- loading="lazy": only download image when near the viewport --&gt;
&lt;img src="product.jpg"
     alt="Blue running shoes"
     width="400"
     height="400"
     loading="lazy"&gt;

&lt;!-- Use loading="eager" (or omit) for images visible on first load --&gt;
&lt;img src="hero.jpg"
     alt="Hero banner"
     width="1200"
     height="600"
     loading="eager"&gt;</code></pre>
<p><code>loading="lazy"</code> is one of the most impactful performance improvements you can make for free. Images below the fold are only downloaded when the user scrolls near them, saving data and speeding up initial page load.</p>

<h3>Responsive Images — Fitting Any Screen</h3>
<pre><code>&lt;!-- In CSS you would write: img { max-width: 100%; height: auto; } --&gt;
&lt;!-- But in HTML, use the srcset for serving different sizes --&gt;

&lt;img src="photo-800.jpg"
     srcset="photo-400.jpg 400w,
             photo-800.jpg 800w,
             photo-1200.jpg 1200w"
     sizes="(max-width: 600px) 100vw, 50vw"
     alt="Community members at a coding event"
     width="800"
     height="500"&gt;</code></pre>
<p>This tells the browser: "Here are three versions of this image. Pick the best one for the current screen size." Smaller screens get the smaller file — saving bandwidth for users on mobile data.</p>

<h3>The Figure and Figcaption Elements</h3>
<p>When an image has a caption, wrap them together in <code>&lt;figure&gt;</code>:</p>
<pre><code>&lt;figure&gt;
  &lt;img src="hackathon-2025.jpg"
       alt="Participants coding at HackathonAfrica 2025"
       width="800"
       height="500"
       loading="lazy"&gt;
  &lt;figcaption&gt;Over 200 developers participated in HackathonAfrica 2025.&lt;/figcaption&gt;
&lt;/figure&gt;</code></pre>
<p><code>&lt;figure&gt;</code> is a semantic container for any self-contained media (image, chart, code snippet, video) with its caption.</p>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Missing alt text</strong> — Never omit <code>alt</code>. If the image is decorative, use <code>alt=""</code>.</li>
  <li><strong>Using alt text like "image of..." or "photo of..."</strong> — Screen readers already say it's an image. Describe the content directly.</li>
  <li><strong>Not specifying width and height</strong> — Causes layout shifts that hurt user experience.</li>
  <li><strong>Using PNG for photographs</strong> — Use JPEG or WebP for photos (much smaller files). Use PNG for logos and icons with transparency.</li>
  <li><strong>Linking to images on other websites</strong> — Their server can remove or change the image, and it may be slow or against their terms of service. Host your own images.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>Always provide descriptive <code>alt</code> text for meaningful images; use <code>alt=""</code> for decorative images.</li>
  <li>Set <code>width</code> and <code>height</code> to prevent layout shifts.</li>
  <li>Use <code>loading="lazy"</code> for images below the fold to improve performance.</li>
  <li>Wrap images with captions in <code>&lt;figure&gt;</code> and <code>&lt;figcaption&gt;</code>.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Find a free image from <code>unsplash.com</code>. Download it and save it to an <code>images/</code> folder. Add it to your HTML page with proper alt text, width, height, and <code>loading="lazy"</code>. Then wrap it in a <code>&lt;figure&gt;</code> with a caption.</div>
HTML,5]);

echo "  [✓] HTML Module 1 lessons\n";

// ── HTML Module 2 ─────────────────────────────────────────
$ins('INSERT INTO modules(id,course_id,title,description,order_index)VALUES(?,?,?,?,?)',
    [2,1,'Semantic HTML5',
     'Learn to use HTML elements that carry meaning — making your pages better for search engines, screen readers, and future developers.',2]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[6,2,'Why Semantic HTML Matters',<<<'HTML'
<h2>Why Semantic HTML Matters</h2>

<p>You could build an entire website using only <code>&lt;div&gt;</code> and <code>&lt;span&gt;</code> tags. It would look the same in the browser. But it would be a disaster for accessibility, SEO, and maintainability. This lesson explains why the <em>meaning</em> of your HTML tags is just as important as their appearance.</p>

<h3>What Does "Semantic" Mean?</h3>
<p><strong>Semantic</strong> means "relating to meaning." A semantic HTML element tells both the browser and the developer what the content <em>is</em>, not just how it looks.</p>

<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Non-Semantic</th><th>Semantic Equivalent</th><th>What it Communicates</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;div id="header"&gt;</code></td><td><code>&lt;header&gt;</code></td><td>This is the page header</td></tr>
    <tr><td><code>&lt;div id="nav"&gt;</code></td><td><code>&lt;nav&gt;</code></td><td>This is navigation</td></tr>
    <tr><td><code>&lt;div id="main"&gt;</code></td><td><code>&lt;main&gt;</code></td><td>This is the main content</td></tr>
    <tr><td><code>&lt;div id="footer"&gt;</code></td><td><code>&lt;footer&gt;</code></td><td>This is the footer</td></tr>
    <tr><td><code>&lt;div class="article"&gt;</code></td><td><code>&lt;article&gt;</code></td><td>This is a standalone piece of content</td></tr>
  </tbody>
</table>

<h3>Three Reasons Semantics Matter</h3>

<h4>1. Accessibility</h4>
<p>Approximately <strong>15% of the world's population</strong> has some form of disability. Screen readers (software used by visually impaired people) navigate pages using semantic HTML elements. When a screen reader finds a <code>&lt;nav&gt;</code> element, it announces "navigation" and lets the user jump straight to it. A <code>&lt;div&gt;</code> with the same content gives no such help.</p>

<h4>2. SEO (Search Engine Optimisation)</h4>
<p>Search engines like Google use your HTML structure to understand what your page is about. An <code>&lt;article&gt;</code> tells Google "this is a complete, shareable piece of content." An <code>&lt;h1&gt;</code> signals the primary topic of the page. Proper semantics improve your ranking in search results.</p>

<h4>3. Maintainability</h4>
<p>When you or another developer comes back to your code in 6 months, semantic HTML makes the structure immediately obvious:</p>
<pre><code>&lt;!-- Hard to understand --&gt;
&lt;div class="box1"&gt;
  &lt;div class="inner"&gt;...&lt;/div&gt;
&lt;/div&gt;

&lt;!-- Immediately clear --&gt;
&lt;article&gt;
  &lt;header&gt;...&lt;/header&gt;
&lt;/article&gt;</code></pre>

<h3>The Non-Semantic Fallbacks</h3>
<p>You will still use <code>&lt;div&gt;</code> and <code>&lt;span&gt;</code> — they are not bad. They are just <strong>containers with no meaning</strong>, used when no semantic element fits:</p>
<ul>
  <li><code>&lt;div&gt;</code> — A generic block-level container (for layout and grouping)</li>
  <li><code>&lt;span&gt;</code> — A generic inline container (for styling a piece of text)</li>
</ul>
<pre><code>&lt;!-- Using div for layout (fine — no semantic equivalent) --&gt;
&lt;div class="card-grid"&gt;
  &lt;article&gt;...&lt;/article&gt;
  &lt;article&gt;...&lt;/article&gt;
&lt;/div&gt;

&lt;!-- Using span to style a specific word --&gt;
&lt;p&gt;Price: &lt;span class="price-highlight"&gt;Free&lt;/span&gt;&lt;/p&gt;</code></pre>

<h3>A Real-World Comparison</h3>
<p>Imagine two restaurants. Both serve the same food. But one has labelled shelves ("Appetisers", "Main Course", "Desserts") and the other has all the food in plain boxes with no labels. The first is easier to navigate — that is semantic HTML.</p>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using <code>&lt;div&gt;</code> for everything</strong> — Ask yourself: "Is there a semantic element for this?" before reaching for a <code>&lt;div&gt;</code>.</li>
  <li><strong>Misusing <code>&lt;section&gt;</code></strong> — A <code>&lt;section&gt;</code> should always have a heading. If it does not, use a <code>&lt;div&gt;</code>.</li>
  <li><strong>Using semantic elements just for their default styling</strong> — Don't use <code>&lt;blockquote&gt;</code> just to get indented text. Use it when the content actually <em>is</em> a quote.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>Semantic HTML uses elements that describe the meaning of content, not just its appearance.</li>
  <li>Good semantics improves accessibility, SEO, and code readability.</li>
  <li>Use <code>&lt;div&gt;</code> and <code>&lt;span&gt;</code> only when no semantic element fits.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Take the "About Me" page you built earlier. Replace any <code>&lt;div&gt;</code> containers with appropriate semantic elements. Add a <code>&lt;header&gt;</code>, <code>&lt;main&gt;</code>, and <code>&lt;footer&gt;</code>.</div>
HTML,1]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[7,2,'Page Layout Elements: header, nav, main, aside, footer',<<<'HTML'
<h2>Page Layout Elements: header, nav, main, aside, footer</h2>

<p>HTML5 introduced a set of landmark elements that define the major regions of a web page. Learning these allows you to give your page a clear, meaningful structure that both users and machines can understand.</p>

<h3>The Anatomy of a Web Page</h3>
<p>Most web pages follow a familiar layout pattern. Here are the HTML5 elements that correspond to each region:</p>

<pre><code>&lt;body&gt;
  &lt;header&gt;
    &lt;!-- Site logo, branding, top navigation --&gt;
  &lt;/header&gt;

  &lt;nav&gt;
    &lt;!-- Main navigation links --&gt;
  &lt;/nav&gt;

  &lt;main&gt;
    &lt;!-- The unique content of this specific page --&gt;
    &lt;article&gt;
      &lt;!-- A self-contained piece of content --&gt;
    &lt;/article&gt;

    &lt;aside&gt;
      &lt;!-- Sidebar: related links, ads, author bio --&gt;
    &lt;/aside&gt;
  &lt;/main&gt;

  &lt;footer&gt;
    &lt;!-- Copyright, links, contact info --&gt;
  &lt;/footer&gt;
&lt;/body&gt;</code></pre>

<h3>Each Element Explained</h3>

<h4>&lt;header&gt;</h4>
<p>Contains introductory content for a page or a section. On a page level, it typically holds the logo, site name, and main navigation. It can also appear inside <code>&lt;article&gt;</code> or <code>&lt;section&gt;</code> as a heading area for that content.</p>
<pre><code>&lt;header&gt;
  &lt;a href="/"&gt;
    &lt;img src="logo.png" alt="HackathonAfrica" width="150" height="40"&gt;
  &lt;/a&gt;
  &lt;nav&gt;
    &lt;ul&gt;
      &lt;li&gt;&lt;a href="/courses"&gt;Courses&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/nav&gt;
&lt;/header&gt;</code></pre>

<h4>&lt;nav&gt;</h4>
<p>Wraps a group of navigation links. You can have multiple <code>&lt;nav&gt;</code> elements on a page (main nav, footer nav, breadcrumbs). Use the <code>aria-label</code> attribute to distinguish them for screen readers.</p>
<pre><code>&lt;nav aria-label="Main navigation"&gt;
  &lt;ul&gt;
    &lt;li&gt;&lt;a href="/" aria-current="page"&gt;Home&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/courses"&gt;Courses&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
&lt;/nav&gt;</code></pre>
<p><code>aria-current="page"</code> marks the current active page for screen readers.</p>

<h4>&lt;main&gt;</h4>
<p>Contains the dominant, unique content of the page. There should be <strong>only one</strong> <code>&lt;main&gt;</code> per page. It should not include repeated elements like the site header, navigation, or footer.</p>
<pre><code>&lt;main&gt;
  &lt;h1&gt;Introduction to HTML&lt;/h1&gt;
  &lt;p&gt;In this course you will learn...&lt;/p&gt;
&lt;/main&gt;</code></pre>

<h4>&lt;aside&gt;</h4>
<p>Contains content that is related to the main content but could stand alone — like a sidebar with related articles, an author biography, a call-to-action, or an advertisement.</p>
<pre><code>&lt;aside&gt;
  &lt;h3&gt;Related Courses&lt;/h3&gt;
  &lt;ul&gt;
    &lt;li&gt;&lt;a href="/css"&gt;CSS Fundamentals&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/js"&gt;JavaScript Basics&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
&lt;/aside&gt;</code></pre>

<h4>&lt;footer&gt;</h4>
<p>Contains closing information for a page or section: copyright notices, secondary navigation, contact links, social media links, and legal information.</p>
<pre><code>&lt;footer&gt;
  &lt;nav aria-label="Footer navigation"&gt;
    &lt;ul&gt;
      &lt;li&gt;&lt;a href="/privacy"&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="/terms"&gt;Terms of Service&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/nav&gt;
  &lt;p&gt;&lt;small&gt;&copy; 2025 HackathonAfrica. All rights reserved.&lt;/small&gt;&lt;/p&gt;
&lt;/footer&gt;</code></pre>

<h3>Putting It All Together — A Complete Page Template</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;meta charset="UTF-8"&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
  &lt;title&gt;Courses | HackathonAfrica&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;

  &lt;header&gt;
    &lt;a href="/"&gt;&lt;img src="logo.png" alt="HackathonAfrica" width="150" height="40"&gt;&lt;/a&gt;
    &lt;nav aria-label="Main navigation"&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/courses" aria-current="page"&gt;Courses&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/nav&gt;
  &lt;/header&gt;

  &lt;main&gt;
    &lt;h1&gt;Our Courses&lt;/h1&gt;
    &lt;section&gt;
      &lt;h2&gt;Web Development Track&lt;/h2&gt;
      &lt;article&gt;
        &lt;h3&gt;HTML Fundamentals&lt;/h3&gt;
        &lt;p&gt;Learn to build the structure of web pages.&lt;/p&gt;
        &lt;a href="/courses/html"&gt;Start Learning&lt;/a&gt;
      &lt;/article&gt;
    &lt;/section&gt;

    &lt;aside&gt;
      &lt;h2&gt;Why Learn to Code?&lt;/h2&gt;
      &lt;p&gt;The African tech sector is growing 40% year over year...&lt;/p&gt;
    &lt;/aside&gt;
  &lt;/main&gt;

  &lt;footer&gt;
    &lt;p&gt;&lt;small&gt;&copy; 2025 HackathonAfrica&lt;/small&gt;&lt;/p&gt;
  &lt;/footer&gt;

&lt;/body&gt;
&lt;/html&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Multiple <code>&lt;main&gt;</code> elements</strong> — There must be only one <code>&lt;main&gt;</code> per page.</li>
  <li><strong>Putting <code>&lt;main&gt;</code> inside <code>&lt;header&gt;</code> or <code>&lt;footer&gt;</code></strong> — <code>&lt;main&gt;</code> is a sibling of <code>&lt;header&gt;</code> and <code>&lt;footer&gt;</code>, not a child.</li>
  <li><strong>Using <code>&lt;header&gt;</code> and <code>&lt;footer&gt;</code> only once</strong> — They can be used inside <code>&lt;article&gt;</code> and <code>&lt;section&gt;</code> too.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li><code>&lt;header&gt;</code>, <code>&lt;nav&gt;</code>, <code>&lt;main&gt;</code>, <code>&lt;aside&gt;</code>, and <code>&lt;footer&gt;</code> are the five major landmark elements.</li>
  <li>Only one <code>&lt;main&gt;</code> per page; it holds the unique content.</li>
  <li>Use <code>aria-label</code> to distinguish multiple <code>&lt;nav&gt;</code> elements on the same page.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Rebuild your "About Me" page using all five landmark elements. Your page should have a header with a nav, a main section with your content, an aside with "Fun Facts About Me", and a footer with a copyright notice.</div>
HTML,2]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[8,2,'Article, Section & Content Grouping',<<<'HTML'
<h2>Article, Section &amp; Content Grouping</h2>

<p>Inside the <code>&lt;main&gt;</code> region of your page, you need to organise content into logical groups. HTML5 gives you <code>&lt;article&gt;</code>, <code>&lt;section&gt;</code>, and <code>&lt;figure&gt;</code> for this purpose. Choosing the right one matters for both semantics and accessibility.</p>

<h3>&lt;article&gt; — Self-Contained, Reusable Content</h3>
<p>An <code>&lt;article&gt;</code> is a standalone piece of content that would make sense on its own, even if you removed it from the page. The test: <em>could this content be shared or republished independently?</em></p>

<p>Good uses for <code>&lt;article&gt;</code>:</p>
<ul>
  <li>A blog post</li>
  <li>A news story</li>
  <li>A product card</li>
  <li>A social media post / comment</li>
  <li>A forum post</li>
</ul>

<pre><code>&lt;article&gt;
  &lt;header&gt;
    &lt;h2&gt;5 Reasons to Learn Web Development in 2025&lt;/h2&gt;
    &lt;p&gt;Published by &lt;strong&gt;Kwame Asante&lt;/strong&gt; on
       &lt;time datetime="2025-03-15"&gt;March 15, 2025&lt;/time&gt;&lt;/p&gt;
  &lt;/header&gt;

  &lt;p&gt;The demand for web developers in Africa is growing faster than ever...&lt;/p&gt;
  &lt;p&gt;Companies across Lagos, Nairobi, and Accra are hiring...&lt;/p&gt;

  &lt;footer&gt;
    &lt;p&gt;Tags: &lt;a href="/tag/career"&gt;Career&lt;/a&gt;, &lt;a href="/tag/web-dev"&gt;Web Dev&lt;/a&gt;&lt;/p&gt;
  &lt;/footer&gt;
&lt;/article&gt;</code></pre>

<h3>&lt;section&gt; — A Thematic Group of Content</h3>
<p>A <code>&lt;section&gt;</code> groups related content that is part of a larger whole. Unlike <code>&lt;article&gt;</code>, a section does not make sense in isolation. It needs the surrounding context.</p>

<p>Good uses for <code>&lt;section&gt;</code>:</p>
<ul>
  <li>Chapters of a long article</li>
  <li>Tabs or panels in an interface</li>
  <li>"Features", "Testimonials", "Pricing" sections of a landing page</li>
</ul>

<pre><code>&lt;main&gt;
  &lt;h1&gt;About Our Bootcamp&lt;/h1&gt;

  &lt;section&gt;
    &lt;h2&gt;What You Will Learn&lt;/h2&gt;
    &lt;ul&gt;
      &lt;li&gt;HTML &amp; CSS fundamentals&lt;/li&gt;
      &lt;li&gt;JavaScript programming&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/section&gt;

  &lt;section&gt;
    &lt;h2&gt;Who Should Apply&lt;/h2&gt;
    &lt;p&gt;This bootcamp is for complete beginners with no prior coding experience.&lt;/p&gt;
  &lt;/section&gt;
&lt;/main&gt;</code></pre>

<p class="alert alert-warning mt-2"><strong>Rule:</strong> A <code>&lt;section&gt;</code> should always have a heading (<code>&lt;h2&gt;</code>, <code>&lt;h3&gt;</code>, etc.). If you cannot think of a heading for it, use a <code>&lt;div&gt;</code> instead.</p>

<h3>Article vs Section — The Decision</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Question</th><th>Yes → Use</th><th>No → Use</th></tr></thead>
  <tbody>
    <tr><td>Could this content be shared/republished independently?</td><td><code>&lt;article&gt;</code></td><td><code>&lt;section&gt;</code></td></tr>
    <tr><td>Does it represent one complete item (post, product)?</td><td><code>&lt;article&gt;</code></td><td><code>&lt;section&gt;</code></td></tr>
    <tr><td>Is it a chapter/part of a bigger topic?</td><td><code>&lt;section&gt;</code></td><td><code>&lt;article&gt;</code></td></tr>
  </tbody>
</table>

<h3>&lt;figure&gt; and &lt;figcaption&gt;</h3>
<p>Use <code>&lt;figure&gt;</code> to group any self-contained media (image, diagram, code example, chart) with its caption.</p>
<pre><code>&lt;figure&gt;
  &lt;img src="dom-tree.png"
       alt="Diagram of the HTML Document Object Model tree structure"
       width="600" height="400" loading="lazy"&gt;
  &lt;figcaption&gt;
    Figure 1: The DOM tree — how browsers represent your HTML internally.
  &lt;/figcaption&gt;
&lt;/figure&gt;</code></pre>

<h3>&lt;blockquote&gt; and &lt;cite&gt;</h3>
<pre><code>&lt;blockquote cite="https://developer.mozilla.org"&gt;
  &lt;p&gt;"HTML is the standard markup language for creating web pages."&lt;/p&gt;
  &lt;footer&gt;— &lt;cite&gt;MDN Web Docs&lt;/cite&gt;&lt;/footer&gt;
&lt;/blockquote&gt;</code></pre>

<h3>A Complete Blog Page Structure</h3>
<pre><code>&lt;main&gt;
  &lt;section&gt;
    &lt;h2&gt;Latest Articles&lt;/h2&gt;

    &lt;article&gt;
      &lt;header&gt;
        &lt;h3&gt;&lt;a href="/blog/html-tips"&gt;10 HTML Tips Every Beginner Needs&lt;/a&gt;&lt;/h3&gt;
        &lt;time datetime="2025-04-01"&gt;April 1, 2025&lt;/time&gt;
      &lt;/header&gt;
      &lt;p&gt;These essential tips will save you hours of debugging...&lt;/p&gt;
    &lt;/article&gt;

    &lt;article&gt;
      &lt;header&gt;
        &lt;h3&gt;&lt;a href="/blog/css-grid"&gt;CSS Grid: From Zero to Hero&lt;/a&gt;&lt;/h3&gt;
        &lt;time datetime="2025-03-20"&gt;March 20, 2025&lt;/time&gt;
      &lt;/header&gt;
      &lt;p&gt;Grid layout changed how developers build web pages...&lt;/p&gt;
    &lt;/article&gt;
  &lt;/section&gt;
&lt;/main&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Nesting <code>&lt;section&gt;</code> inside <code>&lt;section&gt;</code> excessively</strong> — Two or three levels is usually the maximum before your structure becomes confusing.</li>
  <li><strong>Using <code>&lt;section&gt;</code> without a heading</strong> — Always include a heading inside a <code>&lt;section&gt;</code>.</li>
  <li><strong>Confusing <code>&lt;article&gt;</code> with <code>&lt;section&gt;</code></strong> — Ask: "Is this self-contained?" If yes, use <code>&lt;article&gt;</code>.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build the structure (without content) of a news website homepage. It should have at least three <code>&lt;article&gt;</code> cards in a "Top Stories" <code>&lt;section&gt;</code>, a "Sports" <code>&lt;section&gt;</code> with its own articles, and an <code>&lt;aside&gt;</code> with trending topics.</div>
HTML,3]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[9,2,'Tables for Tabular Data',<<<'HTML'
<h2>Tables for Tabular Data</h2>

<p>Tables are for displaying information that naturally fits into rows and columns — like a spreadsheet, a train timetable, or a pricing comparison. In this lesson you will learn how to build accessible, well-structured tables.</p>

<h3>When to Use (and NOT Use) Tables</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Use a Table For</th><th>Do NOT Use a Table For</th></tr></thead>
  <tbody>
    <tr><td>Schedules and timetables</td><td>Page layout (use CSS Grid/Flexbox)</td></tr>
    <tr><td>Financial data and statistics</td><td>Side-by-side columns of text</td></tr>
    <tr><td>Feature comparison charts</td><td>Navigation menus</td></tr>
    <tr><td>Sports league standings</td><td>Image galleries</td></tr>
  </tbody>
</table>

<p class="alert alert-warning mt-2"><strong>Important:</strong> Before the year 2000, developers used tables for ALL page layouts. This was a terrible practice — do not do it. Tables are only for data that is inherently tabular.</p>

<h3>Basic Table Structure</h3>
<pre><code>&lt;table&gt;
  &lt;thead&gt;          &lt;!-- Table header section --&gt;
    &lt;tr&gt;           &lt;!-- Table row --&gt;
      &lt;th&gt;Name&lt;/th&gt;     &lt;!-- Table header cell --&gt;
      &lt;th&gt;Course&lt;/th&gt;
      &lt;th&gt;Score&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;

  &lt;tbody&gt;          &lt;!-- Table body --&gt;
    &lt;tr&gt;
      &lt;td&gt;Amara Diallo&lt;/td&gt;   &lt;!-- Table data cell --&gt;
      &lt;td&gt;HTML Fundamentals&lt;/td&gt;
      &lt;td&gt;92%&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;td&gt;Kofi Mensah&lt;/td&gt;
      &lt;td&gt;CSS Layouts&lt;/td&gt;
      &lt;td&gt;88%&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;

  &lt;tfoot&gt;          &lt;!-- Table footer (totals, averages) --&gt;
    &lt;tr&gt;
      &lt;td colspan="2"&gt;Class Average&lt;/td&gt;
      &lt;td&gt;90%&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tfoot&gt;
&lt;/table&gt;</code></pre>

<h3>Table Elements Reference</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Element</th><th>Purpose</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;table&gt;</code></td><td>The table container</td></tr>
    <tr><td><code>&lt;thead&gt;</code></td><td>Groups the header row(s)</td></tr>
    <tr><td><code>&lt;tbody&gt;</code></td><td>Groups the main data rows</td></tr>
    <tr><td><code>&lt;tfoot&gt;</code></td><td>Groups footer rows (totals, summaries)</td></tr>
    <tr><td><code>&lt;tr&gt;</code></td><td>A table row</td></tr>
    <tr><td><code>&lt;th&gt;</code></td><td>A header cell (bold, centred by default)</td></tr>
    <tr><td><code>&lt;td&gt;</code></td><td>A data cell</td></tr>
    <tr><td><code>&lt;caption&gt;</code></td><td>A title/description for the whole table</td></tr>
  </tbody>
</table>

<h3>Spanning Columns and Rows</h3>
<pre><code>&lt;table&gt;
  &lt;thead&gt;
    &lt;tr&gt;
      &lt;th rowspan="2"&gt;Student&lt;/th&gt;        &lt;!-- Spans 2 rows vertically --&gt;
      &lt;th colspan="2"&gt;Scores&lt;/th&gt;         &lt;!-- Spans 2 columns horizontally --&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;th&gt;Midterm&lt;/th&gt;
      &lt;th&gt;Final&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;
  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;td&gt;Fatima Ouedraogo&lt;/td&gt;
      &lt;td&gt;78%&lt;/td&gt;
      &lt;td&gt;85%&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;
&lt;/table&gt;</code></pre>

<h3>Making Tables Accessible</h3>
<p>For screen reader users, tables need extra information to make sense:</p>
<pre><code>&lt;table&gt;
  &lt;!-- Caption provides a title for the whole table --&gt;
  &lt;caption&gt;Student Quiz Scores — HTML Course, April 2025&lt;/caption&gt;

  &lt;thead&gt;
    &lt;tr&gt;
      &lt;!-- scope tells screen readers what the header applies to --&gt;
      &lt;th scope="col"&gt;Student Name&lt;/th&gt;
      &lt;th scope="col"&gt;Score&lt;/th&gt;
      &lt;th scope="col"&gt;Passed&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;
  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;th scope="row"&gt;Amara Diallo&lt;/th&gt;  &lt;!-- Row header --&gt;
      &lt;td&gt;92%&lt;/td&gt;
      &lt;td&gt;Yes&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;
&lt;/table&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using tables for layout</strong> — Never. Use CSS Flexbox or Grid for page layout.</li>
  <li><strong>Forgetting <code>&lt;thead&gt;</code> and <code>&lt;tbody&gt;</code></strong> — Browsers will add them invisibly, but including them explicitly makes your HTML clearer and enables better CSS targeting.</li>
  <li><strong>Forgetting the <code>scope</code> attribute on <code>&lt;th&gt;</code></strong> — Without it, screen readers cannot associate headers with their data cells.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a table showing a weekly class schedule: Monday–Friday as columns, morning/afternoon as rows. Merge the lunch-break cell across all 5 days using <code>colspan="5"</code>. Add a <code>&lt;caption&gt;</code> and use <code>scope</code> on all header cells.</div>
HTML,4]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[10,2,'HTML5 Audio, Video & Embeds',<<<'HTML'
<h2>HTML5 Audio, Video &amp; Embeds</h2>

<p>Before HTML5, adding video or audio to a web page required third-party plugins like Adobe Flash. Today, browsers natively understand multimedia — you can embed video and audio directly in HTML with just a few lines of code.</p>

<h3>The &lt;video&gt; Element</h3>
<pre><code>&lt;video width="800" height="450" controls&gt;
  &lt;!-- Provide multiple formats for browser compatibility --&gt;
  &lt;source src="intro.mp4" type="video/mp4"&gt;
  &lt;source src="intro.webm" type="video/webm"&gt;

  &lt;!-- Fallback text for very old browsers --&gt;
  &lt;p&gt;Your browser doesn't support video. &lt;a href="intro.mp4"&gt;Download it&lt;/a&gt;.&lt;/p&gt;
&lt;/video&gt;</code></pre>

<h4>Video Attributes</h4>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Attribute</th><th>What It Does</th></tr></thead>
  <tbody>
    <tr><td><code>controls</code></td><td>Shows play/pause, volume, and fullscreen buttons</td></tr>
    <tr><td><code>autoplay</code></td><td>Starts playing automatically (use sparingly — annoying!)</td></tr>
    <tr><td><code>muted</code></td><td>Starts muted (required for autoplay to work in most browsers)</td></tr>
    <tr><td><code>loop</code></td><td>Repeats the video when it ends</td></tr>
    <tr><td><code>poster="preview.jpg"</code></td><td>Image displayed before the video plays</td></tr>
    <tr><td><code>preload="metadata"</code></td><td>Load only duration/dimensions info, not the full video</td></tr>
  </tbody>
</table>

<h3>The &lt;audio&gt; Element</h3>
<pre><code>&lt;audio controls&gt;
  &lt;source src="podcast.mp3" type="audio/mpeg"&gt;
  &lt;source src="podcast.ogg" type="audio/ogg"&gt;
  &lt;p&gt;Your browser doesn't support audio. &lt;a href="podcast.mp3"&gt;Download&lt;/a&gt;.&lt;/p&gt;
&lt;/audio&gt;</code></pre>
<p>The <code>&lt;audio&gt;</code> element works exactly like <code>&lt;video&gt;</code> but without the visual display area. It supports the same attributes: <code>controls</code>, <code>autoplay</code>, <code>muted</code>, <code>loop</code>.</p>

<h3>Embedding YouTube Videos</h3>
<p>The most common way to embed a YouTube video is using an <code>&lt;iframe&gt;</code>. YouTube generates this code for you — click "Share" → "Embed":</p>
<pre><code>&lt;!-- Responsive YouTube embed --&gt;
&lt;div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;"&gt;
  &lt;iframe
    src="https://www.youtube.com/embed/VIDEO_ID"
    title="Introduction to HTML"
    width="560"
    height="315"
    style="position:absolute; top:0; left:0; width:100%; height:100%;"
    allowfullscreen
    loading="lazy"&gt;
  &lt;/iframe&gt;
&lt;/div&gt;</code></pre>
<p>The outer <code>&lt;div&gt;</code> with <code>padding-bottom: 56.25%</code> creates a 16:9 aspect ratio container that scales responsively on any screen size. This is a very common pattern.</p>

<h3>The &lt;iframe&gt; Element</h3>
<p><code>&lt;iframe&gt;</code> (inline frame) embeds another web page or document inside your page. You can embed Google Maps, forms, documents, and more:</p>
<pre><code>&lt;!-- Embed a Google Map --&gt;
&lt;iframe
  src="https://www.google.com/maps/embed?pb=..."
  width="600"
  height="450"
  loading="lazy"
  title="Office location map"
  allowfullscreen&gt;
&lt;/iframe&gt;</code></pre>
<p>Always add a <code>title</code> attribute to iframes — screen readers use it to describe what is embedded.</p>

<h3>Video Best Practices for African Audiences</h3>
<p>In many parts of Africa, mobile data is expensive and connections can be slow. When using video:</p>
<ul>
  <li><strong>Compress your videos</strong> — use tools like HandBrake to reduce file size before uploading.</li>
  <li><strong>Use YouTube or Vimeo</strong> — they compress and serve video efficiently, saving your bandwidth.</li>
  <li><strong>Add a poster image</strong> — users see a preview without needing to download any video data.</li>
  <li><strong>Provide a download link</strong> — some users prefer to download and watch offline.</li>
  <li><strong>Add subtitles with <code>&lt;track&gt;</code></strong> — helps users in noisy environments and non-native speakers.</li>
</ul>
<pre><code>&lt;video controls poster="lesson-preview.jpg" preload="metadata"&gt;
  &lt;source src="lesson.mp4" type="video/mp4"&gt;
  &lt;track kind="subtitles" src="subtitles-en.vtt" srclang="en" label="English" default&gt;
&lt;/video&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using <code>autoplay</code> without <code>muted</code></strong> — Most browsers block autoplaying videos with sound. Always pair <code>autoplay</code> with <code>muted</code>.</li>
  <li><strong>Forgetting a fallback</strong> — Always put fallback text inside <code>&lt;video&gt;</code> and <code>&lt;audio&gt;</code> for old browsers.</li>
  <li><strong>Not adding <code>title</code> to iframes</strong> — Screen readers need it to describe the embedded content.</li>
  <li><strong>Hosting large video files yourself</strong> — Use a video hosting service instead to save bandwidth and loading time.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Find a YouTube video you like. Get its embed code (Share → Embed). Add it to your page inside the responsive 16:9 wrapper shown above. Then find an audio file online and add it using the <code>&lt;audio&gt;</code> element with controls.</div>
HTML,5]);

echo "  [✓] HTML Module 2 lessons\n";

// ── HTML Module 3 ─────────────────────────────────────────
$ins('INSERT INTO modules(id,course_id,title,description,order_index)VALUES(?,?,?,?,?)',
    [3,1,'Forms & User Input',
     'Learn how to collect information from users with HTML forms — the foundation of login pages, sign-up flows, search bars, and every interactive website.',3]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[11,3,'Form Basics: action, method & labels',<<<'HTML'
<h2>Form Basics: action, method &amp; labels</h2>

<p>Forms are how websites collect information from users. Every time you log in, search for something, fill out a contact form, or make a purchase online — you are using an HTML form. This lesson covers the fundamental structure every form must have.</p>

<h3>Anatomy of a Form</h3>
<pre><code>&lt;form action="/submit" method="POST"&gt;
  &lt;label for="name"&gt;Your Name&lt;/label&gt;
  &lt;input type="text" id="name" name="name" placeholder="e.g. Kwame Asante"&gt;

  &lt;button type="submit"&gt;Send&lt;/button&gt;
&lt;/form&gt;</code></pre>

<p>Let's break this down piece by piece:</p>

<h3>The &lt;form&gt; Element</h3>
<p>The <code>&lt;form&gt;</code> tag wraps all the inputs. It has two key attributes:</p>

<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Attribute</th><th>Purpose</th><th>Values</th></tr></thead>
  <tbody>
    <tr><td><code>action</code></td><td>Where to send the form data when submitted</td><td>A URL: <code>"/submit"</code>, <code>"https://example.com/form"</code></td></tr>
    <tr><td><code>method</code></td><td>How to send the data</td><td><code>GET</code> or <code>POST</code></td></tr>
  </tbody>
</table>

<h4>GET vs POST — Which to Use?</h4>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th></th><th>GET</th><th>POST</th></tr></thead>
  <tbody>
    <tr><td><strong>How data travels</strong></td><td>In the URL: <code>?name=Kwame&amp;city=Accra</code></td><td>In the request body (hidden)</td></tr>
    <tr><td><strong>Visible to user</strong></td><td>Yes — in the address bar</td><td>No</td></tr>
    <tr><td><strong>Bookmarkable</strong></td><td>Yes</td><td>No</td></tr>
    <tr><td><strong>Use for</strong></td><td>Search queries, filters</td><td>Login, registration, payment — anything sensitive</td></tr>
    <tr><td><strong>Data limit</strong></td><td>~2000 characters</td><td>No practical limit</td></tr>
  </tbody>
</table>

<h3>Labels — The Most Important Accessibility Feature in Forms</h3>
<p>Every input field <strong>must</strong> have a label. The label describes what the field is for. Sighted users see it visually; screen reader users hear it read aloud.</p>

<pre><code>&lt;!-- Method 1: Link label to input with matching for/id --&gt;
&lt;label for="email"&gt;Email Address&lt;/label&gt;
&lt;input type="email" id="email" name="email"&gt;

&lt;!-- Method 2: Wrap the input inside the label --&gt;
&lt;label&gt;
  Email Address
  &lt;input type="email" name="email"&gt;
&lt;/label&gt;</code></pre>

<p>Method 1 is generally preferred. The <code>for</code> attribute on the label must exactly match the <code>id</code> on the input. This also means <strong>clicking the label text focuses the input</strong> — this is a usability win especially on mobile.</p>

<h3>The name Attribute — Critical for Form Submission</h3>
<p>The <code>name</code> attribute is what the server uses to identify each field's value. Without it, the field's data is not submitted at all.</p>
<pre><code>&lt;!-- This field's value is sent as "email=user@example.com" --&gt;
&lt;input type="email" name="email" id="email"&gt;

&lt;!-- This field is NOT submitted — missing name attribute --&gt;
&lt;input type="email" id="email"&gt;</code></pre>

<h3>A Complete Login Form</h3>
<pre><code>&lt;form action="/login" method="POST"&gt;

  &lt;div&gt;
    &lt;label for="email"&gt;Email Address&lt;/label&gt;
    &lt;input type="email"
           id="email"
           name="email"
           required
           placeholder="you@example.com"
           autocomplete="email"&gt;
  &lt;/div&gt;

  &lt;div&gt;
    &lt;label for="password"&gt;Password&lt;/label&gt;
    &lt;input type="password"
           id="password"
           name="password"
           required
           minlength="8"
           autocomplete="current-password"&gt;
  &lt;/div&gt;

  &lt;button type="submit"&gt;Log In&lt;/button&gt;

  &lt;p&gt;&lt;a href="/forgot-password"&gt;Forgot your password?&lt;/a&gt;&lt;/p&gt;

&lt;/form&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Missing <code>name</code> on inputs</strong> — Data will not be sent to the server. The <code>id</code> is for CSS/JS; <code>name</code> is for form submission.</li>
  <li><strong>Missing labels</strong> — Using placeholder text instead of a label is a common mistake. Placeholder text disappears when the user starts typing, leaving them confused about what goes in the field.</li>
  <li><strong>Using GET for sensitive data</strong> — Passwords and personal data would appear in the URL and in server logs. Always use POST for sensitive forms.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>Use <code>method="POST"</code> for sensitive data; <code>method="GET"</code> for search/filter forms.</li>
  <li>Every input needs both a <code>name</code> attribute (for server submission) and a matching <code>label</code> (for accessibility).</li>
  <li>Link labels to inputs using matching <code>for</code> and <code>id</code> values.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a "Contact Us" form with fields for: Full Name, Email Address, Subject, and a Message textarea. Use <code>method="POST"</code> and link every input to a <code>&lt;label&gt;</code>. Add a submit button that says "Send Message".</div>
HTML,1]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[12,3,'Input Types: text, email, number, date, file & more',<<<'HTML'
<h2>Input Types: text, email, number, date, file &amp; more</h2>

<p>The <code>&lt;input&gt;</code> element is the most versatile element in HTML. By changing its <code>type</code> attribute, you get completely different controls — text boxes, date pickers, colour pickers, file uploads, and more. Using the right type also gives you free validation and better keyboard layouts on mobile.</p>

<h3>Why Input Type Matters</h3>
<p>When you use <code>type="email"</code> on a mobile device, the keyboard automatically switches to an email layout (showing <code>@</code> and <code>.</code> prominently). When you use <code>type="number"</code>, the numeric keypad appears. When you use <code>type="date"</code>, a date picker pops up — no JavaScript required!</p>

<h3>The Full Input Type Reference</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Type</th><th>What It Collects</th><th>Special Behaviour</th></tr></thead>
  <tbody>
    <tr><td><code>text</code></td><td>Any text</td><td>Default type — used when nothing else fits</td></tr>
    <tr><td><code>email</code></td><td>Email address</td><td>Validates format; shows email keyboard on mobile</td></tr>
    <tr><td><code>password</code></td><td>Password</td><td>Hides characters; enables password manager support</td></tr>
    <tr><td><code>number</code></td><td>Numbers only</td><td>Shows numeric keyboard; supports min, max, step</td></tr>
    <tr><td><code>tel</code></td><td>Phone number</td><td>Shows phone keyboard on mobile; no format validation</td></tr>
    <tr><td><code>url</code></td><td>Web address</td><td>Validates URL format</td></tr>
    <tr><td><code>search</code></td><td>Search query</td><td>May show clear button; search semantics for accessibility</td></tr>
    <tr><td><code>date</code></td><td>A date</td><td>Shows a date picker; value is YYYY-MM-DD</td></tr>
    <tr><td><code>time</code></td><td>A time</td><td>Shows a time picker; value is HH:MM</td></tr>
    <tr><td><code>datetime-local</code></td><td>Date and time</td><td>Date + time picker in one</td></tr>
    <tr><td><code>color</code></td><td>A colour</td><td>Shows a colour picker; value is #rrggbb</td></tr>
    <tr><td><code>range</code></td><td>A number in a range</td><td>Shows a slider</td></tr>
    <tr><td><code>file</code></td><td>File upload</td><td>Opens file picker; use multiple for multiple files</td></tr>
    <tr><td><code>checkbox</code></td><td>True/false toggle</td><td>For yes/no options and multi-select lists</td></tr>
    <tr><td><code>radio</code></td><td>One choice from a group</td><td>Only one can be selected per group</td></tr>
    <tr><td><code>hidden</code></td><td>Data not shown to user</td><td>Submits extra data (like CSRF tokens) invisibly</td></tr>
  </tbody>
</table>

<h3>Code Examples for Common Types</h3>

<h4>Text Inputs</h4>
<pre><code>&lt;!-- Standard text --&gt;
&lt;label for="city"&gt;City&lt;/label&gt;
&lt;input type="text" id="city" name="city" placeholder="e.g. Accra"&gt;

&lt;!-- Email -- validates format automatically --&gt;
&lt;label for="email"&gt;Email&lt;/label&gt;
&lt;input type="email" id="email" name="email" autocomplete="email"&gt;

&lt;!-- Phone -- no validation, but mobile keyboard appears --&gt;
&lt;label for="phone"&gt;Phone Number&lt;/label&gt;
&lt;input type="tel" id="phone" name="phone" placeholder="+233 20 000 0000"&gt;</code></pre>

<h4>Number and Range</h4>
<pre><code>&lt;!-- Number with constraints --&gt;
&lt;label for="age"&gt;Your Age&lt;/label&gt;
&lt;input type="number" id="age" name="age" min="16" max="100" step="1"&gt;

&lt;!-- Slider for a rating --&gt;
&lt;label for="rating"&gt;Rating: &lt;span id="rating-value"&gt;5&lt;/span&gt;/10&lt;/label&gt;
&lt;input type="range" id="rating" name="rating" min="1" max="10" value="5"&gt;</code></pre>

<h4>Date and Time</h4>
<pre><code>&lt;!-- Date picker --&gt;
&lt;label for="dob"&gt;Date of Birth&lt;/label&gt;
&lt;input type="date" id="dob" name="dob" min="1990-01-01" max="2010-12-31"&gt;

&lt;!-- Booking time --&gt;
&lt;label for="time"&gt;Preferred Time&lt;/label&gt;
&lt;input type="time" id="time" name="time" min="09:00" max="17:00"&gt;</code></pre>

<h4>File Upload</h4>
<pre><code>&lt;!-- Single file --&gt;
&lt;label for="cv"&gt;Upload your CV&lt;/label&gt;
&lt;input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx"&gt;

&lt;!-- Multiple image files --&gt;
&lt;label for="photos"&gt;Upload photos&lt;/label&gt;
&lt;input type="file" id="photos" name="photos" multiple accept="image/*"&gt;</code></pre>

<h4>Checkbox and Radio</h4>
<pre><code>&lt;!-- Checkbox -- for yes/no choices --&gt;
&lt;label&gt;
  &lt;input type="checkbox" name="agree" value="yes" required&gt;
  I agree to the Terms and Conditions
&lt;/label&gt;

&lt;!-- Radio buttons -- same name = same group (only one can be selected) --&gt;
&lt;fieldset&gt;
  &lt;legend&gt;Experience Level&lt;/legend&gt;

  &lt;label&gt;
    &lt;input type="radio" name="experience" value="beginner"&gt; Beginner
  &lt;/label&gt;
  &lt;label&gt;
    &lt;input type="radio" name="experience" value="intermediate"&gt; Intermediate
  &lt;/label&gt;
  &lt;label&gt;
    &lt;input type="radio" name="experience" value="advanced"&gt; Advanced
  &lt;/label&gt;
&lt;/fieldset&gt;</code></pre>

<p>Notice the <code>&lt;fieldset&gt;</code> and <code>&lt;legend&gt;</code> elements. Use them to group related inputs — the legend acts as a label for the entire group, which is essential for accessibility.</p>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using <code>type="text"</code> for everything</strong> — You lose free validation, better mobile keyboards, and browser autocomplete features.</li>
  <li><strong>Forgetting <code>accept</code> on file inputs</strong> — Without it, users can upload any file type. Specify what you expect: <code>accept=".pdf"</code> or <code>accept="image/*"</code>.</li>
  <li><strong>Radio buttons with different <code>name</code> values</strong> — Radio buttons only work as a group when they share the same <code>name</code> attribute.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a "Sign Up for the Hackathon" form. Include: Full Name (text), Email (email), Phone (tel), Date of Birth (date), Experience Level (radio: Beginner/Intermediate/Advanced), Upload CV (file, PDF only), and a checkbox agreeing to terms. Group the radio buttons in a <code>&lt;fieldset&gt;</code>.</div>
HTML,2]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[13,3,'Select, Textarea & Fieldset',<<<'HTML'
<h2>Select, Textarea &amp; Fieldset</h2>

<p>Not every form field is a simple text box. In this lesson you will learn three more powerful form elements: the dropdown select menu, the multi-line textarea, and the fieldset for grouping related fields.</p>

<h3>The &lt;select&gt; Dropdown Menu</h3>
<p>A dropdown lets users pick one option from a list. It is ideal when there are too many choices for radio buttons (more than 5–6 options).</p>

<pre><code>&lt;label for="country"&gt;Country&lt;/label&gt;
&lt;select id="country" name="country" required&gt;

  &lt;!-- A disabled placeholder option --&gt;
  &lt;option value="" disabled selected&gt;Select your country…&lt;/option&gt;

  &lt;!-- Regular options --&gt;
  &lt;option value="GH"&gt;Ghana&lt;/option&gt;
  &lt;option value="NG"&gt;Nigeria&lt;/option&gt;
  &lt;option value="KE"&gt;Kenya&lt;/option&gt;
  &lt;option value="ZA"&gt;South Africa&lt;/option&gt;
  &lt;option value="ET"&gt;Ethiopia&lt;/option&gt;

&lt;/select&gt;</code></pre>

<p>Key points:</p>
<ul>
  <li>The <code>value</code> attribute is what gets submitted to the server.</li>
  <li>The text between <code>&lt;option&gt;</code> tags is what the user sees.</li>
  <li>The first option with <code>disabled selected</code> and an empty value acts as a placeholder.</li>
</ul>

<h4>Grouping Options with &lt;optgroup&gt;</h4>
<pre><code>&lt;select name="course"&gt;
  &lt;option value=""&gt;Choose a course…&lt;/option&gt;

  &lt;optgroup label="Web Development"&gt;
    &lt;option value="html"&gt;HTML Fundamentals&lt;/option&gt;
    &lt;option value="css"&gt;CSS Layouts&lt;/option&gt;
    &lt;option value="js"&gt;JavaScript Basics&lt;/option&gt;
  &lt;/optgroup&gt;

  &lt;optgroup label="Data Science"&gt;
    &lt;option value="python"&gt;Python for Data&lt;/option&gt;
    &lt;option value="sql"&gt;SQL Fundamentals&lt;/option&gt;
  &lt;/optgroup&gt;
&lt;/select&gt;</code></pre>

<h4>Multi-Select</h4>
<pre><code>&lt;!-- Add the "multiple" attribute to allow multiple selections --&gt;
&lt;!-- Hold Ctrl/Cmd to select multiple items --&gt;
&lt;label for="skills"&gt;Skills (hold Ctrl to select multiple)&lt;/label&gt;
&lt;select id="skills" name="skills" multiple size="5"&gt;
  &lt;option value="html"&gt;HTML&lt;/option&gt;
  &lt;option value="css"&gt;CSS&lt;/option&gt;
  &lt;option value="js"&gt;JavaScript&lt;/option&gt;
  &lt;option value="python"&gt;Python&lt;/option&gt;
  &lt;option value="sql"&gt;SQL&lt;/option&gt;
&lt;/select&gt;</code></pre>

<h3>The &lt;textarea&gt; Element</h3>
<p>Use a textarea for long-form text — like a message, a bio, or a description. Unlike <code>&lt;input&gt;</code>, it has a closing tag and can contain default text.</p>

<pre><code>&lt;label for="bio"&gt;Tell us about yourself&lt;/label&gt;
&lt;textarea
  id="bio"
  name="bio"
  rows="5"
  cols="50"
  minlength="20"
  maxlength="500"
  placeholder="I am a software developer from Nairobi with 2 years of experience..."&gt;
&lt;/textarea&gt;

&lt;!-- To pre-fill with text, put it between the tags (no spaces before/after!) --&gt;
&lt;textarea name="message"&gt;Hello, I would like to enquire about...&lt;/textarea&gt;</code></pre>

<p>Attributes:</p>
<ul>
  <li><code>rows</code> — The visible height (in lines of text)</li>
  <li><code>cols</code> — The visible width (in characters) — usually controlled with CSS instead</li>
  <li><code>maxlength</code> — Maximum characters allowed</li>
  <li><code>minlength</code> — Minimum characters required</li>
</ul>

<h3>The &lt;fieldset&gt; and &lt;legend&gt; Elements</h3>
<p>Use <code>&lt;fieldset&gt;</code> to group related form controls, and <code>&lt;legend&gt;</code> to label the group. This is especially important for radio buttons and checkboxes.</p>

<pre><code>&lt;form action="/register" method="POST"&gt;

  &lt;fieldset&gt;
    &lt;legend&gt;Personal Information&lt;/legend&gt;

    &lt;label for="fname"&gt;First Name&lt;/label&gt;
    &lt;input type="text" id="fname" name="first_name" required&gt;

    &lt;label for="lname"&gt;Last Name&lt;/label&gt;
    &lt;input type="text" id="lname" name="last_name" required&gt;

    &lt;label for="email"&gt;Email&lt;/label&gt;
    &lt;input type="email" id="email" name="email" required&gt;
  &lt;/fieldset&gt;

  &lt;fieldset&gt;
    &lt;legend&gt;Background&lt;/legend&gt;

    &lt;label for="country"&gt;Country&lt;/label&gt;
    &lt;select id="country" name="country"&gt;
      &lt;option value=""&gt;Select country…&lt;/option&gt;
      &lt;option value="GH"&gt;Ghana&lt;/option&gt;
      &lt;option value="NG"&gt;Nigeria&lt;/option&gt;
    &lt;/select&gt;

    &lt;label for="bio"&gt;Short Bio&lt;/label&gt;
    &lt;textarea id="bio" name="bio" rows="4" maxlength="300"&gt;&lt;/textarea&gt;
  &lt;/fieldset&gt;

  &lt;button type="submit"&gt;Register&lt;/button&gt;

&lt;/form&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Putting default text as a value attribute on textarea</strong> — Unlike <code>&lt;input&gt;</code>, textarea default text goes between the tags, not in a <code>value</code> attribute.</li>
  <li><strong>Using dropdown for binary choices</strong> — For Yes/No, use a checkbox or two radio buttons instead of a select dropdown.</li>
  <li><strong>Forgetting to size textareas with CSS</strong> — The <code>cols</code> attribute is unreliable; use CSS <code>width</code> instead.</li>
  <li><strong>Not using <code>&lt;fieldset&gt;</code> for radio/checkbox groups</strong> — Without it, screen reader users hear the question and then isolated options with no context.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a detailed registration form split into two fieldsets: "Account Details" (name, email, password) and "Your Profile" (country dropdown with 5 African countries, experience level as radio buttons, and a bio textarea with a 300-character limit).</div>
HTML,3]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[14,3,'HTML Form Validation',<<<'HTML'
<h2>HTML Form Validation</h2>

<p>Before HTML5, you had to write JavaScript to validate form inputs. Now, browsers do much of this work for you with built-in HTML validation attributes. This lesson covers how to use them effectively.</p>

<h3>Why Validate on the Frontend?</h3>
<p>Form validation serves two purposes:</p>
<ol>
  <li><strong>User Experience</strong> — Catch mistakes immediately, before the user submits the form, with helpful messages.</li>
  <li><strong>Data Quality</strong> — Prevent empty fields, wrong formats, and out-of-range values.</li>
</ol>
<p class="alert alert-warning mt-2"><strong>Critical Note:</strong> HTML validation can be bypassed — a user can disable JavaScript or directly send HTTP requests. ALWAYS validate again on the server. HTML validation is for user experience; server validation is for security.</p>

<h3>The Validation Attributes</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Attribute</th><th>Works On</th><th>What It Does</th></tr></thead>
  <tbody>
    <tr><td><code>required</code></td><td>Most inputs</td><td>Field must not be empty before submitting</td></tr>
    <tr><td><code>minlength</code></td><td>text, email, password, textarea</td><td>Minimum number of characters</td></tr>
    <tr><td><code>maxlength</code></td><td>text, email, password, textarea</td><td>Maximum number of characters</td></tr>
    <tr><td><code>min</code></td><td>number, date, time, range</td><td>Minimum value</td></tr>
    <tr><td><code>max</code></td><td>number, date, time, range</td><td>Maximum value</td></tr>
    <tr><td><code>pattern</code></td><td>text, email, tel, url</td><td>Must match a regular expression</td></tr>
    <tr><td><code>type="email"</code></td><td>input</td><td>Must contain @ and a domain</td></tr>
    <tr><td><code>type="url"</code></td><td>input</td><td>Must be a valid URL format</td></tr>
    <tr><td><code>type="number"</code></td><td>input</td><td>Must be a number</td></tr>
  </tbody>
</table>

<h3>Practical Examples</h3>

<h4>Required Fields</h4>
<pre><code>&lt;label for="name"&gt;Full Name *&lt;/label&gt;
&lt;input type="text" id="name" name="name" required&gt;

&lt;!-- The browser shows an error and prevents submission if this is empty --&gt;</code></pre>

<h4>Length Constraints</h4>
<pre><code>&lt;label for="username"&gt;Username (3–20 characters)&lt;/label&gt;
&lt;input type="text"
       id="username"
       name="username"
       minlength="3"
       maxlength="20"
       required&gt;

&lt;label for="bio"&gt;Bio (max 500 characters)&lt;/label&gt;
&lt;textarea id="bio" name="bio" maxlength="500"&gt;&lt;/textarea&gt;
&lt;small&gt;Max 500 characters.&lt;/small&gt;</code></pre>

<h4>Number Ranges</h4>
<pre><code>&lt;label for="age"&gt;Age (must be 18 or older)&lt;/label&gt;
&lt;input type="number" id="age" name="age" min="18" max="120" required&gt;

&lt;label for="score"&gt;Test Score (0–100)&lt;/label&gt;
&lt;input type="number" id="score" name="score" min="0" max="100" step="1"&gt;</code></pre>

<h4>Pattern Validation</h4>
<p>The <code>pattern</code> attribute uses a <strong>regular expression</strong> — a pattern-matching language. Here are some common ones you can copy:</p>
<pre><code>&lt;!-- Ghana phone: starts with 0, then 9 more digits --&gt;
&lt;input type="tel" name="phone"
       pattern="0[0-9]{9}"
       title="Phone must be 10 digits starting with 0"&gt;

&lt;!-- Alphanumeric username: only letters and numbers --&gt;
&lt;input type="text" name="username"
       pattern="[A-Za-z0-9]+"
       title="Username can only contain letters and numbers"&gt;

&lt;!-- Password: at least 8 characters, one uppercase, one number --&gt;
&lt;input type="password" name="password"
       pattern="(?=.*[A-Z])(?=.*[0-9]).{8,}"
       title="Password needs 8+ characters, one uppercase letter, one number"&gt;</code></pre>
<p>The <code>title</code> attribute shows as a tooltip and in the browser's error message — always describe what format is expected.</p>

<h4>Disabling Browser Validation (When Using Custom JS)</h4>
<pre><code>&lt;!-- novalidate disables browser validation so you can do it in JavaScript --&gt;
&lt;form action="/submit" method="POST" novalidate&gt;
  ...
&lt;/form&gt;</code></pre>

<h3>A Well-Validated Registration Form</h3>
<pre><code>&lt;form action="/register" method="POST"&gt;

  &lt;label for="name"&gt;Full Name *&lt;/label&gt;
  &lt;input type="text" id="name" name="name"
         minlength="2" maxlength="100" required
         placeholder="e.g. Amara Diallo"&gt;

  &lt;label for="email"&gt;Email Address *&lt;/label&gt;
  &lt;input type="email" id="email" name="email"
         required autocomplete="email"&gt;

  &lt;label for="password"&gt;Password * (min 8 characters)&lt;/label&gt;
  &lt;input type="password" id="password" name="password"
         minlength="8" required autocomplete="new-password"&gt;

  &lt;label for="age"&gt;Age *&lt;/label&gt;
  &lt;input type="number" id="age" name="age"
         min="16" max="80" required&gt;

  &lt;label for="phone"&gt;Phone Number&lt;/label&gt;
  &lt;input type="tel" id="phone" name="phone"
         pattern="[0-9+\s\-]{7,15}"
         title="Enter a valid phone number"&gt;

  &lt;label&gt;
    &lt;input type="checkbox" name="agree" required&gt;
    I agree to the &lt;a href="/terms"&gt;Terms and Conditions&lt;/a&gt; *
  &lt;/label&gt;

  &lt;button type="submit"&gt;Create Account&lt;/button&gt;

&lt;/form&gt;</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Relying only on HTML validation</strong> — Always validate on the server too. HTML validation is just for user experience.</li>
  <li><strong>Forgetting the <code>title</code> attribute with <code>pattern</code></strong> — Without it, browsers show a generic unhelpful error. Describe the expected format.</li>
  <li><strong>Setting <code>maxlength</code> but not validating server-side</strong> — <code>maxlength</code> can be removed in browser dev tools. The server must also check.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Take your contact form from the previous lesson and add validation: make Name (min 2 chars), Email, and Message (min 20 chars) all required. Add a phone field with a pattern for 10-digit numbers. Add a checkbox agreeing to be contacted. Try submitting the form with empty fields and watch the browser validation in action.</div>
HTML,4]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[15,3,'Accessible Forms with ARIA',<<<'HTML'
<h2>Accessible Forms with ARIA</h2>

<p>An accessible form works for everyone — including people who are blind, have low vision, or use only a keyboard. In this lesson you will learn the accessibility principles that make forms inclusive, and the ARIA attributes that fill gaps where HTML alone is not enough.</p>

<h3>Why Accessibility in Forms Matters</h3>
<p>Forms are where users take actions — signing up, logging in, making purchases. If your form is not accessible, you are excluding users and, in many countries, violating the law. The good news: accessible forms are also better for all users.</p>

<h3>The Foundation: Proper Labels</h3>
<p>This was covered in a previous lesson, but it bears repeating — it is the most important rule:</p>
<pre><code>&lt;!-- ✓ Correct: Label linked to input --&gt;
&lt;label for="email"&gt;Email Address&lt;/label&gt;
&lt;input type="email" id="email" name="email"&gt;

&lt;!-- ✗ Wrong: No label at all --&gt;
&lt;input type="email" name="email" placeholder="Email"&gt;

&lt;!-- ✗ Wrong: Label not linked --&gt;
&lt;label&gt;Email Address&lt;/label&gt;
&lt;input type="email" name="email"&gt;</code></pre>

<h3>ARIA Attributes for Forms</h3>
<p><strong>ARIA</strong> stands for Accessible Rich Internet Applications. ARIA attributes add semantic information for screen readers when HTML alone is insufficient.</p>

<h4>aria-describedby — Linking Helper Text to an Input</h4>
<pre><code>&lt;label for="password"&gt;Password&lt;/label&gt;
&lt;input type="password"
       id="password"
       name="password"
       aria-describedby="password-help"
       minlength="8"
       required&gt;
&lt;p id="password-help"&gt;Must be at least 8 characters and include a number.&lt;/p&gt;

&lt;!-- Screen reader will announce: "Password. Required. Must be at least 8 characters and include a number." --&gt;</code></pre>

<h4>aria-required — For Cases Where required Isn't Enough</h4>
<pre><code>&lt;!-- Standard HTML required is usually sufficient --&gt;
&lt;input type="email" required aria-required="true"&gt;

&lt;!-- Use aria-required when creating custom controls --&gt;
&lt;div role="combobox" aria-required="true"&gt;...&lt;/div&gt;</code></pre>

<h4>aria-invalid — Marking Validation Errors</h4>
<pre><code>&lt;!-- Use JavaScript to set aria-invalid when a field fails validation --&gt;
&lt;label for="email"&gt;Email Address&lt;/label&gt;
&lt;input type="email"
       id="email"
       name="email"
       aria-invalid="true"
       aria-describedby="email-error"&gt;
&lt;p id="email-error" role="alert"&gt;Please enter a valid email address.&lt;/p&gt;

&lt;!-- role="alert" makes the error message announced immediately to screen readers --&gt;</code></pre>

<h4>Visually Hidden Labels (When You Must Hide Them)</h4>
<pre><code>&lt;!-- Sometimes design requires no visible label (e.g., search bars) --&gt;
&lt;!-- Use a visually-hidden class to hide it visually but keep it for screen readers --&gt;

&lt;!-- In your CSS: --&gt;
&lt;!-- .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); } --&gt;

&lt;label for="search" class="sr-only"&gt;Search courses&lt;/label&gt;
&lt;input type="search" id="search" name="q" placeholder="Search courses..."&gt;

&lt;!-- Never use aria-label as a substitute for a real label when possible --&gt;</code></pre>

<h3>Keyboard Navigation</h3>
<p>All form controls must be operable by keyboard alone (Tab to move, Space/Enter to activate). Here are the rules:</p>
<ul>
  <li>Never remove focus outlines with <code>outline: none</code> without replacing them with a custom style. Keyboard users depend on the focus indicator to know where they are.</li>
  <li>The tab order follows the DOM order — keep your HTML structure logical.</li>
  <li>Use <code>tabindex="0"</code> to make non-focusable elements focusable, and <code>tabindex="-1"</code> to remove them from tab order.</li>
</ul>

<h3>Error Handling Best Practices</h3>
<pre><code>&lt;form&gt;
  &lt;div class="form-group"&gt;
    &lt;label for="name"&gt;
      Full Name
      &lt;span aria-hidden="true"&gt; *&lt;/span&gt;  &lt;!-- * is decorative; don't read it aloud --&gt;
    &lt;/label&gt;

    &lt;input type="text"
           id="name"
           name="name"
           required
           aria-required="true"
           aria-describedby="name-error"
           autocomplete="name"&gt;

    &lt;!-- Error shown only when validation fails (via JavaScript) --&gt;
    &lt;p id="name-error" class="error-message" role="alert" hidden&gt;
      Please enter your full name (at least 2 characters).
    &lt;/p&gt;
  &lt;/div&gt;
&lt;/form&gt;</code></pre>

<h3>A Checklist for Accessible Forms</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Requirement</th><th>How to Achieve It</th></tr></thead>
  <tbody>
    <tr><td>Every input has a label</td><td>Use <code>&lt;label for="id"&gt;</code></td></tr>
    <tr><td>Required fields are marked</td><td>Use <code>required</code> attribute + visual indicator</td></tr>
    <tr><td>Error messages are descriptive</td><td>"Enter a valid email" not "Invalid input"</td></tr>
    <tr><td>Error messages are linked to fields</td><td>Use <code>aria-describedby</code></td></tr>
    <tr><td>Related fields are grouped</td><td>Use <code>&lt;fieldset&gt;</code> + <code>&lt;legend&gt;</code></td></tr>
    <tr><td>Keyboard navigable</td><td>Never remove focus styles; keep logical tab order</td></tr>
    <tr><td>Colour is not the only indicator</td><td>Don't rely on red colour alone for errors — add icons or text</td></tr>
  </tbody>
</table>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using placeholder as a label</strong> — Placeholder text disappears when typing. Users with cognitive disabilities forget what the field was for.</li>
  <li><strong>Removing focus outlines</strong> — <code>outline: none</code> is one of the most harmful accessibility mistakes. Replace with a custom style, never remove entirely.</li>
  <li><strong>Only showing errors in red</strong> — About 8% of men have colour vision deficiency. Always add text or an icon alongside colour.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Review the form you built in the previous lesson. Add <code>aria-describedby</code> to link each input to a helper text paragraph. Mark required fields with an asterisk and a note at the top. Check that you can navigate all fields with just the Tab key.</div>
HTML,5]);

echo "  [✓] HTML Module 3 lessons\n";

// ── HTML Module 4 ─────────────────────────────────────────
$ins('INSERT INTO modules(id,course_id,title,description,order_index)VALUES(?,?,?,?,?)',
    [4,1,'HTML5 Features & Best Practices',
     'Explore powerful HTML5 features including data attributes, meta tags for SEO, and best practices that professional developers use.',4]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[16,4,'Meta Tags, SEO & Social Sharing',<<<'HTML'
<h2>Meta Tags, SEO &amp; Social Sharing</h2>

<p>The <code>&lt;head&gt;</code> section of your HTML contains meta tags — invisible information about your page that search engines, social media platforms, and browsers use. Getting these right can dramatically improve how your site appears in Google search results and when shared on social media.</p>

<h3>Essential Meta Tags</h3>
<pre><code>&lt;head&gt;
  &lt;!-- Always required --&gt;
  &lt;meta charset="UTF-8"&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;

  &lt;!-- Page title — appears in browser tab AND Google search results --&gt;
  &lt;title&gt;Learn HTML for Free | HackathonAfrica&lt;/title&gt;

  &lt;!-- Description — shown in Google search results (160 chars max) --&gt;
  &lt;meta name="description" content="Learn HTML from scratch in our free beginner-friendly course. Join 10,000+ African developers building their careers."&gt;

  &lt;!-- Tell search engines not to index a page (use for admin/login pages) --&gt;
  &lt;!-- &lt;meta name="robots" content="noindex, nofollow"&gt; --&gt;

  &lt;!-- Author information --&gt;
  &lt;meta name="author" content="HackathonAfrica Team"&gt;
&lt;/head&gt;</code></pre>

<h3>Open Graph Tags — For Social Media Sharing</h3>
<p>When someone shares your page on Facebook, Twitter, or WhatsApp, they get a "card" with an image, title, and description. The Open Graph protocol defines these. Without them, social platforms make up their own (usually badly).</p>
<pre><code>&lt;!-- Open Graph (Facebook, WhatsApp, LinkedIn, etc.) --&gt;
&lt;meta property="og:title" content="Free HTML Course | HackathonAfrica"&gt;
&lt;meta property="og:description" content="Learn HTML from scratch. Free. Beginner-friendly."&gt;
&lt;meta property="og:image" content="https://hackathon.africa/images/og-html-course.jpg"&gt;
&lt;meta property="og:url" content="https://hackathon.africa/courses/html"&gt;
&lt;meta property="og:type" content="website"&gt;

&lt;!-- Twitter Card --&gt;
&lt;meta name="twitter:card" content="summary_large_image"&gt;
&lt;meta name="twitter:title" content="Free HTML Course | HackathonAfrica"&gt;
&lt;meta name="twitter:description" content="Learn HTML from scratch. Free."&gt;
&lt;meta name="twitter:image" content="https://hackathon.africa/images/og-html-course.jpg"&gt;</code></pre>

<p class="alert alert-info mt-2"><strong>Tip:</strong> Test your Open Graph tags at <code>developers.facebook.com/tools/debug</code> or use the "opengraph.xyz" preview tool before publishing.</p>

<h3>The Favicon</h3>
<p>The small icon in the browser tab is the favicon. Modern practice is to use an SVG and a PNG fallback:</p>
<pre><code>&lt;!-- Modern approach --&gt;
&lt;link rel="icon" href="/favicon.svg" type="image/svg+xml"&gt;
&lt;link rel="icon" href="/favicon.png" type="image/png"&gt;

&lt;!-- iOS homescreen icon --&gt;
&lt;link rel="apple-touch-icon" href="/apple-touch-icon.png"&gt;</code></pre>

<h3>Canonical URLs — Preventing Duplicate Content</h3>
<pre><code>&lt;!-- If the same content appears at multiple URLs, tell Google which is "official" --&gt;
&lt;link rel="canonical" href="https://hackathon.africa/courses/html"&gt;</code></pre>

<h3>Title Tag Best Practices</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Rule</th><th>Example</th></tr></thead>
  <tbody>
    <tr><td>Keep it under 60 characters</td><td><code>Learn HTML Free | HackathonAfrica</code></td></tr>
    <tr><td>Put the most important keyword first</td><td><code>HTML Tutorial for Beginners | ...</code></td></tr>
    <tr><td>Include your brand at the end</td><td><code>... | HackathonAfrica</code></td></tr>
    <tr><td>Make every page's title unique</td><td>Never use the same title on two pages</td></tr>
    <tr><td>Be descriptive and honest</td><td>Don't clickbait — it increases bounce rate</td></tr>
  </tbody>
</table>

<h3>Complete Head Section Template</h3>
<pre><code>&lt;head&gt;
  &lt;meta charset="UTF-8"&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;

  &lt;title&gt;HTML Fundamentals Course | HackathonAfrica&lt;/title&gt;
  &lt;meta name="description" content="Learn HTML from zero to professional in our free course. 60 lessons, quizzes, and a certificate."&gt;

  &lt;link rel="canonical" href="https://hackathon.africa/courses/html"&gt;

  &lt;meta property="og:title" content="HTML Course | HackathonAfrica"&gt;
  &lt;meta property="og:description" content="Learn HTML from zero to professional."&gt;
  &lt;meta property="og:image" content="https://hackathon.africa/img/html-course-og.jpg"&gt;
  &lt;meta property="og:url" content="https://hackathon.africa/courses/html"&gt;
  &lt;meta property="og:type" content="website"&gt;

  &lt;meta name="twitter:card" content="summary_large_image"&gt;
  &lt;meta name="twitter:title" content="HTML Course | HackathonAfrica"&gt;
  &lt;meta name="twitter:description" content="Learn HTML from zero to professional."&gt;
  &lt;meta name="twitter:image" content="https://hackathon.africa/img/html-course-og.jpg"&gt;

  &lt;link rel="icon" href="/favicon.svg" type="image/svg+xml"&gt;
  &lt;link rel="stylesheet" href="/css/styles.css"&gt;
&lt;/head&gt;</code></pre>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Add a complete <code>&lt;head&gt;</code> section to your personal web page project. Include all essential meta tags, Open Graph tags (use a placeholder image URL), and a canonical URL. Use an online meta tag validator to check your work.</div>
HTML,1]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[17,4,'Data Attributes: data-*',<<<'HTML'
<h2>Data Attributes: data-*</h2>

<p>Sometimes you need to attach extra information to an HTML element — information that is not visible to the user but is useful for JavaScript to read. Data attributes let you store any custom data directly in your HTML.</p>

<h3>What Are Data Attributes?</h3>
<p>Data attributes follow a simple pattern: <code>data-</code> followed by any name you choose. They live in the HTML tag and can be read by JavaScript.</p>

<pre><code>&lt;!-- Storing product information on a card --&gt;
&lt;div class="product-card"
     data-product-id="42"
     data-price="25000"
     data-currency="GHS"
     data-in-stock="true"&gt;
  &lt;h3&gt;Web Development Course&lt;/h3&gt;
  &lt;p&gt;GHS 250.00&lt;/p&gt;
  &lt;button class="add-to-cart"&gt;Add to Cart&lt;/button&gt;
&lt;/div&gt;</code></pre>

<h3>Naming Rules</h3>
<ul>
  <li>Must start with <code>data-</code></li>
  <li>The name after <code>data-</code> must be lowercase</li>
  <li>Use hyphens for multi-word names: <code>data-product-id</code></li>
  <li>In JavaScript, hyphens are converted to camelCase: <code>data-product-id</code> → <code>dataset.productId</code></li>
</ul>

<h3>Reading Data Attributes with JavaScript</h3>
<pre><code>&lt;!-- HTML --&gt;
&lt;button data-user-id="101" data-action="delete" class="delete-btn"&gt;
  Delete User
&lt;/button&gt;

&lt;!-- JavaScript --&gt;
&lt;script&gt;
document.querySelector('.delete-btn').addEventListener('click', function() {
  // Access via dataset (camelCase conversion)
  const userId = this.dataset.userId;      // "101"
  const action = this.dataset.action;      // "delete"

  console.log(`Performing ${action} on user ${userId}`);
});
&lt;/script&gt;</code></pre>

<h3>Real-World Use Case: A Dynamic Course List</h3>
<pre><code>&lt;ul id="course-list"&gt;
  &lt;li data-course-id="1" data-difficulty="beginner" data-duration="4h"&gt;
    &lt;a href="/courses/html"&gt;HTML Fundamentals&lt;/a&gt;
  &lt;/li&gt;
  &lt;li data-course-id="2" data-difficulty="beginner" data-duration="5h"&gt;
    &lt;a href="/courses/css"&gt;CSS Layouts&lt;/a&gt;
  &lt;/li&gt;
  &lt;li data-course-id="3" data-difficulty="intermediate" data-duration="8h"&gt;
    &lt;a href="/courses/js"&gt;JavaScript Basics&lt;/a&gt;
  &lt;/li&gt;
&lt;/ul&gt;

&lt;!-- A filter button --&gt;
&lt;button data-filter="beginner"&gt;Show Beginner Courses&lt;/button&gt;</code></pre>

<p>With JavaScript, you can filter the list by reading <code>data-difficulty</code> on each item — no need to make a server request or store data elsewhere.</p>

<h3>CSS Can Read Data Attributes Too</h3>
<pre><code>&lt;!-- HTML --&gt;
&lt;span data-status="active"&gt;John Doe&lt;/span&gt;
&lt;span data-status="inactive"&gt;Jane Smith&lt;/span&gt;

&lt;!-- CSS --&gt;
&lt;style&gt;
[data-status="active"]   { color: green; }
[data-status="inactive"] { color: red; }
&lt;/style&gt;</code></pre>

<h3>When to Use Data Attributes</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Good Use Cases</th><th>Bad Use Cases</th></tr></thead>
  <tbody>
    <tr><td>IDs/keys for JavaScript to use</td><td>Storing data already in the DOM (text content)</td></tr>
    <tr><td>Configuration for JavaScript components</td><td>Storing large amounts of data (use JS variables)</td></tr>
    <tr><td>State for CSS attribute selectors</td><td>Sensitive data (data attributes are visible in HTML)</td></tr>
    <tr><td>Bridge between HTML and JavaScript</td><td>Replacing proper semantic HTML attributes</td></tr>
  </tbody>
</table>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using uppercase in attribute names</strong> — <code>data-productID</code> is invalid. Use lowercase: <code>data-product-id</code>.</li>
  <li><strong>Storing sensitive data</strong> — Data attributes are visible to anyone who inspects the page. Never store passwords, tokens, or private data in them.</li>
  <li><strong>Using <code>data-id</code> when a proper attribute exists</strong> — For links, use <code>href</code>; for forms, use <code>value</code>. Data attributes are for custom data with no HTML equivalent.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Create a list of 5 team members. Give each <code>&lt;li&gt;</code> data attributes for <code>data-role</code> (designer/developer/manager), <code>data-country</code>, and <code>data-id</code>. Then write CSS that gives each role a different background colour using attribute selectors.</div>
HTML,2]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[18,4,'HTML Best Practices & Code Quality',<<<'HTML'
<h2>HTML Best Practices &amp; Code Quality</h2>

<p>Writing HTML that works is the starting point. Writing HTML that is clean, maintainable, and accessible is the professional standard. This lesson covers the habits that distinguish beginner HTML from professional HTML.</p>

<h3>1. Always Validate Your HTML</h3>
<p>The W3C (World Wide Web Consortium) provides a free validator at <code>validator.w3.org</code>. Paste your HTML or enter your page URL. It catches errors that browsers silently fix but that may cause problems in certain contexts.</p>
<pre><code>&lt;!-- Common errors the validator catches --&gt;

&lt;!-- Error: &lt;p&gt; inside &lt;ul&gt; --&gt;
&lt;ul&gt;
  &lt;p&gt;This is wrong&lt;/p&gt;  &lt;!-- Only &lt;li&gt; is valid directly inside &lt;ul&gt; --&gt;
&lt;/ul&gt;

&lt;!-- Error: missing alt on img --&gt;
&lt;img src="photo.jpg"&gt;  &lt;!-- alt is required --&gt;

&lt;!-- Error: duplicate id --&gt;
&lt;div id="header"&gt;...&lt;/div&gt;
&lt;div id="header"&gt;...&lt;/div&gt;  &lt;!-- IDs must be unique per page --&gt;</code></pre>

<h3>2. Use Consistent Indentation</h3>
<p>Indent nested elements consistently — typically 2 or 4 spaces (pick one and stick to it). This makes your code readable:</p>
<pre><code>&lt;!-- Hard to read --&gt;
&lt;nav&gt;&lt;ul&gt;&lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;&lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;&lt;/ul&gt;&lt;/nav&gt;

&lt;!-- Easy to read --&gt;
&lt;nav&gt;
  &lt;ul&gt;
    &lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
&lt;/nav&gt;</code></pre>

<h3>3. Use Lowercase for Everything</h3>
<pre><code>&lt;!-- Wrong --&gt;
&lt;P Class="intro"&gt;Hello&lt;/P&gt;

&lt;!-- Correct --&gt;
&lt;p class="intro"&gt;Hello&lt;/p&gt;</code></pre>

<h3>4. Quote All Attribute Values</h3>
<pre><code>&lt;!-- Works but fragile --&gt;
&lt;img src=photo.jpg alt=Team Photo&gt;

&lt;!-- Correct and safe --&gt;
&lt;img src="photo.jpg" alt="Team Photo"&gt;</code></pre>

<h3>5. Keep IDs Unique, Use Classes for Reuse</h3>
<pre><code>&lt;!-- ID: unique identifier — one per page --&gt;
&lt;header id="site-header"&gt;...&lt;/header&gt;
&lt;main id="main-content"&gt;...&lt;/main&gt;

&lt;!-- Class: reusable — apply to many elements --&gt;
&lt;div class="card"&gt;...&lt;/div&gt;
&lt;div class="card"&gt;...&lt;/div&gt;
&lt;div class="card"&gt;...&lt;/div&gt;</code></pre>

<h3>6. Write Meaningful Class Names</h3>
<pre><code>&lt;!-- Describes appearance — fragile (what if you change the colour?) --&gt;
&lt;button class="red-button"&gt;Submit&lt;/button&gt;

&lt;!-- Describes purpose — flexible --&gt;
&lt;button class="btn btn-danger"&gt;Delete Account&lt;/button&gt;
&lt;button class="btn btn-primary"&gt;Submit&lt;/button&gt;</code></pre>

<h3>7. Use Comments Wisely</h3>
<pre><code>&lt;!-- Good comment: explains WHY, not WHAT --&gt;

&lt;!-- Skip link: allows keyboard users to jump past the navigation --&gt;
&lt;a href="#main-content" class="skip-link"&gt;Skip to main content&lt;/a&gt;

&lt;!-- Section dividers for long files --&gt;
&lt;!-- ==================== HERO SECTION ==================== --&gt;
&lt;section class="hero"&gt;...&lt;/section&gt;

&lt;!-- Closing tag labels for deeply nested structures --&gt;
&lt;/div&gt;&lt;!-- /.card-grid --&gt;</code></pre>

<h3>8. Keep Inline Styles Out of HTML</h3>
<pre><code>&lt;!-- Wrong: mixing style and structure --&gt;
&lt;p style="color: red; font-size: 20px; margin: 10px;"&gt;Warning!&lt;/p&gt;

&lt;!-- Correct: use a class, style in CSS --&gt;
&lt;p class="warning-text"&gt;Warning!&lt;/p&gt;
&lt;!-- In CSS: .warning-text { color: red; font-size: 1.25rem; margin: 0.625rem; } --&gt;</code></pre>

<h3>9. Add lang and dir for Multilingual Pages</h3>
<pre><code>&lt;!-- English --&gt;
&lt;html lang="en"&gt;

&lt;!-- French --&gt;
&lt;html lang="fr"&gt;

&lt;!-- Swahili --&gt;
&lt;html lang="sw"&gt;

&lt;!-- Arabic (right-to-left) --&gt;
&lt;html lang="ar" dir="rtl"&gt;</code></pre>

<h3>HTML Quality Checklist</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Item</th><th>Check</th></tr></thead>
  <tbody>
    <tr><td>DOCTYPE is present</td><td>First line: <code>&lt;!DOCTYPE html&gt;</code></td></tr>
    <tr><td>HTML is valid</td><td>No errors at validator.w3.org</td></tr>
    <tr><td>All images have alt text</td><td>Meaningful alt or empty alt=""</td></tr>
    <tr><td>All form inputs have labels</td><td>Linked with for/id</td></tr>
    <tr><td>Page has a unique title</td><td>Under 60 characters</td></tr>
    <tr><td>Semantic elements used</td><td>header, nav, main, footer, article, section</td></tr>
    <tr><td>IDs are unique</td><td>No duplicate IDs on the page</td></tr>
    <tr><td>Links have descriptive text</td><td>No "click here" or "read more" alone</td></tr>
  </tbody>
</table>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Take any HTML page you have built during this course and run it through <code>validator.w3.org</code>. Fix every error and warning it reports. Then go through the quality checklist above and make sure every item is ticked.</div>
HTML,3]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[19,4,'Building a Complete Web Page — Putting It All Together',<<<'HTML'
<h2>Building a Complete Web Page — Putting It All Together</h2>

<p>You have learned about document structure, semantic elements, forms, media, and best practices. Now it is time to combine everything into a complete, real-world web page. In this lesson you will build a landing page for a fictional course from scratch.</p>

<h3>What We Are Building</h3>
<p>We will build a "Course Landing Page" for HackathonAfrica that includes:</p>
<ul>
  <li>A site header with navigation</li>
  <li>A hero section with a headline and call-to-action</li>
  <li>A features section with a list of benefits</li>
  <li>A testimonial</li>
  <li>A signup form</li>
  <li>A footer</li>
</ul>

<h3>The Complete HTML</h3>
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
  &lt;meta charset="UTF-8"&gt;
  &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
  &lt;title&gt;Free Web Dev Course | HackathonAfrica&lt;/title&gt;
  &lt;meta name="description"
        content="Learn HTML, CSS, and JavaScript for free. Join 10,000 African developers."&gt;
  &lt;link rel="stylesheet" href="styles.css"&gt;
&lt;/head&gt;
&lt;body&gt;

  &lt;!-- SKIP LINK: lets keyboard users jump past nav --&gt;
  &lt;a href="#main" class="skip-link"&gt;Skip to main content&lt;/a&gt;

  &lt;!-- HEADER --&gt;
  &lt;header&gt;
    &lt;a href="/" class="logo"&gt;
      &lt;img src="logo.png" alt="HackathonAfrica" width="160" height="40"&gt;
    &lt;/a&gt;
    &lt;nav aria-label="Main navigation"&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="#features"&gt;Features&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#testimonials"&gt;Stories&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#signup"&gt;Get Started&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/nav&gt;
  &lt;/header&gt;

  &lt;!-- MAIN CONTENT --&gt;
  &lt;main id="main"&gt;

    &lt;!-- HERO --&gt;
    &lt;section aria-labelledby="hero-heading"&gt;
      &lt;h1 id="hero-heading"&gt;Learn to Code. Build Your Future.&lt;/h1&gt;
      &lt;p&gt;Join thousands of African developers who started their journey
         with our free, self-paced web development course.&lt;/p&gt;
      &lt;a href="#signup"&gt;Start Learning for Free&lt;/a&gt;
    &lt;/section&gt;

    &lt;!-- FEATURES --&gt;
    &lt;section id="features" aria-labelledby="features-heading"&gt;
      &lt;h2 id="features-heading"&gt;Why HackathonAfrica?&lt;/h2&gt;
      &lt;ul&gt;
        &lt;li&gt;
          &lt;h3&gt;100% Free&lt;/h3&gt;
          &lt;p&gt;All courses, quizzes, and certificates — no credit card required.&lt;/p&gt;
        &lt;/li&gt;
        &lt;li&gt;
          &lt;h3&gt;Learn at Your Own Pace&lt;/h3&gt;
          &lt;p&gt;Study when it suits you. Lessons are available 24/7 on any device.&lt;/p&gt;
        &lt;/li&gt;
        &lt;li&gt;
          &lt;h3&gt;Built for Africa&lt;/h3&gt;
          &lt;p&gt;Examples, projects, and context relevant to African developers and markets.&lt;/p&gt;
        &lt;/li&gt;
      &lt;/ul&gt;
    &lt;/section&gt;

    &lt;!-- TESTIMONIALS --&gt;
    &lt;section id="testimonials" aria-labelledby="testimonials-heading"&gt;
      &lt;h2 id="testimonials-heading"&gt;Student Stories&lt;/h2&gt;
      &lt;article&gt;
        &lt;blockquote&gt;
          &lt;p&gt;"Six months ago I couldn't write a single line of code.
             Today I have a job as a junior developer."&lt;/p&gt;
        &lt;/blockquote&gt;
        &lt;footer&gt;
          &lt;cite&gt;— Kwame Asante, Accra, Ghana&lt;/cite&gt;
        &lt;/footer&gt;
      &lt;/article&gt;
    &lt;/section&gt;

    &lt;!-- SIGNUP FORM --&gt;
    &lt;section id="signup" aria-labelledby="signup-heading"&gt;
      &lt;h2 id="signup-heading"&gt;Create Your Free Account&lt;/h2&gt;
      &lt;form action="/register" method="POST"&gt;
        &lt;div&gt;
          &lt;label for="name"&gt;Full Name *&lt;/label&gt;
          &lt;input type="text" id="name" name="name"
                 required minlength="2" autocomplete="name"&gt;
        &lt;/div&gt;
        &lt;div&gt;
          &lt;label for="email"&gt;Email Address *&lt;/label&gt;
          &lt;input type="email" id="email" name="email"
                 required autocomplete="email"&gt;
        &lt;/div&gt;
        &lt;div&gt;
          &lt;label for="country"&gt;Country *&lt;/label&gt;
          &lt;select id="country" name="country" required&gt;
            &lt;option value=""&gt;Select…&lt;/option&gt;
            &lt;option value="GH"&gt;Ghana&lt;/option&gt;
            &lt;option value="NG"&gt;Nigeria&lt;/option&gt;
            &lt;option value="KE"&gt;Kenya&lt;/option&gt;
          &lt;/select&gt;
        &lt;/div&gt;
        &lt;label&gt;
          &lt;input type="checkbox" name="terms" required&gt;
          I agree to the &lt;a href="/terms"&gt;Terms of Service&lt;/a&gt; *
        &lt;/label&gt;
        &lt;button type="submit"&gt;Create Free Account&lt;/button&gt;
      &lt;/form&gt;
    &lt;/section&gt;

  &lt;/main&gt;

  &lt;!-- FOOTER --&gt;
  &lt;footer&gt;
    &lt;nav aria-label="Footer links"&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="/privacy"&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/terms"&gt;Terms of Service&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/contact"&gt;Contact Us&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/nav&gt;
    &lt;p&gt;&lt;small&gt;&copy; 2025 HackathonAfrica. All rights reserved.&lt;/small&gt;&lt;/p&gt;
  &lt;/footer&gt;

  &lt;script src="app.js" defer&gt;&lt;/script&gt;
&lt;/body&gt;
&lt;/html&gt;</code></pre>

<h3>What Makes This Page Professional?</h3>
<ul>
  <li>Valid HTML5 with DOCTYPE</li>
  <li>Semantic landmark elements used throughout</li>
  <li>Every section has an <code>aria-labelledby</code> linking to its heading</li>
  <li>Skip link for keyboard accessibility</li>
  <li>Form uses labels, required attributes, and autocomplete hints</li>
  <li>Images have width, height, and alt text (placeholder used here)</li>
  <li>JavaScript deferred to the end</li>
  <li>Descriptive link text (no "click here")</li>
</ul>

<div class="alert alert-success mt-3"><strong>Your HTML Project:</strong> Build your own version of this landing page. Change the topic to something you care about — a portfolio page, a local business, a community event. Apply everything from this module: semantic HTML, a form with validation, at least one image, and a complete head section with meta tags. This is your HTML graduation project!</div>
HTML,4]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[20,4,'HTML Revision & Common Patterns',<<<'HTML'
<h2>HTML Revision &amp; Common Patterns</h2>

<p>Before you move on to CSS, let's consolidate your HTML knowledge with a review of the most important concepts and a look at the patterns you will write over and over in real projects.</p>

<h3>The 20 HTML Elements You Will Use Every Day</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Element</th><th>Use It For</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;html&gt;</code>, <code>&lt;head&gt;</code>, <code>&lt;body&gt;</code></td><td>The document skeleton — always present</td></tr>
    <tr><td><code>&lt;title&gt;</code>, <code>&lt;meta&gt;</code></td><td>Page metadata — SEO, viewport, charset</td></tr>
    <tr><td><code>&lt;h1&gt;</code>–<code>&lt;h6&gt;</code></td><td>Headings — document outline</td></tr>
    <tr><td><code>&lt;p&gt;</code></td><td>Paragraphs of text</td></tr>
    <tr><td><code>&lt;a&gt;</code></td><td>Links — to pages, sections, email, files</td></tr>
    <tr><td><code>&lt;img&gt;</code></td><td>Images — always with alt, width, height</td></tr>
    <tr><td><code>&lt;ul&gt;</code>, <code>&lt;ol&gt;</code>, <code>&lt;li&gt;</code></td><td>Lists — navigation, steps, features</td></tr>
    <tr><td><code>&lt;header&gt;</code>, <code>&lt;nav&gt;</code>, <code>&lt;main&gt;</code>, <code>&lt;footer&gt;</code></td><td>Page structure landmarks</td></tr>
    <tr><td><code>&lt;article&gt;</code>, <code>&lt;section&gt;</code></td><td>Content grouping</td></tr>
    <tr><td><code>&lt;div&gt;</code>, <code>&lt;span&gt;</code></td><td>Generic containers</td></tr>
    <tr><td><code>&lt;form&gt;</code>, <code>&lt;input&gt;</code>, <code>&lt;label&gt;</code>, <code>&lt;button&gt;</code></td><td>User input</td></tr>
    <tr><td><code>&lt;strong&gt;</code>, <code>&lt;em&gt;</code></td><td>Important / emphasised text</td></tr>
  </tbody>
</table>

<h3>Pattern 1: The Card</h3>
<p>Cards are everywhere — product listings, blog posts, user profiles, course tiles. Here is the standard HTML structure:</p>
<pre><code>&lt;article class="card"&gt;
  &lt;img src="course.jpg" alt="HTML course thumbnail" width="400" height="250" loading="lazy"&gt;
  &lt;div class="card-body"&gt;
    &lt;h3 class="card-title"&gt;HTML Fundamentals&lt;/h3&gt;
    &lt;p class="card-text"&gt;Learn HTML from scratch in 4 hours.&lt;/p&gt;
    &lt;a href="/courses/html" class="btn"&gt;Start Course&lt;/a&gt;
  &lt;/div&gt;
&lt;/article&gt;</code></pre>

<h3>Pattern 2: The Navigation Bar</h3>
<pre><code>&lt;header&gt;
  &lt;a href="/" class="logo"&gt;
    &lt;img src="logo.svg" alt="SiteName" width="120" height="32"&gt;
  &lt;/a&gt;
  &lt;nav aria-label="Main"&gt;
    &lt;ul&gt;
      &lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="/courses"&gt;Courses&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/nav&gt;
  &lt;a href="/login" class="btn"&gt;Log In&lt;/a&gt;
&lt;/header&gt;</code></pre>

<h3>Pattern 3: The Hero Section</h3>
<pre><code>&lt;section class="hero"&gt;
  &lt;div class="hero-content"&gt;
    &lt;h1&gt;Learn to Code for Free&lt;/h1&gt;
    &lt;p&gt;Join 10,000+ African developers. No experience needed.&lt;/p&gt;
    &lt;div class="hero-actions"&gt;
      &lt;a href="/signup" class="btn btn-primary"&gt;Get Started — Free&lt;/a&gt;
      &lt;a href="/courses" class="btn btn-secondary"&gt;See Courses&lt;/a&gt;
    &lt;/div&gt;
  &lt;/div&gt;
  &lt;img src="hero.jpg" alt="Developers collaborating at a hackathon" width="600" height="400"&gt;
&lt;/section&gt;</code></pre>

<h3>Pattern 4: The Footer</h3>
<pre><code>&lt;footer&gt;
  &lt;div class="footer-grid"&gt;
    &lt;div&gt;
      &lt;img src="logo.svg" alt="HackathonAfrica" width="120" height="32"&gt;
      &lt;p&gt;Training the next generation of African tech leaders.&lt;/p&gt;
    &lt;/div&gt;
    &lt;nav aria-label="Courses"&gt;
      &lt;h3&gt;Courses&lt;/h3&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="/courses/html"&gt;HTML&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/courses/css"&gt;CSS&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/courses/js"&gt;JavaScript&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/nav&gt;
    &lt;nav aria-label="Company"&gt;
      &lt;h3&gt;Company&lt;/h3&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="/about"&gt;About Us&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/contact"&gt;Contact&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/privacy"&gt;Privacy Policy&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/nav&gt;
  &lt;/div&gt;
  &lt;div class="footer-bottom"&gt;
    &lt;small&gt;&copy; 2025 HackathonAfrica. All rights reserved.&lt;/small&gt;
  &lt;/div&gt;
&lt;/footer&gt;</code></pre>

<h3>HTML Quick Reference</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Concept</th><th>Key Rule</th></tr></thead>
  <tbody>
    <tr><td>DOCTYPE</td><td>Always the first line: <code>&lt;!DOCTYPE html&gt;</code></td></tr>
    <tr><td>Images</td><td>Always: alt, width, height. Use loading="lazy" below the fold.</td></tr>
    <tr><td>Links</td><td>Descriptive text. Add rel="noopener noreferrer" with target="_blank".</td></tr>
    <tr><td>Forms</td><td>Every input needs a label. name attribute required for submission.</td></tr>
    <tr><td>Headings</td><td>One h1 per page. Never skip levels. Don't use for styling.</td></tr>
    <tr><td>Semantic HTML</td><td>Choose semantic elements first; use div/span only as fallback.</td></tr>
    <tr><td>Accessibility</td><td>Labels, alt text, focus styles, ARIA where needed.</td></tr>
  </tbody>
</table>

<div class="alert alert-success mt-3"><strong>You have completed the HTML course!</strong> You now understand the full structure of web pages. The next step is CSS — where you will learn to make everything you have built beautiful. Every layout, colour, font, and animation you see on modern websites is powered by CSS. See you in the next course!</div>
HTML,5]);

echo "  [✓] HTML Module 4 lessons\n";

// ══════════════════════════════════════════════════════════
// COURSE 2: CSS
// ══════════════════════════════════════════════════════════
$ins('INSERT INTO courses(id,title,description,status,order_index)VALUES(?,?,?,?,?)',
    [2,'CSS — Styling the Web',
     'Transform plain HTML into beautiful, responsive designs. Master selectors, the box model, Flexbox, Grid, and responsive design.',
     'published',2]);

$ins('INSERT INTO modules(id,course_id,title,description,order_index)VALUES(?,?,?,?,?)',
    [5,2,'CSS Fundamentals',
     'Understand how CSS works: selectors, the box model, colours, typography, and the cascade.',1]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[21,5,'How CSS Works: Rules, Selectors & Properties',<<<'CSS'
<h2>How CSS Works: Rules, Selectors &amp; Properties</h2>

<p>CSS (Cascading Style Sheets) is the language that makes websites beautiful. In this lesson you will understand the fundamental mechanism of CSS — how rules are written, how they target elements, and how they are applied.</p>

<h3>What Is CSS?</h3>
<p>Think of HTML as the skeleton of a website and CSS as the skin, clothing, and hair. HTML defines <em>what</em> content is; CSS defines <em>how</em> it looks. A website without CSS would be plain black text on a white background — functional, but not pleasant.</p>

<h3>Anatomy of a CSS Rule</h3>
<p>A CSS rule has two parts: a <strong>selector</strong> (what to style) and a <strong>declaration block</strong> (how to style it).</p>
<pre><code>/* This is a CSS comment */

selector {
  property: value;
  property: value;
}

/* Example: */
h1 {
  color: #1a73e8;
  font-size: 2rem;
  font-weight: 700;
}</code></pre>

<ul>
  <li><strong>Selector</strong> — Identifies which HTML element(s) to style. Here, <code>h1</code> targets all <code>&lt;h1&gt;</code> elements.</li>
  <li><strong>Property</strong> — The aspect of the element you want to change (colour, size, spacing, etc.).</li>
  <li><strong>Value</strong> — How you want to change it.</li>
  <li><strong>Declaration</strong> — One property-value pair: <code>color: #1a73e8;</code></li>
  <li><strong>Declaration block</strong> — All declarations between the curly braces <code>{ }</code>.</li>
  <li><strong>Semicolon</strong> — Required after every declaration.</li>
</ul>

<h3>Three Ways to Add CSS</h3>

<h4>1. External Stylesheet (Best Practice — Always Use This)</h4>
<pre><code>&lt;!-- In your HTML &lt;head&gt; --&gt;
&lt;link rel="stylesheet" href="styles.css"&gt;

/* In styles.css */
body {
  font-family: Arial, sans-serif;
}</code></pre>
<p>Keeps CSS separate from HTML. One file can style multiple pages. Easy to maintain.</p>

<h4>2. Internal Stylesheet (Okay for Small Single Pages)</h4>
<pre><code>&lt;head&gt;
  &lt;style&gt;
    h1 { color: green; }
  &lt;/style&gt;
&lt;/head&gt;</code></pre>

<h4>3. Inline Styles (Avoid — Hard to Maintain)</h4>
<pre><code>&lt;h1 style="color: green; font-size: 2rem;"&gt;Hello&lt;/h1&gt;</code></pre>
<p>Only use inline styles when you have no other option (e.g., dynamic styles from JavaScript).</p>

<h3>Common Selectors</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Selector</th><th>Example</th><th>What It Selects</th></tr></thead>
  <tbody>
    <tr><td>Element</td><td><code>p { }</code></td><td>All <code>&lt;p&gt;</code> elements</td></tr>
    <tr><td>Class</td><td><code>.card { }</code></td><td>All elements with <code>class="card"</code></td></tr>
    <tr><td>ID</td><td><code>#header { }</code></td><td>The element with <code>id="header"</code></td></tr>
    <tr><td>Descendant</td><td><code>nav a { }</code></td><td>All <code>&lt;a&gt;</code> tags inside a <code>&lt;nav&gt;</code></td></tr>
    <tr><td>Direct child</td><td><code>ul &gt; li { }</code></td><td>Only direct <code>&lt;li&gt;</code> children of <code>&lt;ul&gt;</code></td></tr>
    <tr><td>Multiple</td><td><code>h1, h2, h3 { }</code></td><td>h1 AND h2 AND h3</td></tr>
    <tr><td>Pseudo-class</td><td><code>a:hover { }</code></td><td>Links when the mouse hovers over them</td></tr>
    <tr><td>Pseudo-element</td><td><code>p::first-line { }</code></td><td>The first line of a paragraph</td></tr>
    <tr><td>Attribute</td><td><code>[type="email"] { }</code></td><td>Inputs with <code>type="email"</code></td></tr>
    <tr><td>Universal</td><td><code>* { }</code></td><td>Every element on the page</td></tr>
  </tbody>
</table>

<h3>Practical Example — Styling a Navigation</h3>
<pre><code>/* Style the nav container */
nav {
  background-color: #1a1a2e;
  padding: 1rem 2rem;
}

/* Style the list inside nav */
nav ul {
  list-style: none;    /* Remove bullet points */
  margin: 0;
  padding: 0;
  display: flex;       /* Side by side */
  gap: 2rem;
}

/* Style all links in nav */
nav a {
  color: white;
  text-decoration: none;  /* Remove underline */
  font-weight: 600;
}

/* Style links when hovered */
nav a:hover {
  color: #f59e0b;          /* Gold colour on hover */
  text-decoration: underline;
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Missing the semicolon</strong> — <code>color: red</code> without <code>;</code> will break any declarations that follow it.</li>
  <li><strong>Using spaces in class selectors</strong> — <code>.my card</code> means "a <code>.card</code> inside a <code>.my</code>" — not a class called "my card". Use hyphens: <code>.my-card</code>.</li>
  <li><strong>Overusing IDs for styling</strong> — IDs should be unique. Classes are more flexible and reusable for styling.</li>
</ul>

<h3>Key Takeaways</h3>
<ul>
  <li>CSS rules have a selector and a declaration block of property-value pairs.</li>
  <li>Always use an external stylesheet in production.</li>
  <li>Use class selectors (<code>.name</code>) for most styling — they are reusable and not too specific.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Create a <code>styles.css</code> file and link it to your HTML. Style your navigation to have a dark background, white link text, no underline, and a colour change on hover. Change the page's background colour and set a font-family on the body.</div>
CSS,1]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[22,5,'The Box Model: margin, border, padding & content',<<<'CSS'
<h2>The Box Model: margin, border, padding &amp; content</h2>

<p>The box model is the single most important concept in CSS layout. Once you truly understand it, a huge amount of CSS behaviour will "click" into place. Every element on a web page is a rectangular box, and the box model describes how that box is sized and spaced.</p>

<h3>The Four Layers of a Box</h3>
<p>Think of a framed painting: the painting itself (content), a white border around it (padding), the frame (border), and the space on the wall around the frame (margin).</p>
<pre><code>┌─────────────────────────────────┐
│           MARGIN                │  ← Space outside the box
│  ┌───────────────────────────┐  │
│  │         BORDER             │  │  ← The visible edge
│  │  ┌─────────────────────┐  │  │
│  │  │       PADDING        │  │  │  ← Space inside the border
│  │  │  ┌───────────────┐  │  │  │
│  │  │  │    CONTENT    │  │  │  │  ← Your text/image
│  │  │  └───────────────┘  │  │  │
│  │  └─────────────────────┘  │  │
│  └───────────────────────────┘  │
└─────────────────────────────────┘</code></pre>

<h3>Setting Box Model Properties</h3>
<pre><code>.card {
  /* CONTENT */
  width: 320px;
  height: 200px;

  /* PADDING: space between content and border */
  padding: 24px;                  /* All four sides: 24px */
  /* OR specify individually: */
  padding-top: 16px;
  padding-right: 24px;
  padding-bottom: 16px;
  padding-left: 24px;
  /* Shorthand (top, right, bottom, left — clockwise): */
  padding: 16px 24px 16px 24px;
  /* Two values (top+bottom, left+right): */
  padding: 16px 24px;

  /* BORDER */
  border: 2px solid #e5e7eb;
  /* OR individually: */
  border-width: 2px;
  border-style: solid;  /* solid, dashed, dotted, none */
  border-color: #e5e7eb;
  border-radius: 8px;   /* Rounded corners! */

  /* MARGIN: space outside the border */
  margin: 16px;         /* All sides */
  margin: 0 auto;       /* 0 top/bottom, auto left/right = centres the box */
  margin-bottom: 24px;  /* Individual side */
}</code></pre>

<h3>The box-sizing Problem — and Fix</h3>
<p>By default, <code>width</code> and <code>height</code> set the <em>content</em> area only. Padding and border are <strong>added on top</strong>. This causes a common confusion:</p>
<pre><code>.box {
  width: 300px;
  padding: 20px;
  border: 2px solid black;
  /* Actual rendered width = 300 + 20+20 + 2+2 = 344px! Surprising! */
}

/* The fix: box-sizing: border-box */
/* Now width includes padding AND border */
.box {
  box-sizing: border-box;
  width: 300px;
  padding: 20px;
  border: 2px solid black;
  /* Actual rendered width = exactly 300px */
}</code></pre>

<p>The universal fix — <strong>always add this to every CSS file</strong>:</p>
<pre><code>/* Apply border-box to everything */
*,
*::before,
*::after {
  box-sizing: border-box;
}</code></pre>

<h3>Margin Collapsing</h3>
<p>When two vertical margins meet, they <strong>collapse</strong> into the larger of the two — not the sum:</p>
<pre><code>/* These two paragraphs' margins collapse */
p {
  margin-top: 16px;
  margin-bottom: 24px;
}
/* Between two paragraphs, the gap = 24px (not 16+24 = 40px) */

/* Collapsing only happens vertically (top/bottom), never horizontally (left/right) */
/* And it doesn't happen with flex/grid containers */</code></pre>

<h3>display: block vs inline vs inline-block</h3>
<p>The box model behaves differently for block and inline elements:</p>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Display</th><th>Width/Height</th><th>Margin/Padding</th><th>Examples</th></tr></thead>
  <tbody>
    <tr><td><code>block</code></td><td>Full width; height from content</td><td>All sides work</td><td>div, p, h1–h6, section</td></tr>
    <tr><td><code>inline</code></td><td>Content size only — cannot set width/height</td><td>Only left/right work (top/bottom ignored)</td><td>span, a, strong, em</td></tr>
    <tr><td><code>inline-block</code></td><td>Content size, but can set width/height</td><td>All sides work</td><td>buttons, nav items</td></tr>
    <tr><td><code>none</code></td><td>Element removed from layout</td><td>No space taken</td><td>Hidden elements</td></tr>
  </tbody>
</table>

<h3>Practical: Styling a Card</h3>
<pre><code>/* Reset */
*, *::before, *::after { box-sizing: border-box; }

/* Card component */
.card {
  width: 320px;
  background-color: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;

  /* Shadow for depth */
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.card h3 {
  margin-top: 0;         /* Remove default top margin on first heading */
  margin-bottom: 8px;
  font-size: 1.25rem;
}

.card p {
  margin: 0 0 16px;
  color: #6b7280;
}

.card a {
  display: inline-block;  /* So padding works */
  padding: 8px 20px;
  background-color: #1a73e8;
  color: white;
  text-decoration: none;
  border-radius: 6px;
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Not adding <code>box-sizing: border-box</code></strong> — Your layouts will be wider than expected. Add the universal reset to every project.</li>
  <li><strong>Adding <code>margin</code> to inline elements</strong> — Top and bottom margins have no effect on inline elements. Use <code>display: inline-block</code> or <code>display: block</code>.</li>
  <li><strong>Using <code>margin: auto</code> horizontally on inline elements</strong> — It only centres block elements. The element must have an explicit width.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Create three cards side by side. Each card should be 300px wide, have 24px padding, a 1px solid border, 12px border-radius, and 16px margin between them. Add the box-sizing reset. Inspect the elements in browser DevTools and hover over them — you should see the box model layers highlighted in colour.</div>
CSS,2]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[23,5,'Colours & Typography',<<<'CSS'
<h2>Colours &amp; Typography</h2>

<p>After structure and spacing, colour and typography are the most impactful design decisions you will make. In this lesson you will learn all the ways to specify colours in CSS and how to control text appearance professionally.</p>

<h3>CSS Colours — Six Ways to Specify Them</h3>

<h4>1. Named Colours</h4>
<pre><code>color: red;
color: cornflowerblue;
color: darkorange;
/* 140+ named colours — easy to read, limited options */</code></pre>

<h4>2. Hexadecimal (#rrggbb)</h4>
<pre><code>color: #ff0000;    /* Pure red */
color: #1a73e8;    /* Google blue */
color: #ffffff;    /* White */
color: #000000;    /* Black */
color: #f59e0b;    /* Amber */

/* Shorthand: #rgb (when both digits are the same) */
color: #fff;       /* Same as #ffffff */
color: #333;       /* Same as #333333 (dark grey) */</code></pre>

<h4>3. RGB and RGBA</h4>
<pre><code>color: rgb(26, 115, 232);         /* Red, Green, Blue (0–255) */
color: rgba(26, 115, 232, 0.8);   /* + Alpha (opacity: 0 transparent, 1 opaque) */

/* Great for semi-transparent overlays */
background-color: rgba(0, 0, 0, 0.5);  /* 50% transparent black */</code></pre>

<h4>4. HSL and HSLA (Most Intuitive)</h4>
<pre><code>/* Hue (0-360 degrees on colour wheel), Saturation %, Lightness % */
color: hsl(210, 80%, 50%);        /* A blue */
color: hsl(45, 100%, 55%);        /* A gold/yellow */
color: hsla(210, 80%, 50%, 0.7);  /* Blue at 70% opacity */

/* HSL is great for colour palettes — keep hue, vary lightness: */
--blue-100: hsl(210, 80%, 95%);   /* Very light blue */
--blue-500: hsl(210, 80%, 50%);   /* Medium blue */
--blue-900: hsl(210, 80%, 15%);   /* Very dark blue */</code></pre>

<h3>Typography Fundamentals</h3>

<h4>font-family — Choosing Your Typeface</h4>
<pre><code>/* Always provide a stack — fallbacks in case the first isn't available */
body {
  font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
}

/* Using Google Fonts — add to &lt;head&gt; first: */
/* &lt;link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"&gt; */

/* Font categories */
font-family: Georgia, 'Times New Roman', serif;       /* Serif — formal */
font-family: Arial, Helvetica, sans-serif;             /* Sans-serif — modern */
font-family: 'Courier New', Courier, monospace;        /* Monospace — code */</code></pre>

<h4>font-size — Scaling Text</h4>
<pre><code>/* Avoid px for font-size — it doesn't scale with browser settings */
p { font-size: 1rem; }       /* 1rem = browser default (usually 16px) */
h1 { font-size: 2.5rem; }    /* 2.5 × 16px = 40px */
small { font-size: 0.875rem; } /* 0.875 × 16px = 14px */

/* em is relative to parent element's font-size */
.card {
  font-size: 1.125rem;  /* 18px */
}
.card p {
  font-size: 0.9em;     /* 0.9 × 18px = ~16px */
}</code></pre>

<h4>font-weight, font-style & text properties</h4>
<pre><code>h1 {
  font-weight: 700;           /* Bold. Range: 100–900. 400 = normal. */
  font-style: italic;         /* normal | italic | oblique */
  letter-spacing: -0.025em;   /* Tighten letter spacing for large headings */
  line-height: 1.2;           /* 1.2 × font-size. Unitless is best. */
  text-transform: uppercase;  /* uppercase | lowercase | capitalize | none */
}

p {
  line-height: 1.6;           /* 1.5–1.7 is comfortable for body text */
  color: #374151;             /* Slightly off-black is easier to read than pure black */
  max-width: 65ch;            /* 65 characters wide — optimal reading line length */
}</code></pre>

<h4>text-align & text-decoration</h4>
<pre><code>h1 { text-align: center; }          /* left | center | right | justify */

a  { text-decoration: none; }       /* Remove underline from links */
a:hover { text-decoration: underline; }   /* Add back on hover */

del { text-decoration: line-through; }    /* Strikethrough */
u   { text-decoration: underline; }</code></pre>

<h3>A Complete Typography Setup</h3>
<pre><code>/* Import font */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

/* Reset */
*, *::before, *::after { box-sizing: border-box; }

/* Base typography */
html {
  font-size: 16px;  /* 1rem base */
}

body {
  font-family: 'Inter', system-ui, sans-serif;
  font-size: 1rem;
  line-height: 1.6;
  color: #1f2937;
  background-color: #f9fafb;
}

/* Heading scale */
h1 { font-size: 2.5rem;  line-height: 1.2; font-weight: 700; }
h2 { font-size: 2rem;    line-height: 1.3; font-weight: 700; }
h3 { font-size: 1.5rem;  line-height: 1.4; font-weight: 600; }
h4 { font-size: 1.25rem; line-height: 1.4; font-weight: 600; }

/* Links */
a {
  color: #1a73e8;
  text-decoration: underline;
}
a:hover {
  color: #1557b0;
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using <code>px</code> for font sizes</strong> — Users who have set a larger browser font size (often elderly users or those with visual impairments) cannot override <code>px</code> sizes. Use <code>rem</code>.</li>
  <li><strong>Using pure black (<code>#000000</code>) for body text</strong> — It creates too much contrast on white. Use a very dark grey like <code>#1f2937</code> or <code>#111827</code>.</li>
  <li><strong>Small line-height for body text</strong> — <code>line-height: 1</code> on paragraphs makes text almost unreadable. Use at least <code>1.5</code>.</li>
  <li><strong>No font fallbacks</strong> — Always provide at least one generic family (<code>sans-serif</code>, <code>serif</code>, <code>monospace</code>) as the last fallback.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Set up a complete typography system for your project. Import a Google Font, define a type scale from h1 to h4, set body font size to 1rem with line-height 1.6, and choose a colour palette of 5 colours (background, body text, headings, links, muted text). Write it all in your CSS file.</div>
CSS,3]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[24,5,'The Cascade, Specificity & Inheritance',<<<'CSS'
<h2>The Cascade, Specificity &amp; Inheritance</h2>

<p>The "C" in CSS stands for <em>Cascading</em>. Understanding the cascade is understanding how CSS resolves conflicts — what happens when two rules both try to set the same property on the same element. This is the concept that trips up beginners the most, but once understood, it gives you complete control.</p>

<h3>The Cascade — Three Rules for Resolving Conflicts</h3>
<p>When multiple CSS rules apply to the same element, the browser uses these rules (in order) to decide which one wins:</p>
<ol>
  <li><strong>Specificity</strong> — The more specific selector wins</li>
  <li><strong>Source Order</strong> — If specificity is equal, the later rule wins</li>
  <li><strong>Importance</strong> — <code>!important</code> overrides everything (use sparingly)</li>
</ol>

<h3>Specificity — How Specific Is Your Selector?</h3>
<p>Every selector has a specificity score. Think of it as a three-digit number: <code>IDs – Classes – Elements</code></p>

<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Selector Type</th><th>Points</th><th>Example</th><th>Score</th></tr></thead>
  <tbody>
    <tr><td>Inline style</td><td>1,0,0,0</td><td><code>style="color:red"</code></td><td>Highest</td></tr>
    <tr><td>ID selector</td><td>0,1,0,0</td><td><code>#header</code></td><td>100</td></tr>
    <tr><td>Class, attribute, pseudo-class</td><td>0,0,1,0</td><td><code>.card</code>, <code>[type]</code>, <code>:hover</code></td><td>10</td></tr>
    <tr><td>Element, pseudo-element</td><td>0,0,0,1</td><td><code>p</code>, <code>h1</code>, <code>::before</code></td><td>1</td></tr>
    <tr><td>Universal selector</td><td>0,0,0,0</td><td><code>*</code></td><td>0</td></tr>
  </tbody>
</table>

<pre><code>/* Specificity calculation examples */
p              { color: black; }    /* Score: 0,0,0,1 (1 element) */
.intro         { color: blue; }     /* Score: 0,0,1,0 (1 class) */
#hero          { color: green; }    /* Score: 0,1,0,0 (1 ID) */
.hero p        { color: red; }      /* Score: 0,0,1,1 (1 class + 1 element) */
#hero p        { color: purple; }   /* Score: 0,1,0,1 (1 ID + 1 element) */

/* Given: &lt;p id="hero" class="intro"&gt; */
/* The #hero rule wins (highest specificity) */</code></pre>

<h3>Source Order</h3>
<pre><code>/* If specificity is the same, the LATER rule wins */
p { color: blue; }   /* This loses */
p { color: red; }    /* This wins — comes later */

/* In external stylesheets, order of &lt;link&gt; tags matters */
/* The last linked stylesheet's rules override earlier ones */</code></pre>

<h3>!important — The Nuclear Option</h3>
<pre><code>p { color: red !important; }  /* Overrides everything, even higher specificity */

/* Only use !important when:
   1. Overriding a third-party library's styles
   2. Utility classes that must always apply

   NEVER use it to fix specificity issues you caused yourself */</code></pre>

<h3>Inheritance — Styles Passed from Parent to Child</h3>
<p>Some CSS properties are <strong>inherited</strong> by child elements from their parents. Others are not:</p>

<pre><code>body {
  font-family: 'Inter', sans-serif;  /* Inherited by ALL child elements */
  color: #374151;                    /* Inherited by ALL child elements */
  border: 1px solid red;             /* NOT inherited — border only on body */
  padding: 20px;                     /* NOT inherited — padding only on body */
}

/* Inherited properties (propagate down to all children): */
/* color, font-family, font-size, font-weight, line-height,
   text-align, text-transform, letter-spacing, cursor, visibility */

/* Non-inherited properties (only apply to the element itself): */
/* margin, padding, border, width, height, display,
   background, position, top/left/right/bottom */</code></pre>

<h4>Using inherit, initial and unset</h4>
<pre><code>a {
  color: inherit;    /* Force link to inherit the text colour instead of browser default blue */
}

button {
  font-family: inherit;  /* Buttons don't inherit font-family by default in many browsers */
  font-size: inherit;
}

/* Reset a property to browser default */
h3 {
  font-weight: initial;  /* Remove bold if you want */
}

/* Remove all user-agent styles */
.clean {
  all: unset;
}</code></pre>

<h3>The Browser's Default Stylesheet</h3>
<p>Every browser has its own default styles (user agent stylesheet) that apply before your CSS. This is why <code>&lt;h1&gt;</code> is bold and large by default, and why links are blue and underlined. Your CSS overrides these defaults.</p>

<p>The classic CSS reset removes all default styles so you start with a clean slate:</p>
<pre><code>/* Minimal modern reset */
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  min-height: 100vh;
  font-family: system-ui, sans-serif;
  line-height: 1.5;
}

img, video {
  max-width: 100%;  /* Images never overflow their container */
  height: auto;
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Adding <code>!important</code> to fix a specificity problem</strong> — This creates technical debt. Instead, understand why your rule isn't winning and adjust the selector.</li>
  <li><strong>Using many IDs for styling</strong> — ID specificity (100) is much higher than class (10). When you need to override an ID style, you need an even more specific rule. Prefer classes.</li>
  <li><strong>Expecting border to be inherited</strong> — If you set a border on a parent and it doesn't appear on children, this is why.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Write three CSS rules targeting the same paragraph in different ways (element, class, ID). Open the browser DevTools (F12 → Elements tab) and click on the paragraph. In the "Styles" panel, you can see exactly which rules apply and which are crossed out as overridden. This is your most powerful debugging tool for CSS specificity issues.</div>
CSS,4]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[25,5,'CSS Units: px, rem, em, %, vw, vh',<<<'CSS'
<h2>CSS Units: px, rem, em, %, vw, vh</h2>

<p>CSS lets you specify sizes in many different units. Choosing the right unit is crucial for responsive design and accessibility. This lesson explains when to use each unit and why.</p>

<h3>Absolute Units</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Unit</th><th>What It Is</th><th>Best Used For</th></tr></thead>
  <tbody>
    <tr><td><code>px</code></td><td>Pixels — 1/96th of an inch on screen</td><td>Borders, shadows, fine details where exact size matters</td></tr>
    <tr><td><code>pt</code></td><td>Points — for print</td><td>Print stylesheets only</td></tr>
  </tbody>
</table>

<h3>Relative Units — The Good Ones</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Unit</th><th>Relative To</th><th>Best Used For</th></tr></thead>
  <tbody>
    <tr><td><code>rem</code></td><td>Root element (<code>&lt;html&gt;</code>) font size (usually 16px)</td><td>Font sizes, padding, margins — most things</td></tr>
    <tr><td><code>em</code></td><td>Parent element's font size</td><td>Padding/margin inside components that should scale with their text size</td></tr>
    <tr><td><code>%</code></td><td>Parent element's dimension</td><td>Widths in layouts</td></tr>
    <tr><td><code>vw</code></td><td>1% of viewport width</td><td>Full-width elements, fluid typography</td></tr>
    <tr><td><code>vh</code></td><td>1% of viewport height</td><td>Full-screen sections, hero areas</td></tr>
    <tr><td><code>ch</code></td><td>Width of the "0" character in the current font</td><td>Limiting line length (e.g., <code>max-width: 65ch</code>)</td></tr>
  </tbody>
</table>

<h3>rem vs em — Understanding the Difference</h3>
<pre><code>html {
  font-size: 16px;  /* 1rem = 16px everywhere */
}

.parent {
  font-size: 20px;
}

.parent .child {
  /* em is relative to PARENT font-size */
  font-size: 1.5em;    /* 1.5 × 20px = 30px */
  padding: 1em;        /* 1 × 30px = 30px (current element's font-size) */

  /* rem is relative to ROOT (html) font-size */
  margin: 1rem;        /* Always 1 × 16px = 16px, no matter what parent says */
}

/* Rule of thumb:
   - Use rem for font-sizes (scales with user's browser setting)
   - Use rem for spacing (predictable)
   - Use em for padding inside buttons/badges (scales with button text size) */</code></pre>

<h3>When to Use Each Unit</h3>
<pre><code>/* Font sizes: rem */
h1 { font-size: 2.5rem; }
body { font-size: 1rem; }

/* Spacing (margin, padding): rem for page-level, em for component-level */
section { padding: 4rem 2rem; }   /* Page spacing in rem */
.btn {
  padding: 0.75em 1.5em;          /* Button padding scales with button font-size */
  font-size: 1rem;                 /* Default */
}
.btn-large {
  font-size: 1.25rem;              /* Just change font-size — padding scales automatically! */
}

/* Borders: px (stays crisp at any font size) */
.card { border: 1px solid #e5e7eb; }

/* Widths: % or rem */
.container { max-width: 1200px; }   /* px for fixed containers */
.sidebar { width: 30%; }            /* % for flexible widths */
.text { max-width: 65ch; }          /* ch for readable line lengths */

/* Viewport units */
.hero {
  height: 100vh;          /* Full screen hero */
  width: 100vw;           /* Full screen width */
}

/* Fluid typography with clamp() */
h1 {
  font-size: clamp(1.75rem, 4vw, 3rem);
  /* Minimum: 1.75rem, preferred: 4vw, maximum: 3rem */
  /* Scales smoothly between viewport sizes */
}</code></pre>

<h3>Viewport Units for Responsive Layouts</h3>
<pre><code>.hero {
  height: 100vh;                   /* Full screen height */
  background-color: #1a1a2e;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Issue: 100vh on mobile includes the browser UI (address bar) */
/* Modern fix: */
.hero {
  height: 100svh;  /* svh = small viewport height (excludes browser UI) */
}

/* Full-width container */
.full-width {
  width: 100%;     /* Relative to parent */
  /* or */
  width: 100vw;    /* Relative to screen — careful: causes horizontal scroll */
  margin-left: calc(-50vw + 50%);  /* Break out of container properly */
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Using <code>px</code> for font sizes</strong> — If a user sets their browser to 20px default (common for accessibility), <code>px</code> font sizes won't respect their setting. <code>rem</code> will.</li>
  <li><strong>Confusing <code>em</code> compounding</strong> — If you nest elements and use <code>em</code>, sizes compound: a child at <code>1.2em</code> inside a parent at <code>1.2em</code> is 1.44× the base. Prefer <code>rem</code> to avoid surprises.</li>
  <li><strong>Using <code>100vw</code> for a container</strong> — <code>100vw</code> includes the scrollbar width. On pages with a scrollbar, it causes a small horizontal overflow. Use <code>100%</code> instead.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Set your HTML base font size to 16px. Build a responsive type scale where h1 is 2.5rem, h2 is 2rem, h3 is 1.5rem, and body is 1rem. Create a hero section that is exactly 100vh tall. Create a text block with max-width: 65ch. Resize your browser window and watch everything scale.</div>
CSS,5]);

echo "  [✓] CSS Module 1 lessons\n";

// ── CSS Module 2 ─────────────────────────────────────────
$ins('INSERT INTO modules(id,course_id,title,description,order_index)VALUES(?,?,?,?,?)',
    [6,2,'Layout with Flexbox',
     'Master Flexbox — the CSS layout model that makes building navigation bars, card grids, and centred layouts simple.',2]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[26,6,'Introduction to Flexbox',<<<'CSS'
<h2>Introduction to Flexbox</h2>

<p>Before Flexbox, centering something vertically in CSS was notoriously difficult. Web developers wrote hacks involving absolute positioning, negative margins, and table cells. Flexbox (Flexible Box Layout) changed everything. Today it is the most-used CSS layout tool and is supported in every modern browser.</p>

<h3>What Problem Does Flexbox Solve?</h3>
<p>Flexbox is designed for <strong>one-dimensional layouts</strong> — arranging items in a single row OR a single column. It excels at:</p>
<ul>
  <li>Navigation bars (items in a row, spaced apart)</li>
  <li>Card rows (equal-height cards)</li>
  <li>Centring anything — vertically and horizontally</li>
  <li>Distributing space between items</li>
  <li>Controlling item order without changing HTML</li>
</ul>

<h3>Activating Flexbox</h3>
<p>To use Flexbox, add <code>display: flex</code> to the <strong>parent container</strong>. The direct children of that container become <strong>flex items</strong>.</p>
<pre><code>&lt;!-- HTML --&gt;
&lt;div class="navbar"&gt;       &lt;!-- FLEX CONTAINER --&gt;
  &lt;a href="/"&gt;Home&lt;/a&gt;    &lt;!-- flex item --&gt;
  &lt;a href="/about"&gt;About&lt;/a&gt;  &lt;!-- flex item --&gt;
  &lt;a href="/contact"&gt;Contact&lt;/a&gt;  &lt;!-- flex item --&gt;
&lt;/div&gt;

/* CSS */
.navbar {
  display: flex;   /* This one line makes all children flex items */
}</code></pre>

<p>Before <code>display: flex</code>: the links stack vertically (they are block elements as part of a block container).<br>
After <code>display: flex</code>: the links appear side by side in a row.</p>

<h3>The Two Axes</h3>
<p>Flexbox has two axes:</p>
<ul>
  <li><strong>Main axis</strong> — The direction items flow. By default, this is horizontal (left to right).</li>
  <li><strong>Cross axis</strong> — Perpendicular to the main axis. By default, this is vertical.</li>
</ul>
<pre><code>/* Default: items in a row (main axis = horizontal) */
.container { display: flex; }

/* flex-direction: column = items stack vertically (main axis = vertical) */
.container { display: flex; flex-direction: column; }</code></pre>

<h3>Instant Centring — The Famous Flexbox Trick</h3>
<pre><code>/* Centre a child both horizontally AND vertically */
.parent {
  display: flex;
  justify-content: center;   /* Centre on main axis (horizontal) */
  align-items: center;       /* Centre on cross axis (vertical) */
  height: 100vh;             /* Must have a defined height */
}

/* This took 3 lines. Before Flexbox it took 10+ lines of hacky CSS. */</code></pre>

<h3>Your First Flexbox Layout</h3>
<pre><code>&lt;!-- A navigation bar --&gt;
&lt;header class="site-header"&gt;
  &lt;a href="/" class="logo"&gt;HackathonAfrica&lt;/a&gt;
  &lt;nav class="main-nav"&gt;
    &lt;a href="/courses"&gt;Courses&lt;/a&gt;
    &lt;a href="/about"&gt;About&lt;/a&gt;
    &lt;a href="/login"&gt;Log In&lt;/a&gt;
  &lt;/nav&gt;
&lt;/header&gt;</code></pre>
<pre><code>.site-header {
  display: flex;
  justify-content: space-between;  /* Logo left, nav right */
  align-items: center;             /* Vertically centred */
  padding: 1rem 2rem;
  background-color: #1a1a2e;
}

.main-nav {
  display: flex;              /* Nav links in a row */
  gap: 2rem;                  /* Space between links */
}

.main-nav a {
  color: white;
  text-decoration: none;
  font-weight: 500;
}

.logo {
  color: #f59e0b;
  font-weight: 700;
  font-size: 1.25rem;
  text-decoration: none;
}</code></pre>

<h3>The gap Property</h3>
<p>The <code>gap</code> property adds space between flex items — much cleaner than using margins:</p>
<pre><code>.container {
  display: flex;
  gap: 1rem;           /* 1rem between all items */
  gap: 1rem 2rem;      /* row-gap column-gap */
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Applying flex properties to the wrong element</strong> — <code>display: flex</code> goes on the <em>parent</em>. <code>flex-grow</code>, <code>flex-shrink</code>, <code>align-self</code> go on the <em>child</em>.</li>
  <li><strong>Not defining a height when vertically centering</strong> — <code>align-items: center</code> only works if the container has a defined height.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a header with a logo on the left and navigation links on the right using <code>justify-content: space-between</code>. Then build a full-screen centred hero section with a heading and button, using <code>display: flex; align-items: center; justify-content: center</code> on a 100vh container.</div>
CSS,1]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[27,6,'Flex Container Properties',<<<'CSS'
<h2>Flex Container Properties</h2>

<p>In the previous lesson you saw <code>display: flex</code> in action. Now let's go deep on all the properties you can apply to the flex container — the parent element — to control how items are arranged.</p>

<h3>flex-direction — Which Way Items Flow</h3>
<pre><code>.container {
  display: flex;
  flex-direction: row;            /* DEFAULT: items left to right */
  flex-direction: row-reverse;    /* Items right to left */
  flex-direction: column;         /* Items top to bottom */
  flex-direction: column-reverse; /* Items bottom to top */
}</code></pre>
<p>When you change <code>flex-direction</code> to <code>column</code>, the <strong>main axis becomes vertical</strong> — so <code>justify-content</code> now controls vertical alignment, and <code>align-items</code> controls horizontal alignment. This trips up many beginners.</p>

<h3>justify-content — Spacing Along the Main Axis</h3>
<pre><code>.container {
  display: flex;
  justify-content: flex-start;    /* DEFAULT: items at the start */
  justify-content: flex-end;      /* Items at the end */
  justify-content: center;        /* Items centred */
  justify-content: space-between; /* First item at start, last at end, even gaps between */
  justify-content: space-around;  /* Equal space around each item */
  justify-content: space-evenly;  /* Equal space between and around all items */
}</code></pre>

<p>A visual guide to <code>justify-content</code>:</p>
<pre><code>/* flex-start:   [A] [B] [C]                  */
/* flex-end:                [A] [B] [C]       */
/* center:           [A] [B] [C]              */
/* space-between:[A]       [B]       [C]      */
/* space-around: ░[A]░░░░[B]░░░░[C]░         */
/* space-evenly: ░░[A]░░░[B]░░░[C]░░         */</code></pre>

<h3>align-items — Alignment on the Cross Axis</h3>
<pre><code>.container {
  display: flex;
  align-items: stretch;     /* DEFAULT: items stretch to fill container height */
  align-items: flex-start;  /* Items align to top */
  align-items: flex-end;    /* Items align to bottom */
  align-items: center;      /* Items vertically centred */
  align-items: baseline;    /* Items aligned by their text baseline */
}</code></pre>

<h3>flex-wrap — Handling Too Many Items</h3>
<p>By default, flex items try to fit on one line, squishing if necessary. <code>flex-wrap: wrap</code> lets them overflow to the next line:</p>
<pre><code>.card-grid {
  display: flex;
  flex-wrap: wrap;        /* Items wrap to next line when there's no room */
  gap: 1.5rem;
}

/* With flex-wrap: wrap, you can control minimum item width */
.card {
  flex: 1 1 300px;  /* Grow, shrink, minimum 300px wide */
  /* Result: as many 300px cards per row as fit */
}</code></pre>

<h3>align-content — When There Are Multiple Rows</h3>
<pre><code>/* Only applies when flex-wrap: wrap and there are multiple rows */
.container {
  display: flex;
  flex-wrap: wrap;
  align-content: flex-start;    /* Rows at the top */
  align-content: center;         /* Rows centred vertically */
  align-content: space-between;  /* First row at top, last at bottom, even gaps */
}</code></pre>

<h3>Real-World: A Responsive Card Grid</h3>
<pre><code>&lt;div class="card-grid"&gt;
  &lt;article class="card"&gt;...&lt;/article&gt;
  &lt;article class="card"&gt;...&lt;/article&gt;
  &lt;article class="card"&gt;...&lt;/article&gt;
  &lt;article class="card"&gt;...&lt;/article&gt;
  &lt;article class="card"&gt;...&lt;/article&gt;
&lt;/div&gt;</code></pre>
<pre><code>.card-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  padding: 2rem;
}

.card {
  flex: 1 1 280px;        /* Grow and shrink freely, minimum 280px */
  max-width: 380px;       /* Don't grow too wide on large screens */
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column; /* Stack content inside card vertically */
}

.card-footer {
  margin-top: auto;       /* Pushes footer to bottom regardless of card height */
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Forgetting that <code>flex-direction: column</code> swaps the axes</strong> — In column direction, <code>justify-content</code> controls vertical spacing and <code>align-items</code> controls horizontal alignment.</li>
  <li><strong>Using <code>flex-wrap</code> without <code>gap</code></strong> — Without gap, wrapped items have no space between rows.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a card grid where cards are minimum 250px wide. Use <code>flex-wrap: wrap</code> so cards fill the row then move to the next line. Use <code>gap</code> for spacing. Inside each card, use <code>flex-direction: column</code> and <code>margin-top: auto</code> on the last element to push buttons to the bottom of each card, even if card heights differ.</div>
CSS,2]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[28,6,'Flex Item Properties',<<<'CSS'
<h2>Flex Item Properties</h2>

<p>The previous lesson covered properties on the flex <em>container</em>. This lesson covers properties applied directly to flex <em>items</em> — the children. These give you fine-grained control over how each individual item grows, shrinks, and aligns.</p>

<h3>flex-grow — Can This Item Grow?</h3>
<p><code>flex-grow</code> determines how much extra space an item takes up relative to its siblings. The default is <code>0</code> (don't grow).</p>
<pre><code>.item-a { flex-grow: 1; }  /* Gets 1 share of extra space */
.item-b { flex-grow: 2; }  /* Gets 2 shares — twice as much as item-a */
.item-c { flex-grow: 0; }  /* DEFAULT — doesn't grow, keeps its natural size */

/* If a container has 300px of extra space:
   item-a gets 300 × (1/3) = 100px extra
   item-b gets 300 × (2/3) = 200px extra
   item-c gets 0px extra */</code></pre>

<h3>flex-shrink — Can This Item Shrink?</h3>
<p><code>flex-shrink</code> determines how much an item shrinks when there is not enough space. Default is <code>1</code> (shrink proportionally).</p>
<pre><code>.item { flex-shrink: 1; }   /* DEFAULT — can shrink */
.logo { flex-shrink: 0; }   /* Don't shrink — logo must stay full size */</code></pre>

<h3>flex-basis — The Starting Size</h3>
<p><code>flex-basis</code> sets the item's initial size along the main axis, before growing or shrinking.</p>
<pre><code>.item { flex-basis: 200px; }   /* Start at 200px, then grow/shrink from there */
.item { flex-basis: 33.333%; } /* Start at 1/3 of the container */
.item { flex-basis: auto; }    /* DEFAULT — use the item's content size */</code></pre>

<h3>The flex Shorthand</h3>
<p>In practice, you almost always use the <code>flex</code> shorthand which sets grow, shrink, and basis together:</p>
<pre><code>.item { flex: 1; }              /* flex-grow: 1; flex-shrink: 1; flex-basis: 0% */
.item { flex: 0 0 200px; }      /* Don't grow, don't shrink, start at 200px */
.item { flex: 1 1 200px; }      /* Can grow and shrink, start at 200px */
.item { flex: none; }           /* 0 0 auto — size is fixed, no growing/shrinking */

/* Common patterns: */
.fill   { flex: 1; }     /* Fill all available space */
.fixed  { flex: none; }  /* Never grow or shrink */</code></pre>

<h3>align-self — Overriding the Container's align-items</h3>
<pre><code>.container {
  display: flex;
  align-items: center;  /* All items centred by default */
}

.special-item {
  align-self: flex-end;  /* This item aligns to the bottom, overriding the container */
}</code></pre>

<h3>order — Reordering Items Without Changing HTML</h3>
<pre><code>.container { display: flex; }

/* Default order is 0. Lower numbers appear first. */
.item-a { order: 2; }  /* Appears last */
.item-b { order: 0; }  /* DEFAULT — appears first */
.item-c { order: 1; }  /* Appears second */

/* Use case: show a sidebar before main content on mobile,
   but swap them for desktop using a media query */</code></pre>
<p class="alert alert-warning mt-2"><strong>Warning:</strong> <code>order</code> only changes <em>visual</em> order, not DOM order. Screen readers still follow the DOM. Don't use it in a way that creates a confusing mismatch between visual and reading order.</p>

<h3>Real-World: A Sidebar Layout</h3>
<pre><code>&lt;main class="page-layout"&gt;
  &lt;article class="main-content"&gt;...&lt;/article&gt;
  &lt;aside class="sidebar"&gt;...&lt;/aside&gt;
&lt;/main&gt;</code></pre>
<pre><code>.page-layout {
  display: flex;
  gap: 2rem;
  align-items: flex-start;  /* Sidebar doesn't stretch to full height */
}

.main-content {
  flex: 1;         /* Takes all remaining space */
}

.sidebar {
  flex: 0 0 280px; /* Fixed 280px width, never grows or shrinks */
  position: sticky;
  top: 2rem;       /* Stays visible as you scroll */
}</code></pre>

<h3>Common Mistakes</h3>
<ul>
  <li><strong>Setting <code>flex-grow: 1</code> when you want equal widths</strong> — When flex-basis is auto and items have different content, they won't be equal. Use <code>flex: 1 1 0</code> (flex-basis: 0) to give equal extra space from zero.</li>
  <li><strong>Forgetting <code>flex-shrink: 0</code> on logos and icons</strong> — Without it, they squish when space is tight.</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build a two-column layout: a fixed 280px sidebar (<code>flex: 0 0 280px</code>) and a main content area that fills the rest (<code>flex: 1</code>). Add sticky positioning to the sidebar. Then try a three-column equal layout using <code>flex: 1 1 0</code> on all three.</div>
CSS,3]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[29,6,'Common Flexbox Patterns',<<<'CSS'
<h2>Common Flexbox Patterns</h2>

<p>Flexbox shines when applied to recurring UI patterns. In this lesson you will build five of the most common layouts you will encounter as a web developer, using nothing but Flexbox.</p>

<h3>Pattern 1: Horizontal Navigation Bar</h3>
<pre><code>&lt;header class="header"&gt;
  &lt;a href="/" class="brand"&gt;HackathonAfrica&lt;/a&gt;
  &lt;nav class="nav"&gt;
    &lt;a href="/courses"&gt;Courses&lt;/a&gt;
    &lt;a href="/about"&gt;About&lt;/a&gt;
    &lt;a href="/login" class="btn"&gt;Log In&lt;/a&gt;
  &lt;/nav&gt;
&lt;/header&gt;</code></pre>
<pre><code>.header {
  display: flex;
  justify-content: space-between;  /* Brand left, nav right */
  align-items: center;
  padding: 1rem 2rem;
  background: #1a1a2e;
}

.nav {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav a { color: white; text-decoration: none; }
.nav .btn {
  background: #f59e0b;
  color: #1a1a2e;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  font-weight: 600;
}</code></pre>

<h3>Pattern 2: Perfect Centring (Modal, Hero)</h3>
<pre><code>.modal-overlay {
  position: fixed;
  inset: 0;                      /* top: 0; right: 0; bottom: 0; left: 0 */
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  max-width: 500px;
  width: 100%;
}</code></pre>

<h3>Pattern 3: Card Grid with Equal-Height Cards</h3>
<pre><code>.card-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.card {
  flex: 1 1 280px;
  max-width: 380px;
  display: flex;
  flex-direction: column;    /* Stack card contents vertically */
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;          /* Clip image to border-radius */
}

.card-body {
  flex: 1;                   /* Stretches to fill available space */
  padding: 1.5rem;
}

.card-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  margin-top: auto;          /* Always at the bottom */
}</code></pre>

<h3>Pattern 4: Holy Grail Layout (Header, Sidebar, Content, Footer)</h3>
<pre><code>.page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;          /* Full viewport height */
}

.page-header { /* header styles */ }

.page-body {
  display: flex;
  flex: 1;                    /* Grows to fill space between header and footer */
  gap: 2rem;
  padding: 2rem;
}

.sidebar    { flex: 0 0 250px; }    /* Fixed width sidebar */
.main       { flex: 1; }            /* Takes remaining space */
.page-footer { /* footer styles */ }</code></pre>

<h3>Pattern 5: Media Object (Image + Text Side by Side)</h3>
<p>This pattern is everywhere — user profiles, blog post summaries, search results:</p>
<pre><code>&lt;div class="media"&gt;
  &lt;img class="media-img" src="avatar.jpg" alt="Kwame Asante"&gt;
  &lt;div class="media-body"&gt;
    &lt;h3&gt;Kwame Asante&lt;/h3&gt;
    &lt;p&gt;Full-stack developer from Accra, Ghana...&lt;/p&gt;
  &lt;/div&gt;
&lt;/div&gt;</code></pre>
<pre><code>.media {
  display: flex;
  gap: 1rem;
  align-items: flex-start;    /* Image stays at top even if body is tall */
}

.media-img {
  flex: 0 0 64px;             /* Fixed 64px, never shrinks */
  width: 64px;
  height: 64px;
  border-radius: 50%;         /* Circle */
  object-fit: cover;          /* Crop image to fit without distorting */
}

.media-body {
  flex: 1;                    /* Takes remaining width */
}

.media-body h3 { margin: 0 0 0.25rem; }
.media-body p  { margin: 0; color: #6b7280; }</code></pre>

<h3>Quick Reference</h3>
<table class="table table-bordered table-sm mt-2">
  <thead class="table-dark"><tr><th>Pattern</th><th>Key Properties</th></tr></thead>
  <tbody>
    <tr><td>Nav bar</td><td><code>justify-content: space-between; align-items: center</code></td></tr>
    <tr><td>Centred content</td><td><code>justify-content: center; align-items: center</code></td></tr>
    <tr><td>Equal columns</td><td><code>flex: 1</code> on each item</td></tr>
    <tr><td>Fixed + fluid</td><td>Fixed: <code>flex: 0 0 Xpx</code>, fluid: <code>flex: 1</code></td></tr>
    <tr><td>Equal height cards</td><td><code>flex-direction: column</code> on card + <code>flex: 1</code> on card body</td></tr>
  </tbody>
</table>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build all five patterns on the same page. Use each pattern for a real section: a proper header nav, a hero section centred on the screen, a features section with 3 equal cards, a 2-column body layout, and a team section using the media object pattern for each team member.</div>
CSS,4]);

$ins('INSERT INTO lessons(id,module_id,title,content,order_index)VALUES(?,?,?,?,?)',[30,6,'Responsive Navigation with Flexbox',<<<'CSS'
<h2>Responsive Navigation with Flexbox</h2>

<p>Building a navigation bar that works on both desktop (horizontal) and mobile (hamburger menu) is one of the most practical skills in frontend development. In this lesson you will build a fully responsive navigation from scratch.</p>

<h3>The Strategy: Mobile-First</h3>
<p>We build the mobile layout first (stacked, simple), then use a media query to enhance it for desktop. This approach is called <strong>mobile-first design</strong> and it is the industry standard.</p>

<h3>The HTML Structure</h3>
<pre><code>&lt;header class="site-header"&gt;
  &lt;div class="header-inner"&gt;

    &lt;!-- Logo --&gt;
    &lt;a href="/" class="site-logo"&gt;HackathonAfrica&lt;/a&gt;

    &lt;!-- Hamburger button (visible on mobile only) --&gt;
    &lt;button class="nav-toggle" aria-expanded="false" aria-controls="main-nav"
            aria-label="Toggle navigation"&gt;
      &lt;span&gt;&lt;/span&gt;  &lt;!-- These become the hamburger lines with CSS --&gt;
      &lt;span&gt;&lt;/span&gt;
      &lt;span&gt;&lt;/span&gt;
    &lt;/button&gt;

    &lt;!-- Navigation links --&gt;
    &lt;nav id="main-nav" class="main-nav"&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/courses"&gt;Courses&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="/login"&gt;Log In&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/nav&gt;

  &lt;/div&gt;
&lt;/header&gt;</code></pre>

<h3>The CSS — Mobile First</h3>
<pre><code>/* ── Base reset ──────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

/* ── Header wrapper ──────────────────────────── */
.site-header {
  background: #1a1a2e;
  color: white;
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* ── Logo ────────────────────────────────────── */
.site-logo {
  color: #f59e0b;
  text-decoration: none;
  font-weight: 700;
  font-size: 1.25rem;
}

/* ── MOBILE: Nav hidden by default ──────────── */
.main-nav {
  display: none;             /* Hidden on mobile */
  position: absolute;
  top: 100%;                 /* Drops below the header */
  left: 0;
  right: 0;
  background: #1a1a2e;
  padding: 1rem;
  border-top: 1px solid rgba(255,255,255,0.1);
}

.main-nav.is-open {
  display: block;            /* Shown when hamburger is clicked */
}

.main-nav ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.main-nav a {
  display: block;
  padding: 0.75rem 1rem;
  color: white;
  text-decoration: none;
  border-radius: 6px;
}

.main-nav a:hover {
  background: rgba(255,255,255,0.1);
}

/* ── Hamburger button ────────────────────────── */
.nav-toggle {
  display: flex;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
}

.nav-toggle span {
  display: block;
  width: 24px;
  height: 2px;
  background: white;
  border-radius: 2px;
  transition: 0.3s;
}

/* ── DESKTOP: override mobile styles ─────────── */
@media (min-width: 768px) {
  .nav-toggle { display: none; }     /* Hide hamburger */

  .main-nav {
    display: block;                   /* Always visible */
    position: static;                 /* Back to normal flow */
    background: none;
    padding: 0;
    border-top: none;
  }

  .main-nav ul {
    flex-direction: row;              /* Links in a row */
    gap: 0.5rem;
    align-items: center;
  }

  .main-nav a {
    padding: 0.5rem 0.75rem;
  }
}</code></pre>

<h3>The JavaScript to Toggle the Menu</h3>
<pre><code>&lt;script&gt;
const toggle = document.querySelector('.nav-toggle');
const nav = document.querySelector('.main-nav');

toggle.addEventListener('click', () => {
  const isOpen = nav.classList.toggle('is-open');
  toggle.setAttribute('aria-expanded', isOpen);
});
&lt;/script&gt;</code></pre>

<h3>Key Concepts in This Pattern</h3>
<ul>
  <li><code>position: sticky; top: 0</code> on the header — stays visible while scrolling</li>
  <li><code>z-index: 100</code> — ensures the header appears above all page content</li>
  <li><code>position: absolute; top: 100%</code> on the mobile nav — drops it below the header</li>
  <li><code>aria-expanded</code> on the button — tells screen readers if the menu is open or closed</li>
  <li><code>max-width + margin: 0 auto</code> — centres the header content on wide screens</li>
</ul>

<div class="alert alert-info mt-3"><strong>Try This:</strong> Build this navigation from scratch. Test it at mobile width (resize the browser to under 768px) — you should see the hamburger and hidden nav. Then expand the window past 768px — the hamburger disappears and nav links appear horizontally. Add an active style for the current page link.</div>
CSS,5]);

echo "  [✓] CSS Module 2 lessons\n";
