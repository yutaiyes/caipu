<?php
require_once 'config.php';
require_once 'libs/Parsedown.php';

// 12位编码函数已移至 includes/functions.php

if (!file_exists(DB_PATH)) {
    header('Location: install.php');
    exit;
}

$db = new PDO('sqlite:' . DB_PATH);

// 根据伪静态设置处理URL重定向
$rewrite_enabled = Config::get('rewrite_enabled', '0') === '1';

// 如果通过slug访问且没有base参数
if (isset($_GET['slug']) && !isset($_GET['base'])) {
    // 查询ID
    $stmt = $db->prepare("SELECT id FROM pages WHERE slug=?");
    $stmt->execute([$_GET['slug']]);
    $p = $stmt->fetch();
    if ($p) {
        $base12 = encode_id($p['id'], 'page');
        if ($rewrite_enabled) {
            // 开启伪静态：重定向到伪静态URI
            $new_url = $base12 . '.html';
        } else {
            // 关闭伪静态：重定向到base12位的动态地址
            $new_url = 'page.php?base=' . $base12;
        }
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . $new_url);
        exit;
    }
}

// 获取页面参数（支持slug和12位编码）
$slug = $_GET['slug'] ?? '';
$base = $_GET['base'] ?? '';
$page = null;

if ($base) {
    // 使用12位编码查询
    $page_id = decode_id($base);
    $stmt = $db->prepare("SELECT * FROM pages WHERE id=? AND is_public=1");
    $stmt->execute([$page_id]);
    $page = $stmt->fetch();
} elseif ($slug) {
    // 使用slug查询（兼容旧格式）
    $stmt = $db->prepare("SELECT * FROM pages WHERE slug=? AND is_public=1");
    $stmt->execute([$slug]);
    $page = $stmt->fetch();
}

if (!$page) {
    header('HTTP/1.0 404 Not Found');
    exit('页面不存在');
}

// 解析Markdown内容
$parsedown = new Parsedown();
$content = $parsedown->text($page['content']);

// 设置页面特定变量
$page_title = $page['title'];
$page_description = mb_substr(strip_tags($page['content']), 0, 150) . '...';
$page_keywords = $page['title'];
$page_slug = $page['slug'];  // 用于高亮导航（优先使用原始slug）

// 引入公共头部
require_once 'includes/header.php';
?>

<!-- 面包屑导航 -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> 首页</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($page['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container">
    <div class="page-content">
        <div class="markdown-body">
            <?= $content ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
