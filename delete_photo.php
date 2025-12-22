<?php
$conn = new mysqli("localhost", "root", "", "esp32cam");
header('Content-Type: application/json; charset=utf-8');

if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'db_connection_failed']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'method_not_allowed']);
  exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'invalid_id']);
  exit;
}

// Ambil filename dari DB supaya tidak percaya input client
$stmt = $conn->prepare("SELECT filename FROM foto WHERE id = ? LIMIT 1");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'prepare_failed']);
  exit;
}

$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
  http_response_code(404);
  echo json_encode(['ok' => false, 'error' => 'not_found']);
  exit;
}

$filename = (string)($row['filename'] ?? '');
$base = basename($filename);
if ($base === '' || $base !== $filename) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'invalid_filename']);
  exit;
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
$filePath = $uploadDir . $base;

$conn->begin_transaction();

$del = $conn->prepare("DELETE FROM foto WHERE id = ? LIMIT 1");
if (!$del) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'prepare_delete_failed']);
  exit;
}

$del->bind_param('i', $id);
$del->execute();
$affected = $del->affected_rows;
$del->close();

if ($affected < 1) {
  $conn->rollback();
  http_response_code(404);
  echo json_encode(['ok' => false, 'error' => 'not_found']);
  exit;
}

// Hapus file (kalau ada). Kalau gagal unlink (mis. permission), rollback agar DB tidak hilang dulu.
if (file_exists($filePath)) {
  if (!@unlink($filePath)) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'unlink_failed']);
    exit;
  }
}

$conn->commit();

echo json_encode(['ok' => true]);
