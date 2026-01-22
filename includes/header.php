<?php
// 前端公共头部文件
// 确保已经加载了配置和数据库连接
require_once __DIR__ . '/../config.php';

if (!file_exists(DB_PATH)) {
    header('Location: install.php');
    exit;
}
$db = new PDO('sqlite:' . DB_PATH);

// 自动设置 base_path（如果未设置）
if (!isset($base_path)) {
    // 简单的自动检测逻辑
    $base_path = './';
    // 如果在admin目录或includes目录（不应该直接访问），则需要调整
    // 但 header.php 通常是被根目录文件引入的，所以 ./ 是安全的
    // 如果是被子目录文件引入，调用者应该设置 $base_path
}

// 获取网站设置
$site_title = getSiteSetting('site_title', DEFAULT_SITE_TITLE);
$site_subtitle = getSiteSetting('site_subtitle', '专业的商用菜谱管理系统');
$site_slogan = getSiteSetting('site_slogan', '让美食触手可及');
$site_description = getSiteSetting('site_description', DEFAULT_SITE_DESC);
$site_keywords = getSiteSetting('site_keywords', DEFAULT_SITE_KEYWORDS);
$site_author = getSiteSetting('site_author', DEFAULT_SITE_AUTHOR);
$geo_region = getSiteSetting('geo_region', 'CN');
$geo_placename = getSiteSetting('geo_placename', '中国');
$geo_position = getSiteSetting('geo_position', '');

// 获取页面列表
$pages = [];
try {
    $pages = $db->query("SELECT * FROM pages WHERE is_public = 1 ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    // 如果pages表不存在，忽略错误
}

// 获取当前页面信息
$page_slug = null;
$page = null;
if (isset($_GET['slug'])) {
    $page_slug = $_GET['slug'];
    $page = $db->prepare("SELECT * FROM pages WHERE slug = ? AND is_public = 1");
    $page->execute([$page_slug]);
    $page = $page->fetch();
} elseif (isset($_GET['base'])) {
    $encoded_id = $_GET['base'];
    $page_id = decode_id($encoded_id);
    $page = $db->prepare("SELECT * FROM pages WHERE id = ? AND is_public = 1");
    $page->execute([$page_id]);
    $page = $page->fetch();
    if ($page) {
        $page_slug = $page['slug'];
    }
}

// 12位解码函数已移至 includes/functions.php

// 获取分类列表
$categories = $db->query("SELECT c.*, COUNT(r.id) as recipe_count
    FROM categories c
    LEFT JOIN recipes r ON c.id = r.category_id AND r.is_public=1
    GROUP BY c.id
    ORDER BY c.name")->fetchAll();
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <base href="<?= BASE_URI ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page ? $page['title'] . ' - ' : '') . htmlspecialchars($site_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page ? $page['description'] : $site_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page ? $page['keywords'] : $site_keywords) ?>">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?><?= htmlspecialchars($site_title) ?></title>
    <meta name="description" content="<?= isset($page_description) ? htmlspecialchars($page_description) : htmlspecialchars($site_description) ?>">
    <meta name="keywords" content="<?= isset($page_keywords) ? htmlspecialchars($page_keywords) : htmlspecialchars($site_keywords) ?>">
    <meta name="author" content="<?= htmlspecialchars($site_author) ?>">
    <?php if ($geo_region): ?>
    <meta name="geo.region" content="<?= htmlspecialchars($geo_region) ?>">
    <?php endif; ?>
    <?php if ($geo_placename): ?>
    <meta name="geo.placename" content="<?= htmlspecialchars($geo_placename) ?>">
    <?php endif; ?>
    <?php if ($geo_position): ?>
    <meta name="geo.position" content="<?= htmlspecialchars($geo_position) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= isset($base_path) ? $base_path : '' ?>assets/css/frontend.css">
    <?php if (isset($extra_css)): ?>
    <link rel="stylesheet" href="<?= isset($base_path) ? $base_path : '' ?>assets/css/<?= $extra_css ?>">
    <?php endif; ?>
</head>
<body>
<!-- 顶部导航 -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= isset($base_path) ? $base_path : '' ?>">
            <i class="fas fa-utensils"></i>
            <?= htmlspecialchars($site_title) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= isset($base_path) ? $base_path : '' ?>">
                        <i class="fas fa-home"></i> 首页
                    </a>
                </li>
                <?php if (!empty($categories)): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-list"></i> 分类
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= isset($base_path) ? $base_path : '' ?>index.php"><i class="fas fa-th"></i> 全部分类</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($categories as $cat): ?>
                        <?php if ($cat['recipe_count'] > 0): ?>
                        <li>
                            <a class="dropdown-item" href="<?= isset($base_path) ? $base_path : '' ?>index.php?cat=<?= $cat['id'] ?>">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($cat['name']) ?>
                                <span class="badge bg-secondary ms-1"><?= $cat['recipe_count'] ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if (!empty($pages)): ?>
                <?php foreach ($pages as $nav_page): ?>
                <li class="nav-item">
                    <a class="nav-link <?= isset($page_slug) && $page_slug == $nav_page['slug'] ? 'active' : '' ?>"
                       href="<?= isset($base_path) ? $base_path : '' ?>page.php?slug=<?= htmlspecialchars($nav_page['slug']) ?>">
                        <i class="fas fa-file-alt"></i> <?= htmlspecialchars($nav_page['title']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<!-- 主内容区域开始 -->
<div class="main-content">
