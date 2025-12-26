<?php

declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$jenis = $_GET['jenis'] ?? 'otomatis';
$allowedJenis = ['otomatis', 'manual'];
if (!in_array($jenis, $allowedJenis, true)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'invalid_jenis',
    ]);
    exit;
}

// $conn = new mysqli('localhost', 'root', '', 'esp32cam');
$conn = new mysqli("localhost", "dibscode", "Bh#DD|8X7wk+", "dibscode_deteksiburung");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'db_connect_error',
    ]);
    exit;
}

$stmt = $conn->prepare('SELECT id, filename, waktu FROM foto WHERE jenis = ? ORDER BY id DESC LIMIT 1');
if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'db_prepare_error',
    ]);
    exit;
}

$stmt->bind_param('s', $jenis);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'db_execute_error',
    ]);
    exit;
}

$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;

$latest = null;
if ($row) {
    $filename = (string)($row['filename'] ?? '');
    $latest = [
        'id' => (int)($row['id'] ?? 0),
        'filename' => $filename,
        'waktu' => (string)($row['waktu'] ?? ''),
        'url' => 'uploads/' . $filename,
    ];
}

echo json_encode([
    'ok' => true,
    'jenis' => $jenis,
    'latest' => $latest,
]);
