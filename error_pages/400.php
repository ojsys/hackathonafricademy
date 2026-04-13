<?php
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';
http_response_code(400);
?>
<div class="container text-center py-5">
    <h1 class="display-1">400</h1>
    <h2 class="mb-4">Bad Request</h2>
    <p class="lead">The server cannot process the request due to something that is perceived to be a client error.</p>
    <a href="/" class="btn btn-primary mt-3">Go to Homepage</a>
</div>
<?php
include __DIR__ . '/../includes/footer.php';
?>