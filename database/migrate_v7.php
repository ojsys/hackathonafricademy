<?php
/**
 * Migration v7 — Seed the "Python: From Zero to Developer" course.
 * Run ONCE via browser, then DELETE this file.
 *
 *   Access: /database/migrate_v7.php?key=hackathon2026python
 *
 * COMPLETE comprehensive Python course: 12 modules, 46 lessons, 12 quizzes
 * (68 MCQs). Modules: 1 Fundamentals/Setup, 2 Core Syntax & Data Types,
 * 3 Control Flow, 4 Data Structures, 5 Functions, 6 OOP (a deep lesson on each
 * of the four pillars + dunders/dataclasses), 7 Clean Code & SOLID (each
 * principle), 8 Error Handling, 9 File Handling (pathlib/JSON/CSV), 10 Modules/
 * venv/pip/packaging, 11 Testing (unittest/pytest/TDD/debugging), 12 Capstone
 * CLI project. Each lesson is rich HTML (diagrams, analogy, common mistakes,
 * key takeaways) ending in an end-of-module quiz that gates the next module.
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
  <span class="text-muted small">Suggested search: &ldquo;$search&rdquo;<span class="admin-only"> — then paste the YouTube <em>embed</em> URL into this lesson via Admin &rarr; Lessons.</span></span></div>
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
      'video_url' => 'https://www.youtube.com/embed/BkHdmAhapws',
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
      'video_url' => 'https://www.youtube.com/embed/D2cwvpJSBX4',
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
      'video_url' => 'https://www.youtube.com/embed/cQT33yu9pY8',
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
      'video_url' => 'https://www.youtube.com/embed/uBHOb55-fBo',
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

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 3 — CONTROL FLOW: MAKING DECISIONS & REPEATING WORK
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Control Flow: Decisions & Loops',
  'description' => 'How programs choose between paths and repeat work: if/elif/else, for loops over sequences, while loops, and the loop-control tools break, continue, and the loop else clause. Indentation is how Python sees structure.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Making decisions: if, elif, else',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Programs need to choose</h2>
<p>So far our code ran top to bottom, every line, every time. Real programs branch:
"if the user is an admin, show the dashboard; otherwise show the login page." That is the job
of the <code>if</code> statement.</p>

<h2>The basic shape</h2>
<pre><code>age = 20
if age &gt;= 18:
    print("You are an adult")
    print("You can vote")</code></pre>
<p>Read it literally: <em>if</em> the condition (<code>age &gt;= 18</code>) is truthy, run the indented
block. The condition is followed by a colon <code>:</code>, and the body is <strong>indented</strong>.</p>

<div class="alert alert-info" role="alert">
<strong>Indentation IS the syntax.</strong> Many languages use <code>{ }</code> to group lines; Python
uses indentation. Everything indented the same amount under the <code>if:</code> belongs to it.
The standard is <strong>4 spaces</strong> per level. Be consistent — mixing tabs and spaces causes
errors.
</div>

<h2>else: the other path</h2>
<pre><code>age = 15
if age &gt;= 18:
    print("Adult")
else:
    print("Minor")</code></pre>
<p>Exactly one of the two blocks runs. <code>else</code> needs no condition — it catches "everything
the <code>if</code> didn't".</p>

<h2>elif: more than two paths</h2>
<p><code>elif</code> ("else if") lets you test several conditions in order. Python checks them
top-to-bottom and runs the <strong>first</strong> that is true, then skips the rest:</p>
<pre><code>score = 73
if score &gt;= 90:
    grade = "A"
elif score &gt;= 80:
    grade = "B"
elif score &gt;= 70:
    grade = "C"
else:
    grade = "F"
print(grade)   # C</code></pre>
<pre><code>score = 73
   │
   ▼
score &gt;= 90 ? ──No──► score &gt;= 80 ? ──No──► score &gt;= 70 ? ──Yes──► grade = "C"  (stop)
   │Yes                  │Yes                  │No
   ▼                     ▼                     ▼
 "A"                    "B"                  else → "F"
</code></pre>
<p>Order matters: because the first true branch wins, put the <em>narrowest</em> conditions first.
If you wrote <code>score &gt;= 70</code> at the top, a 95 would wrongly get a "C".</p>

<h2>Nesting and combining</h2>
<p>You can put an <code>if</code> inside another, but it's often clearer to combine conditions with
<code>and</code>/<code>or</code>:</p>
<pre><code># Nested (harder to read)
if logged_in:
    if is_admin:
        show_admin()

# Combined (clearer)
if logged_in and is_admin:
    show_admin()</code></pre>

<h2>The conditional (ternary) expression</h2>
<p>For a simple either/or value, Python has a one-line form:</p>
<pre><code>label = "adult" if age &gt;= 18 else "minor"</code></pre>
<p>Read it as "label is 'adult' if age &gt;= 18, else 'minor'". Use it for short, clear choices —
not for cramming complex logic onto one line.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Using <code>=</code> instead of <code>==</code></strong> in a condition. <code>if x = 5:</code> is a
syntax error; you mean <code>if x == 5:</code>.</li>
<li><strong>Forgetting the colon</strong> after the condition → <code>SyntaxError</code>.</li>
<li><strong>Inconsistent indentation</strong> → <code>IndentationError</code>. Pick 4 spaces and stick to it.</li>
<li><strong>Wrong branch order.</strong> Broad conditions before narrow ones make later branches
unreachable.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>if</code>/<code>elif</code>/<code>else</code> choose a path; the first true branch wins, then the
rest are skipped.</li>
<li>Conditions end with <code>:</code> and the body is <strong>indented 4 spaces</strong> — indentation is
the structure.</li>
<li>Combine conditions with <code>and</code>/<code>or</code> instead of deep nesting.</li>
<li>Use the <code>A if cond else B</code> expression for simple either/or values.</li>
</ul>
EOT
      . vid_box('Conditional logic in Python — if, elif, else, and clean branching.', 'python if elif else statements tutorial'),
    ],

    [
      'title' => 'Looping with for and range',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Do something for each item</h2>
<p>A <code>for</code> loop walks through a sequence and runs its body once per item. This is the most
common loop in Python:</p>
<pre><code>for fruit in ["apple", "banana", "cherry"]:
    print(fruit)
# apple
# banana
# cherry</code></pre>
<p>Each pass, the loop variable (<code>fruit</code>) is bound to the next item. You name it; pick
something meaningful.</p>
<pre><code>["apple", "banana", "cherry"]
    │        │         │
    ▼        ▼         ▼
 fruit=   fruit=    fruit=     ← one pass of the body each time
 apple    banana    cherry
</code></pre>

<h2>Looping a fixed number of times: range()</h2>
<p><code>range(n)</code> produces the numbers <code>0, 1, ..., n-1</code> — perfect for "do this N times":</p>
<pre><code>for i in range(5):
    print(i)        # 0 1 2 3 4 (each on its own line)

range(2, 6)         # 2 3 4 5      (start, stop) — stop excluded
range(0, 10, 2)     # 0 2 4 6 8    (start, stop, step)
range(5, 0, -1)     # 5 4 3 2 1    (count down)</code></pre>
<p>Like slicing, the <strong>stop value is excluded</strong>. <code>range(5)</code> gives five numbers
starting at 0.</p>

<h2>Looping over strings and other sequences</h2>
<pre><code>for letter in "cat":
    print(letter)   # c a t

total = 0
for price in [9.99, 4.50, 1.25]:
    total += price
print(f"Total: {total:.2f}")   # Total: 15.74</code></pre>

<h2>Two essential helpers: enumerate and zip</h2>
<p>When you need the <strong>index too</strong>, don't manage a counter by hand — use
<code>enumerate</code>:</p>
<pre><code>colors = ["red", "green", "blue"]
for index, color in enumerate(colors):
    print(index, color)
# 0 red
# 1 green
# 2 blue

# Start counting from 1:
for n, color in enumerate(colors, start=1):
    print(f"{n}. {color}")</code></pre>
<p>To walk through <strong>two lists together</strong>, use <code>zip</code>:</p>
<pre><code>names = ["Ada", "Alan", "Grace"]
ages  = [36, 41, 45]
for name, age in zip(names, ages):
    print(f"{name} is {age}")
# Ada is 36 ...</code></pre>

<div class="alert alert-info" role="alert">
<strong>Pythonic looping.</strong> Coming from other languages you may reach for
<code>for i in range(len(colors)): colors[i]</code>. Resist it. Loop over the items directly, and use
<code>enumerate</code> only when you genuinely need the index. It's shorter and less error-prone.
</div>

<h2>Nested loops</h2>
<p>A loop inside a loop — the inner loop runs fully for each pass of the outer:</p>
<pre><code>for row in range(1, 4):
    for col in range(1, 4):
        print(f"{row}x{col}={row*col}", end="  ")
    print()        # newline after each row</code></pre>

<h2>Accumulator pattern</h2>
<p>A huge fraction of loops follow this shape: start with an empty/zero accumulator, update it each
pass, use it after:</p>
<pre><code>numbers = [4, 8, 15, 16, 23]
biggest = numbers[0]
for n in numbers:
    if n &gt; biggest:
        biggest = n
print(biggest)   # 23   (this is what max() does internally)</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Off-by-one with range.</strong> <code>range(1, 5)</code> is 1,2,3,4 — not 5. Stop is excluded.</li>
<li><strong>Modifying a list while looping over it.</strong> Add/remove during iteration causes
skipped items or errors; build a new list instead.</li>
<li><strong>Reaching for indices needlessly.</strong> <code>for i in range(len(x))</code> is usually a smell;
loop the items.</li>
<li><strong>Forgetting the body must be indented</strong> under the <code>for ... :</code> line.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>for item in sequence:</code> runs the body once per item.</li>
<li><code>range(start, stop, step)</code> generates numbers; <strong>stop is excluded</strong>.</li>
<li>Use <code>enumerate</code> for index+item and <code>zip</code> to iterate sequences in parallel.</li>
<li>The accumulator pattern (init → update each pass → use after) powers sums, counts, max/min.</li>
</ul>
EOT
      . vid_box('for loops, range, enumerate and zip — looping the Pythonic way.', 'python for loops enumerate zip tutorial'),
    ],

    [
      'title' => 'while loops, break, continue, and loop-else',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Looping until a condition changes</h2>
<p>A <code>for</code> loop is for "each item in a known sequence". A <code>while</code> loop is for
"keep going <em>while</em> something is true" — when you don't know how many times up front:</p>
<pre><code>count = 3
while count &gt; 0:
    print(count)
    count -= 1      # MUST move toward the exit condition
print("Lift off!")</code></pre>
<p>Each pass, Python re-checks the condition at the top. When it becomes false, the loop ends.</p>

<div class="alert alert-warning" role="alert">
<strong>Infinite loops.</strong> If the condition never becomes false, the loop runs forever. The
classic cause is forgetting to update the variable (here, <code>count -= 1</code>). If your program
hangs, press <code>Ctrl+C</code> to stop it, then check that <em>something inside the loop changes the
condition</em>.
</div>

<h2>The input-validation loop</h2>
<p><code>while</code> shines when repeating until the user gives valid input:</p>
<pre><code>while True:                       # loop "forever"...
    answer = input("Type 'yes' to continue: ")
    if answer == "yes":
        break                    # ...until we explicitly break out
    print("Try again.")
print("Continuing!")</code></pre>

<h2>break: leave the loop early</h2>
<p><code>break</code> immediately exits the <em>innermost</em> loop, skipping the rest:</p>
<pre><code>for n in [4, 7, 2, 9, 1]:
    if n &gt; 8:
        print("Found a big number:", n)
        break        # stop searching once found
</code></pre>

<h2>continue: skip to the next pass</h2>
<p><code>continue</code> abandons the current pass and jumps to the next one:</p>
<pre><code>for n in range(1, 11):
    if n % 2 == 0:
        continue          # skip even numbers
    print(n)              # 1 3 5 7 9</code></pre>

<h2>The loop else clause (a Python special)</h2>
<p>Both <code>for</code> and <code>while</code> can have an <code>else</code> that runs <strong>only if the loop
finished normally</strong> — i.e. it was <em>not</em> ended by <code>break</code>. It's perfect for
search loops:</p>
<pre><code>target = 7
for n in [2, 4, 6, 8]:
    if n == target:
        print("Found it!")
        break
else:
    print("Not found.")     # runs because no break happened
</code></pre>
<p>Read <code>for...else</code> as "for each item... and if we never broke out, do this." It saves you a
separate "found" flag variable.</p>

<h2>for vs while — which to use</h2>
<table>
<thead><tr><th>Use</th><th>When</th></tr></thead>
<tbody>
<tr><td><code>for</code></td><td>You're iterating a known collection or a fixed count (<code>range</code>).</td></tr>
<tr><td><code>while</code></td><td>You repeat until some condition flips and don't know the count (input, retries, games).</td></tr>
</tbody>
</table>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Infinite loop</strong> from not changing the condition variable inside the body.</li>
<li><strong>Confusing <code>break</code> and <code>continue</code>.</strong> <code>break</code> ends the loop;
<code>continue</code> skips to the next pass.</li>
<li><strong>Expecting <code>break</code> to exit all loops.</strong> It only exits the innermost one.</li>
<li><strong>Misreading loop-<code>else</code></strong> as "if the loop didn't run". It actually means "the
loop completed without <code>break</code>".</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>while condition:</code> repeats until the condition is false; always change something inside.</li>
<li><code>break</code> exits the loop; <code>continue</code> jumps to the next pass.</li>
<li><code>while True: ... break</code> is the standard "loop until valid" pattern.</li>
<li>Loop <code>else</code> runs when the loop ends <em>without</em> a <code>break</code> — great for searches.</li>
</ul>
EOT
      . vid_box('while loops, break/continue, and the for-else clause explained.', 'python while loop break continue tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 3 Quiz: Control Flow',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'In an if/elif/elif/else chain, how many blocks run?',
        'explain' => 'Python runs the first branch whose condition is true and skips all the rest. At most one block runs (the else runs only if none matched).',
        'options' => [
          ['Exactly one — the first whose condition is true (or else)', true],
          ['Every branch whose condition is true', false],
          ['Always exactly two', false],
          ['All of them, top to bottom', false],
        ],
      ],
      [
        'q' => 'What does range(2, 8, 2) produce?',
        'explain' => 'Start at 2, step by 2, stop before 8: 2, 4, 6.',
        'options' => [
          ['2, 4, 6', true],
          ['2, 4, 6, 8', false],
          ['2, 3, 4, 5, 6, 7', false],
          ['0, 2, 4, 6', false],
        ],
      ],
      [
        'q' => 'Which tool gives you both the index and the item while looping a list?',
        'explain' => 'enumerate(seq) yields (index, item) pairs, so you do not need a manual counter.',
        'options' => [
          ['enumerate()', true],
          ['zip()', false],
          ['range()', false],
          ['index()', false],
        ],
      ],
      [
        'q' => 'What is the difference between break and continue?',
        'explain' => 'break exits the loop entirely; continue skips the rest of the current pass and moves to the next iteration.',
        'options' => [
          ['break exits the loop; continue skips to the next iteration', true],
          ['They do the same thing', false],
          ['break skips one iteration; continue exits the loop', false],
          ['continue restarts the loop from the beginning', false],
        ],
      ],
      [
        'q' => 'A loop\'s else block runs when...',
        'explain' => 'The loop else runs only if the loop completed normally — that is, it was not terminated by a break.',
        'options' => [
          ['The loop finished without hitting a break', true],
          ['The loop body never ran', false],
          ['A break was executed', false],
          ['An error occurred in the loop', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 4 — DATA STRUCTURES
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Data Structures: Lists, Tuples, Dicts & Sets',
  'description' => 'Python\'s built-in containers and when to use each. Lists for ordered, changeable sequences; tuples for fixed records; dictionaries for key→value lookup; sets for uniqueness and membership. Plus comprehensions for building them concisely.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Lists: ordered, changeable collections',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What is a list?</h2>
<p>A <strong>list</strong> is an ordered, changeable collection of items. Write one with square
brackets, items separated by commas:</p>
<pre><code>fruits = ["apple", "banana", "cherry"]
numbers = [4, 8, 15, 16, 23, 42]
mixed = ["Ada", 36, True]          # items can be different types
empty = []</code></pre>
<p>"Ordered" means items keep their position; "changeable" (mutable) means you can add, remove, and
replace items after creation.</p>

<h2>Accessing items: indexing and slicing</h2>
<p>Just like strings — index from 0, negatives from the end, slices grab ranges:</p>
<pre><code>fruits[0]      # 'apple'
fruits[-1]     # 'cherry'  (last)
fruits[0:2]    # ['apple', 'banana']  (slice → a new list)
len(fruits)    # 3</code></pre>

<h2>Lists are mutable — change them in place</h2>
<pre><code>fruits[1] = "blueberry"        # replace by index
fruits.append("date")          # add to the end
fruits.insert(0, "apricot")    # insert at a position
fruits.remove("cherry")        # remove first matching value
last = fruits.pop()            # remove & return last item
fruits.pop(0)                  # remove & return item at index 0
fruits.sort()                  # sort in place (alphabetical/numeric)
fruits.reverse()               # reverse in place
fruits.extend(["fig","grape"]) # add all items from another list</code></pre>

<div class="alert alert-info" role="alert">
<strong>append vs extend.</strong> <code>append(x)</code> adds <em>one</em> item (even if x is a list, it
goes in as a single nested element). <code>extend(iterable)</code> adds <em>each</em> item from the
iterable. <code>[1,2].append([3,4])</code> → <code>[1,2,[3,4]]</code>; <code>[1,2].extend([3,4])</code> →
<code>[1,2,3,4]</code>.
</div>

<h2>Searching and counting</h2>
<pre><code>"banana" in fruits     # True/False membership test
fruits.index("date")   # position of first match (error if absent)
fruits.count("fig")    # how many times it appears
sum(numbers)           # 108
max(numbers)           # 42
min(numbers)           # 4
sorted(numbers)        # NEW sorted list (original unchanged)</code></pre>

<h2>The reference gotcha (read this twice)</h2>
<p>A variable holds a <em>reference</em> to the list, not a copy. Assigning doesn't copy:</p>
<pre><code>a = [1, 2, 3]
b = a            # b points at the SAME list as a
b.append(4)
print(a)         # [1, 2, 3, 4]  ← a changed too!</code></pre>
<p>To get an independent copy, use a slice or <code>.copy()</code>:</p>
<pre><code>b = a[:]         # or a.copy()  → a real, separate copy
b.append(99)
print(a)         # unchanged</code></pre>
<pre><code>  a ─┐
     ├──►  [1, 2, 3]      b = a   → both labels, one list
  b ─┘

  a ───►  [1, 2, 3]       b = a[:] → two separate lists
  b ───►  [1, 2, 3]
</code></pre>

<h2>Iterating</h2>
<pre><code>for fruit in fruits:
    print(fruit)

for i, fruit in enumerate(fruits, start=1):
    print(f"{i}. {fruit}")</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Index out of range.</strong> Accessing <code>fruits[10]</code> on a 3-item list raises
<code>IndexError</code>. Check <code>len()</code> or use a loop.</li>
<li><strong>Aliasing.</strong> <code>b = a</code> does not copy. Mutating <code>b</code> mutates <code>a</code>.
Use <code>a.copy()</code> / <code>a[:]</code>.</li>
<li><strong>sort vs sorted.</strong> <code>list.sort()</code> changes in place and returns <code>None</code>;
<code>sorted(list)</code> returns a new list. <code>x = mylist.sort()</code> sets x to <code>None</code> — a
classic bug.</li>
<li><strong>Removing while iterating.</strong> Don't <code>remove()</code> items in the loop that's
iterating the list; build a filtered new list instead.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Lists are <strong>ordered, mutable</strong> sequences in <code>[ ]</code>; index/slice like strings.</li>
<li>Mutate with <code>append</code>, <code>insert</code>, <code>remove</code>, <code>pop</code>, <code>sort</code>,
<code>extend</code>.</li>
<li>Assignment shares a reference — copy with <code>a.copy()</code> or <code>a[:]</code>.</li>
<li><code>.sort()</code> mutates and returns <code>None</code>; <code>sorted()</code> returns a new list.</li>
</ul>
EOT
      . vid_box('Python lists in depth — methods, slicing, copying, and the reference gotcha.', 'python lists tutorial methods corey schafer'),
    ],

    [
      'title' => 'Tuples: fixed, immutable records',
      'minutes' => 12,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What is a tuple?</h2>
<p>A <strong>tuple</strong> is like a list but <strong>immutable</strong> — once created, you cannot change
its items. Write one with parentheses (or just commas):</p>
<pre><code>point = (3, 5)
rgb = (255, 128, 0)
single = (42,)        # one-item tuple NEEDS the trailing comma
also_tuple = 1, 2, 3  # parentheses are optional</code></pre>

<h2>Why immutability is a feature, not a limitation</h2>
<ul>
<li><strong>Safety:</strong> a tuple can't be changed by accident, so it's great for fixed data — a
coordinate, an RGB colour, a database row.</li>
<li><strong>Meaning:</strong> using a tuple signals "this is a fixed record of related values" vs a
list's "a collection that may grow/shrink".</li>
<li><strong>Hashable:</strong> tuples can be dictionary keys and set members; lists cannot.</li>
</ul>
<pre><code>point = (3, 5)
point[0]            # 3   (indexing works like a list)
point[0] = 9        # TypeError! tuples are immutable</code></pre>

<h2>Tuple unpacking — used everywhere</h2>
<p>You can assign a tuple's items to multiple variables at once:</p>
<pre><code>point = (3, 5)
x, y = point          # x=3, y=5

# This is why swapping works:
a, b = b, a

# And why looping pairs works:
for name, age in [("Ada", 36), ("Alan", 41)]:
    print(name, age)

# Functions return multiple values as a tuple:
def min_max(nums):
    return min(nums), max(nums)
low, high = min_max([4, 9, 1])   # low=1, high=9</code></pre>
<p>The <code>*</code> can capture "the rest":</p>
<pre><code>first, *rest = [1, 2, 3, 4]    # first=1, rest=[2, 3, 4]</code></pre>

<div class="alert alert-info" role="alert">
<strong>List or tuple?</strong> Ask: will this collection change? If items get added/removed → list.
If it's a fixed group of values that belong together (a record) → tuple. Returning several values
from a function is the most common tuple use.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting the comma in a one-item tuple.</strong> <code>(42)</code> is just the number 42;
<code>(42,)</code> is a tuple.</li>
<li><strong>Trying to modify a tuple.</strong> No <code>append</code>/item assignment. Build a new tuple
or use a list if you need to change it.</li>
<li><strong>Unpacking count mismatch.</strong> <code>x, y = (1, 2, 3)</code> raises <code>ValueError</code> —
the number of names must match (unless you use <code>*</code>).</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Tuples are <strong>immutable</strong> ordered sequences in <code>( )</code> — fixed records.</li>
<li>They're hashable, so usable as dict keys / set members (lists are not).</li>
<li><strong>Unpacking</strong> (<code>x, y = point</code>) and multi-value returns are their killer feature.</li>
<li>A one-item tuple needs a trailing comma: <code>(x,)</code>.</li>
</ul>
EOT
      . vid_box('Tuples vs lists, immutability, and tuple unpacking patterns.', 'python tuples and unpacking tutorial'),
    ],

    [
      'title' => 'Dictionaries: key → value lookup',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What is a dictionary?</h2>
<p>A <strong>dictionary</strong> (<code>dict</code>) stores <strong>key → value</strong> pairs. Instead of
looking things up by numeric position, you look them up by a meaningful key. Write one with curly
braces:</p>
<pre><code>person = {
    "name": "Ada",
    "age": 36,
    "city": "London",
}</code></pre>
<p>Think of it as a real dictionary: you look up a <em>word</em> (key) to get its <em>definition</em>
(value). Keys are unique; values can be anything.</p>

<h2>Accessing and changing values</h2>
<pre><code>person["name"]          # 'Ada'   (look up by key)
person["age"] = 37      # update a value
person["email"] = "a@x.io"   # add a new pair
del person["city"]      # remove a pair

person["missing"]       # KeyError! key doesn't exist
person.get("missing")   # None   (safe — no error)
person.get("missing", "N/A")   # 'N/A'  (default if absent)</code></pre>

<div class="alert alert-info" role="alert">
<strong>Use <code>.get()</code> to avoid crashes.</strong> <code>d[key]</code> raises <code>KeyError</code> if
the key is absent; <code>d.get(key, default)</code> returns a fallback instead. Reach for
<code>.get()</code> whenever a key might not be there.
</div>

<h2>Looping over a dictionary</h2>
<pre><code>for key in person:                 # iterates KEYS by default
    print(key, "→", person[key])

for key, value in person.items():  # the clean way: key + value
    print(f"{key}: {value}")

person.keys()      # dict_keys(['name', 'age', ...])
person.values()    # dict_values(['Ada', 37, ...])
"name" in person   # True  (membership tests KEYS)</code></pre>

<h2>Why dictionaries are everywhere</h2>
<ul>
<li><strong>Fast lookup.</strong> Finding a value by key is near-instant, even with millions of
entries (it doesn't scan item by item like a list).</li>
<li><strong>Records.</strong> A dict naturally models "a thing with named fields" — a user, a config,
a JSON object (JSON maps directly to dicts).</li>
<li><strong>Counting / grouping.</strong> The classic frequency counter:</li>
</ul>
<pre><code>text = "to be or not to be"
counts = {}
for word in text.split():
    counts[word] = counts.get(word, 0) + 1
print(counts)   # {'to': 2, 'be': 2, 'or': 1, 'not': 1}</code></pre>

<h2>Nesting</h2>
<p>Values can be lists or other dicts — this is how real data is shaped:</p>
<pre><code>users = {
    "u1": {"name": "Ada", "roles": ["admin", "dev"]},
    "u2": {"name": "Alan", "roles": ["dev"]},
}
users["u1"]["roles"][0]    # 'admin'</code></pre>

<h2>Key rules</h2>
<ul>
<li>Keys must be <strong>unique</strong> (assigning an existing key overwrites it).</li>
<li>Keys must be <strong>immutable/hashable</strong> — strings, numbers, tuples work; lists do not.</li>
<li>Since Python 3.7, dicts <strong>remember insertion order</strong>.</li>
</ul>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong><code>KeyError</code></strong> from <code>d[key]</code> on a missing key — use <code>.get()</code> or
check <code>if key in d</code>.</li>
<li><strong>Using a list as a key</strong> → <code>TypeError: unhashable type</code>. Use a tuple.</li>
<li><strong>Confusing <code>in</code>.</strong> <code>x in d</code> tests <em>keys</em>, not values. For values
use <code>x in d.values()</code>.</li>
<li><strong>Assuming order is sorted.</strong> Dicts keep <em>insertion</em> order, not sorted order.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Dicts map <strong>unique keys → values</strong>, written <code>{key: value}</code>; lookup is fast.</li>
<li>Read with <code>d[key]</code> (errors if absent) or <code>d.get(key, default)</code> (safe).</li>
<li>Loop with <code>for k, v in d.items()</code>; <code>in</code> tests keys.</li>
<li>Keys must be hashable (no lists); dicts preserve insertion order.</li>
</ul>
EOT
      . vid_box('Dictionaries explained — keys, values, .get(), .items(), and counting patterns.', 'python dictionaries tutorial corey schafer'),
    ],

    [
      'title' => 'Sets: uniqueness and membership',
      'minutes' => 12,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What is a set?</h2>
<p>A <strong>set</strong> is an unordered collection of <strong>unique</strong> items. It automatically
removes duplicates and is built for two things: fast membership tests and set maths
(union/intersection). Write one with curly braces:</p>
<pre><code>colors = {"red", "green", "blue"}
nums = {1, 2, 2, 3, 3, 3}     # {1, 2, 3} — duplicates dropped
empty = set()                  # NOT {} — that's an empty dict!</code></pre>

<h2>The killer use: deduplicate</h2>
<pre><code>names = ["Ada", "Alan", "Ada", "Grace", "Alan"]
unique = set(names)            # {'Ada', 'Alan', 'Grace'}
unique_list = list(set(names)) # back to a list, duplicates gone</code></pre>

<h2>Fast membership</h2>
<p>Checking "is x in here?" is near-instant for sets, even huge ones — much faster than scanning a
list:</p>
<pre><code>allowed = {"admin", "editor", "viewer"}
if role in allowed:           # very fast lookup
    grant_access()</code></pre>

<h2>Adding and removing</h2>
<pre><code>colors.add("yellow")
colors.discard("red")     # remove if present (no error if absent)
colors.remove("blue")     # remove, but KeyError if absent
colors.pop()              # remove & return an arbitrary item</code></pre>

<h2>Set maths</h2>
<pre><code>a = {1, 2, 3, 4}
b = {3, 4, 5, 6}
a | b      # union        {1,2,3,4,5,6}  (in either)
a &amp; b      # intersection {3, 4}         (in both)
a - b      # difference   {1, 2}         (in a, not b)
a ^ b      # symmetric    {1,2,5,6}      (in one, not both)</code></pre>
<pre><code>   a = {1,2,3,4}        b = {3,4,5,6}
        ┌───────────┬───────────┐
        │  1  2     │ 3  4 │  5  6 │
        │  a only   │ both │ b only│
        └───────────┴───────────┘
   a &amp; b = {3,4}   a - b = {1,2}   a | b = everything</code></pre>

<div class="alert alert-info" role="alert">
<strong>When to choose a set.</strong> Reach for a set when you need uniqueness, when you do lots of
"is it in here?" checks, or when you want union/intersection/difference. The trade-off: sets are
<em>unordered</em> and can't hold unhashable items (no lists inside).
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong><code>{}</code> is an empty dict, not a set.</strong> Use <code>set()</code> for an empty set.</li>
<li><strong>Expecting order.</strong> Sets have no indexing — <code>my_set[0]</code> is an error.</li>
<li><strong>Putting a list in a set</strong> → <code>TypeError: unhashable</code>. Use tuples for grouped
items.</li>
<li><strong><code>remove</code> on a missing item</strong> raises <code>KeyError</code>; use <code>discard</code>
if absence is OK.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Sets hold <strong>unique, unordered</strong> items; <code>set(list)</code> deduplicates instantly.</li>
<li>Membership (<code>x in s</code>) is very fast.</li>
<li>Combine with <code>|</code> (union), <code>&amp;</code> (intersection), <code>-</code> (difference).</li>
<li><code>set()</code> is the empty set; <code>{}</code> is an empty dict.</li>
</ul>
EOT
      . vid_box('Sets in Python — deduplication, membership speed, and set operations.', 'python sets tutorial union intersection'),
    ],

    [
      'title' => 'Comprehensions and choosing the right structure',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Comprehensions: build a collection in one line</h2>
<p>Very often you build a new list by looping and appending. A <strong>list comprehension</strong>
expresses that pattern in one readable line.</p>
<pre><code># The long way:
squares = []
for n in range(1, 6):
    squares.append(n * n)

# The comprehension:
squares = [n * n for n in range(1, 6)]   # [1, 4, 9, 16, 25]</code></pre>
<p>Read it left to right: <em>"give me <code>n*n</code> for each <code>n</code> in range(1,6)"</em>. The
shape is:</p>
<pre><code>[ expression  for item in iterable  if condition ]
     │              │                    │
   what to       loop over           optional filter
   collect
</code></pre>

<h2>Filtering with a condition</h2>
<pre><code>evens = [n for n in range(20) if n % 2 == 0]
names = ["Ada", "al", "Grace", "bo"]
long_names = [name.upper() for name in names if len(name) &gt; 2]
# ['ADA', 'GRACE']</code></pre>

<h2>Dict and set comprehensions</h2>
<pre><code># Dict comprehension: {key: value for ...}
squares_map = {n: n*n for n in range(1, 5)}   # {1:1, 2:4, 3:9, 4:16}

# Set comprehension: {expr for ...}  (auto-deduplicates)
first_letters = {word[0] for word in ["ada", "alan", "bo"]}  # {'a', 'b'}</code></pre>

<div class="alert alert-info" role="alert">
<strong>Keep them readable.</strong> Comprehensions are wonderful for simple transform/filter. If you
need nested loops plus multiple conditions, or the logic gets long, a plain <code>for</code> loop is
clearer. Readability counts — don't write a comprehension just to look clever.
</div>

<h2>Choosing the right data structure</h2>
<p>This is one of the most important skills in the whole course. Match the structure to the job:</p>
<table>
<thead><tr><th>Need</th><th>Use</th><th>Why</th></tr></thead>
<tbody>
<tr><td>Ordered items that change</td><td><strong>list</strong> <code>[ ]</code></td><td>append/remove, keep order, allow duplicates</td></tr>
<tr><td>A fixed record of values</td><td><strong>tuple</strong> <code>( )</code></td><td>immutable, hashable, signals "won't change"</td></tr>
<tr><td>Lookup by a name/key</td><td><strong>dict</strong> <code>{k: v}</code></td><td>fast key→value access; models records/JSON</td></tr>
<tr><td>Unique items / fast "is it in?"</td><td><strong>set</strong> <code>{ }</code></td><td>auto-dedupe, fast membership, set maths</td></tr>
</tbody>
</table>

<h3>A worked decision</h3>
<p>"I'm tracking which users have voted." → You only care about <em>presence</em> and <em>uniqueness</em>
(a user votes once). → <strong>set</strong> of user IDs: <code>voted.add(uid)</code>,
<code>if uid in voted</code>.</p>
<p>"I'm storing each user's profile by ID." → lookup by key → <strong>dict</strong>:
<code>users[uid] = {...}</code>.</p>
<p>"I'm keeping an ordered to-do list the user reorders." → ordered + changeable →
<strong>list</strong>.</p>

<h2>Mutable vs immutable — the big picture</h2>
<pre><code>Mutable   (can change in place):  list, dict, set
Immutable (cannot change):        int, float, str, bool, tuple, frozenset</code></pre>
<p>This distinction explains the copy/aliasing behaviour you saw with lists, why strings return new
values from methods, and why only immutable things can be dict keys or set members.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Overusing comprehensions.</strong> Cramming heavy logic in makes them unreadable — use a
loop.</li>
<li><strong>Picking a list for lookups.</strong> Searching a big list with <code>in</code> is slow; a set
or dict is far faster.</li>
<li><strong>Using a dict when you just need membership</strong> — a set is simpler.</li>
<li><strong>Side effects in a comprehension</strong> (calling <code>print</code> inside). Comprehensions are
for <em>building collections</em>, not for doing actions.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Comprehensions build lists/dicts/sets in one line:
<code>[expr for x in it if cond]</code>.</li>
<li>Keep them simple; fall back to loops when logic grows.</li>
<li>Choose: <strong>list</strong> (ordered, changeable), <strong>tuple</strong> (fixed record),
<strong>dict</strong> (key lookup), <strong>set</strong> (unique/membership).</li>
<li>Know what's <strong>mutable</strong> (list/dict/set) vs <strong>immutable</strong> (int/str/tuple/...).</li>
</ul>
EOT
      . vid_box('List/dict/set comprehensions and how to pick the right data structure.', 'python list comprehension tutorial mCoding'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 4 Quiz: Data Structures',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'After a = [1, 2, 3]; b = a; b.append(4), what is a?',
        'explain' => 'b = a copies the reference, not the list, so both names point to the same list. Appending via b also shows through a: [1, 2, 3, 4]. Use a.copy() or a[:] for an independent copy.',
        'options' => [
          ['[1, 2, 3, 4]', true],
          ['[1, 2, 3]', false],
          ['[4, 1, 2, 3]', false],
          ['It raises an error', false],
        ],
      ],
      [
        'q' => 'Which structure is best for storing unique tags and quickly checking membership?',
        'explain' => 'A set stores unique items and offers very fast membership tests (x in s), making it ideal here.',
        'options' => [
          ['set', true],
          ['list', false],
          ['tuple', false],
          ['str', false],
        ],
      ],
      [
        'q' => 'What does person.get("email", "N/A") return when "email" is not a key?',
        'explain' => '.get() returns the provided default ("N/A") instead of raising KeyError when the key is missing.',
        'options' => [
          ['"N/A"', true],
          ['None', false],
          ['It raises KeyError', false],
          ['An empty string', false],
        ],
      ],
      [
        'q' => 'What is [n * 2 for n in range(4)]?',
        'explain' => 'range(4) is 0,1,2,3; doubling each gives [0, 2, 4, 6].',
        'options' => [
          ['[0, 2, 4, 6]', true],
          ['[2, 4, 6, 8]', false],
          ['[0, 1, 2, 3]', false],
          ['[1, 2, 3, 4]', false],
        ],
      ],
      [
        'q' => 'Which of these is immutable?',
        'explain' => 'Tuples are immutable. Lists, dicts, and sets can all be changed in place.',
        'options' => [
          ['tuple', true],
          ['list', false],
          ['dict', false],
          ['set', false],
        ],
      ],
      [
        'q' => 'How do you create an empty set?',
        'explain' => 'set() creates an empty set. {} creates an empty dictionary, not a set.',
        'options' => [
          ['set()', true],
          ['{}', false],
          ['[]', false],
          ['empty_set()', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 5 — FUNCTIONS
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Functions: Reusable Building Blocks',
  'description' => 'Package logic into named, reusable functions: parameters and arguments, return values, scope, default/keyword/variadic arguments (*args, **kwargs), lambdas and higher-order functions, and documenting with docstrings and type hints.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Defining and calling functions',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Why functions?</h2>
<p>A <strong>function</strong> is a named, reusable block of code. Instead of repeating the same lines
everywhere, you write them once, give them a name, and <em>call</em> that name whenever you need
the behaviour. Functions are how programs stay organised and DRY ("Don't Repeat Yourself").</p>

<h2>Defining and calling</h2>
<pre><code>def greet():
    print("Hello!")
    print("Welcome.")

greet()      # call it — runs the body
greet()      # call again — reuse</code></pre>
<p><code>def</code> starts a definition, then the name, then <code>()</code>, a colon, and the indented
body. Defining a function does <strong>not</strong> run it — calling it (<code>greet()</code>) does.</p>

<h2>Parameters and arguments</h2>
<p><strong>Parameters</strong> are the named inputs in the definition; <strong>arguments</strong> are the
actual values you pass when calling:</p>
<pre><code>def greet(name):            # 'name' is a parameter
    print(f"Hello, {name}!")

greet("Ada")               # "Ada" is an argument
greet("Grace")</code></pre>
<pre><code>def add(a, b):             # two parameters
    print(a + b)

add(3, 4)                  # 7  (positional: a=3, b=4)</code></pre>

<h2>return: sending a value back</h2>
<p><code>print</code> shows something; <code>return</code> hands a value back to the caller so it can be
used. This is the difference between a function that <em>does</em> something visible and one that
<em>computes</em> a result:</p>
<pre><code>def add(a, b):
    return a + b

result = add(3, 4)     # result = 7
total = add(1, 2) + add(10, 20)   # use returns in expressions: 33</code></pre>

<div class="alert alert-info" role="alert">
<strong>print vs return — the #1 confusion.</strong> <code>print</code> writes to the screen and gives
back <code>None</code>. <code>return</code> produces a value your program can store and reuse. A function
that <code>print</code>s a sum can't be used in <code>x = add(2,3)</code> (x becomes <code>None</code>).
Compute with <code>return</code>; only <code>print</code> when display is the actual goal.
</div>

<h2>return ends the function immediately</h2>
<pre><code>def classify(n):
    if n &lt; 0:
        return "negative"      # exits here if n &lt; 0
    if n == 0:
        return "zero"
    return "positive"          # only reached if n &gt; 0</code></pre>
<p>A function with no <code>return</code> (or a bare <code>return</code>) gives back <code>None</code>.</p>

<h2>Returning multiple values</h2>
<p>Return several values as a tuple and unpack them:</p>
<pre><code>def stats(numbers):
    return min(numbers), max(numbers), sum(numbers) / len(numbers)

low, high, avg = stats([4, 8, 15, 16])
print(low, high, avg)   # 4 16 10.75</code></pre>

<h2>A good function does one thing</h2>
<p>Aim for small functions with a clear single purpose and a descriptive verb name
(<code>calculate_total</code>, <code>send_email</code>, <code>is_valid</code>). If you struggle to name it,
it's probably doing too much — split it. (We'll formalise this as the Single Responsibility
Principle later.)</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Defining but never calling.</strong> <code>def greet(): ...</code> alone prints nothing — you
must call <code>greet()</code>.</li>
<li><strong>Using a function's result when it only prints.</strong> If it has no <code>return</code>, it
gives <code>None</code>.</li>
<li><strong>Forgetting <code>return</code></strong> and wondering why the caller gets <code>None</code>.</li>
<li><strong>Wrong argument count.</strong> Calling <code>add(3)</code> when two parameters are required
raises <code>TypeError</code>.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Define with <code>def name(params):</code>; calling runs the body.</li>
<li><strong>Parameters</strong> are names in the definition; <strong>arguments</strong> are values passed in.</li>
<li><code>return</code> sends a value back (and exits); no return → <code>None</code>.</li>
<li>Keep functions small, single-purpose, and named with a clear verb.</li>
</ul>
EOT
      . vid_box('Defining functions, parameters vs arguments, and return vs print.', 'python functions tutorial return vs print'),
    ],

    [
      'title' => 'Arguments in depth: defaults, keywords, *args, **kwargs',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Positional vs keyword arguments</h2>
<p>By default, arguments match parameters by <strong>position</strong>. You can also pass them by
<strong>name</strong> (keyword), which is clearer and order-independent:</p>
<pre><code>def describe(name, age, city):
    print(f"{name}, {age}, from {city}")

describe("Ada", 36, "London")                 # positional
describe(name="Ada", city="London", age=36)   # keyword: order free, self-documenting</code></pre>

<h2>Default values</h2>
<p>Give a parameter a default so callers can omit it:</p>
<pre><code>def greet(name, greeting="Hello"):
    print(f"{greeting}, {name}!")

greet("Ada")                  # Hello, Ada!
greet("Ada", "Welcome")       # Welcome, Ada!
greet("Ada", greeting="Hi")   # Hi, Ada!</code></pre>
<p>Rule: parameters <strong>with</strong> defaults must come <strong>after</strong> those without.</p>

<div class="alert alert-warning" role="alert">
<strong>The mutable default trap.</strong> Never use a list/dict as a default value:
<code>def add(item, bag=[]):</code> is buggy — the <em>same</em> list is shared across all calls.
Use <code>None</code> and create a fresh one inside:
<pre><code>def add(item, bag=None):
    if bag is None:
        bag = []
    bag.append(item)
    return bag</code></pre>
</div>

<h2>*args: any number of positional arguments</h2>
<p>Prefix a parameter with <code>*</code> to collect extra positional arguments into a <strong>tuple</strong>:</p>
<pre><code>def total(*numbers):          # numbers becomes a tuple
    return sum(numbers)

total(1, 2)            # 3
total(1, 2, 3, 4, 5)   # 15
total()                # 0</code></pre>

<h2>**kwargs: any number of keyword arguments</h2>
<p>Prefix with <code>**</code> to collect extra keyword arguments into a <strong>dict</strong>:</p>
<pre><code>def make_user(**fields):      # fields becomes a dict
    return fields

make_user(name="Ada", age=36)
# {'name': 'Ada', 'age': 36}</code></pre>

<h2>Putting it together (the full order)</h2>
<pre><code>def f(required, default="x", *args, **kwargs):
    ...
#     │         │            │       └ extra keyword args → dict
#     │         │            └ extra positional args → tuple
#     │         └ optional with default
#     └ required positional</code></pre>

<h2>Unpacking arguments with * and **</h2>
<p>The same symbols <em>spread</em> a list/dict into arguments at the call site:</p>
<pre><code>nums = [1, 2, 3]
print(*nums)          # same as print(1, 2, 3)

opts = {"sep": "-", "end": "!\n"}
print("a", "b", **opts)   # a-b!</code></pre>

<div class="alert alert-info" role="alert">
<strong>Mental model.</strong> In a <em>definition</em>, <code>*</code>/<code>**</code> <em>collect</em> many
arguments into one parameter. At a <em>call</em>, <code>*</code>/<code>**</code> <em>spread</em> one
collection into many arguments. Same symbols, opposite directions.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Mutable default arguments</strong> (<code>=[]</code>, <code>={}</code>) — use <code>None</code>.</li>
<li><strong>Putting a defaulted parameter before a required one</strong> → <code>SyntaxError</code>.</li>
<li><strong>Mixing up collect vs spread.</strong> Definition collects; call spreads.</li>
<li><strong>Passing a keyword arg that doesn't exist</strong> (without <code>**kwargs</code>) →
<code>TypeError</code>.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Arguments bind by <strong>position</strong> or by <strong>keyword</strong>; keywords are clearer.</li>
<li><strong>Defaults</strong> make parameters optional; never default to a mutable (<code>[]</code>/<code>{}</code>).</li>
<li><code>*args</code> collects extra positionals (tuple); <code>**kwargs</code> collects extra keywords (dict).</li>
<li>At a call, <code>*</code>/<code>**</code> <strong>spread</strong> a list/dict into arguments.</li>
</ul>
EOT
      . vid_box('Default, keyword, *args and **kwargs arguments explained clearly.', 'python args and kwargs tutorial'),
    ],

    [
      'title' => 'Scope: where names live',
      'minutes' => 15,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Local vs global scope</h2>
<p><strong>Scope</strong> is the region where a name is visible. A variable created <em>inside</em> a
function is <strong>local</strong> — it exists only during that call and vanishes afterward. A variable
at the top level of a file is <strong>global</strong>.</p>
<pre><code>def f():
    x = 10        # local to f
    print(x)

f()               # 10
print(x)          # NameError — x doesn't exist out here</code></pre>

<h2>Functions can read globals, but not rebind them by default</h2>
<pre><code>count = 0          # global

def show():
    print(count)   # OK — reading the global works

def bump():
    count = count + 1   # ERROR: Python treats count as local here
</code></pre>
<p>Assigning to a name inside a function makes it local for the <em>whole</em> function, so reading it
before assignment fails. The clean fix is not <code>global</code> — it's to <strong>pass it in and return
it out</strong>:</p>
<pre><code>def bump(count):
    return count + 1

count = bump(count)   # explicit, testable, no hidden state</code></pre>

<h2>The LEGB lookup rule</h2>
<p>When you use a name, Python searches scopes in this order:</p>
<pre><code>L  Local      → inside the current function
E  Enclosing  → any outer function wrapping this one
G  Global     → the module/file level
B  Built-in   → names like print, len, range
</code></pre>
<p>The first match wins. This is why defining a variable called <code>list</code> hides the built-in
<code>list</code>.</p>

<div class="alert alert-info" role="alert">
<strong>Prefer arguments over globals.</strong> Functions that read/modify global state are hard to
test and reason about (their result depends on hidden variables). A good function takes everything
it needs as arguments and returns its result. This makes it <em>pure</em> and predictable — a theme
we return to in the testing module.
</div>

<h2>The mutable-argument subtlety</h2>
<p>You can't rebind a caller's variable from inside a function, but if you pass a <em>mutable</em>
object (list/dict), the function can change its <em>contents</em>:</p>
<pre><code>def add_item(bag):
    bag.append("x")     # mutates the SAME list the caller passed

items = []
add_item(items)
print(items)            # ['x']  — caller's list changed</code></pre>
<p>This follows directly from "variables are references". Be deliberate: mutating arguments can be
useful but can also surprise callers.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Expecting locals to leak out.</strong> Variables made inside a function don't exist
outside it.</li>
<li><strong>Reaching for <code>global</code>.</strong> It usually signals a design problem — pass and
return instead.</li>
<li><strong>Shadowing built-ins/globals</strong> with a local of the same name and getting confused.</li>
<li><strong>Accidentally mutating a passed-in list/dict</strong> and surprising the caller.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Names created in a function are <strong>local</strong>; top-level names are <strong>global</strong>.</li>
<li>Functions can <em>read</em> globals but rebinding needs care — prefer <strong>pass in, return out</strong>.</li>
<li>Name lookup follows <strong>LEGB</strong>: Local → Enclosing → Global → Built-in.</li>
<li>Passing a mutable object lets a function change its contents (references!).</li>
</ul>
EOT
      . vid_box('Variable scope and the LEGB rule in Python functions.', 'python variable scope legb tutorial'),
    ],

    [
      'title' => 'Lambdas, higher-order functions, docstrings & type hints',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Functions are values too</h2>
<p>In Python a function is an <strong>object</strong> you can store in a variable, pass to another
function, and return. Functions that take or return other functions are called
<strong>higher-order functions</strong>.</p>
<pre><code>def shout(text):
    return text.upper()

f = shout            # store the function (no parentheses!)
print(f("hi"))       # HI</code></pre>

<h2>lambda: a tiny anonymous function</h2>
<p>A <code>lambda</code> is a one-line function with no name, handy when you need a quick function to
pass somewhere:</p>
<pre><code>square = lambda n: n * n
square(5)            # 25

# Equivalent to:
def square(n):
    return n * n</code></pre>
<p>The body must be a single expression (its value is returned automatically). Lambdas are best used
<em>inline</em> as arguments — for anything bigger, write a normal <code>def</code>.</p>

<h2>Where lambdas shine: sorting by a key</h2>
<pre><code>people = [("Ada", 36), ("Alan", 41), ("Grace", 28)]

people.sort(key=lambda person: person[1])   # sort by age (index 1)
# [('Grace', 28), ('Ada', 36), ('Alan', 41)]

words = ["banana", "kiwi", "apple"]
sorted(words, key=lambda w: len(w))          # by length: ['kiwi', 'apple', 'banana']
sorted(words, key=str.lower, reverse=True)   # Z→A, case-insensitive</code></pre>

<h2>map and filter (and why comprehensions usually win)</h2>
<pre><code>nums = [1, 2, 3, 4, 5]
list(map(lambda n: n * 2, nums))        # [2, 4, 6, 8, 10]
list(filter(lambda n: n % 2 == 0, nums)) # [2, 4]

# Most Pythonistas prefer comprehensions for readability:
[n * 2 for n in nums]
[n for n in nums if n % 2 == 0]</code></pre>

<h2>Docstrings: document what a function does</h2>
<p>A <strong>docstring</strong> is a string as the first line of a function body. It describes the
function for humans and tools (<code>help()</code>, IDEs):</p>
<pre><code>def bmi(weight_kg, height_m):
    """Return Body Mass Index for the given weight (kg) and height (m).

    BMI = weight / height**2. Raises ValueError if height is not positive.
    """
    if height_m &lt;= 0:
        raise ValueError("height must be positive")
    return weight_kg / height_m ** 2

help(bmi)    # shows the docstring</code></pre>
<p>Good docstrings say <em>what</em> it does, what the parameters mean, and what it returns/raises.</p>

<h2>Type hints: document the types</h2>
<p><strong>Type hints</strong> annotate the expected types. Python does <em>not</em> enforce them at
runtime, but they make code self-documenting and let tools (mypy, your editor) catch bugs before
you run:</p>
<pre><code>def bmi(weight_kg: float, height_m: float) -&gt; float:
    return weight_kg / height_m ** 2

def greet(name: str, times: int = 1) -&gt; str:
    return f"Hello {name}! " * times

# Containers (Python 3.9+):
def total(prices: list[float]) -&gt; float:
    return sum(prices)

# "Maybe a value, maybe None":
def find(users: dict[str, int], key: str) -&gt; int | None:
    return users.get(key)</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why bother with hints?</strong> They're like labelled ports on a device: you instantly see
what plugs in and what comes out. On real projects they prevent whole classes of bugs and make
functions far easier to use correctly. Adopt them as a habit.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Calling instead of referencing.</strong> <code>key=len()</code> is wrong; pass the function
itself: <code>key=len</code>.</li>
<li><strong>Overusing lambdas.</strong> A multi-step lambda is unreadable — use <code>def</code>.</li>
<li><strong>Thinking hints are enforced.</strong> Python won't stop you passing the wrong type at
runtime; hints are for tooling and humans.</li>
<li><strong>Skipping docstrings on non-obvious functions.</strong> Future-you will want them.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Functions are objects — store, pass, and return them (higher-order functions).</li>
<li><code>lambda</code> is a one-expression anonymous function, best used inline (e.g. <code>sort key=</code>).</li>
<li>Comprehensions usually read better than <code>map</code>/<code>filter</code>.</li>
<li>Document with <strong>docstrings</strong> and <strong>type hints</strong> (<code>name: str -&gt; bool</code>) — both make code clearer and safer.</li>
</ul>
EOT
      . vid_box('Lambdas, sorting with key=, map/filter, docstrings and type hints.', 'python lambda functions and type hints tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 5 Quiz: Functions',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What is the difference between print and return in a function?',
        'explain' => 'print displays output to the screen and the function still returns None; return hands a value back to the caller so it can be stored and reused.',
        'options' => [
          ['print shows text on screen; return sends a value back to the caller', true],
          ['They are identical', false],
          ['return shows text; print sends a value back', false],
          ['print can only be used once per function', false],
        ],
      ],
      [
        'q' => 'Why should you avoid def f(items=[]) as a default argument?',
        'explain' => 'A mutable default is created once and shared across all calls, so it accumulates state between calls. Use None as the default and create a fresh list inside.',
        'options' => [
          ['The same list is shared across all calls, causing bugs; use None instead', true],
          ['Lists cannot be function arguments', false],
          ['It is slower than using a tuple', false],
          ['It raises a SyntaxError', false],
        ],
      ],
      [
        'q' => 'In def f(*args, **kwargs), what types are args and kwargs?',
        'explain' => '*args collects extra positional arguments into a tuple; **kwargs collects extra keyword arguments into a dict.',
        'options' => [
          ['args is a tuple, kwargs is a dict', true],
          ['args is a list, kwargs is a set', false],
          ['Both are lists', false],
          ['args is a dict, kwargs is a tuple', false],
        ],
      ],
      [
        'q' => 'What does the LEGB rule describe?',
        'explain' => 'LEGB is the order Python searches for a name: Local, then Enclosing, then Global, then Built-in.',
        'options' => [
          ['The order Python searches scopes: Local, Enclosing, Global, Built-in', true],
          ['The four kinds of function arguments', false],
          ['A way to sort lists', false],
          ['The steps to define a class', false],
        ],
      ],
      [
        'q' => 'How do you sort a list of (name, age) tuples by age?',
        'explain' => 'Pass a key function that returns the age (index 1): sort(key=lambda p: p[1]).',
        'options' => [
          ['people.sort(key=lambda p: p[1])', true],
          ['people.sort(age)', false],
          ['people.sort(key=p[1])', false],
          ['people.sortby("age")', false],
        ],
      ],
      [
        'q' => 'Are Python type hints enforced at runtime?',
        'explain' => 'No. Type hints are for humans and tools (editors, mypy). Python does not stop you from passing a different type at runtime.',
        'options' => [
          ['No — they help humans and tools but are not enforced when running', true],
          ['Yes — passing the wrong type always raises an error', false],
          ['Only for integers', false],
          ['Only inside classes', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 6 — OBJECT-ORIENTED PROGRAMMING (the four principles, in depth)
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Object-Oriented Programming',
  'description' => 'Model real-world things as objects. Classes, instances, __init__ and self, then a deep, separate lesson on each of the four pillars — Encapsulation, Abstraction, Inheritance, Polymorphism — followed by dunder methods and dataclasses. This module turns you from someone who writes scripts into someone who designs software.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Classes and objects: the basics',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>From data + functions to objects</h2>
<p>So far you've kept data in variables and behaviour in functions, separately. <strong>Object-oriented
programming (OOP)</strong> bundles related data and the functions that act on it into a single unit:
an <strong>object</strong>. This mirrors how we think about the world — a "car" has data (colour,
speed) <em>and</em> behaviours (accelerate, brake) together.</p>

<h2>Class vs object (instance)</h2>
<ul>
<li>A <strong>class</strong> is a blueprint — it defines what every object of that kind has and can do.</li>
<li>An <strong>object</strong> (or <strong>instance</strong>) is a concrete thing built from that blueprint.</li>
</ul>
<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> The class is the architect's blueprint for a house; each actual house built
from it is an object. One blueprint → many houses, each with its own address and paint colour but
the same structure.
</div>
<pre><code>   class Dog          ←  the blueprint (one)
   ┌──────────────┐
   │ name, breed   │
   │ bark()        │
   └──────────────┘
        │ build instances
   ┌────┴─────┬──────────┐
  rex        bella       max     ←  objects (many), each with its own data
</code></pre>

<h2>Defining a class</h2>
<pre><code>class Dog:
    def __init__(self, name, breed):
        self.name = name        # instance attribute
        self.breed = breed

    def bark(self):
        return f"{self.name} says Woof!"

# Create instances:
rex = Dog("Rex", "Labrador")
bella = Dog("Bella", "Poodle")

print(rex.name)      # Rex
print(bella.bark())  # Bella says Woof!</code></pre>

<h2>__init__ and self — demystified</h2>
<ul>
<li><strong><code>__init__</code></strong> is the <em>initialiser</em> ("constructor"). Python calls it
automatically when you create an instance, to set up its starting data. You don't call it
directly — <code>Dog("Rex", "Labrador")</code> runs it for you.</li>
<li><strong><code>self</code></strong> is the instance being worked on. Every method's first parameter is
<code>self</code>; Python passes it automatically. <code>self.name = name</code> means "store this
object's own name". When you call <code>rex.bark()</code>, <code>self</code> <em>is</em> <code>rex</code>.</li>
</ul>
<pre><code>rex = Dog("Rex", "Labrador")
#         └── Python calls Dog.__init__(rex, "Rex", "Labrador")
#             so inside, self = rex
rex.bark()
#   └── Python calls Dog.bark(rex), so self = rex</code></pre>

<h2>Instance attributes vs class attributes</h2>
<p><strong>Instance attributes</strong> (set with <code>self.x</code>) are unique per object.
<strong>Class attributes</strong> are defined in the class body and <em>shared</em> by all instances:</p>
<pre><code>class Dog:
    species = "Canis familiaris"   # class attribute — shared

    def __init__(self, name):
        self.name = name           # instance attribute — per dog

print(Dog.species)    # shared default
rex = Dog("Rex")
print(rex.species)    # 'Canis familiaris' (read from the class)
print(rex.name)       # 'Rex' (this dog only)</code></pre>

<h2>Methods are functions that belong to objects</h2>
<pre><code>class BankAccount:
    def __init__(self, owner, balance=0):
        self.owner = owner
        self.balance = balance

    def deposit(self, amount):
        self.balance += amount
        return self.balance

    def withdraw(self, amount):
        if amount &gt; self.balance:
            return "Insufficient funds"
        self.balance -= amount
        return self.balance

acct = BankAccount("Ada", 100)
acct.deposit(50)     # 150
acct.withdraw(30)    # 120
print(acct.balance)  # 120</code></pre>

<h2>__str__: a friendly text form</h2>
<p>By default printing an object shows something ugly like
<code>&lt;__main__.Dog object at 0x...&gt;</code>. Define <code>__str__</code> to control how it prints:</p>
<pre><code>class Dog:
    def __init__(self, name):
        self.name = name
    def __str__(self):
        return f"Dog named {self.name}"

print(Dog("Rex"))    # Dog named Rex</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting <code>self</code></strong> in a method definition → <code>TypeError</code> about
arguments. Every instance method needs <code>self</code> first.</li>
<li><strong>Forgetting <code>self.</code></strong> when storing/reading attributes:
<code>name = name</code> creates a throwaway local; you want <code>self.name = name</code>.</li>
<li><strong>Calling <code>__init__</code> yourself.</strong> Don't — creating the instance calls it.</li>
<li><strong>Mutable class attributes.</strong> A shared <code>tricks = []</code> class attribute is shared by
all dogs; per-dog data belongs in <code>__init__</code> as <code>self.tricks = []</code>.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>A <strong>class</strong> is a blueprint; an <strong>object/instance</strong> is built from it.</li>
<li><code>__init__</code> sets up a new instance; <code>self</code> refers to that instance.</li>
<li><strong>Instance attributes</strong> (<code>self.x</code>) are per-object; <strong>class attributes</strong> are
shared.</li>
<li><strong>Methods</strong> are functions defined in the class; define <code>__str__</code> for readable
printing.</li>
</ul>
EOT
      . vid_box('Classes, objects, __init__ and self explained from scratch.', 'python oop classes and objects corey schafer'),
    ],

    [
      'title' => 'The four pillars of OOP — overview',
      'minutes' => 12,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Why principles, not just syntax</h2>
<p>Knowing <em>how</em> to write a class is the easy part. The hard, valuable part is designing classes
that are easy to change, reuse, and reason about. Decades of practice distilled this into
<strong>four principles</strong> of object-oriented programming. The next four lessons take one each, in
depth. Here's the map so you see how they fit together.</p>

<h2>The four pillars at a glance</h2>
<table>
<thead><tr><th>Principle</th><th>One-line idea</th><th>Everyday analogy</th></tr></thead>
<tbody>
<tr><td><strong>Encapsulation</strong></td><td>Bundle data with the methods that use it, and hide the internals behind a safe interface.</td><td>A medicine capsule: the contents are wrapped; you take the pill, you don't repackage the powder.</td></tr>
<tr><td><strong>Abstraction</strong></td><td>Expose <em>what</em> something does, hide <em>how</em> it does it.</td><td>Driving a car: you use the steering wheel and pedals without knowing the engine internals.</td></tr>
<tr><td><strong>Inheritance</strong></td><td>Build new classes on top of existing ones, reusing and extending their behaviour.</td><td>A child inherits traits from a parent, then adds their own.</td></tr>
<tr><td><strong>Polymorphism</strong></td><td>Different objects respond to the <em>same</em> call in their own way.</td><td>Press "play" on a phone, a guitar, or a sports team — same word, different behaviour.</td></tr>
</tbody>
</table>

<div class="alert alert-info" role="alert">
<strong>How they relate.</strong> Encapsulation and abstraction are about <em>drawing boundaries</em>
(what's hidden, what's exposed). Inheritance and polymorphism are about <em>relationships between
classes</em> (sharing and substituting behaviour). Together they let you build large systems out of
small, swappable, well-protected pieces.
</div>

<h2>A tiny example touching all four</h2>
<pre><code>from abc import ABC, abstractmethod

class Shape(ABC):                       # ABSTRACTION: a contract
    @abstractmethod
    def area(self): ...

class Circle(Shape):                    # INHERITANCE: Circle is a Shape
    def __init__(self, radius):
        self._radius = radius           # ENCAPSULATION: internal data
    def area(self):
        return 3.14159 * self._radius ** 2

class Square(Shape):                    # INHERITANCE
    def __init__(self, side):
        self._side = side
    def area(self):
        return self._side ** 2

shapes = [Circle(2), Square(3)]
for s in shapes:
    print(s.area())                     # POLYMORPHISM: same call, different result</code></pre>
<p>Don't worry about every detail yet — each principle gets its own lesson next. Notice the shape of
it: a shared <em>contract</em> (Shape), specific <em>implementations</em> (Circle, Square), hidden
<em>internals</em> (<code>_radius</code>), and one loop that treats them uniformly.</p>

<h2>✅ Key takeaways</h2>
<ul>
<li>OOP design rests on four pillars: <strong>Encapsulation, Abstraction, Inheritance, Polymorphism</strong>.</li>
<li>Encapsulation/abstraction draw <em>boundaries</em>; inheritance/polymorphism define <em>relationships</em>.</li>
<li>Used together they make systems out of small, swappable, protected parts.</li>
<li>The next four lessons cover each pillar in depth, with Python code.</li>
</ul>
EOT
      . vid_box('A clear overview of the four OOP principles before going deep on each.', 'four pillars of object oriented programming explained'),
    ],

    [
      'title' => 'Pillar 1 — Encapsulation',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What encapsulation really means</h2>
<p><strong>Encapsulation</strong> has two parts that go together:</p>
<ol>
<li><strong>Bundling</strong> data and the methods that operate on it into one unit (the class).</li>
<li><strong>Information hiding</strong>: keeping the internal state private and exposing a controlled,
safe <em>interface</em> for the outside world.</li>
</ol>
<p>The goal is <strong>protecting an object's invariants</strong> — the rules that must always stay true
(e.g. "a bank balance is never negative"). If any code can poke any field directly, those rules are
impossible to guarantee.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> A capsule of medicine wraps the powder so you take a safe, measured dose.
You interact through the capsule (the interface), not by ripping it open and eating raw powder
(the internal state). Encapsulation is that capsule for your data.
</div>

<h2>The problem without encapsulation</h2>
<pre><code>class Account:
    def __init__(self, balance):
        self.balance = balance

acct = Account(100)
acct.balance = -5000      # nothing stops this — the invariant is broken!</code></pre>
<p>Any code anywhere can set <code>balance</code> to nonsense. There's no single place that enforces
"balance can't go negative".</p>

<h2>Python's privacy conventions</h2>
<p>Python has no hard <code>private</code> keyword. Instead it uses naming conventions plus tools:</p>
<table>
<thead><tr><th>Form</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>name</code></td><td>Public — part of the interface, use freely.</td></tr>
<tr><td><code>_name</code></td><td>"Internal" by convention — please don't touch from outside.</td></tr>
<tr><td><code>__name</code></td><td>Name-mangled — Python rewrites it to <code>_Class__name</code> to avoid accidental access/clashes.</td></tr>
</tbody>
</table>

<h2>Enforcing rules through methods</h2>
<pre><code>class Account:
    def __init__(self, balance=0):
        self._balance = balance        # internal state

    def deposit(self, amount):
        if amount &lt;= 0:
            raise ValueError("deposit must be positive")
        self._balance += amount

    def withdraw(self, amount):
        if amount &gt; self._balance:
            raise ValueError("insufficient funds")
        self._balance -= amount

    def get_balance(self):
        return self._balance</code></pre>
<p>Now the only way to change the balance goes through methods that <em>enforce the rules</em>. The
invariant ("never negative, deposits positive") lives in one place.</p>

<h2>Properties: the Pythonic getter/setter</h2>
<p>Writing <code>get_balance()</code>/<code>set_balance()</code> everywhere is clunky. Python's
<code>@property</code> lets a method <em>look like</em> an attribute, so you get clean syntax
<em>and</em> validation:</p>
<pre><code>class Account:
    def __init__(self, balance=0):
        self._balance = balance

    @property
    def balance(self):                 # the "getter"
        return self._balance

    @balance.setter
    def balance(self, value):          # the "setter" — validates
        if value &lt; 0:
            raise ValueError("balance cannot be negative")
        self._balance = value

acct = Account(100)
print(acct.balance)     # 100  — looks like a plain attribute (calls the getter)
acct.balance = 250      # calls the setter (allowed)
acct.balance = -5       # ValueError! rule enforced</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why this is powerful.</strong> You can start with a plain public attribute and later add
validation via <code>@property</code> <em>without changing any calling code</em> — they still write
<code>acct.balance = 250</code>. That's encapsulation paying off: the interface stayed stable while
the internals gained protection.
</div>

<h2>Benefits of encapsulation</h2>
<ul>
<li><strong>Safety:</strong> invalid states become impossible (rules enforced in one place).</li>
<li><strong>Maintainability:</strong> you can change internals freely as long as the interface holds.</li>
<li><strong>Clarity:</strong> users of your class see a small, intentional surface, not every field.</li>
</ul>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Exposing everything as public</strong> and then being unable to enforce rules later.</li>
<li><strong>Treating <code>_x</code> as truly private.</strong> It's a convention — Python won't stop a
determined caller. The convention is a strong social contract, though.</li>
<li><strong>Writing Java-style <code>get_x()</code>/<code>set_x()</code> for everything.</strong> In Python,
start with a public attribute and reach for <code>@property</code> only when you need validation or
computation.</li>
<li><strong>Putting validation in <code>__init__</code> only.</strong> If a setter also exists, validate
there too, or route <code>__init__</code> through the property.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Encapsulation = <strong>bundle data with behaviour</strong> + <strong>hide internals behind a safe
interface</strong>.</li>
<li>It exists to <strong>protect invariants</strong> — keep objects always valid.</li>
<li>Use <code>_name</code> for "internal", and <code>@property</code> for attribute-style getters/setters
with validation.</li>
<li>A stable public interface lets you change internals without breaking callers.</li>
</ul>
EOT
      . vid_box('Encapsulation, private attributes, and @property getters/setters in Python.', 'python property decorator getters setters corey schafer'),
    ],

    [
      'title' => 'Pillar 2 — Abstraction',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Abstraction: show what, hide how</h2>
<p><strong>Abstraction</strong> means exposing a simple, essential interface while hiding complex
implementation details. You describe <em>what</em> an object does, not <em>how</em> it does it. Where
encapsulation hides <em>data</em>, abstraction hides <em>complexity</em>.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> When you drive, you use a steering wheel, pedals, and a gear lever. You don't
think about fuel injection, valve timing, or the differential. The car <em>abstracts away</em> the
engine: a small, understandable interface over enormous complexity. Good classes do the same.
</div>

<h2>Abstraction you already use</h2>
<p><code>len(x)</code>, <code>sorted(x)</code>, <code>"hi".upper()</code> — you call them without knowing
their internal algorithms. That's abstraction. Your job as a developer is to provide the <em>same
gift</em> to whoever uses your classes: a clean interface over messy details.</p>

<h2>Abstract Base Classes (ABCs): defining a contract</h2>
<p>Sometimes you want to say "every payment method <em>must</em> provide a <code>pay()</code> method" —
without specifying how. Python's <code>abc</code> module lets you define an <strong>abstract base
class</strong>: a contract that subclasses are required to fulfil.</p>
<pre><code>from abc import ABC, abstractmethod

class PaymentMethod(ABC):
    @abstractmethod
    def pay(self, amount):
        """Charge the given amount. Subclasses MUST implement this."""

    @abstractmethod
    def refund(self, amount):
        ...

class CreditCard(PaymentMethod):
    def pay(self, amount):
        return f"Charged ${amount} to card"
    def refund(self, amount):
        return f"Refunded ${amount} to card"

class PayPal(PaymentMethod):
    def pay(self, amount):
        return f"Paid ${amount} via PayPal"
    def refund(self, amount):
        return f"Refunded ${amount} via PayPal"</code></pre>

<h2>The contract is enforced</h2>
<pre><code>PaymentMethod()        # TypeError: can't instantiate an abstract class

class Bitcoin(PaymentMethod):
    def pay(self, amount):
        return "sent BTC"
    # forgot refund()!

Bitcoin()              # TypeError: missing refund() — contract not met</code></pre>
<p>This is abstraction enforced by the language: you literally cannot create an object that doesn't
honour the agreed interface. That guarantee lets the rest of your code rely on
<code>method.pay(...)</code> existing, whatever the concrete type.</p>

<h2>Why abstraction matters</h2>
<ul>
<li><strong>Manageable complexity:</strong> users learn a small interface, not the whole implementation.</li>
<li><strong>Interchangeable parts:</strong> code that depends on the abstraction
(<code>PaymentMethod</code>) works with any implementation — and new ones can be added later
without touching it. (This sets up polymorphism and the SOLID "depend on abstractions" rule.)</li>
<li><strong>Freedom to change internals:</strong> as long as the interface holds, you can rewrite the
"how" entirely.</li>
</ul>

<h2>Abstraction vs encapsulation</h2>
<table>
<thead><tr><th></th><th>Encapsulation</th><th>Abstraction</th></tr></thead>
<tbody>
<tr><td>Hides</td><td>internal <em>data/state</em></td><td>internal <em>complexity/implementation</em></td></tr>
<tr><td>Question</td><td>"who can touch this field?"</td><td>"what's the simplest interface?"</td></tr>
<tr><td>Tool</td><td><code>_x</code>, <code>@property</code></td><td>methods, ABCs, clear interfaces</td></tr>
</tbody>
</table>
<p>They're complementary: encapsulation is often <em>how</em> you achieve abstraction.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Leaky abstractions:</strong> an interface that forces users to understand internals anyway
(e.g. methods that must be called in a precise hidden order).</li>
<li><strong>Over-abstracting:</strong> creating ABCs and layers for a tiny program with one
implementation. Add abstraction when you actually have (or clearly will have) multiple variants.</li>
<li><strong>Forgetting <code>@abstractmethod</code></strong> — then subclasses aren't forced to implement
anything and the contract is fake.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Abstraction exposes <strong>what</strong> an object does and hides <strong>how</strong>.</li>
<li>It controls <em>complexity</em>; encapsulation controls <em>data access</em> — they work together.</li>
<li><strong>Abstract base classes</strong> (<code>ABC</code> + <code>@abstractmethod</code>) define enforced
contracts subclasses must fulfil.</li>
<li>Depending on an abstraction lets you swap implementations freely.</li>
</ul>
EOT
      . vid_box('Abstraction and abstract base classes (ABC) in Python.', 'python abstract base class abc abstractmethod tutorial'),
    ],

    [
      'title' => 'Pillar 3 — Inheritance',
      'minutes' => 22,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Reuse by building on existing classes</h2>
<p><strong>Inheritance</strong> lets a new class (the <strong>child</strong>/subclass) reuse and extend an
existing class (the <strong>parent</strong>/superclass). The child automatically gets the parent's
attributes and methods, and can add new ones or change existing ones. It models an
<strong>"is-a"</strong> relationship: a <code>Dog</code> <em>is an</em> <code>Animal</code>.</p>

<pre><code>class Animal:
    def __init__(self, name):
        self.name = name
    def eat(self):
        return f"{self.name} is eating"
    def speak(self):
        return "Some generic sound"

class Dog(Animal):                # Dog inherits from Animal
    def speak(self):              # override the parent's version
        return f"{self.name} says Woof!"

class Cat(Animal):
    def speak(self):
        return f"{self.name} says Meow!"

rex = Dog("Rex")
print(rex.eat())      # 'Rex is eating'   — inherited from Animal
print(rex.speak())    # 'Rex says Woof!'  — overridden in Dog</code></pre>
<pre><code>            Animal  (parent: name, eat(), speak())
           /      \
        Dog        Cat        ← children: inherit eat(), override speak()
</code></pre>

<h2>super(): cooperate with the parent</h2>
<p>Often a child wants to <em>extend</em> the parent rather than replace it. <code>super()</code> calls
the parent's version — essential in <code>__init__</code> so the parent can set up its part:</p>
<pre><code>class Animal:
    def __init__(self, name):
        self.name = name

class Dog(Animal):
    def __init__(self, name, breed):
        super().__init__(name)    # let Animal set self.name
        self.breed = breed        # then add Dog-specific data

rex = Dog("Rex", "Labrador")
print(rex.name, rex.breed)        # Rex Labrador</code></pre>

<h2>Overriding and extending methods</h2>
<pre><code>class Vehicle:
    def describe(self):
        return "A vehicle"

class Car(Vehicle):
    def describe(self):
        base = super().describe()       # reuse parent's result
        return f"{base} — specifically a car"

print(Car().describe())   # 'A vehicle — specifically a car'</code></pre>

<h2>Checking relationships</h2>
<pre><code>isinstance(rex, Dog)      # True
isinstance(rex, Animal)   # True — a Dog IS an Animal
issubclass(Dog, Animal)   # True</code></pre>

<h2>Inheritance vs composition — choose well</h2>
<p>Inheritance is powerful but easy to overuse. It creates <strong>tight coupling</strong>: a child
depends on its parent's internals, so parent changes can break children. The alternative,
<strong>composition</strong>, builds objects from other objects ("has-a") rather than inheriting
("is-a").</p>
<pre><code># Inheritance: a Car IS-A Engine?  No — wrong relationship.
# Composition: a Car HAS-A Engine.  Correct.
class Engine:
    def start(self):
        return "Engine started"

class Car:
    def __init__(self):
        self.engine = Engine()        # composition
    def start(self):
        return self.engine.start()    # delegate to the part</code></pre>

<div class="alert alert-info" role="alert">
<strong>Rule of thumb: prefer composition over inheritance.</strong> Use inheritance only for a true
"is-a" relationship where the child genuinely is a specialised kind of the parent. For "has-a" or
"uses-a", compose. Deep inheritance trees (4+ levels) are usually a design smell. We'll revisit this
under SOLID (Liskov Substitution).
</div>

<h2>A note on multiple inheritance & the MRO</h2>
<p>Python allows a class to inherit from several parents. When names collide, Python resolves them via
the <strong>Method Resolution Order</strong> (MRO), which you can inspect with <code>Class.mro()</code>.
Multiple inheritance is powerful but can get confusing — use it sparingly (mixins are the common,
disciplined use).</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting <code>super().__init__()</code></strong> — the parent's setup never runs, so its
attributes are missing.</li>
<li><strong>Inheriting for code reuse alone</strong> when there's no real "is-a" relationship. Use
composition instead.</li>
<li><strong>Deep, fragile hierarchies</strong> where a change high up breaks many descendants.</li>
<li><strong>Overriding a method with an incompatible signature</strong>, breaking code that relied on
the parent's contract (a Liskov violation — next module).</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Inheritance models <strong>"is-a"</strong>: a child reuses and extends a parent's attributes/methods.</li>
<li><strong>Override</strong> to change behaviour; call <code>super()</code> to reuse/extend the parent.</li>
<li>Check types with <code>isinstance</code>/<code>issubclass</code>.</li>
<li><strong>Prefer composition ("has-a") over inheritance</strong> unless there's a true is-a relationship.</li>
</ul>
EOT
      . vid_box('Inheritance, super(), overriding, and composition vs inheritance.', 'python inheritance super composition tutorial'),
    ],

    [
      'title' => 'Pillar 4 — Polymorphism',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>One interface, many forms</h2>
<p><strong>Polymorphism</strong> (Greek for "many shapes") means different object types can be used
through the <em>same</em> interface, each responding in its own way. You write code that says
"call <code>.speak()</code>" and each object does the right thing for its type — no <code>if/elif</code>
checking what kind it is.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> The word "play" means different things to a musician, an actor, and an
athlete — but you can tell any of them "play!" and each does their version. Same message, many
behaviours.
</div>

<h2>Polymorphism via a shared method name</h2>
<pre><code>class Dog:
    def speak(self):
        return "Woof!"
class Cat:
    def speak(self):
        return "Meow!"
class Duck:
    def speak(self):
        return "Quack!"

# One loop handles ALL types uniformly:
for animal in [Dog(), Cat(), Duck()]:
    print(animal.speak())     # Woof! / Meow! / Quack!</code></pre>
<p>The loop doesn't know or care which class each object is. It relies only on the shared
<code>speak()</code> interface. Add a <code>Cow</code> tomorrow and the loop works unchanged — that's the
payoff.</p>

<h2>Compare: without polymorphism (the bad way)</h2>
<pre><code># Brittle — must edit this every time a new type appears:
def make_sound(animal):
    if isinstance(animal, Dog):
        return "Woof!"
    elif isinstance(animal, Cat):
        return "Meow!"
    elif isinstance(animal, Duck):
        return "Quack!"
    # ...endless elif chain</code></pre>
<p>Polymorphism replaces this <code>if/elif</code> ladder with a method call, pushing each behaviour
into its own class. This is one of OOP's biggest practical wins.</p>

<h2>Duck typing: Python's flavour of polymorphism</h2>
<p>Python doesn't require a shared base class for polymorphism. If an object has the method you call,
it works. This is <strong>duck typing</strong>: <em>"If it walks like a duck and quacks like a duck,
treat it as a duck."</em></p>
<pre><code>class FileLogger:
    def log(self, msg):
        print(f"FILE: {msg}")
class ConsoleLogger:
    def log(self, msg):
        print(f"CONSOLE: {msg}")

def run(logger):           # accepts ANYTHING with a .log() method
    logger.log("started")

run(FileLogger())          # FILE: started
run(ConsoleLogger())       # CONSOLE: started</code></pre>
<p>No common parent needed — <code>run</code> just needs <em>something</em> with <code>.log()</code>. This
makes Python code flexible and easy to test (you can pass a fake logger).</p>

<h2>Polymorphism with abstraction (the robust combo)</h2>
<p>Pair polymorphism with an ABC when you want to <em>guarantee</em> the interface exists:</p>
<pre><code>from abc import ABC, abstractmethod

class Shape(ABC):
    @abstractmethod
    def area(self): ...

class Circle(Shape):
    def __init__(self, r): self.r = r
    def area(self): return 3.14159 * self.r ** 2

class Rectangle(Shape):
    def __init__(self, w, h): self.w, self.h = w, h
    def area(self): return self.w * self.h

def total_area(shapes):
    return sum(shape.area() for shape in shapes)   # polymorphic call

print(total_area([Circle(2), Rectangle(3, 4)]))     # 12.566... + 12</code></pre>

<h2>Operator/built-in polymorphism</h2>
<p>You've used polymorphism since lesson one: <code>+</code> adds numbers but joins strings and lists;
<code>len()</code> works on strings, lists, dicts. The same operation adapts to the type. You can give
your own classes this behaviour with dunder methods (next lesson).</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Type-checking instead of trusting the interface.</strong> Long <code>isinstance</code>
ladders usually mean "I should be using polymorphism."</li>
<li><strong>Inconsistent method signatures</strong> across types that should be interchangeable — the
shared interface must truly match.</li>
<li><strong>Assuming a base class is required.</strong> Duck typing works without one; ABCs just make
the contract explicit.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Polymorphism = <strong>same interface, type-specific behaviour</strong>; one piece of code handles
many types.</li>
<li>It replaces brittle <code>if/elif</code> type checks with method calls.</li>
<li><strong>Duck typing:</strong> if an object has the needed method, Python uses it — no shared base
class required.</li>
<li>Combine with ABCs when you want the interface guaranteed.</li>
</ul>
EOT
      . vid_box('Polymorphism and duck typing in Python with practical examples.', 'python polymorphism duck typing tutorial'),
    ],

    [
      'title' => 'Dunder methods and dataclasses',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Dunder ("magic") methods</h2>
<p><strong>Dunder methods</strong> (double-underscore, like <code>__init__</code>) let your objects plug
into Python's built-in syntax. By implementing them, your class can be printed, compared, added,
measured with <code>len()</code>, and more — just like built-in types. This is how you give your
classes <em>polymorphic</em> behaviour with operators.</p>

<h2>The essential ones</h2>
<table>
<thead><tr><th>Method</th><th>Powers</th><th>Example trigger</th></tr></thead>
<tbody>
<tr><td><code>__init__</code></td><td>setup</td><td><code>Point(1, 2)</code></td></tr>
<tr><td><code>__str__</code></td><td>readable text</td><td><code>print(p)</code>, <code>str(p)</code></td></tr>
<tr><td><code>__repr__</code></td><td>debug text</td><td>REPL echo, <code>repr(p)</code></td></tr>
<tr><td><code>__eq__</code></td><td>equality</td><td><code>p1 == p2</code></td></tr>
<tr><td><code>__lt__</code></td><td>ordering</td><td><code>p1 &lt; p2</code>, <code>sorted()</code></td></tr>
<tr><td><code>__len__</code></td><td>length</td><td><code>len(p)</code></td></tr>
<tr><td><code>__add__</code></td><td><code>+</code></td><td><code>p1 + p2</code></td></tr>
</tbody>
</table>

<pre><code>class Point:
    def __init__(self, x, y):
        self.x, self.y = x, y

    def __repr__(self):
        return f"Point({self.x}, {self.y})"     # for developers/debugging

    def __eq__(self, other):
        return self.x == other.x and self.y == other.y

    def __add__(self, other):
        return Point(self.x + other.x, self.y + other.y)

p1 = Point(1, 2)
p2 = Point(1, 2)
print(p1)            # Point(1, 2)        (uses __repr__ here)
print(p1 == p2)      # True               (without __eq__ this would be False!)
print(p1 + Point(5, 5))   # Point(6, 7)   (custom + behaviour)</code></pre>

<div class="alert alert-info" role="alert">
<strong>__str__ vs __repr__.</strong> <code>__str__</code> is the friendly version for end users;
<code>__repr__</code> is the unambiguous version for developers (ideally looks like code that
re-creates the object). If you only write one, write <code>__repr__</code> — Python falls back to it
for <code>str()</code> too.
</div>

<h2>dataclasses: stop writing boilerplate</h2>
<p>Classes that mainly hold data force you to write repetitive <code>__init__</code>, <code>__repr__</code>,
and <code>__eq__</code>. The <code>@dataclass</code> decorator generates all of that from a few typed
field declarations:</p>
<pre><code>from dataclasses import dataclass

@dataclass
class Point:
    x: int
    y: int

p = Point(1, 2)
print(p)             # Point(x=1, y=2)     — __repr__ generated
print(p == Point(1, 2))   # True           — __eq__ generated</code></pre>
<p>That tiny class is equivalent to the long one above. Dataclasses also support defaults, ordering,
and immutability:</p>
<pre><code>@dataclass(order=True, frozen=True)   # comparable AND immutable
class Money:
    amount: float
    currency: str = "USD"             # default value

a = Money(10)
b = Money(20)
print(a &lt; b)         # True   (order=True gives comparisons)
a.amount = 5         # FrozenInstanceError (frozen=True → immutable)</code></pre>

<div class="alert alert-info" role="alert">
<strong>When to use dataclasses.</strong> Reach for <code>@dataclass</code> whenever a class is mostly a
bundle of values (a record, a config, a DTO). It removes boilerplate, reduces bugs, and reads
beautifully. Use a regular class when behaviour — not data — is the main point.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Defining <code>__eq__</code> without <code>__hash__</code></strong> and then trying to use the
object in a set/dict. (Dataclasses handle this for you; <code>frozen=True</code> makes them hashable.)</li>
<li><strong>Only writing <code>__str__</code></strong> and getting an ugly default in the REPL — add
<code>__repr__</code>.</li>
<li><strong>Forgetting type annotations in a dataclass</strong> — a bare <code>x</code> without
<code>x: int</code> is ignored as a field.</li>
<li><strong>Mutable dataclass defaults</strong> (<code>items: list = []</code>) — use
<code>field(default_factory=list)</code>, the same trap as mutable function defaults.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>Dunder methods</strong> (<code>__repr__</code>, <code>__eq__</code>, <code>__add__</code>, ...) let your
objects work with Python's built-in syntax and operators.</li>
<li>Prefer <code>__repr__</code>; it doubles as the fallback for <code>str()</code>.</li>
<li><code>@dataclass</code> auto-generates <code>__init__</code>/<code>__repr__</code>/<code>__eq__</code> for
data-holding classes.</li>
<li>Use <code>frozen=True</code> for immutability, <code>order=True</code> for comparisons,
<code>default_factory</code> for mutable defaults.</li>
</ul>
EOT
      . vid_box('Dunder/magic methods and Python dataclasses explained.', 'python dunder methods dataclasses tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 6 Quiz: Object-Oriented Programming',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What is the role of self in a method?',
        'explain' => 'self refers to the specific instance the method is called on. Python passes it automatically: rex.bark() calls Dog.bark(rex), so self is rex.',
        'options' => [
          ['It refers to the specific instance the method is called on', true],
          ['It is the class itself', false],
          ['It is a reserved keyword you must import', false],
          ['It is the return value of the method', false],
        ],
      ],
      [
        'q' => 'Which principle is about hiding internal state behind a safe interface and protecting invariants?',
        'explain' => 'Encapsulation bundles data with behaviour and hides internal state, exposing a controlled interface (e.g. via @property) to keep objects always valid.',
        'options' => [
          ['Encapsulation', true],
          ['Inheritance', false],
          ['Polymorphism', false],
          ['Recursion', false],
        ],
      ],
      [
        'q' => 'What does @property let you do?',
        'explain' => 'It lets a method be accessed like an attribute, so you can add validation/computation while callers still write obj.x and obj.x = value.',
        'options' => [
          ['Access a method using attribute syntax, enabling validation behind obj.x', true],
          ['Make a class abstract', false],
          ['Automatically inherit from a parent', false],
          ['Convert a function into a class', false],
        ],
      ],
      [
        'q' => 'An abstract base class with an @abstractmethod...',
        'explain' => 'It cannot be instantiated directly, and any concrete subclass must implement every abstract method or it cannot be instantiated either. This enforces a contract.',
        'options' => [
          ['Cannot be instantiated, and forces subclasses to implement the method', true],
          ['Can be instantiated like any class', false],
          ['Automatically writes the method body for you', false],
          ['Is the same as a dataclass', false],
        ],
      ],
      [
        'q' => 'A loop calls shape.area() on Circle and Square objects, each returning its own result. This is...',
        'explain' => 'Polymorphism: the same interface (area()) produces type-specific behaviour, so one loop handles all shapes without type checks.',
        'options' => [
          ['Polymorphism', true],
          ['Encapsulation', false],
          ['A syntax error', false],
          ['Composition', false],
        ],
      ],
      [
        'q' => 'When should you generally prefer composition over inheritance?',
        'explain' => 'Use inheritance only for a true "is-a" relationship. For "has-a"/"uses-a" relationships, compose objects from parts to avoid tight coupling and fragile hierarchies.',
        'options' => [
          ['When the relationship is "has-a" rather than a true "is-a"', true],
          ['Always — inheritance should never be used', false],
          ['Only when using dataclasses', false],
          ['When you want faster code', false],
        ],
      ],
      [
        'q' => 'What does @dataclass generate for you?',
        'explain' => 'It auto-generates __init__, __repr__, and __eq__ (and optionally ordering/immutability) from typed field declarations, removing boilerplate.',
        'options' => [
          ['__init__, __repr__ and __eq__ from the declared fields', true],
          ['Database tables', false],
          ['Abstract methods', false],
          ['Nothing — it is just documentation', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 7 — CLEAN CODE & SOLID
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Clean Code & SOLID Principles',
  'description' => 'Write code other people (and future you) can read and change safely. Naming and structure, the SOLID design principles each explained with before/after Python, and the practical heuristics DRY, KISS, YAGNI, and composition over inheritance.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'What is clean code, and why it matters',
      'minutes' => 14,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Code is read far more than it is written</h2>
<p>You write a line once; you and your teammates read it dozens of times while fixing and extending
it. <strong>Clean code</strong> is code optimised for that reading — clear, simple, and easy to change
without fear. It is not about cleverness; it's about <em>communication</em>.</p>
<p>This is the Zen of Python made practical: "Readability counts." Messy code still runs — but it
slows every future change and hides bugs. Clean code is a long-term investment that pays back on
every edit.</p>

<h2>Names are documentation</h2>
<p>Good names remove the need for most comments. Compare:</p>
<pre><code># Unclear
def calc(x, y):
    return x * y * 0.1

# Clear — the names explain themselves
def calculate_commission(sale_amount, quantity):
    COMMISSION_RATE = 0.1
    return sale_amount * quantity * COMMISSION_RATE</code></pre>
<p>Rules of thumb: use full words (<code>customer</code> not <code>cust</code>); name booleans as
questions (<code>is_active</code>, <code>has_access</code>); name functions as verbs
(<code>send_email</code>); avoid single letters except short loop counters; replace "magic numbers"
with named constants.</p>

<h2>Small functions that do one thing</h2>
<p>A function should do <em>one</em> thing at <em>one</em> level of detail. If you describe it with
"and", it's probably two functions. Small functions are easier to name, test, and reuse.</p>
<pre><code># Doing too much
def process(order):
    # validate, calculate tax, save to db, send email...  (40 lines)

# Decomposed — each step is named and testable
def process(order):
    validate(order)
    total = calculate_total(order)
    save(order, total)
    send_confirmation(order)</code></pre>

<h2>Consistent style: PEP 8</h2>
<p>Python has an official style guide, <strong>PEP 8</strong>. The essentials: 4-space indentation,
<code>snake_case</code> for functions/variables, <code>CapWords</code> for classes,
<code>UPPER_CASE</code> for constants, spaces around operators, and lines kept reasonably short.
You don't memorise it — you use a <strong>formatter</strong> and <strong>linter</strong>:</p>
<pre><code>pip install black ruff
black myfile.py     # auto-formats your code to a consistent style
ruff check myfile.py  # flags style issues, unused imports, likely bugs</code></pre>
<p>Let tools handle formatting so you can focus on logic. Most teams run these automatically.</p>

<div class="alert alert-info" role="alert">
<strong>The boy-scout rule.</strong> "Leave the code a little cleaner than you found it." You don't
need a giant rewrite — small, steady improvements (a better name here, a split function there) keep
a codebase healthy over time.
</div>

<h2>Comments: explain why, delete the rest</h2>
<p>Prefer code that doesn't need comments. When you do comment, explain the <em>reasoning</em> a reader
can't infer — a tricky edge case, a business rule, a "why we do it this odd way". Delete commented-out
code; Git remembers it.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Cryptic names</strong> (<code>d</code>, <code>tmp</code>, <code>data2</code>) that force readers to
reverse-engineer intent.</li>
<li><strong>Giant functions</strong> doing many things — hard to test and reuse.</li>
<li><strong>Magic numbers/strings</strong> scattered around instead of named constants.</li>
<li><strong>"Clever" one-liners</strong> that are shorter but unreadable. Clear beats clever.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Clean code optimises for <strong>reading and changing</strong>, not just running.</li>
<li><strong>Good names</strong> and <strong>small single-purpose functions</strong> do most of the work.</li>
<li>Follow <strong>PEP 8</strong> automatically with <code>black</code> + <code>ruff</code>.</li>
<li>Comment the <em>why</em>; let clear code show the <em>what</em>.</li>
</ul>
EOT
      . vid_box('Clean code principles and naming, with PEP 8, black and ruff.', 'python clean code pep8 black ruff tutorial'),
    ],

    [
      'title' => 'SOLID part 1 — SRP & OCP',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What is SOLID?</h2>
<p><strong>SOLID</strong> is five design principles for classes that make object-oriented code easier to
extend and maintain. They're guidelines, not laws — but understanding them sharply improves your
designs. This lesson covers the first two; the next covers L, I, and D.</p>
<pre><code>S  Single Responsibility Principle
O  Open/Closed Principle
L  Liskov Substitution Principle
I  Interface Segregation Principle
D  Dependency Inversion Principle</code></pre>

<h2>S — Single Responsibility Principle (SRP)</h2>
<p><em>A class should have one, and only one, reason to change.</em> Put another way: each class should
do one job. When a class mixes concerns, a change to one concern risks breaking the others, and the
class becomes hard to understand and test.</p>
<pre><code># VIOLATION: this class does three unrelated jobs
class Report:
    def generate(self): ...        # build the report data
    def save_to_file(self): ...    # file/storage concern
    def send_email(self): ...      # networking/email concern
</code></pre>
<p>Storage rules, email rules, and report logic all change for different reasons and now live
together. Split by responsibility:</p>
<pre><code># FIX: one responsibility each
class Report:
    def generate(self): ...

class ReportStorage:
    def save(self, report): ...

class ReportMailer:
    def send(self, report): ...</code></pre>
<p>Now each class has a single reason to change, and you can test/reuse them independently. (Notice
this is the class-level version of "functions should do one thing".)</p>

<div class="alert alert-info" role="alert">
<strong>How to spot an SRP violation.</strong> If you describe a class with "and" — "it generates the
report <em>and</em> saves it <em>and</em> emails it" — it has too many responsibilities. Also: lots of
unrelated imports, or methods that touch totally different kinds of data.
</div>

<h2>O — Open/Closed Principle (OCP)</h2>
<p><em>Software entities should be open for extension but closed for modification.</em> You should be
able to add new behaviour by <strong>adding new code</strong>, not by <strong>editing existing, working
code</strong>. Editing tested code to add a case risks breaking it.</p>
<p>The classic smell is a growing <code>if/elif</code> chain on a "type":</p>
<pre><code># VIOLATION: every new shape forces editing this function
def area(shape):
    if shape.kind == "circle":
        return 3.14159 * shape.r ** 2
    elif shape.kind == "square":
        return shape.side ** 2
    # add a triangle? edit here again... and again...</code></pre>
<p>Use polymorphism so new types <em>extend</em> the system without touching existing code:</p>
<pre><code>from abc import ABC, abstractmethod

class Shape(ABC):
    @abstractmethod
    def area(self): ...

class Circle(Shape):
    def __init__(self, r): self.r = r
    def area(self): return 3.14159 * self.r ** 2

class Square(Shape):
    def __init__(self, s): self.s = s
    def area(self): return self.s ** 2

# Adding Triangle later = a NEW class. This function never changes:
def total_area(shapes):
    return sum(shape.area() for shape in shapes)</code></pre>

<div class="alert alert-info" role="alert">
<strong>Connection.</strong> OCP is usually achieved <em>through</em> abstraction + polymorphism (Module
6). The abstract <code>Shape</code> is the stable thing; concrete shapes are the extensions. When you
find yourself editing a long <code>if/elif</code> to add a case, that's OCP asking for polymorphism.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>"God classes"</strong> that do everything — the opposite of SRP.</li>
<li><strong>Splitting too far</strong> — a dozen one-method classes for a tiny script is over-engineering.
Apply SRP where change actually happens.</li>
<li><strong>Editing core logic for every new case</strong> instead of extending via new classes (OCP).</li>
<li><strong>Premature OCP:</strong> building elaborate extension points for variation that never comes.
Add abstraction when a second case appears.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>SRP:</strong> one class, one responsibility, one reason to change.</li>
<li><strong>OCP:</strong> add behaviour by adding code, not editing working code — usually via
polymorphism.</li>
<li>A growing <code>if/elif</code> on a type is the signal to apply OCP.</li>
<li>Balance: apply these where change is real; don't over-engineer small programs.</li>
</ul>
EOT
      . vid_box('Single Responsibility and Open/Closed principles with Python examples.', 'SOLID principles python SRP OCP arjancodes'),
    ],

    [
      'title' => 'SOLID part 2 — LSP, ISP & DIP',
      'minutes' => 22,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>L — Liskov Substitution Principle (LSP)</h2>
<p><em>Subtypes must be substitutable for their base types.</em> Anywhere code expects a parent class,
you should be able to pass any child <strong>without surprises</strong>. A subclass must honour the
parent's contract — not weaken it or change its meaning.</p>
<pre><code># VIOLATION: a Penguin IS-A Bird, but can't fly
class Bird:
    def fly(self):
        return "flying"

class Penguin(Bird):
    def fly(self):
        raise NotImplementedError("penguins can't fly!")

def make_it_fly(bird: Bird):
    return bird.fly()        # breaks for a Penguin — substitution fails</code></pre>
<p>The hierarchy is wrong: not every bird flies. Fix the model so substitution always holds:</p>
<pre><code>class Bird:
    def eat(self): ...

class FlyingBird(Bird):
    def fly(self): return "flying"

class Penguin(Bird):     # a Bird, but NOT a FlyingBird
    def swim(self): return "swimming"</code></pre>

<div class="alert alert-info" role="alert">
<strong>LSP and inheritance.</strong> This is the precise reason for "prefer composition over
inheritance" (Module 6). If a subclass can't fully stand in for its parent, the "is-a" relationship
is false. Symptoms: a subclass that overrides a method to raise an error, do nothing, or return an
incompatible type.
</div>

<h2>I — Interface Segregation Principle (ISP)</h2>
<p><em>Clients shouldn't be forced to depend on methods they don't use.</em> Prefer several small,
focused interfaces over one big "do-everything" interface. A fat interface forces classes to
implement irrelevant methods (often as empty stubs or errors).</p>
<pre><code># VIOLATION: one fat interface
class Worker(ABC):
    @abstractmethod
    def work(self): ...
    @abstractmethod
    def eat(self): ...

class Robot(Worker):
    def work(self): return "working"
    def eat(self): raise NotImplementedError  # robots don't eat!</code></pre>
<p>Split into focused contracts so each class implements only what it truly does:</p>
<pre><code>class Workable(ABC):
    @abstractmethod
    def work(self): ...

class Eatable(ABC):
    @abstractmethod
    def eat(self): ...

class Human(Workable, Eatable):
    def work(self): return "working"
    def eat(self): return "eating"

class Robot(Workable):       # only Workable — no forced eat()
    def work(self): return "working"</code></pre>

<h2>D — Dependency Inversion Principle (DIP)</h2>
<p><em>Depend on abstractions, not on concrete implementations.</em> High-level code (your business
logic) shouldn't be hard-wired to low-level details (a specific database, email provider, file
format). Both should depend on an abstraction, so you can swap the detail without touching the
logic.</p>
<pre><code># VIOLATION: high-level OrderService is welded to a specific DB
class MySQLDatabase:
    def save(self, data): ...

class OrderService:
    def __init__(self):
        self.db = MySQLDatabase()      # hard dependency — can't swap or test
    def place(self, order):
        self.db.save(order)</code></pre>
<p>Invert it: depend on an abstract interface and <strong>inject</strong> the concrete one from outside
(dependency injection):</p>
<pre><code>class Database(ABC):
    @abstractmethod
    def save(self, data): ...

class MySQLDatabase(Database):
    def save(self, data): ...

class OrderService:
    def __init__(self, db: Database):   # depends on the abstraction
        self.db = db                    # injected from outside
    def place(self, order):
        self.db.save(order)

# Swap implementations freely — and pass a fake in tests:
service = OrderService(MySQLDatabase())
test_service = OrderService(FakeDatabase())   # easy to test!</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why DIP is a superpower for testing.</strong> Because <code>OrderService</code> accepts any
<code>Database</code>, you can pass a fake/in-memory one in tests — no real database needed. Loose
coupling and testability go hand in hand. We use exactly this idea in the testing module.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>LSP:</strong> subclasses that override a method to raise <code>NotImplementedError</code> or
quietly do nothing — a sign the hierarchy is wrong.</li>
<li><strong>ISP:</strong> fat interfaces with optional methods stubbed out across implementations.</li>
<li><strong>DIP:</strong> classes that build their own dependencies internally (<code>= MySQLDatabase()</code>),
making them rigid and untestable. Inject instead.</li>
<li><strong>Cargo-culting SOLID</strong> into tiny scripts — these principles earn their keep as systems
grow.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>LSP:</strong> a subclass must be a true drop-in for its parent — no broken promises.</li>
<li><strong>ISP:</strong> many small, focused interfaces beat one fat one.</li>
<li><strong>DIP:</strong> depend on abstractions and <em>inject</em> concrete implementations — this is
what makes code swappable and testable.</li>
<li>SOLID principles reinforce each other and build on encapsulation/abstraction/polymorphism.</li>
</ul>
EOT
      . vid_box('Liskov, Interface Segregation, and Dependency Inversion with Python.', 'SOLID principles python LSP ISP DIP arjancodes'),
    ],

    [
      'title' => 'DRY, KISS, YAGNI & composition over inheritance',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Heuristics that keep code sane</h2>
<p>Beyond SOLID, a handful of short principles guide everyday decisions. They're memorable on
purpose — say them to yourself while coding.</p>

<h2>DRY — Don't Repeat Yourself</h2>
<p>Every piece of knowledge should have a <strong>single, authoritative home</strong>. Duplicated logic
means you must remember to change it in every copy — and you'll miss one.</p>
<pre><code># WET ("write everything twice")
price_with_tax = price + price * 0.075
ship_with_tax  = ship + ship * 0.075     # the 0.075 rule is duplicated

# DRY
TAX_RATE = 0.075
def with_tax(amount):
    return amount + amount * TAX_RATE</code></pre>
<div class="alert alert-warning" role="alert">
<strong>But don't over-DRY.</strong> Two pieces of code that <em>look</em> similar but change for
<em>different reasons</em> are not real duplication — forcing them together creates the wrong coupling.
DRY is about duplicated <em>knowledge</em>, not duplicated <em>characters</em>.
</div>

<h2>KISS — Keep It Simple, Stupid</h2>
<p>Prefer the simplest solution that works. Clever, abstract, "future-proof" designs often cost more
than they save. Simple code is easier to read, debug, and change.</p>
<pre><code># Over-engineered
def is_even(n):
    return [True, False][n % 2 != 0]

# KISS
def is_even(n):
    return n % 2 == 0</code></pre>

<h2>YAGNI — You Aren't Gonna Need It</h2>
<p>Don't build features, options, or abstractions "in case we need them later". Most speculative
features never get used, yet you maintain them forever. Build what's needed <em>now</em>; add more
when a real need arrives.</p>
<p>YAGNI balances the design principles: SOLID tells you how to structure code <em>when</em> variation
exists; YAGNI reminds you not to invent variation that doesn't.</p>

<h2>Composition over inheritance (revisited)</h2>
<p>You met this in Module 6; it deserves its place among the core heuristics. Favour assembling
behaviour from smaller parts ("has-a") over deep inheritance chains ("is-a"), because composition
is more flexible and avoids fragile hierarchies.</p>
<pre><code># Inheritance explosion: FlyingCar? AmphibiousCar? FlyingBoat?
# Composition: give a vehicle the capabilities it has.
class Engine:
    def start(self): return "vroom"

class GPS:
    def locate(self): return "12.34, 56.78"

class Car:
    def __init__(self):
        self.engine = Engine()    # has-a
        self.gps = GPS()          # has-a
    def start(self): return self.engine.start()</code></pre>
<p>Need a new capability? Add a component. Need a variant? Swap a component. No combinatorial
subclass explosion.</p>

<div class="alert alert-info" role="alert">
<strong>How it all fits.</strong> Clean code (names, small functions) is the foundation; SOLID guides
class design; DRY/KISS/YAGNI are the day-to-day filters. None is a rigid rule — together they push
you toward code that's simple now and safe to change later. When two principles seem to conflict,
favour the simpler, more readable result.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Over-DRYing</strong> coincidentally-similar code into a tangled shared function.</li>
<li><strong>Violating KISS</strong> with abstraction layers a small problem doesn't need.</li>
<li><strong>Ignoring YAGNI</strong> and gold-plating features nobody asked for.</li>
<li><strong>Reaching for inheritance reflexively</strong> when composition is the better fit.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>DRY:</strong> one home for each piece of knowledge — but only real duplication.</li>
<li><strong>KISS:</strong> simplest thing that works beats clever.</li>
<li><strong>YAGNI:</strong> build for today's needs, not imagined ones.</li>
<li><strong>Composition over inheritance:</strong> assemble behaviour from parts to stay flexible.</li>
</ul>
EOT
      . vid_box('DRY, KISS, YAGNI and composition over inheritance in practice.', 'DRY KISS YAGNI principles programming explained'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 7 Quiz: Clean Code & SOLID',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'The Single Responsibility Principle (SRP) says a class should...',
        'explain' => 'A class should have one responsibility — one reason to change. Mixing concerns (e.g. report logic + storage + email) violates SRP.',
        'options' => [
          ['Have only one reason to change (one responsibility)', true],
          ['Have only one method', false],
          ['Never use inheritance', false],
          ['Be as small as a single line', false],
        ],
      ],
      [
        'q' => 'A growing if/elif chain on an object\'s "type" most directly suggests applying which principle?',
        'explain' => 'Open/Closed: add new behaviour by adding new classes (polymorphism) instead of editing the existing chain each time.',
        'options' => [
          ['Open/Closed Principle', true],
          ['Single Responsibility Principle', false],
          ['Interface Segregation Principle', false],
          ['DRY', false],
        ],
      ],
      [
        'q' => 'A subclass overrides a parent method to raise NotImplementedError. Which principle does this most likely violate?',
        'explain' => 'Liskov Substitution: the subclass cannot stand in for its parent, so the "is-a" relationship is broken.',
        'options' => [
          ['Liskov Substitution Principle', true],
          ['Single Responsibility Principle', false],
          ['KISS', false],
          ['DRY', false],
        ],
      ],
      [
        'q' => 'The Dependency Inversion Principle recommends that high-level code should...',
        'explain' => 'Depend on abstractions (interfaces) and have concrete implementations injected, rather than constructing concrete dependencies internally. This enables swapping and testing.',
        'options' => [
          ['Depend on abstractions and receive concrete implementations from outside', true],
          ['Create its own concrete dependencies internally', false],
          ['Avoid using classes entirely', false],
          ['Always inherit from a base class', false],
        ],
      ],
      [
        'q' => 'What does YAGNI advise?',
        'explain' => 'You Aren\'t Gonna Need It: don\'t build speculative features/abstractions before there is a real need.',
        'options' => [
          ['Don\'t build features until they are actually needed', true],
          ['Always add extra options for the future', false],
          ['Repeat code to be safe', false],
          ['Use the cleverest solution available', false],
        ],
      ],
      [
        'q' => 'DRY is fundamentally about avoiding duplication of...',
        'explain' => 'DRY targets duplicated knowledge/logic (a single source of truth), not merely code that looks similar but changes for different reasons.',
        'options' => [
          ['Knowledge/logic — a single source of truth', true],
          ['Any code that looks similar, always', false],
          ['Variable names', false],
          ['Comments', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 8 — ERROR & EXCEPTION HANDLING
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Error & Exception Handling',
  'description' => 'Things go wrong — bad input, missing files, network hiccups. Learn to read tracebacks, catch exceptions with try/except/else/finally, raise your own (including custom exception classes), and choose the Pythonic EAFP style.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Exceptions and reading tracebacks',
      'minutes' => 14,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Errors are normal — handle them on purpose</h2>
<p>When Python hits something it can't do — dividing by zero, converting "abc" to an int, opening a
missing file — it <strong>raises an exception</strong>. If nothing handles it, the program stops and
prints a <strong>traceback</strong>. Learning to read tracebacks and handle exceptions is what
separates a fragile script from robust software.</p>

<h2>Syntax errors vs exceptions</h2>
<ul>
<li><strong>SyntaxError</strong> happens while Python is <em>reading</em> your code — it can't even start
(a missing colon, unbalanced bracket). Fix the code.</li>
<li><strong>Exceptions</strong> happen while the program <em>runs</em> — the code is valid but a situation
went wrong. These you can catch and handle.</li>
</ul>

<h2>Reading a traceback (read it bottom-up)</h2>
<pre><code>Traceback (most recent call last):
  File "app.py", line 10, in &lt;module&gt;
    main()
  File "app.py", line 6, in main
    result = 10 / divisor
ZeroDivisionError: division by zero</code></pre>
<p>The <strong>last line</strong> is the most useful: the exception <em>type</em>
(<code>ZeroDivisionError</code>) and <em>message</em> (<code>division by zero</code>). Above it is the
<strong>call stack</strong>, newest at the bottom — it shows the exact file and line where the error
happened and how the code got there. <em>Always read the bottom line first.</em></p>

<div class="alert alert-info" role="alert">
<strong>Don't fear red text.</strong> A traceback is Python <em>helping</em> you: it names exactly what
went wrong and where. Beginners panic and skim it; pros read the last line, jump to the named file
and line, and usually see the bug immediately.
</div>

<h2>Common built-in exceptions</h2>
<table>
<thead><tr><th>Exception</th><th>Typical cause</th></tr></thead>
<tbody>
<tr><td><code>ValueError</code></td><td>Right type, wrong value: <code>int("abc")</code></td></tr>
<tr><td><code>TypeError</code></td><td>Wrong type: <code>"x" + 5</code></td></tr>
<tr><td><code>KeyError</code></td><td>Missing dict key: <code>d["nope"]</code></td></tr>
<tr><td><code>IndexError</code></td><td>List index out of range: <code>lst[99]</code></td></tr>
<tr><td><code>FileNotFoundError</code></td><td>Opening a file that doesn't exist</td></tr>
<tr><td><code>ZeroDivisionError</code></td><td>Dividing by zero</td></tr>
<tr><td><code>AttributeError</code></td><td>Accessing a method/attribute that doesn't exist</td></tr>
<tr><td><code>NameError</code></td><td>Using a variable that was never defined</td></tr>
</tbody>
</table>

<h2>Exceptions are objects in a hierarchy</h2>
<p>Every exception is an object whose class inherits from <code>Exception</code> (which inherits from
<code>BaseException</code>). This hierarchy matters: catching a parent type also catches its children.
<code>ValueError</code> and <code>TypeError</code> are both <code>Exception</code>s, so
<code>except Exception</code> catches them both (use that broad catch sparingly).</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Reading the traceback top-down</strong> and missing the key bottom line.</li>
<li><strong>Confusing SyntaxError with runtime exceptions</strong> — you can't <code>try/except</code> a
syntax error away; you fix the code.</li>
<li><strong>Ignoring the exception type</strong> — it tells you the category of problem and which
<code>except</code> to write.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Runtime problems <strong>raise exceptions</strong>; unhandled ones print a <strong>traceback</strong> and
stop the program.</li>
<li>Read tracebacks <strong>bottom-up</strong>: type + message first, then the file/line.</li>
<li>Learn the common types (<code>ValueError</code>, <code>KeyError</code>, <code>FileNotFoundError</code>, ...).</li>
<li>Exceptions are objects in an inheritance hierarchy rooted at <code>Exception</code>.</li>
</ul>
EOT
      . vid_box('Understanding exceptions and reading Python tracebacks.', 'python exceptions and traceback explained beginners'),
    ],

    [
      'title' => 'try, except, else, finally',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Catching exceptions</h2>
<p>Wrap risky code in <code>try</code>; handle problems in <code>except</code>:</p>
<pre><code>try:
    age = int(input("Your age: "))
    print(f"Next year you'll be {age + 1}")
except ValueError:
    print("That wasn't a valid number.")</code></pre>
<p>If <code>int()</code> raises <code>ValueError</code>, Python jumps straight to the matching
<code>except</code> block instead of crashing. If no error occurs, the <code>except</code> is skipped.</p>

<h2>Catch specific exceptions, in order</h2>
<pre><code>try:
    data = records[index]
    value = int(data)
    result = 100 / value
except IndexError:
    print("No record at that position")
except ValueError:
    print("Record isn't a number")
except ZeroDivisionError:
    print("Value was zero")</code></pre>
<p>You can also catch several in one block: <code>except (ValueError, TypeError):</code>.</p>

<h2>Accessing the exception object</h2>
<pre><code>try:
    int("abc")
except ValueError as e:
    print(f"Conversion failed: {e}")   # Conversion failed: invalid literal for int()...</code></pre>

<h2>else and finally</h2>
<p>The full shape has four parts, each with a clear job:</p>
<pre><code>try:
    f = open("data.txt")
    contents = f.read()
except FileNotFoundError:
    print("File missing")          # runs only if that error happened
else:
    print("Read OK:", len(contents))  # runs only if NO exception occurred
finally:
    print("Cleanup")               # ALWAYS runs (error or not)
</code></pre>
<table>
<thead><tr><th>Block</th><th>Runs when</th><th>Use for</th></tr></thead>
<tbody>
<tr><td><code>try</code></td><td>always (the risky code)</td><td>the operation that might fail</td></tr>
<tr><td><code>except</code></td><td>a matching error occurs</td><td>handling/recovering</td></tr>
<tr><td><code>else</code></td><td>no exception occurred</td><td>code that should run only on success</td></tr>
<tr><td><code>finally</code></td><td>always, no matter what</td><td>cleanup (close files, release locks)</td></tr>
</tbody>
</table>

<div class="alert alert-warning" role="alert">
<strong>Never use a bare <code>except:</code></strong> (or <code>except Exception: pass</code>) to swallow
everything silently. It hides real bugs, catches things you didn't mean to (even
<code>Ctrl+C</code>), and makes debugging miserable. Catch the <em>specific</em> exceptions you can
actually handle; let the rest surface.
</div>

<h2>A robust input loop</h2>
<pre><code>while True:
    try:
        age = int(input("Your age: "))
    except ValueError:
        print("Please enter a whole number.")
        continue          # ask again
    if age &lt; 0:
        print("Age can't be negative.")
        continue
    break                 # valid — leave the loop
print(f"Thanks! You are {age}.")</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Bare <code>except:</code></strong> hiding all errors — catch specific types.</li>
<li><strong>Wrapping too much</strong> in one <code>try</code> so you can't tell which line failed — keep
<code>try</code> blocks tight.</li>
<li><strong>Using exceptions for normal control flow</strong> where a simple <code>if</code> is clearer.</li>
<li><strong>Putting cleanup in <code>try</code> instead of <code>finally</code></strong>, so it's skipped on
error. (Better: use <code>with</code> — next lesson on files.)</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>try</code> runs risky code; <code>except</code> handles specific exceptions.</li>
<li>Use <code>except SomeError as e</code> to read the message; catch types specifically.</li>
<li><code>else</code> runs on success; <code>finally</code> always runs (great for cleanup).</li>
<li>Avoid bare <code>except</code>; never silently swallow errors.</li>
</ul>
EOT
      . vid_box('try/except/else/finally and handling exceptions the right way.', 'python try except finally tutorial corey schafer'),
    ],

    [
      'title' => 'Raising exceptions, custom exceptions & EAFP',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Raising your own exceptions</h2>
<p>Don't let bad data flow silently through your program. When a function receives something invalid,
<strong>raise</strong> an exception to stop it early and clearly:</p>
<pre><code>def set_age(age):
    if not isinstance(age, int):
        raise TypeError("age must be an integer")
    if age &lt; 0:
        raise ValueError("age cannot be negative")
    return age</code></pre>
<p>Raising early (<strong>fail fast</strong>) means problems surface at their source with a clear
message, instead of causing a confusing error far away later.</p>

<h2>Custom exception classes</h2>
<p>For your own error categories, define exception classes by subclassing <code>Exception</code>. This
lets callers catch <em>your specific</em> error precisely:</p>
<pre><code>class InsufficientFundsError(Exception):
    """Raised when a withdrawal exceeds the balance."""

class Account:
    def __init__(self, balance):
        self.balance = balance
    def withdraw(self, amount):
        if amount &gt; self.balance:
            raise InsufficientFundsError(
                f"Tried to withdraw {amount}, only {self.balance} available"
            )
        self.balance -= amount

# Caller handles YOUR error type specifically:
try:
    account.withdraw(1000)
except InsufficientFundsError as e:
    print("Declined:", e)</code></pre>
<p>Custom exceptions make code self-documenting and let different problems be handled differently.</p>

<h2>re-raising and chaining</h2>
<pre><code>try:
    risky()
except ValueError as e:
    log(e)
    raise            # re-raise the same error after logging

# Wrap a low-level error in a domain one, preserving the cause:
try:
    parse(config)
except KeyError as e:
    raise ConfigError("bad config file") from e</code></pre>

<h2>EAFP vs LBYL — the Pythonic choice</h2>
<p>Two philosophies for risky operations:</p>
<ul>
<li><strong>LBYL</strong> — "Look Before You Leap": check conditions first, then act.</li>
<li><strong>EAFP</strong> — "Easier to Ask Forgiveness than Permission": just try it, handle the
exception if it fails. <em>Python prefers EAFP.</em></li>
</ul>
<pre><code># LBYL — check first (can have race conditions, extra lookups)
if "email" in user and user["email"]:
    send(user["email"])

# EAFP — try, then handle (Pythonic)
try:
    send(user["email"])
except KeyError:
    print("No email on file")</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why EAFP?</strong> It avoids the "check then act" gap (where the world can change between the
check and the action), and it keeps the normal path clean — you don't clutter the happy case with
defensive checks. Use LBYL when a check is cheap and clearly clearer; otherwise lean EAFP.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Returning error codes or <code>None</code></strong> instead of raising — callers forget to
check, and bad data spreads. Prefer raising.</li>
<li><strong>Raising bare <code>Exception</code></strong> instead of a specific/custom type, so callers
can't catch precisely.</li>
<li><strong>Catching, then losing the original cause</strong> — use <code>raise ... from e</code> to keep
the chain.</li>
<li><strong>Over-checking (LBYL everywhere)</strong> when a clean <code>try/except</code> reads better.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>raise</code> stops bad data early with a clear message (<strong>fail fast</strong>).</li>
<li>Define <strong>custom exceptions</strong> (subclass <code>Exception</code>) so callers can catch your
specific errors.</li>
<li>Re-raise with bare <code>raise</code>; preserve causes with <code>raise ... from e</code>.</li>
<li>Python favours <strong>EAFP</strong> (try/except) over LBYL (check-first).</li>
</ul>
EOT
      . vid_box('Raising exceptions, writing custom exception classes, and EAFP vs LBYL.', 'python raise custom exceptions eafp tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 8 Quiz: Error Handling',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'When reading a traceback, which line is usually the most useful?',
        'explain' => 'The last line names the exception type and message. The lines above show the call stack (where it happened).',
        'options' => [
          ['The last line — the exception type and message', true],
          ['The first line', false],
          ['The middle line', false],
          ['Tracebacks contain no useful information', false],
        ],
      ],
      [
        'q' => 'Which exception does int("abc") raise?',
        'explain' => 'The type is right (a string) but the value can\'t be parsed as an integer, so it raises ValueError.',
        'options' => [
          ['ValueError', true],
          ['TypeError', false],
          ['KeyError', false],
          ['SyntaxError', false],
        ],
      ],
      [
        'q' => 'In try/except/else/finally, which block always runs?',
        'explain' => 'finally always runs, whether or not an exception occurred — ideal for cleanup.',
        'options' => [
          ['finally', true],
          ['else', false],
          ['except', false],
          ['try only', false],
        ],
      ],
      [
        'q' => 'Why should you avoid a bare except: that catches everything and passes?',
        'explain' => 'It silently hides real bugs (and even things like KeyboardInterrupt), making problems invisible and debugging very hard. Catch specific exceptions you can handle.',
        'options' => [
          ['It hides real bugs and makes debugging very hard', true],
          ['It is faster but uses more memory', false],
          ['It is required in every program', false],
          ['It only works in Python 2', false],
        ],
      ],
      [
        'q' => 'How do you define a custom exception type?',
        'explain' => 'Subclass Exception: class MyError(Exception): pass. Then you can raise and catch it specifically.',
        'options' => [
          ['Subclass Exception, e.g. class MyError(Exception): ...', true],
          ['Call raise CustomError() without defining anything', false],
          ['Import it from the errors module', false],
          ['You cannot create custom exceptions in Python', false],
        ],
      ],
      [
        'q' => 'EAFP, the Pythonic style, stands for...',
        'explain' => '"Easier to Ask Forgiveness than Permission" — attempt the operation and handle the exception if it fails, rather than checking everything first (LBYL).',
        'options' => [
          ['Easier to Ask Forgiveness than Permission', true],
          ['Evaluate All Functions Properly', false],
          ['Exceptions Are For Programmers', false],
          ['Each Argument Found in Parameters', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 9 — FILE HANDLING & DATA FORMATS
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'File Handling & Data Formats',
  'description' => 'Read and write files safely with context managers, navigate the filesystem with pathlib, and work with the two most common data formats: JSON and CSV. Persisting data is what turns a script into a useful tool.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Reading and writing files with context managers',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Why files?</h2>
<p>Variables vanish when your program ends. To <strong>persist</strong> data — save notes, logs,
results, configuration — you write it to a file and read it back later.</p>

<h2>The with statement (always use it)</h2>
<p>Opening a file ties up a system resource that must be closed. The <code>with</code> statement (a
<strong>context manager</strong>) opens the file and <strong>guarantees it's closed</strong> when the block
ends — even if an error happens inside:</p>
<pre><code>with open("notes.txt", "r") as f:
    contents = f.read()
# file is automatically closed here, no matter what
print(contents)</code></pre>
<p>Compare the manual way (don't do this):</p>
<pre><code>f = open("notes.txt")
contents = f.read()
f.close()           # easy to forget; skipped entirely if an error is raised above</code></pre>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> <code>with</code> is like a self-closing door: you walk through, do your thing,
and it shuts behind you automatically — even if you leave in a hurry (an exception). You never leave
a file "open" by accident.
</div>

<h2>File modes</h2>
<table>
<thead><tr><th>Mode</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>"r"</code></td><td>read (default); error if the file is missing</td></tr>
<tr><td><code>"w"</code></td><td>write; <strong>creates or TRUNCATES</strong> (erases existing content!)</td></tr>
<tr><td><code>"a"</code></td><td>append; add to the end, keep existing content</td></tr>
<tr><td><code>"x"</code></td><td>create; error if it already exists</td></tr>
<tr><td><code>"r+"</code></td><td>read and write</td></tr>
</tbody>
</table>

<h2>Reading: three ways</h2>
<pre><code>with open("data.txt") as f:
    whole = f.read()          # entire file as ONE string

with open("data.txt") as f:
    lines = f.readlines()     # list of lines (each keeps its \n)

with open("data.txt") as f:
    for line in f:            # BEST for big files — one line at a time
        print(line.rstrip())  # rstrip() drops the trailing newline</code></pre>
<p>Looping line-by-line is memory-friendly: it never loads the whole file at once, so it handles
gigabyte files fine.</p>

<h2>Writing</h2>
<pre><code>with open("output.txt", "w") as f:    # 'w' erases any existing file!
    f.write("First line\n")           # you must add \n yourself
    f.write("Second line\n")

lines = ["apple\n", "banana\n"]
with open("fruits.txt", "w") as f:
    f.writelines(lines)

with open("log.txt", "a") as f:       # append — safe, keeps history
    f.write("event happened\n")</code></pre>

<div class="alert alert-warning" role="alert">
<strong>The "w" trap.</strong> Opening an existing file in <code>"w"</code> mode <em>immediately erases
it</em>, before you write anything. If you mean to add to a file, use <code>"a"</code> (append). Many
beginners lose data by reaching for <code>"w"</code> out of habit.
</div>

<h2>Encoding</h2>
<p>For text, specify <code>encoding="utf-8"</code> so emojis and non-English characters work reliably
across systems:</p>
<pre><code>with open("notes.txt", "w", encoding="utf-8") as f:
    f.write("Café — 日本語 — 🚀")</code></pre>

<h2>Handling a missing file</h2>
<pre><code>try:
    with open("config.txt") as f:
        config = f.read()
except FileNotFoundError:
    config = "default settings"</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Not using <code>with</code></strong> and leaking open file handles.</li>
<li><strong>Using <code>"w"</code> when you meant <code>"a"</code></strong> and wiping the file.</li>
<li><strong>Forgetting <code>\n</code></strong> — <code>f.write</code> doesn't add newlines for you.</li>
<li><strong>Loading huge files with <code>.read()</code></strong> instead of iterating line by line.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Always use <code>with open(...) as f:</code> — it closes the file automatically, even on error.</li>
<li>Modes: <code>r</code> read, <code>w</code> write (erases!), <code>a</code> append, <code>x</code> create.</li>
<li>Iterate <code>for line in f:</code> for large files; <code>.read()</code> for small whole-file reads.</li>
<li>Use <code>encoding="utf-8"</code> for text; you add <code>\n</code> yourself when writing.</li>
</ul>
EOT
      . vid_box('Reading/writing files and the with statement (context managers).', 'python file handling with open context manager tutorial'),
    ],

    [
      'title' => 'Paths the right way with pathlib',
      'minutes' => 14,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Stop building paths with string concatenation</h2>
<p>Joining paths by hand (<code>folder + "/" + name</code>) breaks across operating systems (Windows
uses <code>\</code>, others use <code>/</code>) and is error-prone. The modern standard library tool is
<strong><code>pathlib</code></strong>, which represents paths as objects with handy methods.</p>
<pre><code>from pathlib import Path

# Join paths with the / operator — cross-platform and clean:
data_dir = Path("data")
file_path = data_dir / "users" / "ada.txt"
print(file_path)        # data/users/ada.txt  (or data\users\ada.txt on Windows)</code></pre>

<h2>Reading and writing the easy way</h2>
<p><code>Path</code> objects have shortcut methods for small files:</p>
<pre><code>from pathlib import Path

p = Path("notes.txt")
p.write_text("Hello, file!", encoding="utf-8")   # write a whole string
content = p.read_text(encoding="utf-8")          # read it all back

# For bigger files you still use open() — Path works there too:
with p.open() as f:
    for line in f:
        ...</code></pre>

<h2>Inspecting paths</h2>
<pre><code>p = Path("/home/ada/report.csv")
p.name        # 'report.csv'   (file name)
p.stem        # 'report'       (name without extension)
p.suffix      # '.csv'         (extension)
p.parent      # Path('/home/ada')   (containing folder)
p.exists()    # True/False
p.is_file()   # True/False
p.is_dir()    # True/False</code></pre>

<h2>Creating folders and listing contents</h2>
<pre><code>out = Path("output/reports")
out.mkdir(parents=True, exist_ok=True)   # make folders; no error if they exist

# List files matching a pattern:
for csv_file in Path("data").glob("*.csv"):
    print(csv_file.name)

# Recursively, through subfolders:
for py_file in Path(".").rglob("*.py"):
    print(py_file)</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why pathlib over the old <code>os.path</code>?</strong> <code>os.path</code> functions
(<code>os.path.join</code>, <code>os.path.exists</code>) still work, but <code>pathlib</code>'s object
approach is more readable: the <code>/</code> operator for joining, and methods like
<code>.exists()</code> right on the path. It's the recommended modern style.
</div>

<h2>Current location and the script's own folder</h2>
<pre><code>Path.cwd()              # the current working directory (where you ran the program)
Path(__file__).parent   # the folder THIS script lives in — useful for finding
                        # data files relative to the code, not to where it was run</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Hardcoding separators</strong> (<code>"data\\file.txt"</code>) that break on other OSes — use
<code>Path("data") / "file.txt"</code>.</li>
<li><strong>Assuming relative paths point to the script.</strong> They're relative to the <em>current
working directory</em>; use <code>Path(__file__).parent</code> for files next to your code.</li>
<li><strong>Forgetting <code>exist_ok=True</code></strong> on <code>mkdir</code> and crashing when the folder
already exists.</li>
<li><strong>Mixing <code>Path</code> objects and strings</strong> carelessly — convert with <code>str(p)</code>
when an API needs a string (most modern ones accept <code>Path</code> directly).</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Use <code>pathlib.Path</code> and the <code>/</code> operator to build cross-platform paths.</li>
<li><code>read_text</code>/<code>write_text</code> handle small files in one line.</li>
<li>Inspect with <code>.name</code>, <code>.stem</code>, <code>.suffix</code>, <code>.parent</code>,
<code>.exists()</code>; list with <code>.glob()</code>/<code>.rglob()</code>.</li>
<li><code>mkdir(parents=True, exist_ok=True)</code> creates folders safely.</li>
</ul>
EOT
      . vid_box('Modern file paths with pathlib in Python.', 'python pathlib tutorial'),
    ],

    [
      'title' => 'Working with JSON and CSV',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>JSON: structured data as text</h2>
<p><strong>JSON</strong> (JavaScript Object Notation) is the universal format for structured data — APIs,
config files, and data exchange all use it. The good news: JSON maps almost perfectly onto Python's
dicts and lists. The <code>json</code> module converts between them.</p>
<pre><code>import json

data = {
    "name": "Ada",
    "age": 36,
    "languages": ["Python", "C"],
    "active": True,
}

# Python object → JSON string (dumps = "dump string")
text = json.dumps(data, indent=2)
print(text)

# JSON string → Python object (loads = "load string")
back = json.loads(text)
print(back["languages"][0])    # Python</code></pre>

<h2>JSON files</h2>
<pre><code>import json

# Write a Python object to a .json file:
with open("user.json", "w", encoding="utf-8") as f:
    json.dump(data, f, indent=2)          # note: dump, not dumps (writes to file)

# Read it back:
with open("user.json", encoding="utf-8") as f:
    loaded = json.load(f)                  # load, not loads (reads from file)</code></pre>

<div class="alert alert-info" role="alert">
<strong>The naming trick:</strong> the functions with an <strong>s</strong> work on <strong>strings</strong>
(<code>dumps</code>/<code>loads</code>); the ones <em>without</em> work on <strong>files</strong>
(<code>dump</code>/<code>load</code>). Remember "s = string".
</div>

<h2>Type mapping between JSON and Python</h2>
<table>
<thead><tr><th>JSON</th><th>Python</th></tr></thead>
<tbody>
<tr><td>object <code>{ }</code></td><td><code>dict</code></td></tr>
<tr><td>array <code>[ ]</code></td><td><code>list</code></td></tr>
<tr><td>string</td><td><code>str</code></td></tr>
<tr><td>number</td><td><code>int</code> / <code>float</code></td></tr>
<tr><td>true / false</td><td><code>True</code> / <code>False</code></td></tr>
<tr><td>null</td><td><code>None</code></td></tr>
</tbody>
</table>

<h2>CSV: tabular data (spreadsheets)</h2>
<p><strong>CSV</strong> (Comma-Separated Values) is the format for rows and columns — exports from
Excel/Google Sheets, datasets, reports. Use the <code>csv</code> module (don't split on commas
yourself — real CSV has quoting rules you'll get wrong).</p>
<pre><code>import csv

# Writing rows:
with open("people.csv", "w", newline="", encoding="utf-8") as f:
    writer = csv.writer(f)
    writer.writerow(["name", "age", "city"])   # header
    writer.writerow(["Ada", 36, "London"])
    writer.writerow(["Alan", 41, "Manchester"])</code></pre>

<h2>Reading CSV as dictionaries (the nice way)</h2>
<p><code>DictReader</code> uses the header row as keys, so you access columns by name:</p>
<pre><code>import csv

with open("people.csv", newline="", encoding="utf-8") as f:
    reader = csv.DictReader(f)
    for row in reader:
        print(row["name"], "is", row["age"])
        # row is a dict: {'name': 'Ada', 'age': '36', 'city': 'London'}</code></pre>
<p><strong>Note:</strong> every CSV value is read as a <em>string</em> — convert numbers yourself with
<code>int(row["age"])</code>.</p>
<pre><code># Writing dicts with DictWriter:
with open("out.csv", "w", newline="", encoding="utf-8") as f:
    writer = csv.DictWriter(f, fieldnames=["name", "age"])
    writer.writeheader()
    writer.writerow({"name": "Grace", "age": 45})</code></pre>

<h2>When to use which</h2>
<ul>
<li><strong>JSON</strong> — nested/structured data, configs, talking to web APIs.</li>
<li><strong>CSV</strong> — flat tables of rows and columns, spreadsheet interop.</li>
</ul>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Mixing up <code>dump</code>/<code>dumps</code> (and <code>load</code>/<code>loads</code>).</strong>
"s = string", no-s = file.</li>
<li><strong>Forgetting <code>newline=""</code></strong> when opening CSV files — on Windows you get blank
rows between every line.</li>
<li><strong>Forgetting CSV values are strings</strong> — convert numeric columns with <code>int()</code>/
<code>float()</code>.</li>
<li><strong>Splitting CSV on commas manually</strong> — breaks on quoted fields containing commas. Use
the <code>csv</code> module.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>json</code> converts between Python dicts/lists and JSON; <code>dump(s)</code> writes,
<code>load(s)</code> reads ("s = string").</li>
<li>JSON maps cleanly to Python types (object→dict, array→list, null→None).</li>
<li>Use the <code>csv</code> module for tables; <code>DictReader</code>/<code>DictWriter</code> work by column
name.</li>
<li>Open CSV with <code>newline=""</code>; remember CSV values come back as strings.</li>
</ul>
EOT
      . vid_box('Reading and writing JSON and CSV files in Python.', 'python json and csv files tutorial corey schafer'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 9 Quiz: File Handling',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'Why is "with open(...) as f:" preferred over open()/close()?',
        'explain' => 'The with statement (a context manager) automatically closes the file when the block ends, even if an exception is raised inside it.',
        'options' => [
          ['It closes the file automatically, even if an error occurs', true],
          ['It makes the file read faster', false],
          ['It is the only way to open files in Python 3', false],
          ['It encrypts the file', false],
        ],
      ],
      [
        'q' => 'What happens when you open an existing file in "w" mode?',
        'explain' => '"w" truncates the file immediately — its existing contents are erased. Use "a" to append without losing data.',
        'options' => [
          ['Its existing contents are erased (truncated)', true],
          ['New text is added to the end', false],
          ['Python raises an error', false],
          ['Nothing changes until you write', false],
        ],
      ],
      [
        'q' => 'Which is the recommended modern way to join filesystem paths?',
        'explain' => 'pathlib.Path with the / operator builds cross-platform paths cleanly, e.g. Path("data") / "file.txt".',
        'options' => [
          ['Path("data") / "file.txt" using pathlib', true],
          ['"data" + "/" + "file.txt"', false],
          ['"data\\\\file.txt" hardcoded', false],
          ['concat(data, file)', false],
        ],
      ],
      [
        'q' => 'In the json module, what does json.dump(obj, f) do?',
        'explain' => 'dump (no s) writes a Python object as JSON to a file object f. dumps (with s) returns a JSON string instead.',
        'options' => [
          ['Writes the Python object as JSON to the file f', true],
          ['Returns a JSON string (writes nothing)', false],
          ['Reads JSON from a file', false],
          ['Deletes the file', false],
        ],
      ],
      [
        'q' => 'When reading a CSV with csv.DictReader, the value row["age"] is...',
        'explain' => 'All CSV values are read as strings. You must convert numeric columns yourself, e.g. int(row["age"]).',
        'options' => [
          ['A string, which you must convert to int/float yourself', true],
          ['Automatically an integer', false],
          ['Automatically the correct type', false],
          ['None unless the column is empty', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 10 — MODULES, PACKAGES, VIRTUAL ENVIRONMENTS & pip
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Modules, Packages, venv & pip',
  'description' => 'Organise code across files, use the standard library, and manage third-party packages the professional way: isolated virtual environments, pip, requirements.txt, and a sensible project structure.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Modules, imports & the standard library',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Splitting code across files</h2>
<p>As programs grow you split them into multiple <code>.py</code> files. Each file is a
<strong>module</strong>, and you bring code from one into another with <code>import</code>. This keeps
each file focused (hello, SRP) and lets you reuse code.</p>
<pre><code># file: math_utils.py
def add(a, b):
    return a + b
PI = 3.14159

# file: main.py
import math_utils
print(math_utils.add(2, 3))   # 5
print(math_utils.PI)          # 3.14159</code></pre>

<h2>Forms of import</h2>
<pre><code>import math                    # use as math.sqrt(...)
import math as m               # alias: m.sqrt(...)
from math import sqrt, pi      # bring names directly: sqrt(16)
from math import sqrt as root  # rename on import</code></pre>
<div class="alert alert-warning" role="alert">
<strong>Avoid <code>from module import *</code>.</strong> It dumps every name into your file, hiding
where things came from and risking name clashes. Import what you need explicitly — it's clearer and
safer.
</div>

<h2>The standard library: "batteries included"</h2>
<p>Python ships with a huge standard library — hundreds of modules ready to import, no install needed.
A starter tour you'll use constantly:</p>
<table>
<thead><tr><th>Module</th><th>For</th></tr></thead>
<tbody>
<tr><td><code>math</code></td><td><code>sqrt</code>, <code>ceil</code>, <code>floor</code>, <code>pi</code></td></tr>
<tr><td><code>random</code></td><td><code>random()</code>, <code>randint()</code>, <code>choice()</code>, <code>shuffle()</code></td></tr>
<tr><td><code>datetime</code></td><td>dates and times, durations</td></tr>
<tr><td><code>pathlib</code></td><td>filesystem paths (Module 9)</td></tr>
<tr><td><code>json</code> / <code>csv</code></td><td>data formats (Module 9)</td></tr>
<tr><td><code>collections</code></td><td><code>Counter</code>, <code>defaultdict</code>, <code>namedtuple</code></td></tr>
<tr><td><code>os</code> / <code>sys</code></td><td>operating system, arguments, environment</td></tr>
<tr><td><code>re</code></td><td>regular expressions (pattern matching)</td></tr>
</tbody>
</table>
<pre><code>import random
random.randint(1, 6)            # roll a die
random.choice(["a", "b", "c"])  # pick one

from collections import Counter
Counter("mississippi")          # Counter({'s':4, 'i':4, 'p':2, 'm':1})

from datetime import datetime
datetime.now()                  # current date and time</code></pre>

<h2>if __name__ == "__main__"</h2>
<p>When you import a module, Python <em>runs</em> its top-level code. To have code that runs only when
the file is executed directly (not when imported), guard it:</p>
<pre><code># greeter.py
def greet(name):
    return f"Hello, {name}!"

if __name__ == "__main__":
    # runs only with `python greeter.py`, NOT on `import greeter`
    print(greet("World"))</code></pre>
<p>Python sets the special variable <code>__name__</code> to <code>"__main__"</code> in the file you run
directly, and to the module's name when imported. This lets a file be both an importable library and
a runnable script.</p>

<div class="alert alert-info" role="alert">
<strong>Why this matters.</strong> Without the guard, importing your module to reuse one function would
also trigger all its demo/print code. The <code>if __name__ == "__main__":</code> block is the
conventional "start here when run directly" entry point.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Circular imports</strong> — module A imports B which imports A. Restructure so dependencies
flow one way.</li>
<li><strong>Naming your file after a stdlib module</strong> (e.g. <code>random.py</code>) — your file
shadows the real one and imports break.</li>
<li><strong><code>import *</code></strong> polluting the namespace.</li>
<li><strong>Top-level side effects</strong> (prints, network calls) that fire on import — guard them.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Each <code>.py</code> file is a <strong>module</strong>; <code>import</code> reuses its code.</li>
<li>Prefer explicit imports (<code>from math import sqrt</code>) over <code>import *</code>.</li>
<li>The <strong>standard library</strong> covers huge ground — reach for it before writing your own.</li>
<li>Use <code>if __name__ == "__main__":</code> for code that should run only when the file is executed
directly.</li>
</ul>
EOT
      . vid_box('Modules, imports, the standard library, and if __name__ == "__main__".', 'python modules imports name main tutorial'),
    ],

    [
      'title' => 'Virtual environments and pip',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>The problem: project dependencies collide</h2>
<p>Real projects use third-party packages (requests, Django, pandas...). If you install everything
into one system-wide Python, two projects that need <em>different versions</em> of the same package
will conflict, and you can break your system Python. The professional solution is a
<strong>virtual environment</strong>: a private, isolated Python for each project.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> A virtual environment is like a separate kitchen for each recipe. Project A's
kitchen has flour v1; Project B's has flour v2. They never mix, and cleaning up one (delete the
folder) never affects the other. The system Python stays pristine.
</div>

<h2>Creating and activating a venv</h2>
<p><code>venv</code> is built into Python — no install needed. From your project folder:</p>
<pre><code># 1. Create a virtual environment in a folder called .venv
python3 -m venv .venv

# 2. Activate it:
#    macOS / Linux:
source .venv/bin/activate
#    Windows (PowerShell):
.venv\Scripts\Activate.ps1

# Your prompt now shows (.venv) — you're inside the isolated environment.

# 3. When done:
deactivate</code></pre>
<p>While activated, <code>python</code> and <code>pip</code> refer to the project's private copies.
Anything you install goes into <code>.venv</code> only.</p>

<h2>pip: installing packages</h2>
<p><strong>pip</strong> is Python's package installer; it downloads from <strong>PyPI</strong> (the Python
Package Index, pypi.org).</p>
<pre><code>pip install requests              # install latest
pip install "django==5.0.1"       # a specific version
pip install --upgrade requests    # upgrade
pip uninstall requests            # remove
pip list                          # what's installed
pip show requests                 # details about a package</code></pre>
<pre><code>import requests
r = requests.get("https://api.github.com")
print(r.status_code)              # 200</code></pre>

<h2>requirements.txt: reproducible installs</h2>
<p>To let anyone (a teammate, a server, future-you) recreate the exact same environment, record your
dependencies in a <code>requirements.txt</code> file:</p>
<pre><code># Save the current environment's packages + versions:
pip freeze &gt; requirements.txt

# The file looks like:
#   requests==2.31.0
#   python-dotenv==1.0.1

# Recreate it elsewhere (new machine / server / teammate):
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt</code></pre>

<div class="alert alert-warning" role="alert">
<strong>Never commit <code>.venv</code> to Git.</strong> It's large, machine-specific, and recreatable.
Add it to <code>.gitignore</code>. You commit <code>requirements.txt</code> (the recipe), not the
installed packages (the cooked meal).
</div>

<h2>The standard project workflow</h2>
<pre><code>mkdir myproject &amp;&amp; cd myproject
python3 -m venv .venv
source .venv/bin/activate
pip install requests
pip freeze &gt; requirements.txt
# ...write code...
deactivate</code></pre>
<p>Do this for every project. It's the habit that keeps your machine clean and your projects
shareable. (Modern tools like <em>uv</em> and <em>Poetry</em> streamline this further, but
<code>venv</code> + <code>pip</code> is the universal foundation to learn first.)</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Installing globally with <code>sudo pip</code></strong> — pollutes/breaks system Python. Always
use a venv.</li>
<li><strong>Forgetting to activate</strong> the venv, then wondering why imports fail or land in the
wrong place.</li>
<li><strong>Committing <code>.venv</code></strong> instead of <code>requirements.txt</code>.</li>
<li><strong>Not pinning versions</strong>, so a fresh install pulls a newer, incompatible release later.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>A <strong>virtual environment</strong> gives each project an isolated Python + packages.</li>
<li>Create with <code>python3 -m venv .venv</code>, then <strong>activate</strong> it; <code>deactivate</code>
when done.</li>
<li><strong>pip</strong> installs packages from PyPI; pin versions for stability.</li>
<li>Share environments via <code>pip freeze &gt; requirements.txt</code> and
<code>pip install -r requirements.txt</code>; never commit <code>.venv</code>.</li>
</ul>
EOT
      . vid_box('Virtual environments (venv), pip, and requirements.txt — the pro workflow.', 'python virtual environment venv pip requirements tutorial'),
    ],

    [
      'title' => 'Packages and project structure',
      'minutes' => 14,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>From modules to packages</h2>
<p>A <strong>package</strong> is a folder of modules — a way to group related modules under a namespace.
Historically a package needed an <code>__init__.py</code> file; it's optional now but still common and
useful to mark a folder as a package and run setup code.</p>
<pre><code>myapp/
├── __init__.py          # marks myapp as a package
├── models.py
├── services.py
└── utils/
    ├── __init__.py
    └── formatting.py</code></pre>
<pre><code># Import across the package:
from myapp.services import create_user
from myapp.utils.formatting import to_title_case</code></pre>

<h2>A sensible project layout</h2>
<p>A typical small-to-medium Python project looks like this. You don't need every piece on day one,
but knowing the shape helps you read real projects:</p>
<pre><code>myproject/
├── .venv/                 # virtual environment (gitignored)
├── .gitignore
├── README.md             # what it is, how to run it
├── requirements.txt      # dependencies
├── src/
│   └── myapp/            # your package (the actual code)
│       ├── __init__.py
│       └── main.py
└── tests/                # automated tests (next module)
    └── test_main.py</code></pre>

<h2>Absolute vs relative imports</h2>
<pre><code># Absolute (clear, recommended) — full path from the package root:
from myapp.utils.formatting import to_title_case

# Relative (within the same package) — the dots mean "current/parent package":
from .utils.formatting import to_title_case      # . = current package
from ..models import User                          # .. = parent package</code></pre>
<p>Prefer <strong>absolute imports</strong> for clarity; relative imports are handy inside a package but
can confuse beginners.</p>

<h2>The README and .gitignore</h2>
<ul>
<li><strong>README.md</strong> — the front door: what the project does, how to set it up and run it.
The first thing anyone (including future-you) reads.</li>
<li><strong>.gitignore</strong> — tells Git what <em>not</em> to track: <code>.venv/</code>,
<code>__pycache__/</code>, <code>*.pyc</code>, secrets/<code>.env</code>, editor files.</li>
</ul>
<pre><code># .gitignore essentials for Python:
.venv/
__pycache__/
*.pyc
.env</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why structure matters.</strong> A predictable layout means anyone can clone your project,
read the README, create a venv, <code>pip install -r requirements.txt</code>, and run it — in minutes.
Good structure is a courtesy to every future reader, and a sign of a professional codebase.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>One giant <code>main.py</code></strong> with everything in it — split into modules/packages by
responsibility.</li>
<li><strong>Committing secrets</strong> (API keys in code) — keep them in <code>.env</code> (gitignored)
and load them at runtime.</li>
<li><strong>No README</strong> — others (and you in six months) can't tell how to run it.</li>
<li><strong>Tracking <code>__pycache__</code>/<code>.venv</code></strong> in Git — noise; gitignore them.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>A <strong>package</strong> is a folder of modules (often with <code>__init__.py</code>).</li>
<li>Use a predictable layout: <code>src/</code> package, <code>tests/</code>, <code>README.md</code>,
<code>requirements.txt</code>, <code>.gitignore</code>.</li>
<li>Prefer <strong>absolute imports</strong>; keep secrets out of Git.</li>
<li>A good README + structure makes a project easy for anyone to set up and run.</li>
</ul>
EOT
      . vid_box('Structuring a Python project: packages, __init__.py, src layout, and imports.', 'python project structure packages tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 10 Quiz: Modules, venv & pip',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What does if __name__ == "__main__": achieve?',
        'explain' => 'Code inside runs only when the file is executed directly, not when it is imported as a module. __name__ is "__main__" only for the directly-run file.',
        'options' => [
          ['Code inside runs only when the file is run directly, not when imported', true],
          ['It defines the main() function automatically', false],
          ['It is required at the top of every Python file', false],
          ['It imports the main module', false],
        ],
      ],
      [
        'q' => 'What is the main purpose of a virtual environment?',
        'explain' => 'It isolates a project\'s Python interpreter and packages so different projects (and the system Python) do not conflict.',
        'options' => [
          ['To isolate each project\'s packages so they don\'t conflict', true],
          ['To make Python run faster', false],
          ['To compile Python into an executable', false],
          ['To connect to a database', false],
        ],
      ],
      [
        'q' => 'Which command records the current environment\'s exact packages for sharing?',
        'explain' => 'pip freeze > requirements.txt writes installed packages and versions to a file others can install with pip install -r requirements.txt.',
        'options' => [
          ['pip freeze > requirements.txt', true],
          ['pip list --save', false],
          ['python -m venv requirements.txt', false],
          ['pip export', false],
        ],
      ],
      [
        'q' => 'What should you commit to Git: the .venv folder or requirements.txt?',
        'explain' => 'Commit requirements.txt (the recipe). The .venv folder is large, machine-specific, and recreatable, so it is gitignored.',
        'options' => [
          ['requirements.txt — not the .venv folder', true],
          ['The .venv folder — not requirements.txt', false],
          ['Both', false],
          ['Neither', false],
        ],
      ],
      [
        'q' => 'Why avoid "from module import *"?',
        'explain' => 'It imports every name into your namespace, hiding where names came from and risking clashes. Explicit imports are clearer and safer.',
        'options' => [
          ['It pollutes the namespace and hides where names came from', true],
          ['It is slower to type', false],
          ['It only works in packages', false],
          ['It deletes the module', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 11 — WRITING TESTABLE CODE
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Writing Clean, Testable Code',
  'description' => 'Tests are how professionals change code without fear. Why testing matters, the assert basics, writing tests with unittest and the popular pytest, test-driven development, designing code for testability, and debugging.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Why test? assert and your first tests',
      'minutes' => 16,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Why automated tests?</h2>
<p>Manually re-checking your program after every change is slow and unreliable. <strong>Automated
tests</strong> are code that checks your code: run them in seconds and instantly know whether anything
broke. They are what let you refactor and add features <em>without fear</em> — the safety net of
professional development.</p>
<ul>
<li><strong>Catch regressions:</strong> a change that breaks old behaviour fails a test immediately.</li>
<li><strong>Document behaviour:</strong> a test shows exactly how a function is meant to be used.</li>
<li><strong>Enable refactoring:</strong> rewrite internals freely; passing tests prove behaviour held.</li>
<li><strong>Design pressure:</strong> hard-to-test code is usually badly-designed code.</li>
</ul>

<h2>The simplest test: assert</h2>
<p><code>assert</code> checks that a condition is true. If it is, nothing happens; if not, it raises
<code>AssertionError</code>. It's the atom of all testing:</p>
<pre><code>def add(a, b):
    return a + b

assert add(2, 3) == 5
assert add(-1, 1) == 0
assert add(0, 0) == 0
print("All checks passed!")     # only prints if every assert held</code></pre>

<h2>Anatomy of a good test: Arrange–Act–Assert</h2>
<p>Most tests follow three clear steps:</p>
<pre><code>def test_withdraw_reduces_balance():
    account = Account(100)        # ARRANGE: set up the scenario
    account.withdraw(30)          # ACT:     do the thing under test
    assert account.balance == 70  # ASSERT:  check the outcome</code></pre>

<h2>Test the edges, not just the happy path</h2>
<p>Bugs hide at the boundaries. For each function, think about:</p>
<ul>
<li><strong>Typical input</strong> (the happy path).</li>
<li><strong>Edge cases:</strong> empty input, zero, negatives, very large values, the first/last item.</li>
<li><strong>Error cases:</strong> invalid input that should raise — assert it raises.</li>
</ul>
<pre><code>def divide(a, b):
    if b == 0:
        raise ValueError("cannot divide by zero")
    return a / b

# happy path:
assert divide(10, 2) == 5
# edge case:
assert divide(0, 5) == 0
# error case — we expect an exception:
try:
    divide(1, 0)
    assert False, "should have raised"
except ValueError:
    pass    # correct — it raised as designed</code></pre>

<div class="alert alert-info" role="alert">
<strong>What makes code testable?</strong> Pure functions — ones that take inputs and return outputs
with no hidden state or side effects — are the easiest to test (recall "pass in, return out" from
the functions module, and dependency injection from SOLID). If a function is hard to test, that's a
hint to simplify its design.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Only testing the happy path</strong> and missing edge/error cases where bugs live.</li>
<li><strong>Relying on <code>assert</code> for production validation.</strong> Asserts can be stripped with
<code>python -O</code>; use them for tests/debugging, and raise real exceptions to validate input.</li>
<li><strong>Tests that depend on each other</strong> or on external state — each test should stand alone.</li>
<li><strong>Not testing at all</strong> until the end. Test as you build.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Automated tests catch regressions, document behaviour, and enable fearless refactoring.</li>
<li><code>assert condition</code> is the basic check; failing raises <code>AssertionError</code>.</li>
<li>Structure tests as <strong>Arrange–Act–Assert</strong>; cover happy, edge, and error cases.</li>
<li>Pure, well-designed functions are the easiest to test.</li>
</ul>
EOT
      . vid_box('Why testing matters and writing your first assert-based tests.', 'python testing for beginners why test'),
    ],

    [
      'title' => 'unittest and pytest',
      'minutes' => 20,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>From asserts to a test framework</h2>
<p>Bare <code>assert</code> scripts work, but frameworks give you test discovery, clear failure reports,
setup/teardown, and tools for checking exceptions. Python has a built-in one (<code>unittest</code>)
and a hugely popular third-party one (<code>pytest</code>).</p>

<h2>unittest (built-in)</h2>
<p>Subclass <code>unittest.TestCase</code>; each method starting with <code>test_</code> is a test. Use
the <code>self.assert*</code> methods:</p>
<pre><code># test_math.py
import unittest
from mymath import add, divide

class TestMath(unittest.TestCase):
    def test_add(self):
        self.assertEqual(add(2, 3), 5)

    def test_divide(self):
        self.assertEqual(divide(10, 2), 5)

    def test_divide_by_zero_raises(self):
        with self.assertRaises(ValueError):
            divide(1, 0)

if __name__ == "__main__":
    unittest.main()</code></pre>
<pre><code>python -m unittest test_math.py</code></pre>

<h2>pytest (the popular choice)</h2>
<p><code>pytest</code> needs less boilerplate: plain functions named <code>test_*</code> and plain
<code>assert</code> — it rewrites asserts to give rich failure messages.</p>
<pre><code># test_math.py
import pytest
from mymath import add, divide

def test_add():
    assert add(2, 3) == 5

def test_divide():
    assert divide(10, 2) == 5

def test_divide_by_zero_raises():
    with pytest.raises(ValueError):
        divide(1, 0)</code></pre>
<pre><code>pip install pytest
pytest                 # auto-discovers test_*.py files and test_* functions
pytest -v              # verbose: list each test
pytest test_math.py    # run one file</code></pre>

<div class="alert alert-info" role="alert">
<strong>unittest or pytest?</strong> <code>pytest</code> is the de-facto standard in modern Python:
less ceremony, plain <code>assert</code>, better output, and a rich plugin ecosystem. Learn
<code>unittest</code> enough to read it (you'll meet it in older codebases), but reach for
<code>pytest</code> in your own projects.
</div>

<h2>Helpful pytest features</h2>
<h3>Fixtures — reusable setup</h3>
<pre><code>import pytest

@pytest.fixture
def account():
    return Account(100)        # fresh account for each test that asks for it

def test_deposit(account):     # pytest injects the fixture by name
    account.deposit(50)
    assert account.balance == 150

def test_withdraw(account):    # gets its OWN fresh account
    account.withdraw(40)
    assert account.balance == 60</code></pre>

<h3>Parametrize — many cases, one test</h3>
<pre><code>@pytest.mark.parametrize("a, b, expected", [
    (2, 3, 5),
    (-1, 1, 0),
    (0, 0, 0),
])
def test_add(a, b, expected):
    assert add(a, b) == expected   # runs three times, one per row</code></pre>

<h2>Where tests live</h2>
<p>Put tests in a <code>tests/</code> folder, files named <code>test_*.py</code>, functions named
<code>test_*</code>. Keep tests next to the project (Module 10's layout) so <code>pytest</code> finds them
automatically.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Test functions not named <code>test_*</code></strong> — pytest won't discover them.</li>
<li><strong>Asserting exceptions with plain <code>try/except</code></strong> in tests instead of
<code>pytest.raises</code>/<code>assertRaises</code>.</li>
<li><strong>Shared mutable state between tests</strong> — use fixtures so each test is independent.</li>
<li><strong>Testing implementation details</strong> instead of observable behaviour — brittle tests that
break on every refactor.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>unittest</code> (built-in) uses <code>TestCase</code> classes and <code>self.assert*</code>.</li>
<li><code>pytest</code> uses plain <code>test_*</code> functions and plain <code>assert</code> — prefer it.</li>
<li>Check exceptions with <code>pytest.raises(...)</code> / <code>self.assertRaises(...)</code>.</li>
<li>Use <strong>fixtures</strong> for fresh setup and <strong>parametrize</strong> for many cases; keep tests
independent in <code>tests/</code>.</li>
</ul>
EOT
      . vid_box('Writing tests with unittest and pytest, fixtures, and parametrize.', 'python pytest tutorial for beginners'),
    ],

    [
      'title' => 'TDD, testable design & debugging',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>Test-Driven Development (TDD)</h2>
<p><strong>TDD</strong> flips the usual order: you write the test <em>first</em>, watch it fail, then
write just enough code to pass. The cycle is <strong>Red → Green → Refactor</strong>:</p>
<pre><code>1. RED      — write a failing test for the behaviour you want
2. GREEN    — write the simplest code that makes it pass
3. REFACTOR — clean up the code, tests still green
   ↺ repeat for the next small behaviour
</code></pre>
<pre><code># 1. RED: write the test first (function doesn't exist yet)
def test_is_palindrome():
    assert is_palindrome("racecar") is True
    assert is_palindrome("hello") is False

# 2. GREEN: simplest code that passes
def is_palindrome(s):
    return s == s[::-1]

# 3. REFACTOR: handle spaces/case, tests guide and protect you
def is_palindrome(s):
    cleaned = "".join(s.lower().split())
    return cleaned == cleaned[::-1]</code></pre>
<p>TDD keeps you focused on <em>behaviour</em>, gives you tests for free, and prevents
over-engineering (you only build what a test demands — hello, YAGNI).</p>

<h2>Designing for testability</h2>
<p>Everything in this course converges here. Code is testable when it's:</p>
<ul>
<li><strong>Pure where possible</strong> — inputs in, outputs out, no hidden global state (Functions
module).</li>
<li><strong>Decoupled via injection</strong> — pass dependencies in rather than hard-wiring them, so you
can substitute fakes (DIP, SOLID module).</li>
<li><strong>Single-responsibility</strong> — small focused units are easy to test in isolation (SRP).</li>
</ul>
<pre><code># Hard to test: reaches out to the real world directly
def report():
    data = requests.get("https://api/...").json()   # network!
    return summarise(data)

# Easy to test: the pure logic is separated from the I/O
def summarise(data):                 # pure — test this directly with sample data
    return {"count": len(data), "names": [d["name"] for d in data]}

def report(fetch):                   # inject the fetcher
    return summarise(fetch())        # pass a fake fetch() in tests</code></pre>

<h2>Debugging: a method, not magic</h2>
<p>When something's wrong, work systematically instead of randomly changing code:</p>
<ol>
<li><strong>Reproduce</strong> it reliably — find the smallest input that triggers it.</li>
<li><strong>Read the traceback</strong> — bottom line first (Errors module).</li>
<li><strong>Locate</strong> — inspect values where you think it breaks.</li>
<li><strong>Hypothesise &amp; test</strong> one change at a time.</li>
</ol>

<h3>print debugging (fine for quick checks)</h3>
<pre><code>print(f"{user=}")          # f-string '=' shows name AND value: user=...
print(f"{total=}, {items=}")</code></pre>

<h3>The real debugger: breakpoint()</h3>
<p>Drop <code>breakpoint()</code> on any line. Running the program pauses there and opens an interactive
prompt (pdb) where you can inspect variables and step through:</p>
<pre><code>def buggy(items):
    total = 0
    for x in items:
        breakpoint()       # execution pauses here
        total += x
    return total</code></pre>
<pre><code>pdb commands:  n (next line)   s (step into)   c (continue)
               p name (print)  l (list code)   q (quit)</code></pre>
<p>(Editors like VS Code give the same power with clickable breakpoints and a variables panel.)</p>

<div class="alert alert-info" role="alert">
<strong>Tests + debugging together.</strong> When you find a bug, first write a failing test that
reproduces it, then fix it. The test confirms the fix <em>and</em> stops the bug from ever returning.
This turns every bug into a permanent improvement.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Changing code randomly</strong> hoping something works — debug systematically instead.</li>
<li><strong>Mixing I/O and logic</strong>, making the logic impossible to test without the network/DB.</li>
<li><strong>Leaving <code>breakpoint()</code> / debug prints</strong> in committed code.</li>
<li><strong>Fixing a bug without adding a test</strong> — it can silently come back.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>TDD</strong>: Red → Green → Refactor — test first, then minimal code, then clean up.</li>
<li>Design for testability: <strong>pure functions</strong>, <strong>dependency injection</strong>, <strong>single
responsibility</strong> — separate logic from I/O.</li>
<li>Debug methodically; use f-string <code>=</code> prints and <code>breakpoint()</code>/pdb.</li>
<li>Turn each bug into a <strong>regression test</strong> so it never returns.</li>
</ul>
EOT
      . vid_box('Test-driven development, designing for testability, and debugging with pdb.', 'python tdd and debugging breakpoint pdb tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 11 Quiz: Testable Code',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What is the main benefit of having automated tests?',
        'explain' => 'Tests catch regressions instantly and let you refactor or add features with confidence that existing behaviour still works.',
        'options' => [
          ['They let you change code confidently and catch regressions automatically', true],
          ['They make the program run faster', false],
          ['They are required for Python to run', false],
          ['They replace the need for functions', false],
        ],
      ],
      [
        'q' => 'What are the three steps of the Arrange-Act-Assert pattern?',
        'explain' => 'Arrange the scenario/inputs, Act by running the code under test, then Assert the expected outcome.',
        'options' => [
          ['Set up the scenario, run the code, check the result', true],
          ['Import, compile, execute', false],
          ['Try, except, finally', false],
          ['Red, green, refactor', false],
        ],
      ],
      [
        'q' => 'In pytest, how do you assert that calling f() raises ValueError?',
        'explain' => 'Wrap the call in a with pytest.raises(ValueError): block. If it does not raise, the test fails.',
        'options' => [
          ['with pytest.raises(ValueError): f()', true],
          ['assert f() == ValueError', false],
          ['try: f() except: pass', false],
          ['pytest.assertError(f)', false],
        ],
      ],
      [
        'q' => 'What is the TDD cycle?',
        'explain' => 'Red (write a failing test), Green (write minimal code to pass), Refactor (clean up while keeping tests green).',
        'options' => [
          ['Red, Green, Refactor', true],
          ['Plan, Build, Ship', false],
          ['Arrange, Act, Assert', false],
          ['Import, Run, Debug', false],
        ],
      ],
      [
        'q' => 'Which design choice makes code easiest to test?',
        'explain' => 'Pure functions and injected dependencies (no hidden global state, I/O separated from logic) let you test units in isolation with simple inputs and fakes.',
        'options' => [
          ['Pure functions with dependencies injected, separating logic from I/O', true],
          ['Putting all logic and network calls in one big function', false],
          ['Using many global variables', false],
          ['Avoiding functions entirely', false],
        ],
      ],
      [
        'q' => 'What does breakpoint() do?',
        'explain' => 'It pauses execution at that line and drops into the interactive debugger (pdb) so you can inspect variables and step through the code.',
        'options' => [
          ['Pauses execution and opens the interactive debugger (pdb)', true],
          ['Stops the program permanently', false],
          ['Deletes the current variable', false],
          ['Runs all tests', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 12 — CAPSTONE PROJECT
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Capstone: Build a Real CLI App',
  'description' => 'Put every skill together by building a complete command-line Expense Tracker: structured project, OOP design, file persistence with JSON, error handling, a clean CLI, and a pytest test suite. This is the project that proves you can build software.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Project brief, setup & design',
      'minutes' => 18,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>What you'll build</h2>
<p>A command-line <strong>Expense Tracker</strong> — a small but complete app that lets a user add
expenses, list them, see totals by category, and persist everything to a JSON file between runs.
It's deliberately chosen to exercise <em>every</em> module of this course at once.</p>
<pre><code>$ python -m tracker add 12.50 food "Lunch"
Added: $12.50 — food — Lunch
$ python -m tracker add 40 transport "Train ticket"
Added: $40.00 — transport — Train ticket
$ python -m tracker list
#1  $12.50  food       Lunch
#2  $40.00  transport  Train ticket
$ python -m tracker summary
food:       $12.50
transport:  $40.00
TOTAL:      $52.50</code></pre>

<h2>Which skills it uses</h2>
<table>
<thead><tr><th>Module</th><th>Used for</th></tr></thead>
<tbody>
<tr><td>Data types &amp; structures</td><td>expenses as dicts/dataclasses in a list; totals via a dict</td></tr>
<tr><td>Functions</td><td>small, single-purpose functions with type hints</td></tr>
<tr><td>OOP</td><td>an <code>Expense</code> dataclass and an <code>ExpenseTracker</code> class (encapsulation)</td></tr>
<tr><td>Clean code &amp; SOLID</td><td>storage injected into the tracker (DIP); SRP separation</td></tr>
<tr><td>Errors</td><td>validating amounts/categories; handling a missing/corrupt data file</td></tr>
<tr><td>Files &amp; JSON</td><td>persisting expenses to <code>expenses.json</code></td></tr>
<tr><td>Modules/venv/pip</td><td>a real package layout, venv, pytest installed</td></tr>
<tr><td>Testing</td><td>a <code>pytest</code> suite for the core logic</td></tr>
</tbody>
</table>

<h2>Set up the project</h2>
<pre><code>mkdir expense-tracker &amp;&amp; cd expense-tracker
python3 -m venv .venv
source .venv/bin/activate        # Windows: .venv\Scripts\Activate.ps1
pip install pytest
pip freeze &gt; requirements.txt</code></pre>
<p>Target layout (Module 10):</p>
<pre><code>expense-tracker/
├── .venv/                 # gitignored
├── .gitignore
├── README.md
├── requirements.txt
├── tracker/
│   ├── __init__.py
│   ├── models.py         # the Expense dataclass
│   ├── storage.py        # save/load JSON  (the "detail")
│   ├── core.py           # ExpenseTracker logic  (the "policy")
│   └── __main__.py       # the CLI entry point
└── tests/
    └── test_core.py</code></pre>
<pre><code># .gitignore
.venv/
__pycache__/
*.pyc
expenses.json</code></pre>

<h2>Design first (think before typing)</h2>
<p>A little design saves a lot of rewriting. We'll separate three concerns (SRP), and make the core
logic depend on an abstraction for storage (DIP) so it's easy to test:</p>
<pre><code>           ┌──────────────┐
  CLI ───►  │ ExpenseTracker│  ◄── core business logic (add, list, summary)
 (__main__) │   (core.py)   │
           └──────┬───────┘
                  │ uses a Storage abstraction (inject real or fake)
                  ▼
           ┌──────────────┐
           │ JsonStorage   │  ◄── reads/writes expenses.json (a detail)
           │  (storage.py) │
           └──────────────┘
   Expense dataclass (models.py) is the data passed around.
</code></pre>

<div class="alert alert-info" role="alert">
<strong>Why this shape?</strong> The CLI only talks to <code>ExpenseTracker</code>. The tracker doesn't
know <em>how</em> data is stored — it just calls a storage object's <code>load()</code>/<code>save()</code>.
That means in tests we pass a fake in-memory storage (no files needed), and we could swap JSON for a
database later without touching the core logic. Every principle from the course, working together.
</div>

<h2>Your tasks in this module</h2>
<ol>
<li>This lesson: set up the project and understand the design.</li>
<li>Next lesson: implement <code>models.py</code>, <code>storage.py</code>, and <code>core.py</code>.</li>
<li>Final lesson: build the CLI, write tests, and polish.</li>
</ol>

<h2>✅ Key takeaways</h2>
<ul>
<li>The capstone is a complete CLI <strong>Expense Tracker</strong> exercising the whole course.</li>
<li>Start with a proper <strong>venv + package layout + .gitignore</strong>.</li>
<li>Design by <strong>separating concerns</strong> (models / storage / core / CLI) and depending on a
storage <strong>abstraction</strong>.</li>
<li>Good design up front makes the code easy to build <em>and</em> test.</li>
</ul>
EOT
      . vid_box('Planning and structuring a Python CLI application project.', 'python cli project structure planning tutorial'),
    ],

    [
      'title' => 'Implementing the core: models, storage, logic',
      'minutes' => 22,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>models.py — the Expense dataclass</h2>
<p>An expense is a record of values, so a <code>@dataclass</code> is perfect (Module 6). We add helpers
to convert to/from plain dicts for JSON.</p>
<pre><code># tracker/models.py
from dataclasses import dataclass, asdict

@dataclass
class Expense:
    amount: float
    category: str
    note: str = ""

    def to_dict(self) -&gt; dict:
        return asdict(self)

    @staticmethod
    def from_dict(data: dict) -&gt; "Expense":
        return Expense(
            amount=float(data["amount"]),
            category=data["category"],
            note=data.get("note", ""),
        )</code></pre>

<h2>storage.py — persistence behind an abstraction</h2>
<p>We define an abstract <code>Storage</code> contract (Module 6/7) and a concrete JSON implementation.
The core logic will depend on the abstraction, not on JSON specifically.</p>
<pre><code># tracker/storage.py
import json
from abc import ABC, abstractmethod
from pathlib import Path
from .models import Expense

class Storage(ABC):
    @abstractmethod
    def load(self) -&gt; list[Expense]: ...
    @abstractmethod
    def save(self, expenses: list[Expense]) -&gt; None: ...

class JsonStorage(Storage):
    def __init__(self, path: str = "expenses.json"):
        self.path = Path(path)

    def load(self) -&gt; list[Expense]:
        if not self.path.exists():
            return []                      # first run — no file yet
        try:
            raw = json.loads(self.path.read_text(encoding="utf-8"))
        except json.JSONDecodeError:
            raise ValueError(f"{self.path} is corrupted")
        return [Expense.from_dict(item) for item in raw]

    def save(self, expenses: list[Expense]) -&gt; None:
        data = [e.to_dict() for e in expenses]
        self.path.write_text(json.dumps(data, indent=2), encoding="utf-8")</code></pre>
<p>Notice the error handling (Module 8): a missing file is a normal first run (return empty); a
corrupt file is a real error we surface clearly.</p>

<h2>core.py — the ExpenseTracker logic</h2>
<p>This is the heart of the app. It encapsulates the list of expenses (Module 6), validates input
(Module 8), and receives its storage via injection (DIP, Module 7) so it's trivial to test.</p>
<pre><code># tracker/core.py
from collections import defaultdict
from .models import Expense
from .storage import Storage

class ExpenseTracker:
    def __init__(self, storage: Storage):
        self._storage = storage               # injected dependency (DIP)
        self._expenses = storage.load()       # load existing data

    def add(self, amount: float, category: str, note: str = "") -&gt; Expense:
        if amount &lt;= 0:
            raise ValueError("amount must be positive")
        if not category.strip():
            raise ValueError("category is required")
        expense = Expense(amount=amount, category=category.strip(), note=note)
        self._expenses.append(expense)
        self._storage.save(self._expenses)    # persist after every change
        return expense

    def list_all(self) -&gt; list[Expense]:
        return list(self._expenses)            # return a copy (encapsulation)

    def summary(self) -&gt; dict[str, float]:
        totals: dict[str, float] = defaultdict(float)
        for e in self._expenses:
            totals[e.category] += e.amount
        return dict(totals)

    def total(self) -&gt; float:
        return sum(e.amount for e in self._expenses)</code></pre>

<div class="alert alert-info" role="alert">
<strong>See the principles at work.</strong> <code>_expenses</code> and <code>_storage</code> are internal
(encapsulation); <code>list_all</code> returns a copy so callers can't mutate internal state; the
tracker depends on the abstract <code>Storage</code> (DIP); each method does one thing (SRP); and
<code>summary</code> uses <code>defaultdict</code> from the standard library. This is what "comprehensive"
looks like — small clean pieces composing into a real app.
</div>

<h2>Quick manual check in the REPL</h2>
<pre><code>&gt;&gt;&gt; from tracker.storage import JsonStorage
&gt;&gt;&gt; from tracker.core import ExpenseTracker
&gt;&gt;&gt; t = ExpenseTracker(JsonStorage("test.json"))
&gt;&gt;&gt; t.add(12.5, "food", "Lunch")
Expense(amount=12.5, category='food', note='Lunch')
&gt;&gt;&gt; t.summary()
{'food': 12.5}</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Letting the core read/write JSON directly</strong> — that couples logic to a file format and
makes testing painful. Keep persistence in <code>storage.py</code>.</li>
<li><strong>Skipping validation</strong> in <code>add</code> — invalid data then corrupts your file.</li>
<li><strong>Returning the internal list directly</strong> from <code>list_all</code> — callers could mutate
it; return a copy.</li>
<li><strong>Not handling the missing/corrupt file</strong> cases in <code>load</code>.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>Expense</code> is a <strong>dataclass</strong> with dict conversion for JSON.</li>
<li>Persistence lives behind a <strong>Storage abstraction</strong>; <code>JsonStorage</code> handles
missing/corrupt files.</li>
<li><code>ExpenseTracker</code> holds the logic, <strong>validates input</strong>, and gets storage
<strong>injected</strong>.</li>
<li>Every layer is small, single-purpose, and independently testable.</li>
</ul>
EOT
      . vid_box('Implementing models, JSON storage, and core logic for a Python app.', 'python build cli app dataclass json storage tutorial'),
    ],

    [
      'title' => 'The CLI, tests & shipping it',
      'minutes' => 22,
      'video_url' => '',
      'content' => <<<'EOT'
<h2>__main__.py — the command-line interface</h2>
<p>We use the standard library's <code>argparse</code> to parse commands and arguments. Keep the CLI
<em>thin</em>: it parses input and prints output, delegating all real work to <code>ExpenseTracker</code>
(SRP — the CLI is just the user-facing layer).</p>
<pre><code># tracker/__main__.py
import argparse
import sys
from .core import ExpenseTracker
from .storage import JsonStorage

def build_parser() -&gt; argparse.ArgumentParser:
    parser = argparse.ArgumentParser(prog="tracker", description="Track expenses.")
    sub = parser.add_subparsers(dest="command", required=True)

    add = sub.add_parser("add", help="add an expense")
    add.add_argument("amount", type=float)
    add.add_argument("category")
    add.add_argument("note", nargs="?", default="")

    sub.add_parser("list", help="list all expenses")
    sub.add_parser("summary", help="totals by category")
    return parser

def main(argv=None):
    args = build_parser().parse_args(argv)
    tracker = ExpenseTracker(JsonStorage())

    try:
        if args.command == "add":
            e = tracker.add(args.amount, args.category, args.note)
            print(f"Added: ${e.amount:.2f} — {e.category} — {e.note}")

        elif args.command == "list":
            for i, e in enumerate(tracker.list_all(), start=1):
                print(f"#{i}  ${e.amount:&gt;7.2f}  {e.category:&lt;10} {e.note}")

        elif args.command == "summary":
            for category, amount in tracker.summary().items():
                print(f"{category + ':':&lt;12} ${amount:.2f}")
            print(f"{'TOTAL:':&lt;12} ${tracker.total():.2f}")

    except ValueError as e:
        print(f"Error: {e}", file=sys.stderr)
        return 1                       # non-zero exit code signals failure
    return 0

if __name__ == "__main__":
    raise SystemExit(main())</code></pre>
<p>Run it with <code>python -m tracker add 12.50 food "Lunch"</code>. The error handling turns an
invalid amount into a friendly message and a proper exit code, not a traceback.</p>

<h2>tests/test_core.py — prove it works</h2>
<p>Here's the payoff of the design: because <code>ExpenseTracker</code> takes any <code>Storage</code>, we
test it with a <strong>fake in-memory storage</strong> — fast, isolated, no files touched (Modules 7 &amp;
11).</p>
<pre><code># tests/test_core.py
import pytest
from tracker.core import ExpenseTracker
from tracker.storage import Storage
from tracker.models import Expense

class FakeStorage(Storage):
    """In-memory storage for tests — no real files."""
    def __init__(self):
        self.data: list[Expense] = []
    def load(self):
        return list(self.data)
    def save(self, expenses):
        self.data = list(expenses)

@pytest.fixture
def tracker():
    return ExpenseTracker(FakeStorage())

def test_add_returns_expense(tracker):
    e = tracker.add(12.5, "food", "Lunch")
    assert e.amount == 12.5
    assert e.category == "food"

def test_add_persists(tracker):
    tracker.add(10, "food")
    tracker.add(5, "food")
    assert len(tracker.list_all()) == 2

def test_summary_groups_by_category(tracker):
    tracker.add(10, "food")
    tracker.add(40, "transport")
    tracker.add(5, "food")
    assert tracker.summary() == {"food": 15, "transport": 40}

def test_total(tracker):
    tracker.add(10, "food")
    tracker.add(40, "transport")
    assert tracker.total() == 50

@pytest.mark.parametrize("amount", [0, -5, -0.01])
def test_rejects_non_positive_amount(tracker, amount):
    with pytest.raises(ValueError):
        tracker.add(amount, "food")

def test_rejects_blank_category(tracker):
    with pytest.raises(ValueError):
        tracker.add(10, "   ")</code></pre>
<pre><code>$ pytest -v
test_core.py::test_add_returns_expense PASSED
test_core.py::test_summary_groups_by_category PASSED
... all green ✅</code></pre>

<div class="alert alert-info" role="alert">
<strong>This is the moment it clicks.</strong> You didn't need a database or files to test the logic —
the <code>FakeStorage</code> stands in because the core depends on the <em>abstraction</em>. That's
dependency inversion making code testable, exactly as promised. Clean design and testability are the
same thing seen from two angles.
</div>

<h2>Write the README</h2>
<pre><code># Expense Tracker

A command-line tool to track expenses, persisted to JSON.

## Setup
    python3 -m venv .venv
    source .venv/bin/activate
    pip install -r requirements.txt

## Usage
    python -m tracker add 12.50 food "Lunch"
    python -m tracker list
    python -m tracker summary

## Tests
    pytest</code></pre>

<h2>Where to go next</h2>
<p>You now have a complete, tested, well-structured Python application — the real proof you can build
software. Extend it to cement the skills: add a <code>delete</code> command, date filtering, CSV export
(Module 9), a monthly budget with warnings, or swap <code>JsonStorage</code> for an SQLite one
(without touching <code>core.py</code> — that's the design paying off). Then build something of your
own from scratch. That's how you become a developer: keep shipping.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Putting logic in the CLI</strong> instead of the tracker — keep <code>__main__.py</code> thin.</li>
<li><strong>Testing against real files</strong> instead of a fake storage — slow and flaky.</li>
<li><strong>Letting tracebacks reach the user</strong> — catch expected errors and print friendly
messages with a non-zero exit code.</li>
<li><strong>Shipping without a README</strong> — others can't run it.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Build the CLI with <code>argparse</code> and keep it <strong>thin</strong> — it delegates to the core.</li>
<li>Convert expected errors into friendly messages + non-zero exit codes.</li>
<li>Test the core with a <strong>fake storage</strong> — fast, isolated, file-free (DIP in action).</li>
<li>Ship with a <strong>README</strong>; then extend the app to keep practising. You can build software now.</li>
</ul>
EOT
      . vid_box('Building a CLI with argparse, testing with fakes, and finishing a project.', 'python argparse cli tutorial build project'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 12 Quiz: Capstone',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'Why does ExpenseTracker receive its storage object as a constructor argument?',
        'explain' => 'Dependency injection: depending on the abstract Storage lets you swap implementations (JSON, SQLite, a fake) and makes the core logic easy to test without real files.',
        'options' => [
          ['So storage can be swapped and a fake can be injected in tests (DIP)', true],
          ['Because Python requires all classes to take arguments', false],
          ['To make the program run faster', false],
          ['So the CLI can skip argparse', false],
        ],
      ],
      [
        'q' => 'In the test suite, what is the purpose of FakeStorage?',
        'explain' => 'It is an in-memory Storage implementation so tests run fast and isolated without reading or writing real files.',
        'options' => [
          ['Provide in-memory storage so tests need no real files', true],
          ['Encrypt the expenses file', false],
          ['Speed up JSON parsing in production', false],
          ['Replace argparse', false],
        ],
      ],
      [
        'q' => 'Why should the CLI layer (__main__.py) stay thin?',
        'explain' => 'Single Responsibility: the CLI should only parse input and print output, delegating real work to the tracker. This keeps logic testable and the CLI simple.',
        'options' => [
          ['So logic stays in the testable core and the CLI just parses/prints', true],
          ['Because argparse cannot handle much code', false],
          ['To avoid using functions', false],
          ['So it can run without Python installed', false],
        ],
      ],
      [
        'q' => 'How does JsonStorage.load() handle the very first run when no file exists yet?',
        'explain' => 'A missing file is a normal first-run situation, so load() returns an empty list rather than raising an error. A corrupt file, by contrast, raises a clear error.',
        'options' => [
          ['It returns an empty list (no expenses yet)', true],
          ['It raises FileNotFoundError and crashes', false],
          ['It creates random sample data', false],
          ['It deletes the directory', false],
        ],
      ],
      [
        'q' => 'When an invalid amount is entered on the command line, the app should...',
        'explain' => 'It should catch the ValueError, print a friendly message to stderr, and return a non-zero exit code — not dump a raw traceback at the user.',
        'options' => [
          ['Print a friendly error message and return a non-zero exit code', true],
          ['Show the full Python traceback to the user', false],
          ['Silently ignore it and save nothing', false],
          ['Save the expense anyway', false],
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
