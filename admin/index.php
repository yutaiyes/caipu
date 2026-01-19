<?php
require 'layout_header.php';
$total_recipes = $db->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$public_recipes = $db->query("SELECT COUNT(*) FROM recipes WHERE is_public=1")->fetchColumn();
$total_categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$recent_recipes = $db->query("SELECT * FROM recipes ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-chart-line"></i> 数据仪表板</h3>
</div>
<!-- 统计卡片 -->
<div class="row">
<div class="col-md-4">
<div class="stat-card">
<div class="d-flex justify-content-between align-items-center">
<div>
<h6 class="mb-1 opacity-75">总菜谱数</h6>
<h2 class="mb-0"><?= $total_recipes ?></h2>
</div>
<div class="fs-1 opacity-50">
<i class="fas fa-book"></i>
</div>
</div>
</div>
</div>
<div class="col-md-4">
<div class="stat-card success">
<div class="d-flex justify-content-between align-items-center">
<div>
<h6 class="mb-1 opacity-75">公开菜谱</h6>
<h2 class="mb-0"><?= $public_recipes ?></h2>
</div>
<div class="fs-1 opacity-50">
<i class="fas fa-eye"></i>
</div>
</div>
</div>
</div>
<div class="col-md-4">
<div class="stat-card info">
<div class="d-flex justify-content-between align-items-center">
<div>
<h6 class="mb-1 opacity-75">分类数量</h6>
<h2 class="mb-0"><?= $total_categories ?></h2>
</div>
<div class="fs-1 opacity-50">
<i class="fas fa-tags"></i>
</div>
</div>
</div>
</div>
</div>
<!-- 最近菜谱 -->
<div class="card">
<div class="card-header bg-white">
<h5 class="mb-0"><i class="fas fa-clock"></i> 最近添加的菜谱</h5>
</div>
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover">
<thead>
<tr>
<th>ID</th>
<th>菜名</th>
<th>成本价</th>
<th>售价</th>
<th>状态</th>
<th>创建时间</th>
<th>操作</th>
</tr>
</thead>
<tbody>
<?php foreach($recent_recipes as $r): ?>
<tr>
<td><?= $r['id'] ?></td>
<td><?= htmlspecialchars($r['title']) ?></td>
<td>¥<?= number_format($r['cost_price'], 2) ?></td>
<td>¥<?= number_format($r['sell_price'], 2) ?></td>
<td>
<?php if($r['is_public']): ?>
<span class="badge bg-success">公开</span>
<?php else: ?>
<span class="badge bg-secondary">私有</span>
<?php endif; ?>
</td>
<td><?= $r['created_at'] ?></td>
<td>
<a href="recipe_edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
<i class="fas fa-edit"></i> 编辑
</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
</div>
<?php require 'layout_footer.php'; ?>

