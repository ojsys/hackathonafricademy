<?php
/**
 * Migration v7 — Seed the "Python: From Zero to Developer" course.
 * Run ONCE via browser, then DELETE this file.
 *
 *   Access: /database/migrate_v7.php?key=hackathon2026python
 *
 * This is the COMPREHENSIVE Python course. It is being built module-by-module.
 * Currently seeded: Module 1 (Fundamentals & Setup) + Module 2 (Core Syntax &
 * Data Types), each with rich HTML lessons and an end-of-module quiz.
 * More modules (Data Structures, Functions, OOP + 4 principles, SOLID/clean
 * code, Errors, Files, venv/pip, Testing, Capstone) are appended later.
 *
 * EACH lesson and module has a `video_url` slot left EMPTY on purpose, with a
 * "Watch as a guide" box inside the content suggesting a search term. Curate the
 * real creator video and paste the YouTube *embed* URL via Admin → Lessons.
 *
 * IDEMPOTENT: if the course already exists it is deleted (cascade) and re-seeded,
 * so re-running always yields one clean copy. Touches no other course's data.
 *
 * Production is SQLite with live student data — this script only creates/replaces
 * THIS one course (matched by title) and never alters users, attempts, or other
 * courses.
 */

define('MIGRATION_PASSWORD', 'hackathon2026python');
if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026python to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();

header('Content-Type: text/html; charset=utf-8');

$COURSE_TITLE = 'Python: From Zero to Developer';
$COURSE_DESC  = 'A complete, beginner-friendly path that turns you into a working Python developer. '
    . 'Detailed explanations, diagrams, and worked examples cover Python fundamentals, core syntax and '
    . 'data structures, functions, object-oriented programming (with the four OOP principles and SOLID '
    . 'explained in depth), error handling, file handling, virtual environments and packaging, and how to '
    . 'write clean, testable code. Every lesson links to a hand-picked video guide so you can watch and read together.';

/*
 * ──────────────────────────────────────────────────────────────────────────
 * CONTENT
 * Each module: title, description, video_url, lessons[], quiz{}.
 * Each lesson: title, minutes, video_url, content (HTML).
 * Each quiz:   title, pass_mark, questions[] -> {q, explain, options[[text,correct]]}.
 * Lesson content is authored HTML rendered as-is by pages/lesson.php.
 * Inside <pre><code> blocks, < > & are written as &lt; &gt; &amp; so they show.
 * ──────────────────────────────────────────────────────────────────────────
 */

/** Small helper to build the consistent "watch as a guide" box. */
function vid_box(string $what, string $search): string {
    $what = htmlspecialchars($what);
    $search = htmlspecialchars($search);
    return <<<HTML
<div class="alert alert-secondary d-flex align-items-start gap-2 mt-4" style="border-left:4px solid #6c757d">
  <i class="bi bi-youtube text-danger fs-5"></i>
  <div><strong>Watch as a guide:</strong> $what<br>
  <span class="text-muted small">Suggested search: &ldquo;$search&rdquo; — then paste the YouTube <em>embed</em> URL into this lesson via Admin → Lessons.</span></div>
</div>
HTML;
}

$MODULES = [

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 1 — PYTHON FUNDAMENTALS: GETTING STARTED
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Python Fundamentals: Getting Started',
  'description' => 'What Python is, how it actually runs your code, how to install it, and how to write and run your first programs. You will leave this module able to set up Python and reason about variables and objects.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'What is Python and how does it run?',
      'minutes' => 14,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What is Python?</h2>
<p>Python is a <strong>general-purpose programming language</strong>. "General-purpose" means it is
not locked to one job — people use the exact same language to build websites, automate boring
tasks, analyse data, train AI models, control robots, and write desktop tools.</p>
<p>Two things make Python special for learners:</p>
<ul>
<li><strong>It reads like English.</strong> <code>if age &gt; 18: print("adult")</code> almost says what it does.</li>
<li><strong>It hides the hard machine details.</strong> You don't manage memory by hand or declare the
type of every variable. You focus on <em>ideas</em>, and Python handles the plumbing.</li>
</ul>

<h2>Source code, the interpreter, and bytecode</h2>
<p>You write Python in plain text files ending in <code>.py</code>. That text is called <strong>source
code</strong> — it is for humans. Your computer's CPU cannot run it directly. Something has to
translate it. That something is the <strong>Python interpreter</strong> (a program usually called
<code>python</code> or <code>python3</code>).</p>
<p>Here is what happens when you run a file:</p>
<pre><code>  your_file.py            Python interpreter                  CPU
 (source code)   ─────►   compiles to bytecode   ─────►   runs it,
 "human text"            (.pyc, a simpler form)          line by line
</code></pre>
<p>Python first <em>compiles</em> your source into <strong>bytecode</strong> (a compact set of
instructions, sometimes cached in a <code>__pycache__</code> folder), then a part of the interpreter
called the <strong>Python Virtual Machine</strong> executes that bytecode. You don't have to manage any
of this — but knowing it exists explains messages like <code>SyntaxError</code> (caught while
compiling, before anything runs).</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> Think of source code as a recipe written in English. The interpreter is a
chef who reads the recipe, rewrites it into quick shorthand notes (bytecode), and then cooks
step by step. You hand over the recipe; you don't need to know how the chef chops onions.
</div>

<h2>Two ways to run Python</h2>
<p>You will use both constantly:</p>
<h3>1. The REPL (interactive mode)</h3>
<p>Type <code>python</code> in a terminal and you get a prompt (<code>&gt;&gt;&gt;</code>). You type one line,
press Enter, and Python runs it <em>immediately</em> and shows the result. REPL stands for
<strong>R</strong>ead–<strong>E</strong>val–<strong>P</strong>rint <strong>L</strong>oop.</p>
<pre><code>&gt;&gt;&gt; 2 + 2
4
&gt;&gt;&gt; "hi" * 3
'hihihi'
</code></pre>
<p>The REPL is your scratchpad: perfect for trying an idea or checking "what does this do?".</p>
<h3>2. Scripts (files)</h3>
<p>Real programs live in <code>.py</code> files you run all at once:</p>
<pre><code>python hello.py
</code></pre>
<p>Use the REPL to <em>explore</em>; use scripts to <em>keep</em> work you want to run again.</p>

<h2>CPython, versions, and "Python 3"</h2>
<p>The standard interpreter most people mean by "Python" is <strong>CPython</strong> (written in C).
Always use <strong>Python 3</strong> — Python 2 reached end-of-life and is dead. When you see
<code>python3 --version</code> printing something like <code>Python 3.12.x</code>, you are good.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Confusing the language with an editor.</strong> Python is the interpreter; VS Code, PyCharm,
and Jupyter are just tools that <em>run</em> it. You can write Python in Notepad if you want.</li>
<li><strong>Using Python 2.</strong> On some Macs/Linux, bare <code>python</code> is old Python 2. Prefer
<code>python3</code> until you confirm.</li>
<li><strong>Expecting compiled <code>.exe</code> files.</strong> Python normally needs the interpreter present
to run. (Tools like PyInstaller can bundle one, but that is advanced.)</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Python is a readable, general-purpose language that hides machine details.</li>
<li>You write source <code>.py</code> files; the <strong>interpreter</strong> compiles them to <strong>bytecode</strong>
and runs them.</li>
<li>The <strong>REPL</strong> runs single lines interactively; <strong>scripts</strong> run a whole file.</li>
<li>Use <strong>Python 3</strong> (CPython). Python 2 is obsolete.</li>
</ul>
EOT
      . vid_box('A clear overview of what Python is and how the interpreter runs your code.', 'how python works under the hood interpreter bytecode'),
    ],

    [
      'title' => 'Installing Python and your first program',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Goal</h2>
<p>By the end of this lesson you will have Python installed, confirmed it works from the
terminal, and run your very first program two different ways.</p>

<h2>Step 1 — Install Python 3</h2>
<ul>
<li><strong>Windows:</strong> download from <code>python.org/downloads</code>. On the first installer screen
<strong>tick "Add python.exe to PATH"</strong> before clicking Install. This one checkbox saves hours
of "python is not recognized" pain.</li>
<li><strong>macOS:</strong> the cleanest route is Homebrew: <code>brew install python</code>. (The system
Python that ships with macOS is meant for the OS — don't rely on it.)</li>
<li><strong>Linux:</strong> usually preinstalled. If not, e.g. on Debian/Ubuntu:
<code>sudo apt install python3 python3-pip</code>.</li>
</ul>

<h2>Step 2 — Confirm it works</h2>
<p>Open a terminal (Command Prompt / PowerShell / Terminal) and run:</p>
<pre><code>python3 --version
# or on Windows, sometimes:
python --version</code></pre>
<p>You should see something like <code>Python 3.12.4</code>. If you do, the interpreter is on your
<strong>PATH</strong> (the list of places your shell looks for programs) and you are ready.</p>

<div class="alert alert-info" role="alert">
<strong>What is the terminal?</strong> It's a text window where you type commands instead of clicking.
Developers live here. You don't need to memorise it — you'll pick up a handful of commands
(<code>cd</code> to change folder, <code>ls</code>/<code>dir</code> to list files) by using them.
</div>

<h2>Step 3 — Your first program in the REPL</h2>
<p>Start the interactive prompt:</p>
<pre><code>python3
&gt;&gt;&gt; print("Hello, world!")
Hello, world!
&gt;&gt;&gt; exit()</code></pre>
<p><code>print(...)</code> is a <strong>function</strong>: it takes whatever is inside the parentheses and
displays it. The text in quotes is a <strong>string</strong> (more on those soon).</p>

<h2>Step 4 — Your first program as a file</h2>
<p>Create a file named <code>hello.py</code> with this content:</p>
<pre><code>name = "Ada"
print("Hello,", name)
print("Welcome to Python.")</code></pre>
<p>Then, in the terminal, from the folder containing the file:</p>
<pre><code>python3 hello.py</code></pre>
<p>Output:</p>
<pre><code>Hello, Ada
Welcome to Python.</code></pre>
<p>Congratulations — that is a real program. It will produce the same result every time you run
it, which is exactly why we keep code in files.</p>

<h2>Pick an editor (optional but recommended)</h2>
<p>You can use any text editor, but a code editor gives you colour highlighting and error hints.
<strong>VS Code</strong> (free) with the official <em>Python</em> extension is the most common starting
point. Open your project folder in it and use its built-in terminal.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting "Add to PATH" on Windows</strong> → <code>python</code> not recognised. Re-run the
installer and tick it (or use the <code>py</code> launcher).</li>
<li><strong>Running the file from the wrong folder.</strong> <code>python3 hello.py</code> only works if your
terminal is in the folder that contains <code>hello.py</code>. Use <code>cd</code> to get there.</li>
<li><strong>Saving the file as <code>hello.py.txt</code></strong> in editors that hide extensions. Make sure it
ends in <code>.py</code>.</li>
<li><strong>Smart quotes.</strong> Copying code from a word processor can turn <code>"</code> into curly
<code>&ldquo;&rdquo;</code>, which Python rejects. Always type code in a code editor.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Install <strong>Python 3</strong>; on Windows tick <strong>Add to PATH</strong>.</li>
<li>Verify with <code>python3 --version</code>.</li>
<li><code>print()</code> displays output; run code in the REPL for quick tries and in <code>.py</code> files to keep it.</li>
<li>Run a file with <code>python3 filename.py</code> from its folder.</li>
</ul>
EOT
      . vid_box('A step-by-step install walkthrough for your operating system, then running a first script.', 'install python and vs code setup beginners 2024'),
    ],

    [
      'title' => 'Variables, objects, and dynamic typing',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Variables are names, not boxes</h2>
<p>A <strong>variable</strong> is a <em>name</em> that refers to a value. You create one with <code>=</code>
(the assignment operator):</p>
<pre><code>age = 25
name = "Ada"
price = 19.99</code></pre>
<p>Read <code>age = 25</code> as "let the name <code>age</code> refer to the value 25" — not "age equals 25"
in the maths sense. The value <code>25</code> lives in memory as an <strong>object</strong>, and
<code>age</code> is a label pointing at it.</p>
<pre><code>   name ───►  "Ada"        (a string object)
   age  ───►  25           (an int object)
   price ──►  19.99        (a float object)
</code></pre>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> A variable is a luggage tag, not a suitcase. The suitcase (the value/object)
sits on the conveyor belt of memory; the tag is just a name you stuck on it so you can find it.
Two tags can point at the same suitcase.
</div>

<h2>Everything is an object</h2>
<p>In Python, every value is an <strong>object</strong> with a <strong>type</strong> and some built-in
behaviour. You can ask any value its type:</p>
<pre><code>&gt;&gt;&gt; type(25)
&lt;class 'int'&gt;
&gt;&gt;&gt; type(19.99)
&lt;class 'float'&gt;
&gt;&gt;&gt; type("Ada")
&lt;class 'str'&gt;
&gt;&gt;&gt; type(True)
&lt;class 'bool'&gt;</code></pre>

<h2>Dynamic typing</h2>
<p>Python is <strong>dynamically typed</strong>: a variable does not have a fixed type — the
<em>object</em> it points to has a type, and you can repoint the variable at a different type later.</p>
<pre><code>x = 10        # x points at an int
x = "ten"     # now x points at a str — perfectly legal
x = [1, 2, 3] # now a list</code></pre>
<p>Contrast this with languages like Java where you must declare <code>int x</code> and can never put a
string in it. Python's flexibility is convenient but means <em>you</em> must keep track of what a
variable currently holds.</p>

<h2>Reassignment and the right-hand side rule</h2>
<p>Python always evaluates the <strong>right side first</strong>, then binds the name:</p>
<pre><code>count = 5
count = count + 1   # right side: 5 + 1 = 6, then count points at 6
print(count)        # 6</code></pre>
<p>This "compute, then rebind" pattern is so common there are shortcuts called
<strong>augmented assignment</strong>:</p>
<pre><code>count += 1   # same as count = count + 1
total *= 2   # total = total * 2
n -= 3       # n = n - 3</code></pre>

<h2>Naming rules and conventions</h2>
<ul>
<li>Must start with a letter or underscore; then letters, digits, underscores. No spaces.</li>
<li>Case-sensitive: <code>age</code> and <code>Age</code> are different.</li>
<li>Cannot be a reserved keyword (<code>if</code>, <code>for</code>, <code>class</code>, <code>True</code>, ...).</li>
<li><strong>Convention:</strong> use <code>snake_case</code> for variables — lowercase words joined by
underscores: <code>first_name</code>, <code>total_price</code>. Choose descriptive names; <code>n</code> is fine
for a loop counter, but <code>x1</code>, <code>data2</code>, <code>temp</code> hide meaning.</li>
</ul>

<h2>Multiple assignment</h2>
<pre><code>a, b = 1, 2          # a=1, b=2
a, b = b, a          # swap! now a=2, b=1
x = y = z = 0        # all three point at 0</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Using a variable before assigning it</strong> → <code>NameError</code>. You must create a name
before reading it.</li>
<li><strong>Thinking <code>=</code> compares.</strong> <code>=</code> assigns; <code>==</code> compares. <code>x = 5</code>
sets x; <code>x == 5</code> asks "is x five?".</li>
<li><strong>Overwriting built-in names</strong> like <code>list</code>, <code>str</code>, <code>sum</code>,
<code>type</code>. <code>list = [1,2]</code> works but then you can't use <code>list()</code> anymore. Avoid.</li>
<li><strong>Assuming the variable "is" the value.</strong> It's a reference. This matters a lot with
lists later, where two names can point at the <em>same</em> list.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>A variable is a <strong>name that refers</strong> to an object in memory.</li>
<li>Every value is an <strong>object</strong> with a <strong>type</strong>; check with <code>type(x)</code>.</li>
<li>Python is <strong>dynamically typed</strong> — names can be repointed to any type.</li>
<li>Use descriptive <code>snake_case</code> names; <code>=</code> assigns, <code>==</code> compares.</li>
</ul>
EOT
      . vid_box('How Python variables really work — names, objects, and references.', 'python variables and references corey schafer'),
    ],

    [
      'title' => 'Comments, the REPL workflow, and the Zen of Python',
      'minutes' => 12,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Comments: notes for humans</h2>
<p>A <strong>comment</strong> is text Python ignores. It exists to explain code to people (including
future you). Anything after a <code>#</code> on a line is a comment:</p>
<pre><code># Calculate the price including 7.5% VAT
price = 100
total = price * 1.075   # 1.075 = 1 + the tax rate</code></pre>
<p>Good comments explain <strong>why</strong>, not <strong>what</strong>. The code already says <em>what</em> it
does; a comment should add the reasoning the code can't show.</p>
<pre><code># BAD  — just repeats the code
x = x + 1   # add one to x

# GOOD — explains intent
x = x + 1   # advance to the next page; pages are 1-indexed</code></pre>

<h2>Docstrings vs comments</h2>
<p>A <strong>docstring</strong> is a string literal at the top of a file, function, or class. Unlike a
comment it is part of the program and can be read by tools and <code>help()</code>. You'll use these
heavily once we reach functions:</p>
<pre><code>def greet(name):
    """Return a friendly greeting for the given name."""
    return f"Hello, {name}!"</code></pre>

<h2>A productive REPL workflow</h2>
<p>The REPL is more than a calculator. Two tricks make it powerful:</p>
<ul>
<li><strong><code>help(thing)</code></strong> shows documentation. Try <code>help(str)</code> or
<code>help(print)</code>.</li>
<li><strong><code>dir(thing)</code></strong> lists everything you can do with a value. Try
<code>dir("hello")</code> to see all string methods.</li>
</ul>
<pre><code>&gt;&gt;&gt; name = "ada"
&gt;&gt;&gt; dir(name)        # shows .upper, .capitalize, .replace, ...
&gt;&gt;&gt; name.upper()
'ADA'
&gt;&gt;&gt; help(name.replace)  # read how .replace works</code></pre>
<p>This <em>explore-in-the-REPL</em> habit is how experienced developers learn unfamiliar code without
leaving the terminal.</p>

<h2>The Zen of Python</h2>
<p>Python has a built-in mini-philosophy. Run this once:</p>
<pre><code>&gt;&gt;&gt; import this</code></pre>
<p>It prints the <strong>Zen of Python</strong> — 19 guiding principles. The ones that matter most on day
one:</p>
<blockquote>
<p>Readability counts.<br>
Explicit is better than implicit.<br>
Simple is better than complex.<br>
There should be one—and preferably only one—obvious way to do it.</p>
</blockquote>
<p>This is not poetry for its own sake. It is the value system behind the whole language: when two
solutions work, prefer the one a human reads more easily. We will keep coming back to this when
we discuss clean code.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> Code is read far more often than it is written. Optimising code only for the
computer is like writing a book in shorthand only you understand — it works once, then becomes a
burden. Write for the next reader.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Over-commenting.</strong> A comment on every line is noise. Comment the surprising parts.</li>
<li><strong>Stale comments.</strong> A comment that contradicts the code is worse than none. Update
comments when you change code.</li>
<li><strong>Commenting out code and leaving it forever.</strong> Delete dead code — version control
(Git) remembers it for you.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Comments start with <code>#</code> and are ignored by Python; explain <em>why</em>, not <em>what</em>.</li>
<li><strong>Docstrings</strong> are readable documentation strings, surfaced by <code>help()</code>.</li>
<li>Use <code>help()</code> and <code>dir()</code> in the REPL to explore anything.</li>
<li><code>import this</code> reveals the Zen of Python: <strong>readability counts</strong>.</li>
</ul>
EOT
      . vid_box('The Zen of Python explained, and why readability is a core value.', 'zen of python explained beginners'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 1 Quiz: Python Fundamentals',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What does the Python interpreter do with your .py source file before running it?',
        'explain' => 'Python compiles source to bytecode, which the Python Virtual Machine then executes. SyntaxErrors are caught at the compile step, before any code runs.',
        'options' => [
          ['Compiles it to bytecode, then executes that bytecode', true],
          ['Sends the text directly to the CPU unchanged', false],
          ['Converts it permanently into a standalone .exe', false],
          ['Translates it into the C programming language', false],
        ],
      ],
      [
        'q' => 'Which statement about Python variables is correct?',
        'explain' => 'A variable is a name that refers to an object in memory. Python is dynamically typed, so a name can be reassigned to a value of any type.',
        'options' => [
          ['A variable is a name that refers to an object, and can point to any type', true],
          ['A variable has a fixed type that can never change', false],
          ['A variable is a fixed-size box that stores bytes directly', false],
          ['You must declare a variable\'s type before using it', false],
        ],
      ],
      [
        'q' => 'What is the difference between = and == ?',
        'explain' => '= assigns a value to a name; == compares two values and returns True or False.',
        'options' => [
          ['= assigns a value to a name; == compares two values', true],
          ['They are interchangeable', false],
          ['= compares; == assigns', false],
          ['== is only used for numbers', false],
        ],
      ],
      [
        'q' => 'Which command shows the documentation for the str type in the REPL?',
        'explain' => 'help(str) prints documentation. dir(str) lists available methods/attributes but is not the documentation itself.',
        'options' => [
          ['help(str)', true],
          ['docs(str)', false],
          ['print(str.help)', false],
          ['manual(str)', false],
        ],
      ],
      [
        'q' => 'According to the Zen of Python, when two solutions both work you should prefer the one that is...',
        'explain' => '"Readability counts." Python\'s philosophy favours the clearer, more readable solution.',
        'options' => [
          ['More readable', true],
          ['Shorter, even if cryptic', false],
          ['Cleverer / more advanced', false],
          ['Faster to type', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 2 — CORE SYNTAX & DATA TYPES
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Core Syntax & Data Types',
  'description' => 'The building blocks of every Python program: numbers, strings, booleans, None, operators, and how to convert between types and talk to the user with input() and print().',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Numbers: integers, floats, and arithmetic',
      'minutes' => 15,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Two number types you will use daily</h2>
<ul>
<li><strong><code>int</code></strong> — whole numbers, positive or negative, with <em>unlimited</em> size:
<code>0</code>, <code>42</code>, <code>-7</code>, <code>1000000</code>.</li>
<li><strong><code>float</code></strong> — numbers with a decimal point: <code>3.14</code>, <code>-0.5</code>,
<code>2.0</code>. "Float" means floating-point.</li>
</ul>
<pre><code>&gt;&gt;&gt; type(42)
&lt;class 'int'&gt;
&gt;&gt;&gt; type(3.14)
&lt;class 'float'&gt;</code></pre>

<h2>Arithmetic operators</h2>
<table>
<thead><tr><th>Operator</th><th>Meaning</th><th>Example</th><th>Result</th></tr></thead>
<tbody>
<tr><td><code>+</code></td><td>add</td><td><code>7 + 2</code></td><td><code>9</code></td></tr>
<tr><td><code>-</code></td><td>subtract</td><td><code>7 - 2</code></td><td><code>5</code></td></tr>
<tr><td><code>*</code></td><td>multiply</td><td><code>7 * 2</code></td><td><code>14</code></td></tr>
<tr><td><code>/</code></td><td>divide (always float)</td><td><code>7 / 2</code></td><td><code>3.5</code></td></tr>
<tr><td><code>//</code></td><td>floor divide (drop remainder)</td><td><code>7 // 2</code></td><td><code>3</code></td></tr>
<tr><td><code>%</code></td><td>modulo (the remainder)</td><td><code>7 % 2</code></td><td><code>1</code></td></tr>
<tr><td><code>**</code></td><td>power</td><td><code>7 ** 2</code></td><td><code>49</code></td></tr>
</tbody>
</table>

<div class="alert alert-info" role="alert">
<strong>Two divisions, on purpose.</strong> <code>/</code> always gives a float (<code>4 / 2</code> is
<code>2.0</code>, not <code>2</code>). Use <code>//</code> when you want a whole-number result, e.g.
"how many full boxes of 12?": <code>29 // 12</code> is <code>2</code>.
</div>

<h2>The modulo operator <code>%</code> is more useful than it looks</h2>
<p><code>%</code> gives the <strong>remainder</strong> of a division. It answers "what's left over?" — and
that powers a surprising number of real tasks:</p>
<pre><code>n % 2 == 0     # True when n is even (no remainder when divided by 2)
n % 2 == 1     # True when n is odd
14 % 12        # 2  → 14:00 is 2 o'clock on a 12-hour clock
i % len(items) # wrap an index around a list (cycling)</code></pre>

<h2>Operator precedence (order of operations)</h2>
<p>Python follows maths rules: <code>**</code> first, then <code>* / // %</code>, then <code>+ -</code>.
Use parentheses to be explicit and readable:</p>
<pre><code>&gt;&gt;&gt; 2 + 3 * 4
14
&gt;&gt;&gt; (2 + 3) * 4
20</code></pre>
<p>When in doubt, add parentheses. They cost nothing and remove ambiguity for the next reader.</p>

<h2>Floats are approximate — know this now</h2>
<p>Computers store floats in binary, and some decimals can't be represented exactly:</p>
<pre><code>&gt;&gt;&gt; 0.1 + 0.2
0.30000000000000004</code></pre>
<p>This is not a Python bug — it's how floating-point works in every language. The takeaways:</p>
<ul>
<li>Don't test floats with <code>==</code>. Instead check they are <em>close enough</em>, or use the
<code>decimal</code> module for money.</li>
<li>Round for display with <code>round(value, 2)</code>: <code>round(0.1 + 0.2, 2)</code> → <code>0.3</code>.</li>
</ul>

<h2>Handy number tools</h2>
<pre><code>abs(-5)        # 5   (absolute value)
round(3.14159, 2)  # 3.14
max(3, 9, 1)   # 9
min(3, 9, 1)   # 1
pow(2, 10)     # 1024  (same as 2 ** 10)
1_000_000      # underscores allowed for readability → 1000000</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Expecting <code>/</code> to give a whole number.</strong> It returns a float. Use <code>//</code>
for integer division.</li>
<li><strong>Comparing floats with <code>==</code>.</strong> <code>0.1 + 0.2 == 0.3</code> is <code>False</code>.</li>
<li><strong>Dividing by zero</strong> → <code>ZeroDivisionError</code>. Guard against a zero denominator.</li>
<li><strong>Mixing up <code>//</code> and <code>%</code>.</strong> <code>//</code> is the quotient, <code>%</code> is
the remainder. Together: <code>17 // 5 == 3</code> and <code>17 % 5 == 2</code> (3 fives plus 2).</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>int</code> = whole numbers (unlimited size); <code>float</code> = decimals (approximate).</li>
<li><code>/</code> always returns a float; <code>//</code> floor-divides; <code>%</code> gives the remainder; <code>**</code> is power.</li>
<li>Use parentheses for clarity; <code>**</code> binds tighter than <code>*</code>, which binds tighter than <code>+</code>.</li>
<li>Never compare floats with <code>==</code>; round for display, use <code>decimal</code> for money.</li>
</ul>
EOT
      . vid_box('Python numbers and arithmetic operators, including // and % explained with examples.', 'python numbers int float operators tutorial'),
    ],

    [
      'title' => 'Strings and f-strings',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What is a string?</h2>
<p>A <strong>string</strong> (<code>str</code>) is text — a sequence of characters. Write one with single or
double quotes (pick one style and be consistent):</p>
<pre><code>greeting = "Hello"
name = 'Ada'</code></pre>
<p>For text spanning multiple lines, use triple quotes:</p>
<pre><code>poem = """Roses are red,
Violets are blue."""</code></pre>

<h2>Strings are sequences: indexing and slicing</h2>
<p>Each character has a position (<strong>index</strong>), starting at <code>0</code>. Negative indices
count from the end:</p>
<pre><code> P  y  t  h  o  n
 0  1  2  3  4  5     (from the front)
-6 -5 -4 -3 -2 -1     (from the back)
</code></pre>
<pre><code>word = "Python"
word[0]      # 'P'
word[-1]     # 'n'  (last character)
word[0:3]    # 'Pyt'  (slice: start 0 up to, NOT including, 3)
word[:3]     # 'Pyt'  (start omitted = from beginning)
word[3:]     # 'hon'  (end omitted = to the end)
word[::-1]   # 'nohtyP'  (step -1 = reversed)</code></pre>
<p>A <strong>slice</strong> <code>[start:stop:step]</code> grabs a sub-range. Remember: <em>stop is
exclusive</em>. <code>word[0:3]</code> gives indices 0, 1, 2.</p>

<h2>Strings are immutable</h2>
<p>You cannot change a character in place. <code>word[0] = "J"</code> raises a <code>TypeError</code>.
Instead you build a <em>new</em> string:</p>
<pre><code>word = "Python"
word = "J" + word[1:]   # 'Jython'</code></pre>
<p>"Immutable" means string methods never modify the original — they <strong>return a new string</strong>.</p>

<h2>Essential string methods</h2>
<pre><code>s = "  Hello, World  "
s.strip()         # 'Hello, World'  (remove surrounding whitespace)
s.lower()         # '  hello, world  '
s.upper()         # '  HELLO, WORLD  '
s.replace("l", "L")   # '  HeLLo, WorLd  '
"a,b,c".split(",")    # ['a', 'b', 'c']  → string to list
"-".join(["a","b","c"])  # 'a-b-c'      → list to string
"Python".startswith("Py")  # True
"file.txt".endswith(".txt")  # True
"Python".find("th")   # 2  (index of first match, -1 if absent)
len("Python")     # 6  (number of characters)</code></pre>
<p>Methods <strong>chain</strong> left to right because each returns a string:</p>
<pre><code>"  Ada Lovelace  ".strip().upper()   # 'ADA LOVELACE'</code></pre>

<h2>f-strings: the modern way to build text</h2>
<p>An <strong>f-string</strong> (formatted string literal) lets you drop variables and expressions
directly into text. Put an <code>f</code> before the quote and wrap expressions in <code>{ }</code>:</p>
<pre><code>name = "Ada"
age = 36
print(f"{name} is {age} years old.")
# Ada is 36 years old.

# Any expression works inside the braces:
print(f"Next year she'll be {age + 1}.")
price = 19.5
print(f"Total: ${price * 1.075:.2f}")   # Total: $20.96</code></pre>
<p>The <code>:.2f</code> after the expression is a <strong>format spec</strong> — here "show 2 decimal
places". Other handy ones:</p>
<pre><code>f"{1234567:,}"     # '1,234,567'   (thousands separators)
f"{0.25:.0%}"      # '25%'         (percentage)
f"{42:5}"          # '   42'       (right-align in width 5)
f"{'hi':&gt;10}"      # '        hi'  (right-align text)
f"{name=}"         # "name='Ada'"  (debug: shows name and value)</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why f-strings win.</strong> Older styles (<code>"%s" % name</code> and <code>"{}".format(name)</code>)
still exist, but f-strings put the value right where it appears in the text, so they are the
easiest to read. Prefer them.
</div>

<h2>Escape sequences</h2>
<pre><code>"line one\nline two"   # \n = newline
"col1\tcol2"           # \t = tab
"She said \"hi\""      # \" = a literal quote inside double quotes
r"C:\Users\new"        # r"" = raw string: backslashes stay literal</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting the <code>f</code>.</strong> <code>"{name}"</code> prints the literal braces;
<code>f"{name}"</code> inserts the value.</li>
<li><strong>Expecting a method to mutate the string.</strong> <code>s.upper()</code> returns a new string;
you must capture it: <code>s = s.upper()</code>.</li>
<li><strong>Off-by-one in slices.</strong> <code>stop</code> is exclusive: <code>"hello"[0:2]</code> is
<code>'he'</code>, not <code>'hel'</code>.</li>
<li><strong>Adding a string and a number.</strong> <code>"Age: " + 25</code> is a <code>TypeError</code>.
Convert: <code>"Age: " + str(25)</code>, or just use an f-string.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Strings are <strong>immutable sequences</strong> of characters; index with <code>[i]</code>, slice with
<code>[start:stop:step]</code> (stop excluded).</li>
<li>Methods like <code>.strip()</code>, <code>.lower()</code>, <code>.split()</code>, <code>.join()</code>,
<code>.replace()</code> return <em>new</em> strings and can be chained.</li>
<li>Use <strong>f-strings</strong> (<code>f"{value}"</code>) to build text; format with specs like
<code>:.2f</code> and <code>:,</code>.</li>
</ul>
EOT
      . vid_box('A deep dive into Python strings, slicing, methods, and f-string formatting.', 'python f-strings and string methods corey schafer'),
    ],

    [
      'title' => 'Booleans, None, and comparison/logical operators',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Booleans: True and False</h2>
<p>A <strong>boolean</strong> (<code>bool</code>) has exactly two values: <code>True</code> and
<code>False</code> (capitalised). They are the answers to yes/no questions and drive all
decision-making in your programs.</p>

<h2>Comparison operators produce booleans</h2>
<pre><code>5 &gt; 3     # True
5 &lt; 3     # False
5 == 5    # True   (equal to)
5 != 3    # True   (not equal to)
5 &gt;= 5    # True   (greater than or equal)
3 &lt;= 2    # False  (less than or equal)
"a" &lt; "b"  # True   (strings compare alphabetically)</code></pre>
<p>Remember: <code>==</code> compares (asks a question), <code>=</code> assigns (gives a name a value).</p>

<h2>Logical operators: and, or, not</h2>
<p>Combine boolean expressions with plain English words:</p>
<table>
<thead><tr><th>Operator</th><th>True when...</th><th>Example</th></tr></thead>
<tbody>
<tr><td><code>and</code></td><td>both sides are True</td><td><code>age &gt;= 18 and has_id</code></td></tr>
<tr><td><code>or</code></td><td>at least one side is True</td><td><code>is_admin or is_owner</code></td></tr>
<tr><td><code>not</code></td><td>flips True/False</td><td><code>not logged_in</code></td></tr>
</tbody>
</table>
<pre><code>age = 20
has_ticket = True
age &gt;= 18 and has_ticket    # True
age &lt; 13 or age &gt; 65        # False
not has_ticket             # False</code></pre>

<h2>Truthiness: values that act like True or False</h2>
<p>Python lets you test any value as if it were a boolean. These are <strong>falsy</strong> (act like
<code>False</code>):</p>
<pre><code>0        0.0      ""       (empty string)
[]       {}       ()       (empty containers)
None     False</code></pre>
<p>Almost everything else is <strong>truthy</strong>. This enables clean checks:</p>
<pre><code>name = ""
if name:               # truthy test — runs only if name is non-empty
    print("Hi", name)
else:
    print("No name given")

items = []
if not items:          # "if the list is empty"
    print("Cart is empty")</code></pre>

<div class="alert alert-info" role="alert">
<strong>Idiom.</strong> Prefer <code>if items:</code> over <code>if len(items) &gt; 0:</code> and
<code>if not name:</code> over <code>if name == "":</code>. It's shorter and reads naturally — and it's the
Pythonic way.
</div>

<h2>None: the "nothing here" value</h2>
<p><code>None</code> is a special object that means "no value / not set yet". It is its own type
(<code>NoneType</code>) and is <em>not</em> the same as <code>0</code>, <code>False</code>, or
<code>""</code> — though all four are falsy.</p>
<pre><code>winner = None          # no winner decided yet
if winner is None:
    print("Still playing")</code></pre>
<p><strong>Always test for None with <code>is</code> / <code>is not</code></strong>, not <code>==</code>.
<code>is</code> checks identity ("the exact same object"), which is correct and faster for
<code>None</code>.</p>

<h2>Short-circuit evaluation</h2>
<p><code>and</code>/<code>or</code> stop as soon as the answer is known:</p>
<pre><code># If user is None, Python never evaluates user.name → no crash
if user is not None and user.name == "Ada":
    ...</code></pre>
<p>This lets you guard a risky check behind a safety check on the left.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Writing <code>True</code>/<code>False</code> lowercase</strong> (<code>true</code>) → <code>NameError</code>.
They are capitalised in Python.</li>
<li><strong>Comparing to <code>None</code> with <code>==</code>.</strong> Use <code>is None</code> /
<code>is not None</code>.</li>
<li><strong>Chaining wrong.</strong> Python actually allows <code>18 &lt;= age &lt; 65</code> (math-style
chaining!) — use it; it's clearer than <code>age &gt;= 18 and age &lt; 65</code>.</li>
<li><strong>Confusing <code>and</code>/<code>or</code> with <code>&amp;</code>/<code>|</code>.</strong> The words are
for booleans; the symbols are bitwise operators — different thing.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Booleans are <code>True</code>/<code>False</code>; comparisons (<code>== != &lt; &gt; &lt;= &gt;=</code>) produce them.</li>
<li>Combine with <code>and</code>, <code>or</code>, <code>not</code>; they short-circuit.</li>
<li>Empty/zero/<code>None</code> values are <strong>falsy</strong>; use <code>if items:</code> idioms.</li>
<li><code>None</code> means "no value"; test it with <code>is None</code> / <code>is not None</code>.</li>
</ul>
EOT
      . vid_box('Booleans, comparisons, truthiness, and the None value in Python.', 'python booleans truthiness none explained'),
    ],

    [
      'title' => 'Type conversion, input() and print()',
      'minutes' => 15,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Converting between types</h2>
<p>Python won't silently mix types, so you convert explicitly with the type's name as a function
(this is called <strong>casting</strong>):</p>
<pre><code>int("42")      # 42      (str → int)
float("3.14")  # 3.14    (str → float)
str(42)        # '42'    (int → str)
int(3.9)       # 3       (float → int, truncates toward zero — does NOT round)
bool(0)        # False
bool("hi")     # True
list("abc")    # ['a', 'b', 'c']</code></pre>
<p>Conversions that don't make sense raise <code>ValueError</code>:</p>
<pre><code>int("hello")   # ValueError: invalid literal for int()
int("3.5")     # ValueError too — go via float: int(float("3.5"))</code></pre>

<h2>print(): showing output</h2>
<p><code>print()</code> displays values. It can take several arguments and has useful options:</p>
<pre><code>print("Hello", "Ada")          # Hello Ada   (space between by default)
print("a", "b", "c", sep="-")  # a-b-c       (custom separator)
print("no newline", end="")    # stays on the same line
print("x =", 5)                # x = 5        (mixes text and numbers fine)</code></pre>
<p><code>print</code> automatically converts each argument to a string, which is why
<code>print("x =", 5)</code> works even though 5 is an int.</p>

<h2>input(): reading from the user</h2>
<p><code>input(prompt)</code> shows the prompt, waits for the user to type a line and press Enter, and
returns what they typed. <strong>It always returns a string</strong> — even if they type digits:</p>
<pre><code>name = input("What's your name? ")
print(f"Hello, {name}!")</code></pre>
<p>Because input is always text, you must convert when you want a number:</p>
<pre><code>age_text = input("Your age: ")   # e.g. "25"  (a string!)
age = int(age_text)              # 25  (now an int)
print(f"Next year you'll be {age + 1}.")

# Common compact form:
age = int(input("Your age: "))</code></pre>

<div class="alert alert-info" role="alert">
<strong>This is the #1 beginner bug.</strong> <code>input("Age: ") + 1</code> crashes with a
<code>TypeError</code> because you're adding a number to a string. And <code>"5" + "3"</code> is
<code>"53"</code> (text joined), not <code>8</code>. Convert with <code>int()</code> first.
</div>

<h2>A complete tiny program</h2>
<pre><code># tip_calculator.py
bill = float(input("Bill amount: "))
percent = float(input("Tip percent: "))

tip = bill * percent / 100
total = bill + tip

print(f"Tip:   ${tip:.2f}")
print(f"Total: ${total:.2f}")</code></pre>
<p>Run it:</p>
<pre><code>$ python3 tip_calculator.py
Bill amount: 50
Tip percent: 15
Tip:   $7.50
Total: $57.50</code></pre>
<p>Notice every piece you've learned working together: <code>input</code> (strings) →
<code>float()</code> (conversion) → arithmetic → f-string formatting → <code>print</code>.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting <code>input()</code> returns a string.</strong> Convert before doing maths.</li>
<li><strong>Assuming <code>int()</code> rounds.</strong> <code>int(3.9)</code> is <code>3</code>; use
<code>round(3.9)</code> for <code>4</code>.</li>
<li><strong>Crashing on bad input.</strong> If the user types "abc" when you expect a number,
<code>int()</code> raises <code>ValueError</code>. We'll handle that gracefully in the error-handling
module.</li>
<li><strong>Concatenating instead of adding.</strong> <code>"5" + "3" == "53"</code>. Convert both to
<code>int</code> first.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Convert types explicitly: <code>int()</code>, <code>float()</code>, <code>str()</code>, <code>bool()</code>;
bad conversions raise <code>ValueError</code>.</li>
<li><code>print()</code> auto-converts arguments and supports <code>sep=</code> and <code>end=</code>.</li>
<li><code>input()</code> <strong>always returns a string</strong> — wrap with <code>int()</code>/<code>float()</code>
for numbers.</li>
<li><code>int(float_value)</code> truncates; use <code>round()</code> to round.</li>
</ul>
EOT
      . vid_box('Reading user input, converting types, and formatting output in Python.', 'python input function and type conversion tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 2 Quiz: Core Syntax & Data Types',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What is the result of 7 / 2 in Python 3?',
        'explain' => 'The / operator always returns a float. 7 / 2 is 3.5. Use // for floor (integer) division, which gives 3.',
        'options' => [
          ['3.5', true],
          ['3', false],
          ['4', false],
          ['3.0', false],
        ],
      ],
      [
        'q' => 'What does 17 % 5 evaluate to?',
        'explain' => '% is modulo, the remainder. 5 goes into 17 three times (15) with 2 left over, so 17 % 5 is 2.',
        'options' => [
          ['2', true],
          ['3', false],
          ['3.4', false],
          ['12', false],
        ],
      ],
      [
        'q' => 'Given word = "Python", what is word[1:4]?',
        'explain' => 'Slicing [1:4] takes indices 1, 2, 3 (stop is exclusive): y, t, h → "yth".',
        'options' => [
          ['"yth"', true],
          ['"ytho"', false],
          ['"Pyt"', false],
          ['"yt"', false],
        ],
      ],
      [
        'q' => 'What type does input() always return?',
        'explain' => 'input() always returns a str, even when the user types digits. Convert with int() or float() for numeric work.',
        'options' => [
          ['str (a string)', true],
          ['int', false],
          ['It guesses the type automatically', false],
          ['float', false],
        ],
      ],
      [
        'q' => 'Which is the correct, Pythonic way to check that a variable x holds no value (is None)?',
        'explain' => 'Use identity comparison: x is None. Comparing with == works for None but is not the recommended idiom.',
        'options' => [
          ['if x is None:', true],
          ['if x = None:', false],
          ['if x == none:', false],
          ['if not x exists:', false],
        ],
      ],
      [
        'q' => 'What does the f-string f"{19.5:.2f}" produce?',
        'explain' => 'The :.2f format spec shows the number with exactly two decimal places: "19.50".',
        'options' => [
          ['"19.50"', true],
          ['"19.5"', false],
          ['"{19.5:.2f}"', false],
          ['"20"', false],
        ],
      ],
    ],
  ],
],

]; // end $MODULES

/*
 * ──────────────────────────────────────────────────────────────────────────
 * SEEDING LOGIC (idempotent — safe to re-run; only touches this one course)
 * ──────────────────────────────────────────────────────────────────────────
 */
echo '<h2>Seeding course: Python: From Zero to Developer</h2>';

$pdo->beginTransaction();
try {
    // --- Idempotency: remove any existing copy of this course (cascade children) ---
    $find = $pdo->prepare('SELECT id FROM courses WHERE title = ?');
    $find->execute([$COURSE_TITLE]);
    foreach ($find->fetchAll(PDO::FETCH_COLUMN) as $oldId) {
        $mods = $pdo->prepare('SELECT id FROM modules WHERE course_id = ?');
        $mods->execute([$oldId]);
        foreach ($mods->fetchAll(PDO::FETCH_COLUMN) as $mid) {
            // lessons → their exercises/progress cascade via FK; remove lessons + quiz
            $lids = $pdo->prepare('SELECT id FROM lessons WHERE module_id = ?');
            $lids->execute([$mid]);
            foreach ($lids->fetchAll(PDO::FETCH_COLUMN) as $lid) {
                $pdo->prepare('DELETE FROM coding_exercises WHERE lesson_id = ?')->execute([$lid]);
            }
            $pdo->prepare('DELETE FROM lessons WHERE module_id = ?')->execute([$mid]);

            // quiz → questions → options
            $qz = $pdo->prepare('SELECT id FROM quizzes WHERE module_id = ?');
            $qz->execute([$mid]);
            foreach ($qz->fetchAll(PDO::FETCH_COLUMN) as $qzid) {
                $qq = $pdo->prepare('SELECT id FROM quiz_questions WHERE quiz_id = ?');
                $qq->execute([$qzid]);
                foreach ($qq->fetchAll(PDO::FETCH_COLUMN) as $qqid) {
                    $pdo->prepare('DELETE FROM quiz_options WHERE question_id = ?')->execute([$qqid]);
                }
                $pdo->prepare('DELETE FROM quiz_questions WHERE quiz_id = ?')->execute([$qzid]);
                $pdo->prepare('DELETE FROM quizzes WHERE id = ?')->execute([$qzid]);
            }
        }
        $pdo->prepare('DELETE FROM modules WHERE course_id = ?')->execute([$oldId]);
        $pdo->prepare('DELETE FROM courses WHERE id = ?')->execute([$oldId]);
        echo '<p>&#9851; Removed existing course id ' . (int)$oldId . ' for a clean re-seed.</p>';
    }

    // --- Course ---
    $maxOrder = (int)$pdo->query('SELECT COALESCE(MAX(order_index),0) FROM courses')->fetchColumn();
    $ins = $pdo->prepare(
        "INSERT INTO courses (title, description, status, order_index, difficulty, estimated_hours)
         VALUES (?, ?, 'published', ?, 'beginner', 40)"
    );
    $ins->execute([$COURSE_TITLE, $COURSE_DESC, $maxOrder + 1]);
    $courseId = (int)$pdo->lastInsertId();
    echo '<p>&#9989; Course created (id ' . $courseId . ').</p>';

    $modOrder = 0; $lessonCount = 0; $quizCount = 0; $questionCount = 0;
    foreach ($MODULES as $module) {
        $modOrder++;
        $mStmt = $pdo->prepare('INSERT INTO modules (course_id, title, description, video_url, order_index) VALUES (?, ?, ?, ?, ?)');
        $mStmt->execute([$courseId, $module['title'], $module['description'] ?? '', $module['video_url'] ?? '', $modOrder]);
        $moduleId = (int)$pdo->lastInsertId();
        echo '<p>&#128218; Module ' . $modOrder . ': ' . htmlspecialchars($module['title']) . '</p>';

        $lOrder = 0;
        foreach ($module['lessons'] as $lesson) {
            $lOrder++;
            $lStmt = $pdo->prepare('INSERT INTO lessons (module_id, title, content, video_url, estimated_minutes, order_index) VALUES (?, ?, ?, ?, ?, ?)');
            $lStmt->execute([$moduleId, $lesson['title'], $lesson['content'], $lesson['video_url'] ?? '', $lesson['minutes'] ?? 10, $lOrder]);
            $lessonCount++;
            echo '<p style="margin-left:20px">&#8226; ' . htmlspecialchars($lesson['title']) . '</p>';
        }

        // --- Module quiz (optional) ---
        if (!empty($module['quiz'])) {
            $quiz = $module['quiz'];
            $qStmt = $pdo->prepare('INSERT INTO quizzes (module_id, title, pass_mark) VALUES (?, ?, ?)');
            $qStmt->execute([$moduleId, $quiz['title'], $quiz['pass_mark'] ?? 70]);
            $quizId = (int)$pdo->lastInsertId();
            $quizCount++;
            $qOrder = 0;
            foreach ($quiz['questions'] as $question) {
                $qOrder++;
                $qqStmt = $pdo->prepare('INSERT INTO quiz_questions (quiz_id, question_text, explanation, order_index) VALUES (?, ?, ?, ?)');
                $qqStmt->execute([$quizId, $question['q'], $question['explain'] ?? '', $qOrder]);
                $questionId = (int)$pdo->lastInsertId();
                $questionCount++;
                foreach ($question['options'] as $opt) {
                    $oStmt = $pdo->prepare('INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?, ?, ?)');
                    $oStmt->execute([$questionId, $opt[0], !empty($opt[1]) ? 1 : 0]);
                }
            }
            echo '<p style="margin-left:20px;color:#0a58ca">&#10068; Quiz: ' . htmlspecialchars($quiz['title']) . ' (' . count($quiz['questions']) . ' questions)</p>';
        }
    }

    $pdo->commit();
    echo '<h3 style="color:green">Done. Seeded 1 course, ' . $modOrder . ' modules, '
        . $lessonCount . ' lessons, ' . $quizCount . ' quizzes (' . $questionCount . ' questions).</h3>';
    echo '<p><strong>Now DELETE this file from the server.</strong></p>';
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo '<h3 style="color:red">Failed: ' . htmlspecialchars($e->getMessage()) . '</h3><p>No changes were saved.</p>';
}
