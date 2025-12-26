<?php
// $conn = new mysqli("localhost", "root", "", "esp32cam");
$conn = new mysqli("localhost", "dibscode", "Bh#DD|8X7wk+", "dibscode_deteksiburung");

$status = isset($_POST['status']) ? intval($_POST['status']) : 0;

$conn->query("UPDATE kontrol SET status=$status WHERE id=1");

echo "OK";
