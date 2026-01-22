<?php
/**
 * 检查页面数据库状态
 */

require_once 'config.php';

if (!file_exists(DB_PATH)) {
    die("数据库不存在，请先运行 install.php");
}

$db = new PDO('sqlite:' . DB_PATH);

echo "<h1>页面数据库检查</h1>";
echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; background: white; }
th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
th { background: #4CAF50; color: white; }
tr:hover { background: #f5f5f5; }
.status-ok { color: green; font-weight: bold; }
.status-error { color: red; font-weight: bold; }
.slug { font-family: monospace; background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
</style>";

// 检查pages表是否存在
try {
    $count = $db->query("SELECT COUNT(*) FROM pages")->fetchColumn();
    echo "<p>总页面数: <strong>$count</strong></p>";
} catch (Exception $e) {
    echo "<p class='status-error'>错误: pages表不存在 - " . $e->getMessage() . "</p>";
    exit;
}

// 显示所有页面
echo "<table>";
echo "<thead>";
echo "<tr>";
echo "<th>ID</th>";
echo "<th>标题</th>";
echo "<th>Slug</th>";
echo "<th>类型</th>";
echo "<th>状态</th>";
echo "<th>排序</th>";
echo "<th>访问链接</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

$pages = $db->query("SELECT * FROM pages ORDER BY sort_order")->fetchAll();

foreach ($pages as $page) {
    $is_public = $page['is_public'] ? '<span class="status-ok">公开</span>' : '<span class="status-error">隐藏</span>';
    $type = $page['type'] ?: '<span style="color: #999;">未设置</span>';
    $link = "<a href='page.php?slug={$page['slug']}' target='_blank'>查看</a>";

    echo "<tr>";
    echo "<td>{$page['id']}</td>";
    echo "<td>" . htmlspecialchars($page['title']) . "</td>";
    echo "<td><span class='slug'>" . htmlspecialchars($page['slug']) . "</span></td>";
    echo "<td>" . htmlspecialchars($type) . "</td>";
    echo "<td>$is_public</td>";
    echo "<td>{$page['sort_order']}</td>";
    echo "<td>$link</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

// 检查必要的页面
$required_slugs = ['about', 'contact', 'privacy', 'partnership'];
echo "<h2>必要页面检查</h2>";
echo "<table>";
echo "<tr>";
echo "<th>页面</th>";
echo "<th>Slug</th>";
echo "<th>状态</th>";
echo "</tr>";

foreach ($required_slugs as $slug) {
    $stmt = $db->prepare("SELECT id FROM pages WHERE slug=?");
    $stmt->execute([$slug]);
    $exists = $stmt->fetch();

    $status = $exists ? '<span class="status-ok">存在</span>' : '<span class="status-error">不存在</span>';
    $slug_display = [
        'about' => '关于我们',
        'contact' => '联系我们',
        'privacy' => '隐私政策',
        'partnership' => '合作伙伴'
    ];

    echo "<tr>";
    echo "<td>{$slug_display[$slug]}</td>";
    echo "<td><span class='slug'>$slug</span></td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><strong>操作建议：</strong></p>";
echo "<ul>";
echo "<li>如果某些必要页面不存在，请运行 <a href='upgrade_pages.php'>upgrade_pages.php</a></li>";
echo "<li>如果需要重新安装，请访问 <a href='install.php'>install.php</a></li>";
echo "<li>如果要添加新页面，请访问后台：<a href='admin/page_add.php'>添加页面</a></li>";
echo "</ul>";
