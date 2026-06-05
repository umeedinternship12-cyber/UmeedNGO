<?php
header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$name  = trim($data['name']  ?? '');
$email = trim($data['email'] ?? '');

if (!$name || !$email) {
    echo json_encode(['ok' => false, 'error' => 'Name and email are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Please enter a valid email address.']);
    exit;
}

require_once __DIR__ . '/sheets.php';

$ok = postToSheets([
    'type'    => 'internship',
    'name'    => strip_tags($name),
    'email'   => filter_var($email, FILTER_SANITIZE_EMAIL),
    'phone'   => strip_tags(trim($data['phone']   ?? '')),
    'college' => strip_tags(trim($data['college'] ?? '')),
]);

echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => 'Could not save. Please try again.']);
