<?php
date_default_timezone_set("Asia/Jakarta");
header("Content-Type: text/plain");
header("Cache-Control: no-cache");

$conn = new mysqli("localhost", "root", "", "esp32cam");
if ($conn->connect_error) {
    die("DB CONNECT ERROR");
}

if (!isset($_FILES['image'])) {
    die("NO IMAGE");
}

$jenis = $_POST['jenis'] ?? 'unknown';

$folder = "uploads/";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$filename = date("Ymd_His") . ".jpg";
$path = $folder . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
    die("UPLOAD FAIL");
}

/* ================= INSERT DATABASE ================= */

$sql = "INSERT INTO foto (jenis, filename) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL PREPARE ERROR: " . $conn->error);
}

$stmt->bind_param("ss", $jenis, $filename);

if (!$stmt->execute()) {
    die("SQL EXEC ERROR: " . $stmt->error);
}

echo "OK | JENIS=$jenis | FILE=$filename";
