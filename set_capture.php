<?php
$conn = new mysqli("localhost","root","","esp32cam");
$conn->query("UPDATE kontrol SET capture=1 WHERE id=1");
header("Location: manual.php");
exit;
