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
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-utensils"></i> 菜谱列表</h3>
</div>
<div class="card">
<div class="card-header bg-white">
<div class="row align-items-center">
<div class="col-md-6">
<form method="get" class="d-flex">
<input type="text" name="search" class="form-control me-2"
placeholder="搜索菜谱..." value="<?=htmlspecialchars($search)?>">
<button class="btn btn-primary" type="submit">
<i class="fas fa-search"></i> 搜索
</button>
</form>
</div>
<div class="col-md-6 text-end">
<a href="recipe_add.php" class="btn btn-success">
<i class="fas fa-plus"></i> 新增菜谱
</a>
</div>
</div>
</div>
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover">
<thead class="table-light">
<tr>
<th width="60">ID</th>
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
<td><?=$r['id']?></td>
<td>
<strong><?=htmlspecialchars($r['title'])?></strong>
<?php if($r['description']):?>
<br><small class="text-muted"><?=htmlspecialchars(mb_substr($r['description'],0,30))?>...</small>
<?php endif;?>
</td>
<td>
<?php if($r['category_name']):?>
<span class="badge bg-info"><?=htmlspecialchars($r['category_name'])?></span>
<?php else:?>
<span class="text-muted">未分类</span>
<?php endif;?>
</td>
<td>¥<?=number_format($r['cost_price'],2)?></td>
<td>¥<?=number_format($r['sell_price'],2)?></td>
<td>
<?php
$profit=$r['sell_price']-$r['cost_price'];
$color=$profit>0?'success':'danger';
?>
<span class="text-<?=$color?>">¥<?=number_format($profit,2)?></span>
</td>
<td>
<?php if($r['is_public']):?>
<span class="badge bg-success">公开</span>
<?php else:?>
<span class="badge bg-secondary">私有</span>
<?php endif;?>
</td>
<td>
<a href="recipe_edit.php?id=<?=$r['id']?>" class="btn btn-sm btn-outline-primary" title="编辑">
<i class="fas fa-edit"></i>
</a>
<?php
// 根据伪静态设置显示对应格式的预览URL
$rewrite_enabled = Config::get('rewrite_enabled', '0') === '1';
$base12 = encode_id($r['id']);
if ($rewrite_enabled) {
    // 开启伪静态：显示伪静态URI
    $preview_url = FRONTEND_BASE_URL . $base12 . ".html";
} else {
    // 关闭伪静态：显示动态地址（base12位）
    $preview_url = FRONTEND_BASE_URL . "recipe.php?base=" . $base12;
}
?>
<a href="<?= $preview_url ?>" target="_blank" class="btn btn-sm btn-outline-success" title="预览">
<i class="fas fa-eye"></i>
</a>
<a href="?delete=<?=$r['id']?>" class="btn btn-sm btn-outline-danger"
onclick="return confirm('确定删除《<?=htmlspecialchars($r['title'])?>》吗？')" title="删除">
<i class="fas fa-trash"></i>
</a>
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

