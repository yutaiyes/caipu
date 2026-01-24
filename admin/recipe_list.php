<?php
require'layout_header.php';

if(isset($_GET['delete'])){
$id=(int)$_GET['delete'];
$db->exec("DELETE FROM recipes WHERE id=$id");
header('Location: recipe_list.php');
exit;
}
$search=$_GET['search']??'';
$where=$search?"WHERE title LIKE '%$search%' OR description LIKE '%$search%'":'';
$list=$db->query("SELECT r.*, c.name as category_name FROM recipes r
LEFT JOIN categories c ON r.category_id=c.id
$where ORDER BY r.id DESC")->fetchAll();
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
<h3 class="mb-0"><i class="fas fa-utensils"></i> 菜谱列表</h3>
<div class="mt-2 mt-md-0">
<a href="recipe_add.php" class="btn btn-success">
<i class="fas fa-plus"></i> 新增菜谱
</a>
</div>
</div>
<div class="card">
<div class="card-header bg-white">
<form method="get" class="input-group">
<input type="text" name="search" class="form-control"
placeholder="搜索菜谱..." value="<?=htmlspecialchars($search)?>">
<button class="btn btn-primary" type="submit" title="搜索">
<i class="fas fa-search"></i>
</button>
<?php if($search):?>
<a href="recipe_list.php" class="btn btn-outline-secondary" title="清空搜索">
<i class="fas fa-times"></i>
</a>
<?php endif;?>
</form>
</div>
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th width="80">封面</th>
<th>菜名</th>
<th>分类</th>
<th>成本价</th>
<th>售价</th>
<th>利润</th>
<th>状态</th>
<th width="180">操作</th>
</tr>
</thead>
<tbody>
<?php foreach($list as $r):?>
<tr>
<td>
<?php if($r['cover']):?>
<div style="width:60px;height:60px;background-image:url('../image.php?file=<?=htmlspecialchars($r['cover'])?>');background-size:cover;background-position:center;border-radius:6px;background-color:#f8f9fa;"></div>
<?php else:?>
<div style="width:60px;height:60px;background:#f8f9fa;border-radius:6px;display:flex;align-items:center;justify-content:center;">
<i class="fas fa-utensils text-muted"></i>
</div>
<?php endif;?>
</td>
<td>
<div>
<strong class="d-block"><?=htmlspecialchars($r['title'])?></strong>
<?php if($r['description']):?>
<div class="small text-muted text-truncate" style="max-width:200px;">
<?=htmlspecialchars(mb_substr($r['description'],0,50))?>...
</div>
<?php endif;?>
</div>
</td>
<td>
<?php if($r['category_name']):?>
<span class="badge bg-info bg-opacity-10 text-info"><?=htmlspecialchars($r['category_name'])?></span>
<?php else:?>
<span class="text-muted small">未分类</span>
<?php endif;?>
</td>
<td class="text-primary fw-medium">¥<?=number_format($r['cost_price'],2)?></td>
<td class="text-success fw-medium">¥<?=number_format($r['sell_price'],2)?></td>
<td>
<?php
$profit=$r['sell_price']-$r['cost_price'];
$color=$profit>0?'success':'danger';
?>
<span class="fw-bold text-<?=$color?>">¥<?=number_format($profit,2)?></span>
</td>
<td>
<?php if($r['is_public']):?>
<span class="badge bg-success bg-opacity-10 text-success">公开</span>
<?php else:?>
<span class="badge bg-secondary bg-opacity-10 text-secondary">私有</span>
<?php endif;?>
</td>
<td>
<div class="btn-group btn-group-sm">
<a href="recipe_edit.php?id=<?=$r['id']?>" class="btn btn-outline-primary" title="编辑">
<i class="fas fa-edit"></i>
</a>
<?php
$rewrite_enabled = Config::get('rewrite_enabled', '0') === '1';
$base12 = encode_id($r['id']);
if ($rewrite_enabled) {
    $preview_url = FRONTEND_BASE_URL . $base12 . ".html";
} else {
    $preview_url = FRONTEND_BASE_URL . "recipe.php?base=" . $base12;
}
?>
<a href="<?= $preview_url ?>" target="_blank" class="btn btn-outline-success" title="预览">
<i class="fas fa-eye"></i>
</a>
<a href="?delete=<?=$r['id']?>" class="btn btn-outline-danger"
onclick="return confirm('确定删除《<?=htmlspecialchars($r['title'])?>》吗？')" title="删除">
<i class="fas fa-trash"></i>
</a>
</div>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
</div>
<?php if(empty($list)):?>
<div class="text-center py-5 text-muted">
<i class="fas fa-inbox fa-3x mb-3"></i>
<p>暂无菜谱数据</p>
</div>
<?php endif;?>
</div>
</div>
<?php require'layout_footer.php';?>

