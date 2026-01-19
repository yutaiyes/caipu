<?php
date_default_timezone_set('Asia/Shanghai');
$db_path = 'data/data.db';
if (!file_exists($db_path)) {
die('错误：数据库文件不存在！');
}
try {
$db = new PDO('sqlite:' . $db_path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$table_exists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
if ($table_exists) {
echo "settings表已存在，检查是否需要添加新字段...\n";
$has_subtitle = $db->query("SELECT COUNT(*) FROM settings WHERE key='site_subtitle'")->fetchColumn();
$has_slogan = $db->query("SELECT COUNT(*) FROM settings WHERE key='site_slogan'")->fetchColumn();
$has_readme_browse = $db->query("SELECT COUNT(*) FROM settings WHERE key='enable_readme_browse'")->fetchColumn();
if (!$has_subtitle) {
$stmt = $db->prepare("INSERT INTO settings (key, value, description) VALUES (?, ?, ?)");
$stmt->execute(['site_subtitle', '专业的商用菜谱管理系统', '网站副标题']);
echo "✓ 添加 site_subtitle 字段成功！\n";
}
if (!$has_slogan) {
$stmt = $db->prepare("INSERT INTO settings (key, value, description) VALUES (?, ?, ?)");
$stmt->execute(['site_slogan', '让美食触手可及', '网站口号']);
echo "✓ 添加 site_slogan 字段成功！\n";
}
if (!$has_readme_browse) {
$stmt = $db->prepare("INSERT INTO settings (key, value, description) VALUES (?, ?, ?)");
$stmt->execute(['enable_readme_browse', '0', '启用readme目录浏览（0=关闭，1=开启）']);
echo "✓ 添加 enable_readme_browse 字段成功！\n";
}
if ($has_subtitle && $has_slogan && $has_readme_browse) {
echo "所有字段都已存在，无需添加。\n";
}
} else {
$sql = "CREATE TABLE settings (
id INTEGER PRIMARY KEY AUTOINCREMENT,
key TEXT NOT NULL UNIQUE,
value TEXT,
description TEXT,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
$db->exec($sql);
echo "✓ settings表创建成功！\n";
$default_settings = [
['site_title', '商用菜谱库', '网站标题'],
['site_subtitle', '专业的商用菜谱管理系统', '网站副标题'],
['site_slogan', '让美食触手可及', '网站口号'],
['site_description', '专业的商用菜谱管理系统', '网站描述'],
['site_keywords', '菜谱,美食,烹饪,食谱,商用菜谱', '网站关键词'],
['site_author', '商用菜谱库', '网站作者'],
['geo_region', 'CN', '地理区域代码'],
['geo_placename', '中国', '地理位置名称'],
['geo_position', '', '地理坐标 (纬度;经度)'],
['enable_readme_browse', '0', '启用readme目录浏览（0=关闭，1=开启）'],
];
$stmt = $db->prepare("INSERT INTO settings (key, value, description) VALUES (?, ?, ?)");
foreach ($default_settings as $setting) {
$stmt->execute($setting);
}
echo "✓ 默认设置插入成功！\n";
}
echo "\n数据库升级完成！\n";
echo "现在可以访问后台 -> 网站设置 进行配置。\n";
} catch (PDOException $e) {
die('数据库错误：' . $e->getMessage());
}
?>

