<?php
require 'layout_header.php';
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-bug"></i> 程序调试</h3>
</div>
<div class="row">
<!-- PHP 环境 -->
<div class="col-md-6 mb-4">
<div class="card">
<div class="card-header bg-primary text-white">
<i class="fas fa-server"></i> PHP 环境
</div>
<div class="card-body">
<table class="table table-sm mb-0">
<tr>
<td><strong>PHP 版本</strong></td>
<td><?= phpversion() ?></td>
</tr>
<tr>
<td><strong>Session 支持</strong></td>
<td><?= function_exists('session_start') ? '<span class="badge bg-success">是</span>' : '<span class="badge bg-danger">否</span>' ?></td>
</tr>
<tr>
<td><strong>PDO 支持</strong></td>
<td><?= class_exists('PDO') ? '<span class="badge bg-success">是</span>' : '<span class="badge bg-danger">否</span>' ?></td>
</tr>
<tr>
<td><strong>SQLite 支持</strong></td>
<td><?= in_array('sqlite', PDO::getAvailableDrivers()) ? '<span class="badge bg-success">是</span>' : '<span class="badge bg-danger">否</span>' ?></td>
</tr>
</table>
</div>
</div>
</div>
<!-- Session 测试 -->
<div class="col-md-6 mb-4">
<div class="card">
<div class="card-header bg-success text-white">
<i class="fas fa-check-circle"></i> Session 测试
</div>
<div class="card-body">
<?php
try {
$_SESSION['test'] = 'Hello World';
echo '<div class="alert alert-success mb-2"><i class="fas fa-check"></i> Session 启动成功</div>';
echo '<div class="alert alert-info mb-0"><strong>测试值:</strong> ' . $_SESSION['test'] . '</div>';
} catch (Exception $e) {
echo '<div class="alert alert-danger"><i class="fas fa-times"></i> ' . $e->getMessage() . '</div>';
}
?>
</div>
</div>
</div>
<!-- 数据库测试 -->
<div class="col-md-6 mb-4">
<div class="card">
<div class="card-header bg-warning text-dark">
<i class="fas fa-database"></i> 数据库测试
</div>
<div class="card-body">
<?php
try {
$test_db = new PDO('sqlite:../data/data.db');
echo '<div class="alert alert-success mb-2"><i class="fas fa-check"></i> 数据库连接成功</div>';
$admin_count = $test_db->query("SELECT COUNT(*) FROM admin")->fetchColumn();
$recipe_count = $test_db->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$category_count = $test_db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
echo '<table class="table table-sm mb-0">';
echo '<tr><td>管理员数量</td><td><span class="badge bg-primary">' . $admin_count . '</span></td></tr>';
echo '<tr><td>菜谱数量</td><td><span class="badge bg-info">' . $recipe_count . '</span></td></tr>';
echo '<tr><td>分类数量</td><td><span class="badge bg-secondary">' . $category_count . '</span></td></tr>';
echo '</table>';
} catch (Exception $e) {
echo '<div class="alert alert-danger"><i class="fas fa-times"></i> ' . $e->getMessage() . '</div>';
}
?>
</div>
</div>
</div>
<!-- 文件检查 -->
<div class="col-md-6 mb-4">
<div class="card">
<div class="card-header bg-info text-white">
<i class="fas fa-folder"></i> 文件检查
</div>
<div class="card-body">
<table class="table table-sm mb-0">
<?php
$files = [
'security.php' => '安全配置',
'layout_header.php' => '头部布局',
'layout_footer.php' => '底部布局',
'../data/data.db' => '数据库文件'
];
foreach ($files as $file => $desc) {
$exists = file_exists($file);
$icon = $exists ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
echo "<tr><td>{$icon} {$desc}</td><td><code>{$file}</code></td></tr>";
}
?>
</table>
</div>
</div>
</div>
<!-- 权限检查 -->
<div class="col-md-12 mb-4">
<div class="card">
<div class="card-header bg-secondary text-white">
<i class="fas fa-lock"></i> 权限检查
</div>
<div class="card-body">
<div class="row">
<div class="col-md-6">
<h6>数据库文件</h6>
<?php
$db_file = '../data/data.db';
if (file_exists($db_file)) {
$readable = is_readable($db_file);
$writable = is_writable($db_file);
echo $readable ? '<div class="text-success"><i class="fas fa-check"></i> 可读</div>' : '<div class="text-danger"><i class="fas fa-times"></i> 不可读</div>';
echo $writable ? '<div class="text-success"><i class="fas fa-check"></i> 可写</div>' : '<div class="text-danger"><i class="fas fa-times"></i> 不可写</div>';
} else {
echo '<div class="text-danger"><i class="fas fa-times"></i> 文件不存在</div>';
}
?>
</div>
<div class="col-md-6">
<h6>上传目录</h6>
<?php
$upload_dir = '../uploads/images';
if (is_dir($upload_dir)) {
$writable = is_writable($upload_dir);
echo $writable ? '<div class="text-success"><i class="fas fa-check"></i> 可写</div>' : '<div class="text-danger"><i class="fas fa-times"></i> 不可写</div>';
} else {
echo '<div class="text-danger"><i class="fas fa-times"></i> 目录不存在</div>';
}
?>
</div>
</div>
</div>
</div>
</div>
<!-- 系统信息 -->
<div class="col-md-12">
<div class="card">
<div class="card-header bg-dark text-white">
<i class="fas fa-info-circle"></i> 系统信息
</div>
<div class="card-body">
<div class="row">
<div class="col-md-4">
<strong>操作系统:</strong> <?= PHP_OS ?>
</div>
<div class="col-md-4">
<strong>服务器软件:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?>
</div>
<div class="col-md-4">
<strong>当前用户:</strong> <?= htmlspecialchars($_SESSION['admin']) ?>
</div>
</div>
</div>
</div>
</div>
</div>
<?php require 'layout_footer.php'; ?>

