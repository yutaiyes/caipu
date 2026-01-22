<?php
require_once 'config.php';
require_once 'includes/functions.php';

echo "测试新的编码函数（大小写混合）:\n";
echo "=================================\n\n";

// 测试编码
$test_ids = [1, 15, 999, 9999, 10000];

foreach ($test_ids as $id) {
    $encoded_recipe = encode_id($id, 'recipe');
    $encoded_page = encode_id($id, 'page');
    $decoded_recipe = decode_id($encoded_recipe);
    $decoded_page = decode_id($encoded_page);

    echo "ID: $id\n";
    echo "  Recipe编码: $encoded_recipe\n";
    echo "  Recipe解码: $decoded_recipe " . ($decoded_recipe == $id ? '✓' : '✗') . "\n";
    echo "  Page编码: $encoded_page\n";
    echo "  Page解码: $decoded_page " . ($decoded_page == $id ? '✓' : '✗') . "\n\n";
}
