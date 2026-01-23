<?php
// build.php - 打包发布脚本

$version = '1.0.1';
$outputZip = __DIR__ . "/Caipu_v{$version}.zip";

// 如果存在旧包，先删除
if (file_exists($outputZip)) {
    unlink($outputZip);
}

$zip = new ZipArchive();
if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("无法创建 ZIP 文件\n");
}

echo "正在打包 Caipu v{$version}...\n";

// 需要排除的文件和目录（相对于根目录）
$excludePatterns = [
    '/^\.git/',        // git目录和文件 (.git, .gitignore, .github 等)
    '/^\.github/',     // .github 目录
    '/^data\//',       // data 目录
    '/^backups\//',    // backups 目录
    '/^uploads\//',    // uploads 目录
    '/\.zip$/i',       // zip 文件
    '/^[^ \/\\\\]+\.md$/i', // 根目录下的 .md 文件 (不包含子目录中的 md)
    '/build\.php/',    // 本脚本
    '/\.idea/',        // IDE配置
    '/\.vscode/',      // IDE配置
    '/test.*/',        // 测试文件
];

// 必须包含的重要文件（即使被上面的规则排除，这里作为白名单检查，其实主要靠遍历逻辑控制）
// 这里主要逻辑是遍历所有文件，然后检查是否匹配排除规则

$rootPath = realpath(__DIR__);

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$count = 0;

foreach ($files as $name => $file) {
    // 获取相对路径
    $relativePath = substr($file->getRealPath(), strlen($rootPath) + 1);
    // 统一分隔符
    $relativePath = str_replace('\\', '/', $relativePath);

    // 检查排除规则
    $exclude = false;
    foreach ($excludePatterns as $pattern) {
        // 使用正则匹配
        // 注意：$relativePath 可能是 "index.php" 或 "admin/index.php"
        // 对于根目录文件，我们需要确保正则能正确匹配
        
        // 针对根目录文件的特殊处理 (例如 /^[^ \/\\\\]+\.md$/i)
        if ($pattern[1] === '^') {
             // 如果正则以 ^ 开头，直接匹配相对路径
             if (preg_match($pattern, $relativePath)) {
                 $exclude = true;
                 break;
             }
        } else {
             // 否则匹配路径中的任意部分 (为了兼容旧逻辑，还是建议加上 / 前缀或者调整正则)
             // 原有逻辑是 preg_match($pattern, '/' . $relativePath)
             if (preg_match($pattern, '/' . $relativePath)) {
                 $exclude = true;
                 break;
             }
        }
    }
    
    // 再次确认：如果排除的是 uploads 或 data，是否需要保留目录结构？
    // 用户明确要求忽略，所以这里不保留 data/empty.db 或 uploads/.gitkeep
    // 除非用户指令有误，否则严格执行。

    if ($exclude) {
        // echo "跳过: $relativePath\n";
        continue;
    }

    // 添加文件到ZIP
    $zip->addFile($file->getRealPath(), $relativePath);
    $count++;
}

$zip->close();

echo "打包完成！\n";
echo "文件位置: $outputZip\n";
echo "包含文件数: $count\n";
