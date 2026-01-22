<?php
require_once 'config.php';

echo "Config loaded<br>";

if (!file_exists(DB_PATH)) {
    echo "Database file not found<br>";
    exit;
}

echo "Database file exists<br>";

try {
    $db = new PDO('sqlite:' . DB_PATH);
    echo "Database connected successfully<br>";
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}

echo "Testing getSiteSetting...<br>";

$rewrite_enabled = getSiteSetting('rewrite_enabled', 0);
echo "Rewrite enabled: " . $rewrite_enabled . "<br>";

echo "Testing encode_id...<br>";
$encoded = encode_id(1);
echo "Encoded ID: " . $encoded . "<br>";

echo "All tests completed<br>";
?>
