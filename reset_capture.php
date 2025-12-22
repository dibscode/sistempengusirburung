<?php
$conn = new mysqli("localhost","root","","esp32cam");
$conn->query("UPDATE kontrol SET capture=0 WHERE id=1");
echo "OK";
exit;
