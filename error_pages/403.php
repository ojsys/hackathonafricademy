<?php
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';
http_response_code(403);
?>
<div class="container text-center py-5">
    <h1 class="display-1">403</h1>
    <h2 class="mb-4">Forbidden</h2>
    <p class="lead">You do not have permission to access this resource.</p>
    <a href="/" class="btn btn-primary mt-3">Go to Homepage</a>
</div>
<?php
include __DIR__ . '/../includes/footer.php';
?>