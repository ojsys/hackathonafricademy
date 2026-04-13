<?php
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/../includes/header.php';
http_response_code(500);
?>
<div class="container text-center py-5">
    <h1 class="display-1">500</h1>
    <h2 class="mb-4">Internal Server Error</h2>
    <p class="lead">Oops! Something went wrong on our end. We're working to fix it.</p>
    <p class="lead">Please try again later or contact support if the issue persists.</p>
    <a href="/" class="btn btn-primary mt-3">Go to Homepage</a>
</div>
<?php
include __DIR__ . '/../includes/footer.php';
?>