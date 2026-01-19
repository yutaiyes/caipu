<?php
require 'layout_header.php';
try {
$db->query("SELECT 1 FROM pages LIMIT 1");
} catch (Exception $e) {
echo "<script>alert('请先运行数据库升级脚本：upgrade_pages.php');location.href='page_list.php';</script>";
exit;
}
if ($_POST) {
$stmt = $db->prepare("
INSERT INTO pages (title, slug, content, type, is_public, sort_order)
VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([
$_POST['title'],
$_POST['slug'],
$_POST['content'],
$_POST['type'],
$_POST['is_public'] ?? 1,
$_POST['sort_order'] ?? 0
]);
echo "<script>alert('添加成功！');location.href='page_list.php';</script>";
exit;
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-plus-circle"></i> 新增页面</h3>
</div>
<div class="card">
<div class="card-body">
<form method="post">
<div class="row">
<div class="col-12 col-md-6">
<div class="mb-3">
<label class="form-label">页面标题 <span class="text-danger">*</span></label>
<input class="form-control" name="title" placeholder="例如：关于我们" required>
</div>
</div>
<div class="col-12 col-md-6">
<div class="mb-3">
<label class="form-label">URL标识 <span class="text-danger">*</span></label>
<input class="form-control" name="slug" placeholder="例如：about（仅英文字母、数字、横线）" required>
<small class="text-muted">访问地址：page.php?slug=标识</small>
</div>
</div>
</div>
<div class="row">
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">页面类型</label>
<select class="form-select" name="type">
<option value="custom">自定义</option>
<option value="about">关于</option>
<option value="privacy">隐私</option>
<option value="contact">联系</option>
<option value="partnership">合作</option>
</select>
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">状态</label>
<select class="form-select" name="is_public">
<option value="1">公开</option>
<option value="0">隐藏</option>
</select>
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">排序</label>
<input type="number" class="form-control" name="sort_order" value="0" placeholder="数字越小越靠前">
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
<textarea id="md" name="content"></textarea>
</div>
<div class="d-flex flex-column flex-md-row gap-2">
<button type="submit" class="btn btn-primary">
<i class="fas fa-save"></i> 保存页面
</button>
<a href="page_list.php" class="btn btn-secondary">
<i class="fas fa-times"></i> 取消
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

