-- HackathonAfrica LMS Seed Data
-- Preloaded courses: HTML, CSS, JavaScript Fundamentals
-- Run AFTER schema.sql

-- Default admin account (password: Admin@1234)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@hackathon.africa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Note: The above hash is for 'password'. Change immediately after first login.
-- To generate a real hash: php -r "echo password_hash('Admin@1234', PASSWORD_BCRYPT);"

-- ============================================================
-- COURSE 1: HTML Fundamentals
-- ============================================================
INSERT INTO courses (id, title, description, status, order_index) VALUES
(1, 'HTML Fundamentals', 'Learn the building blocks of the web. HTML gives structure to every page you visit online. In this course, you will go from zero to confidently writing HTML documents.', 'published', 1);

-- Module 1.1
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(1, 1, 'Introduction to the Web', 'Understand how the internet works and where HTML fits in.', 1);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(1, 'How the Web Works', '<h2>How the Web Works</h2>
<p>Every time you open a website, a lot happens behind the scenes. Understanding this process helps you become a better developer.</p>

<h3>The Client-Server Model</h3>
<p>The web works on a <strong>client-server model</strong>:</p>
<ul>
  <li><strong>Client</strong> – Your browser (Chrome, Firefox, Safari). It requests content.</li>
  <li><strong>Server</strong> – A computer somewhere in the world that stores website files and sends them back to your browser.</li>
</ul>

<h3>What Happens When You Visit a Website</h3>
<ol>
  <li>You type a URL (e.g., <code>www.example.com</code>) into your browser.</li>
  <li>Your browser asks a DNS server to find the IP address of that domain.</li>
  <li>Your browser connects to the web server at that IP address.</li>
  <li>The server sends back HTML, CSS, and JavaScript files.</li>
  <li>Your browser reads those files and displays the page.</li>
</ol>

<h3>What is HTML?</h3>
<p><strong>HTML</strong> stands for <em>HyperText Markup Language</em>. It is the language used to create the structure of web pages. Think of HTML as the skeleton of a website — it defines what content appears on the page and how it is organized.</p>

<div class="code-block">
<pre><code>&lt;!-- This is a simple HTML page --&gt;
&lt;!DOCTYPE html&gt;
&lt;html&gt;
  &lt;head&gt;
    &lt;title&gt;My First Page&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;h1&gt;Hello, World!&lt;/h1&gt;
  &lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<h3>Key Takeaway</h3>
<p>HTML defines <em>what</em> is on a page. CSS controls <em>how it looks</em>. JavaScript makes it <em>interactive</em>. All three work together to create modern websites.</p>', 1),

(1, 'Text Editors and Browser Tools', '<h2>Text Editors and Browser Tools</h2>
<p>Before writing any code, you need the right tools. The good news — they are free.</p>

<h3>Choosing a Text Editor</h3>
<p>A text editor is where you write your code. Some popular choices:</p>
<ul>
  <li><strong>VS Code</strong> (recommended) – Free, powerful, used by millions of developers worldwide.</li>
  <li><strong>Sublime Text</strong> – Lightweight and fast.</li>
  <li><strong>Notepad++</strong> – Simple option for Windows users.</li>
</ul>

<h3>Your First HTML File</h3>
<ol>
  <li>Open VS Code.</li>
  <li>Create a new file called <code>index.html</code>.</li>
  <li>Type the code below and save it.</li>
  <li>Open the file in your browser by double-clicking it.</li>
</ol>

<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
  &lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;My Page&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;h1&gt;Welcome to HackathonAfrica!&lt;/h1&gt;
    &lt;p&gt;I am learning HTML.&lt;/p&gt;
  &lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<h3>Using Browser Developer Tools</h3>
<p>Every modern browser has built-in developer tools. Press <code>F12</code> (or right-click → Inspect) to open them. You can:</p>
<ul>
  <li>View the HTML structure of any page</li>
  <li>See CSS styles applied to elements</li>
  <li>Debug JavaScript errors</li>
</ul>

<p><strong>Practice:</strong> Open any website, right-click on some text, and select "Inspect". Explore the HTML structure you see in the Elements panel.</p>', 2);

-- Module 1.2
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(2, 1, 'HTML Document Structure', 'Master the anatomy of a valid HTML document.', 2);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(2, 'Anatomy of an HTML Document', '<h2>Anatomy of an HTML Document</h2>
<p>Every HTML page follows the same basic structure. Let us break it down piece by piece.</p>

<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
  &lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;
    &lt;title&gt;Page Title&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;!-- Your visible content goes here --&gt;
    &lt;h1&gt;Main Heading&lt;/h1&gt;
    &lt;p&gt;A paragraph of text.&lt;/p&gt;
  &lt;/body&gt;
&lt;/html&gt;</code></pre>
</div>

<h3>Breaking It Down</h3>
<table class="table table-bordered">
  <thead><tr><th>Tag</th><th>Purpose</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;!DOCTYPE html&gt;</code></td><td>Tells the browser this is an HTML5 document.</td></tr>
    <tr><td><code>&lt;html&gt;</code></td><td>The root element — wraps all content.</td></tr>
    <tr><td><code>&lt;head&gt;</code></td><td>Contains metadata — not visible on the page.</td></tr>
    <tr><td><code>&lt;meta charset&gt;</code></td><td>Sets character encoding (supports all languages).</td></tr>
    <tr><td><code>&lt;meta viewport&gt;</code></td><td>Makes the page responsive on mobile devices.</td></tr>
    <tr><td><code>&lt;title&gt;</code></td><td>Text shown in the browser tab.</td></tr>
    <tr><td><code>&lt;body&gt;</code></td><td>All visible page content goes here.</td></tr>
  </tbody>
</table>

<h3>Nesting and Indentation</h3>
<p>HTML elements can be placed inside other elements — this is called <strong>nesting</strong>. Always indent nested elements to make your code readable.</p>

<p><strong>Rule:</strong> Every opening tag <code>&lt;tag&gt;</code> must have a matching closing tag <code>&lt;/tag&gt;</code> (except self-closing tags like <code>&lt;meta&gt;</code>, <code>&lt;br&gt;</code>, and <code>&lt;img&gt;</code>).</p>', 1),

(2, 'Headings, Paragraphs, and Text', '<h2>Headings, Paragraphs, and Text</h2>
<p>Content on a web page is organized using headings and paragraphs — just like a book or an article.</p>

<h3>Headings</h3>
<p>HTML has six levels of headings, from <code>&lt;h1&gt;</code> (most important) to <code>&lt;h6&gt;</code> (least important).</p>

<div class="code-block">
<pre><code>&lt;h1&gt;Main Title of the Page&lt;/h1&gt;
&lt;h2&gt;Section Heading&lt;/h2&gt;
&lt;h3&gt;Sub-section Heading&lt;/h3&gt;
&lt;h4&gt;Minor Heading&lt;/h4&gt;
&lt;h5&gt;Small Heading&lt;/h5&gt;
&lt;h6&gt;Smallest Heading&lt;/h6&gt;</code></pre>
</div>

<p><strong>Best Practice:</strong> Use only one <code>&lt;h1&gt;</code> per page. It tells search engines what your page is about.</p>

<h3>Paragraphs and Line Breaks</h3>
<div class="code-block">
<pre><code>&lt;p&gt;This is a paragraph. It creates space above and below automatically.&lt;/p&gt;
&lt;p&gt;This is another paragraph.&lt;/p&gt;

&lt;!-- Line break (no closing tag needed) --&gt;
&lt;p&gt;First line.&lt;br&gt;Second line in the same paragraph.&lt;/p&gt;</code></pre>
</div>

<h3>Text Formatting</h3>
<div class="code-block">
<pre><code>&lt;strong&gt;Bold text&lt;/strong&gt;
&lt;em&gt;Italic text&lt;/em&gt;
&lt;u&gt;Underlined text&lt;/u&gt;
&lt;mark&gt;Highlighted text&lt;/mark&gt;
&lt;del&gt;Strikethrough text&lt;/del&gt;
&lt;code&gt;Inline code&lt;/code&gt;</code></pre>
</div>

<h3>Lists</h3>
<div class="code-block">
<pre><code>&lt;!-- Unordered (bullet) list --&gt;
&lt;ul&gt;
  &lt;li&gt;HTML&lt;/li&gt;
  &lt;li&gt;CSS&lt;/li&gt;
  &lt;li&gt;JavaScript&lt;/li&gt;
&lt;/ul&gt;

&lt;!-- Ordered (numbered) list --&gt;
&lt;ol&gt;
  &lt;li&gt;Register&lt;/li&gt;
  &lt;li&gt;Learn&lt;/li&gt;
  &lt;li&gt;Build&lt;/li&gt;
&lt;/ol&gt;</code></pre>
</div>', 2);

-- Module 1.3
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(3, 1, 'Tags and Elements', 'Deep dive into essential HTML tags and how to use them.', 3);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(3, 'Links and Images', '<h2>Links and Images</h2>
<p>Links and images are two of the most important elements in HTML. They connect pages together and make content visual.</p>

<h3>Creating Links</h3>
<p>Links are created with the <code>&lt;a&gt;</code> (anchor) tag and the <code>href</code> attribute.</p>

<div class="code-block">
<pre><code>&lt;!-- External link --&gt;
&lt;a href="https://www.google.com"&gt;Visit Google&lt;/a&gt;

&lt;!-- Open in new tab --&gt;
&lt;a href="https://hackathon.africa" target="_blank"&gt;HackathonAfrica&lt;/a&gt;

&lt;!-- Link to another page in your project --&gt;
&lt;a href="about.html"&gt;About Us&lt;/a&gt;

&lt;!-- Link to a section on the same page --&gt;
&lt;a href="#contact"&gt;Jump to Contact&lt;/a&gt;
&lt;section id="contact"&gt;...&lt;/section&gt;</code></pre>
</div>

<h3>Adding Images</h3>
<p>Images use the <code>&lt;img&gt;</code> tag — a self-closing tag that does not need <code>&lt;/img&gt;</code>.</p>

<div class="code-block">
<pre><code>&lt;!-- Basic image --&gt;
&lt;img src="photo.jpg" alt="A photo of a developer"&gt;

&lt;!-- Image with dimensions --&gt;
&lt;img src="logo.png" alt="Company Logo" width="200" height="100"&gt;

&lt;!-- Image from the internet --&gt;
&lt;img src="https://example.com/image.jpg" alt="Description"&gt;</code></pre>
</div>

<h3>The alt Attribute</h3>
<p>The <code>alt</code> attribute provides a text description of an image. It is important for:</p>
<ul>
  <li><strong>Accessibility</strong> – Screen readers use it for visually impaired users.</li>
  <li><strong>SEO</strong> – Search engines use it to understand images.</li>
  <li><strong>Fallback</strong> – Shown if the image fails to load.</li>
</ul>

<h3>Combining Links and Images</h3>
<div class="code-block">
<pre><code>&lt;!-- Make an image a clickable link --&gt;
&lt;a href="https://hackathon.africa"&gt;
  &lt;img src="banner.jpg" alt="HackathonAfrica Banner"&gt;
&lt;/a&gt;</code></pre>
</div>', 1),

(3, 'Tables and Semantic HTML', '<h2>Tables and Semantic HTML</h2>

<h3>HTML Tables</h3>
<p>Tables display data in rows and columns. Use them for data — not for page layouts.</p>

<div class="code-block">
<pre><code>&lt;table&gt;
  &lt;thead&gt;
    &lt;tr&gt;
      &lt;th&gt;Name&lt;/th&gt;
      &lt;th&gt;Course&lt;/th&gt;
      &lt;th&gt;Score&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;
  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;td&gt;Amara&lt;/td&gt;
      &lt;td&gt;HTML&lt;/td&gt;
      &lt;td&gt;95%&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;td&gt;Kwame&lt;/td&gt;
      &lt;td&gt;CSS&lt;/td&gt;
      &lt;td&gt;88%&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;
&lt;/table&gt;</code></pre>
</div>

<h3>Semantic HTML5 Elements</h3>
<p>Semantic elements clearly describe their purpose. They help browsers, search engines, and developers understand your page structure.</p>

<div class="code-block">
<pre><code>&lt;header&gt;
  &lt;nav&gt;
    &lt;a href="/"&gt;Home&lt;/a&gt;
    &lt;a href="/about"&gt;About&lt;/a&gt;
  &lt;/nav&gt;
&lt;/header&gt;

&lt;main&gt;
  &lt;article&gt;
    &lt;h1&gt;Article Title&lt;/h1&gt;
    &lt;p&gt;Article content...&lt;/p&gt;
  &lt;/article&gt;

  &lt;aside&gt;
    &lt;h3&gt;Related Links&lt;/h3&gt;
  &lt;/aside&gt;
&lt;/main&gt;

&lt;footer&gt;
  &lt;p&gt;&copy; 2025 HackathonAfrica&lt;/p&gt;
&lt;/footer&gt;</code></pre>
</div>

<table class="table table-bordered">
  <thead><tr><th>Tag</th><th>Meaning</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;header&gt;</code></td><td>Page or section header</td></tr>
    <tr><td><code>&lt;nav&gt;</code></td><td>Navigation links</td></tr>
    <tr><td><code>&lt;main&gt;</code></td><td>Primary content area</td></tr>
    <tr><td><code>&lt;article&gt;</code></td><td>Self-contained content piece</td></tr>
    <tr><td><code>&lt;section&gt;</code></td><td>A grouping of related content</td></tr>
    <tr><td><code>&lt;aside&gt;</code></td><td>Sidebar / supplementary content</td></tr>
    <tr><td><code>&lt;footer&gt;</code></td><td>Page or section footer</td></tr>
  </tbody>
</table>', 2);

-- Module 1.4
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(4, 1, 'Forms and Inputs', 'Build interactive forms to collect user data.', 4);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(4, 'Building HTML Forms', '<h2>Building HTML Forms</h2>
<p>Forms are how users interact with websites — login pages, registration forms, search boxes, and more all use HTML forms.</p>

<h3>Basic Form Structure</h3>
<div class="code-block">
<pre><code>&lt;form action="/submit" method="POST"&gt;
  &lt;label for="name"&gt;Your Name:&lt;/label&gt;
  &lt;input type="text" id="name" name="name" placeholder="Enter your name" required&gt;

  &lt;label for="email"&gt;Email Address:&lt;/label&gt;
  &lt;input type="email" id="email" name="email" placeholder="you@example.com" required&gt;

  &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Common Input Types</h3>
<div class="code-block">
<pre><code>&lt;!-- Text input --&gt;
&lt;input type="text" name="username"&gt;

&lt;!-- Password input --&gt;
&lt;input type="password" name="password"&gt;

&lt;!-- Email input --&gt;
&lt;input type="email" name="email"&gt;

&lt;!-- Number input --&gt;
&lt;input type="number" name="age" min="1" max="100"&gt;

&lt;!-- Checkbox --&gt;
&lt;input type="checkbox" name="agree" id="agree"&gt;
&lt;label for="agree"&gt;I agree to the terms&lt;/label&gt;

&lt;!-- Radio buttons --&gt;
&lt;input type="radio" name="level" value="beginner" id="beg"&gt;
&lt;label for="beg"&gt;Beginner&lt;/label&gt;
&lt;input type="radio" name="level" value="advanced" id="adv"&gt;
&lt;label for="adv"&gt;Advanced&lt;/label&gt;

&lt;!-- Dropdown select --&gt;
&lt;select name="country"&gt;
  &lt;option value="ng"&gt;Nigeria&lt;/option&gt;
  &lt;option value="gh"&gt;Ghana&lt;/option&gt;
  &lt;option value="ke"&gt;Kenya&lt;/option&gt;
&lt;/select&gt;

&lt;!-- Multi-line text --&gt;
&lt;textarea name="message" rows="5" cols="40"&gt;&lt;/textarea&gt;</code></pre>
</div>

<h3>Form Attributes</h3>
<ul>
  <li><code>action</code> – Where to send the form data (URL)</li>
  <li><code>method</code> – How to send it: <code>GET</code> (in URL) or <code>POST</code> (hidden, secure)</li>
  <li><code>required</code> – Makes a field mandatory</li>
  <li><code>placeholder</code> – Hint text shown inside the input</li>
</ul>

<h3>The label Element</h3>
<p>Always pair inputs with <code>&lt;label&gt;</code> tags. The <code>for</code> attribute on the label must match the <code>id</code> on the input. This improves accessibility — clicking the label focuses the input.</p>', 1),

(4, 'Form Validation and Best Practices', '<h2>Form Validation and Best Practices</h2>
<p>Validating form data ensures users fill in the correct information before submitting.</p>

<h3>HTML5 Built-in Validation</h3>
<div class="code-block">
<pre><code>&lt;form&gt;
  &lt;!-- Required field --&gt;
  &lt;input type="text" name="name" required&gt;

  &lt;!-- Min and max length --&gt;
  &lt;input type="text" name="username" minlength="3" maxlength="20" required&gt;

  &lt;!-- Pattern matching (regex) --&gt;
  &lt;input type="text" name="phone" pattern="[0-9]{11}"
         title="Enter an 11-digit phone number" required&gt;

  &lt;!-- Email format validation --&gt;
  &lt;input type="email" name="email" required&gt;

  &lt;!-- URL validation --&gt;
  &lt;input type="url" name="website"&gt;

  &lt;button type="submit"&gt;Register&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>A Complete Registration Form</h3>
<div class="code-block">
<pre><code>&lt;form action="/register" method="POST"&gt;
  &lt;h2&gt;Create Account&lt;/h2&gt;

  &lt;div&gt;
    &lt;label for="fullname"&gt;Full Name *&lt;/label&gt;
    &lt;input type="text" id="fullname" name="fullname"
           placeholder="e.g. Amara Osei" required&gt;
  &lt;/div&gt;

  &lt;div&gt;
    &lt;label for="email"&gt;Email Address *&lt;/label&gt;
    &lt;input type="email" id="email" name="email"
           placeholder="you@example.com" required&gt;
  &lt;/div&gt;

  &lt;div&gt;
    &lt;label for="password"&gt;Password *&lt;/label&gt;
    &lt;input type="password" id="password" name="password"
           minlength="8" required&gt;
  &lt;/div&gt;

  &lt;div&gt;
    &lt;input type="checkbox" id="terms" name="terms" required&gt;
    &lt;label for="terms"&gt;I agree to the Terms of Service&lt;/label&gt;
  &lt;/div&gt;

  &lt;button type="submit"&gt;Create Account&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Best Practices</h3>
<ul>
  <li>Always use <code>POST</code> method for sensitive data (passwords, payments)</li>
  <li>Always validate on the server side too — never trust client-only validation</li>
  <li>Label every input for accessibility</li>
  <li>Use appropriate input types (<code>email</code>, <code>tel</code>, <code>number</code>) for better mobile keyboards</li>
  <li>Provide clear error messages</li>
</ul>', 2);

-- Module 1.4
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(4, 1, 'Forms and Inputs', 'Build interactive forms to collect user data.', 4);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(4, 'Building HTML Forms', '<h2>Building HTML Forms</h2>
<p>Forms are how users interact with websites — login pages, registration forms, search boxes, and more all use HTML forms.</p>

<h3>Basic Form Structure</h3>
<div class="code-block">
<pre><code>&lt;form action="/submit" method="POST"&gt;
  &lt;label for="name"&gt;Your Name:&lt;/label&gt;
  &lt;input type="text" id="name" name="name" placeholder="Enter your name" required&gt;

  &lt;label for="email"&gt;Email Address:&lt;/label&gt;
  &lt;input type="email" id="email" name="email" placeholder="you@example.com" required&gt;

  &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Common Input Types</h3>
<div class="code-block">
<pre><code>&lt;!-- Text input --&gt;
&lt;input type="text" name="username"&gt;

&lt;!-- Password input --&gt;
&lt;input type="password" name="password"&gt;

&lt;!-- Email input --&gt;
&lt;input type="email" name="email"&gt;

&lt;!-- Number input --&gt;
&lt;input type="number" name="age" min="1" max="100"&gt;

&lt;!-- Checkbox --&gt;
&lt;input type="checkbox" name="agree" id="agree"&gt;
&lt;label for="agree"&gt;I agree to the terms&lt;/label&gt;

&lt;!-- Radio buttons --&gt;
&lt;input type="radio" name="level" value="beginner" id="beg"&gt;
&lt;label for="beg"&gt;Beginner&lt;/label&gt;
&lt;input type="radio" name="level" value="advanced" id="adv"&gt;
&lt;label for="adv"&gt;Advanced&lt;/label&gt;

&lt;!-- Dropdown select --&gt;
&lt;select name="country"&gt;
  &lt;option value="ng"&gt;Nigeria&lt;/option&gt;
  &lt;option value="gh"&gt;Ghana&lt;/option&gt;
  &lt;option value="ke"&gt;Kenya&lt;/option&gt;
&lt;/select&gt;

&lt;!-- Multi-line text --&gt;
&lt;textarea name="message" rows="5" cols="40"&gt;&lt;/textarea&gt;</code></pre>
</div>

<h3>Form Attributes</h3>
<ul>
  <li><code>action</code> – Where to send the form data (URL)</li>
  <li><code>method</code> – How to send it: <code>GET</code> (in URL) or <code>POST</code> (hidden, secure)</li>
  <li><code>required</code> – Makes a field mandatory</li>
  <li><code>placeholder</code> – Hint text shown inside the input</li>
</ul>

<h3>The label Element</h3>
<p>Always pair inputs with <code>&lt;label&gt;</code> tags. The <code>for</code> attribute on the label must match the <code>id</code> on the input. This improves accessibility — clicking the label focuses the input.</p>', 1),

(4, 'Form Validation and Best Practices', '<h2>Form Validation and Best Practices</h2>
<p>Validating form data ensures users fill in the correct information before submitting.</p>

<h3>HTML5 Built-in Validation</h3>
<div class="code-block">
<pre><code>&lt;form&gt;
  &lt;!-- Required field --&gt;
  &lt;input type="text" name="name" required&gt;

  &lt;!-- Min and max length --&gt;
  &lt;input type="text" name="username" minlength="3" maxlength="20" required&gt;

  &lt;!-- Pattern matching (regex) --&gt;
  &lt;input type="text" name="phone" pattern="[0-9]{11}"
         title="Enter an 11-digit phone number" required&gt;

  &lt;!-- Email format validation --&gt;
  &lt;input type="email" name="email" required&gt;

  &lt;!-- URL validation --&gt;
  &lt;input type="url" name="website"&gt;

  &lt;button type="submit"&gt;Register&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>A Complete Registration Form</h3>
<div class="code-block">
<pre><code>&lt;form action="/register" method="POST"&gt;
  &lt;h2&gt;Create Account&lt;/h2&gt;

  &lt;div&gt;
    &lt;label for="fullname"&gt;Full Name *&lt;/label&gt;
    &lt;input type="text" id="fullname" name="fullname"
           placeholder="e.g. Amara Osei" required&gt;
  &lt;/div&gt;

  &lt;div&gt;
    &lt;label for="email"&gt;Email Address *&lt;/label&gt;
    &lt;input type="email" id="email" name="email"
           placeholder="you@example.com" required&gt;
  &lt;/div&gt;

  &lt;div&gt;
    &lt;label for="password"&gt;Password *&lt;/label&gt;
    &lt;input type="password" id="password" name="password"
           minlength="8" required&gt;
  &lt;/div&gt;

  &lt;div&gt;
    &lt;input type="checkbox" id="terms" name="terms" required&gt;
    &lt;label for="terms"&gt;I agree to the Terms of Service&lt;/label&gt;
  &lt;/div&gt;

  &lt;button type="submit"&gt;Create Account&lt;/button&gt;
&lt;/form&gt;</code></pre>
</div>

<h3>Best Practices</h3>
<ul>
  <li>Always use <code>POST</code> method for sensitive data (passwords, payments)</li>
  <li>Always validate on the server side too — never trust client-only validation</li>
  <li>Label every input for accessibility</li>
  <li>Use appropriate input types (<code>email</code>, <code>tel</code>, <code>number</code>) for better mobile keyboards</li>
  <li>Provide clear error messages</li>
</ul>', 2);

-- Module 1.5
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(13, 1, 'Advanced HTML & Accessibility', 'Learn advanced HTML techniques and best practices for creating accessible web content.', 5);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(13, 'Semantic HTML5 Elements Deep Dive', '<h2>Semantic HTML5 Elements Deep Dive</h2>
<p>Beyond the basic structural tags, HTML5 offers a rich set of semantic elements that convey meaning to both browsers and developers. Using these tags correctly improves accessibility, SEO, and code maintainability.</p>

<h3>Why Semantic HTML Matters</h3>
<ul>
  <li><strong>Accessibility:</strong> Screen readers and other assistive technologies rely on semantic tags to understand the structure and meaning of a page.</li>
  <li><strong>SEO:</strong> Search engines can better understand the content and hierarchy of your page, potentially improving rankings.</li>
  <li><strong>Maintainability:</strong> Code is easier to read, understand, and maintain when elements clearly describe their purpose.</li>
</ul>

<h3>Key Semantic Elements</h3>
<table class="table table-bordered">
  <thead><tr><th>Tag</th><th>Purpose</th><th>Example Use</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;article&gt;</code></td><td>Self-contained content, like a blog post or news story.</td><td><code>&lt;article&gt;&lt;h2&gt;Blog Post&lt;/h2&gt;&lt;p&gt;...&lt;/p&gt;&lt;/article&gt;</code></td></tr>
    <tr><td><code>&lt;section&gt;</code></td><td>A generic standalone section of a document, often with its own heading.</td><td><code>&lt;section&gt;&lt;h2&gt;About Us&lt;/h2&gt;&lt;p&gt;...&lt;/p&gt;&lt;/section&gt;</code></td></tr>
    <tr><td><code>&lt;nav&gt;</code></td><td>Navigation links.</td><td><code>&lt;nav&gt;&lt;ul&gt;&lt;li&gt;&lt;a href="#"&gt;Home&lt;/a&gt;&lt;/li&gt;&lt;/ul&gt;&lt;/nav&gt;</code></td></tr>
    <tr><td><code>&lt;aside&gt;</code></td><td>Content indirectly related to the main content (e.g., sidebar).</td><td><code>&lt;aside&gt;&lt;h3&gt;Related Articles&lt;/h3&gt;&lt;/aside&gt;</code></td></tr>
    <tr><td><code>&lt;header&gt;</code></td><td>Introductory content or a set of navigational links for its nearest ancestor.</td><td><code>&lt;header&gt;&lt;h1&gt;My Site&lt;/h1&gt;&lt;/header&gt;</code></td></tr>
    <tr><td><code>&lt;footer&gt;</code></td><td>Concluding content for its nearest ancestor (e.g., copyright, author info).</td><td><code>&lt;footer&gt;&lt;p&gt;&copy; 2024&lt;/p&gt;&lt;/footer&gt;</code></td></tr>
    <tr><td><code>&lt;figure&gt;</code></td><td>Self-contained content, potentially with an optional caption.</td><td><code>&lt;figure&gt;&lt;img src="image.jpg"&gt;&lt;figcaption&gt;Caption&lt;/figcaption&gt;&lt;/figure&gt;</code></td></tr>
    <tr><td><code>&lt;figcaption&gt;</code></td><td>A caption or legend for the parent <code>&lt;figure&gt;</code>.</td><td>(See above)</td></tr>
    <tr><td><code>&lt;main&gt;</code></td><td>The dominant content of the <code>&lt;body&gt;</code>. There should only be one per page.</td><td><code>&lt;main&gt;&lt;article&gt;...&lt;/article&gt;&lt;/main&gt;</code></td></tr>
    <tr><td><code>&lt;mark&gt;</code></td><td>Represents text highlighted for reference.</td><td><code>&lt;p&gt;Please &lt;mark&gt;review&lt;/mark&gt; these changes.&lt;/p&gt;</code></td></tr>
    <tr><td><code>&lt;time&gt;</code></td><td>Represents a specific period in time.</td><td><code>Published on &lt;time datetime="2024-04-23"&gt;April 23, 2024&lt;/time&gt;.</code></td></tr>
    <tr><td><code>&lt;dialog&gt;</code></td><td>A dialog box or other interactive component.</td><td><code>&lt;dialog open&gt;&lt;p&gt;This is a dialog.&lt;/p&gt;&lt;/dialog&gt;</code></td></tr>
    <tr><td><code>&lt;details&gt;</code></td><td>A disclosure widget from which information can be retrieved.</td><td><code>&lt;details&gt;&lt;summary&gt;Click to expand&lt;/summary&gt;&lt;p&gt;More info.&lt;/p&gt;&lt;/details&gt;</code></td></tr>
    <tr><td><code>&lt;summary&gt;</code></td><td>A summary, caption, or legend for the content of a <code>&lt;details&gt;</code> element.</td><td>(See above)</td></tr>
  </tbody>
</table>

<h3>Example Structure</h3>
<div class="code-block">
<pre><code>&lt;!DOCTYPE html&gt;
&lt;html lang="en"&gt;
&lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Semantic HTML Example&lt;/title&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;header&gt;
        &lt;h1&gt;My Awesome Website&lt;/h1&gt;
        &lt;nav&gt;
            &lt;ul&gt;
                &lt;li&gt;&lt;a href="#"&gt;Home&lt;/a&gt;&lt;/li&gt;
                &lt;li&gt;&lt;a href="#"&gt;About&lt;/a&gt;&lt;/li&gt;
            &lt;/ul&gt;
        &lt;/nav&gt;
    &lt;/header&gt;

    &lt;main&gt;
        &lt;article&gt;
            &lt;h2&gt;Understanding Semantic Elements&lt;/h2&gt;
            &lt;p&gt;Using the right HTML tags is crucial...&lt;/p&gt;
            &lt;figure&gt;
                &lt;img src="semantic-example.png" alt="Diagram showing semantic HTML structure"&gt;
                &lt;figcaption&gt;A well-structured HTML document using semantic tags.&lt;/figcaption&gt;
            &lt;/figure&gt;
        &lt;/article&gt;

        &lt;aside&gt;
            &lt;h3&gt;Quick Links&lt;/h3&gt;
            &lt;ul&gt;
                &lt;li&gt;&lt;a href="#"&gt;Accessibility Guide&lt;/a&gt;&lt;/li&gt;
            &lt;/ul&gt;
        &lt;/aside&gt;
    &lt;/main&gt;

    &lt;footer&gt;
        &lt;p&gt;&copy; 2024. All rights reserved.&lt;/p&gt;
    &lt;/footer&gt;
&lt;/body&gt;
&lt;/html&gt;
</code></pre>
</div>', 1),

(13, 'ARIA Attributes for Enhanced Accessibility', '<h2>ARIA Attributes for Enhanced Accessibility</h2>
<p><strong>ARIA (Accessible Rich Internet Applications)</strong> is a set of attributes that you can add to HTML elements to improve accessibility for users with disabilities, especially when using dynamic content and advanced UI components not natively supported by HTML.</p>

<h3>When to Use ARIA</h3>
<p>The first rule of ARIA is: <strong>"If you can use a native HTML element or attribute with the semantics and behavior you require, instead of re-purposing an element and adding an ARIA role, state or property to it, then do so."</strong> This means ARIA is for when native HTML isn't enough.</p>

<h3>Core Concepts: Roles, States, and Properties</h3>
<ul>
  <li><strong>Roles:</strong> Define what an element is or does. E.g., `role="button"`, `role="navigation"`, `role="alert"`.</li>
  <li><strong>States:</strong> Define the current condition of an element. E.g., `aria-checked="true"`, `aria-disabled="true"`, `aria-expanded="false"`.</li>
  <li><strong>Properties:</strong> Describe the characteristics of an element. E.g., `aria-label`, `aria-describedby`, `aria-controls`.</li>
</ul>

<h3>Common ARIA Attributes</h3>
<table class="table table-bordered">
  <thead><tr><th>Attribute</th><th>Purpose</th><th>Example</th></tr></thead>
  <tbody>
    <tr><td><code>role="button"</code></td><td>Identifies an element as a button. Useful when using a `div` or `span` as a button.</td><td><code>&lt;div role="button" tabindex="0"&gt;Click Me&lt;/div&gt;</code></td></tr>
    <tr><td><code>aria-label</code></td><td>Provides an accessible name for an element when no visible label exists.</td><td><code>&lt;button aria-label="Close"&gt;X&lt;/button&gt;</code></td></tr>
    <tr><td><code>aria-labelledby</code></td><td>References an existing element to serve as the label.</td><td><code>&lt;div role="dialog" aria-labelledby="dialog-title"&gt;&lt;h2 id="dialog-title"&gt;Welcome&lt;/h2&gt;...&lt;/div&gt;</code></td></tr>
    <tr><td><code>aria-describedby</code></td><td>References an element that provides a description for the current element.</td><td><code>&lt;input type="text" aria-describedby="email-hint"&gt;&lt;p id="email-hint"&gt;Your email will not be shared.&lt;/p&gt;</code></td></tr>
    <tr><td><code>aria-expanded</code></td><td>Indicates whether a collapsible element is currently expanded or collapsed.</td><td><code>&lt;button aria-expanded="false" aria-controls="menu"&gt;Menu&lt;/button&gt;&lt;ul id="menu"&gt;...&lt;/ul&gt;</code></td></tr>
    <tr><td><code>aria-haspopup</code></td><td>Indicates the availability and type of interactive popup that can be triggered.</td><td><code>&lt;button aria-haspopup="menu"&gt;Settings&lt;/button&gt;</code></td></tr>
    <tr><td><code>aria-live</code></td><td>Indicates that an element will be updated and describes the types of updates the user agent, a assistive technologies, and the user can expect. Values: `off`, `polite`, `assertive`.</td><td><code>&lt;div aria-live="polite"&gt;Item added to cart.&lt;/div&gt;</code></td></tr>
  </tbody>
</table>

<h3>Example: Custom Toggle Button</h3>
<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;div class="toggle-switch" role="switch" aria-checked="false" tabindex="0"&gt;
  &lt;span class="toggle-label"&gt;Enable Notifications&lt;/span&gt;
  &lt;span class="toggle-handle"&gt;&lt;/span&gt;
&lt;/div&gt;

&lt;!-- JavaScript (simplified) --&gt;
const toggle = document.querySelector(".toggle-switch");
toggle.addEventListener("click", () => {
  const isChecked = toggle.getAttribute("aria-checked") === "true";
  toggle.setAttribute("aria-checked", !isChecked);
});
</code></pre>
</div>

<h3>Best Practices</h3>
<ul>
  <li>Always test with screen readers (e.g., NVDA, JAWS, VoiceOver).</li>
  <li>Don\'t over-ARIA: Use ARIA only when necessary to supplement HTML semantics.</li>
  <li>Ensure ARIA attributes are dynamically updated with JavaScript as the UI changes.</li>
</ul>', 2),

(13, 'Responsive Images: srcset and <picture>', '<h2>Responsive Images: <code>srcset</code> and <code>&lt;picture&gt;</code></h2>
<p>Delivering optimized images for different devices and screen sizes is crucial for performance and user experience. HTML provides `srcset` and the `<picture>` element to achieve this.</p>

<h3>The `srcset` Attribute</h3>
<p>The `srcset` attribute on an `<img>` tag allows you to provide a list of different image sources along with their intrinsic widths or pixel densities. The browser then chooses the most appropriate image.</p>

<div class="code-block">
<pre><code>&lt;!-- Using width descriptors --&gt;
&lt;img src="hero-small.jpg"
     srcset="hero-small.jpg 480w,
             hero-medium.jpg 800w,
             hero-large.jpg 1200w"
     sizes="(max-width: 600px) 480px,
            (max-width: 900px) 800px,
            1200px"
     alt="A beautiful landscape"&gt;

&lt;!-- Using pixel density descriptors --&gt;
&lt;img src="logo-1x.png"
     srcset="logo-1x.png 1x,
             logo-2x.png 2x"
     alt="Company Logo"&gt;
</code></pre>
</div>
<ul>
  <li><code>w</code> descriptor: Specifies the intrinsic width of the image file (e.g., `480w` means the image is 480 pixels wide).</li>
  <li><code>x</code> descriptor: Specifies the pixel density of the image (e.g., `2x` for Retina displays).</li>
  <li>`sizes` attribute: Informs the browser about the intended display size of the image relative to the viewport. This helps the browser select the correct `srcset` image based on available space.</li>
</ul>

<h3>The `<picture>` Element</h3>
<p>The `<picture>` element gives you even more control, allowing you to specify different image sources based on media queries (e.g., different images for different viewport widths or even different image formats).</p>

<div class="code-block">
<pre><code>&lt;picture&gt;
  &lt;!-- WebP for modern browsers --&gt;
  &lt;source srcset="hero-large.webp" media="(min-width: 1200px)" type="image/webp"&gt;
  &lt;source srcset="hero-medium.webp" media="(min-width: 800px)" type="image/webp"&gt;
  &lt;source srcset="hero-small.webp" type="image/webp"&gt;
  
  &lt;!-- JPEG fallback for older browsers --&gt;
  &lt;source srcset="hero-large.jpg" media="(min-width: 1200px)" type="image/jpeg"&gt;
  &lt;source srcset="hero-medium.jpg" media="(min-width: 800px)" type="image/jpeg"&gt;
  &lt;img src="hero-small.jpg" alt="A beautiful landscape"&gt;
&lt;/picture&gt;
</code></pre>
</div>
<ul>
  <li>`<source>` tag: Specifies different image resources. It can include `media` attributes (like CSS media queries) and `type` attributes (for image formats).</li>
  <li>The browser will use the first `<source>` tag that matches its criteria.</li>
  <li>The `<img>` tag inside `<picture>` acts as a fallback for browsers that don\'t support `<picture>` and is also where the `alt` attribute should be placed.</li>
</ul>

<h3>Best Practices for Responsive Images</h3>
<ul>
  <li>Always include an `alt` attribute on your `<img>` tags for accessibility.</li>
  <li>Generate multiple sizes and formats of your images to cater to different devices and browser capabilities.</li>
  <li>Use `srcset` for resolution switching (same image, different sizes).</li>
  <li>Use `<picture>` for art direction (different image content/cropping for different breakpoints) or format switching (e.g., WebP for modern browsers, JPEG for older ones).</li>
</ul>', 3),

(13, 'Meta Tags for SEO & Social Media', '<h2>Meta Tags for SEO & Social Media</h2>
<p>Meta tags provide metadata about your HTML document. While many are not directly visible on the page, they are crucial for search engine optimization (SEO), social media sharing, and overall browser behavior.</p>

<h3>Essential Meta Tags</h3>
<table class="table table-bordered">
  <thead><tr><th>Meta Tag</th><th>Purpose</th><th>Example</th></tr></thead>
  <tbody>
    <tr><td><code>&lt;meta charset="UTF-8"&gt;</code></td><td>Specifies the character encoding for the document (should always be UTF-8).</td><td><code>&lt;meta charset="UTF-8"&gt;</code></td></tr>
    <tr><td><code>&lt;meta name="viewport" ...&gt;</code></td><td>Controls the viewport width and initial zoom for responsive design.</td><td><code>&lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;</code></td></tr>
    <tr><td><code>&lt;meta name="description" ...&gt;</code></td><td>A concise summary of the page content, often shown in search results.</td><td><code>&lt;meta name="description" content="Learn advanced HTML, CSS, and JavaScript with HackathonAfrica LMS."&gt;</code></td></tr>
    <tr><td><code>&lt;meta name="keywords" ...&gt;</code></td><td>(Less important for modern SEO) Comma-separated list of keywords.</td><td><code>&lt;meta name="keywords" content="HTML, CSS, JavaScript, web development"&gt;</code></td></tr>
    <tr><td><code>&lt;title&gt;</code></td><td>(Not a meta tag, but critical for SEO) Defines the title of the document, shown in browser tab and search results.</td><td><code>&lt;title&gt;Advanced HTML & Accessibility | HackathonAfrica LMS&lt;/title&gt;</code></td></tr>
    <tr><td><code>&lt;link rel="canonical" ...&gt;</code></td><td>Helps prevent duplicate content issues by specifying the preferred URL.</td><td><code>&lt;link rel="canonical" href="https://example.com/advanced-html"&gt;</code></td></tr>
    <tr><td><code>&lt;link rel="icon" ...&gt;</code></td><td>Defines the favicon for the website.</td><td><code>&lt;link rel="icon" href="/favicon.ico" type="image/x-icon"&gt;</code></td></tr>
  </tbody>
</table>

<h3>Open Graph Protocol (for Social Media)</h3>
<p>Open Graph (OG) meta tags allow you to control how your web page appears when shared on social media platforms like Facebook, LinkedIn, etc.</p>

<div class="code-block">
<pre><code>&lt;!-- Basic Open Graph tags --&gt;
&lt;meta property="og:title" content="Advanced HTML & Accessibility"&gt;
&lt;meta property="og:description" content="Unlock advanced HTML techniques and build more accessible web experiences."&gt;
&lt;meta property="og:image" content="https://example.com/images/advanced-html-banner.jpg"&gt;
&lt;meta property="og:url" content="https://example.com/courses/html/advanced"&gt;
&lt;meta property="og:type" content="website"&gt;

&lt;!-- Twitter Card tags (often used in conjunction with OG) --&gt;
&lt;meta name="twitter:card" content="summary_large_image"&gt;
&lt;meta name="twitter:site" content="@yourtwitterhandle"&gt;
&lt;meta name="twitter:title" content="Advanced HTML & Accessibility"&gt;
&lt;meta name="twitter:description" content="Unlock advanced HTML techniques and build more accessible web experiences."&gt;
&lt;meta name="twitter:image" content="https://example.com/images/advanced-html-twitter.jpg"&gt;
</code></pre>
</div>
<ul>
  <li><code>og:title</code>: The title of your content as it should appear in the social media feed.</li>
  <li><code>og:description</code>: A brief description of the content.</li>
  <li><code>og:image</code>: The URL of an image that will appear when the content is shared.</li>
  <li><code>og:url</code>: The canonical URL of the page.</li>
  <li><code>og:type</code>: The type of content (e.g., `website`, `article`, `video.movie`).</li>
</ul>

<h3>Best Practices</h3>
<ul>
  <li>Include a descriptive and unique `&lt;title&gt;` tag for every page.</li>
  <li>Craft a compelling `description` meta tag for better click-through rates from search results.</li>
  <li>Implement Open Graph and Twitter Card tags to ensure your content looks great when shared on social media.</li>
  <li>Test your Open Graph tags using tools like Facebook Sharing Debugger or Twitter Card Validator.</li>
</ul>', 4);
INSERT INTO courses (id, title, description, status, order_index) VALUES
(2, 'CSS Fundamentals', 'Master the art of styling web pages. CSS transforms plain HTML into beautiful, professional-looking websites. Learn selectors, layouts, and responsive design.', 'published', 2);

-- Module 2.1
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(5, 2, 'Selectors and Styling', 'Learn how to target HTML elements and apply styles.', 1);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(5, 'CSS Basics and Selectors', '<h2>CSS Basics and Selectors</h2>
<p>CSS (Cascading Style Sheets) is used to control the visual appearance of HTML elements. Without CSS, web pages would just be plain text.</p>

<h3>Three Ways to Add CSS</h3>
<div class="code-block">
<pre><code>&lt;!-- 1. Inline CSS (least recommended) --&gt;
&lt;p style="color: red; font-size: 18px;"&gt;Red text&lt;/p&gt;

&lt;!-- 2. Internal CSS (inside &lt;head&gt;) --&gt;
&lt;style&gt;
  p { color: blue; }
&lt;/style&gt;

&lt;!-- 3. External CSS (best practice) --&gt;
&lt;link rel="stylesheet" href="style.css"&gt;</code></pre>
</div>

<h3>CSS Selectors</h3>
<div class="code-block">
<pre><code>/* Element selector - targets all &lt;p&gt; tags */
p {
  color: #333;
  font-size: 16px;
}

/* Class selector - targets elements with class="highlight" */
.highlight {
  background-color: yellow;
}

/* ID selector - targets the element with id="header" */
#header {
  background-color: #1a1a2e;
  color: white;
}

/* Descendant selector - targets &lt;p&gt; inside .container */
.container p {
  margin: 0 0 16px;
}

/* Multiple selectors */
h1, h2, h3 {
  font-family: Georgia, serif;
}

/* Pseudo-class selectors */
a:hover { color: orange; }
input:focus { border-color: blue; }
li:first-child { font-weight: bold; }</code></pre>
</div>

<h3>CSS Properties Overview</h3>
<div class="code-block">
<pre><code>p {
  /* Text */
  color: #333333;
  font-size: 16px;
  font-family: Arial, sans-serif;
  font-weight: bold;
  text-align: center;
  line-height: 1.6;
  text-decoration: underline;

  /* Background */
  background-color: #f0f0f0;

  /* Spacing */
  margin: 16px;       /* outside spacing */
  padding: 8px 16px;  /* inside spacing */
}</code></pre>
</div>', 1),

(5, 'Colors and Typography', '<h2>Colors and Typography</h2>

<h3>Working with Colors</h3>
<p>CSS supports several color formats:</p>
<div class="code-block">
<pre><code>/* Color names */
color: red;
color: tomato;
color: cornflowerblue;

/* Hexadecimal (#RRGGBB) */
color: #FF5733;
color: #333;       /* shorthand for #333333 */

/* RGB */
color: rgb(255, 87, 51);

/* RGBA (with transparency) */
color: rgba(255, 87, 51, 0.5);  /* 50% transparent */

/* HSL (Hue, Saturation, Lightness) */
color: hsl(14, 100%, 60%);</code></pre>
</div>

<h3>Typography</h3>
<div class="code-block">
<pre><code>body {
  font-family: "Segoe UI", Arial, sans-serif;
  font-size: 16px;
  line-height: 1.6;
  color: #333;
}

h1 {
  font-size: 2.5rem;      /* relative to root font size */
  font-weight: 700;
  letter-spacing: -1px;
}

/* Using Google Fonts */
/* Add to HTML &lt;head&gt;:
   &lt;link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet"&gt;
*/
body {
  font-family: "Inter", sans-serif;
}</code></pre>
</div>

<h3>CSS Units</h3>
<table class="table table-bordered">
  <thead><tr><th>Unit</th><th>Description</th><th>Example</th></tr></thead>
  <tbody>
    <tr><td><code>px</code></td><td>Fixed pixels</td><td><code>font-size: 16px</code></td></tr>
    <tr><td><code>rem</code></td><td>Relative to root element size</td><td><code>font-size: 1.5rem</code></td></tr>
    <tr><td><code>em</code></td><td>Relative to parent element size</td><td><code>margin: 1em</code></td></tr>
    <tr><td><code>%</code></td><td>Relative to parent element</td><td><code>width: 50%</code></td></tr>
    <tr><td><code>vw/vh</code></td><td>Percentage of viewport width/height</td><td><code>height: 100vh</code></td></tr>
  </tbody>
</table>', 2);

-- Module 2.2
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(6, 2, 'Box Model', 'Understand how every HTML element is a box.', 2);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(6, 'Understanding the Box Model', '<h2>Understanding the Box Model</h2>
<p>Every element in HTML is treated as a rectangular box. Understanding this "Box Model" is fundamental to controlling layout in CSS.</p>

<h3>The Four Layers</h3>
<p>From inside to outside:</p>
<ol>
  <li><strong>Content</strong> – Where text and images appear</li>
  <li><strong>Padding</strong> – Space between content and the border (inside)</li>
  <li><strong>Border</strong> – A line around the padding</li>
  <li><strong>Margin</strong> – Space outside the border (between elements)</li>
</ol>

<div class="code-block">
<pre><code>.box {
  /* Content size */
  width: 300px;
  height: 150px;

  /* Padding (inside spacing) */
  padding: 20px;
  /* Or individually: */
  padding-top: 10px;
  padding-right: 20px;
  padding-bottom: 10px;
  padding-left: 20px;
  /* Shorthand: top right bottom left */
  padding: 10px 20px 10px 20px;
  /* Two-value shorthand: vertical horizontal */
  padding: 10px 20px;

  /* Border */
  border: 2px solid #333;
  border-radius: 8px;    /* rounded corners */

  /* Margin (outside spacing) */
  margin: 16px auto;     /* auto centers horizontally */
}</code></pre>
</div>

<h3>Box-Sizing</h3>
<p>By default, padding and border are added to the specified width. Use <code>box-sizing: border-box</code> to include them in the width — this is almost always what you want.</p>

<div class="code-block">
<pre><code>/* Apply to all elements — recommended practice */
*, *::before, *::after {
  box-sizing: border-box;
}

.card {
  width: 300px;
  padding: 20px;
  border: 2px solid #ddd;
  /* Total width is still 300px, not 342px */
}</code></pre>
</div>

<h3>Display Property</h3>
<div class="code-block">
<pre><code>/* Block elements take full width, stack vertically */
div { display: block; }

/* Inline elements flow with text, no width/height control */
span { display: inline; }

/* Inline-block: inline flow but supports width/height */
img { display: inline-block; }

/* Remove element from page entirely */
.hidden { display: none; }</code></pre>
</div>', 1),

(6, 'Positioning Elements', '<h2>Positioning Elements</h2>
<p>CSS positioning controls where elements appear on the page relative to their normal flow or the viewport.</p>

<div class="code-block">
<pre><code>/* Static (default) - normal document flow */
.element { position: static; }

/* Relative - offset from its normal position */
.element {
  position: relative;
  top: 10px;    /* move 10px down */
  left: 20px;   /* move 20px right */
}

/* Absolute - positioned relative to nearest positioned ancestor */
.container { position: relative; }
.tooltip {
  position: absolute;
  top: 0;
  right: 0;
  /* Appears at top-right of .container */
}

/* Fixed - stays in place when scrolling */
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 100;
}

/* Sticky - switches between relative and fixed */
.table-header {
  position: sticky;
  top: 0;
  background: white;
}</code></pre>
</div>

<h3>Z-index (Stacking Order)</h3>
<div class="code-block">
<pre><code>.modal-overlay {
  position: fixed;
  z-index: 1000;  /* Higher = appears on top */
}

.dropdown {
  position: absolute;
  z-index: 500;
}</code></pre>
</div>

<h3>Practical Example: Card with Badge</h3>
<div class="code-block">
<pre><code>.card {
  position: relative;
  width: 250px;
  padding: 20px;
  border: 1px solid #ddd;
}

.badge {
  position: absolute;
  top: -10px;
  right: -10px;
  background: red;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
}</code></pre>
</div>', 2);

-- Module 2.3
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(7, 2, 'Flexbox', 'Master modern flexible box layout.', 3);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(7, 'Flexbox Layout', '<h2>Flexbox Layout</h2>
<p>Flexbox is the modern way to arrange elements in one dimension (row or column). It makes complex layouts simple.</p>

<h3>Enabling Flexbox</h3>
<div class="code-block">
<pre><code>.container {
  display: flex;
}</code></pre>
</div>

<h3>Key Flexbox Properties</h3>
<div class="code-block">
<pre><code>.container {
  display: flex;

  /* Direction: row (default) or column */
  flex-direction: row;        /* left to right */
  flex-direction: column;     /* top to bottom */
  flex-direction: row-reverse;

  /* Wrapping */
  flex-wrap: nowrap;   /* default - all on one line */
  flex-wrap: wrap;     /* wrap to next line if needed */

  /* Alignment on main axis (horizontal in row) */
  justify-content: flex-start;    /* default */
  justify-content: flex-end;
  justify-content: center;
  justify-content: space-between; /* equal gaps between items */
  justify-content: space-around;

  /* Alignment on cross axis (vertical in row) */
  align-items: stretch;     /* default - fill height */
  align-items: center;
  align-items: flex-start;
  align-items: flex-end;

  /* Gap between items */
  gap: 16px;
  gap: 16px 24px;  /* row-gap column-gap */
}

/* Child item properties */
.item {
  flex-grow: 1;    /* take up available space */
  flex-shrink: 0;  /* dont shrink below base size */
  flex-basis: 200px; /* base size */
  /* Shorthand: */
  flex: 1;         /* flex: 1 1 0% */

  align-self: center; /* override container align-items for this item */
}</code></pre>
</div>

<h3>Practical Examples</h3>
<div class="code-block">
<pre><code>/* Navbar */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 24px;
  height: 60px;
}

/* Center content vertically and horizontally */
.hero {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}

/* Equal-width card grid */
.cards {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
}
.card {
  flex: 1 1 300px; /* grows, shrinks, min 300px */
}</code></pre>
</div>', 1),

(7, 'Flexbox Practice Projects', '<h2>Flexbox Practice Projects</h2>
<p>The best way to learn Flexbox is by building real components. Let us create three common UI patterns.</p>

<h3>Project 1: Navigation Bar</h3>
<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;nav class="navbar"&gt;
  &lt;div class="logo"&gt;HackathonAfrica&lt;/div&gt;
  &lt;ul class="nav-links"&gt;
    &lt;li&gt;&lt;a href="/"&gt;Home&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/courses"&gt;Courses&lt;/a&gt;&lt;/li&gt;
    &lt;li&gt;&lt;a href="/about"&gt;About&lt;/a&gt;&lt;/li&gt;
  &lt;/ul&gt;
  &lt;button&gt;Login&lt;/button&gt;
&lt;/nav&gt;

/* CSS */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 32px;
  background: #1a1a2e;
  color: white;
}

.nav-links {
  display: flex;
  list-style: none;
  gap: 24px;
  margin: 0;
  padding: 0;
}

.nav-links a {
  color: white;
  text-decoration: none;
}</code></pre>
</div>

<h3>Project 2: Dashboard Cards</h3>
<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;div class="stats-grid"&gt;
  &lt;div class="stat-card"&gt;
    &lt;span class="stat-number"&gt;12&lt;/span&gt;
    &lt;span class="stat-label"&gt;Lessons Completed&lt;/span&gt;
  &lt;/div&gt;
  &lt;div class="stat-card"&gt;
    &lt;span class="stat-number"&gt;85%&lt;/span&gt;
    &lt;span class="stat-label"&gt;Average Score&lt;/span&gt;
  &lt;/div&gt;
  &lt;div class="stat-card"&gt;
    &lt;span class="stat-number"&gt;3&lt;/span&gt;
    &lt;span class="stat-label"&gt;Courses Enrolled&lt;/span&gt;
  &lt;/div&gt;
&lt;/div&gt;

/* CSS */
.stats-grid {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.stat-card {
  flex: 1 1 150px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #6c63ff;
}

.stat-label {
  font-size: 0.875rem;
  color: #666;
  margin-top: 4px;
}</code></pre>
</div>', 2);

-- Module 2.4
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(8, 2, 'Responsive Design', 'Make your websites look great on all screen sizes.', 4);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(8, 'Media Queries and Responsive Design', '<h2>Media Queries and Responsive Design</h2>
<p>Responsive design ensures your website works well on phones, tablets, and desktop screens.</p>

<h3>The Viewport Meta Tag</h3>
<p>First, always include this in your HTML <code>&lt;head&gt;</code>:</p>
<div class="code-block">
<pre><code>&lt;meta name="viewport" content="width=device-width, initial-scale=1.0"&gt;</code></pre>
</div>

<h3>Media Queries</h3>
<p>Media queries apply CSS rules only when certain conditions are met (e.g., screen width).</p>
<div class="code-block">
<pre><code>/* Base styles (mobile-first approach) */
.container {
  width: 100%;
  padding: 0 16px;
}

/* Tablet (768px and above) */
@media (min-width: 768px) {
  .container {
    max-width: 720px;
    margin: 0 auto;
  }
}

/* Desktop (1024px and above) */
@media (min-width: 1024px) {
  .container {
    max-width: 960px;
  }
}

/* Large desktop (1280px and above) */
@media (min-width: 1280px) {
  .container {
    max-width: 1200px;
  }
}</code></pre>
</div>

<h3>Responsive Navigation Pattern</h3>
<div class="code-block">
<pre><code>/* Mobile: hide nav links, show hamburger */
.nav-links { display: none; }
.hamburger { display: block; }

/* Desktop: show nav links, hide hamburger */
@media (min-width: 768px) {
  .nav-links { display: flex; }
  .hamburger { display: none; }
}</code></pre>
</div>

<h3>Responsive Images</h3>
<div class="code-block">
<pre><code>/* Always make images responsive */
img {
  max-width: 100%;
  height: auto;
}

/* Responsive background images */
.hero {
  background-image: url("hero.jpg");
  background-size: cover;
  background-position: center;
  min-height: 400px;
}</code></pre>
</div>', 1),

(8, 'CSS Grid Basics', '<h2>CSS Grid Basics</h2>
<p>CSS Grid is a powerful two-dimensional layout system. While Flexbox handles one direction at a time, Grid handles both rows and columns simultaneously.</p>

<div class="code-block">
<pre><code>/* Enable Grid */
.grid-container {
  display: grid;

  /* Define columns */
  grid-template-columns: 200px 1fr 1fr;    /* fixed + two equal */
  grid-template-columns: repeat(3, 1fr);   /* three equal columns */
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* responsive */

  /* Define rows */
  grid-template-rows: 60px 1fr 60px;

  /* Gaps */
  gap: 24px;
  row-gap: 16px;
  column-gap: 24px;
}

/* Item spanning */
.hero-banner {
  grid-column: 1 / -1;  /* span all columns */
}

.sidebar {
  grid-row: 1 / 3;       /* span 2 rows */
}</code></pre>
</div>

<h3>Page Layout Example</h3>
<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;div class="page-layout"&gt;
  &lt;header&gt;Header&lt;/header&gt;
  &lt;aside&gt;Sidebar&lt;/aside&gt;
  &lt;main&gt;Main Content&lt;/main&gt;
  &lt;footer&gt;Footer&lt;/footer&gt;
&lt;/div&gt;

/* CSS */
.page-layout {
  display: grid;
  grid-template-areas:
    "header header"
    "sidebar main"
    "footer footer";
  grid-template-columns: 250px 1fr;
  grid-template-rows: 60px 1fr 60px;
  min-height: 100vh;
}

header  { grid-area: header; }
aside   { grid-area: sidebar; }
main    { grid-area: main; }
footer  { grid-area: footer; }

/* Responsive: single column on mobile */
@media (max-width: 768px) {
  .page-layout {
    grid-template-areas:
      "header"
      "main"
      "footer";
    grid-template-columns: 1fr;
  }
  aside { display: none; }
}</code></pre>
</div>', 2);

(8, 'CSS Grid Basics', '<h2>CSS Grid Basics</h2>
<p>CSS Grid is a powerful two-dimensional layout system. While Flexbox handles one direction at a time, Grid handles both rows and columns simultaneously.</p>

<div class="code-block">
<pre><code>/* Enable Grid */
.grid-container {
  display: grid;

  /* Define columns */
  grid-template-columns: 200px 1fr 1fr;    /* fixed + two equal */
  grid-template-columns: repeat(3, 1fr);   /* three equal columns */
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* responsive */

  /* Define rows */
  grid-template-rows: 60px 1fr 60px;

  /* Gaps */
  gap: 24px;
  row-gap: 16px;
  column-gap: 24px;
}

/* Item spanning */
.hero-banner {
  grid-column: 1 / -1;  /* span all columns */
}

.sidebar {
  grid-row: 1 / 3;       /* span 2 rows */
}</code></pre>
</div>

<h3>Page Layout Example</h3>
<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;div class="page-layout"&gt;
  &lt;header&gt;Header&lt;/header&gt;
  &lt;aside&gt;Sidebar&lt;/aside&gt;
  &lt;main&gt;Main Content&lt;/main&gt;
  &lt;footer&gt;Footer&lt;/footer&gt;
&lt;/div&gt;

/* CSS */
.page-layout {
  display: grid;
  grid-template-areas:
    "header header"
    "sidebar main"
    "footer footer";
  grid-template-columns: 250px 1fr;
  grid-template-rows: 60px 1fr 60px;
  min-height: 100vh;
}

header  { grid-area: header; }
aside   { grid-area: sidebar; }
main    { grid-area: main; }
footer  { grid-area: footer; }

/* Responsive: single column on mobile */
@media (max-width: 768px) {
  .page-layout {
    grid-template-areas:
      "header"
      "main"
      "footer";
    grid-template-columns: 1fr;
  }
  aside { display: none; }
}</code></pre>
</div>', 2);

-- Module 2.5
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(14, 2, 'Advanced CSS & Modern Layouts', 'Explore advanced CSS features like Grid, Custom Properties, preprocessors, and animations for cutting-edge designs.', 5);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(14, 'CSS Grid Layout Deep Dive', '<h2>CSS Grid Layout Deep Dive</h2>
<p>While Flexbox excels at one-dimensional layouts, CSS Grid is your go-to for powerful two-dimensional control, allowing you to define complex page structures with ease.</p>

<h3>Grid Container Properties</h3>
<div class="code-block">
<pre><code>.grid-container {
  display: grid;

  /* Explicitly define rows and columns */
  grid-template-columns: 1fr 2fr 1fr; /* three columns: one auto, one double, one auto */
  grid-template-rows: auto 200px 50px; /* three rows: auto-height, 200px, 50px */

  /* Define areas for semantic layout control */
  grid-template-areas:
    "header header header"
    "nav    main   aside"
    "footer footer footer";

  /* Gaps between grid items */
  gap: 1rem; /* Shorthand for row-gap and column-gap */
  row-gap: 15px;
  column-gap: 20px;

  /* Implicit Grid: how to handle items that are not explicitly placed */
  grid-auto-rows: minmax(100px, auto); /* auto-create rows with min-height 100px */
  grid-auto-columns: 100px; /* auto-create columns with 100px width */
  grid-auto-flow: row; /* or column, dense */
}
</code></pre>
</div>

<h3>Grid Item Properties</h3>
<div class="code-block">
<pre><code>.grid-item {
  /* Placing items by line numbers */
  grid-column-start: 1;
  grid-column-end: 3;
  /* Shorthand */
  grid-column: 1 / 3; /* Spans from line 1 to line 3 */
  grid-row: 1 / span 2; /* Starts at row line 1, spans 2 rows */

  /* Placing items by grid areas */
  grid-area: header; /* Assigns item to the "header" area */

  /* Alignment within its grid cell */
  justify-self: start; /* horizontal alignment */
  align-self: center;  /* vertical alignment */
  place-self: center start; /* shorthand */
}
</code></pre>
</div>

<h3>Practical Example: Magazine Layout</h3>
<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;div class="magazine-layout"&gt;
  &lt;div class="hero"&gt;Hero Article&lt;/div&gt;
  &lt;div class="sidebar"&gt;Sidebar Ads&lt;/div&gt;
  &lt;div class="main-content"&gt;Main Content Area&lt;/div&gt;
  &lt;div class="small-ad"&gt;Small Ad&lt;/div&gt;
&lt;/div&gt;

/* CSS */
.magazine-layout {
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* 4 equal columns */
  grid-template-rows: auto 1fr auto; /* header, main, footer */
  grid-template-areas:
    "hero hero sidebar sidebar"
    "main main main small-ad"
    "footer footer footer footer";
  gap: 20px;
}

.hero { grid-area: hero; background: #f0f0f0; padding: 20px; }
.sidebar { grid-area: sidebar; background: #e0e0e0; padding: 20px; }
.main-content { grid-area: main; background: #d0d0d0; padding: 20px; }
.small-ad { grid-area: small-ad; background: #c0c0c0; padding: 20px; }

@media (max-width: 768px) {
  .magazine-layout {
    grid-template-areas:
      "hero"
      "sidebar"
      "main"
      "small-ad"
      "footer";
    grid-template-columns: 1fr; /* Single column layout on small screens */
  }
}
</code></pre>
</div>', 1),

(14, 'CSS Custom Properties (Variables)', '<h2>CSS Custom Properties (Variables)</h2>
<p>CSS Custom Properties, often called CSS Variables, allow you to define reusable values that can be used throughout your stylesheets. They make your CSS more maintainable, flexible, and dynamic.</p>

<h3>Declaring and Using Custom Properties</h3>
<div class="code-block">
<pre><code>/* Declare global custom properties on the :root pseudo-class */
:root {
  --primary-color: #007bff;
  --secondary-color: #6c757d;
  --spacing-md: 16px;
  --font-stack: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

/* Use custom properties with the var() function */
body {
  font-family: var(--font-stack);
  margin: var(--spacing-md);
}

h1 {
  color: var(--primary-color);
  margin-bottom: var(--spacing-md);
}

button {
  background-color: var(--primary-color);
  color: white;
  padding: 10px var(--spacing-md);
  border-radius: 5px;
  border: none;
}
</code></pre>
</div>

<h3>Scope of Custom Properties</h3>
<p>Custom properties are inherited. They can be defined globally (on `:root` or `html`) or locally within specific elements, making them scoped to that element and its children.</p>
<div class="code-block">
<pre><code>.dark-theme {
  --background-color: #333;
  --text-color: #eee;
}

.light-theme {
  --background-color: #fff;
  --text-color: #333;
}

body {
  background-color: var(--background-color);
  color: var(--text-color);
}
</code></pre>
</div>
<p>You can then toggle classes like `.dark-theme` or `.light-theme` on the `body` or a container to switch themes dynamically with minimal CSS changes.</p>

<h3>Fallback Values</h3>
<p>You can provide a fallback value in case the custom property is not defined:</p>
<div class="code-block">
<pre><code>.element {
  color: var(--undefined-color, hotpink); /* uses hotpink if --undefined-color is not set */
}
</code></pre>
</div>

<h3>Benefits of Custom Properties</h3>
<ul>
  <li><strong>Maintainability:</strong> Change a value in one place, and it updates everywhere.</li>
  <li><strong>Readability:</strong> Descriptive names (e.g., `--primary-color`) are clearer than hex codes.</li>
  <li><strong>Dynamic Theming:</strong> Easily implement light/dark modes or custom themes with JavaScript.</li>
  <li><strong>Component-based Styling:</strong> Define properties specific to components.</li>
</ul>', 2),

(14, 'Introduction to Sass/SCSS', '<h2>Introduction to Sass/SCSS</h2>
<p><strong>Sass (Syntactically Awesome Style Sheets)</strong> is a CSS preprocessor that extends CSS with features like variables, nesting, mixins, functions, and more. It helps you write more organized, maintainable, and efficient CSS. SCSS (Sassy CSS) is the main syntax for Sass, which is fully compatible with CSS syntax.</p>

<h3>Key Features</h3>
<ul>
  <li><strong>Variables:</strong> Store information like colors, font stacks, or any CSS value.</li>
  <li><strong>Nesting:</strong> Nest CSS selectors within each other, mirroring the HTML structure.</li>
  <li><strong>Partials & Imports:</strong> Break down your CSS into smaller, more manageable files and import them.</li>
  <li><strong>Mixins:</strong> Reusable blocks of CSS declarations.</li>
  <li><strong>Functions:</strong> Define custom operations that return values.</li>
</ul>

<h3>Sass Variables</h3>
<div class="code-block">
<pre><code>/* style.scss */
$primary-color: #007bff;
$font-stack: "Arial", sans-serif;

body {
  font-family: $font-stack;
  color: $primary-color;
}

/* Compiled CSS */
/* body {
  font-family: "Arial", sans-serif;
  color: #007bff;
} */
</code></pre>
</div>

<h3>Nesting</h3>
<div class="code-block">
<pre><code>/* style.scss */
.navbar {
  background-color: #333;
  ul {
    margin: 0;
    padding: 0;
    list-style: none;
    li {
      display: inline-block;
      a {
        display: block;
        padding: 15px;
        text-decoration: none;
        color: white;
        &:hover {
          background-color: #555;
        }
      }
    }
  }
}

/* Compiled CSS */
/* .navbar {
  background-color: #333;
}
.navbar ul {
  margin: 0;
  padding: 0;
  list-style: none;
}
.navbar ul li {
  display: inline-block;
}
.navbar ul li a {
  display: block;
  padding: 15px;
  text-decoration: none;
  color: white;
}
.navbar ul li a:hover {
  background-color: #555;
} */
</code></pre>
</div>

<h3>Mixins</h3>
<div class="code-block">
<pre><code>/* style.scss */
@mixin flex-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.container {
  @include flex-center;
  height: 100vh;
}

/* Compiled CSS */
/* .container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
} */
</code></pre>
</div>

<h3>Workflow</h3>
<p>To use Sass, you need a compiler (e.g., Node-Sass, LibSass, Dart Sass) to convert your `.scss` files into standard `.css` files that browsers can understand. Many build tools (Webpack, Gulp, Parcel) have Sass compilation integrated.</p>

<h3>Further Learning</h3>
<p>Sass offers much more, including control directives (`@if`, `@each`, `@for`), functions, and inheritance (`@extend`). It's a powerful tool for large and complex stylesheets.</p>', 3),

(14, 'CSS Animations and Transitions', '<h2>CSS Animations and Transitions</h2>
<p>CSS transitions and animations allow you to create dynamic and engaging user interfaces without needing JavaScript for simple effects. They bring your designs to life by smoothly changing property values over time.</p>

<h3>CSS Transitions</h3>
<p>Transitions provide a way to animate changes in CSS properties smoothly, rather than having them snap instantly. They are typically triggered by user actions or state changes (e.g., `:hover`, `:focus`).</p>

<div class="code-block">
<pre><code>/* HTML for example */
&lt;button class="animated-button"&gt;Hover Me&lt;/button&gt;

/* CSS */
.animated-button {
  background-color: #007bff;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;

  /* Define the transition */
  transition-property: background-color, transform;
  transition-duration: 0.3s;
  transition-timing-function: ease-out;
  transition-delay: 0s;
  /* Shorthand: */
  /* transition: background-color 0.3s ease-out, transform 0.3s ease-out; */
  transition: all 0.3s ease-out; /* Most common shorthand */
}

.animated-button:hover {
  background-color: #0056b3; /* Change background on hover */
  transform: scale(1.05);   /* Enlarge slightly on hover */
}
</code></pre>
</div>
<ul>
  <li>`transition-property`: The CSS property to animate (e.g., `opacity`, `transform`, `background-color`). Use `all` to transition all changeable properties.</li>
  <li>`transition-duration`: How long the transition takes (e.g., `0.3s`, `300ms`).</li>
  <li>`transition-timing-function`: Speed curve of the transition (e.g., `ease-in`, `ease-out`, `linear`, `cubic-bezier`).</li>
  <li>`transition-delay`: How long to wait before starting the transition.</li>
</ul>

<h3>CSS Animations (@keyframes)</h3>
<p>Animations allow for more complex, multi-stage, and continuous effects. They are defined using the `@keyframes` rule and then applied to an element using the `animation` properties.</p>

<div class="code-block">
<pre><code>/* HTML for example */
&lt;div class="loading-spinner"&gt;&lt;/div&gt;

/* CSS */
/* Define the animation sequence */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 5px solid rgba(0,0,0,0.1);
  border-top-color: #007bff;
  border-radius: 50%;
  
  /* Apply the animation */
  animation-name: spin;
  animation-duration: 1s;
  animation-timing-function: linear;
  animation-iteration-count: infinite; /* Loop forever */
  /* Shorthand: */
  /* animation: spin 1s linear infinite; */
}
</code></pre>
</div>
<ul>
  <li>`@keyframes`: Defines the animation stages. You specify CSS properties at different percentages (`0%` to `100%`, or `from` and `to`).</li>
  <li>`animation-name`: Links the element to a `@keyframes` rule.</li>
  <li>`animation-duration`: How long one cycle of the animation takes.</li>
  <li>`animation-timing-function`: Speed curve of the animation.</li>
  <li>`animation-iteration-count`: How many times the animation should run (`infinite` for continuous).</li>
  <li>`animation-direction`: Whether the animation should play forwards, backwards, or alternate.</li>
  <li>`animation-fill-mode`: What styles apply to the element before/after the animation runs.</li>
</ul>

<h3>When to Use Which?</h3>
<ul>
  <li><strong>Transitions:</strong> Best for simple, one-off changes between two states (e.g., hover effects, showing/hiding elements).</li>
  <li><strong>Animations:</strong> Best for complex, multi-stage sequences, continuous looping effects, or when you need more control over timing and direction.</li>
</ul>', 4);
INSERT INTO courses (id, title, description, status, order_index) VALUES
(3, 'JavaScript Fundamentals', 'Bring your web pages to life with JavaScript — the programming language of the web. Learn variables, functions, DOM manipulation, and events.', 'published', 3);

-- Module 3.1
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(9, 3, 'Variables and Data Types', 'Learn how JavaScript stores and works with data.', 1);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(9, 'Variables and Declarations', '<h2>Variables and Declarations</h2>
<p>Variables store data that your program can use and change. JavaScript has three ways to declare variables.</p>

<div class="code-block">
<pre><code>// let - use for values that will change
let score = 0;
score = 10;  // OK

// const - use for values that won't change
const PI = 3.14159;
const siteName = "HackathonAfrica";
// PI = 3; // ERROR! Cannot reassign const

// var - old way, avoid it in modern code
var name = "Amara";  // has function scope issues</code></pre>
</div>

<h3>Naming Rules</h3>
<ul>
  <li>Start with a letter, <code>$</code>, or <code>_</code> (not a number)</li>
  <li>Case-sensitive: <code>name</code> and <code>Name</code> are different</li>
  <li>Use camelCase for variables: <code>firstName</code>, <code>totalScore</code></li>
  <li>Use UPPER_SNAKE_CASE for constants: <code>MAX_SCORE</code></li>
  <li>Cannot use reserved words: <code>let</code>, <code>if</code>, <code>class</code>, etc.</li>
</ul>

<h3>Data Types</h3>
<div class="code-block">
<pre><code>// String - text
let name = "Kwame";
let greeting = `Hello, ${name}!`;  // template literal

// Number - integers and decimals
let age = 25;
let price = 9.99;

// Boolean - true or false
let isLoggedIn = true;
let hasCompleted = false;

// null - intentionally empty
let result = null;

// undefined - declared but not assigned
let data;
console.log(data);  // undefined

// Array - ordered list of values
let courses = ["HTML", "CSS", "JavaScript"];
let scores = [85, 92, 78];

// Object - key-value pairs
let user = {
  name: "Amara",
  age: 22,
  isStudent: true
};

// Check the type of a value
console.log(typeof "hello");    // "string"
console.log(typeof 42);         // "number"
console.log(typeof true);       // "boolean"
console.log(typeof undefined);  // "undefined"
console.log(typeof null);       // "object" (quirk of JS)</code></pre>
</div>', 1),

(9, 'Operators and Conditionals', '<h2>Operators and Conditionals</h2>

<h3>Arithmetic Operators</h3>
<div class="code-block">
<pre><code>let a = 10, b = 3;

console.log(a + b);   // 13 (addition)
console.log(a - b);   // 7  (subtraction)
console.log(a * b);   // 30 (multiplication)
console.log(a / b);   // 3.33... (division)
console.log(a % b);   // 1  (remainder/modulo)
console.log(a ** b);  // 1000 (exponentiation)

// Increment / Decrement
let count = 0;
count++;   // count is now 1
count--;   // count is now 0
count += 5; // count is now 5
count *= 2; // count is now 10</code></pre>
</div>

<h3>Comparison Operators</h3>
<div class="code-block">
<pre><code>// Always use === (strict equality) not == (loose equality)
console.log(5 === 5);    // true
console.log(5 === "5");  // false (different types)
console.log(5 == "5");   // true (type coercion - avoid!)

console.log(5 !== 3);   // true
console.log(5 > 3);     // true
console.log(5 < 3);     // false
console.log(5 >= 5);    // true
console.log(5 <= 4);    // false</code></pre>
</div>

<h3>Conditional Statements</h3>
<div class="code-block">
<pre><code>let score = 85;

// if / else if / else
if (score >= 90) {
  console.log("Excellent!");
} else if (score >= 70) {
  console.log("Good job!");
} else if (score >= 50) {
  console.log("Keep trying.");
} else {
  console.log("Needs improvement.");
}

// Ternary operator (short if/else)
let message = score >= 70 ? "Passed!" : "Try again";

// Switch statement
let day = "Monday";
switch (day) {
  case "Monday":
    console.log("Start of the week!");
    break;
  case "Friday":
    console.log("Weekend coming up!");
    break;
  default:
    console.log("Midweek hustle.");
}

// Logical operators
let isLoggedIn = true;
let isAdmin = false;

if (isLoggedIn && isAdmin) {
  console.log("Welcome, Admin!");     // AND
}
if (isLoggedIn || isAdmin) {
  console.log("At least one true");  // OR
}
if (!isAdmin) {
  console.log("Not an admin");       // NOT
}</code></pre>
</div>', 2);

-- Module 3.2
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(10, 3, 'Functions', 'Write reusable blocks of code with functions.', 2);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(10, 'Functions and Scope', '<h2>Functions and Scope</h2>
<p>Functions let you group code into reusable blocks. Instead of repeating the same code, you write it once and call it whenever needed.</p>

<h3>Declaring Functions</h3>
<div class="code-block">
<pre><code>// Function declaration
function greet(name) {
  return `Hello, ${name}!`;
}
console.log(greet("Amara"));  // Hello, Amara!

// Function expression
const add = function(a, b) {
  return a + b;
};

// Arrow function (modern, concise syntax)
const multiply = (a, b) => a * b;
const square = n => n * n;  // single parameter, no parentheses needed

// Arrow function with block body
const calculateGrade = (score) => {
  if (score >= 90) return "A";
  if (score >= 80) return "B";
  if (score >= 70) return "C";
  return "F";
};</code></pre>
</div>

<h3>Parameters and Default Values</h3>
<div class="code-block">
<pre><code>// Default parameters
function createUser(name, role = "student") {
  return { name, role };
}

console.log(createUser("Kwame"));          // { name: "Kwame", role: "student" }
console.log(createUser("Admin", "admin")); // { name: "Admin", role: "admin" }

// Rest parameters (collect multiple arguments)
function sumAll(...numbers) {
  return numbers.reduce((total, n) => total + n, 0);
}
console.log(sumAll(1, 2, 3, 4, 5));  // 15</code></pre>
</div>

<h3>Scope</h3>
<div class="code-block">
<pre><code>// Global scope - accessible everywhere
let globalVar = "I am global";

function myFunction() {
  // Local scope - only accessible inside this function
  let localVar = "I am local";
  console.log(globalVar);  // OK - can access global
  console.log(localVar);   // OK - local variable
}

console.log(globalVar);  // OK
// console.log(localVar); // ERROR - not accessible outside

// Block scope with let and const
if (true) {
  let blockVar = "I am block scoped";
  const blockConst = "Me too";
}
// console.log(blockVar);  // ERROR - not accessible outside block</code></pre>
</div>

<h3>Common Array Methods with Functions</h3>
<div class="code-block">
<pre><code>const scores = [85, 92, 78, 95, 60];

// forEach - loop through each item
scores.forEach(score => console.log(score));

// map - transform each item, return new array
const grades = scores.map(score => score >= 70 ? "Pass" : "Fail");

// filter - keep items that match condition
const passing = scores.filter(score => score >= 70);
// [85, 92, 78, 95]

// find - get first matching item
const highScore = scores.find(score => score > 90);
// 92

// reduce - collapse array to single value
const total = scores.reduce((sum, score) => sum + score, 0);
const average = total / scores.length;  // 82</code></pre>
</div>', 1),

(10, 'Arrays and Objects Deep Dive', '<h2>Arrays and Objects Deep Dive</h2>

<h3>Working with Arrays</h3>
<div class="code-block">
<pre><code>let fruits = ["mango", "banana", "orange"];

// Access by index (starts at 0)
console.log(fruits[0]);   // "mango"
console.log(fruits.length); // 3

// Add and remove
fruits.push("pineapple");    // add to end
fruits.unshift("guava");     // add to beginning
fruits.pop();                // remove from end
fruits.shift();              // remove from beginning

// Find index
let idx = fruits.indexOf("banana"); // 1

// Slice (extract without modifying original)
let slice = fruits.slice(1, 3);  // ["banana", "orange"]

// Splice (modify original - remove/insert)
fruits.splice(1, 1, "pawpaw");  // replace banana with pawpaw

// Join array to string
let str = fruits.join(", ");  // "mango, pawpaw, orange"

// Sort
fruits.sort();  // alphabetical
[3, 1, 4, 1, 5].sort((a, b) => a - b);  // numeric sort</code></pre>
</div>

<h3>Working with Objects</h3>
<div class="code-block">
<pre><code>const student = {
  name: "Amara",
  age: 22,
  courses: ["HTML", "CSS", "JavaScript"],
  address: {
    city: "Accra",
    country: "Ghana"
  },
  greet() {
    return `Hi, I am ${this.name}`;
  }
};

// Access properties
console.log(student.name);           // "Amara"
console.log(student["age"]);         // 22
console.log(student.address.city);   // "Accra"
console.log(student.greet());        // "Hi, I am Amara"

// Add / modify properties
student.email = "amara@example.com";
student.age = 23;

// Delete property
delete student.address;

// Check if property exists
console.log("name" in student);           // true
console.log(student.hasOwnProperty("age")); // true

// Destructuring
const { name, age, courses } = student;
console.log(name);  // "Amara"

// Spread operator
const updated = { ...student, age: 24 };

// Object.keys, values, entries
Object.keys(student);    // ["name", "age", "courses", ...]
Object.values(student);  // ["Amara", 22, [...], ...]
Object.entries(student); // [["name","Amara"], ["age",22], ...]</code></pre>
</div>', 2);

-- Module 3.3
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(11, 3, 'DOM Manipulation', 'Control web page elements with JavaScript.', 3);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(11, 'Selecting and Modifying DOM Elements', '<h2>Selecting and Modifying DOM Elements</h2>
<p>The DOM (Document Object Model) is a tree representation of your HTML. JavaScript lets you read and change any part of it.</p>

<h3>Selecting Elements</h3>
<div class="code-block">
<pre><code>// By ID (returns single element)
const header = document.getElementById("main-header");

// By CSS selector (returns first match)
const btn = document.querySelector(".btn-primary");
const input = document.querySelector("#email");

// By CSS selector (returns all matches - NodeList)
const cards = document.querySelectorAll(".card");
const links = document.querySelectorAll("nav a");

// Iterate NodeList
cards.forEach(card => {
  console.log(card.textContent);
});</code></pre>
</div>

<h3>Modifying Content</h3>
<div class="code-block">
<pre><code>const heading = document.querySelector("h1");

// Change text (safe, no HTML)
heading.textContent = "Welcome to HackathonAfrica!";

// Change HTML (can inject tags - careful with user input!)
const div = document.querySelector(".info");
div.innerHTML = "&lt;strong&gt;Important:&lt;/strong&gt; Complete all lessons.";

// Read/set attribute
const img = document.querySelector("img");
img.getAttribute("src");            // get
img.setAttribute("alt", "Logo");    // set
img.removeAttribute("hidden");      // remove</code></pre>
</div>

<h3>Modifying Styles and Classes</h3>
<div class="code-block">
<pre><code>const element = document.querySelector(".box");

// Direct style changes (use classes instead when possible)
element.style.color = "red";
element.style.backgroundColor = "#f0f0f0";
element.style.display = "none";

// Classes (preferred approach)
element.classList.add("active");
element.classList.remove("hidden");
element.classList.toggle("expanded");     // add if absent, remove if present
element.classList.contains("active");     // true/false check
element.className = "card card-primary"; // replace all classes</code></pre>
</div>

<h3>Creating and Removing Elements</h3>
<div class="code-block">
<pre><code>// Create new element
const newCard = document.createElement("div");
newCard.className = "card";
newCard.textContent = "New Course Card";

// Add to page
const container = document.querySelector(".container");
container.appendChild(newCard);          // add at end
container.prepend(newCard);             // add at beginning
container.insertBefore(newCard, ref);   // before reference element

// Remove elements
const old = document.querySelector(".outdated");
old.remove();
// Or: old.parentNode.removeChild(old);</code></pre>
</div>', 1),

(11, 'Reading and Changing the Page', '<h2>Reading and Changing the Page</h2>
<p>Practical DOM manipulation — the patterns you will use in almost every project.</p>

<h3>Working with Forms</h3>
<div class="code-block">
<pre><code>// Get form values
const nameInput = document.querySelector("#name");
const emailInput = document.querySelector("#email");
const checkbox = document.querySelector("#agree");
const select = document.querySelector("#country");

console.log(nameInput.value);     // text input value
console.log(emailInput.value);    // email input value
console.log(checkbox.checked);    // true/false
console.log(select.value);        // selected option value

// Set values
nameInput.value = "Amara";
checkbox.checked = true;

// Clear a form
document.querySelector("form").reset();</code></pre>
</div>

<h3>Building a Live List</h3>
<div class="code-block">
<pre><code>&lt;!-- HTML --&gt;
&lt;div&gt;
  &lt;input type="text" id="task-input" placeholder="Add a task"&gt;
  &lt;button id="add-btn"&gt;Add&lt;/button&gt;
  &lt;ul id="task-list"&gt;&lt;/ul&gt;
&lt;/div&gt;

/* JavaScript */
const input = document.querySelector("#task-input");
const btn = document.querySelector("#add-btn");
const list = document.querySelector("#task-list");

btn.addEventListener("click", function() {
  const taskText = input.value.trim();
  if (!taskText) return;  // ignore empty input

  const li = document.createElement("li");
  li.textContent = taskText;

  // Add delete button
  const deleteBtn = document.createElement("button");
  deleteBtn.textContent = "Delete";
  deleteBtn.addEventListener("click", () => li.remove());
  li.appendChild(deleteBtn);

  list.appendChild(li);
  input.value = "";   // clear input
  input.focus();      // return focus to input
});</code></pre>
</div>

<h3>Changing Styles Dynamically</h3>
<div class="code-block">
<pre><code>// Progress bar update
function updateProgress(percent) {
  const bar = document.querySelector(".progress-bar");
  bar.style.width = percent + "%";
  bar.textContent = percent + "%";

  if (percent === 100) {
    bar.classList.add("complete");
  }
}

updateProgress(75);</code></pre>
</div>', 2);

-- Module 3.4
INSERT INTO modules (id, course_id, title, description, order_index) VALUES
(12, 3, 'Events and Interactivity', 'Respond to user actions to create dynamic experiences.', 4);

INSERT INTO lessons (module_id, title, content, order_index) VALUES
(12, 'Event Listeners', '<h2>Event Listeners</h2>
<p>Events are actions that happen in the browser — a click, a key press, a form submission. JavaScript lets you listen for these events and respond to them.</p>

<h3>Adding Event Listeners</h3>
<div class="code-block">
<pre><code>const btn = document.querySelector("#myBtn");

// addEventListener (preferred)
btn.addEventListener("click", function(event) {
  console.log("Button clicked!");
  console.log(event);  // event object with details
});

// Arrow function
btn.addEventListener("click", (e) => {
  console.log("Clicked at:", e.clientX, e.clientY);
});

// Named function (can be removed later)
function handleClick() {
  console.log("Clicked!");
}
btn.addEventListener("click", handleClick);
btn.removeEventListener("click", handleClick);  // removes listener</code></pre>
</div>

<h3>Common Events</h3>
<div class="code-block">
<pre><code>// Mouse events
element.addEventListener("click", handler);
element.addEventListener("dblclick", handler);
element.addEventListener("mouseover", handler);
element.addEventListener("mouseout", handler);

// Keyboard events
document.addEventListener("keydown", (e) => {
  console.log(e.key);      // "Enter", "Escape", "a", etc.
  console.log(e.keyCode);  // numeric code
  if (e.key === "Enter") {
    submitForm();
  }
});

// Form events
form.addEventListener("submit", (e) => {
  e.preventDefault();  // stop page from reloading
  processForm();
});
input.addEventListener("input", (e) => {
  console.log("Current value:", e.target.value);
});
input.addEventListener("focus", () => { /* input focused */ });
input.addEventListener("blur", () => { /* input lost focus */ });

// Window events
window.addEventListener("load", () => { /* page fully loaded */ });
window.addEventListener("resize", () => {
  console.log("Window width:", window.innerWidth);
});</code></pre>
</div>

<h3>The Event Object</h3>
<div class="code-block">
<pre><code>document.addEventListener("click", (e) => {
  e.target;          // element that was clicked
  e.currentTarget;   // element listener is attached to
  e.preventDefault(); // stop default behavior (e.g., link navigation)
  e.stopPropagation(); // stop event from bubbling up
  e.clientX;         // x position of mouse
  e.clientY;         // y position of mouse
});</code></pre>
</div>', 1),

(12, 'Building Interactive Components', '<h2>Building Interactive Components</h2>
<p>Let us put everything together by building three real, interactive UI components.</p>

<h3>Project 1: Quiz Component</h3>
<div class="code-block">
<pre><code>const question = {
  text: "What does HTML stand for?",
  options: [
    "HyperText Markup Language",
    "High Transfer Markup Language",
    "HyperText Making Language",
    "HyperText Management Language"
  ],
  correct: 0
};

function buildQuiz(q) {
  const container = document.querySelector("#quiz");
  container.innerHTML = `&lt;h3&gt;${q.text}&lt;/h3&gt;`;

  q.options.forEach((option, index) => {
    const btn = document.createElement("button");
    btn.textContent = option;
    btn.addEventListener("click", () => checkAnswer(index, q.correct, btn));
    container.appendChild(btn);
  });
}

function checkAnswer(selected, correct, btn) {
  const allBtns = document.querySelectorAll("#quiz button");
  allBtns.forEach(b => b.disabled = true);  // disable all

  if (selected === correct) {
    btn.classList.add("correct");
    showFeedback("Correct! Well done.", "success");
  } else {
    btn.classList.add("wrong");
    allBtns[correct].classList.add("correct");
    showFeedback("Incorrect. The correct answer is highlighted.", "error");
  }
}

function showFeedback(message, type) {
  const feedback = document.querySelector("#feedback");
  feedback.textContent = message;
  feedback.className = type;
}

buildQuiz(question);</code></pre>
</div>

<h3>Project 2: Accordion (Toggle Sections)</h3>
<div class="code-block">
<pre><code>// Assuming HTML:
// &lt;div class="accordion-item"&gt;
//   &lt;button class="accordion-header"&gt;Section 1&lt;/button&gt;
//   &lt;div class="accordion-body"&gt;Content here&lt;/div&gt;
// &lt;/div&gt;

const headers = document.querySelectorAll(".accordion-header");

headers.forEach(header => {
  header.addEventListener("click", function() {
    const body = this.nextElementSibling;

    // Close all others
    document.querySelectorAll(".accordion-body").forEach(b => {
      if (b !== body) b.style.display = "none";
    });

    // Toggle current
    body.style.display = body.style.display === "block" ? "none" : "block";
  });
});</code></pre>
</div>

<h3>Project 3: Live Character Counter</h3>
<div class="code-block">
<pre><code>const textarea = document.querySelector("#bio");
const counter = document.querySelector("#char-count");
const maxLength = 200;

textarea.addEventListener("input", function() {
  const remaining = maxLength - this.value.length;
  counter.textContent = `${remaining} characters remaining`;

  if (remaining < 20) {
    counter.style.color = "red";
  } else {
    counter.style.color = "#666";
  }
});</code></pre>
</div>', 2);

-- ============================================================
-- QUIZZES
-- ============================================================

-- Quiz for Module 1.1 (Intro to Web)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (1, 1, 'Introduction to the Web Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(1, 1, 'What does HTML stand for?', 1),
(2, 1, 'In the client-server model, what is the "client"?', 2),
(3, 1, 'Which tool do you press F12 to open in the browser?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(1, 'HyperText Markup Language', 1), (1, 'High Transfer Markup Language', 0), (1, 'HyperText Making Language', 0), (1, 'Hyperlink Text Machine Language', 0),
(2, 'The web server', 0), (2, 'The browser that requests content', 1), (2, 'The database', 0), (2, 'The DNS server', 0),
(3, 'Developer Tools', 1), (3, 'Task Manager', 0), (3, 'Source Code', 0), (3, 'Settings Panel', 0);

-- Quiz for Module 1.2 (HTML Doc Structure)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (2, 2, 'HTML Document Structure Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(4, 2, 'Which tag wraps all visible page content?', 1),
(5, 2, 'What does the <meta charset="UTF-8"> tag do?', 2),
(6, 2, 'How many <h1> tags should a page ideally have?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(4, '<head>', 0), (4, '<body>', 1), (4, '<html>', 0), (4, '<main>', 0),
(5, 'Sets the page language', 0), (5, 'Sets the character encoding', 1), (5, 'Adds a page description', 0), (5, 'Links a stylesheet', 0),
(6, 'As many as needed', 0), (6, 'Two', 0), (6, 'One', 1), (6, 'Six', 0);

-- Quiz for Module 1.3 (Tags and Elements)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (3, 3, 'Tags and Elements Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(7, 3, 'Which attribute is required on <img> tags for accessibility?', 1),
(8, 3, 'Which HTML element represents navigation links?', 2),
(9, 3, 'What does target="_blank" do on a link?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(7, 'src', 0), (7, 'href', 0), (7, 'alt', 1), (7, 'title', 0),
(8, '<section>', 0), (8, '<nav>', 1), (8, '<menu>', 0), (8, '<header>', 0),
(9, 'Opens the link in a new tab', 1), (9, 'Downloads the linked file', 0), (9, 'Opens in the same tab', 0), (9, 'Opens in a popup', 0);

-- Quiz for Module 1.4 (Forms)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (4, 4, 'Forms and Inputs Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(10, 4, 'Which form method should be used for sensitive data like passwords?', 1),
(11, 4, 'What attribute makes a form field mandatory?', 2),
(12, 4, 'What input type validates email format automatically?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(10, 'GET', 0), (10, 'POST', 1), (10, 'PUT', 0), (10, 'SEND', 0),
(11, 'mandatory', 0), (11, 'required', 1), (11, 'validate', 0), (11, 'important', 0),
(12, 'text', 0), (12, 'string', 0), (12, 'email', 1), (12, 'address', 0);

-- Quiz for Module 2.1 (Selectors)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (5, 5, 'Selectors and Styling Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(13, 5, 'Which CSS selector targets an element with class "card"?', 1),
(14, 5, 'What is the correct CSS property to change text color?', 2),
(15, 5, 'Which unit is relative to the root element font size?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(13, '#card', 0), (13, '.card', 1), (13, 'card', 0), (13, '@card', 0),
(14, 'text-color', 0), (14, 'font-color', 0), (14, 'color', 1), (14, 'foreground-color', 0),
(15, 'px', 0), (15, 'em', 0), (15, 'rem', 1), (15, 'vw', 0);

-- Quiz for Module 2.2 (Box Model)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (6, 6, 'Box Model Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(16, 6, 'Which box model property adds space INSIDE the border?', 1),
(17, 6, 'What CSS value includes padding and border in the element width?', 2),
(18, 6, 'Which display value removes an element from the page completely?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(16, 'margin', 0), (16, 'padding', 1), (16, 'border', 0), (16, 'outline', 0),
(17, 'box-sizing: content-box', 0), (17, 'box-sizing: border-box', 1), (17, 'box-sizing: padding-box', 0), (17, 'box-model: include', 0),
(18, 'display: hidden', 0), (18, 'visibility: hidden', 0), (18, 'display: none', 1), (18, 'opacity: 0', 0);

-- Quiz for Module 2.3 (Flexbox)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (7, 7, 'Flexbox Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(19, 7, 'Which property enables flexbox on a container?', 1),
(20, 7, 'Which flexbox property centers items along the main axis?', 2),
(21, 7, 'What does flex-wrap: wrap do?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(19, 'display: flex', 1), (19, 'position: flex', 0), (19, 'flex: enable', 0), (19, 'layout: flex', 0),
(20, 'align-items', 0), (20, 'justify-content', 1), (20, 'flex-align', 0), (20, 'content-align', 0),
(21, 'Items overflow the container', 0), (21, 'Items wrap to the next line when needed', 1), (21, 'Items shrink to fit', 0), (21, 'Items are deleted when they overflow', 0);

-- Quiz for Module 2.4 (Responsive)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (8, 8, 'Responsive Design Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(22, 8, 'What meta tag is required for responsive design?', 1),
(23, 8, 'Which CSS rule applies styles only when screen is 768px or wider?', 2),
(24, 8, 'What CSS property makes images scale down on small screens?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(22, 'meta charset', 0), (22, 'meta viewport', 1), (22, 'meta responsive', 0), (22, 'meta screen', 0),
(23, '@media (max-width: 768px)', 0), (23, '@media (min-width: 768px)', 1), (23, '@screen (min: 768px)', 0), (23, '@responsive (768px)', 0),
(24, 'width: 100%', 0), (24, 'max-width: 100%', 1), (24, 'size: responsive', 0), (24, 'image-fit: scale', 0);

-- Quiz for Module 3.1 (Variables)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (9, 9, 'Variables and Data Types Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(25, 9, 'Which keyword declares a variable that cannot be reassigned?', 1),
(26, 9, 'What is the output of: typeof "hello"?', 2),
(27, 9, 'Which comparison operator checks both value AND type?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(25, 'let', 0), (25, 'var', 0), (25, 'const', 1), (25, 'fixed', 0),
(26, '"text"', 0), (26, '"string"', 1), (26, '"word"', 0), (26, '"str"', 0),
(27, '==', 0), (27, '===', 1), (27, '=', 0), (27, '!==', 0);

-- Quiz for Module 3.2 (Functions)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (10, 10, 'Functions Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(28, 10, 'What does the return statement do in a function?', 1),
(29, 10, 'Which array method creates a NEW array by transforming each item?', 2),
(30, 10, 'What is the arrow function syntax for: function add(a, b) { return a + b; }?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(28, 'Stops the script entirely', 0), (28, 'Outputs a value from the function', 1), (28, 'Declares a new variable', 0), (28, 'Calls another function', 0),
(29, 'forEach', 0), (29, 'filter', 0), (29, 'map', 1), (29, 'reduce', 0),
(30, 'add => (a, b) a + b', 0), (30, 'const add = (a, b) => a + b', 1), (30, 'const add = a, b -> a + b', 0), (30, 'function add => (a, b) a + b', 0);

-- Quiz for Module 3.3 (DOM)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (11, 11, 'DOM Manipulation Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(31, 11, 'Which method selects the first matching element by CSS selector?', 1),
(32, 11, 'What property changes the text content of an element safely?', 2),
(33, 11, 'Which method adds a CSS class to an element?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(31, 'document.getElementById()', 0), (31, 'document.querySelector()', 1), (31, 'document.getElement()', 0), (31, 'document.select()', 0),
(32, 'innerHTML', 0), (32, 'textContent', 1), (32, 'value', 0), (32, 'innerText', 0),
(33, 'element.addClass()', 0), (33, 'element.classList.add()', 1), (33, 'element.setClass()', 0), (33, 'element.class.push()', 0);

-- Quiz for Module 3.4 (Events)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (12, 12, 'Events and Interactivity Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(34, 12, 'Which method attaches an event handler to an element?', 1),
(35, 12, 'What does event.preventDefault() do?', 2),
(36, 12, 'Which event fires when a form is submitted?', 3);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(34, 'element.on()', 0), (34, 'element.addEventListener()', 1), (34, 'element.listen()', 0), (34, 'element.attachEvent()', 0),
(35, 'Stops the event from firing', 0), (35, 'Stops the default browser action (e.g., form submission reload)', 1), (35, 'Removes the event listener', 0), (35, 'Cancels all pending events', 0),
(36, 'change', 0), (36, 'input', 0), (36, 'submit', 1), (36, 'click', 0);

-- Quiz for Module 1.5 (Advanced HTML & Accessibility)
INSERT INTO quizzes (id, module_id, title, pass_mark) VALUES (13, 13, 'Advanced HTML & Accessibility Quiz', 70);
INSERT INTO quiz_questions (id, quiz_id, question_text, order_index) VALUES
(37, 13, 'What is the primary benefit of using semantic HTML5 elements?', 1),
(38, 13, 'Which ARIA attribute would you use to provide a label for an element that doesn''t have visible text?', 2),
(39, 13, 'What is the purpose of the srcset attribute in an <img> tag?', 3),
(40, 13, 'Which HTML element is best suited for wrapping an image with a caption?', 4),
(41, 13, 'What is the Open Graph protocol primarily used for?', 5);
INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES
(37, 'To make your code shorter', 0), (37, 'To improve page loading speed', 0), (37, 'To provide better structure and meaning for accessibility and SEO', 1), (37, 'To apply styles more easily with CSS', 0),
(38, 'aria-description', 0), (38, 'aria-text', 0), (38, 'aria-label', 1), (38, 'aria-name', 0),
(39, 'To specify the image''s height and width', 0), (39, 'To provide different image sources based on screen resolution or viewport width', 1), (39, 'To add a tooltip when hovering over the image', 0), (39, 'To lazy load images for performance', 0),
(40, '<section>', 0), (40, '<div>', 0), (40, '<figure>', 1), (40, '<span>', 0),
(41, 'To define metadata for web analytics tools', 0), (41, 'To control how content appears when shared on social media platforms', 1), (41, 'To optimize image delivery for different devices', 0), (41, 'To secure data transmission over HTTP', 0);
