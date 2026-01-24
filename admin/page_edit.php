<?php
require 'layout_header.php';

try {
    $db->query("SELECT 1 FROM pages LIMIT 1");
} catch (Exception $e) {
?>
<script>
showMessage('请先运行数据库升级脚本：upgrade_pages.php', 'danger');
setTimeout(() => {
location.href='page_list.php';
}, 2000);
</script>
<?php
    exit;
}
$id = (int)$_GET['id'];
$page = $db->query("SELECT * FROM pages WHERE id=$id")->fetch();
if (!$page) {
?>
<script>
showMessage('页面不存在！', 'danger');
setTimeout(() => {
location.href='page_list.php';
}, 2000);
</script>
<?php
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
?>
<script>
showMessage('更新成功！', 'success');
setTimeout(() => {
location.href='page_list.php';
}, 500);
</script>
<?php
    exit;
}
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap">
<h3 class="mb-0"><i class="fas fa-edit"></i> 编辑页面</h3>
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
<a href="page_list.php" class="btn btn-secondary" title="取消">
<i class="fas fa-times"></i> 取消
</a>
<button type="submit" form="pageForm" class="btn btn-success" title="保存">
<i class="fas fa-save"></i> 保存修改
</button>
</div>
</div>
</div>
<div class="card">
<div class="card-body">
<form method="post" id="pageForm">
<div class="row">
<div class="col-12 col-md-6">
<div class="mb-3">
<label class="form-label">页面标题 <span class="text-danger">*</span></label>
<input class="form-control" name="title"
value="<?=htmlspecialchars($page['title'])?>" required>
</div>
</div>
<div class="col-12 col-md-6">
<div class="mb-3">
<label class="form-label">URL标识 <span class="text-danger">*</span></label>
<input class="form-control" name="slug"
value="<?=htmlspecialchars($page['slug'])?>" required>
<small class="text-muted">访问地址：page.php?slug=标识</small>
</div>
</div>
</div>
<div class="row">
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">页面类型</label>
<select class="form-select" name="type">
<option value="custom" <?=$page['type']=='custom'?'selected':''?>>自定义</option>
<option value="about" <?=$page['type']=='about'?'selected':''?>>关于</option>
<option value="privacy" <?=$page['type']=='privacy'?'selected':''?>>隐私</option>
<option value="contact" <?=$page['type']=='contact'?'selected':''?>>联系</option>
<option value="partnership" <?=$page['type']=='partnership'?'selected':''?>>合作</option>
</select>
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">状态</label>
<select class="form-select" name="is_public">
<option value="1" <?=$page['is_public']?'selected':''?>>公开</option>
<option value="0" <?=!$page['is_public']?'selected':''?>>隐藏</option>
</select>
</div>
</div>
<div class="col-12 col-md-4">
<div class="mb-3">
<label class="form-label">排序</label>
<input type="number" class="form-control" name="sort_order"
value="<?=$page['sort_order']?>">
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
<textarea id="md" name="content"><?=htmlspecialchars($page['content'])?></textarea>
</div>
<div class="d-flex flex-column flex-md-row gap-2">
<button type="submit" class="btn btn-primary">
<i class="fas fa-save"></i> 保存修改
</button>
<a href="page_list.php" class="btn btn-secondary">
<i class="fas fa-times"></i> 取消
</a>
<a href="<?=BASE_URI?>page.php?slug=<?=$page['slug']?>"
class="btn btn-info" target="_blank">
<i class="fas fa-eye"></i> 预览页面
</a>
</div>
</form>
</div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<style>
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
.simple-editor {
min-height: 300px;
font-family: monospace;
}
</style>
<script>
let easyMDE = null;
let isAdvancedMode = true;
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
    placeholder: "请输入页面内容，支持Markdown格式...\n\n提示：\n- 使用 # 创建标题\n- 使用 ** 加粗文字\n- 使用 - 创建列表\n- 点击工具栏图标插入图片、链接等\n- 可以直接拖拽图片到编辑器上传",
    spellChecker: false,
    autosave: {
        enabled: true,
        uniqueId: "page_edit_<?=$id?>",
        delay: 1000
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
        codeSyntaxHighlighting: true
    }
});
}
function switchToSimpleMode() {
if (!isAdvancedMode) return;
const content = easyMDE ? easyMDE.value() : '';
if (easyMDE) {
easyMDE.toTextArea();
easyMDE = null;
}
const textarea = document.getElementById('md');
textarea.value = content;
textarea.className = 'form-control simple-editor';
textarea.style.display = 'block';
isAdvancedMode = false;
document.getElementById('simpleMode').classList.add('active');
document.getElementById('advancedMode').classList.remove('active');
document.getElementById('simpleMode').classList.replace('btn-outline-primary', 'btn-primary');
document.getElementById('advancedMode').classList.replace('btn-primary', 'btn-outline-primary');
}
function switchToAdvancedMode() {
if (isAdvancedMode) return;
const textarea = document.getElementById('md');
const content = textarea.value;
textarea.className = '';
textarea.style.display = '';
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
<?php require 'layout_footer.php';?>
