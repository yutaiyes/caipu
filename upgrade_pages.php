<?php
/**
 * 数据库升级脚本 - 添加页面管理功能
 * Database Upgrade Script - Add Page Management
 */

$db = new PDO('sqlite:data/data.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // 检查pages表是否已存在
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='pages'");
    if ($result->fetch()) {
        exit('✅ pages表已存在，无需升级');
    }
    
    // 创建页面表
    $db->exec("
    CREATE TABLE pages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        content TEXT,
        type TEXT DEFAULT 'custom',
        is_public INTEGER DEFAULT 1,
        sort_order INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ");
    
    // 插入默认页面
    $defaultPages = [
        ['关于我们', 'about', '# 关于我们\n\n这里是关于我们的内容...', 'about', 1, 1],
        ['隐私政策', 'privacy', '# 隐私政策\n\n这里是隐私政策的内容...', 'privacy', 1, 2],
        ['联系我们', 'contact', '# 联系我们\n\n这里是联系我们的内容...', 'contact', 1, 3],
        ['合作伙伴', 'partnership', '# 合作伙伴\n\n这里是合作伙伴的内容...', 'partnership', 1, 4],
    ];
    
    $stmt = $db->prepare("INSERT INTO pages (title, slug, content, type, is_public, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($defaultPages as $page) {
        $stmt->execute($page);
    }
    
    echo "✅ 数据库升级成功！\n";
    echo "✅ 已创建pages表并插入默认页面\n";
    echo "✅ 请删除此升级脚本文件\n";
    
} catch (Exception $e) {
    echo "❌ 升级失败: " . $e->getMessage();
}
?>
