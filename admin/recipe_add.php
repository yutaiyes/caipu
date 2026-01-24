<?php
require'layout_header.php';

$categories=$db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
if($_POST){
// 处理封面图片上传
$cover = null;
if(isset($_FILES['cover']) && $_FILES['cover']['error'] == 0){
    $upload_dir = __DIR__ . '/../uploads/';
    $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
    $filename = 'recipe_' . time() . '_' . uniqid() . '.' . $ext;
    if(move_uploaded_file($_FILES['cover']['tmp_name'], $upload_dir . $filename)){
        $cover = $filename;
    }
}

$stmt=$db->prepare("
INSERT INTO recipes (title, description, content, category_id, cost_price, sell_price, is_public, cover)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
$_POST['title'],
$_POST['description'],
$_POST['content'],
$_POST['category_id']?:null,
$_POST['cost_price']?:0,
$_POST['sell_price']?:0,
$_POST['is_public']??1,
$cover
]);
$new_id = $db->lastInsertId();
$rewrite_enabled = getSiteSetting('rewrite_enabled', 0);
if ($rewrite_enabled) {
    $preview_url = BASE_URI . encode_id($new_id) . ".html";
} else {
    $preview_url = BASE_URI . "recipe.php?id=" . $new_id;
}
?>
<script>
showMessage('添加成功！', 'success');
setTimeout(() => {
if(confirm('是否预览新添加的菜谱？')) {
    window.open('<?= $preview_url ?>', '_blank');
}
location.href='recipe_list.php';
}, 500);
</script>
<?php
exit;
}
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
<h3 class="mb-0"><i class="fas fa-plus-circle"></i> 新增菜谱</h3>
<div class="mt-2 mt-md-0 d-flex gap-2 flex-wrap">
<div role="group" class="btn-group btn-group-sm">
<button type="button" class="btn btn-outline-primary" id="simpleMode">
<i class="fas fa-edit"></i> 简单模式
</button>
<button type="button" class="btn btn-primary active" id="advancedMode">
<i class="fas fa-code"></i> 高级模式
</button>
</div>
<div role="group" class="btn-group btn-group-sm">
<a href="recipe_list.php" class="btn btn-secondary" title="取消">
<i class="fas fa-times"></i> 取消
</a>
<button type="submit" form="recipeForm" class="btn btn-success" title="保存">
<i class="fas fa-save"></i> 保存菜谱
</button>
</div>
</div>
</div>
<div class="card">
<div class="card-body">
<form method="post" id="recipeForm" enctype="multipart/form-data">
<!-- 基本信息 -->
<h6 class="text-primary mb-3 pb-2 border-bottom border-light">
<i class="fas fa-info-circle"></i> 基本信息
</h6>
<div class="row g-3">
<div class="col-12 col-md-8">
<div>
<label class="form-label">菜名 <span class="text-danger">*</span></label>
<input class="form-control" name="title" placeholder="请输入菜名" required>
</div>
</div>
<div class="col-12 col-md-4">
<div>
<label class="form-label">分类</label>
<select class="form-select" name="category_id">
<option value="">未分类</option>
<?php foreach($categories as $c):?>
<option value="<?=$c['id']?>">
<?=htmlspecialchars($c['name'])?>
</option>
<?php endforeach;?>
</select>
</div>
</div>
</div>
<div class="row g-3 mt-2">
<div class="col-12 col-md-6">
<div>
<label class="form-label">封面图片</label>
<input type="file" name="cover" class="form-control" accept="image/webp,image/jpeg,image/png,image/gif">
<div class="form-text">推荐尺寸：800x600px，支持WebP、JPG、PNG、GIF格式（WebP优先）</div>
</div>
</div>
<div class="col-12 col-md-6">
<div>
<label class="form-label">状态</label>
<select class="form-select" name="is_public">
<option value="1">公开</option>
<option value="0">私有</option>
</select>
</div>
</div>
</div>
<div class="mt-3">
<label class="form-label">简介</label>
<textarea class="form-control" name="description" rows="2"
placeholder="菜谱简短描述"></textarea>
</div>

<!-- 价格信息 -->
<h6 class="text-primary mb-3 mt-4 pb-2 border-bottom border-light">
<i class="fas fa-yen-sign"></i> 价格信息
</h6>
<div class="row g-3">
<div class="col-12 col-md-6">
<div>
<label class="form-label">成本价(元)</label>
<input type="number" step="0.01" class="form-control"
name="cost_price" value="0" placeholder="0.00">
</div>
</div>
<div class="col-12 col-md-6">
<div>
<label class="form-label">售价(元)</label>
<input type="number" step="0.01" class="form-control"
name="sell_price" value="0" placeholder="0.00">
</div>
</div>
</div>

<!-- 详细内容 -->
<h6 class="text-primary mb-3 mt-4 pb-2 border-bottom border-light">
<i class="fas fa-file-alt"></i> 详细内容
</h6>
<div>
<div class="d-flex justify-content-between align-items-center mb-2">
<label class="form-label mb-0">菜谱内容</label>
<span class="badge bg-light text-secondary">
<i class="fas fa-info-circle"></i> 支持Markdown格式
</span>
</div>
<textarea id="md" name="content"></textarea>
</div>

<!-- 操作按钮 -->
<div class="d-flex flex-column flex-md-row gap-2 mt-4 pt-3 border-top">
<button type="submit" class="btn btn-success">
<i class="fas fa-save"></i> 保存菜谱
</button>
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
if (typeof EasyMDE === 'undefined') {
    setTimeout(initAdvancedEditor, 50);
    return;
}
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
        uniqueId: "recipe_add",
        delay: 1000,
    },
    toolbar: [
        "undo", "redo", "|",
        "bold", "italic", "strikethrough", "heading", "heading-1", "heading-2", "heading-3", "|",
        "quote", "unordered-list", "ordered-list",
        {
            name: "insert-table",
            action: EasyMDE.drawTable,
            className: "fa fa-table",
            title: "Insert Table"
        },
        "code", "horizontal-rule", "|",
        "link", "image", "upload-image", "|",
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
const textarea = document.getElementById('md');
const content = easyMDE ? easyMDE.value() : textarea.value;
// 销毁EasyMDE
if (easyMDE) {
easyMDE.toTextArea();
easyMDE = null;
}
// 显示简单文本框
textarea.value = content;
textarea.className = 'form-control simple-editor';
textarea.style.display = 'block';
isAdvancedMode = false;
document.getElementById('simpleMode').classList.add('active');
document.getElementById('advancedMode').classList.remove('active');
document.getElementById('simpleMode').classList.replace('btn-outline-primary', 'btn-primary');
document.getElementById('advancedMode').classList.replace('btn-primary', 'btn-outline-primary');
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
document.getElementById('advancedMode').classList.replace('btn-outline-primary', 'btn-primary');
document.getElementById('simpleMode').classList.replace('btn-primary', 'btn-outline-primary');
}
function bindEditorModeEvents() {
initAdvancedEditor();
const simpleButton = document.getElementById('simpleMode');
const advancedButton = document.getElementById('advancedMode');
if (simpleButton) {
simpleButton.addEventListener('click', switchToSimpleMode);
}
if (advancedButton) {
advancedButton.addEventListener('click', switchToAdvancedMode);
}
}
if (document.readyState === 'loading') {
document.addEventListener('DOMContentLoaded', bindEditorModeEvents);
} else {
bindEditorModeEvents();
}
</script>
<?php require'layout_footer.php';?>
