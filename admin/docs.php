<?php
require 'layout_header.php';
require '../libs/Parsedown.php';
$docs_dir = '../readme/';
$files = glob($docs_dir . '*.md');
$docs = [];
foreach ($files as $file) {
$filename = basename($file);
$name = str_replace('.md', '', $filename);
$icon = 'fa-file-alt';
if (strpos($name, 'README') !== false) $icon = 'fa-home';
elseif (strpos($name, 'QUICK') !== false) $icon = 'fa-rocket';
elseif (strpos($name, 'GUIDE') !== false) $icon = 'fa-book';
elseif (strpos($name, 'UPGRADE') !== false) $icon = 'fa-arrow-up';
elseif (strpos($name, 'COMPARISON') !== false) $icon = 'fa-balance-scale';
elseif (strpos($name, 'SUMMARY') !== false) $icon = 'fa-chart-bar';
elseif (strpos($name, 'FILE') !== false) $icon = 'fa-folder';
elseif (strpos($name, 'ADMIN') !== false) $icon = 'fa-user-shield';
elseif (strpos($name, 'FRONTEND') !== false) $icon = 'fa-desktop';
elseif (strpos($name, 'BACKEND') !== false) $icon = 'fa-server';
elseif (strpos($name, 'FEATURES') !== false) $icon = 'fa-star';
elseif (strpos($name, 'VISUAL') !== false) $icon = 'fa-eye';
elseif (strpos($name, 'PROJECT') !== false) $icon = 'fa-project-diagram';
elseif (strpos($name, 'SECURITY') !== false) $icon = 'fa-shield-alt';
elseif (strpos($name, 'RESPONSIVE') !== false) $icon = 'fa-mobile-alt';
elseif (strpos($name, 'DEBUG') !== false) $icon = 'fa-bug';
elseif (strpos($name, 'HOTFIX') !== false) $icon = 'fa-wrench';
elseif (strpos($name, 'UPDATE') !== false) $icon = 'fa-sync-alt';
elseif (strpos($name, 'LAYOUT') !== false) $icon = 'fa-th-large';
$docs[] = [
'filename' => $filename,
'name' => $name,
'slug' => $name,
'icon' => $icon,
'path' => $file
];
}
usort($docs, function($a, $b) {
if (strpos($a['name'], 'README') !== false) return -1;
if (strpos($b['name'], 'README') !== false) return 1;
return strcmp($a['name'], $b['name']);
});
$current_slug = $_GET['doc'] ?? '';
$current_doc = '';
$current_content = '';
$current_name = '';
$current_icon = 'fa-file-alt';
if (!empty($current_slug) && substr($current_slug, -3) === '.md') {
$clean_slug = str_replace('.md', '', $current_slug);
header('Location: docs.php?doc=' . urlencode($clean_slug));
exit;
}
if ($current_slug) {
foreach ($docs as $doc) {
if ($doc['slug'] === $current_slug) {
$current_doc = $doc['filename'];
$current_name = $doc['name'];
$current_icon = $doc['icon'];
break;
}
}
} else {
if (!empty($docs)) {
$current_doc = $docs[0]['filename'];
$current_name = $docs[0]['name'];
$current_icon = $docs[0]['icon'];
$current_slug = $docs[0]['slug'];
}
}
if ($current_doc) {
$doc_path = $docs_dir . $current_doc;
if (file_exists($doc_path)) {
$markdown = file_get_contents($doc_path);
$Parsedown = new Parsedown();
$current_content = $Parsedown->text($markdown);
}
}
?>
<div class="page-header d-flex justify-content-between align-items-center">
<h3 class="mb-0"><i class="fas fa-book"></i> 文档中心</h3>
<!-- 移动端文档选择按钮 -->
<button class="btn btn-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#docList">
<i class="fas fa-list"></i> 文档列表
</button>
</div>
<!-- 桌面端：文档选择器在右上方 -->
<div class="row mb-3 d-none d-md-flex">
<div class="col-md-12">
<div class="doc-selector-horizontal">
<label class="me-2"><i class="fas fa-file-alt"></i> 选择文档：</label>
<select class="form-select form-select-sm d-inline-block w-auto" onchange="if(this.value) location.href='?doc='+this.value">
<option value="">-- 请选择 --</option>
<?php foreach ($docs as $doc): ?>
<option value="<?= urlencode($doc['slug']) ?>" <?= $current_slug == $doc['slug'] ? 'selected' : '' ?>>
<?= htmlspecialchars($doc['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
</div>
<!-- 移动端：折叠的文档列表 -->
<div class="collapse d-md-none mb-3" id="docList">
<div class="doc-list-mobile">
<?php foreach ($docs as $doc): ?>
<a href="?doc=<?= urlencode($doc['slug']) ?>"
class="doc-link-mobile <?= $current_slug == $doc['slug'] ? 'active' : '' ?>">
<i class="fas <?= $doc['icon'] ?>"></i>
<?= htmlspecialchars($doc['name']) ?>
</a>
<?php endforeach; ?>
</div>
</div>
<!-- 文档内容 -->
<div class="row">
<div class="col-12">
<div class="doc-content">
<?php if ($current_content): ?>
<div class="doc-title-bar">
<h4><i class="fas <?= $current_icon ?>"></i> <?= htmlspecialchars($current_name) ?></h4>
</div>
<div class="markdown-body">
<?= $current_content ?>
</div>
<?php else: ?>
<div class="text-center text-muted py-5">
<i class="fas fa-file-alt fa-4x mb-3"></i>
<h4>请选择一个文档查看</h4>
<p>从上方下拉菜单中选择要查看的文档</p>
</div>
<?php endif; ?>
</div>
</div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.5.0/github-markdown.min.css">
<?php require 'layout_footer.php'; ?>

