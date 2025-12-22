<?php
header("Content-Type: text/plain");
header("Cache-Control: no-cache, must-revalidate");

$conn = new mysqli("localhost","root","","esp32cam");
$q = $conn->query("SELECT status, capture FROM kontrol WHERE id=1");
$d = $q->fetch_assoc();

echo $d['status'] . "," . $d['capture'];
exit;
