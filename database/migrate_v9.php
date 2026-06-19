<?php
/**
 * Migration v9 — Seed the "FastAPI & Databases: Build Production APIs" course.
 * Run ONCE via browser, then DELETE this file.
 *
 *   Access: /database/migrate_v9.php?key=hackathon2026fastapi
 *
 * An intermediate course (assumes the Python course / solid Python basics):
 * REST API development with FastAPI, request/response modelling with Pydantic,
 * PostgreSQL + SQLAlchemy ORM, a full CRUD API, JWT/OAuth2 authentication,
 * Git workflows & CI/CD, and deployment on Railway / Render.
 *
 * Each lesson is rich HTML (diagrams, analogy, common mistakes, key takeaways)
 * ending in an end-of-module quiz that gates the next module. Every lesson and
 * module has an EMPTY `video_url` slot with a "Watch as a guide" box suggesting a
 * search term; curate the real video and paste the YouTube *embed* URL via
 * Admin → Lessons (the paste-instruction is admin-only, hidden from students).
 *
 * IDEMPOTENT: if the course already exists it is deleted (cascade) and re-seeded,
 * so re-running always yields one clean copy. Touches no other course's data.
 * Production is SQLite with live student data — this script only creates/replaces
 * THIS one course (matched by title) and never alters users, attempts, or other
 * courses.
 */

define('MIGRATION_PASSWORD', 'hackathon2026fastapi');
if (($_GET['key'] ?? '') !== MIGRATION_PASSWORD) {
    http_response_code(403);
    die('<h2>Access denied.</h2><p>Add ?key=hackathon2026fastapi to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();

header('Content-Type: text/html; charset=utf-8');

$COURSE_TITLE = 'FastAPI & Databases: Build Production APIs';
$COURSE_DESC  = 'Go from Python developer to backend engineer. Build real REST APIs with FastAPI, '
    . 'model requests and responses with Pydantic, persist data in PostgreSQL through the SQLAlchemy ORM, '
    . 'secure your API with JWT and OAuth2, set up Git workflows and CI/CD with GitHub Actions, and deploy '
    . 'a live, database-backed service on Railway or Render. Every lesson links to a hand-picked video guide.';

/** Build the consistent "watch as a guide" box. Admin-only paste instruction is hidden from students. */
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

/*
 * Each module: title, description, video_url, lessons[], quiz{}.
 * Each lesson: title, minutes, video_url, content (HTML).
 * Each quiz:   title, pass_mark, questions[] -> {q, explain, options[[text,correct]]}.
 * Inside <pre><code> blocks, < > & are written as &lt; &gt; &amp; so they render.
 */
$MODULES = [

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 1 — REST APIs & FASTAPI FUNDAMENTALS
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'REST APIs & FastAPI Fundamentals',
  'description' => 'What a REST API actually is, and how to build your first one with FastAPI: installing it, running the server, automatic docs, and handling path and query parameters.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'What is a REST API?',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/lsMQRaeKNDk',
      'content' => <<<'EOT'
<h2>An API is a contract between programs</h2>
<p>An <strong>API</strong> (Application Programming Interface) is how one program talks to another. A
<strong>web API</strong> does this over HTTP: a client (a browser, a mobile app, another server) sends
a request, your server runs some code, and sends back a response — usually <strong>JSON</strong>.</p>
<p>This is the backbone of modern software: the mobile app you use doesn't contain your data, it
<em>asks an API</em> for it.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> A REST API is like a restaurant waiter. You (the client) don't walk into the
kitchen (the database). You give the waiter a structured order (a request), and they bring back
exactly what you asked for (a response). The menu (the API's documented endpoints) tells you what
you're allowed to order.
</div>

<h2>What makes an API "REST"</h2>
<p><strong>REST</strong> (Representational State Transfer) is a style for designing web APIs around
<strong>resources</strong> — the "things" your app manages (users, products, orders). Each resource has
a URL, and you act on it using HTTP methods.</p>
<pre><code>GET    /products        → list all products
GET    /products/42     → get product 42
POST   /products        → create a new product
PUT    /products/42     → replace product 42
PATCH  /products/42     → partially update product 42
DELETE /products/42     → delete product 42
</code></pre>
<p>Notice the URL names the <em>resource</em> (a noun), and the HTTP <em>method</em> (a verb) says what
to do with it. You don't put verbs in the URL (<code>/getProduct</code> is not RESTful).</p>

<h2>HTTP methods and safety</h2>
<table>
<thead><tr><th>Method</th><th>Purpose</th><th>Safe?</th><th>Idempotent?</th></tr></thead>
<tbody>
<tr><td><code>GET</code></td><td>read data</td><td>yes (no changes)</td><td>yes</td></tr>
<tr><td><code>POST</code></td><td>create</td><td>no</td><td>no (calls twice → two records)</td></tr>
<tr><td><code>PUT</code></td><td>replace</td><td>no</td><td>yes</td></tr>
<tr><td><code>PATCH</code></td><td>partial update</td><td>no</td><td>usually</td></tr>
<tr><td><code>DELETE</code></td><td>delete</td><td>no</td><td>yes</td></tr>
</tbody>
</table>
<p><strong>Idempotent</strong> means calling it repeatedly has the same effect as calling it once —
important for retries.</p>

<h2>Status codes: the response's headline</h2>
<p>Every response carries a 3-digit <strong>status code</strong> grouped by first digit:</p>
<pre><code>2xx  Success      200 OK, 201 Created, 204 No Content
3xx  Redirect     301 Moved, 304 Not Modified
4xx  Client error 400 Bad Request, 401 Unauthorized,
                  403 Forbidden, 404 Not Found, 422 Unprocessable
5xx  Server error 500 Internal Server Error, 503 Unavailable
</code></pre>
<p>Rule of thumb: <strong>4xx = the caller did something wrong; 5xx = your server broke.</strong>
Returning the right code is part of a good API — clients rely on it.</p>

<h2>Anatomy of a request and response</h2>
<pre><code>REQUEST                          RESPONSE
POST /products HTTP/1.1          HTTP/1.1 201 Created
Host: api.example.com           Content-Type: application/json
Content-Type: application/json
Authorization: Bearer eyJ...    {
                                  "id": 42,
{                                 "name": "Keyboard",
  "name": "Keyboard",            "price": 49.99
  "price": 49.99               }
}
</code></pre>
<p>A request has a <strong>method</strong>, <strong>path</strong>, <strong>headers</strong> (metadata like
content type and auth), and often a <strong>body</strong>. The response has a <strong>status code</strong>,
headers, and a body.</p>

<h2>Why FastAPI?</h2>
<p><strong>FastAPI</strong> is a modern Python framework for building APIs. It's popular because it gives
you, almost for free: data validation, automatic interactive documentation, async support, and great
speed — all driven by standard Python <strong>type hints</strong> (which you learned in the Python
course).</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Verbs in URLs.</strong> <code>/createUser</code> is not RESTful — use <code>POST /users</code>.</li>
<li><strong>Always returning 200.</strong> A failed create that returns <code>200 OK</code> lies to the
client. Use the right code (<code>201</code>, <code>404</code>, <code>400</code>...).</li>
<li><strong>Using GET to change data.</strong> GET must be safe (read-only); browsers and caches assume
it has no side effects.</li>
<li><strong>Confusing 401 and 403.</strong> 401 = "I don't know who you are" (not logged in);
403 = "I know who you are, but you're not allowed".</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>A REST API exposes <strong>resources</strong> (nouns) acted on by HTTP <strong>methods</strong> (verbs),
exchanging JSON over HTTP.</li>
<li><code>GET/POST/PUT/PATCH/DELETE</code> map to read/create/replace/update/delete.</li>
<li><strong>Status codes</strong> communicate the outcome: 2xx success, 4xx client error, 5xx server error.</li>
<li><strong>FastAPI</strong> builds these APIs using Python type hints, with validation and docs included.</li>
</ul>
EOT
      . vid_box('A clear explanation of REST APIs, HTTP methods, and status codes.', 'what is a rest api http methods explained'),
    ],

    [
      'title' => 'Setting up FastAPI and your first endpoint',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/7AMjmCTumuo',
      'content' => <<<'EOT'
<h2>Install FastAPI in a virtual environment</h2>
<p>Always work in a virtual environment (from the Python course). FastAPI needs an <strong>ASGI
server</strong> to run — <code>uvicorn</code> — so install both:</p>
<pre><code>python3 -m venv .venv
source .venv/bin/activate          # Windows: .venv\Scripts\Activate.ps1
pip install "fastapi[standard]" uvicorn
pip freeze &gt; requirements.txt</code></pre>
<p><code>fastapi[standard]</code> pulls in sensible extras (including uvicorn and the CLI). Think of
FastAPI as the framework and <strong>uvicorn</strong> as the engine that actually serves HTTP.</p>

<h2>Your first endpoint</h2>
<pre><code># main.py
from fastapi import FastAPI

app = FastAPI()                    # the application object

@app.get("/")                      # register a GET handler for "/"
def read_root():
    return {"message": "Hello, API!"}</code></pre>
<p>Two things to notice:</p>
<ul>
<li><code>@app.get("/")</code> is a <strong>decorator</strong> that tells FastAPI "run this function for
GET requests to <code>/</code>." This is called a <strong>path operation</strong>.</li>
<li>You return a plain Python <code>dict</code>; FastAPI automatically converts it to <strong>JSON</strong>
and sets the right headers.</li>
</ul>

<h2>Run the development server</h2>
<pre><code>fastapi dev main.py
# or the classic way:
uvicorn main:app --reload</code></pre>
<p><code>main:app</code> means "the <code>app</code> object inside <code>main.py</code>". <code>--reload</code>
restarts the server automatically when you save a file. Visit
<code>http://127.0.0.1:8000/</code> and you'll see your JSON.</p>

<div class="alert alert-info" role="alert">
<strong>The killer feature: automatic docs.</strong> Open <code>http://127.0.0.1:8000/docs</code>. FastAPI
generated an interactive <strong>Swagger UI</strong> from your code — you can try every endpoint in the
browser. There's also <code>/redoc</code>. You wrote zero documentation; the type hints produced it.
</div>

<h2>Multiple endpoints</h2>
<pre><code>@app.get("/health")
def health_check():
    return {"status": "ok"}

@app.get("/products")
def list_products():
    return [
        {"id": 1, "name": "Keyboard"},
        {"id": 2, "name": "Mouse"},
    ]</code></pre>
<p>Returning a list of dicts becomes a JSON array. Each decorated function is one endpoint.</p>

<h2>async or not?</h2>
<p>FastAPI supports both regular and <code>async</code> functions:</p>
<pre><code>@app.get("/sync")
def sync_endpoint():
    return {"type": "sync"}

@app.get("/async")
async def async_endpoint():
    return {"type": "async"}</code></pre>
<p>Use <code>async def</code> when you call libraries that support <code>await</code> (async DB drivers,
HTTP clients). If you're unsure or using blocking libraries, a normal <code>def</code> is perfectly
fine — FastAPI runs it safely in a threadpool. Don't put blocking calls inside an <code>async def</code>;
that stalls the server.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting uvicorn</strong> — <code>fastapi</code> alone doesn't serve HTTP; you need the
server (<code>fastapi dev</code> or <code>uvicorn</code>).</li>
<li><strong>Wrong <code>main:app</code> reference</strong> — the part before <code>:</code> is the filename
(no <code>.py</code>), after is the variable name.</li>
<li><strong>Blocking code in <code>async def</code></strong> (e.g. <code>time.sleep</code>, sync DB calls)
freezes the whole server. Use a normal <code>def</code> or an async library.</li>
<li><strong>Editing without <code>--reload</code></strong> in dev and wondering why changes don't appear.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Install <code>fastapi[standard]</code> + <code>uvicorn</code> in a venv; run with <code>fastapi dev main.py</code>.</li>
<li>Decorate functions with <code>@app.get(...)</code> etc. to create <strong>path operations</strong>;
return dicts/lists → FastAPI sends JSON.</li>
<li>Interactive docs are auto-generated at <strong><code>/docs</code></strong> and <code>/redoc</code>.</li>
<li>Use <code>async def</code> only with awaitable libraries; never block inside it.</li>
</ul>
EOT
      . vid_box('Installing FastAPI and building/running your first endpoint with the auto docs.', 'fastapi tutorial getting started first app'),
    ],

    [
      'title' => 'Path and query parameters',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/WRjXIA5pMtk',
      'content' => <<<'EOT'
<h2>Path parameters: part of the URL</h2>
<p>A <strong>path parameter</strong> is a variable piece of the URL path — typically a resource's id.
Declare it in the path string and as a function argument with a type hint:</p>
<pre><code>@app.get("/products/{product_id}")
def get_product(product_id: int):
    return {"product_id": product_id}</code></pre>
<p>Request <code>/products/42</code> → <code>product_id</code> is <code>42</code>. Because you typed it as
<code>int</code>, FastAPI <strong>validates and converts</strong> it: <code>/products/abc</code> returns a
clean <code>422</code> error automatically — you never wrote that check.</p>

<div class="alert alert-info" role="alert">
<strong>Type hints do real work here.</strong> In plain Python a type hint is just documentation. In
FastAPI it drives parsing, validation, conversion, and the docs. <code>product_id: int</code> means
"this must be an integer" — enforced for you.
</div>

<h2>Query parameters: after the <code>?</code></h2>
<p>Function arguments that are <em>not</em> in the path become <strong>query parameters</strong> — the
<code>?key=value&amp;key2=value2</code> part of a URL:</p>
<pre><code>@app.get("/products")
def list_products(skip: int = 0, limit: int = 10):
    return {"skip": skip, "limit": limit}
# GET /products            → skip=0, limit=10   (defaults)
# GET /products?limit=5    → skip=0, limit=5
# GET /products?skip=20&amp;limit=5</code></pre>
<p>Give them <strong>default values</strong> to make them optional. Without a default, the parameter is
required and its absence returns a 422.</p>

<h2>Optional parameters</h2>
<pre><code>@app.get("/search")
def search(q: str | None = None, in_stock: bool = False):
    return {"q": q, "in_stock": in_stock}
# GET /search?q=keyboard&amp;in_stock=true</code></pre>
<p><code>str | None = None</code> means "a string, or omitted entirely." FastAPI also parses booleans
loosely: <code>true</code>, <code>1</code>, <code>yes</code>, <code>on</code> all become <code>True</code>.</p>

<h2>Validating parameters with Path and Query</h2>
<p>You can add constraints (and docs) with <code>Path</code> and <code>Query</code>:</p>
<pre><code>from fastapi import Path, Query

@app.get("/products/{product_id}")
def get_product(
    product_id: int = Path(gt=0, description="The product's ID"),
    q: str | None = Query(default=None, max_length=50),
):
    return {"product_id": product_id, "q": q}</code></pre>
<p><code>gt=0</code> ("greater than 0"), <code>max_length=50</code>, etc. are validated automatically and
shown in <code>/docs</code>. Invalid input → a descriptive 422 with the exact field that failed.</p>

<h2>Path order matters</h2>
<pre><code># WRONG order — /products/featured never reached:
@app.get("/products/{product_id}")
def get_product(product_id: int): ...
@app.get("/products/featured")        # shadowed!
def featured(): ...

# RIGHT — put the fixed path FIRST:
@app.get("/products/featured")
def featured(): ...
@app.get("/products/{product_id}")
def get_product(product_id: int): ...</code></pre>
<p>FastAPI matches routes top to bottom; a fixed path must be declared before the matching dynamic
one.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting the type hint</strong> — without it, everything arrives as a string and no
validation happens.</li>
<li><strong>No default = required.</strong> If a query param should be optional, give it a default
(often <code>None</code>).</li>
<li><strong>Route ordering bugs</strong> — dynamic paths can shadow fixed ones; order fixed paths first.</li>
<li><strong>Expecting 404 for bad types.</strong> A non-integer id gives <strong>422</strong> (validation),
not 404.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>Path parameters</strong> (<code>{id}</code>) identify a resource; <strong>query parameters</strong>
(after <code>?</code>) filter/paginate.</li>
<li>Type hints drive automatic parsing, validation, and conversion (bad input → 422).</li>
<li>A default value makes a parameter optional; no default makes it required.</li>
<li>Use <code>Path(...)</code>/<code>Query(...)</code> for constraints; declare fixed routes before dynamic ones.</li>
</ul>
EOT
      . vid_box('Path parameters, query parameters, and validation in FastAPI.', 'fastapi path and query parameters tutorial'),
    ],

    [
      'title' => 'Request bodies, status codes, and responses',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/zq0_g3BKltE',
      'content' => <<<'EOT'
<h2>Sending data in the body</h2>
<p>GET requests carry data in the URL. To <em>create</em> or <em>update</em> resources, clients send a
<strong>request body</strong> (JSON) with POST/PUT/PATCH. In FastAPI, you receive that body by declaring
a parameter typed as a <strong>Pydantic model</strong> (full detail in the next module):</p>
<pre><code>from fastapi import FastAPI
from pydantic import BaseModel

app = FastAPI()

class ProductIn(BaseModel):       # describes the expected body
    name: str
    price: float

@app.post("/products")
def create_product(product: ProductIn):
    return {"created": product.name, "price": product.price}</code></pre>
<p>FastAPI reads the JSON body, validates it against <code>ProductIn</code>, and hands you a typed
object. A missing or wrong-typed field → an automatic 422 listing exactly what's wrong.</p>

<h2>Setting the status code</h2>
<p>A successful <em>create</em> should return <strong>201 Created</strong>, not 200. Declare it on the
decorator:</p>
<pre><code>from fastapi import status

@app.post("/products", status_code=status.HTTP_201_CREATED)
def create_product(product: ProductIn):
    return {"name": product.name}</code></pre>
<p>Use the <code>status</code> constants for readability instead of magic numbers like <code>201</code>.</p>

<h2>Raising errors with HTTPException</h2>
<p>When something is wrong, don't return an error dict with a 200 code — <strong>raise
<code>HTTPException</code></strong> so the client gets the correct status:</p>
<pre><code>from fastapi import HTTPException

products = {1: "Keyboard", 2: "Mouse"}

@app.get("/products/{product_id}")
def get_product(product_id: int):
    if product_id not in products:
        raise HTTPException(
            status_code=404,
            detail="Product not found",
        )
    return {"id": product_id, "name": products[product_id]}</code></pre>
<p>FastAPI turns the exception into a proper JSON error response with the status you set:
<code>{"detail": "Product not found"}</code> and a 404.</p>

<div class="alert alert-info" role="alert">
<strong>Let exceptions flow.</strong> You can <code>raise HTTPException</code> from anywhere in your call
stack — a helper function deep inside still produces the right HTTP response. This is the EAFP style
from the Python course applied to web APIs.
</div>

<h2>The request → response lifecycle</h2>
<pre><code>Client request
   │  JSON body + headers
   ▼
FastAPI parses & VALIDATES against your type hints / Pydantic model
   │  (invalid → 422 automatically, your code never runs)
   ▼
Your path operation function runs (you get clean, typed data)
   │  return a dict / model  (or raise HTTPException)
   ▼
FastAPI SERIALIZES the return value to JSON + sets status code
   ▼
Client response
</code></pre>

<h2>Common response shapes</h2>
<pre><code>return {"id": 1, "name": "Keyboard"}      # 200 + JSON object
return [ {...}, {...} ]                    # 200 + JSON array
raise HTTPException(404, "Not found")      # error with proper code
# 204 No Content — return nothing:
@app.delete("/products/{id}", status_code=204)
def delete_product(id: int):
    ...        # no return body</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Returning errors as 200.</strong> <code>{"error": "not found"}</code> with a 200 status
breaks clients — raise <code>HTTPException</code> instead.</li>
<li><strong>Wrong create status.</strong> Creates should return <code>201</code>; set
<code>status_code=</code> on the decorator.</li>
<li><strong>Putting a body on GET/DELETE.</strong> Request bodies belong on POST/PUT/PATCH.</li>
<li><strong>Manually parsing JSON.</strong> Don't read the raw request — declare a Pydantic model and
let FastAPI validate it.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Receive JSON bodies by typing a parameter as a <strong>Pydantic model</strong>; validation is automatic.</li>
<li>Set success codes with <code>status_code=</code> (e.g. <code>201</code> for creates, <code>204</code> for deletes).</li>
<li>Signal failures by <strong>raising <code>HTTPException</code></strong> with the right status and a <code>detail</code>.</li>
<li>FastAPI validates input then serializes your return value to JSON — you focus on logic.</li>
</ul>
EOT
      . vid_box('Handling request bodies, status codes, and HTTPException in FastAPI.', 'fastapi request body httpexception status code tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 1 Quiz: REST & FastAPI Fundamentals',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'In REST, which HTTP method should create a new resource?',
        'explain' => 'POST is used to create a new resource (e.g. POST /products). GET reads, PUT replaces, DELETE removes.',
        'options' => [
          ['POST', true],
          ['GET', false],
          ['DELETE', false],
          ['PATCH', false],
        ],
      ],
      [
        'q' => 'A client sends a request without valid credentials to a protected endpoint. Which status code fits "I don\'t know who you are"?',
        'explain' => '401 Unauthorized means not authenticated. 403 Forbidden means authenticated but not allowed.',
        'options' => [
          ['401 Unauthorized', true],
          ['403 Forbidden', false],
          ['404 Not Found', false],
          ['200 OK', false],
        ],
      ],
      [
        'q' => 'What does @app.get("/products/{product_id}") with product_id: int do when called as /products/abc?',
        'explain' => 'The int type hint makes FastAPI validate and convert the path param. A non-integer fails validation and returns 422 automatically.',
        'options' => [
          ['Returns a 422 validation error automatically', true],
          ['Passes "abc" to the function as a string', false],
          ['Returns 404 Not Found', false],
          ['Crashes the server with 500', false],
        ],
      ],
      [
        'q' => 'How does a function parameter become a query parameter (not a path parameter)?',
        'explain' => 'If a parameter is not part of the path template, FastAPI treats it as a query parameter (e.g. ?limit=5). A default value makes it optional.',
        'options' => [
          ['It is declared in the function but not in the path string', true],
          ['It must be named "query"', false],
          ['It must be a Pydantic model', false],
          ['It must use the @query decorator', false],
        ],
      ],
      [
        'q' => 'What is the correct way to signal "product not found" from an endpoint?',
        'explain' => 'Raise HTTPException(status_code=404, detail=...). Returning an error dict with status 200 misleads clients.',
        'options' => [
          ['raise HTTPException(status_code=404, detail="Product not found")', true],
          ['return {"error": "not found"} with status 200', false],
          ['return None', false],
          ['print an error and return 200', false],
        ],
      ],
      [
        'q' => 'Where does FastAPI generate interactive API documentation by default?',
        'explain' => 'FastAPI auto-generates Swagger UI at /docs (and ReDoc at /redoc) from your type hints and models.',
        'options' => [
          ['/docs', true],
          ['/api', false],
          ['/swagger.json only', false],
          ['You must write docs manually', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 2 — REQUEST/RESPONSE MODELLING WITH PYDANTIC
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Request/Response Modelling with Pydantic',
  'description' => 'Pydantic is the data layer of FastAPI. Define typed models that validate incoming requests, shape outgoing responses, and document your API — with custom validation when you need it.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Pydantic models and validation',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/ok8bF8M7gjk',
      'content' => <<<'EOT'
<h2>What is Pydantic?</h2>
<p><strong>Pydantic</strong> turns Python classes with type hints into <strong>data validators</strong>. You
declare what your data should look like; Pydantic parses raw input, checks it, converts it, and gives
you a clean typed object — or a precise error. FastAPI uses it for every request and response.</p>
<pre><code>from pydantic import BaseModel

class Product(BaseModel):
    name: str
    price: float
    in_stock: bool = True          # default makes it optional

p = Product(name="Keyboard", price="49.99")   # note: price is a string!
print(p.price)        # 49.99  (float) — Pydantic CONVERTED it
print(p.in_stock)     # True   (default)</code></pre>
<p>Subclass <code>BaseModel</code>, declare fields with type hints. Pydantic even coerced the string
<code>"49.99"</code> into a <code>float</code>.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> A Pydantic model is a bouncer with a guest list. Data tries to get in; the
bouncer checks each field against the list (types and rules), turns away anything invalid with a
clear reason, and only lets clean, correctly-typed data through to your code.
</div>

<h2>Validation errors are precise</h2>
<pre><code>from pydantic import ValidationError

try:
    Product(name="Keyboard", price="abc")
except ValidationError as e:
    print(e)
# 1 validation error for Product
# price
#   Input should be a valid number ... (type=float_parsing)</code></pre>
<p>It tells you the model, the field, and why. In FastAPI this becomes an automatic <strong>422</strong>
response listing every bad field — you never write validation by hand.</p>

<h2>Type coercion vs strictness</h2>
<p>By default Pydantic is helpful: <code>"49.99"</code> → <code>49.99</code>, <code>"true"</code> →
<code>True</code>. This is great for web input (everything arrives as strings). When you need exact
types, you can opt into strict mode, but the lenient default is usually what you want for APIs.</p>

<h2>Rich field types</h2>
<p>Pydantic understands many types out of the box, which gives you validation for free:</p>
<pre><code>from datetime import datetime
from pydantic import BaseModel, EmailStr, HttpUrl

class User(BaseModel):
    id: int
    email: EmailStr          # must be a valid email
    website: HttpUrl | None = None   # must be a valid URL if given
    created_at: datetime     # parses ISO-8601 strings
    tags: list[str] = []     # a list of strings, default empty</code></pre>
<p>(<code>EmailStr</code> needs <code>pip install "pydantic[email]"</code>.) Declaring
<code>email: EmailStr</code> means malformed emails are rejected automatically.</p>

<h2>Nested models</h2>
<p>Models can contain other models — perfect for structured JSON:</p>
<pre><code>class Address(BaseModel):
    street: str
    city: str

class Customer(BaseModel):
    name: str
    address: Address          # nested model

c = Customer(name="Ada", address={"street": "1 Main", "city": "Lagos"})
print(c.address.city)         # Lagos  (Address validated too)</code></pre>

<h2>Accessing and exporting data</h2>
<pre><code>p = Product(name="Mouse", price=19.99)
p.name                 # attribute access
p.model_dump()         # → dict: {"name": "Mouse", "price": 19.99, "in_stock": True}
p.model_dump_json()    # → JSON string</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Missing type hints.</strong> A field with no annotation isn't validated — every field needs a
type.</li>
<li><strong>Mutable defaults done wrong.</strong> Pydantic handles <code>tags: list[str] = []</code>
safely (unlike plain functions), but for complex defaults use <code>Field(default_factory=list)</code>.</li>
<li><strong>Expecting old <code>.dict()</code>/<code>.json()</code>.</strong> Pydantic v2 uses
<code>model_dump()</code> / <code>model_dump_json()</code>.</li>
<li><strong>Assuming strictness.</strong> By default Pydantic coerces types; don't rely on it rejecting a
numeric string unless you enable strict mode.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Pydantic models (<code>BaseModel</code> + type hints) <strong>validate and convert</strong> data, raising
precise errors.</li>
<li>FastAPI turns those errors into automatic <strong>422</strong> responses.</li>
<li>Rich types (<code>EmailStr</code>, <code>datetime</code>, <code>HttpUrl</code>, nested models) give
free validation.</li>
<li>Export with <code>model_dump()</code> / <code>model_dump_json()</code> (Pydantic v2).</li>
</ul>
EOT
      . vid_box('Pydantic models, validation, and type coercion explained.', 'pydantic v2 tutorial models validation'),
    ],

    [
      'title' => 'Request bodies with Pydantic',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/9GHxnttXxrA',
      'content' => <<<'EOT'
<h2>From model to request body</h2>
<p>When a FastAPI parameter is typed as a Pydantic model, FastAPI reads the JSON body, validates it,
and gives you the object. You saw this in Module 1; now let's use it properly.</p>
<pre><code>from fastapi import FastAPI, status
from pydantic import BaseModel

app = FastAPI()

class ProductCreate(BaseModel):
    name: str
    price: float
    description: str | None = None

@app.post("/products", status_code=status.HTTP_201_CREATED)
def create_product(product: ProductCreate):
    # product is fully validated here
    return {"received": product.model_dump()}</code></pre>
<p>Send <code>{"name": "Keyboard", "price": 49.99}</code> and it works; send
<code>{"name": "Keyboard"}</code> and FastAPI returns a 422 saying <code>price</code> is required.</p>

<h2>Body + path + query together</h2>
<p>FastAPI figures out where each parameter comes from by its type:</p>
<pre><code>@app.put("/products/{product_id}")
def update_product(
    product_id: int,            # in the path → path param
    product: ProductCreate,     # Pydantic model → request body
    notify: bool = False,       # simple type with default → query param
):
    return {"id": product_id, "data": product.model_dump(), "notify": notify}</code></pre>
<p>Path params come from the URL template, Pydantic models from the body, and remaining simple types
become query params. No manual wiring.</p>

<h2>Documenting and constraining fields with Field</h2>
<pre><code>from pydantic import BaseModel, Field

class ProductCreate(BaseModel):
    name: str = Field(min_length=1, max_length=100,
                      description="Display name")
    price: float = Field(gt=0, description="Must be positive")
    quantity: int = Field(default=0, ge=0)</code></pre>
<p><code>gt=0</code> (greater than), <code>ge=0</code> (≥ 0), <code>min_length</code>, etc. are validated
automatically and surface in the <code>/docs</code> schema. A price of <code>-5</code> is rejected with
a clear message before your code runs.</p>

<h2>Example payloads in the docs</h2>
<pre><code>class ProductCreate(BaseModel):
    name: str
    price: float

    model_config = {
        "json_schema_extra": {
            "examples": [
                {"name": "Keyboard", "price": 49.99}
            ]
        }
    }</code></pre>
<p>This pre-fills the "Try it out" example in Swagger UI, making your API easy to explore.</p>

<h2>Separate models for create vs update</h2>
<p>A create needs all required fields; a partial update (PATCH) makes everything optional. Model them
separately so validation matches intent:</p>
<pre><code>class ProductCreate(BaseModel):
    name: str
    price: float

class ProductUpdate(BaseModel):       # all optional for PATCH
    name: str | None = None
    price: float | None = None</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>One giant model for everything.</strong> Create, update, and response usually need different
shapes — use separate models.</li>
<li><strong>Reading the raw body manually.</strong> Declare a model; don't parse <code>request.json()</code>
yourself.</li>
<li><strong>Forgetting <code>| None = None</code> for optional fields</strong> — without it the field is
required.</li>
<li><strong>Validation in the endpoint body.</strong> Put rules in the model (<code>Field</code>,
validators), not scattered <code>if</code> checks in the handler.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Type a parameter as a Pydantic model → FastAPI validates the JSON <strong>request body</strong> for you.</li>
<li>FastAPI infers source by type: path template → path, model → body, simple types → query.</li>
<li>Use <code>Field(...)</code> for constraints, descriptions, and docs.</li>
<li>Model <strong>create / update / response</strong> separately to match each operation's needs.</li>
</ul>
EOT
      . vid_box('Receiving and validating request bodies with Pydantic models in FastAPI.', 'fastapi request body pydantic model tutorial'),
    ],

    [
      'title' => 'Response models and serialization',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/WjStUP4XFCc',
      'content' => <<<'EOT'
<h2>Why a response model?</h2>
<p>What you return isn't always what you should send. A user record has a hashed password — you must
<em>never</em> leak it. A <strong>response model</strong> defines the exact shape of the output, and
FastAPI filters your return value to match.</p>
<pre><code>from pydantic import BaseModel

class UserIn(BaseModel):       # what the client sends
    username: str
    email: str
    password: str

class UserOut(BaseModel):      # what we send back — NO password
    username: str
    email: str

@app.post("/users", response_model=UserOut)
def create_user(user: UserIn):
    save(user)
    return user                # has a password, but...
    # response_model strips it: only username + email go out</code></pre>
<p>Even though you returned the full object, <code>response_model=UserOut</code> guarantees the response
only contains <code>username</code> and <code>email</code>. This is a safety net, not just docs.</p>

<div class="alert alert-info" role="alert">
<strong>Two models, two jobs.</strong> Think "In" and "Out": <code>UserIn</code> validates what comes in;
<code>UserOut</code> controls what goes out. Keeping them separate prevents accidental data leaks and
lets the two shapes evolve independently.
</div>

<h2>Response models also document and validate output</h2>
<ul>
<li><strong>Filtering:</strong> extra fields are removed.</li>
<li><strong>Validation:</strong> if your code returns the wrong shape, FastAPI errors instead of sending
bad data.</li>
<li><strong>Docs:</strong> <code>/docs</code> shows the exact response schema.</li>
</ul>

<h2>Lists of models</h2>
<pre><code>@app.get("/users", response_model=list[UserOut])
def list_users():
    return get_all_users()     # each item filtered to UserOut</code></pre>

<h2>Reading ORM objects: from_attributes</h2>
<p>Later you'll return SQLAlchemy rows (objects, not dicts). Tell the response model it may read object
attributes:</p>
<pre><code>class UserOut(BaseModel):
    id: int
    username: str
    email: str

    model_config = {"from_attributes": True}   # read ORM objects</code></pre>
<p>Now you can <code>return db_user</code> (a SQLAlchemy object) and FastAPI maps its attributes onto
<code>UserOut</code>.</p>

<h2>Controlling output</h2>
<pre><code># Drop unset/None fields to keep responses lean:
@app.get("/products/{id}", response_model=Product,
         response_model_exclude_none=True)
def get_product(id: int): ...

# Set the success status too:
@app.post("/users", response_model=UserOut, status_code=201)
def create_user(user: UserIn): ...</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>No response model on sensitive endpoints.</strong> Returning ORM/user objects directly can
leak password hashes, internal flags, etc. Always shape output.</li>
<li><strong>Reusing the input model as output.</strong> The input often contains fields (passwords) that
must not be returned.</li>
<li><strong>Forgetting <code>from_attributes</code></strong> when returning ORM objects → serialization
errors.</li>
<li><strong>Building response dicts by hand.</strong> Let <code>response_model</code> do the filtering
consistently.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>response_model=</code> defines and <strong>filters</strong> the output shape — your safety net
against leaking data.</li>
<li>Use separate <strong>In</strong> and <strong>Out</strong> models (e.g. never return the password).</li>
<li>Set <code>model_config = {"from_attributes": True}</code> to serialize ORM objects.</li>
<li>It also validates output and documents the response schema in <code>/docs</code>.</li>
</ul>
EOT
      . vid_box('Using response_model to shape and secure API output in FastAPI.', 'fastapi response model tutorial'),
    ],

    [
      'title' => 'Custom validation: validators and nested models',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/mOpeuZeBYt8',
      'content' => <<<'EOT'
<h2>When field types aren\'t enough</h2>
<p>Sometimes a value is the right type but still invalid: a password that's too weak, an end date
before a start date, a username with spaces. Pydantic <strong>validators</strong> let you add custom
rules.</p>

<h2>Field validators</h2>
<pre><code>from pydantic import BaseModel, field_validator

class SignUp(BaseModel):
    username: str
    password: str

    @field_validator("username")
    @classmethod
    def no_spaces(cls, v: str) -&gt; str:
        if " " in v:
            raise ValueError("username cannot contain spaces")
        return v.lower()          # validators can also TRANSFORM

    @field_validator("password")
    @classmethod
    def strong_enough(cls, v: str) -&gt; str:
        if len(v) &lt; 8:
            raise ValueError("password must be at least 8 characters")
        return v</code></pre>
<p>A validator receives the value, can check or transform it, and raises <code>ValueError</code> to
reject. In FastAPI that becomes a clean 422 with your message. Note validators can also normalise
data (here, lowercasing the username).</p>

<h2>Model validators: rules across fields</h2>
<p>To compare multiple fields, use a <strong>model validator</strong> that runs after all fields are set:</p>
<pre><code>from pydantic import BaseModel, model_validator
from datetime import date

class Booking(BaseModel):
    start: date
    end: date

    @model_validator(mode="after")
    def check_dates(self):
        if self.end &lt; self.start:
            raise ValueError("end date must be after start date")
        return self</code></pre>

<h2>Reusable constrained types</h2>
<p>For common constraints, annotate types directly instead of repeating <code>Field</code>:</p>
<pre><code>from typing import Annotated
from pydantic import BaseModel, Field

Price = Annotated[float, Field(gt=0)]
Name  = Annotated[str, Field(min_length=1, max_length=100)]

class Product(BaseModel):
    name: Name
    price: Price</code></pre>

<h2>Nested and list-of-model bodies</h2>
<p>Real payloads are nested. Pydantic validates the whole tree:</p>
<pre><code>class Item(BaseModel):
    product_id: int
    quantity: int = Field(gt=0)

class Order(BaseModel):
    customer_email: EmailStr
    items: list[Item]            # a list of validated Items

@app.post("/orders")
def create_order(order: Order):
    return {"item_count": len(order.items)}</code></pre>
<p>Send a JSON order with an <code>items</code> array; each item is validated, and an empty/invalid item
produces a precise error path like <code>items.0.quantity</code>.</p>

<div class="alert alert-info" role="alert">
<strong>Push validation to the edge.</strong> Validating at the model boundary means the rest of your
code can <em>trust</em> its data — no defensive checks scattered through business logic. This is the
single-responsibility and clean-code mindset applied to APIs.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Returning instead of raising in a validator.</strong> To reject, <code>raise ValueError(...)</code>;
returning the value <em>accepts</em> it.</li>
<li><strong>Cross-field checks in a field validator.</strong> A field validator only sees its own field;
use a <code>model_validator(mode="after")</code> to compare fields.</li>
<li><strong>Forgetting <code>@classmethod</code></strong> on <code>field_validator</code> (Pydantic v2 style).</li>
<li><strong>Doing validation in the endpoint.</strong> Keep rules in the model so they're reused and
auto-applied everywhere the model is used.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><code>@field_validator</code> adds custom per-field rules (and can transform values).</li>
<li><code>@model_validator(mode="after")</code> validates relationships <em>across</em> fields.</li>
<li>Nested models and <code>list[Model]</code> validate complex payloads with precise error paths.</li>
<li>Validate at the boundary so the rest of your code can trust the data.</li>
</ul>
EOT
      . vid_box('Custom Pydantic validators and validating nested request data.', 'pydantic field validator model validator tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 2 Quiz: Pydantic Modelling',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What does a Pydantic BaseModel do when you pass price="49.99" to a field typed as float?',
        'explain' => 'By default Pydantic coerces compatible types, converting the string "49.99" to the float 49.99. Truly invalid values raise a ValidationError.',
        'options' => [
          ['Converts it to the float 49.99', true],
          ['Raises an error because it is a string', false],
          ['Keeps it as the string "49.99"', false],
          ['Silently sets it to 0.0', false],
        ],
      ],
      [
        'q' => 'In FastAPI, how does a parameter get read from the JSON request body?',
        'explain' => 'A parameter typed as a Pydantic model is read from the request body and validated. Simple-typed params become query params; path-template names become path params.',
        'options' => [
          ['By typing the parameter as a Pydantic model', true],
          ['By naming the parameter "body"', false],
          ['By calling request.json() manually', false],
          ['By adding @body to the function', false],
        ],
      ],
      [
        'q' => 'What is the main purpose of response_model=UserOut on an endpoint?',
        'explain' => 'It defines and filters the output shape, so fields not in UserOut (like a password) are stripped from the response — preventing data leaks and documenting the schema.',
        'options' => [
          ['Filter the output to only the fields in UserOut (e.g. hide password)', true],
          ['Validate the incoming request body', false],
          ['Speed up the database query', false],
          ['Require authentication', false],
        ],
      ],
      [
        'q' => 'You need to reject a request when end_date is before start_date. Which tool fits?',
        'explain' => 'Comparing two fields requires a model validator (mode="after"), which runs once all fields are set. A field validator only sees its own field.',
        'options' => [
          ['A @model_validator(mode="after")', true],
          ['A @field_validator on end_date only', false],
          ['A response_model', false],
          ['An HTTPException in the model', false],
        ],
      ],
      [
        'q' => 'Inside a @field_validator, how do you reject an invalid value?',
        'explain' => 'Raise a ValueError with a message; FastAPI converts it to a 422. Returning the value accepts (and optionally transforms) it.',
        'options' => [
          ['raise ValueError("...")', true],
          ['return False', false],
          ['return None', false],
          ['raise HTTPException(422)', false],
        ],
      ],
      [
        'q' => 'When returning a SQLAlchemy ORM object through a response_model, what configuration is needed?',
        'explain' => 'Set model_config = {"from_attributes": True} so the Pydantic model can read attributes off the ORM object rather than expecting a dict.',
        'options' => [
          ['model_config = {"from_attributes": True}', true],
          ['response_model_exclude = True', false],
          ['Nothing — it always works', false],
          ['Convert the object to a string first', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 3 — POSTGRESQL & SQLALCHEMY ORM
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'PostgreSQL & SQLAlchemy ORM',
  'description' => 'Persist data in a real relational database. Relational concepts and PostgreSQL, mapping Python classes to tables with SQLAlchemy, CRUD queries, relationships, and schema migrations with Alembic.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Relational databases & PostgreSQL basics',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/WivyLhbR-_g',
      'content' => <<<'EOT'
<h2>Why a database?</h2>
<p>So far our APIs returned hardcoded data. Real apps must <strong>persist</strong> data reliably,
query it efficiently, and let many users access it at once. That's what a database is for. We use
<strong>PostgreSQL</strong> — a powerful, free, production-grade <strong>relational</strong> database.</p>

<h2>Tables, rows, and columns</h2>
<p>A <strong>relational database</strong> stores data in <strong>tables</strong> — like spreadsheets. Each
<strong>row</strong> is a record; each <strong>column</strong> is a typed field.</p>
<pre><code> products
 ┌────┬───────────┬────────┬───────────┐
 │ id │ name      │ price  │ in_stock  │   ← columns (typed)
 ├────┼───────────┼────────┼───────────┤
 │ 1  │ Keyboard  │ 49.99  │ true      │   ← a row (one product)
 │ 2  │ Mouse     │ 19.99  │ true      │
 └────┴───────────┴────────┴───────────┘
</code></pre>
<p>The <code>id</code> column is the <strong>primary key</strong> — a unique identifier for each row.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> A relational database is a set of linked spreadsheets where the links are
enforced and the lookups are fast. Unlike a real spreadsheet, it guarantees data integrity (types,
uniqueness, relationships) and handles thousands of simultaneous users safely.
</div>

<h2>SQL: the language of relational databases</h2>
<p>You talk to the database with <strong>SQL</strong> (Structured Query Language):</p>
<pre><code>SELECT * FROM products WHERE price &lt; 30;     -- read
INSERT INTO products (name, price) VALUES ('Cable', 9.99);
UPDATE products SET price = 24.99 WHERE id = 2;
DELETE FROM products WHERE id = 1;</code></pre>
<p>These four operations — SELECT, INSERT, UPDATE, DELETE — are the database equivalent of read,
create, update, delete (CRUD). Soon you'll generate them through SQLAlchemy instead of writing SQL by
hand, but it helps to know what's happening underneath.</p>

<h2>Relationships connect tables</h2>
<p>The "relational" power is linking tables. An order belongs to a customer; you store the customer's
id as a <strong>foreign key</strong>:</p>
<pre><code> customers                orders
 ┌────┬────────┐          ┌────┬─────────────┬────────┐
 │ id │ name   │◄─────────│ id │ customer_id │ total  │
 └────┴────────┘   FK     └────┴─────────────┴────────┘
</code></pre>
<p>A <strong>foreign key</strong> (<code>customer_id</code>) points at a primary key in another table,
enforcing that every order belongs to a real customer.</p>

<h2>Installing & running PostgreSQL</h2>
<ul>
<li><strong>Local:</strong> install Postgres (e.g. <code>brew install postgresql</code>, the official
installer, or the Postgres.app on Mac), or run it in Docker:
<code>docker run -e POSTGRES_PASSWORD=secret -p 5432:5432 postgres</code>.</li>
<li><strong>Connection string</strong> (you'll use this everywhere): <br>
<code>postgresql://user:password@host:5432/dbname</code></li>
</ul>
<p>A GUI like <strong>pgAdmin</strong>, <strong>TablePlus</strong>, or <strong>DBeaver</strong> helps you
browse tables while learning.</p>

<h2>Why PostgreSQL over SQLite for production</h2>
<p>SQLite (a single file) is great for learning and tests. PostgreSQL is the production choice:
concurrent writes, rich types (JSON, arrays), constraints, and it's what Railway/Render provision as
a managed service. You'll often develop on SQLite and deploy on Postgres — SQLAlchemy makes switching
mostly a one-line change.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>No primary key.</strong> Every table needs a unique id to reference rows reliably.</li>
<li><strong>Storing related data as duplicated columns</strong> instead of linking tables with foreign
keys (leads to inconsistency).</li>
<li><strong>Hardcoding the connection string / password</strong> in code — use environment variables
(covered in the deployment module).</li>
<li><strong>Forgetting the DB server must be running</strong> before your app can connect.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Relational databases store data in <strong>tables</strong> (rows + typed columns) with a
<strong>primary key</strong> per row.</li>
<li><strong>SQL</strong> (SELECT/INSERT/UPDATE/DELETE) reads and changes data.</li>
<li><strong>Foreign keys</strong> link tables to model relationships.</li>
<li><strong>PostgreSQL</strong> is the production database; connect via a
<code>postgresql://...</code> URL.</li>
</ul>
EOT
      . vid_box('Relational database fundamentals and getting started with PostgreSQL.', 'postgresql tutorial for beginners relational database basics'),
    ],

    [
      'title' => 'SQLAlchemy setup and models',
      'minutes' => 20,
      'video_url' => 'https://www.youtube.com/embed/XWtj4zLl_tg',
      'content' => <<<'EOT'
<h2>What is an ORM?</h2>
<p>An <strong>ORM</strong> (Object-Relational Mapper) lets you work with database rows as Python
<em>objects</em> instead of writing raw SQL. <strong>SQLAlchemy</strong> is the standard Python ORM. You
define a class; it maps to a table. You create an object; it becomes a row.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> An ORM is a translator between two languages: Python objects and SQL tables.
You speak Python (<code>user.name = "Ada"</code>); the ORM translates to SQL
(<code>UPDATE users SET name='Ada'</code>) and back. You get to stay in Python.
</div>

<h2>Install</h2>
<pre><code>pip install sqlalchemy
pip install "psycopg[binary]"     # PostgreSQL driver (psycopg 3)
# (SQLite needs no extra driver — it's built into Python)</code></pre>

<h2>Engine and session</h2>
<p>Two core objects:</p>
<ul>
<li>The <strong>engine</strong> manages the connection pool to your database.</li>
<li>A <strong>session</strong> is your workspace for a unit of work — you add/query objects, then
<code>commit()</code>.</li>
</ul>
<pre><code># database.py
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker, DeclarativeBase

DATABASE_URL = "postgresql+psycopg://user:pass@localhost:5432/shop"
# For local dev you could use: "sqlite:///./app.db"

engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

class Base(DeclarativeBase):       # base class for all models
    pass</code></pre>

<h2>Define a model</h2>
<pre><code># models.py
from sqlalchemy.orm import Mapped, mapped_column
from database import Base

class Product(Base):
    __tablename__ = "products"

    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str] = mapped_column(index=True)
    price: Mapped[float]
    in_stock: Mapped[bool] = mapped_column(default=True)
    description: Mapped[str | None] = mapped_column(default=None)</code></pre>
<p>Each <code>Mapped[...]</code> attribute is a column. The type hint sets the column type;
<code>Mapped[str | None]</code> means the column is nullable. <code>index=True</code> speeds up lookups
by that column.</p>

<h2>Create the tables</h2>
<pre><code>from database import engine, Base
import models                     # ensure models are imported/registered

Base.metadata.create_all(bind=engine)   # creates tables that don't exist yet</code></pre>
<p><code>create_all</code> is fine for getting started, but it won't <em>change</em> existing tables. For
evolving schemas you'll use Alembic migrations (a later lesson).</p>

<h2>Pydantic vs SQLAlchemy models — don\'t confuse them</h2>
<table>
<thead><tr><th>Pydantic model</th><th>SQLAlchemy model</th></tr></thead>
<tbody>
<tr><td>Validates API input/output</td><td>Maps to a database table</td></tr>
<tr><td><code>BaseModel</code></td><td><code>Base</code> / <code>DeclarativeBase</code></td></tr>
<tr><td>Lives at the API boundary</td><td>Lives at the database layer</td></tr>
</tbody>
</table>
<p>You'll have <em>both</em>: a SQLAlchemy <code>Product</code> (table) and Pydantic
<code>ProductCreate</code>/<code>ProductOut</code> (request/response). Keeping them separate is good
design, not duplication — they have different jobs.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Confusing Pydantic and SQLAlchemy models</strong> — one validates API data, the other maps
tables. You need both.</li>
<li><strong>Missing the DB driver</strong> (<code>psycopg</code>) — the engine URL needs a driver
installed.</li>
<li><strong>Relying on <code>create_all</code> to alter tables</strong> — it only creates missing ones;
schema changes need migrations.</li>
<li><strong>Forgetting <code>primary_key=True</code></strong> on the id column.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>An <strong>ORM</strong> maps Python classes to tables and objects to rows — SQLAlchemy is the standard.</li>
<li>The <strong>engine</strong> manages connections; a <strong>session</strong> is a unit of work you commit.</li>
<li>Define models with <code>Base</code> + <code>Mapped[...]</code> columns; create tables with
<code>Base.metadata.create_all</code>.</li>
<li>SQLAlchemy models (tables) are separate from Pydantic models (API) — you use both.</li>
</ul>
EOT
      . vid_box('Setting up SQLAlchemy: engine, session, Base, and your first model.', 'sqlalchemy 2.0 orm tutorial setup models'),
    ],

    [
      'title' => 'CRUD operations with SQLAlchemy',
      'minutes' => 20,
      'video_url' => 'https://www.youtube.com/embed/f0-kEG37GE0',
      'content' => <<<'EOT'
<h2>The session is your workspace</h2>
<p>All reads and writes go through a <strong>session</strong>. The pattern: open a session, do work,
<code>commit()</code> to save, and always close it. We'll wire sessions into FastAPI cleanly in the
next module; here, focus on the four operations.</p>
<pre><code>from database import SessionLocal
from models import Product

db = SessionLocal()</code></pre>

<h2>Create</h2>
<pre><code>new = Product(name="Keyboard", price=49.99)
db.add(new)            # stage it
db.commit()            # write to the database
db.refresh(new)        # reload (so new.id is populated)
print(new.id)          # e.g. 1</code></pre>
<p><code>add</code> stages the object, <code>commit</code> writes it, <code>refresh</code> reloads
DB-generated fields like the auto id.</p>

<h2>Read</h2>
<pre><code>from sqlalchemy import select

# By primary key (fast path):
product = db.get(Product, 1)              # Product or None

# Query with filters:
cheap = db.scalars(
    select(Product).where(Product.price &lt; 30)
).all()                                    # list of Products

first = db.scalars(
    select(Product).where(Product.name == "Mouse")
).first()                                  # first match or None

# Pagination:
page = db.scalars(
    select(Product).offset(0).limit(10)
).all()</code></pre>
<p><code>db.get(Model, pk)</code> fetches by primary key. For anything else, build a
<code>select(...)</code> with <code>.where(...)</code>, then <code>.all()</code> or <code>.first()</code>.</p>

<h2>Update</h2>
<pre><code>product = db.get(Product, 1)
if product:
    product.price = 39.99          # just change the attribute
    db.commit()                    # SQLAlchemy detects the change &amp; saves</code></pre>
<p>You don't write UPDATE SQL — change the object's attributes and commit. SQLAlchemy tracks what
changed.</p>

<h2>Delete</h2>
<pre><code>product = db.get(Product, 1)
if product:
    db.delete(product)
    db.commit()</code></pre>

<h2>Transactions: commit and rollback</h2>
<p>A session groups changes into a <strong>transaction</strong>. <code>commit()</code> makes them
permanent; if something fails, <code>rollback()</code> undoes everything since the last commit —
keeping data consistent.</p>
<pre><code>try:
    db.add(order)
    db.add(payment)
    db.commit()            # both saved together, or...
except Exception:
    db.rollback()          # ...neither is saved
    raise
finally:
    db.close()</code></pre>

<div class="alert alert-info" role="alert">
<strong>Commit means "save".</strong> Until you <code>commit()</code>, your changes live only in the
session. Forgetting to commit is the #1 reason "my data didn't save." And always
<code>close()</code> the session to return the connection to the pool.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting <code>commit()</code></strong> — changes vanish when the session closes.</li>
<li><strong>Not handling "not found".</strong> <code>db.get</code> / <code>.first()</code> can return
<code>None</code>; check before using (then raise a 404 in your endpoint).</li>
<li><strong>Leaking sessions.</strong> Always close them (the next module's dependency does this
automatically).</li>
<li><strong>Catching errors without <code>rollback()</code></strong> — leaves the session in a broken
state.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Create: <code>db.add()</code> → <code>db.commit()</code> → <code>db.refresh()</code>.</li>
<li>Read: <code>db.get(Model, pk)</code> or <code>db.scalars(select(...).where(...)).all()/.first()</code>.</li>
<li>Update: mutate the object's attributes, then <code>commit()</code>.</li>
<li>Delete: <code>db.delete(obj)</code> → <code>commit()</code>. Use <code>rollback()</code> on failure;
always <code>close()</code>.</li>
</ul>
EOT
      . vid_box('Performing create, read, update, and delete with SQLAlchemy sessions.', 'sqlalchemy crud operations session tutorial'),
    ],

    [
      'title' => 'Relationships: one-to-many and many-to-many',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/wvQJzMrKy9E',
      'content' => <<<'EOT'
<h2>Modelling connections between tables</h2>
<p>Real data is connected: a user has many posts; an order has many items; a post has many tags.
SQLAlchemy expresses these with <strong>foreign keys</strong> and <strong>relationships</strong>, letting
you navigate links as Python attributes.</p>

<h2>One-to-many</h2>
<p>One customer has many orders; each order belongs to one customer.</p>
<pre><code>from sqlalchemy import ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship

class Customer(Base):
    __tablename__ = "customers"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str]
    # one customer → many orders
    orders: Mapped[list["Order"]] = relationship(back_populates="customer")

class Order(Base):
    __tablename__ = "orders"
    id: Mapped[int] = mapped_column(primary_key=True)
    total: Mapped[float]
    customer_id: Mapped[int] = mapped_column(ForeignKey("customers.id"))
    # many orders → one customer
    customer: Mapped["Customer"] = relationship(back_populates="orders")</code></pre>
<p>The <code>ForeignKey</code> stores the link in the database; <code>relationship(...)</code> lets you
navigate it in Python:</p>
<pre><code>customer = db.get(Customer, 1)
for order in customer.orders:        # SQLAlchemy loads related orders
    print(order.total)

order = db.get(Order, 5)
print(order.customer.name)           # navigate the other way</code></pre>
<p><code>back_populates</code> keeps both sides in sync — set one and the other reflects it.</p>

<h2>Many-to-many</h2>
<p>A post has many tags; a tag belongs to many posts. This needs an <strong>association table</strong>:</p>
<pre><code>from sqlalchemy import Table, Column

post_tags = Table(
    "post_tags", Base.metadata,
    Column("post_id", ForeignKey("posts.id"), primary_key=True),
    Column("tag_id", ForeignKey("tags.id"), primary_key=True),
)

class Post(Base):
    __tablename__ = "posts"
    id: Mapped[int] = mapped_column(primary_key=True)
    title: Mapped[str]
    tags: Mapped[list["Tag"]] = relationship(
        secondary=post_tags, back_populates="posts")

class Tag(Base):
    __tablename__ = "tags"
    id: Mapped[int] = mapped_column(primary_key=True)
    name: Mapped[str]
    posts: Mapped[list["Post"]] = relationship(
        secondary=post_tags, back_populates="tags")</code></pre>
<pre><code>post.tags.append(tag)     # link them
db.commit()               # row added to post_tags automatically</code></pre>

<h2>Cascades: deleting a parent</h2>
<pre><code>orders: Mapped[list["Order"]] = relationship(
    back_populates="customer",
    cascade="all, delete-orphan",   # delete a customer → delete their orders
)</code></pre>
<p>Without a cascade, deleting a parent that still has children can raise a foreign-key error.</p>

<h2>The N+1 query problem</h2>
<p>Looping over parents and touching each one's children can fire one query per parent — slow. Load
related data up front with <code>selectinload</code>:</p>
<pre><code>from sqlalchemy.orm import selectinload

customers = db.scalars(
    select(Customer).options(selectinload(Customer.orders))
).all()        # orders loaded efficiently, not one-query-per-customer</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Forgetting the <code>ForeignKey</code></strong> column — <code>relationship</code> alone doesn't
create the database link.</li>
<li><strong>Mismatched <code>back_populates</code> names</strong> on the two sides.</li>
<li><strong>N+1 queries</strong> in loops — use <code>selectinload</code>/<code>joinedload</code> for
performance.</li>
<li><strong>Deleting parents without a cascade</strong> and hitting foreign-key constraint errors.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>One-to-many</strong>: a <code>ForeignKey</code> on the "many" side + <code>relationship</code> on
both, linked by <code>back_populates</code>.</li>
<li><strong>Many-to-many</strong>: an association table referenced via <code>secondary=</code>.</li>
<li>Navigate links as attributes (<code>customer.orders</code>, <code>order.customer</code>).</li>
<li>Use <code>cascade</code> for deletes and <code>selectinload</code> to avoid N+1 queries.</li>
</ul>
EOT
      . vid_box('Defining one-to-many and many-to-many relationships in SQLAlchemy.', 'sqlalchemy relationships one to many many to many tutorial'),
    ],

    [
      'title' => 'Schema migrations with Alembic',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/HuOG7VS8qvE',
      'content' => <<<'EOT'
<h2>Why migrations?</h2>
<p><code>Base.metadata.create_all</code> creates new tables but never <em>changes</em> existing ones. The
moment you add a column to a model that's already in the database, you need a way to evolve the schema
safely — without dropping data. That's a <strong>migration</strong>.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> Migrations are version control for your database schema, like Git for your
tables. Each migration is a small, ordered, reversible step ("add a <code>created_at</code> column").
You can move a database forward (upgrade) or back (downgrade), and every environment ends up with the
same structure.
</div>

<h2>Alembic: the standard tool</h2>
<pre><code>pip install alembic
alembic init alembic          # creates an alembic/ folder + alembic.ini</code></pre>
<p>Point Alembic at your database and models. In <code>alembic/env.py</code> set the URL and your
metadata so it can autogenerate:</p>
<pre><code># alembic/env.py (key parts)
from database import Base          # your DeclarativeBase
import models                      # import so all tables are registered
target_metadata = Base.metadata
# set sqlalchemy.url (often read from an env var, not hardcoded)</code></pre>

<h2>Autogenerate a migration</h2>
<p>Change a model (say, add <code>description</code> to <code>Product</code>), then:</p>
<pre><code>alembic revision --autogenerate -m "add product description"</code></pre>
<p>Alembic compares your models to the current database and writes a migration file with
<code>upgrade()</code> and <code>downgrade()</code> functions:</p>
<pre><code># alembic/versions/xxxx_add_product_description.py
def upgrade():
    op.add_column("products",
        sa.Column("description", sa.String(), nullable=True))

def downgrade():
    op.drop_column("products", "description")</code></pre>
<p><strong>Always review the generated file</strong> — autogenerate is great but not perfect (it can miss
renames or special types).</p>

<h2>Apply and revert</h2>
<pre><code>alembic upgrade head          # apply all pending migrations
alembic downgrade -1          # undo the last migration
alembic current               # which revision is the DB on?
alembic history               # list migrations in order</code></pre>
<p><code>head</code> means "the latest revision." In production you run <code>alembic upgrade head</code>
as part of deployment (covered in the deployment module).</p>

<h2>Workflow in practice</h2>
<pre><code>1. Edit a SQLAlchemy model (add/change a column)
2. alembic revision --autogenerate -m "describe the change"
3. Review the generated migration file
4. alembic upgrade head            (locally)
5. Commit the migration file to Git
6. On deploy: alembic upgrade head (on the server)
</code></pre>
<p>Because migration files are committed to Git, every teammate and every environment applies the exact
same ordered changes.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Using <code>create_all</code> in production</strong> for schema changes — it won't alter
existing tables. Use Alembic.</li>
<li><strong>Not reviewing autogenerated migrations</strong> — they can misread renames or drop/recreate
columns (data loss).</li>
<li><strong>Forgetting to commit migration files to Git</strong> — other environments fall out of sync.</li>
<li><strong>Editing an already-applied migration</strong> instead of creating a new one.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Migrations are <strong>version control for your schema</strong>; Alembic is the standard tool.</li>
<li><code>alembic revision --autogenerate -m "..."</code> generates a migration from model changes
— always review it.</li>
<li><code>alembic upgrade head</code> applies; <code>downgrade</code> reverts.</li>
<li>Commit migration files to Git and run <code>upgrade head</code> on deploy.</li>
</ul>
EOT
      . vid_box('Database schema migrations with Alembic and SQLAlchemy.', 'alembic migrations tutorial fastapi sqlalchemy'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 3 Quiz: PostgreSQL & SQLAlchemy',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What is a foreign key?',
        'explain' => 'A foreign key is a column that points to the primary key of another table, linking rows across tables and enforcing referential integrity.',
        'options' => [
          ['A column that references the primary key of another table', true],
          ['The unique identifier of a row in its own table', false],
          ['A password used to connect to the database', false],
          ['An index that speeds up queries', false],
        ],
      ],
      [
        'q' => 'In SQLAlchemy, what does an ORM let you do?',
        'explain' => 'An ORM maps Python classes to tables and objects to rows, so you work with Python objects instead of writing raw SQL.',
        'options' => [
          ['Work with database rows as Python objects instead of raw SQL', true],
          ['Run the database server', false],
          ['Replace the need for a database entirely', false],
          ['Validate HTTP request bodies', false],
        ],
      ],
      [
        'q' => 'After db.add(obj), what must you call to actually save it to the database?',
        'explain' => 'Changes live in the session until you call db.commit(), which writes them to the database.',
        'options' => [
          ['db.commit()', true],
          ['db.refresh()', false],
          ['db.close()', false],
          ['db.save()', false],
        ],
      ],
      [
        'q' => 'Which SQLAlchemy feature lets you navigate from a Customer object to its Order objects?',
        'explain' => 'relationship() (backed by a ForeignKey) lets you access related rows as Python attributes, e.g. customer.orders.',
        'options' => [
          ['relationship() with a ForeignKey', true],
          ['response_model', false],
          ['A Pydantic validator', false],
          ['create_all()', false],
        ],
      ],
      [
        'q' => 'Why use Alembic instead of Base.metadata.create_all() in a real project?',
        'explain' => 'create_all() only creates missing tables; it cannot alter existing ones. Alembic versions and applies incremental schema changes safely (and reversibly).',
        'options' => [
          ['create_all cannot change existing tables; Alembic versions and applies schema changes', true],
          ['Alembic is faster at running queries', false],
          ['create_all only works with SQLite', false],
          ['Alembic replaces SQLAlchemy', false],
        ],
      ],
      [
        'q' => 'What problem does selectinload (or joinedload) help avoid?',
        'explain' => 'The N+1 query problem: looping over parents and lazily loading each one\'s children fires many queries. Eager loading fetches related data efficiently.',
        'options' => [
          ['The N+1 query problem when accessing related rows in a loop', true],
          ['SQL injection', false],
          ['Forgetting to commit', false],
          ['Invalid request bodies', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 4 — BUILDING A FULL CRUD API
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Building a Full CRUD API',
  'description' => 'Combine FastAPI, Pydantic, and SQLAlchemy into a real, well-structured API: a sensible project layout, database sessions via dependency injection, complete CRUD endpoints, and proper error handling.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Structuring a FastAPI project',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/_kNyYIFSOFU',
      'content' => <<<'EOT'
<h2>Beyond a single main.py</h2>
<p>A real API outgrows one file. A predictable structure (clean code from the Python course) keeps it
maintainable as it grows. A common, scalable layout:</p>
<pre><code>shop-api/
├── .venv/                  # gitignored
├── .env                    # secrets (gitignored)
├── requirements.txt
├── alembic/                # migrations
└── app/
    ├── __init__.py
    ├── main.py             # creates the FastAPI app, includes routers
    ├── database.py         # engine, SessionLocal, Base, get_db
    ├── models.py           # SQLAlchemy models (tables)
    ├── schemas.py          # Pydantic models (request/response)
    ├── crud.py             # database operations (reusable functions)
    └── routers/
        ├── __init__.py
        └── products.py     # endpoints for /products</code></pre>

<div class="alert alert-info" role="alert">
<strong>Separation of concerns (SRP).</strong> Each file has one job: <code>models.py</code> = tables,
<code>schemas.py</code> = API shapes, <code>crud.py</code> = data access, <code>routers/</code> =
endpoints. This is the Single Responsibility Principle applied to a project — easy to find things,
easy to test, easy to grow.
</div>

<h2>Routers: splitting endpoints by resource</h2>
<p><code>APIRouter</code> lets you group related endpoints in their own file, then plug them into the
app:</p>
<pre><code># app/routers/products.py
from fastapi import APIRouter

router = APIRouter(prefix="/products", tags=["products"])

@router.get("/")            # becomes GET /products
def list_products():
    return []

@router.get("/{product_id}")   # becomes GET /products/{product_id}
def get_product(product_id: int):
    return {"id": product_id}</code></pre>
<p>The <code>prefix</code> is prepended to every route; <code>tags</code> group them in <code>/docs</code>.</p>

<h2>Wiring routers into the app</h2>
<pre><code># app/main.py
from fastapi import FastAPI
from app.routers import products

app = FastAPI(title="Shop API")
app.include_router(products.router)

@app.get("/health")
def health():
    return {"status": "ok"}</code></pre>
<p>Add a new resource by creating <code>routers/orders.py</code> and one
<code>app.include_router(orders.router)</code> line — the app scales cleanly.</p>

<h2>schemas.py vs models.py</h2>
<pre><code># app/schemas.py — Pydantic (API boundary)
from pydantic import BaseModel

class ProductCreate(BaseModel):
    name: str
    price: float

class ProductOut(BaseModel):
    id: int
    name: str
    price: float
    model_config = {"from_attributes": True}   # read ORM objects

# app/models.py — SQLAlchemy (database)
# class Product(Base): ...   (the table, from Module 3)</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Everything in <code>main.py</code></strong> — fine for a demo, painful for a real app. Split
by responsibility.</li>
<li><strong>Mixing tables and schemas in one file</strong> — keep <code>models.py</code> (DB) and
<code>schemas.py</code> (API) separate.</li>
<li><strong>Circular imports</strong> from poor layering — depend in one direction (routers → crud →
models).</li>
<li><strong>Forgetting <code>include_router</code></strong> — endpoints defined in a router won't appear
until the app includes it.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Split a growing API into <code>models</code> (tables), <code>schemas</code> (API shapes),
<code>crud</code> (data access), and <code>routers</code> (endpoints).</li>
<li>Use <code>APIRouter(prefix=..., tags=...)</code> to group endpoints by resource.</li>
<li>Plug routers in with <code>app.include_router(...)</code> in <code>main.py</code>.</li>
<li>This separation (SRP) keeps the project testable and easy to grow.</li>
</ul>
EOT
      . vid_box('Structuring a FastAPI project with routers, schemas, and models.', 'fastapi project structure routers bigger applications'),
    ],

    [
      'title' => 'Dependency injection and database sessions',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/Tyhtp1Ou_Pk',
      'content' => <<<'EOT'
<h2>The problem: every endpoint needs a database session</h2>
<p>Each request that touches the database needs its own session, used and then closed. Creating and
closing it by hand in every endpoint is repetitive and error-prone. FastAPI's <strong>dependency
injection</strong> solves this elegantly.</p>

<h2>What is dependency injection?</h2>
<p>A <strong>dependency</strong> is something your endpoint needs (a DB session, the current user,
settings). With <code>Depends(...)</code>, FastAPI <em>provides</em> it for you — you just declare it as
a parameter. This is the Dependency Inversion idea from the Python course, built into the framework.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> Dependency injection is like a well-run kitchen: a chef (your endpoint) asks
for "a clean, prepped station" and it's handed over ready to use, then cleared away afterward. The
chef doesn't fetch and wash everything themselves — they declare what they need and receive it.
</div>

<h2>A database-session dependency</h2>
<pre><code># app/database.py
from collections.abc import Generator

def get_db() -&gt; Generator:
    db = SessionLocal()
    try:
        yield db            # hand the session to the endpoint
    finally:
        db.close()          # ALWAYS close it afterward</code></pre>
<p>This generator opens a session, <code>yield</code>s it to the endpoint, and — crucially — closes it
in <code>finally</code> when the request finishes, even if an error was raised. One place handles
cleanup for every endpoint.</p>

<h2>Using it in endpoints</h2>
<pre><code>from fastapi import Depends
from sqlalchemy.orm import Session
from app.database import get_db
from app import models

@router.get("/{product_id}")
def get_product(product_id: int, db: Session = Depends(get_db)):
    product = db.get(models.Product, product_id)
    return product</code></pre>
<p><code>db: Session = Depends(get_db)</code> tells FastAPI to run <code>get_db</code>, inject the session,
and clean up after. Every DB endpoint gets a fresh, properly-closed session with one line.</p>

<h2>A cleaner pattern with Annotated</h2>
<p>To avoid repeating <code>Depends(get_db)</code> everywhere, alias it once:</p>
<pre><code>from typing import Annotated

DbSession = Annotated[Session, Depends(get_db)]

@router.get("/{product_id}")
def get_product(product_id: int, db: DbSession):
    return db.get(models.Product, product_id)</code></pre>

<h2>Dependencies compose</h2>
<p>Dependencies can depend on other dependencies. Soon you'll write a <code>get_current_user</code>
dependency that itself uses <code>get_db</code> — and protecting an endpoint becomes just adding a
parameter. This composability is what makes FastAPI auth so clean (next module).</p>
<pre><code># preview of the auth module:
@router.post("/products")
def create_product(
    product: schemas.ProductCreate,
    db: DbSession,
    user: User = Depends(get_current_user),   # now this route requires login
):
    ...</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Creating <code>SessionLocal()</code> manually in each endpoint</strong> and forgetting to
close it — use the <code>get_db</code> dependency.</li>
<li><strong>Using <code>return</code> instead of <code>yield</code></strong> in <code>get_db</code> — you
need <code>yield</code> so cleanup runs after the response.</li>
<li><strong>Sharing one global session across requests</strong> — each request needs its own.</li>
<li><strong>Doing cleanup outside <code>finally</code></strong> — an exception would skip the close.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>Dependency injection</strong> (<code>Depends</code>) provides what an endpoint needs and
handles setup/teardown.</li>
<li>A <code>get_db</code> generator <code>yield</code>s a session and closes it in <code>finally</code>
— one place, every request.</li>
<li>Inject with <code>db: Session = Depends(get_db)</code> (or an <code>Annotated</code> alias).</li>
<li>Dependencies <strong>compose</strong>, which makes auth and other cross-cutting concerns clean.</li>
</ul>
EOT
      . vid_box('FastAPI dependency injection and providing a database session per request.', 'fastapi dependency injection depends database session tutorial'),
    ],

    [
      'title' => 'Building the CRUD endpoints',
      'minutes' => 20,
      'video_url' => 'https://www.youtube.com/embed/HxRKUlb4qqk',
      'content' => <<<'EOT'
<h2>Putting it all together</h2>
<p>Now we combine everything: SQLAlchemy models (tables), Pydantic schemas (API shapes), the
<code>get_db</code> dependency, and routers — into a complete set of CRUD endpoints for products.</p>

<h2>A reusable crud layer (optional but clean)</h2>
<pre><code># app/crud.py
from sqlalchemy import select
from sqlalchemy.orm import Session
from app import models, schemas

def get_product(db: Session, product_id: int):
    return db.get(models.Product, product_id)

def list_products(db: Session, skip=0, limit=100):
    return db.scalars(select(models.Product).offset(skip).limit(limit)).all()

def create_product(db: Session, data: schemas.ProductCreate):
    product = models.Product(**data.model_dump())
    db.add(product)
    db.commit()
    db.refresh(product)
    return product</code></pre>
<p>Keeping data access in <code>crud.py</code> means endpoints stay thin and the queries are reusable
and testable.</p>

<h2>The router: full CRUD</h2>
<pre><code># app/routers/products.py
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from app.database import get_db
from app import crud, schemas

router = APIRouter(prefix="/products", tags=["products"])

@router.post("/", response_model=schemas.ProductOut,
             status_code=status.HTTP_201_CREATED)
def create(product: schemas.ProductCreate, db: Session = Depends(get_db)):
    return crud.create_product(db, product)

@router.get("/", response_model=list[schemas.ProductOut])
def list_all(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    return crud.list_products(db, skip, limit)

@router.get("/{product_id}", response_model=schemas.ProductOut)
def get_one(product_id: int, db: Session = Depends(get_db)):
    product = crud.get_product(db, product_id)
    if product is None:
        raise HTTPException(404, "Product not found")
    return product</code></pre>

<h2>Update and delete</h2>
<pre><code>@router.put("/{product_id}", response_model=schemas.ProductOut)
def update(product_id: int, data: schemas.ProductCreate,
           db: Session = Depends(get_db)):
    product = crud.get_product(db, product_id)
    if product is None:
        raise HTTPException(404, "Product not found")
    for key, value in data.model_dump().items():
        setattr(product, key, value)     # apply each field
    db.commit()
    db.refresh(product)
    return product

@router.delete("/{product_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete(product_id: int, db: Session = Depends(get_db)):
    product = crud.get_product(db, product_id)
    if product is None:
        raise HTTPException(404, "Product not found")
    db.delete(product)
    db.commit()
    # 204 → no response body</code></pre>

<h2>The full flow of one request</h2>
<pre><code>POST /products  {"name":"Keyboard","price":49.99}
   │
   ▼ Pydantic ProductCreate validates the body
   ▼ get_db injects a session
   ▼ crud.create_product → db.add / commit / refresh
   ▼ response_model=ProductOut filters the output
   ▼ 201 Created  {"id":1,"name":"Keyboard","price":49.99}
</code></pre>

<div class="alert alert-info" role="alert">
<strong>Notice each layer's job.</strong> Pydantic guards the boundary, the dependency supplies the
session, crud talks to the database, and the response model shapes the output. Every concept from the
last three modules working together — this is a production-shaped API.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Returning ORM objects without <code>from_attributes</code></strong> in the response schema →
serialization error.</li>
<li><strong>Forgetting the 404 check</strong> before update/delete — operating on <code>None</code>
crashes with a 500.</li>
<li><strong>Skipping <code>response_model</code></strong> and leaking internal fields.</li>
<li><strong>Fat endpoints</strong> with raw queries inline — push data access into <code>crud.py</code>.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>A full resource = create (201) / list / get (404 if missing) / update / delete (204).</li>
<li>Keep queries in a <strong>crud layer</strong> so endpoints stay thin and testable.</li>
<li>Use <code>response_model</code> on every endpoint to shape and secure output.</li>
<li>Always check for "not found" and raise <code>HTTPException(404)</code>.</li>
</ul>
EOT
      . vid_box('Building complete CRUD endpoints with FastAPI and SQLAlchemy.', 'fastapi sqlalchemy crud rest api full tutorial'),
    ],

    [
      'title' => 'Error handling and validation in APIs',
      'minutes' => 14,
      'video_url' => 'https://www.youtube.com/embed/7MHDDOrDx-w',
      'content' => <<<'EOT'
<h2>Good APIs fail clearly</h2>
<p>A professional API never crashes with a raw stack trace or a vague 500. It returns the right status
code and a helpful message the client can act on. You've met the tools (HTTPException, Pydantic
validation); here's how to use them well.</p>

<h2>Consistent error responses</h2>
<p>FastAPI's <code>HTTPException</code> produces a consistent JSON shape:</p>
<pre><code>raise HTTPException(status_code=404, detail="Product not found")
# → 404  {"detail": "Product not found"}

raise HTTPException(
    status_code=status.HTTP_409_CONFLICT,
    detail="A product with that name already exists",
)</code></pre>
<p>Pick the code that matches the situation: 400 (bad request), 401 (not authenticated), 403
(forbidden), 404 (not found), 409 (conflict/duplicate), 422 (validation — FastAPI raises this
automatically).</p>

<h2>Validation errors are already handled</h2>
<p>Thanks to Pydantic, malformed input returns a detailed 422 <em>before</em> your code runs — listing
each bad field and why. You don't write these checks; you just design good models.</p>

<h2>Catch database integrity errors</h2>
<p>Some rules live in the database (unique constraints, foreign keys). Catch the error and translate it
into a clean HTTP response:</p>
<pre><code>from sqlalchemy.exc import IntegrityError

@router.post("/users", response_model=schemas.UserOut, status_code=201)
def create_user(user: schemas.UserCreate, db: Session = Depends(get_db)):
    db_user = models.User(**user.model_dump())
    db.add(db_user)
    try:
        db.commit()
    except IntegrityError:
        db.rollback()                       # important!
        raise HTTPException(409, "Email already registered")
    db.refresh(db_user)
    return db_user</code></pre>
<p>Note the <code>rollback()</code> — after a failed commit the session must be rolled back before reuse.</p>

<h2>Custom exception handlers (app-wide)</h2>
<p>For your own exception types, register a handler once and raise the exception anywhere:</p>
<pre><code>from fastapi import Request
from fastapi.responses import JSONResponse

class ResourceNotFound(Exception):
    def __init__(self, name: str):
        self.name = name

@app.exception_handler(ResourceNotFound)
def handle_not_found(request: Request, exc: ResourceNotFound):
    return JSONResponse(status_code=404,
                        content={"detail": f"{exc.name} not found"})

# anywhere in your code:
raise ResourceNotFound("Product")</code></pre>
<p>This keeps endpoints clean and error formatting consistent across the whole API.</p>

<div class="alert alert-info" role="alert">
<strong>Never leak internals.</strong> Don't send raw exception text or stack traces to clients — they
can expose your structure and secrets. Log the details server-side; return a safe, generic message
with the right status code.
</div>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Returning 200 with an error body.</strong> Use the correct status code so clients can react.</li>
<li><strong>No <code>rollback()</code> after a failed commit</strong> — leaves the session unusable.</li>
<li><strong>Leaking stack traces</strong> to clients — log server-side, return safe messages.</li>
<li><strong>Re-implementing validation</strong> the model already does — trust Pydantic's 422.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Use <code>HTTPException</code> with the correct status code and a clear <code>detail</code>.</li>
<li>Pydantic gives you automatic <strong>422</strong> validation errors for free.</li>
<li>Catch DB errors (e.g. <code>IntegrityError</code>), <code>rollback()</code>, and translate them to
clean responses (409, etc.).</li>
<li>Register custom exception handlers for consistency; never leak internals to clients.</li>
</ul>
EOT
      . vid_box('Error handling, HTTPException, and exception handlers in FastAPI.', 'fastapi error handling exception handlers tutorial'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 4 Quiz: Full CRUD API',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What is the role of APIRouter in a FastAPI project?',
        'explain' => 'APIRouter groups related endpoints (often per resource) in their own module, which you then plug into the app with include_router — keeping the project organized and scalable.',
        'options' => [
          ['Group related endpoints into a module included via app.include_router', true],
          ['Connect to the database', false],
          ['Validate request bodies', false],
          ['Generate migrations', false],
        ],
      ],
      [
        'q' => 'Why does the get_db dependency use yield instead of return?',
        'explain' => 'yield lets FastAPI run teardown code (closing the session in finally) after the response is sent. A plain return could not guarantee cleanup.',
        'options' => [
          ['So the session can be closed in finally after the request completes', true],
          ['Because return is not allowed in FastAPI', false],
          ['To make the endpoint asynchronous', false],
          ['To cache the session globally', false],
        ],
      ],
      [
        'q' => 'How do you give an endpoint a database session?',
        'explain' => 'Declare a parameter like db: Session = Depends(get_db). FastAPI injects a fresh session and cleans it up afterward.',
        'options' => [
          ['db: Session = Depends(get_db)', true],
          ['db = SessionLocal() at module top level', false],
          ['Import the session as a global', false],
          ['Pass it in the URL', false],
        ],
      ],
      [
        'q' => 'Before updating or deleting a record fetched by id, what should you check?',
        'explain' => 'db.get can return None. Check for None and raise HTTPException(404); otherwise operating on None causes a 500 error.',
        'options' => [
          ['Whether it is None, and raise HTTPException(404) if so', true],
          ['Whether the server is async', false],
          ['Whether response_model is set', false],
          ['Nothing — SQLAlchemy handles it', false],
        ],
      ],
      [
        'q' => 'After a failed db.commit() that raised IntegrityError, what must you do before reusing the session?',
        'explain' => 'Call db.rollback() to return the session to a usable state, then translate the error into a clean HTTP response (e.g. 409).',
        'options' => [
          ['Call db.rollback()', true],
          ['Call db.commit() again immediately', false],
          ['Ignore it and continue', false],
          ['Restart the server', false],
        ],
      ],
      [
        'q' => 'Which status code best fits a successful DELETE with no response body?',
        'explain' => '204 No Content indicates success with nothing to return — ideal for deletes.',
        'options' => [
          ['204 No Content', true],
          ['200 OK with the deleted object', false],
          ['201 Created', false],
          ['404 Not Found', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 5 — AUTHENTICATION: JWT & OAUTH2
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Authentication: JWT & OAuth2',
  'description' => 'Secure your API. The difference between authentication and authorization, hashing passwords safely, issuing and verifying JWT tokens with the OAuth2 password flow, protecting routes with a current-user dependency, and role-based access.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Authentication vs authorization, and how tokens work',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/Y2H3DXDeS3Q',
      'content' => <<<'EOT'
<h2>Two different questions</h2>
<ul>
<li><strong>Authentication</strong> = <em>who are you?</em> Proving identity (logging in).</li>
<li><strong>Authorization</strong> = <em>what are you allowed to do?</em> Permissions (can this user
delete that product?).</li>
</ul>
<p>You authenticate first, then authorize. Mixing them up is a classic source of security bugs (and
the 401-vs-403 confusion from Module 1: 401 = not authenticated, 403 = authenticated but not
permitted).</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> Authentication is showing your passport at the airport (proving who you are).
Authorization is your boarding pass deciding which lounge and seat you can access. The passport
doesn't get you into business class — that's a separate permission.
</div>

<h2>HTTP is stateless — so how do we stay logged in?</h2>
<p>Each HTTP request is independent; the server doesn't "remember" you between requests. After you log
in, the server gives you a <strong>token</strong>, and you send it with every subsequent request to
prove who you are.</p>
<pre><code>1. POST /login   (username + password)
        │
        ▼  server verifies credentials, returns a TOKEN
2. GET /me      Authorization: Bearer &lt;token&gt;
        │
        ▼  server verifies the token, knows it's you — no re-login
</code></pre>

<h2>What is a JWT?</h2>
<p>A <strong>JWT</strong> (JSON Web Token) is a compact, signed token that carries a small JSON payload
(the "claims"). It has three dot-separated parts:</p>
<pre><code>header.payload.signature
eyJhbGc...  .  eyJzdWIiOiIxMi..  .  SflKxw...
</code></pre>
<ul>
<li><strong>Header</strong> — the algorithm used.</li>
<li><strong>Payload</strong> — claims like <code>sub</code> (the user) and <code>exp</code> (expiry).</li>
<li><strong>Signature</strong> — a cryptographic signature made with your <em>secret key</em>.</li>
</ul>

<h2>The key insight: signed, not encrypted</h2>
<p>The signature lets the server verify the token <em>hasn't been tampered with</em> and that <em>it
issued the token</em> — without storing anything. But the payload is only Base64-encoded, <strong>not
encrypted</strong>: anyone can read it.</p>
<div class="alert alert-warning" role="alert">
<strong>Never put secrets in a JWT payload.</strong> No passwords, no sensitive personal data — the
payload is readable by anyone who has the token. Put only an identifier (like the user id) and
non-sensitive claims.
</div>

<h2>Why tokens (vs server sessions)</h2>
<ul>
<li><strong>Stateless:</strong> the server verifies the signature; it needn't store sessions, which
scales well across multiple servers.</li>
<li><strong>Portable:</strong> the same token works for web, mobile, and other services.</li>
<li><strong>Expiring:</strong> the <code>exp</code> claim limits how long a stolen token is useful.</li>
</ul>
<p>The trade-off: you can't easily "un-issue" a JWT before it expires, so keep lifetimes short (and use
refresh tokens for long sessions — an advanced topic).</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Confusing authentication and authorization</strong> — they're separate steps (401 vs 403).</li>
<li><strong>Thinking a JWT is encrypted</strong> — it's signed; the payload is readable. No secrets in it.</li>
<li><strong>Tokens that never expire</strong> — always set <code>exp</code>; a leaked eternal token is a
disaster.</li>
<li><strong>Sending the token in the URL</strong> — put it in the <code>Authorization</code> header, not
query strings (which get logged).</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>Authentication</strong> = who you are; <strong>authorization</strong> = what you may do.</li>
<li>After login the server issues a <strong>token</strong> sent as <code>Authorization: Bearer ...</code>
on each request (HTTP is stateless).</li>
<li>A <strong>JWT</strong> is a signed (not encrypted) token: header.payload.signature — readable, so no
secrets inside.</li>
<li>Tokens are stateless and portable; always set an <strong>expiry</strong>.</li>
</ul>
EOT
      . vid_box('How authentication, tokens, and JWTs work (conceptual overview).', 'jwt authentication explained tokens vs sessions'),
    ],

    [
      'title' => 'Hashing passwords safely',
      'minutes' => 14,
      'video_url' => 'https://www.youtube.com/embed/hNa05wr0DSA',
      'content' => <<<'EOT'
<h2>Never store passwords as plain text</h2>
<p>If your database is ever leaked and passwords are stored in plain text, every user is compromised —
and people reuse passwords everywhere. The rule is absolute: <strong>never store the actual
password.</strong> Store a <strong>hash</strong> of it.</p>

<h2>What is hashing?</h2>
<p>A <strong>hash function</strong> turns input into a fixed-length string and is <strong>one-way</strong>:
you can't reverse the hash back into the password. To check a login, you hash the submitted password
and compare hashes.</p>
<pre><code>"hunter2"  ──hash──►  "$2b$12$Q7...long...gibberish"
                       (stored; cannot be reversed)
</code></pre>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> Hashing is like blending a smoothie: easy to make from the fruit, impossible
to turn back into the original fruit. You verify someone by blending the same fruit the same way and
checking you get the same smoothie — you never store the fruit.
</div>

<h2>Use a slow, salted password hash — not SHA-256</h2>
<p>General-purpose hashes (MD5, SHA-256) are <em>too fast</em> — attackers can try billions per second.
For passwords use a deliberately <strong>slow</strong>, <strong>salted</strong> algorithm like
<strong>bcrypt</strong> or <strong>argon2</strong>. A <strong>salt</strong> is random data mixed in so two
users with the same password get different hashes (defeating precomputed "rainbow table" attacks) —
bcrypt handles the salt for you.</p>

<h2>In practice</h2>
<pre><code>pip install "passlib[bcrypt]"</code></pre>
<pre><code># app/security.py
from passlib.context import CryptContext

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

def hash_password(plain: str) -&gt; str:
    return pwd_context.hash(plain)

def verify_password(plain: str, hashed: str) -&gt; bool:
    return pwd_context.verify(plain, hashed)</code></pre>

<h2>On signup and login</h2>
<pre><code># Signup: store ONLY the hash
user = models.User(
    email=data.email,
    hashed_password=hash_password(data.password),  # never store data.password
)

# Login: verify the submitted password against the stored hash
if not verify_password(submitted_password, user.hashed_password):
    raise HTTPException(401, "Incorrect email or password")</code></pre>
<p>Notice the column is named <code>hashed_password</code> — and (from Module 2) your
<code>UserOut</code> response model must <em>never</em> include it.</p>

<h2>Don\'t leak which part was wrong</h2>
<p>On a failed login, say "Incorrect email or password" — not "no such email" or "wrong password."
Revealing which one helps attackers enumerate valid accounts.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Storing plain-text passwords</strong> — never, under any circumstances.</li>
<li><strong>Using fast hashes (MD5/SHA-256) for passwords</strong> — use bcrypt/argon2.</li>
<li><strong>Returning the password hash</strong> in API responses — exclude it via the response model.</li>
<li><strong>Telling the user which field was wrong</strong> on login — use one generic message.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Store a <strong>one-way hash</strong> of the password, never the password itself.</li>
<li>Use a slow, salted algorithm (<strong>bcrypt</strong>/argon2) — not MD5/SHA-256.</li>
<li>Verify logins by hashing the input and comparing (<code>passlib</code> does this).</li>
<li>Never return the hash; use a generic "incorrect email or password" message.</li>
</ul>
EOT
      . vid_box('Why and how to hash passwords with bcrypt in Python.', 'password hashing bcrypt python passlib tutorial'),
    ],

    [
      'title' => 'OAuth2 password flow and issuing JWTs',
      'minutes' => 20,
      'video_url' => 'https://www.youtube.com/embed/Go4wYJJhR3k',
      'content' => <<<'EOT'
<h2>FastAPI\'s built-in OAuth2 support</h2>
<p>FastAPI ships helpers for the <strong>OAuth2 password flow</strong> — the standard "send username and
password, get back a token" pattern. It also wires into the <code>/docs</code> "Authorize" button.</p>
<pre><code>pip install "python-jose[cryptography]" "passlib[bcrypt]" python-multipart</code></pre>
<p>(<code>python-jose</code> creates/verifies JWTs; <code>python-multipart</code> is needed for the form
login.)</p>

<h2>Config: secret key and expiry</h2>
<pre><code># app/security.py
from datetime import datetime, timedelta, timezone
from jose import jwt

SECRET_KEY = "...load from an env var, never hardcode in real apps..."
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

def create_access_token(data: dict) -&gt; str:
    to_encode = data.copy()
    expire = datetime.now(timezone.utc) + timedelta(
        minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    to_encode.update({"exp": expire})
    return jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)</code></pre>
<div class="alert alert-warning" role="alert">
<strong>The SECRET_KEY is the master key.</strong> Anyone who has it can forge valid tokens for any
user. Generate a long random value (<code>openssl rand -hex 32</code>), load it from an environment
variable, and never commit it to Git.
</div>

<h2>The token endpoint</h2>
<pre><code>from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from app.security import verify_password, create_access_token

@router.post("/token")
def login(form: OAuth2PasswordRequestForm = Depends(),
          db: Session = Depends(get_db)):
    user = get_user_by_email(db, form.username)   # OAuth2 calls it "username"
    if not user or not verify_password(form.password, user.hashed_password):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password",
            headers={"WWW-Authenticate": "Bearer"},
        )
    token = create_access_token(data={"sub": str(user.id)})
    return {"access_token": token, "token_type": "bearer"}</code></pre>
<p><code>OAuth2PasswordRequestForm</code> reads the username/password from form data. On success we put the
user id in the <code>sub</code> ("subject") claim and return the JWT in the standard
<code>{"access_token", "token_type"}</code> shape.</p>

<h2>What the client does next</h2>
<pre><code>POST /token   (form: username=ada@x.io&amp;password=secret)
  → {"access_token": "eyJ...", "token_type": "bearer"}

# then on every protected request:
GET /me   Authorization: Bearer eyJ...</code></pre>

<h2>The flow end to end</h2>
<pre><code>Client                         Server
  │  POST /token (user+pass)     │
  │ ───────────────────────────► │ verify_password()
  │                              │ create_access_token() → JWT
  │ �„ access_token �„◄────────── │
  │                              │
  │  GET /me  Bearer &lt;jwt&gt;        │
  │ ───────────────────────────► │ decode + verify signature & exp
  │ ◄────────── user data ────── │ → it's user 12
</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Hardcoding <code>SECRET_KEY</code></strong> or committing it — load from env; rotating it
invalidates all tokens.</li>
<li><strong>No <code>exp</code> claim</strong> — tokens must expire.</li>
<li><strong>Forgetting <code>python-multipart</code></strong> — the form login fails without it.</li>
<li><strong>Putting sensitive data in claims</strong> — only an id and non-sensitive info.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>The <strong>OAuth2 password flow</strong>: POST credentials to <code>/token</code>, receive a JWT.</li>
<li>Use <code>OAuth2PasswordRequestForm</code> for the login, verify the password, then
<code>create_access_token</code> with a <code>sub</code> and <code>exp</code>.</li>
<li>Return <code>{"access_token", "token_type": "bearer"}</code>; clients send
<code>Authorization: Bearer ...</code>.</li>
<li>Keep <code>SECRET_KEY</code> in an env var; always set token expiry.</li>
</ul>
EOT
      . vid_box('Implementing OAuth2 password flow and JWT creation in FastAPI.', 'fastapi oauth2 jwt authentication tutorial'),
    ],

    [
      'title' => 'Protecting routes with a current-user dependency',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/MY0TFMMm9B0',
      'content' => <<<'EOT'
<h2>Turning a token back into a user</h2>
<p>Issuing tokens is half the job; now we <em>verify</em> them on protected routes. The elegant FastAPI
way is a <code>get_current_user</code> <strong>dependency</strong> (Module 4) that reads the token,
validates it, and loads the user — so protecting any endpoint is just adding a parameter.</p>

<h2>The OAuth2 scheme</h2>
<pre><code>from fastapi.security import OAuth2PasswordBearer

# tells FastAPI where to get a token (and powers the /docs Authorize button)
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")</code></pre>
<p><code>OAuth2PasswordBearer</code> extracts the token from the <code>Authorization: Bearer</code> header
for you.</p>

<h2>The current-user dependency</h2>
<pre><code>from fastapi import Depends, HTTPException, status
from jose import jwt, JWTError
from app.security import SECRET_KEY, ALGORITHM

def get_current_user(
    token: str = Depends(oauth2_scheme),
    db: Session = Depends(get_db),
):
    credentials_error = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Could not validate credentials",
        headers={"WWW-Authenticate": "Bearer"},
    )
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        user_id = payload.get("sub")
        if user_id is None:
            raise credentials_error
    except JWTError:                 # bad signature, expired, malformed
        raise credentials_error

    user = db.get(models.User, int(user_id))
    if user is None:
        raise credentials_error
    return user</code></pre>
<p><code>jwt.decode</code> verifies the signature and expiry in one step (raising <code>JWTError</code> on
any problem). We read the <code>sub</code> claim, load that user, and return it.</p>

<div class="alert alert-info" role="alert">
<strong>This is dependency composition.</strong> <code>get_current_user</code> itself depends on
<code>oauth2_scheme</code> and <code>get_db</code>. FastAPI resolves the whole chain for you — exactly
the Dependency Inversion idea, and it's why protecting a route is a one-liner.
</div>

<h2>Protect an endpoint</h2>
<pre><code>from typing import Annotated

CurrentUser = Annotated[models.User, Depends(get_current_user)]

@router.get("/me", response_model=schemas.UserOut)
def read_me(current_user: CurrentUser):
    return current_user            # only reached if the token is valid

@router.post("/products", response_model=schemas.ProductOut, status_code=201)
def create_product(product: schemas.ProductCreate,
                   db: DbSession, current_user: CurrentUser):
    # this route now REQUIRES a valid token
    return crud.create_product(db, product)</code></pre>
<p>Add <code>current_user: CurrentUser</code> and the route is protected. No token (or a bad/expired one)
→ automatic 401, and your code never runs. You also now know <em>who</em> is acting, e.g. to set
<code>owner_id=current_user.id</code>.</p>

<h2>Testing it in /docs</h2>
<p>The <code>/docs</code> page shows an <strong>Authorize</strong> button. Log in there once and Swagger
sends the Bearer token on every "Try it out" — great for manual testing.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Verifying the token manually in each endpoint</strong> — centralize it in
<code>get_current_user</code>.</li>
<li><strong>Not catching <code>JWTError</code></strong> — expired/tampered tokens would crash with 500
instead of a clean 401.</li>
<li><strong>Trusting the payload without verifying the signature</strong> — always
<code>jwt.decode</code> with the secret; never just read claims.</li>
<li><strong>Returning the user with the password hash</strong> — use <code>UserOut</code>.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>A <code>get_current_user</code> dependency decodes/validates the JWT and loads the user.</li>
<li>Protect any route by adding <code>current_user: CurrentUser</code> — invalid token → automatic 401.</li>
<li><code>jwt.decode</code> verifies signature <em>and</em> expiry; catch <code>JWTError</code> → 401.</li>
<li>Dependencies compose, so auth is reusable and one line per endpoint.</li>
</ul>
EOT
      . vid_box('Protecting FastAPI routes with a get_current_user JWT dependency.', 'fastapi get current user jwt protected routes tutorial'),
    ],

    [
      'title' => 'Roles, scopes, and authorization',
      'minutes' => 14,
      'video_url' => 'https://www.youtube.com/embed/_k2M-LpxId8',
      'content' => <<<'EOT'
<h2>From "who" to "what they can do"</h2>
<p>Authentication tells you <em>who</em> the user is. <strong>Authorization</strong> decides what they're
allowed to do. A logged-in user can edit their own profile; only an admin can delete any product.
That's a permission check on top of authentication.</p>

<h2>Role-based access</h2>
<p>The simplest model: give each user a <strong>role</strong> and check it.</p>
<pre><code>class User(Base):
    __tablename__ = "users"
    id: Mapped[int] = mapped_column(primary_key=True)
    email: Mapped[str]
    hashed_password: Mapped[str]
    role: Mapped[str] = mapped_column(default="user")  # "user" or "admin"</code></pre>

<h2>An admin-only dependency</h2>
<p>Build on <code>get_current_user</code> to make a stricter dependency:</p>
<pre><code>from fastapi import Depends, HTTPException, status

def require_admin(current_user: CurrentUser):
    if current_user.role != "admin":
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,   # 403, not 401!
            detail="Admin access required",
        )
    return current_user

AdminUser = Annotated[models.User, Depends(require_admin)]

@router.delete("/products/{product_id}", status_code=204)
def delete_product(product_id: int, db: DbSession, admin: AdminUser):
    # only admins reach here
    ...</code></pre>
<p>Note the code: a logged-in non-admin gets <strong>403 Forbidden</strong> (authenticated but not
permitted), not 401 (not authenticated).</p>

<div class="alert alert-info" role="alert">
<strong>Layered dependencies = layered security.</strong> <code>require_admin</code> depends on
<code>get_current_user</code>, which depends on the token and the DB. Each layer adds a check, and you
attach exactly the level a route needs. This composition is the clean way to express permissions.
</div>

<h2>Ownership checks</h2>
<p>Often the rule isn't a global role but "you can only modify your own resources":</p>
<pre><code>@router.put("/posts/{post_id}")
def update_post(post_id: int, data: schemas.PostUpdate,
                db: DbSession, current_user: CurrentUser):
    post = db.get(models.Post, post_id)
    if post is None:
        raise HTTPException(404, "Post not found")
    if post.owner_id != current_user.id:
        raise HTTPException(403, "Not your post")    # authorization
    ...</code></pre>

<h2>Scopes (a quick note)</h2>
<p>OAuth2 <strong>scopes</strong> are fine-grained permissions attached to a token (e.g.
<code>products:read</code>, <code>products:write</code>). FastAPI supports them via
<code>Security(..., scopes=[...])</code>. Roles are simpler and enough for most apps; reach for scopes
when different clients need different slices of access.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Using 401 for permission failures</strong> — an authenticated-but-not-allowed user gets
<strong>403</strong>.</li>
<li><strong>Checking roles only in the UI</strong> — the API must enforce it; clients can be bypassed.</li>
<li><strong>Forgetting ownership checks</strong> — "logged in" doesn't mean "allowed to edit
<em>this</em> record."</li>
<li><strong>Scattering permission logic</strong> in endpoints instead of reusable dependencies.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>Authorization</strong> checks permissions <em>after</em> authentication.</li>
<li>Model roles on the user and enforce them with dependencies like <code>require_admin</code>.</li>
<li>Permission failures return <strong>403 Forbidden</strong> (vs 401 for not authenticated).</li>
<li>Enforce <strong>ownership</strong> for per-resource rules; the API — not the UI — is the source of
truth.</li>
</ul>
EOT
      . vid_box('Role-based access control and authorization in FastAPI.', 'fastapi role based access control authorization dependency'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 5 Quiz: Authentication',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'What is the difference between authentication and authorization?',
        'explain' => 'Authentication proves who you are (login); authorization decides what you are allowed to do (permissions). 401 = not authenticated, 403 = not authorized.',
        'options' => [
          ['Authentication = who you are; authorization = what you may do', true],
          ['They are two words for the same thing', false],
          ['Authentication = permissions; authorization = identity', false],
          ['Authorization always happens before authentication', false],
        ],
      ],
      [
        'q' => 'Is the payload of a JWT encrypted?',
        'explain' => 'No. A JWT is signed (to detect tampering), but the payload is only Base64-encoded and readable by anyone. Never put secrets in it.',
        'options' => [
          ['No — it is signed, not encrypted, and is readable', true],
          ['Yes — it is fully encrypted and unreadable', false],
          ['Only the signature is readable', false],
          ['It depends on the database', false],
        ],
      ],
      [
        'q' => 'How should passwords be stored?',
        'explain' => 'Store a one-way, salted, slow hash (bcrypt/argon2). Never store plain text, and avoid fast hashes like MD5/SHA-256 for passwords.',
        'options' => [
          ['As a salted bcrypt/argon2 hash, never plain text', true],
          ['As plain text for easy comparison', false],
          ['Encrypted with the JWT secret', false],
          ['As a fast SHA-256 hash', false],
        ],
      ],
      [
        'q' => 'In the OAuth2 password flow, what does the /token endpoint return on success?',
        'explain' => 'It returns a JSON object with the JWT: {"access_token": "...", "token_type": "bearer"}. Clients then send Authorization: Bearer <token>.',
        'options' => [
          ['{"access_token": "...", "token_type": "bearer"}', true],
          ['The user\'s plain-text password', false],
          ['A session cookie only', false],
          ['The SECRET_KEY', false],
        ],
      ],
      [
        'q' => 'How do you protect a FastAPI endpoint so it requires a valid token?',
        'explain' => 'Add a dependency parameter like current_user = Depends(get_current_user). If the token is missing/invalid, FastAPI returns 401 and your code never runs.',
        'options' => [
          ['Add a parameter that Depends(get_current_user)', true],
          ['Check the password again inside the endpoint', false],
          ['Put the token in the URL path', false],
          ['Set response_model on the route', false],
        ],
      ],
      [
        'q' => 'A logged-in non-admin user calls an admin-only DELETE endpoint. Which status code should they get?',
        'explain' => '403 Forbidden — they are authenticated but lack permission. 401 would imply they are not authenticated at all.',
        'options' => [
          ['403 Forbidden', true],
          ['401 Unauthorized', false],
          ['200 OK', false],
          ['404 Not Found', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 6 — GIT WORKFLOWS & CI/CD BASICS
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Git Workflows & CI/CD Basics',
  'description' => 'Collaborate and ship safely. Practical Git branching and pull-request workflow, writing automated tests for your FastAPI app, and setting up continuous integration and delivery with GitHub Actions.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Git workflow essentials: branches and pull requests',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/oFYyTZwMyAg',
      'content' => <<<'EOT'
<h2>Why a workflow, not just commits</h2>
<p>You know <code>git add/commit/push</code>. A <strong>workflow</strong> is the team agreement for how
changes flow into the main codebase safely — so <code>main</code> always works and changes are
reviewed. The most common is the <strong>feature-branch + pull-request</strong> workflow.</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> <code>main</code> is the published edition of a book that readers (and your
deployment) rely on. You never scribble in the published copy. You photocopy a chapter (a branch),
edit it, have an editor review it (a pull request), and only then merge the changes into the
published edition.
</div>

<h2>The feature-branch flow</h2>
<pre><code>main ──●──●──────────────●(merge)──►   always working / deployable
        \                /
 feature ●──●──●──●──●──/   your isolated work
</code></pre>
<pre><code>git switch -c feature/add-orders     # create + switch to a branch
# ...make changes, commit as you go...
git add .
git commit -m "Add order endpoints"
git push -u origin feature/add-orders</code></pre>
<p>Your branch is isolated: you can experiment without breaking <code>main</code> or other people's
work.</p>

<h2>Pull requests (PRs)</h2>
<p>A <strong>pull request</strong> proposes merging your branch into <code>main</code>. It's where:</p>
<ul>
<li>teammates <strong>review</strong> the diff and comment;</li>
<li><strong>automated checks</strong> (tests, linting — next lessons) run;</li>
<li>discussion happens before anything touches <code>main</code>.</li>
</ul>
<p>After approval and green checks, you <strong>merge</strong> the PR. This is the gate that keeps
<code>main</code> healthy.</p>

<h2>Good commits and messages</h2>
<pre><code># Imperative mood, concise summary, why in the body if needed:
git commit -m "Add JWT auth to product endpoints"

# Not: "stuff", "fix", "asdf", "final final v2"</code></pre>
<p>Commit small, logical units. A clear history is documentation — and makes reviews and reverts easy.</p>

<h2>Keeping your branch up to date</h2>
<pre><code>git switch main
git pull                       # get the latest main
git switch feature/add-orders
git merge main                 # bring main's changes into your branch
# resolve any conflicts, commit, continue</code></pre>
<p>Updating regularly keeps merge conflicts small. A <strong>conflict</strong> happens when two branches
change the same lines; Git marks them and you choose what to keep.</p>

<h2>The <code>.gitignore</code> reminder</h2>
<p>Never commit secrets or generated files. For a FastAPI project, ignore at least:</p>
<pre><code>.venv/
__pycache__/
*.pyc
.env            # secrets — critical!
*.db</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Committing directly to <code>main</code></strong> — use branches + PRs so changes are reviewed
and tested first.</li>
<li><strong>Giant, unfocused commits/PRs</strong> — small ones are easier to review and revert.</li>
<li><strong>Committing <code>.env</code> / secrets</strong> — gitignore them; a leaked key is a breach.</li>
<li><strong>Letting branches drift</strong> for weeks — merge <code>main</code> in often to avoid huge
conflicts.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Keep <code>main</code> always working; do work on <strong>feature branches</strong>.</li>
<li><strong>Pull requests</strong> are where review + automated checks happen before merging.</li>
<li>Write small commits with clear, imperative messages.</li>
<li>Sync with <code>main</code> regularly; never commit secrets (use <code>.gitignore</code>).</li>
</ul>
EOT
      . vid_box('A practical Git branching and pull-request workflow for teams.', 'git branching pull request workflow tutorial'),
    ],

    [
      'title' => 'Testing your FastAPI app',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/SO7m7nod0ts',
      'content' => <<<'EOT'
<h2>Why tests gate your pipeline</h2>
<p>CI/CD (next lesson) runs your tests automatically on every change. So first you need tests. From the
Python course you know <code>pytest</code>; FastAPI adds a <strong>TestClient</strong> that calls your
API in-process — no running server needed.</p>

<h2>The TestClient</h2>
<pre><code>pip install pytest httpx</code></pre>
<pre><code># tests/test_products.py
from fastapi.testclient import TestClient
from app.main import app

client = TestClient(app)

def test_health():
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json() == {"status": "ok"}

def test_create_product():
    response = client.post("/products/",
                           json={"name": "Keyboard", "price": 49.99})
    assert response.status_code == 201
    body = response.json()
    assert body["name"] == "Keyboard"
    assert "id" in body</code></pre>
<p><code>TestClient</code> lets you send real requests (<code>get</code>, <code>post</code>, ...) and
assert on status codes and JSON — fast and reliable.</p>

<h2>Use a separate test database</h2>
<p>Tests must never touch real data. Point the app at a throwaway database (often SQLite) for tests by
<strong>overriding the <code>get_db</code> dependency</strong> — this is exactly why dependency injection
matters:</p>
<pre><code>from app.database import get_db, Base
from app.main import app
# create a test engine/session bound to a temp SQLite DB ...

def override_get_db():
    db = TestingSessionLocal()
    try:
        yield db
    finally:
        db.close()

app.dependency_overrides[get_db] = override_get_db   # swap in the test DB</code></pre>
<div class="alert alert-info" role="alert">
<strong>This is dependency inversion paying off.</strong> Because endpoints depend on the abstract
<code>get_db</code> (not a hardcoded connection), tests swap in a fake/temporary database with one
line — no real Postgres required. Clean design and testability are the same thing, again.
</div>

<h2>Testing protected routes</h2>
<pre><code>def test_me_requires_auth():
    assert client.get("/me").status_code == 401   # no token

def test_me_with_token():
    # log in to get a token
    token = client.post("/token",
        data={"username": "ada@x.io", "password": "secret"}
    ).json()["access_token"]
    r = client.get("/me", headers={"Authorization": f"Bearer {token}"})
    assert r.status_code == 200</code></pre>

<h2>Run them</h2>
<pre><code>pytest               # discovers tests/ and test_*.py
pytest -v            # verbose
pytest --cov=app     # with coverage (pip install pytest-cov)</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Testing against the real database</strong> — override <code>get_db</code> with a temp DB.</li>
<li><strong>Tests that depend on order or shared state</strong> — each test should set up its own data.</li>
<li><strong>Only testing the happy path</strong> — also test 404s, validation 422s, and auth 401/403.</li>
<li><strong>No tests at all</strong>, then a CI pipeline with nothing to run.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Use FastAPI's <strong>TestClient</strong> with <strong>pytest</strong> to call your API in tests.</li>
<li>Override <code>get_db</code> to use a <strong>separate test database</strong> (DI makes this trivial).</li>
<li>Test success, error, and auth cases; get a token to test protected routes.</li>
<li>These tests are what your CI pipeline will run automatically.</li>
</ul>
EOT
      . vid_box('Testing a FastAPI app with pytest and TestClient, including a test database.', 'fastapi testing pytest testclient tutorial'),
    ],

    [
      'title' => 'Continuous Integration with GitHub Actions',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/ZR34Cnkelk4',
      'content' => <<<'EOT'
<h2>What is CI/CD?</h2>
<ul>
<li><strong>CI (Continuous Integration)</strong> — automatically build and <strong>test</strong> every
change (every push/PR), so bugs are caught immediately and <code>main</code> stays healthy.</li>
<li><strong>CD (Continuous Delivery/Deployment)</strong> — automatically <strong>ship</strong> changes
that pass CI to staging or production.</li>
</ul>
<p>Together they turn "it works on my machine" into "it's verified and deployed automatically."</p>

<div class="alert alert-info" role="alert">
<strong>Analogy.</strong> CI is a quality-control conveyor belt in a factory: every item (code change)
automatically passes through the same inspections (tests, linting) before it's allowed to ship.
Nothing reaches customers without passing the checks — and the checks are identical every time.
</div>

<h2>GitHub Actions: CI built into your repo</h2>
<p><strong>GitHub Actions</strong> runs <strong>workflows</strong> defined in YAML files under
<code>.github/workflows/</code>. A workflow listens for events (a push, a PR) and runs jobs on a fresh
virtual machine.</p>
<pre><code># .github/workflows/ci.yml
name: CI

on:                       # when to run
  push:
    branches: [main]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4          # get the code
      - uses: actions/setup-python@v5      # install Python
        with:
          python-version: "3.12"
      - name: Install dependencies
        run: |
          python -m venv .venv
          source .venv/bin/activate
          pip install -r requirements.txt
      - name: Run tests
        run: |
          source .venv/bin/activate
          pytest</code></pre>
<p>Push this file and GitHub runs it automatically. The PR page shows a green check ✅ if tests pass,
or a red ✗ ✗ if they fail — blocking bad merges.</p>

<h2>Anatomy of a workflow</h2>
<table>
<thead><tr><th>Term</th><th>Meaning</th></tr></thead>
<tbody>
<tr><td><code>on</code></td><td>events that trigger the workflow (push, pull_request, schedule)</td></tr>
<tr><td><code>jobs</code></td><td>units that run (can run in parallel); each on a fresh runner</td></tr>
<tr><td><code>steps</code></td><td>commands/actions within a job, run in order</td></tr>
<tr><td><code>uses</code></td><td>a prebuilt action (e.g. checkout, setup-python)</td></tr>
<tr><td><code>run</code></td><td>a shell command you provide</td></tr>
</tbody>
</table>

<h2>Add linting too</h2>
<pre><code>      - name: Lint
        run: |
          source .venv/bin/activate
          pip install ruff
          ruff check .</code></pre>
<p>Now every PR is automatically tested <em>and</em> style-checked. Combine with branch protection
(require checks to pass before merge) and <code>main</code> stays reliable.</p>

<h2>Secrets in CI</h2>
<p>Tests may need config (a test database URL, dummy keys). Store sensitive values in <strong>GitHub
repository secrets</strong> (Settings → Secrets) and read them as environment variables — never
hardcode them in the YAML.</p>
<pre><code>      - name: Run tests
        env:
          SECRET_KEY: ${{ secrets.SECRET_KEY }}
        run: pytest</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>YAML indentation errors</strong> — Actions is strict about spacing (like Python).</li>
<li><strong>Not installing dependencies in the job</strong> — the runner starts empty every time.</li>
<li><strong>Hardcoding secrets in the workflow file</strong> — use repository secrets.</li>
<li><strong>Green CI with no real tests</strong> — a pipeline that runs nothing proves nothing.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>CI</strong> auto-tests every push/PR; <strong>CD</strong> auto-ships what passes.</li>
<li>GitHub Actions workflows live in <code>.github/workflows/*.yml</code> (events → jobs → steps).</li>
<li>Install deps and run <code>pytest</code> (and <code>ruff</code>) on every PR; require checks before
merge.</li>
<li>Keep secrets in <strong>repository secrets</strong>, injected as env vars.</li>
</ul>
EOT
      . vid_box('Setting up Continuous Integration for Python with GitHub Actions.', 'github actions python ci pytest tutorial'),
    ],

    [
      'title' => 'From CI to CD: automating delivery',
      'minutes' => 14,
      'video_url' => 'https://www.youtube.com/embed/w6Y19RWawc0',
      'content' => <<<'EOT'
<h2>Closing the loop</h2>
<p>CI proves a change is good; <strong>CD</strong> (Continuous Delivery/Deployment) gets that good change
to users automatically. The whole pipeline:</p>
<pre><code>push / PR ─► CI: install + lint + test ─► merge to main ─► CD: deploy
   │              │ (red ✗ blocks merge)        │            │
 commit       green ✅                       protected     live site
</code></pre>

<h2>Delivery vs deployment</h2>
<ul>
<li><strong>Continuous Delivery:</strong> every passing change is <em>ready</em> to deploy at the click
of a button (a human approves the release).</li>
<li><strong>Continuous Deployment:</strong> every passing change on <code>main</code> deploys
<em>automatically</em>, no manual step.</li>
</ul>
<p>Start with delivery (safer); move to full deployment once you trust your tests.</p>

<h2>The easy path: platform auto-deploy</h2>
<p>You don't need to hand-write deployment scripts. Platforms like <strong>Render</strong> and
<strong>Railway</strong> (next module) connect to your GitHub repo and <strong>auto-deploy on every push
to <code>main</code></strong>. That <em>is</em> CD — your CI checks run first, and merging triggers the
deploy. For most projects this is all the CD you need.</p>

<div class="alert alert-info" role="alert">
<strong>You already have most of CD.</strong> With branch protection (CI must pass to merge) + a
platform that deploys <code>main</code> automatically, merging a reviewed, tested PR ships it to
production. No bespoke pipeline required to start.
</div>

<h2>Branch protection: the safety gate</h2>
<p>In GitHub repo settings, protect <code>main</code>:</p>
<ul>
<li>require pull requests (no direct pushes);</li>
<li>require the CI checks to pass before merging;</li>
<li>optionally require a review.</li>
</ul>
<p>Now broken code literally cannot reach <code>main</code> — and therefore can't auto-deploy.</p>

<h2>A deploy job in Actions (alternative)</h2>
<p>If your host needs an explicit step, add a job that runs only after tests pass and only on
<code>main</code>:</p>
<pre><code>  deploy:
    needs: test                 # waits for the test job to succeed
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Trigger deploy
        run: curl -X POST "$DEPLOY_HOOK_URL"   # e.g. Render/Railway deploy hook
        env:
          DEPLOY_HOOK_URL: ${{ secrets.DEPLOY_HOOK_URL }}</code></pre>
<p><code>needs: test</code> enforces "deploy only if tests passed"; the <code>if</code> ensures it only
deploys from <code>main</code>.</p>

<h2>Don\'t forget migrations</h2>
<p>A database-backed app must run migrations as part of deploy, before the new code serves traffic:</p>
<pre><code>alembic upgrade head     # run on deploy (covered in the next module)</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Deploying without CI passing first</strong> — gate deploys behind tests (<code>needs:</code> +
branch protection).</li>
<li><strong>No branch protection</strong> — direct pushes to <code>main</code> bypass every check.</li>
<li><strong>Forgetting migrations</strong> in the deploy — new code + old schema = runtime errors.</li>
<li><strong>Auto-deploying before you trust your tests</strong> — start with delivery (manual approve).</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li><strong>CD</strong> ships changes that pass CI; <em>delivery</em> = one-click, <em>deployment</em> =
automatic.</li>
<li>The simplest CD: <strong>branch protection</strong> + a platform that <strong>auto-deploys
<code>main</code></strong> (Render/Railway).</li>
<li>Gate deploy jobs with <code>needs:</code> and <code>if: main</code> so only tested code ships.</li>
<li>Run <strong>migrations</strong> as part of every deploy.</li>
</ul>
EOT
      . vid_box('Continuous delivery/deployment concepts and automating deploys from main.', 'ci cd pipeline explained continuous delivery deployment'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 6 Quiz: Git & CI/CD',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'In a feature-branch + pull-request workflow, what is the role of main?',
        'explain' => 'main should always be working/deployable. New work happens on feature branches and is merged via reviewed, tested pull requests.',
        'options' => [
          ['It stays always-working; changes arrive via reviewed PRs from branches', true],
          ['It is where you make all experimental commits directly', false],
          ['It is deleted after each release', false],
          ['It stores secrets', false],
        ],
      ],
      [
        'q' => 'Which tool lets you call your FastAPI endpoints in tests without running a live server?',
        'explain' => 'FastAPI\'s TestClient sends requests to the app in-process, so pytest can assert on responses without starting uvicorn.',
        'options' => [
          ['FastAPI TestClient', true],
          ['Alembic', false],
          ['uvicorn --reload', false],
          ['pgAdmin', false],
        ],
      ],
      [
        'q' => 'How do tests avoid touching the real database?',
        'explain' => 'Override the get_db dependency (app.dependency_overrides[get_db]) to use a temporary test database. This is dependency injection making testing easy.',
        'options' => [
          ['Override the get_db dependency to use a test database', true],
          ['Delete the production data first', false],
          ['Run tests only on the server', false],
          ['Disable the database entirely', false],
        ],
      ],
      [
        'q' => 'What does CI (Continuous Integration) primarily do?',
        'explain' => 'CI automatically builds and tests every change (push/PR) so problems are caught early and main stays healthy.',
        'options' => [
          ['Automatically build and test every change', true],
          ['Automatically design the database schema', false],
          ['Replace the need for Git', false],
          ['Encrypt the JWT secret', false],
        ],
      ],
      [
        'q' => 'Where do GitHub Actions workflow files live?',
        'explain' => 'Workflow YAML files go in .github/workflows/ in the repository; GitHub runs them on the configured events.',
        'options' => [
          ['.github/workflows/*.yml', true],
          ['/etc/github/actions', false],
          ['In the database', false],
          ['requirements.txt', false],
        ],
      ],
      [
        'q' => 'How should secrets (like SECRET_KEY) be provided to a CI workflow?',
        'explain' => 'Store them as GitHub repository secrets and inject them as environment variables; never hardcode secrets in the YAML file or code.',
        'options' => [
          ['As GitHub repository secrets injected via env vars', true],
          ['Hardcoded in the YAML file', false],
          ['Committed in a .env file', false],
          ['Printed to the build log', false],
        ],
      ],
    ],
  ],
],

/* ══════════════════════════════════════════════════════════════════════════
 * MODULE 7 — DEPLOYMENT ON RAILWAY & RENDER
 * ══════════════════════════════════════════════════════════════════════════ */
[
  'title' => 'Deployment on Railway & Render',
  'description' => 'Ship your API to the internet. Prepare a production-ready app (settings, env vars, a production server), deploy to Render and to Railway with a managed PostgreSQL database, run migrations in production, and monitor the result.',
  'video_url' => '',
  'lessons' => [

    [
      'title' => 'Preparing your app for production',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/iDgbS3RTkTE',
      'content' => <<<'EOT'
<h2>Dev settings won\'t cut it in production</h2>
<p>Locally you used <code>--reload</code>, a hardcoded SQLite path, and a fake secret. Production needs
configuration from the environment, a real database URL, and a production server. Let's get the app
deploy-ready.</p>

<h2>Configuration via environment variables</h2>
<p>The <strong>twelve-factor</strong> rule: keep config (secrets, URLs) in the <strong>environment</strong>,
not in code. Use <code>pydantic-settings</code> to load and validate it:</p>
<pre><code>pip install pydantic-settings</code></pre>
<pre><code># app/config.py
from pydantic_settings import BaseSettings, SettingsConfigDict

class Settings(BaseSettings):
    database_url: str
    secret_key: str
    access_token_expire_minutes: int = 30

    model_config = SettingsConfigDict(env_file=".env")

settings = Settings()    # reads env vars (and .env locally)</code></pre>
<p>Now <code>settings.database_url</code> and <code>settings.secret_key</code> come from the environment.
Locally they're in <code>.env</code> (gitignored); in production the platform injects them.</p>

<div class="alert alert-warning" role="alert">
<strong>Secrets never go in Git.</strong> <code>.env</code> must be in <code>.gitignore</code>. Commit a
<code>.env.example</code> with the <em>keys</em> but no values, so others know what to set. A leaked
<code>SECRET_KEY</code> or database password is a serious breach.
</div>

<h2>Use the database URL from settings</h2>
<pre><code># app/database.py
from app.config import settings
engine = create_engine(settings.database_url)</code></pre>
<p>Hosts provide PostgreSQL via a <code>DATABASE_URL</code> env var, so your app just reads it — no code
change between local and production. (One gotcha: some platforms set a URL starting with
<code>postgres://</code>; SQLAlchemy/psycopg may want <code>postgresql+psycopg://</code> — normalise it
if needed.)</p>

<h2>A production server command</h2>
<p>Don't use <code>--reload</code> in production. Run uvicorn pointing at the app and binding to the
port the host gives you via the <code>$PORT</code> env var:</p>
<pre><code>uvicorn app.main:app --host 0.0.0.0 --port $PORT</code></pre>
<p><code>0.0.0.0</code> listens on all interfaces (required in containers); <code>$PORT</code> is assigned
by Render/Railway. This line is your "start command."</p>

<h2>Pin your dependencies</h2>
<pre><code>pip freeze &gt; requirements.txt</code></pre>
<p>The host installs exactly what's in <code>requirements.txt</code>. Make sure it includes
<code>fastapi</code>, <code>uvicorn</code>, <code>sqlalchemy</code>, <code>psycopg[binary]</code>,
<code>alembic</code>, <code>pydantic-settings</code>, and your auth libs.</p>

<h2>Production checklist</h2>
<pre><code>☐ Config from env vars (pydantic-settings); .env gitignored
☐ DATABASE_URL points at managed PostgreSQL
☐ Strong SECRET_KEY from the environment
☐ Start command: uvicorn app.main:app --host 0.0.0.0 --port $PORT
☐ requirements.txt up to date (pip freeze)
☐ Alembic migrations committed
☐ CORS configured if a browser frontend will call the API
</code></pre>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Hardcoding the DB URL / secret</strong> — read them from the environment.</li>
<li><strong>Committing <code>.env</code></strong> — gitignore it; commit <code>.env.example</code> instead.</li>
<li><strong>Running with <code>--reload</code> or binding to <code>127.0.0.1</code></strong> in production —
use <code>0.0.0.0</code> and <code>$PORT</code>.</li>
<li><strong>An incomplete <code>requirements.txt</code></strong> — the deploy fails on a missing package.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Load config (DB URL, secret) from the <strong>environment</strong> with <code>pydantic-settings</code>;
keep <code>.env</code> out of Git.</li>
<li>Read <code>DATABASE_URL</code> so the same code runs locally and in production.</li>
<li>Start with <code>uvicorn app.main:app --host 0.0.0.0 --port $PORT</code> (no reload).</li>
<li>Keep <code>requirements.txt</code> pinned and migrations committed.</li>
</ul>
EOT
      . vid_box('Preparing a FastAPI app for production: settings, env vars, and the server command.', 'fastapi production settings environment variables pydantic-settings'),
    ],

    [
      'title' => 'Deploying to Render',
      'minutes' => 18,
      'video_url' => 'https://www.youtube.com/embed/nPUA8BLWzeY',
      'content' => <<<'EOT'
<h2>What Render gives you</h2>
<p><strong>Render</strong> is a platform that builds and runs your app from a GitHub repo, with managed
PostgreSQL, HTTPS, and auto-deploys on push — no servers to manage. Perfect for a FastAPI API.</p>

<h2>Step 1 — Push your project to GitHub</h2>
<p>Render deploys from a repo, so commit everything (with <code>.env</code> gitignored) and push to
GitHub. This also ties in your CI from Module 6.</p>

<h2>Step 2 — Create the PostgreSQL database</h2>
<p>In the Render dashboard: <strong>New → PostgreSQL</strong>. Render provisions a managed database and
gives you a connection string (the <strong>Internal Database URL</strong> for services in the same
region). Copy it — you'll set it as <code>DATABASE_URL</code>.</p>

<h2>Step 3 — Create the Web Service</h2>
<p><strong>New → Web Service</strong>, connect your repo, and configure:</p>
<pre><code>Build Command:   pip install -r requirements.txt
Start Command:   uvicorn app.main:app --host 0.0.0.0 --port $PORT
</code></pre>
<p>Render sets <code>$PORT</code> automatically; your start command uses it.</p>

<h2>Step 4 — Set environment variables</h2>
<p>In the service's <strong>Environment</strong> tab, add the values your <code>Settings</code> expects:</p>
<pre><code>DATABASE_URL = &lt;the Postgres connection string from step 2&gt;
SECRET_KEY   = &lt;a long random value: openssl rand -hex 32&gt;
ACCESS_TOKEN_EXPIRE_MINUTES = 30
</code></pre>
<p>These are the production equivalents of your local <code>.env</code> — injected securely, never in
code.</p>

<div class="alert alert-info" role="alert">
<strong>Same code, different environment.</strong> Because the app reads <code>DATABASE_URL</code> and
<code>SECRET_KEY</code> from the environment (last lesson), you deploy the <em>identical</em> code you
ran locally. Only the env var values differ. That's the payoff of twelve-factor config.
</div>

<h2>Step 5 — Run migrations</h2>
<p>Your tables must exist in the production database. Options:</p>
<ul>
<li>Add a <strong>pre-deploy command</strong> in Render: <code>alembic upgrade head</code> (runs before the
new version goes live — the recommended spot).</li>
<li>Or run it once via Render's <strong>Shell</strong> for the service.</li>
</ul>

<h2>Step 6 — Deploy and verify</h2>
<p>Render builds and starts the service, gives you a URL like
<code>https://your-api.onrender.com</code>, and provisions HTTPS. Verify:</p>
<pre><code>https://your-api.onrender.com/health   → {"status":"ok"}
https://your-api.onrender.com/docs     → live interactive docs
</code></pre>
<p>Every future <code>git push</code> to <code>main</code> triggers an automatic redeploy (that's your CD
from Module 6).</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Wrong start command</strong> — must be <code>app.main:app</code> matching your file layout,
with <code>--host 0.0.0.0 --port $PORT</code>.</li>
<li><strong>Forgetting to set env vars</strong> — the app crashes on startup because
<code>Settings</code> can't find <code>DATABASE_URL</code>/<code>SECRET_KEY</code>.</li>
<li><strong>Skipping migrations</strong> — endpoints fail because the tables don't exist.</li>
<li><strong>Using the external DB URL where the internal one is needed</strong> (or vice versa).</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Render deploys from GitHub: create a <strong>PostgreSQL</strong> instance + a <strong>Web Service</strong>.</li>
<li>Build = <code>pip install -r requirements.txt</code>; Start =
<code>uvicorn app.main:app --host 0.0.0.0 --port $PORT</code>.</li>
<li>Set <code>DATABASE_URL</code>, <code>SECRET_KEY</code>, etc. as environment variables.</li>
<li>Run <code>alembic upgrade head</code> (pre-deploy); verify <code>/health</code> and <code>/docs</code>;
pushes auto-deploy.</li>
</ul>
EOT
      . vid_box('Deploying a FastAPI app with PostgreSQL on Render step by step.', 'deploy fastapi to render postgres tutorial'),
    ],

    [
      'title' => 'Deploying to Railway with managed PostgreSQL',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/HFVuJUlO7ik',
      'content' => <<<'EOT'
<h2>Railway: another great option</h2>
<p><strong>Railway</strong> is a similar deploy platform with a slick workflow: add a database and a
service to a "project," and Railway wires them together with shared variables. The concepts mirror
Render — only the dashboard differs.</p>

<h2>Step 1 — Create a project and add PostgreSQL</h2>
<p>In Railway: <strong>New Project → Deploy PostgreSQL</strong>. Railway provisions the database and
exposes a <code>DATABASE_URL</code> <strong>variable reference</strong> you can share with your service —
no copy-pasting connection strings.</p>

<h2>Step 2 — Add your app from GitHub</h2>
<p>In the same project: <strong>New → GitHub Repo</strong>, pick your repo. Railway detects Python and
installs <code>requirements.txt</code>. Set the <strong>start command</strong>:</p>
<pre><code>uvicorn app.main:app --host 0.0.0.0 --port $PORT</code></pre>
<p>Railway provides <code>$PORT</code>, same as Render.</p>

<h2>Step 3 — Reference the database variable</h2>
<p>In your service's <strong>Variables</strong>, add:</p>
<pre><code>DATABASE_URL = ${{ Postgres.DATABASE_URL }}   # reference the DB service
SECRET_KEY   = &lt;openssl rand -hex 32&gt;
ACCESS_TOKEN_EXPIRE_MINUTES = 30</code></pre>
<p>The <code>${{ Postgres.DATABASE_URL }}</code> syntax links your app to the database service's
URL automatically — if the DB credentials change, your app stays connected.</p>

<div class="alert alert-info" role="alert">
<strong>Note the URL scheme.</strong> Managed Postgres often hands you a <code>postgres://...</code>
URL. The psycopg driver wants <code>postgresql+psycopg://...</code>. Either set the variable in the
correct form, or normalise it in <code>config.py</code> (e.g. replace the <code>postgres://</code>
prefix). This trips up many first deploys.
</div>

<h2>Step 4 — Migrations on Railway</h2>
<p>Run Alembic against the production DB. You can:</p>
<ul>
<li>add a <strong>deploy/release command</strong> <code>alembic upgrade head</code>, or</li>
<li>run it from Railway's service shell / CLI:
<code>railway run alembic upgrade head</code> (the Railway CLI injects the project's variables).</li>
</ul>

<h2>Step 5 — Generate a domain and verify</h2>
<p>Under <strong>Settings → Networking</strong>, generate a public domain. You'll get a URL like
<code>https://your-api.up.railway.app</code>:</p>
<pre><code>https://your-api.up.railway.app/health  → {"status":"ok"}
https://your-api.up.railway.app/docs    → live docs</code></pre>
<p>Pushes to your connected branch auto-deploy, just like Render.</p>

<h2>Render vs Railway — which?</h2>
<p>Both are excellent for FastAPI + Postgres and follow the same model (managed DB + service + env vars
+ migrations + auto-deploy). Pick by pricing, free-tier limits, and which dashboard you prefer. The
skills transfer directly — and to other hosts (Fly.io, Heroku, a VPS) too.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Not referencing the DB variable</strong> — link <code>${{ Postgres.DATABASE_URL }}</code>
rather than hardcoding.</li>
<li><strong>The <code>postgres://</code> vs <code>postgresql+psycopg://</code> scheme mismatch</strong> —
normalise it.</li>
<li><strong>Forgetting to generate a public domain</strong> — the service runs but isn't reachable.</li>
<li><strong>Skipping migrations</strong> — same failure as on any host.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Railway = project with a <strong>Postgres service</strong> + your <strong>app service</strong>, linked by
variable references.</li>
<li>Start command and <code>$PORT</code> work the same as Render.</li>
<li>Reference <code>${{ Postgres.DATABASE_URL }}</code>; watch the URL scheme for psycopg.</li>
<li>Run <code>alembic upgrade head</code>, generate a domain, verify <code>/health</code> and
<code>/docs</code>.</li>
</ul>
EOT
      . vid_box('Deploying FastAPI with PostgreSQL on Railway step by step.', 'deploy fastapi to railway postgres tutorial'),
    ],

    [
      'title' => 'Production operations: migrations, logs & monitoring',
      'minutes' => 16,
      'video_url' => 'https://www.youtube.com/embed/kmJz8w5ij8Y',
      'content' => <<<'EOT'
<h2>Shipping is the start, not the end</h2>
<p>A deployed API needs ongoing care: applying schema changes safely, watching logs, handling errors,
and protecting it. Here are the production basics that keep a live service healthy.</p>

<h2>Migrations in production — safely</h2>
<p>Run <code>alembic upgrade head</code> as part of every deploy (pre-deploy/release command), so the
schema matches the new code before it serves traffic. Safe practices:</p>
<ul>
<li><strong>Additive first:</strong> add columns as nullable or with defaults; don't drop columns the
running code still uses.</li>
<li><strong>Back up</strong> (or snapshot) the database before risky migrations — managed hosts offer
backups.</li>
<li><strong>Test migrations</strong> on a staging copy before production.</li>
<li><strong>Never</strong> point <code>create_all</code> at production — Alembic only.</li>
</ul>

<h2>Logs: your eyes in production</h2>
<p>Both Render and Railway stream your service's logs in the dashboard. Use Python's <code>logging</code>
(not bare <code>print</code>) so you can control levels and formats:</p>
<pre><code>import logging
logger = logging.getLogger("app")

@app.post("/orders")
def create_order(...):
    logger.info("Order created for user %s", user.id)
    ...
    logger.error("Payment failed: %s", err)</code></pre>
<p>When something breaks, the logs (plus the traceback) are where you look first — read them bottom-up,
as in the Python course.</p>

<h2>Health checks and uptime</h2>
<p>Keep a simple <code>/health</code> endpoint; platforms ping it to know your service is alive and to
decide when a new deploy is ready. You can add free uptime monitoring (e.g. a pinger that alerts you
if <code>/health</code> stops returning 200).</p>

<h2>Error tracking</h2>
<p>Logs are reactive; an error tracker is proactive. Tools like <strong>Sentry</strong> capture
exceptions with full context and alert you — often before users complain. Adding it is a few lines and
well worth it for a real service.</p>

<div class="alert alert-info" role="alert">
<strong>You can\'t fix what you can\'t see.</strong> Logging, health checks, and error tracking turn
"users say it's broken" into "I got an alert with the stack trace and the exact request." Observability
is part of building production software, not an afterthought.
</div>

<h2>Production hardening checklist</h2>
<pre><code>☐ HTTPS (managed hosts give this automatically)
☐ Strong SECRET_KEY; short token expiry
☐ CORS restricted to your real frontend origin(s)
☐ Migrations run on deploy; DB backups enabled
☐ Logging + error tracking (e.g. Sentry)
☐ /health endpoint + uptime monitoring
☐ Don't expose stack traces to clients (Module 4)
</code></pre>

<h2>You\'ve built and shipped a real backend</h2>
<p>You can now design a REST API, model it with Pydantic, persist data in PostgreSQL via SQLAlchemy,
secure it with JWT/OAuth2, test it, run it through CI/CD, and deploy it live with monitoring. That's
the full backend lifecycle — the core of a backend engineer's job. Keep building: add features to your
deployed API, then start a project of your own.</p>

<h2>⚠️ Common mistakes</h2>
<ul>
<li><strong>Running destructive migrations without a backup</strong> or staging test.</li>
<li><strong>Using <code>print</code> instead of <code>logging</code></strong> — no levels, no control,
harder to filter.</li>
<li><strong>No monitoring</strong> — you learn about outages from users, not alerts.</li>
<li><strong>Wide-open CORS / exposed tracebacks</strong> in production — tighten both.</li>
</ul>

<h2>✅ Key takeaways</h2>
<ul>
<li>Run <strong>migrations on every deploy</strong>; keep them additive, backed up, and staging-tested.</li>
<li>Use <strong>logging</strong> (not print) and read your platform's log stream when debugging.</li>
<li>Keep a <strong>/health</strong> endpoint; add <strong>uptime monitoring</strong> and <strong>error
tracking</strong> (Sentry).</li>
<li>Harden production: HTTPS, strong secrets, restricted CORS, no leaked tracebacks.</li>
</ul>
EOT
      . vid_box('Running migrations, reading logs, and monitoring a deployed API in production.', 'fastapi production logging monitoring sentry deployment best practices'),
    ],

  ],
  'quiz' => [
    'title' => 'Module 7 Quiz: Deployment',
    'pass_mark' => 70,
    'questions' => [
      [
        'q' => 'Where should production configuration like the database URL and secret key come from?',
        'explain' => 'From environment variables (twelve-factor config), loaded with something like pydantic-settings — never hardcoded in code or committed in .env.',
        'options' => [
          ['Environment variables (e.g. via pydantic-settings)', true],
          ['Hardcoded constants in the source code', false],
          ['A committed .env file in the repo', false],
          ['The JWT payload', false],
        ],
      ],
      [
        'q' => 'What is the correct production start command for a FastAPI app on Render/Railway?',
        'explain' => 'uvicorn app.main:app --host 0.0.0.0 --port $PORT — bind all interfaces and use the platform-assigned $PORT; no --reload in production.',
        'options' => [
          ['uvicorn app.main:app --host 0.0.0.0 --port $PORT', true],
          ['uvicorn app.main:app --reload', false],
          ['python app/main.py', false],
          ['fastapi dev app/main.py', false],
        ],
      ],
      [
        'q' => 'How do you make your database tables exist in the production database?',
        'explain' => 'Run alembic upgrade head as part of deployment (e.g. a pre-deploy/release command). Do not use create_all in production.',
        'options' => [
          ['Run alembic upgrade head on deploy', true],
          ['Call Base.metadata.create_all in production', false],
          ['Tables appear automatically', false],
          ['Recreate the database on every request', false],
        ],
      ],
      [
        'q' => 'A managed Postgres gives a URL starting with postgres://, but psycopg expects another form. What do you do?',
        'explain' => 'Normalise the scheme to postgresql+psycopg:// (in config or the variable). This scheme mismatch is a common first-deploy failure.',
        'options' => [
          ['Normalise it to postgresql+psycopg://', true],
          ['Ignore it — both are identical to psycopg', false],
          ['Switch to SQLite in production', false],
          ['Remove the password from the URL', false],
        ],
      ],
      [
        'q' => 'After connecting your GitHub repo to Render/Railway, what happens on a push to main?',
        'explain' => 'The platform auto-deploys the new version — this is continuous deployment from Module 6. Combined with CI/branch protection, only tested code ships.',
        'options' => [
          ['The platform automatically redeploys the app', true],
          ['Nothing until you re-upload files manually', false],
          ['The database is wiped', false],
          ['It only deploys feature branches', false],
        ],
      ],
      [
        'q' => 'Which practice best helps you detect and diagnose problems in a live API?',
        'explain' => 'Structured logging plus error tracking (e.g. Sentry) and a /health endpoint with uptime monitoring give you visibility — you can\'t fix what you can\'t see.',
        'options' => [
          ['Logging + error tracking + a health endpoint with monitoring', true],
          ['Waiting for users to report issues', false],
          ['Disabling logs to save space', false],
          ['Exposing full stack traces to clients', false],
        ],
      ],
    ],
  ],
],

]; // end $MODULES
echo '<h2>Seeding course: ' . htmlspecialchars($COURSE_TITLE) . '</h2>';

$pdo->beginTransaction();
try {
    // --- Idempotency: remove any existing copy of this course (cascade children) ---
    $find = $pdo->prepare('SELECT id FROM courses WHERE title = ?');
    $find->execute([$COURSE_TITLE]);
    foreach ($find->fetchAll(PDO::FETCH_COLUMN) as $oldId) {
        $mods = $pdo->prepare('SELECT id FROM modules WHERE course_id = ?');
        $mods->execute([$oldId]);
        foreach ($mods->fetchAll(PDO::FETCH_COLUMN) as $mid) {
            $lids = $pdo->prepare('SELECT id FROM lessons WHERE module_id = ?');
            $lids->execute([$mid]);
            foreach ($lids->fetchAll(PDO::FETCH_COLUMN) as $lid) {
                $pdo->prepare('DELETE FROM coding_exercises WHERE lesson_id = ?')->execute([$lid]);
            }
            $pdo->prepare('DELETE FROM lessons WHERE module_id = ?')->execute([$mid]);

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
         VALUES (?, ?, 'published', ?, 'intermediate', 35)"
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
