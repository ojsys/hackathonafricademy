<?php
/**
 * Read-only sample data API for the applied "fetch & render" interview tasks.
 * Candidates fetch this from their live preview to populate the page.
 *
 *   ?resource=users     -> { ok, users:    [{name, email}, ...] }
 *   ?resource=products  -> { ok, products: [{name, price}, ...] }
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();
header('Content-Type: application/json');

$resource = $_GET['resource'] ?? 'users';

$data = [
    'users' => [
        ['name' => 'Amina Bello',    'email' => 'amina@example.com'],
        ['name' => 'Kwame Mensah',   'email' => 'kwame@example.com'],
        ['name' => 'Lerato Dlamini', 'email' => 'lerato@example.com'],
        ['name' => 'Tunde Okafor',   'email' => 'tunde@example.com'],
        ['name' => 'Fatima Sow',     'email' => 'fatima@example.com'],
    ],
    'products' => [
        ['name' => 'Keyboard',  'price' => 25],
        ['name' => 'Mouse',     'price' => 12],
        ['name' => 'Monitor',   'price' => 150],
        ['name' => 'USB Cable', 'price' => 5],
        ['name' => 'Webcam',    'price' => 40],
    ],
];

if ($resource === 'products') {
    echo json_encode(['ok' => true, 'products' => $data['products']]);
} else {
    echo json_encode(['ok' => true, 'users' => $data['users']]);
}
