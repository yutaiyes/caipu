<?php
require 'config.php';
require 'libs/Parsedown.php';
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
$id = intval($_GET['id'] ?? 0);
$timestamp = intval($_GET['t'] ?? 0);
if ($timestamp > 0) {
$data = $db->query("SELECT r.*, c.name as category_name
FROM recipes r
LEFT JOIN categories c ON r.category_id = c.id
WHERE strftime('%s', r.created_at) = '$timestamp' AND r.is_public=1")->fetch();
} else {
$data = $db->query("SELECT r.*, c.name as category_name
FROM recipes r
LEFT JOIN categories c ON r.category_id = c.id
WHERE r.id=$id AND r.is_public=1")->fetch();
}
if (!$data) {
header('Location: index.php');
exit;
}
$Parsedown = new Parsedown();
$html = $Parsedown->text($data['content']);
$related = [];
if ($data['category_id']) {
$current_id = $data['id'];
$related = $db->query("SELECT *, strftime('%s', created_at) as timestamp FROM recipes
WHERE category_id={$data['category_id']}
AND id != $current_id
AND is_public=1
LIMIT 3")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($data['title']) ?> - <?= htmlspecialchars($site_title) ?></title>
<meta name="description" content="<?= htmlspecialchars(mb_substr(strip_tags($data['content']), 0, 150)) ?>...">
<meta name="keywords" content="<?= htmlspecialchars($site_keywords) ?>,<?= htmlspecialchars($data['title']) ?>,<?= htmlspecialchars($data['category_name']) ?>">
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.5.0/github-markdown.min.css">
<link rel="stylesheet" href="assets/css/recipe-detail.css">
</head>
<body>
<!-- 顶部导航 -->
<nav class="navbar navbar-expand-lg navbar-dark">
<div class="container">
<a class="navbar-brand" href="index.php">
<i class="fas fa-utensils"></i> <?= htmlspecialchars($site_title) ?>
</a>
<div class="ms-auto">
<a class="nav-link d-inline-block" href="index.php">
<i class="fas fa-home"></i> 返回首页
</a>
</div>
</div>
</nav>
<!-- 主内容区域 -->
<div class="main-content">
<div class="container py-4">
<div class="row">
<!-- 主内容区 -->
<div class="col-lg-9">
<!-- 菜谱头部 -->
<div class="recipe-header">
<h1 class="recipe-title">
<i class="fas fa-utensils"></i>
<?= htmlspecialchars($data['title']) ?>
</h1>
<?php if($data['description']): ?>
<p class="lead text-muted"><?= htmlspecialchars($data['description']) ?></p>
<?php endif; ?>
<div class="recipe-meta-bar">
<?php if($data['category_name']): ?>
<div class="meta-item">
<i class="fas fa-tag"></i>
<div>
<div class="meta-label">分类</div>
<div class="meta-value"><?= htmlspecialchars($data['category_name']) ?></div>
</div>
</div>
<?php endif; ?>
<?php if($data['cost_price'] > 0): ?>
<div class="meta-item">
<i class="fas fa-coins"></i>
<div>
<div class="meta-label">成本价</div>
<div class="meta-value">¥<?= number_format($data['cost_price'], 2) ?></div>
</div>
</div>
<?php endif; ?>
<?php if($data['sell_price'] > 0): ?>
<div class="meta-item">
<i class="fas fa-money-bill-wave"></i>
<div>
<div class="meta-label">售价</div>
<div class="meta-value">¥<?= number_format($data['sell_price'], 2) ?></div>
</div>
</div>
<?php endif; ?>
<?php if($data['sell_price'] > 0 && $data['cost_price'] > 0): ?>
<div class="meta-item">
<i class="fas fa-chart-line"></i>
<div>
<div class="meta-label">利润</div>
<div class="meta-value text-success">
¥<?= number_format($data['sell_price'] - $data['cost_price'], 2) ?>
</div>
</div>
</div>
<?php endif; ?>
</div>
<div class="action-buttons">
<a href="index.php" class="btn-back">
<i class="fas fa-arrow-left"></i> 返回列表
</a>
<button onclick="window.print()" class="btn-print">
<i class="fas fa-print"></i> 打印菜谱
</button>
</div>
</div>
<!-- 菜谱内容 -->
<div class="recipe-content">
<div class="markdown-body">
<?= $html ?>
</div>
</div>
</div>
<!-- 侧边栏 -->
<div class="col-lg-3">
<!-- 价格信息 -->
<?php if($data['sell_price'] > 0): ?>
<div class="price-box mb-4">
<div class="price-label">建议售价</div>
<div class="price-value">¥<?= number_format($data['sell_price'], 2) ?></div>
<?php if($data['cost_price'] > 0): ?>
<div class="mt-2 small">
成本：¥<?= number_format($data['cost_price'], 2) ?><br>
利润：¥<?= number_format($data['sell_price'] - $data['cost_price'], 2) ?>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
<!-- 相关菜谱 -->
<?php if(!empty($related)): ?>
<div class="related-recipes">
<h5 class="mb-3">
<i class="fas fa-list"></i> 相关菜谱
</h5>
<?php foreach($related as $r): ?>
<a href="recipe.php?t=<?= $r['timestamp'] ?>" class="related-recipe-card">
<div class="related-recipe-title">
<?= htmlspecialchars($r['title']) ?>
</div>
<div class="related-recipe-price">
¥<?= number_format($r['sell_price'], 2) ?>
</div>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
<!-- 主内容区域结束 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>

