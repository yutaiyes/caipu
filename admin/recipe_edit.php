<?php
require'layout_header.php';
$id=(int)$_GET['id'];
$recipe=$db->query("SELECT * FROM recipes WHERE id=$id")->fetch();
$cats=$db->query("SELECT * FROM categories")->fetchAll();
if(!$recipe){
echo "<script>alert('菜谱不存在！');location.href='recipe_list.php';</script>";
exit;
}
if($_POST){
$stmt=$db->prepare("
UPDATE recipes
SET title=?, description=?, content=?, category_id=?, cost_price=?, sell_price=?, is_public=?
WHERE id=?
");
$stmt->execute([
$_POST['title'],
$_POST['description'],
$_POST['content'],
$_POST['category_id']?:null,
$_POST['cost_price']?:0,
$_POST['sell_price']?:0,
$_POST['is_public'],
$id
]);
echo "<script>alert('更新成功！');location.href='recipe_list.php';</script>";
exit;
}
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
<h3 class="mb-0"><i class="fas fa-edit"></i> 编辑菜谱</h3>
<div class="btn-group mt-2 mt-md-0" role="group">
<button type="button" class="btn btn-sm btn-outline-primary" id="simpleMode">
<i class="fas fa-edit"></i> 简单模式
</button>
<button type="button" class="btn btn-sm btn-outline-primary active" id="advancedMode">
<i class="fas fa-code"></i> 高级模式
</button>
</div>
</div>
<div class="card">
<div class="card-body">
<form method="post">
<div class="row">
<div class="col-12 col-md-8">
<div class="mb-3">
<label class="form-label">菜名 <span class="text-danger">*</span></label>
<input class="form-control" name="title"
value="<?=htmlspecialchars($recipe['title'])?>" required>
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">分类</label>
<select class="form-select" name="category_id">
<option value="">未分类</option>
<?php foreach($cats as $c):?>
<option value="<?=$c['id']?>"
<?=$c['id']==$recipe['category_id']?'selected':''?>>
<?=htmlspecialchars($c['name'])?>
</option>
<?php endforeach;?>
</select>
</div>
</div>
</div>
<div class="mb-3">
<label class="form-label">简介</label>
<textarea class="form-control" name="description" rows="2"><?=htmlspecialchars($recipe['description'])?></textarea>
</div>
<div class="mb-3">
<div class="d-flex justify-content-between align-items-center mb-2">
<label class="form-label mb-0">详细内容</label>
<small class="text-muted">
<i class="fas fa-info-circle"></i> 支持Markdown格式
</small>
</div>
<textarea id="md" name="content"><?=htmlspecialchars($recipe['content'])?></textarea>
</div>
<div class="row">
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">成本价(元)</label>
<input type="number" step="0.01" class="form-control"
name="cost_price" value="<?=$recipe['cost_price']?>">
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">售价(元)</label>
<input type="number" step="0.01" class="form-control"
name="sell_price" value="<?=$recipe['sell_price']?>">
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">状态</label>
<select class="form-select" name="is_public">
<option value="1" <?=$recipe['is_public']?'selected':''?>>公开</option>
<option value="0" <?=!$recipe['is_public']?'selected':''?>>私有</option>
</select>
</div>
</div>
</div>
<div class="d-flex flex-column flex-md-row gap-2">
<button type="submit" class="btn btn-primary">
<i class="fas fa-save"></i> 保存修改
</button>
<?php
// 根据伪静态设置显示对应格式的预览URL
$rewrite_enabled = getSiteSetting('rewrite_enabled', 0) == '1';
$base12 = encode_id($id);
if ($rewrite_enabled) {
    // 开启伪静态：显示伪静态URI
    $preview_url = FRONTEND_BASE_URL . $base12 . ".html";
} else {
    // 关闭伪静态：显示动态地址（base12位）
    $preview_url = FRONTEND_BASE_URL . "recipe.php?base=" . $base12;
}
?>
<a href="<?= $preview_url ?>" target="_blank" class="btn btn-success">
<i class="fas fa-eye"></i> 预览页面
</a>
<a href="recipe_list.php" class="btn btn-secondary">
<i class="fas fa-times"></i> 取消
</a>
</div>
</form>
</div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<style>
/* 响应式编辑器样式 */
.EasyMDEContainer {
width: 100% !important;
}
.EasyMDEContainer .CodeMirror {
min-height: 300px;
height: auto;
}
@media (max-width: 768px) {
.EasyMDEContainer .CodeMirror {
min-height: 250px;
font-size: 14px;
}
.editor-toolbar {
padding: 5px !important;
}
.editor-toolbar button {
width: 28px !important;
height: 28px !important;
}
}
/* 简单模式样式 */
.simple-editor {
min-height: 300px;
font-family: monospace;
}
</style>
<script>
let easyMDE = null;
let isAdvancedMode = true;
// 初始化高级模式编辑器
function initAdvancedEditor() {
if (easyMDE) return;
easyMDE = new EasyMDE({
    element: document.getElementById("md"),
    uploadImage: true,
    imageUploadEndpoint: "upload.php",
    imageUploadFunction: function(file, onSuccess, onError) {
        const formData = new FormData();
        formData.append('file', file);
        
        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                onSuccess(data.url);
            } else {
                onError(data.message || '上传失败');
            }
        })
        .catch(error => {
            onError('上传失败: ' + error.message);
        });
    },
    placeholder: "请输入菜谱详细内容，支持Markdown格式...\n\n提示：\n- 使用 # 创建标题\n- 使用 ** 加粗文字\n- 使用 - 创建列表\n- 点击工具栏图标插入图片、链接等\n- 可以直接拖拽图片到编辑器上传",
    spellChecker: false,
    autosave: {
        enabled: true,
        uniqueId: "recipe_edit_<?=$id?>",
        delay: 1000,
    },
    toolbar: [
        "bold", "italic", "heading", "|",
        "quote", "unordered-list", "ordered-list", "|",
        "link", "upload-image", "|",
        "preview", "side-by-side", "fullscreen", "|",
        "guide"
    ],
    status: ["lines", "words", "cursor", "upload-image"],
    renderingConfig: {
        codeSyntaxHighlighting: true,
    }
});
}
// 切换到简单模式
function switchToSimpleMode() {
if (!isAdvancedMode) return;
const content = easyMDE ? easyMDE.value() : '';
// 销毁EasyMDE
if (easyMDE) {
easyMDE.toTextArea();
easyMDE = null;
}
// 显示简单文本框
const textarea = document.getElementById('md');
textarea.value = content;
textarea.className = 'form-control simple-editor';
textarea.style.display = 'block';
isAdvancedMode = false;
document.getElementById('simpleMode').classList.add('active');
document.getElementById('advancedMode').classList.remove('active');
}
// 切换到高级模式
function switchToAdvancedMode() {
if (isAdvancedMode) return;
const textarea = document.getElementById('md');
const content = textarea.value;
// 恢复textarea样式
textarea.className = '';
textarea.style.display = '';
// 初始化EasyMDE
initAdvancedEditor();
if (easyMDE) {
easyMDE.value(content);
}
isAdvancedMode = true;
document.getElementById('advancedMode').classList.add('active');
document.getElementById('simpleMode').classList.remove('active');
}
// 页面加载时初始化
document.addEventListener('DOMContentLoaded', function() {
initAdvancedEditor();
// 绑定切换按钮
document.getElementById('simpleMode').addEventListener('click', switchToSimpleMode);
document.getElementById('advancedMode').addEventListener('click', switchToAdvancedMode);
});
</script>
<?php require'layout_footer.php';?>

