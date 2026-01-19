<?php
require_once 'config.php';
require_once 'libs/Parsedown.php';
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
$slug = $_GET['slug'] ?? '';
$stmt = $db->prepare("SELECT * FROM pages WHERE slug=? AND is_public=1");
$stmt->execute([$slug]);
$page = $stmt->fetch();
if (!$page) {
header('HTTP/1.0 404 Not Found');
exit('页面不存在');
}
$categories = $db->query("SELECT c.*, COUNT(r.id) as recipe_count
FROM categories c
LEFT JOIN recipes r ON c.id = r.category_id AND r.is_public=1
GROUP BY c.id
ORDER BY c.name")->fetchAll();
$pages = $db->query("SELECT * FROM pages WHERE is_public=1 ORDER BY sort_order")->fetchAll();
$parsedown = new Parsedown();
$content = $parsedown->text($page['content']);
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page['title']) ?> - <?= htmlspecialchars($site_title) ?></title>
<meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($page['content']), 0, 150)) ?>...">
<meta name="keywords" content="<?= htmlspecialchars($site_keywords) ?>,<?= htmlspecialchars($page['title']) ?>">
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
<style>
.page-content {
background: white;
border-radius: 15px;
padding: 40px;
box-shadow: 0 2px 15px rgba(0,0,0,0.1);
margin: 40px 0;
}
.page-content h1 {
color: #6f42c1;
margin-bottom: 30px;
padding-bottom: 15px;
border-bottom: 3px solid #6f42c1;
}
.page-content h2 {
color: #6f42c1;
margin-top: 30px;
margin-bottom: 20px;
}
.page-content p {
line-height: 1.8;
color: #555;
}
@media (max-width: 768px) {
.page-content {
padding: 20px;
margin: 20px 0;
}
}
</style>
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
<a class="nav-link" href="index.php">
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
<a class="dropdown-item" href="index.php?cat=<?= $cat['id'] ?>">
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
<?php foreach($pages as $p): ?>
<li class="nav-item">
<a class="nav-link <?= $p['slug'] == $slug ? 'active' : '' ?>"
href="page.php?slug=<?= htmlspecialchars($p['slug']) ?>">
<i class="fas fa-file-alt"></i> <?= htmlspecialchars($p['title']) ?>
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
<div class="container pb-5">
<div class="page-content">
<?= $content ?>
</div>
<div class="text-center">
<a href="index.php" class="btn btn-primary">
<i class="fas fa-arrow-left"></i> 返回首页
</a>
</div>
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
<?php foreach($pages as $p): ?>
<a href="page.php?slug=<?= htmlspecialchars($p['slug']) ?>" class="me-3">
<?= htmlspecialchars($p['title']) ?>
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

