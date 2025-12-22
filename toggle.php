<?php
$conn = new mysqli("localhost", "root", "", "esp32cam");

$status = isset($_POST['status']) ? intval($_POST['status']) : 0;

$conn->query("UPDATE kontrol SET status=$status WHERE id=1");

echo "OK";
