<?php
require_once 'config.php';
echo "Test page";
$db = new PDO('sqlite:' . DB_PATH);
echo "Database connected successfully";
?>
