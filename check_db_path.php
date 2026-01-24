<?php
/**
 * 数据库查找工具
 * 用于测试和验证数据库自动查找功能
 */

// 引入配置文件
require_once __DIR__ . '/config.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><meta charset='UTF-8'><title>数据库查找测试</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;} .ok{color:green;} .warn{color:orange;} .error{color:red;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f4f4f4;}</style>";
echo "</head><body>";

echo "<h1>数据库查找功能测试</h1>\n";

// 测试1: 显示当前数据库路径
echo "<h2>当前数据库信息</h2>\n";
echo "<table>\n";
echo "<tr><th>项目</th><th>值</th></tr>\n";
echo "<tr><td>DB_PATH 常量</td><td>" . htmlspecialchars(DB_PATH) . "</td></tr>\n";
echo "<tr><td>文件存在</td><td>" . (file_exists(DB_PATH) ? '<span class="ok">✓ 是</span>' : '<span class="error">✗ 否</span>') . "</td></tr>\n";
if (file_exists(DB_PATH)) {
    echo "<tr><td>文件大小</td><td>" . number_format(filesize(DB_PATH)) . " 字节 (" . round(filesize(DB_PATH)/1024, 2) . " KB)</td></tr>\n";
    echo "<tr><td>最后修改</td><td>" . date('Y-m-d H:i:s', filemtime(DB_PATH)) . "</td></tr>\n";
    echo "<tr><td>文件权限</td><td>" . substr(sprintf('%o', fileperms(DB_PATH)), -4) . "</td></tr>\n";
}
echo "</table>\n";

// 测试2: 检测data目录下的所有数据库
echo "<h2>data目录下的所有.db文件</h2>\n";
$data_dir = dirname(DB_PATH);
$db_files = glob($data_dir . '/*.db');

if (empty($db_files)) {
    echo "<p class='warn'>⚠️ 未找到任何.db文件</p>\n";
} else {
    echo "<table>\n";
    echo "<tr><th>文件名</th><th>大小</th><th>最后修改</th><th>状态</th></tr>\n";
    foreach ($db_files as $file) {
        $status = ($file === DB_PATH) ? '<span class="ok">✓ 当前使用</span>' : '<span class="warn">未使用</span>';
        echo "<tr>\n";
        echo "<td>" . htmlspecialchars(basename($file)) . "</td>\n";
        echo "<td>" . number_format(filesize($file)) . " 字节</td>\n";
        echo "<td>" . date('Y-m-d H:i:s', filemtime($file)) . "</td>\n";
        echo "<td>" . $status . "</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    if (count($db_files) > 1) {
        echo "<p class='warn'>⚠️ 检测到多个数据库文件，系统只使用第一个找到的数据库</p>\n";
    }
}

// 测试3: 数据库连接测试
echo "<h2>数据库连接测试</h2>\n";
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='ok'>✓ 数据库连接成功</p>\n";
    
    // 检查表结构
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>数据库中的表</h3>\n";
    echo "<ul>\n";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>\n";
    }
    echo "</ul>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>✗ 数据库连接失败: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// 测试4: 安全性检查
echo "<h2>安全性检查</h2>\n";
echo "<table>\n";
echo "<tr><th>检查项</th><th>结果</th></tr>\n";

$htaccess = $data_dir . '/.htaccess';
if (file_exists($htaccess)) {
    echo "<tr><td>.htaccess 保护</td><td><span class='ok'>✓ 已存在</span></td></tr>\n";
} else {
    echo "<tr><td>.htaccess 保护</td><td><span class='warn'>⚠️ 不存在（建议创建以防止直接访问）</span></td></tr>\n";
}

if (file_exists($data_dir . '/index.html') || file_exists($data_dir . '/index.php')) {
    echo "<tr><td>index 文件保护</td><td><span class='ok'>✓ 已存在</span></td></tr>\n";
} else {
    echo "<tr><td>index 文件保护</td><td><span class='warn'>⚠️ 不存在</span></td></tr>\n";
}

echo "</table>\n";

// 测试5: 自动查找逻辑测试
echo "<h2>自动查找逻辑测试</h2>\n";
echo "<table>\n";
echo "<tr><th>场景</th><th>预期结果</th><th>实际结果</th></tr>\n";

// 场景1: 无数据库文件
if (empty($db_files)) {
    $expected = "caipudata.db";
    $actual = basename(DB_PATH);
    $match = (basename($expected) === $actual) ? '<span class="ok">✓ 匹配</span>' : '<span class="error">✗ 不匹配</span>';
    echo "<tr><td>无数据库文件时</td><td>$expected</td><td>$actual $match</td></tr>\n";
}

// 场景2: 只有一个数据库文件
if (count($db_files) === 1) {
    $expected = basename($db_files[0]);
    $actual = basename(DB_PATH);
    $match = ($expected === $actual) ? '<span class="ok">✓ 匹配</span>' : '<span class="error">✗ 不匹配</span>';
    echo "<tr><td>只有一个数据库文件时</td><td>$expected</td><td>$actual $match</td></tr>\n";
}

// 场景3: 有多个数据库文件
if (count($db_files) > 1) {
    $default_db = $data_dir . '/caipudata.db';
    $expected = in_array($default_db, $db_files) ? 'caipudata.db' : basename($db_files[0]);
    $actual = basename(DB_PATH);
    $match = ($expected === $actual) ? '<span class="ok">✓ 匹配</span>' : '<span class="error">✗ 不匹配</span>';
    echo "<tr><td>有多个数据库文件时</td><td>$expected</td><td>$actual $match</td></tr>\n";
}

echo "</table>\n";

echo "<hr>\n";
echo "<p><strong>测试完成！</strong></p>\n";
echo "<p><a href='admin/site_settings.php'>返回网站设置</a></p>\n";

echo "</body></html>";
?>
