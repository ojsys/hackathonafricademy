<?php
/**
 * Migration: Add 4 missing HTML lessons (IDs 61-64)
 * Run once: php database/add_html_lessons.php
 */
$pdo = new PDO('sqlite:' . __DIR__ . '/lms.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$addLesson = function(int $id, int $moduleId, string $title, string $content, int $order) use ($pdo) {
    $existing = $pdo->prepare("SELECT id FROM lessons WHERE id = ?");
    $existing->execute([$id]);
    if ($existing->fetch()) { echo "Lesson $id already exists, skipping.\n"; return; }
    $stmt = $pdo->prepare("INSERT INTO lessons (id, module_id, title, content, order_index, estimated_minutes) VALUES (?, ?, ?, ?, ?, 20)");
    $stmt->execute([$id, $moduleId, $title, $content, $order]);
    echo "Added: [$id] $title\n";
};

// ===========================================================================
// LESSON 61 — HTML Entities & Special Characters (Module 1, order 6)
// ===========================================================================
$c61 = <<<'HTML'
<h2>HTML Entities & Special Characters</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of entities like escape codes...</h4>
    <p>HTML uses &lt; and &gt; to define tags. If you write those characters inside your text content, the browser may misread them as a broken tag. Entities are special codes that let you display reserved characters safely — the browser always understands them correctly.</p>
</div>

<h3>Why Entities Exist</h3>
<p>The characters <code>&lt;</code>, <code>&gt;</code>, and <code>&amp;</code> have special meaning in HTML. To display them as visible text rather than markup, you replace them with their entity code.</p>

<div class="code-block">
<pre><code>&lt;!-- WRONG — browser may misparse the less-than sign --&gt;
&lt;p&gt;5 &lt; 10 and 10 &gt; 5&lt;/p&gt;

&lt;!-- CORRECT — use entities --&gt;
&lt;p&gt;5 &amp;lt; 10 and 10 &amp;gt; 5&lt;/p&gt;
&lt;!-- Displays: 5 < 10 and 10 > 5 --&gt;</code></pre>
</div>

<h3>The Essential Entities</h3>
<div class="code-block">
<pre><code>&lt;!-- Reserved HTML characters --&gt;
&amp;lt;      →  &lt;   (less-than sign)
&amp;gt;      →  &gt;   (greater-than sign)
&amp;amp;     →  &amp;   (ampersand — always encode this in text!)
&amp;quot;    →  "   (double quote — useful inside attributes)
&amp;apos;    →  '   (apostrophe / single quote)

&lt;!-- Non-breaking space --&gt;
&amp;nbsp;    →      (keeps two words on the same line)

&lt;!-- Copyright, trademark, symbols --&gt;
&amp;copy;    →  ©   (copyright)
&amp;reg;     →  ®   (registered trademark)
&amp;trade;   →  ™   (trademark)
&amp;deg;     →  °   (degree — for temperature)
&amp;euro;    →  €   (Euro sign)
&amp;pound;   →  £   (Pound sign)

&lt;!-- Typography --&gt;
&amp;mdash;   →  —   (em dash — for sentence breaks)
&amp;ndash;   →  –   (en dash — for ranges like 2020–2024)
&amp;hellip;  →  …   (ellipsis — three dots)
&amp;laquo;   →  «   (left guillemet)
&amp;raquo;   →  »   (right guillemet)</code></pre>
</div>

<h3>Non-Breaking Space (&amp;nbsp;) — Use It Wisely</h3>
<p>A normal space can cause a line break between words at the end of a line. <code>&amp;nbsp;</code> keeps two words glued together on the same line.</p>

<div class="code-block">
<pre><code>&lt;!-- "Mr." and "Okafor" stay on the same line --&gt;
&lt;p&gt;Good morning, Mr.&amp;nbsp;Okafor!&lt;/p&gt;

&lt;!-- Phone number stays together --&gt;
&lt;p&gt;Call us: 080&amp;nbsp;1234&amp;nbsp;5678&lt;/p&gt;

&lt;!-- Temperature: number and degree stay together --&gt;
&lt;p&gt;Today it is 35&amp;deg;C outside.&lt;/p&gt;</code></pre>
</div>

<h3>Numeric Entities (Alternative Syntax)</h3>
<p>Every entity can also be written as a Unicode number. Both forms produce the same result — named entities are easier to read.</p>

<div class="code-block">
<pre><code>&lt;!-- Named vs numeric — identical output --&gt;
&amp;copy;    =  &amp;#169;   →  ©
&amp;amp;     =  &amp;#38;    →  &amp;
&amp;lt;      =  &amp;#60;    →  &lt;
&amp;hearts;  =  &amp;#9829;  →  ♥
&amp;star;    =  &amp;#9733;  →  ★</code></pre>
</div>

<h3>Real-World Examples</h3>
<div class="code-block">
<pre><code>&lt;!-- Footer copyright --&gt;
&lt;footer&gt;
    &lt;p&gt;&amp;copy; 2024 HackathonAfrica. All rights reserved.&lt;/p&gt;
&lt;/footer&gt;

&lt;!-- Showing HTML code in a tutorial --&gt;
&lt;p&gt;To create a link, write: &amp;lt;a href="url"&amp;gt;text&amp;lt;/a&amp;gt;&lt;/p&gt;

&lt;!-- Science and maths --&gt;
&lt;p&gt;Water is H&lt;sub&gt;2&lt;/sub&gt;O. Boiling point: 100&amp;deg;C.&lt;/p&gt;

&lt;!-- Prices in multiple currencies --&gt;
&lt;p&gt;Price: &amp;pound;9.99 or &amp;euro;11.50&lt;/p&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting the semicolon</strong> — <code>&amp;amp</code> is broken; <code>&amp;amp;</code> is correct. The semicolon is required.</li>
        <li><strong>Using &amp;nbsp; for layout spacing</strong> — use CSS margin/padding instead. &amp;nbsp; is for meaning, not design.</li>
        <li><strong>Not encoding &amp; in attribute values</strong> — write <code>href="page.php?a=1&amp;amp;b=2"</code> not <code>href="page.php?a=1&b=2"</code></li>
        <li><strong>Writing &lt; directly in text</strong> — always use <code>&amp;lt;</code> when you mean the literal less-than character in content</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li>Use <code>&amp;lt;</code> and <code>&amp;gt;</code> to display angle brackets as visible text</li>
        <li>Always encode <code>&amp;</code> as <code>&amp;amp;</code> in HTML content and attribute values</li>
        <li><code>&amp;nbsp;</code> prevents a line break between two words</li>
        <li><code>&amp;copy;</code>, <code>&amp;mdash;</code>, <code>&amp;deg;</code> are handy for real-world content</li>
        <li>Every entity starts with <code>&amp;</code> and ends with <code>;</code></li>
    </ul>
</div>
HTML;

$addLesson(61, 1, 'HTML Entities & Special Characters', $c61, 6);

// ===========================================================================
// LESSON 62 — Block vs Inline Elements (Module 1, order 7)
// ===========================================================================
$c62 = <<<'HTML'
<h2>Block vs Inline Elements</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of it like furniture vs decorations...</h4>
    <p>A wardrobe (block element) takes up the full width of a wall — nothing else can sit beside it on the same row. A picture frame (inline element) sits on the wall next to other frames — they share the same row. This distinction is one of the most fundamental concepts in HTML and CSS.</p>
</div>

<h3>Block-Level Elements</h3>
<p>Block elements always start on a new line and stretch to fill the full width of their container by default.</p>

<div class="code-block">
<pre><code>&lt;!-- Each of these forces a new line --&gt;
&lt;div&gt;Generic block container&lt;/div&gt;
&lt;p&gt;A paragraph&lt;/p&gt;
&lt;h1&gt;A heading&lt;/h1&gt;
&lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;
&lt;table&gt;, &lt;form&gt;
&lt;header&gt;, &lt;main&gt;, &lt;section&gt;, &lt;article&gt;, &lt;footer&gt;
&lt;blockquote&gt;, &lt;pre&gt;, &lt;figure&gt;

&lt;!-- Box 1 and Box 2 appear on SEPARATE lines --&gt;
&lt;div&gt;Box 1&lt;/div&gt;
&lt;div&gt;Box 2&lt;/div&gt;</code></pre>
</div>

<h3>Inline Elements</h3>
<p>Inline elements flow within text — they only take up as much width as their content and do not force a new line.</p>

<div class="code-block">
<pre><code>&lt;!-- These sit WITHIN a line of text --&gt;
&lt;span&gt;Generic inline container&lt;/span&gt;
&lt;a&gt;Link&lt;/a&gt;
&lt;strong&gt;Bold&lt;/strong&gt;
&lt;em&gt;Italic&lt;/em&gt;
&lt;img&gt;     &lt;!-- img is inline! --&gt;
&lt;code&gt;, &lt;mark&gt;, &lt;abbr&gt;, &lt;label&gt;, &lt;button&gt;, &lt;input&gt;

&lt;!-- All four sit on the same line naturally --&gt;
&lt;p&gt;
    I &lt;strong&gt;love&lt;/strong&gt; learning
    &lt;em&gt;HTML&lt;/em&gt; at
    &lt;a href="#"&gt;HackathonAfrica&lt;/a&gt;!
&lt;/p&gt;</code></pre>
</div>

<h3>Side-by-Side Comparison</h3>
<div class="code-block">
<pre><code>&lt;!-- BLOCK: each on its own row, full width --&gt;
&lt;p&gt;First paragraph&lt;/p&gt;
&lt;p&gt;Second paragraph&lt;/p&gt;

&lt;!-- INLINE: sit together on one line --&gt;
&lt;p&gt;Price: &lt;strong&gt;FREE&lt;/strong&gt; — join &lt;a href="/register"&gt;today&lt;/a&gt;!&lt;/p&gt;

&lt;!-- WRONG — block element inside an inline element --&gt;
&lt;span&gt;&lt;p&gt;Invalid HTML!&lt;/p&gt;&lt;/span&gt;  ❌

&lt;!-- CORRECT — inline inside block is fine --&gt;
&lt;p&gt;&lt;span&gt;This is fine!&lt;/span&gt;&lt;/p&gt;  ✓

&lt;!-- WRONG — &lt;p&gt; can only hold inline content --&gt;
&lt;p&gt;&lt;div&gt;A div inside a p&lt;/div&gt;&lt;/p&gt;  ❌</code></pre>
</div>

<h3>The &lt;div&gt; and &lt;span&gt; Pair</h3>
<p>These two elements have no semantic meaning — they are pure containers used solely for grouping and styling with CSS.</p>

<div class="code-block">
<pre><code>&lt;!-- div = block container --&gt;
&lt;div class="card"&gt;
    &lt;h3&gt;Card Title&lt;/h3&gt;
    &lt;p&gt;Card content goes here.&lt;/p&gt;
&lt;/div&gt;

&lt;!-- span = inline container for targeting part of text --&gt;
&lt;p&gt;
    The price is &lt;span class="highlight"&gt;FREE&lt;/span&gt; for all students.
&lt;/p&gt;

&lt;!-- Tip: always prefer semantic elements over plain div/span --&gt;
&lt;article&gt; instead of &lt;div class="article"&gt;
&lt;strong&gt;  instead of &lt;span class="bold"&gt;</code></pre>
</div>

<h3>CSS Can Change the Display Type</h3>
<p>The CSS <code>display</code> property lets you override an element's default behaviour — this is how horizontal nav menus and image galleries are built.</p>

<div class="code-block">
<pre><code>&lt;style&gt;
    /* Make inline &lt;a&gt; tags behave as blocks (full-width clickable rows) */
    nav a {
        display: block;
        padding: 10px 16px;
    }

    /* Make block &lt;li&gt; items sit side by side in a row */
    ul.nav-list li {
        display: inline;
        margin-right: 12px;
    }

    /* inline-block: respects width/height but flows inline */
    .badge {
        display: inline-block;
        padding: 4px 8px;
        background: gold;
        border-radius: 4px;
    }

    /* Completely remove from layout */
    .hidden {
        display: none;
    }
&lt;/style&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Nesting &lt;p&gt; inside &lt;span&gt;</strong> — block elements cannot go inside inline elements; this breaks the HTML spec</li>
        <li><strong>Putting &lt;div&gt; inside &lt;p&gt;</strong> — a &lt;p&gt; can only contain inline content, not block elements</li>
        <li><strong>Using &lt;div&gt; for everything</strong> — prefer semantic block elements (&lt;section&gt;, &lt;article&gt;, &lt;header&gt;) when they match the content</li>
        <li><strong>Mystery space below &lt;img&gt;</strong> — because img is inline, it sits on the text baseline causing a small gap; fix with <code>display: block</code> on the img</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><strong>Block elements</strong> start on a new line and fill full width: &lt;div&gt;, &lt;p&gt;, &lt;h1-h6&gt;, &lt;section&gt;</li>
        <li><strong>Inline elements</strong> flow within text and only use needed width: &lt;span&gt;, &lt;a&gt;, &lt;strong&gt;, &lt;img&gt;</li>
        <li>Never nest a block element inside an inline element</li>
        <li>&lt;div&gt; is the generic block container; &lt;span&gt; is the generic inline container</li>
        <li>CSS <code>display</code> property can override any element's default behaviour</li>
    </ul>
</div>
HTML;

$addLesson(62, 1, 'Block vs Inline Elements', $c62, 7);

// ===========================================================================
// LESSON 63 — Interactive Elements: details, summary & dialog (Module 3, order 6)
// ===========================================================================
$c63 = <<<'HTML'
<h2>Interactive Elements: details, summary & dialog</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of these like built-in furniture that comes with your house...</h4>
    <p>You could build a custom accordion from scratch with JavaScript, or you could use the folding door HTML already provides. These elements give you real interactivity — collapsible sections, modal dialogs, progress bars — with zero JavaScript and full accessibility built in.</p>
</div>

<h3>The &lt;details&gt; and &lt;summary&gt; Elements</h3>
<p><code>&lt;details&gt;</code> creates a collapsible section. <code>&lt;summary&gt;</code> is the always-visible heading that the user clicks to expand or collapse. Perfect for FAQs and toggleable content.</p>

<div class="code-block">
<pre><code>&lt;!-- Basic collapsible — no JavaScript needed --&gt;
&lt;details&gt;
    &lt;summary&gt;What is HackathonAfrica?&lt;/summary&gt;
    &lt;p&gt;HackathonAfrica is a programme by AfricaPlan Foundation
    that trains African youth in web development and connects
    them to tech opportunities across the continent.&lt;/p&gt;
&lt;/details&gt;

&lt;!-- Open by default using the "open" attribute --&gt;
&lt;details open&gt;
    &lt;summary&gt;Course Requirements (click to collapse)&lt;/summary&gt;
    &lt;ul&gt;
        &lt;li&gt;Complete all 3 courses&lt;/li&gt;
        &lt;li&gt;Pass all module quizzes&lt;/li&gt;
        &lt;li&gt;Score 70% or above on the final exam&lt;/li&gt;
    &lt;/ul&gt;
&lt;/details&gt;

&lt;!-- Multiple details elements create an accordion-style FAQ --&gt;
&lt;details&gt;
    &lt;summary&gt;Is the course free?&lt;/summary&gt;
    &lt;p&gt;Yes! 100% free for all learners.&lt;/p&gt;
&lt;/details&gt;
&lt;details&gt;
    &lt;summary&gt;How long does it take?&lt;/summary&gt;
    &lt;p&gt;Most learners finish in 4 to 8 weeks at their own pace.&lt;/p&gt;
&lt;/details&gt;</code></pre>
</div>

<h3>The &lt;dialog&gt; Element</h3>
<p><code>&lt;dialog&gt;</code> is a native modal/popup built into HTML. It is keyboard-accessible and focusable by default — no modal library needed.</p>

<div class="code-block">
<pre><code>&lt;!-- Define the dialog --&gt;
&lt;dialog id="confirmDialog"&gt;
    &lt;h3&gt;Submit your exam?&lt;/h3&gt;
    &lt;p&gt;You cannot change your answers after submitting.&lt;/p&gt;
    &lt;div&gt;
        &lt;button id="cancelBtn"&gt;Cancel&lt;/button&gt;
        &lt;button id="confirmBtn"&gt;Yes, Submit&lt;/button&gt;
    &lt;/div&gt;
&lt;/dialog&gt;

&lt;!-- Button that opens the dialog --&gt;
&lt;button id="openBtn"&gt;Submit Exam&lt;/button&gt;

&lt;script&gt;
    const dialog    = document.getElementById("confirmDialog");
    const openBtn   = document.getElementById("openBtn");
    const cancelBtn = document.getElementById("cancelBtn");

    openBtn.addEventListener("click", () =&gt; dialog.showModal());
    cancelBtn.addEventListener("click", () =&gt; dialog.close());
&lt;/script&gt;</code></pre>
</div>

<h3>Modal vs Non-Modal, and the form[method=dialog] Trick</h3>
<div class="code-block">
<pre><code>&lt;!-- showModal() — shows backdrop, blocks background clicks --&gt;
dialog.showModal();

&lt;!-- show() — no backdrop, background still usable --&gt;
dialog.show();

&lt;!-- close() — closes the dialog --&gt;
dialog.close();

&lt;!-- A form with method="dialog" closes the dialog on submit
     without sending data to a server --&gt;
&lt;dialog id="settings"&gt;
    &lt;form method="dialog"&gt;
        &lt;h3&gt;Settings&lt;/h3&gt;
        &lt;label&gt;
            Dark mode
            &lt;input type="checkbox" name="darkMode"&gt;
        &lt;/label&gt;
        &lt;button type="submit"&gt;Save and Close&lt;/button&gt;
    &lt;/form&gt;
&lt;/dialog&gt;</code></pre>
</div>

<h3>The &lt;progress&gt; and &lt;meter&gt; Elements</h3>
<p>Two more native elements that provide visual feedback with no CSS or JavaScript required.</p>

<div class="code-block">
<pre><code>&lt;!-- progress: shows how far along a task is --&gt;
&lt;label&gt;Course completion:&lt;/label&gt;
&lt;progress value="65" max="100"&gt;65%&lt;/progress&gt;

&lt;!-- Indeterminate: unknown end (loading animation) --&gt;
&lt;progress&gt;&lt;/progress&gt;

&lt;!-- meter: measures a value within a known range --&gt;
&lt;label&gt;Quiz score:&lt;/label&gt;
&lt;meter value="78" min="0" max="100" low="40" high="70" optimum="90"&gt;
    78 out of 100
&lt;/meter&gt;
&lt;!-- Browser colours it automatically:
     red = below "low", yellow = between low and high,
     green = near "optimum" --&gt;

&lt;!-- Storage usage example --&gt;
&lt;label&gt;Storage used:&lt;/label&gt;
&lt;meter value="7" min="0" max="10" low="2" high="8" optimum="1"&gt;
    7 GB of 10 GB
&lt;/meter&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Forgetting &lt;summary&gt; inside &lt;details&gt;</strong> — without it the browser defaults to showing "Details" as the label</li>
        <li><strong>Expecting &lt;dialog&gt; to show on its own</strong> — dialogs are hidden by default; you must call <code>.showModal()</code> or <code>.show()</code></li>
        <li><strong>Mixing up &lt;progress&gt; and &lt;meter&gt;</strong> — use progress for tasks (loading, completion); use meter for gauges with an optimal zone</li>
        <li><strong>Building a custom accordion in JavaScript when &lt;details&gt; is enough</strong> — native HTML is simpler, always accessible, and works without scripts</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><code>&lt;details&gt;</code> + <code>&lt;summary&gt;</code> = a collapsible section with zero JavaScript</li>
        <li>Add the <code>open</code> attribute to <code>&lt;details&gt;</code> to start it expanded</li>
        <li><code>&lt;dialog&gt;</code> is a native modal — open it with <code>.showModal()</code>, close it with <code>.close()</code></li>
        <li><code>&lt;progress&gt;</code> shows task completion; <code>&lt;meter&gt;</code> shows a gauge with low / high / optimum zones</li>
        <li>Always prefer native HTML interactive elements over custom JavaScript solutions — they are already accessible</li>
    </ul>
</div>
HTML;

$addLesson(63, 3, 'Interactive Elements: details, summary & dialog', $c63, 6);

// ===========================================================================
// LESSON 64 — Embedding Content: iframes & Responsive Images (Module 4, order 6)
// ===========================================================================
$c64 = <<<'HTML'
<h2>Embedding Content: iframes & Responsive Images</h2>

<div class="analogy-box">
    <h4><i class="bi bi-lightbulb"></i> Think of an iframe like a window in a wall...</h4>
    <p>A window lets you see outside without actually being outside. An iframe is a window in your webpage that shows another website or document inside it. The other page lives in its own separate, sandboxed world — it cannot mess with your page, and your page cannot mess with it.</p>
</div>

<h3>The &lt;iframe&gt; Element</h3>
<p>iframes embed another HTML page inside your page. They are used for YouTube videos, Google Maps, payment widgets, and social media embeds.</p>

<div class="code-block">
<pre><code>&lt;!-- Basic iframe --&gt;
&lt;iframe
    src="https://www.example.com"
    width="600"
    height="400"
    title="Example website"&gt;
    Your browser does not support iframes.
&lt;/iframe&gt;

&lt;!-- Embedding a YouTube video
     Use the /embed/ URL, NOT the /watch?v= URL --&gt;
&lt;iframe
    width="560"
    height="315"
    src="https://www.youtube.com/embed/VIDEO_ID"
    title="Introduction to HTML"
    frameborder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media"
    allowfullscreen&gt;
&lt;/iframe&gt;

&lt;!-- Embedding Google Maps --&gt;
&lt;iframe
    src="https://www.google.com/maps/embed?pb=YOUR_MAP_ID"
    width="600"
    height="450"
    style="border:0;"
    allowfullscreen
    loading="lazy"
    title="Our office on Google Maps"&gt;
&lt;/iframe&gt;</code></pre>
</div>

<h3>The sandbox Attribute — Security First</h3>
<p>Always use <code>sandbox</code> when embedding content you do not fully control. It restricts what the embedded page can do.</p>

<div class="code-block">
<pre><code>&lt;!-- Maximum restriction — almost nothing is permitted --&gt;
&lt;iframe src="user-content.html" sandbox&gt;&lt;/iframe&gt;

&lt;!-- Grant specific permissions only --&gt;
&lt;iframe
    src="quiz-widget.html"
    sandbox="allow-scripts allow-forms"&gt;
    &lt;!-- allow-scripts     → run JavaScript --&gt;
    &lt;!-- allow-forms       → submit forms --&gt;
    &lt;!-- allow-same-origin → access own cookies / storage --&gt;
    &lt;!-- allow-popups      → open new windows --&gt;
&lt;/iframe&gt;

&lt;!-- loading="lazy" delays loading until scrolled into view --&gt;
&lt;iframe src="map.html" loading="lazy" title="Map"&gt;&lt;/iframe&gt;</code></pre>
</div>

<h3>Responsive iframes — Making Them Scale</h3>
<p>iframes have fixed pixel dimensions by default. This CSS technique makes them scale fluidly on any screen size.</p>

<div class="code-block">
<pre><code>&lt;style&gt;
    /* Responsive 16:9 container */
    .video-wrapper {
        position: relative;
        padding-bottom: 56.25%; /* 9 / 16 = 0.5625 */
        height: 0;
        overflow: hidden;
    }
    .video-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
&lt;/style&gt;

&lt;div class="video-wrapper"&gt;
    &lt;iframe
        src="https://www.youtube.com/embed/VIDEO_ID"
        title="My Video"
        allowfullscreen&gt;
    &lt;/iframe&gt;
&lt;/div&gt;</code></pre>
</div>

<h3>Responsive Images with srcset</h3>
<p>A 2 000 px image is wasteful on a phone. <code>srcset</code> lets the browser choose the right file size automatically.</p>

<div class="code-block">
<pre><code>&lt;img
    src="hero-800.jpg"
    srcset="
        hero-400.jpg   400w,
        hero-800.jpg   800w,
        hero-1600.jpg 1600w
    "
    sizes="
        (max-width: 600px)  400px,
        (max-width: 1200px) 800px,
        1600px
    "
    alt="Students coding at HackathonAfrica"
    width="800"
    height="400"&gt;
&lt;!-- The browser picks the best file for the screen size --&gt;</code></pre>
</div>

<h3>The &lt;picture&gt; Element — Full Control</h3>
<p><code>&lt;picture&gt;</code> lets you serve modern formats (WebP) to browsers that support them, and provide a different crop on mobile.</p>

<div class="code-block">
<pre><code>&lt;picture&gt;
    &lt;!-- Modern browsers: serve WebP (smaller file, same quality) --&gt;
    &lt;source
        srcset="hero.webp 1x, hero@2x.webp 2x"
        type="image/webp"&gt;

    &lt;!-- On narrow screens: serve a tighter crop --&gt;
    &lt;source
        media="(max-width: 600px)"
        srcset="hero-mobile.jpg"&gt;

    &lt;!-- Fallback for all other browsers --&gt;
    &lt;img
        src="hero.jpg"
        alt="Students coding at HackathonAfrica"
        width="800"
        height="400"&gt;
&lt;/picture&gt;</code></pre>
</div>

<h3>Lazy Loading Images and iframes</h3>
<div class="code-block">
<pre><code>&lt;!-- loading="lazy" defers off-screen images until scrolled near --&gt;
&lt;img
    src="course-thumbnail.jpg"
    alt="HTML Course thumbnail"
    loading="lazy"
    width="400"
    height="250"&gt;

&lt;!-- Same attribute works on iframes --&gt;
&lt;iframe
    src="https://www.google.com/maps/embed?..."
    loading="lazy"
    title="Our office location"&gt;
&lt;/iframe&gt;

&lt;!-- loading="eager" — load immediately (for above-the-fold hero images) --&gt;
&lt;img src="hero.jpg" alt="Hero image" loading="eager"&gt;</code></pre>
</div>

<div class="mistakes-box">
    <h4><i class="bi bi-exclamation-triangle"></i> Common Beginner Mistakes</h4>
    <ul>
        <li><strong>Missing the title attribute on iframes</strong> — screen readers need it to describe what is embedded</li>
        <li><strong>Using the YouTube watch URL</strong> — use <code>/embed/VIDEO_ID</code>, not <code>/watch?v=VIDEO_ID</code></li>
        <li><strong>No sandbox on user-generated iframes</strong> — a security risk; always sandbox content you do not fully trust</li>
        <li><strong>Omitting width and height on &lt;img&gt;</strong> — causes layout shift as the page loads; always declare dimensions</li>
        <li><strong>Lazy-loading the hero image</strong> — use <code>loading="eager"</code> (or omit the attribute) for the first visible image; only lazy-load images below the fold</li>
    </ul>
</div>

<div class="takeaways-box">
    <h4><i class="bi bi-check-circle"></i> Key Takeaways</h4>
    <ul>
        <li><code>&lt;iframe&gt;</code> embeds another page — use it for YouTube, Maps, and third-party widgets</li>
        <li>Always add a <code>title</code> to iframes and use <code>sandbox</code> for untrusted sources</li>
        <li>Wrap iframes in a CSS container to make them responsive at any aspect ratio</li>
        <li><code>srcset</code> + <code>sizes</code> lets the browser pick the right image resolution automatically</li>
        <li><code>&lt;picture&gt;</code> gives full control: modern formats (WebP) and different crops per screen size</li>
        <li><code>loading="lazy"</code> speeds up the initial page load by deferring off-screen images and iframes</li>
    </ul>
</div>
HTML;

$addLesson(64, 4, 'Embedding Content: iframes & Responsive Images', $c64, 6);

// ===========================================================================
// Summary
// ===========================================================================
echo "\nFinal HTML course structure:\n";
$rows = $pdo->query("
    SELECT m.title as module, l.id, l.title, l.order_index
    FROM lessons l
    JOIN modules m ON m.id = l.module_id
    WHERE m.course_id = 1
    ORDER BY l.module_id, l.order_index
")->fetchAll();
$currentModule = '';
foreach ($rows as $r) {
    if ($r['module'] !== $currentModule) {
        echo "\n" . $r['module'] . "\n";
        $currentModule = $r['module'];
    }
    $new = $r['id'] >= 61 ? ' ← NEW' : '';
    echo "  L" . $r['order_index'] . ": " . $r['title'] . $new . "\n";
}
