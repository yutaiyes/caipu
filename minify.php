<?php
function minifyPHP($code) {
    $code = preg_replace('/<!--.*?-->/s', '', $code);
    $code = preg_replace('/\/\*.*?\*\//s', '', $code);
    $code = preg_replace('/\/\/.*$/m', '', $code);
    $code = preg_replace('/\s+/', ' ', $code);
    $code = preg_replace('/\s*([{}();,:])\s*/', '$1', $code);
    $code = preg_replace('/\s*=\s*/', '=', $code);
    $code = preg_replace('/\s*\?\>\s*/', '?>', $code);
    $code = preg_replace('/\<\?php\s+/', '<?php ', $code);
    return trim($code);
}

$files = [
    'index.php',
    'recipe.php',
    'admin/index.php',
    'admin/login.php',
    'admin/logout.php',
    'admin/profile.php',
    'admin/category.php',
    'admin/recipe_list.php',
    'admin/recipe_add.php',
    'admin/recipe_edit.php',
    'admin/upload.php',
    'admin/layout_header.php',
    'admin/layout_footer.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $minified = minifyPHP($content);
        file_put_contents($file, $minified);
        echo "✓ Minified: $file\n";
    }
}

echo "\n✅ 所有文件都已成功压缩!\n";
?>
