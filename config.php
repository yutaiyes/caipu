<?php
/**
 * 系统配置文件
 * System Configuration File
 */

// 时区设置 (UTC+8 中国标准时间)
// Timezone setting (UTC+8 China Standard Time)
date_default_timezone_set('Asia/Shanghai');

// 管理后台目录名称（可自定义以增强安全性）
// Admin directory name (customizable for enhanced security)
define('ADMIN_DIR', 'admin');

// 数据库文件路径
// Database file path
define('DB_PATH', __DIR__ . '/data/data.db');

// 上传目录
// Upload directory
define('UPLOAD_DIR', __DIR__ . '/uploads');

// 网站标题
// Site title
define('SITE_TITLE', '商用菜谱库');

// 网站描述
// Site description
define('SITE_DESC', '专业的商用菜谱管理系统');

// 网站关键词
// Site keywords
define('SITE_KEYWORDS', '菜谱,美食,烹饪,食谱,商用菜谱');

// 网站作者
// Site author
define('SITE_AUTHOR', '商用菜谱库');

// 每页显示数量
// Items per page
define('PER_PAGE', 12);

// 是否开启调试模式
// Enable debug mode
define('DEBUG_MODE', false);

// 获取网站设置（从数据库读取，如果存在）
// Get site settings from database if exists
function getSiteSetting($key, $default = '') {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        // 检查settings表是否存在
        $table_exists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
        if ($table_exists) {
            $stmt = $db->prepare("SELECT value FROM settings WHERE key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetchColumn();
            return $result !== false ? $result : $default;
        }
    } catch (Exception $e) {
        // 如果出错，返回默认值
    }
    return $default;
}

// 动态网站配置（优先从数据库读取）
// Dynamic site configuration (read from database first)
$SITE_TITLE = getSiteSetting('site_title', SITE_TITLE);
$SITE_DESC = getSiteSetting('site_description', SITE_DESC);
$SITE_KEYWORDS = getSiteSetting('site_keywords', SITE_KEYWORDS);
$SITE_AUTHOR = getSiteSetting('site_author', SITE_AUTHOR);
?>
