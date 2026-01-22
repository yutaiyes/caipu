<?php
/**
 * 页面数据库升级脚本
 * 添加"合作伙伴"页面（如果不存在）
 */

require_once 'config.php';

if (!file_exists(DB_PATH)) {
    die("数据库不存在，请先运行 install.php");
}

$db = new PDO('sqlite:' . DB_PATH);

echo "开始升级页面数据...\n\n";

try {
    // 检查合作伙伴页面是否存在
    $stmt = $db->prepare("SELECT id FROM pages WHERE slug='partnership'");
    $stmt->execute();
    $exists = $stmt->fetch();

    if ($exists) {
        echo "✓ 合作伙伴页面已存在，跳过创建\n";
    } else {
        // 创建合作伙伴页面
        $stmt = $db->prepare("INSERT INTO pages (title, slug, content, type, is_public, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now'))");
        $stmt->execute([
            '合作伙伴',
            'partnership',
            '# 合作伙伴

欢迎与我们合作的优秀企业！

## 合作伙伴

我们与以下优秀企业建立了合作关系：

### 餐饮服务
- **供应商A** - 提供优质食材
- **供应商B** - 专业厨房设备

### 技术支持
- **技术公司A** - 系统开发
- **技术公司B** - 网络服务

### 物流配送
- **物流公司A** - 冷链配送
- **物流公司B** - 城市配送

## 合作咨询

如果您有兴趣与我们合作，欢迎联系！

',
            'partnership',
            1,
            4
        ]);

        echo "✓ 成功创建合作伙伴页面\n";
    }

    // 检查并更新现有页面的type字段（如果为空）
    $stmt = $db->query("SELECT id, slug FROM pages WHERE type IS NULL OR type=''");
    $null_type_pages = $stmt->fetchAll();

    if (!empty($null_type_pages)) {
        echo "\n更新页面类型...\n";
        $stmt = $db->prepare("UPDATE pages SET type=? WHERE id=?");

        foreach ($null_type_pages as $page) {
            // 根据slug推断type
            $type = 'custom';
            if ($page['slug'] == 'about') {
                $type = 'about';
            } elseif ($page['slug'] == 'contact') {
                $type = 'contact';
            } elseif ($page['slug'] == 'privacy') {
                $type = 'privacy';
            } elseif ($page['slug'] == 'partnership') {
                $type = 'partnership';
            }

            $stmt->execute([$type, $page['id']]);
            echo "  ✓ 更新页面 '{$page['slug']}' 的类型为 '{$type}'\n";
        }
    } else {
        echo "\n✓ 所有页面类型都已设置\n";
    }

    // 显示当前所有页面
    echo "\n当前页面列表：\n";
    echo "----------------------------------------\n";
    echo str_pad("ID", 5) . " | " . str_pad("标题", 12) . " | " . str_pad("Slug", 15) . " | " . str_pad("类型", 10) . " | 公开\n";
    echo "----------------------------------------\n";

    $pages = $db->query("SELECT * FROM pages ORDER BY sort_order")->fetchAll();
    foreach ($pages as $page) {
        $public = $page['is_public'] ? '是' : '否';
        echo str_pad($page['id'], 5) . " | " .
             str_pad(mb_substr($page['title'], 0, 10), 12) . " | " .
             str_pad($page['slug'], 15) . " | " .
             str_pad($page['type'] ?? '-', 10) . " | " . $public . "\n";
    }

    echo "\n========================================\n";
    echo "页面升级完成！\n";
    echo "========================================\n";

} catch (Exception $e) {
    echo "\n❌ 错误：" . $e->getMessage() . "\n";
    exit(1);
}
