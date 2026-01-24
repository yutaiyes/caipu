<?php
/**
 * 图片防盗链控制器 - 优化版
 * Image Hotlink Protection Controller - Optimized Version
 */

$upload_dir = __DIR__ . '/uploads';

// 获取请求的图片路径
$image_path = $_GET['file'] ?? '';

// 验证图片路径安全性
if (empty($image_path) || strpos($image_path, '..') !== false) {
    http_response_code(403);
    exit('Access denied');
}

// 允许的目录
$allowed_dirs = ['recipes', 'images'];
$path_parts = explode('/', trim($image_path, '/'));
if (count($path_parts) > 2 || (count($path_parts) > 1 && !in_array($path_parts[0], $allowed_dirs))) {
    http_response_code(403);
    exit('Access denied');
}

// 构建完整的图片路径
$full_path = $upload_dir . '/' . $image_path;

// 检查文件是否存在
if (!file_exists($full_path)) {
    http_response_code(404);
    exit('Image not found');
}

// 获取文件扩展名
$extension = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($extension, $allowed_extensions)) {
    http_response_code(403);
    exit('File type not allowed');
}

// 防盗链检查 - 支持本地和授权域名
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$current_host = $_SERVER['HTTP_HOST'] ?? '';
$allowed = false;

// 允许的情况
if (empty($referer)) {
    // 直接访问（无Referer）
    $allowed = true;
} else {
    $referer_host = parse_url($referer, PHP_URL_HOST);
    
    // 检查当前域名（带www和不带www）
    if ($referer_host === $current_host || 
        $referer_host === 'www.' . $current_host ||
        $current_host === 'www.' . $referer_host) {
        $allowed = true;
    }
    
    // 检查本地访问
    if (in_array($referer_host, ['localhost', '127.0.0.1', '::1'])) {
        $allowed = true;
    }
    
    // 检查本地IP段
    if (preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.)/', $referer_host)) {
        $allowed = true;
    }
    
    // 检查授权域名（从数据库获取）
    if (!$allowed) {
        try {
            $cache_hit = false;
            $allowed_domains = '';
            
            if (function_exists('apcu_fetch')) {
                $allowed_domains = apcu_fetch('allowed_domains_cache', $cache_hit);
            }
            
            if (!$cache_hit) {
                require_once 'config.php';
                $allowed_domains = Config::get('allowed_domains', '');
                if (function_exists('apcu_store')) {
                    apcu_store('allowed_domains_cache', $allowed_domains, 300);
                }
            }
            
            if ($allowed_domains) {
                $domains = array_map('trim', explode(',', $allowed_domains));
                foreach ($domains as $domain) {
                    $domain = trim($domain, " \t\n\r\0\x0B/");
                    if (!empty($domain) && 
                        ($referer_host === $domain || 
                         $referer_host === 'www.' . $domain ||
                         $domain === 'www.' . $referer_host)) {
                        $allowed = true;
                        break;
                    }
                }
            }
        } catch (Exception $e) {
        }
    }
}

if (!$allowed) {
    // 返回403
    http_response_code(403);
    exit('Hotlink not allowed');
}

// 设置正确的Content-Type和缓存头
$content_types = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'webp' => 'image/webp'
];

// 设置强缓存
header('Content-Type: ' . ($content_types[$extension] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($full_path));
header('Cache-Control: public, max-age=31536000, immutable'); // 1年缓存
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', filemtime($full_path)));

// 检查If-Modified-Since头
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($if_modified_since >= filemtime($full_path)) {
        header('HTTP/1.1 304 Not Modified');
        exit;
    }
}

// 输出图片内容
readfile($full_path);
?>
