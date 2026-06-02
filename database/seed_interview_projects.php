<?php
/**
 * Applied / project interview tasks — built in a live browser preview, exactly
 * like the HTML/CSS/JS lessons on the LMS. Defines $projects[] (consumed by
 * setup_interview.php). Each task is an HTML *fragment* (markup + inline
 * <style>/<script>); the preview wraps it and injects a fetch helper so calls
 * to our /actions/interview_sandbox.php and /actions/interview_api.php work.
 *
 * 2 variants per category so candidates get different applied tasks:
 *   form_db · layout · dom · fetch
 */

$projects = [];

/* ═══════════ form_db — build a form that saves to the database ═══════════ */
$projects[] = PR('form_db', 'Contact Form That Saves to the Database',
    'Build a contact form. When submitted, save each message to the database and list all saved messages below the form.',
    "1. The form has a text input for the name, an email input, and a textarea for the message\n2. All three fields are required\n3. Submitting must not reload the page and should send the data as JSON to /actions/interview_sandbox.php\n4. After saving, the saved messages are listed on the page\n5. The message count updates to match the number of saved messages",
    'combined',
    <<<'EOT'
<h1>Contact Us</h1>
<form id="contact-form">
  <!-- TODO: a name (text), an email, and a message (textarea) — all required -->

  <button type="submit">Send</button>
</form>

<h2>Saved messages (<span id="count">0</span>)</h2>
<ul id="messages"></ul>

<script>
  const form    = document.getElementById('contact-form');
  const list    = document.getElementById('messages');
  const countEl = document.getElementById('count');

  // Save one message to the database
  async function saveMessage(data) {
    // TODO: POST `data` as JSON to '/actions/interview_sandbox.php'
  }

  // Load all saved messages and render them
  async function loadMessages() {
    const res  = await fetch('/actions/interview_sandbox.php?action=list');
    const json = await res.json();
    // TODO: render json.entries into #messages and set #count to json.count
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    // TODO: read + validate the inputs, saveMessage(...), then loadMessages()
  });

  loadMessages();
</script>
EOT,
    <<<'EOT'
<h1>Contact Us</h1>
<form id="contact-form">
  <input type="text" id="name" placeholder="Your name" required>
  <input type="email" id="email" placeholder="you@example.com" required>
  <textarea id="message" placeholder="Your message" required></textarea>
  <button type="submit">Send</button>
</form>

<h2>Saved messages (<span id="count">0</span>)</h2>
<ul id="messages"></ul>

<script>
  const form    = document.getElementById('contact-form');
  const list    = document.getElementById('messages');
  const countEl = document.getElementById('count');

  async function saveMessage(data) {
    await fetch('/actions/interview_sandbox.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
  }

  async function loadMessages() {
    const res  = await fetch('/actions/interview_sandbox.php?action=list');
    const json = await res.json();
    countEl.textContent = json.count;
    list.innerHTML = '';
    json.entries.forEach(function (m) {
      const li = document.createElement('li');
      li.textContent = m.name + ' (' + m.email + '): ' + m.message;
      list.appendChild(li);
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
      name:    document.getElementById('name').value.trim(),
      email:   document.getElementById('email').value.trim(),
      message: document.getElementById('message').value.trim()
    };
    if (!data.name || !data.email || !data.message) return;
    await saveMessage(data);
    form.reset();
    loadMessages();
  });

  loadMessages();
</script>
EOT,
    'medium');

$projects[] = PR('form_db', 'Newsletter Signup That Stores Subscribers',
    'Build a newsletter signup form that stores each subscriber in the database and shows the growing subscriber list.',
    "1. The form has a name (text) input and an email input, both required\n2. Submitting must not reload the page and should send the data as JSON to /actions/interview_sandbox.php\n3. After signing up, the subscriber appears in a list on the page\n4. The subscriber count is shown and stays correct\n5. The form clears after a successful signup",
    'combined',
    <<<'EOT'
<h1>Join our Newsletter</h1>
<form id="signup">
  <!-- TODO: name (text) and email — both required -->

  <button type="submit">Subscribe</button>
</form>

<h2>Subscribers (<span id="count">0</span>)</h2>
<ul id="subs"></ul>

<script>
  const form = document.getElementById('signup');

  async function addSubscriber(data) {
    // TODO: POST `data` as JSON to '/actions/interview_sandbox.php'
  }

  async function loadSubscribers() {
    const res  = await fetch('/actions/interview_sandbox.php?action=list');
    const json = await res.json();
    // TODO: render json.entries into #subs and update #count
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    // TODO: read + validate, addSubscriber(...), reset the form, loadSubscribers()
  });

  loadSubscribers();
</script>
EOT,
    <<<'EOT'
<h1>Join our Newsletter</h1>
<form id="signup">
  <input type="text" id="name" placeholder="Name" required>
  <input type="email" id="email" placeholder="Email" required>
  <button type="submit">Subscribe</button>
</form>

<h2>Subscribers (<span id="count">0</span>)</h2>
<ul id="subs"></ul>

<script>
  const form    = document.getElementById('signup');
  const subs    = document.getElementById('subs');
  const countEl = document.getElementById('count');

  async function addSubscriber(data) {
    await fetch('/actions/interview_sandbox.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
  }

  async function loadSubscribers() {
    const res  = await fetch('/actions/interview_sandbox.php?action=list');
    const json = await res.json();
    countEl.textContent = json.count;
    subs.innerHTML = '';
    json.entries.forEach(function (s) {
      const li = document.createElement('li');
      li.textContent = s.name + ' — ' + s.email;
      subs.appendChild(li);
    });
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = {
      name:  document.getElementById('name').value.trim(),
      email: document.getElementById('email').value.trim()
    };
    if (!data.name || !data.email) return;
    await addSubscriber(data);
    form.reset();
    loadSubscribers();
  });

  loadSubscribers();
</script>
EOT,
    'medium');

/* ═══════════ layout — responsive CSS ═══════════ */
$projects[] = PR('layout', 'Responsive Pricing Cards',
    'Style three pricing cards so they sit in a row on wide screens and stack on narrow screens.',
    "1. The three cards are laid out in a row using Flexbox or CSS Grid\n2. Each card has padding, rounded corners, and a border or box-shadow\n3. On screens narrower than 600px the cards stack vertically using a media query\n4. The price stands out (larger or bolder than the rest of the card)",
    'combined',
    <<<'EOT'
<style>
  /* TODO: lay the .cards out in a row (flex or grid), style each .card,
     and stack them on screens under 600px with a media query */
  .cards { }
  .card  { }
</style>

<div class="cards">
  <div class="card"><h3>Starter</h3><div class="price">$0</div><button>Choose</button></div>
  <div class="card"><h3>Pro</h3><div class="price">$15</div><button>Choose</button></div>
  <div class="card"><h3>Team</h3><div class="price">$49</div><button>Choose</button></div>
</div>
EOT,
    <<<'EOT'
<style>
  .cards { display: flex; gap: 1rem; }
  .card {
    flex: 1;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.12);
    text-align: center;
  }
  .price { font-size: 2rem; font-weight: 800; margin: .5rem 0; }
  @media (max-width: 600px) {
    .cards { flex-direction: column; }
  }
</style>

<div class="cards">
  <div class="card"><h3>Starter</h3><div class="price">$0</div><button>Choose</button></div>
  <div class="card"><h3>Pro</h3><div class="price">$15</div><button>Choose</button></div>
  <div class="card"><h3>Team</h3><div class="price">$49</div><button>Choose</button></div>
</div>
EOT,
    'easy');

$projects[] = PR('layout', 'Responsive Navigation Bar',
    'Style a navigation bar that is horizontal on wide screens and stacks vertically on narrow screens.',
    "1. The brand and the links sit on one row on wide screens using Flexbox\n2. The links are spaced apart and the brand is on the left\n3. On screens narrower than 600px the nav items stack vertically using a media query\n4. Links change appearance on hover",
    'combined',
    <<<'EOT'
<style>
  /* TODO: make .nav a horizontal flex bar, space out the links,
     add a hover style, and stack everything under 600px */
  .nav { }
</style>

<nav class="nav">
  <span class="brand">HackathonAfrica</span>
  <a href="#">Home</a>
  <a href="#">Courses</a>
  <a href="#">Contact</a>
</nav>
EOT,
    <<<'EOT'
<style>
  .nav {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1rem;
    background: #111827;
  }
  .nav .brand { font-weight: 800; color: #fff; margin-right: auto; }
  .nav a { color: #cbd5e1; text-decoration: none; }
  .nav a:hover { color: #fff; text-decoration: underline; }
  @media (max-width: 600px) {
    .nav { flex-direction: column; align-items: flex-start; gap: .5rem; }
    .nav .brand { margin-right: 0; }
  }
</style>

<nav class="nav">
  <span class="brand">HackathonAfrica</span>
  <a href="#">Home</a>
  <a href="#">Courses</a>
  <a href="#">Contact</a>
</nav>
EOT,
    'easy');

/* ═══════════ dom — interactivity ═══════════ */
$projects[] = PR('dom', 'Interactive To-Do List',
    'Build a to-do list where the user can add tasks and remove them.',
    "1. There is a text input and an Add button\n2. Clicking Add inserts the typed text as a new item in the list\n3. An empty input does not add an item\n4. Each item has a button that removes that item\n5. The input clears after adding",
    'combined',
    <<<'EOT'
<h1>My Tasks</h1>
<input type="text" id="task-input" placeholder="New task...">
<button id="add-btn">Add</button>
<ul id="tasks"></ul>

<script>
  const input = document.getElementById('task-input');
  const addBtn = document.getElementById('add-btn');
  const tasks = document.getElementById('tasks');

  function addTask() {
    // TODO: read the input, ignore empty values, add an <li> with a Remove button,
    //       then clear the input
  }

  addBtn.addEventListener('click', addTask);
</script>
EOT,
    <<<'EOT'
<h1>My Tasks</h1>
<input type="text" id="task-input" placeholder="New task...">
<button id="add-btn">Add</button>
<ul id="tasks"></ul>

<script>
  const input  = document.getElementById('task-input');
  const addBtn = document.getElementById('add-btn');
  const tasks  = document.getElementById('tasks');

  function addTask() {
    const text = input.value.trim();
    if (!text) return;
    const li = document.createElement('li');
    li.textContent = text;
    const remove = document.createElement('button');
    remove.textContent = 'Remove';
    remove.addEventListener('click', function () { li.remove(); });
    li.appendChild(remove);
    tasks.appendChild(li);
    input.value = '';
  }

  addBtn.addEventListener('click', addTask);
</script>
EOT,
    'easy');

$projects[] = PR('dom', 'Live Search Filter',
    'Filter a list of names as the user types in a search box.',
    "1. As the user types, only the names that contain the typed text are shown (case-insensitive)\n2. Clearing the box shows all names again\n3. When nothing matches, a 'No results' message is shown\n4. Filtering happens on every keystroke (input event)",
    'combined',
    <<<'EOT'
<h1>Find a Country</h1>
<input type="text" id="search" placeholder="Type to filter...">
<ul id="list">
  <li>Nigeria</li><li>Kenya</li><li>Ghana</li>
  <li>South Africa</li><li>Egypt</li><li>Rwanda</li>
</ul>
<p id="empty" style="display:none">No results</p>

<script>
  const search = document.getElementById('search');
  const items  = Array.from(document.querySelectorAll('#list li'));
  const empty  = document.getElementById('empty');

  // TODO: on each input event, show only items whose text contains the query
  //       (case-insensitive) and toggle the #empty message
</script>
EOT,
    <<<'EOT'
<h1>Find a Country</h1>
<input type="text" id="search" placeholder="Type to filter...">
<ul id="list">
  <li>Nigeria</li><li>Kenya</li><li>Ghana</li>
  <li>South Africa</li><li>Egypt</li><li>Rwanda</li>
</ul>
<p id="empty" style="display:none">No results</p>

<script>
  const search = document.getElementById('search');
  const items  = Array.from(document.querySelectorAll('#list li'));
  const empty  = document.getElementById('empty');

  search.addEventListener('input', function () {
    const q = search.value.trim().toLowerCase();
    let visible = 0;
    items.forEach(function (li) {
      const match = li.textContent.toLowerCase().includes(q);
      li.style.display = match ? '' : 'none';
      if (match) visible++;
    });
    empty.style.display = visible === 0 ? 'block' : 'none';
  });
</script>
EOT,
    'medium');

/* ═══════════ fetch — load and render data from an API ═══════════ */
$projects[] = PR('fetch', 'Load Users from the API',
    'Fetch a list of users from the provided API and render them on the page.',
    "1. On load, fetch JSON from /actions/interview_api.php?resource=users\n2. Show a 'Loading...' message while the request is in flight\n3. Render each user's name and email into the list\n4. If the API returns no users, show an empty-state message instead of an empty list",
    'combined',
    <<<'EOT'
<h1>Team Members</h1>
<div id="status">Loading...</div>
<ul id="users"></ul>

<script>
  const statusEl = document.getElementById('status');
  const usersEl  = document.getElementById('users');

  async function loadUsers() {
    // TODO: fetch '/actions/interview_api.php?resource=users',
    //       hide the loading message, render json.users (name + email),
    //       and show an empty-state message if there are none
  }

  loadUsers();
</script>
EOT,
    <<<'EOT'
<h1>Team Members</h1>
<div id="status">Loading...</div>
<ul id="users"></ul>

<script>
  const statusEl = document.getElementById('status');
  const usersEl  = document.getElementById('users');

  async function loadUsers() {
    try {
      const res  = await fetch('/actions/interview_api.php?resource=users');
      const json = await res.json();
      statusEl.style.display = 'none';
      if (!json.users || json.users.length === 0) {
        statusEl.style.display = 'block';
        statusEl.textContent = 'No users found.';
        return;
      }
      json.users.forEach(function (u) {
        const li = document.createElement('li');
        li.textContent = u.name + ' — ' + u.email;
        usersEl.appendChild(li);
      });
    } catch (e) {
      statusEl.textContent = 'Failed to load users.';
    }
  }

  loadUsers();
</script>
EOT,
    'medium');

$projects[] = PR('fetch', 'Product List with Search',
    'Fetch products from the provided API and let the user search them by name.',
    "1. On load, fetch JSON from /actions/interview_api.php?resource=products and render each product's name and price\n2. Show a 'Loading...' message while fetching\n3. A search box filters the rendered products by name (case-insensitive)\n4. When nothing matches, show a 'No products' message",
    'combined',
    <<<'EOT'
<h1>Products</h1>
<input type="text" id="search" placeholder="Search products...">
<div id="status">Loading...</div>
<ul id="products"></ul>

<script>
  const search   = document.getElementById('search');
  const statusEl = document.getElementById('status');
  const listEl   = document.getElementById('products');
  let all = [];

  function render(items) {
    // TODO: render items (name + price) into #products; show a message if empty
  }

  async function loadProducts() {
    // TODO: fetch '/actions/interview_api.php?resource=products', store json.products
    //       in `all`, hide loading, render(all)
  }

  search.addEventListener('input', function () {
    // TODO: filter `all` by name (case-insensitive) and render the matches
  });

  loadProducts();
</script>
EOT,
    <<<'EOT'
<h1>Products</h1>
<input type="text" id="search" placeholder="Search products...">
<div id="status">Loading...</div>
<ul id="products"></ul>

<script>
  const search   = document.getElementById('search');
  const statusEl = document.getElementById('status');
  const listEl   = document.getElementById('products');
  let all = [];

  function render(items) {
    listEl.innerHTML = '';
    if (!items.length) { statusEl.style.display = 'block'; statusEl.textContent = 'No products'; return; }
    statusEl.style.display = 'none';
    items.forEach(function (p) {
      const li = document.createElement('li');
      li.textContent = p.name + ' — $' + p.price;
      listEl.appendChild(li);
    });
  }

  async function loadProducts() {
    const res  = await fetch('/actions/interview_api.php?resource=products');
    const json = await res.json();
    all = json.products || [];
    render(all);
  }

  search.addEventListener('input', function () {
    const q = search.value.trim().toLowerCase();
    render(all.filter(function (p) { return p.name.toLowerCase().includes(q); }));
  });

  loadProducts();
</script>
EOT,
    'medium');
