<?php
// $conn = new mysqli("localhost","root","","esp32cam");
$conn = new mysqli("localhost", "dibscode", "Bh#DD|8X7wk+", "dibscode_deteksiburung");
$conn->query("UPDATE kontrol SET capture=1 WHERE id=1");
header("Location: manual.php");
exit;
