<?php
/**
 * 系统配置文件
 * System Configuration File
 */

// 引入公共函数库
require_once __DIR__ . '/includes/functions.php';

// 时区设置 (UTC+8 中国标准时间)
date_default_timezone_set('Asia/Shanghai');

// 管理后台目录名称（可自定义以增强安全性）
define('ADMIN_DIR', 'admin');

// 数据库文件路径
define('DB_PATH', __DIR__ . '/data/data.db');

// 自动检测并定义 BASE_URI
$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// 如果在admin目录下，向上一级
if (strpos($script_dir, '/admin') !== false) {
    $base_uri = dirname($script_dir) . '/';
} else {
    $base_uri = $script_dir . '/';
}
// 确保以/结尾且不含双斜杠
$base_uri = rtrim(str_replace('//', '/', $base_uri), '/') . '/';
define('BASE_URI', $base_uri);

// 上传目录
define('UPLOAD_DIR', __DIR__ . '/uploads');

// 默认配置常量
define('DEFAULT_SITE_TITLE', '商用菜谱库');
define('DEFAULT_SITE_DESC', '专业的商用菜谱管理系统');
define('DEFAULT_SITE_KEYWORDS', '菜谱,美食,烹饪,食谱,商用菜谱');
define('DEFAULT_SITE_AUTHOR', '商用菜谱库');
define('PER_PAGE', 12);

// 配置管理类
class Config {
    private static $settings = [];
    private static $db = null;
    private static $initialized = false;

    public static function init() {
        if (self::$initialized) return;

        try {
            if (!file_exists(DB_PATH)) return;
            
            self::$db = new PDO('sqlite:' . DB_PATH);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // 检查settings表是否存在
            $result = self::$db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
            if ($result) {
                $rows = self::$db->query("SELECT key, value FROM settings")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    self::$settings[$row['key']] = $row['value'];
                }
            }
        } catch (Exception $e) {
            // 静默失败，使用默认值
        }
        
        self::$initialized = true;
    }

    public static function get($key, $default = null) {
        if (!self::$initialized) self::init();
        return self::$settings[$key] ?? $default;
    }

    public static function set($key, $value) {
        if (!self::$initialized) self::init();
        self::$settings[$key] = $value;
        // 注意：这里只更新内存，不写入数据库。写入数据库需要单独处理，或者在Config中添加save方法
    }
}

// 初始化配置
Config::init();

// 环境模式配置
$env_mode = Config::get('environment_mode', 'production');
define('ENVIRONMENT_MODE', $env_mode);
define('DEBUG_MODE', $env_mode === 'development');

if (ENVIRONMENT_MODE === 'development') {
    // 开发环境：显示所有错误
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    // 生产环境：隐藏错误
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/data/error.log');
}

// 兼容旧函数 getSiteSetting
function getSiteSetting($key, $default = '') {
    return Config::get($key, $default);
}

// 动态网站配置变量（保持向下兼容）
$SITE_TITLE = Config::get('site_title', DEFAULT_SITE_TITLE);
$SITE_DESC = Config::get('site_description', DEFAULT_SITE_DESC);
$SITE_KEYWORDS = Config::get('site_keywords', DEFAULT_SITE_KEYWORDS);
$SITE_AUTHOR = Config::get('site_author', DEFAULT_SITE_AUTHOR);
?>
