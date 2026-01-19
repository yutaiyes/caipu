<?php
require_once 'config.php';
if (!file_exists(DB_PATH)) {
header('Location: install.php');
exit;
}
$db = new PDO('sqlite:data/data.db');
$site_title = getSiteSetting('site_title', SITE_TITLE);
$site_description = getSiteSetting('site_description', SITE_DESC);
$site_keywords = getSiteSetting('site_keywords', SITE_KEYWORDS);
$site_author = getSiteSetting('site_author', SITE_AUTHOR);
$geo_region = getSiteSetting('geo_region', 'CN');
$geo_placename = getSiteSetting('geo_placename', '中国');
$geo_position = getSiteSetting('geo_position', '');
$categories = $db->query("SELECT c.*, COUNT(r.id) as recipe_count
FROM categories c
LEFT JOIN recipes r ON c.id = r.category_id AND r.is_public=1
GROUP BY c.id
ORDER BY c.name")->fetchAll();
try {
$pages = $db->query("SELECT * FROM pages WHERE is_public=1 ORDER BY sort_order")->fetchAll();
} catch (Exception $e) {
$pages = [];
}
$category_filter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$where = $category_filter ? "WHERE is_public=1 AND category_id=$category_filter" : "WHERE is_public=1";
$list = $db->query("SELECT r.*, c.name as category_name, strftime('%s', r.created_at) as timestamp
FROM recipes r
LEFT JOIN categories c ON r.category_id = c.id
$where
ORDER BY r.id DESC")->fetchAll();
$total_recipes = $db->query("SELECT COUNT(*) FROM recipes WHERE is_public=1")->fetchColumn();
$total_categories = count($categories);
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($site_title) ?> - 精选美食菜谱</title>
<meta name="description" content="<?= htmlspecialchars($site_description) ?>">
<meta name="keywords" content="<?= htmlspecialchars($site_keywords) ?>">
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
<link rel="stylesheet" href="assets/css/frontend.css">
</head>
<body>
<!-- 顶部导航 -->
<nav class="navbar navbar-expand-lg navbar-dark">
<div class="container">
<a class="navbar-brand" href="index.php">
<i class="fas fa-utensils"></i>
<?= htmlspecialchars($site_title) ?>
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto">
<li class="nav-item">
<a class="nav-link active" href="index.php">
<i class="fas fa-home"></i> 首页
</a>
</li>
<?php if(!empty($categories)): ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
<i class="fas fa-list"></i> 分类
</a>
<ul class="dropdown-menu">
<li><a class="dropdown-item" href="index.php"><i class="fas fa-th"></i> 全部分类</a></li>
<li><hr class="dropdown-divider"></li>
<?php foreach($categories as $cat): ?>
<?php if($cat['recipe_count'] > 0): ?>
<li>
<a class="dropdown-item" href="?cat=<?= $cat['id'] ?>">
<i class="fas fa-tag"></i> <?= htmlspecialchars($cat['name']) ?>
<span class="badge bg-secondary ms-1"><?= $cat['recipe_count'] ?></span>
</a>
</li>
<?php endif; ?>
<?php endforeach; ?>
</ul>
</li>
<?php endif; ?>
<?php if(!empty($pages)): ?>
<?php foreach($pages as $page): ?>
<li class="nav-item">
<a class="nav-link" href="page.php?slug=<?= htmlspecialchars($page['slug']) ?>">
<i class="fas fa-file-alt"></i> <?= htmlspecialchars($page['title']) ?>
</a>
</li>
<?php endforeach; ?>
<?php endif; ?>
</ul>
</div>
</div>
</nav>
<!-- 主内容区域 -->
<div class="main-content">
<!-- Hero 区域 -->
<div class="hero-section">
<div class="container text-center">
<h1><i class="fas fa-book-open"></i> 精选美食菜谱</h1>
<p>专业的商用菜谱库，助力您的餐饮事业</p>
<div class="stats-box">
<div class="stat-item">
<span class="stat-number"><?= $total_recipes ?></span>
<span class="stat-label">道菜谱</span>
</div>
<div class="stat-item">
<span class="stat-number"><?= $total_categories ?></span>
<span class="stat-label">个分类</span>
</div>
</div>
</div>
</div>
<div class="container pb-5">
<!-- 分类筛选 -->
<?php if(!empty($categories)): ?>
<div class="category-filter">
<h5 class="mb-3"><i class="fas fa-filter"></i> 分类筛选</h5>
<a href="index.php" class="category-badge <?= !$category_filter ? 'active' : '' ?>">
<i class="fas fa-th"></i> 全部
<span class="badge bg-secondary"><?= $total_recipes ?></span>
</a>
<?php foreach($categories as $cat): ?>
<?php if($cat['recipe_count'] > 0): ?>
<a href="?cat=<?= $cat['id'] ?>" class="category-badge <?= $category_filter == $cat['id'] ? 'active' : '' ?>">
<i class="fas fa-tag"></i> <?= htmlspecialchars($cat['name']) ?>
<span class="badge bg-secondary"><?= $cat['recipe_count'] ?></span>
</a>
<?php endif; ?>
<?php endforeach; ?>
</div>
<?php endif; ?>
<!-- 菜谱列表 -->
<?php if(!empty($list)): ?>
<div class="row">
<?php foreach($list as $r): ?>
<div class="col-lg-4 col-md-6 mb-4">
<div class="recipe-card">
<div class="recipe-card-header">
<div class="recipe-card-title">
<i class="fas fa-utensils"></i>
<?= htmlspecialchars($r['title']) ?>
</div>
<?php if($r['category_name']): ?>
<div class="recipe-card-category">
<i class="fas fa-tag"></i> <?= htmlspecialchars($r['category_name']) ?>
</div>
<?php endif; ?>
</div>
<div class="recipe-card-body">
<div class="recipe-description">
<?= htmlspecialchars($r['description'] ?: '暂无简介') ?>
</div>
<div class="recipe-meta">
<div class="recipe-price">
¥<?= number_format($r['sell_price'], 2) ?>
<?php if($r['cost_price'] > 0): ?>
<small>成本 ¥<?= number_format($r['cost_price'], 2) ?></small>
<?php endif; ?>
</div>
<a href="recipe.php?t=<?= $r['timestamp'] ?>" class="btn-view-recipe">
<i class="fas fa-arrow-right"></i> 查看详情
</a>
</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
<i class="fas fa-inbox"></i>
<h4>暂无菜谱</h4>
<p>该分类下还没有菜谱，请选择其他分类或查看全部</p>
<?php if($category_filter): ?>
<a href="index.php" class="btn btn-primary mt-3">
<i class="fas fa-home"></i> 返回首页
</a>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
</div>
<!-- 主内容区域结束 -->
<!-- 页脚 -->
<footer class="footer">
<div class="container">
<div class="row">
<div class="col-md-6">
<h5><i class="fas fa-utensils"></i> <?= SITE_TITLE ?></h5>
<p class="text-muted"><?= SITE_DESC ?></p>
</div>
<div class="col-md-6 text-md-end">
<?php if(!empty($pages)): ?>
<p class="mb-2">
<?php foreach($pages as $page): ?>
<a href="page.php?slug=<?= htmlspecialchars($page['slug']) ?>" class="me-3">
<?= htmlspecialchars($page['title']) ?>
</a>
<?php endforeach; ?>
</p>
<?php endif; ?>
<p class="text-muted small mt-2">© 2026 <?= SITE_TITLE ?>. All rights reserved.</p>
</div>
</div>
</div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

