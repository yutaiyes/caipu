<?php
require 'layout_header.php';
try {
$db->query("SELECT 1 FROM pages LIMIT 1");
} catch (Exception $e) {
echo "<script>alert('请先运行数据库升级脚本：upgrade_pages.php');location.href='page_list.php';</script>";
exit;
}
$id = (int)$_GET['id'];
$page = $db->query("SELECT * FROM pages WHERE id=$id")->fetch();
if (!$page) {
echo "<script>alert('页面不存在！');location.href='page_list.php';</script>";
exit;
}
if ($_POST) {
$stmt = $db->prepare("
UPDATE pages
SET title=?, slug=?, content=?, type=?, is_public=?, sort_order=?, updated_at=CURRENT_TIMESTAMP
WHERE id=?
");
$stmt->execute([
$_POST['title'],
$_POST['slug'],
$_POST['content'],
$_POST['type'],
$_POST['is_public'],
$_POST['sort_order'],
$id
]);
echo "<script>alert('更新成功！');location.href='page_list.php';</script>";
exit;
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-edit"></i> 编辑页面</h3>
</div>
<div class="card">
<div class="card-body">
<form method="post">
<div class="row">
<div class="col-12 col-md-6">
<div class="mb-3">
<label class="form-label">页面标题 <span class="text-danger">*</span></label>
<input class="form-control" name="title"
value="<?= htmlspecialchars($page['title']) ?>" required>
</div>
</div>
<div class="col-12 col-md-6">
<div class="mb-3">
<label class="form-label">URL标识 <span class="text-danger">*</span></label>
<input class="form-control" name="slug"
value="<?= htmlspecialchars($page['slug']) ?>" required>
<small class="text-muted">访问地址：page.php?slug=标识</small>
</div>
</div>
</div>
<div class="row">
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">页面类型</label>
<select class="form-select" name="type">
<option value="custom" <?= $page['type']=='custom'?'selected':'' ?>>自定义</option>
<option value="about" <?= $page['type']=='about'?'selected':'' ?>>关于</option>
<option value="privacy" <?= $page['type']=='privacy'?'selected':'' ?>>隐私</option>
<option value="contact" <?= $page['type']=='contact'?'selected':'' ?>>联系</option>
<option value="partnership" <?= $page['type']=='partnership'?'selected':'' ?>>合作</option>
</select>
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">状态</label>
<select class="form-select" name="is_public">
<option value="1" <?= $page['is_public']?'selected':'' ?>>公开</option>
<option value="0" <?= !$page['is_public']?'selected':'' ?>>隐藏</option>
</select>
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">排序</label>
<input type="number" class="form-control" name="sort_order"
value="<?= $page['sort_order'] ?>">
</div>
</div>
</div>
<div class="mb-3">
<div class="d-flex justify-content-between align-items-center mb-2">
<label class="form-label mb-0">页面内容</label>
<small class="text-muted">
<i class="fas fa-info-circle"></i> 支持Markdown格式
</small>
</div>
<textarea id="md" name="content"><?= htmlspecialchars($page['content']) ?></textarea>
</div>
<div class="d-flex flex-column flex-md-row gap-2">
<button type="submit" class="btn btn-primary">
<i class="fas fa-save"></i> 保存修改
</button>
<a href="page_list.php" class="btn btn-secondary">
<i class="fas fa-times"></i> 取消
</a>
<a href="../page.php?slug=<?= $page['slug'] ?>"
class="btn btn-info" target="_blank">
<i class="fas fa-eye"></i> 预览页面
</a>
</div>
</form>
</div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
const easyMDE = new EasyMDE({
element: document.getElementById("md"),
placeholder: "请输入页面内容，支持Markdown格式...",
spellChecker: false,
toolbar: [
"bold", "italic", "heading", "|",
"quote", "unordered-list", "ordered-list", "|",
"link", "image", "|",
"preview", "side-by-side", "fullscreen", "|",
"guide"
],
status: ["lines", "words", "cursor"]
});
</script>
<?php require 'layout_footer.php'; ?>

