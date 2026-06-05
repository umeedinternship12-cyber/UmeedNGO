<?php
header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

require_once __DIR__ . '/sheets.php';

$ok = postToSheets([
    'type'          => 'donation',
    'name'          => strip_tags(trim($data['name']          ?? '')),
    'email'         => filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL),
    'phone'         => strip_tags(trim($data['phone']         ?? '')),
    'pan'           => strip_tags(trim($data['pan']           ?? '')),
    'amount'        => (int)($data['amount'] ?? 0),
    'donation_type' => strip_tags(trim($data['donation_type'] ?? '')),
    'anonymous'     => !empty($data['anonymous']),
    'message'       => strip_tags(trim($data['message']       ?? '')),
]);

echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => 'Could not save. Please try again.']);
