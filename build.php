<?php
// build.php - 打包发布脚本

$version = '1.0.0';
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
    '/\.git/',        // git目录
    '/\.idea/',       // IDE配置
    '/\.vscode/',     // IDE配置
    '/data\/data\.db/', // 运行时的数据库
    '/data\/.*\.log/', // 日志文件
    '/build\.php/',   // 本脚本
    '/\.zip$/',       // 压缩包
    '/test.*/',       // 测试文件
    '/backups/',      // 备份目录
    '/admin\/\.htaccess/', // 管理后台的伪静态文件
    '/uploads\/.*(?<!\.gitkeep)$/', // 上传目录中的所有文件（保留.gitkeep）
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
        if (preg_match($pattern, '/' . $relativePath)) { // 加前导斜杠以匹配路径开头
            $exclude = true;
            // 特殊处理：如果是 data/empty.db，不能排除
            if (strpos($relativePath, 'data/empty.db') !== false) {
                $exclude = false;
            }
            // 特殊处理：保留 uploads 目录结构 (.gitkeep)
            if (strpos($relativePath, 'uploads/') === 0 && strpos($relativePath, '.gitkeep') !== false) {
                $exclude = false;
            }
            break;
        }
    }

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
