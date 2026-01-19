<?php
require 'layout_header.php';
if (isset($_POST['add'])) {
$stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
$stmt->execute([trim($_POST['name'])]);
header('Location: category.php');
exit;
}
if (isset($_POST['edit'])) {
$stmt = $db->prepare("UPDATE categories SET name=? WHERE id=?");
$stmt->execute([$_POST['name'], $_POST['id']]);
header('Location: category.php');
exit;
}
if (isset($_GET['del'])) {
$id = (int)$_GET['del'];
$count = $db->query("SELECT COUNT(*) FROM recipes WHERE category_id=$id")->fetchColumn();
if ($count > 0) {
echo "<script>alert('该分类下还有 $count 个菜谱，无法删除！');location.href='category.php';</script>";
exit;
}
$db->exec("DELETE FROM categories WHERE id=$id");
header('Location: category.php');
exit;
}
$list = $db->query("
SELECT c.*, COUNT(r.id) as recipe_count
FROM categories c
LEFT JOIN recipes r ON c.id = r.category_id
GROUP BY c.id
ORDER BY c.id DESC
")->fetchAll();
$edit_cat = null;
if (isset($_GET['edit'])) {
$edit_id = (int)$_GET['edit'];
$edit_cat = $db->query("SELECT * FROM categories WHERE id=$edit_id")->fetch();
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-tags"></i> 分类管理</h3>
</div>
<div class="row">
<div class="col-md-4">
<div class="card">
<div class="card-header bg-white">
<h5 class="mb-0">
<?= $edit_cat ? '<i class="fas fa-edit"></i> 编辑分类' : '<i class="fas fa-plus"></i> 新增分类' ?>
</h5>
</div>
<div class="card-body">
<form method="post">
<?php if ($edit_cat): ?>
<input type="hidden" name="id" value="<?= $edit_cat['id'] ?>">
<div class="mb-3">
<label class="form-label">分类名称</label>
<input class="form-control" name="name"
value="<?= htmlspecialchars($edit_cat['name']) ?>" required>
</div>
<button name="edit" class="btn btn-primary w-100">
<i class="fas fa-save"></i> 保存修改
</button>
<a href="category.php" class="btn btn-secondary w-100 mt-2">
<i class="fas fa-times"></i> 取消
</a>
<?php else: ?>
<div class="mb-3">
<label class="form-label">分类名称</label>
<input class="form-control" name="name" placeholder="请输入分类名称" required>
</div>
<button name="add" class="btn btn-success w-100">
<i class="fas fa-plus"></i> 添加分类
</button>
<?php endif; ?>
</form>
</div>
</div>
</div>
<div class="col-md-8">
<div class="card">
<div class="card-header bg-white">
<h5 class="mb-0"><i class="fas fa-list"></i> 分类列表</h5>
</div>
<div class="card-body">
<div class="table-responsive">
<table class="table table-hover">
<thead class="table-light">
<tr>
<th width="80">ID</th>
<th>分类名称</th>
<th>菜谱数量</th>
<th width="150">操作</th>
</tr>
</thead>
<tbody>
<?php foreach ($list as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td>
<strong><?= htmlspecialchars($c['name']) ?></strong>
</td>
<td>
<span class="badge bg-info"><?= $c['recipe_count'] ?>个</span>
</td>
<td>
<a href="?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
<i class="fas fa-edit"></i>
</a>
<a href="?del=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
onclick="return confirm('确定删除《<?= htmlspecialchars($c['name']) ?>》分类吗？')">
<i class="fas fa-trash"></i>
</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if (empty($list)): ?>
<div class="text-center py-5 text-muted">
<i class="fas fa-inbox fa-3x mb-3"></i>
<p>暂无分类数据</p>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
<?php require 'layout_footer.php'; ?>

