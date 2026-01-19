<?php
session_start();
// 设置时区为 UTC+8
date_default_timezone_set('Asia/Shanghai');
$db_path = '../data/data.db';
$backup_dir = '../backups';
if (!is_dir($backup_dir)) {
@mkdir($backup_dir, 0755, true);
}
function getDatabaseInfo($db_path) {
if (!file_exists($db_path)) {
return null;
}
$info = [
'size' => filesize($db_path),
'size_mb' => round(filesize($db_path) / 1024 / 1024, 2),
'modified' => filemtime($db_path),
'readable' => is_readable($db_path),
'writable' => is_writable($db_path)
];
try {
$db = new PDO('sqlite:' . $db_path);
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
$info['tables'] = $tables;
$info['table_count'] = count($tables);
$info['records'] = [];
foreach ($tables as $table) {
$count = $db->query("SELECT COUNT(*) FROM " . $table)->fetchColumn();
$info['records'][$table] = $count;
}
$page_size = $db->query("PRAGMA page_size")->fetchColumn();
$page_count = $db->query("PRAGMA page_count")->fetchColumn();
$freelist_count = $db->query("PRAGMA freelist_count")->fetchColumn();
$info['page_size'] = $page_size;
$info['page_count'] = $page_count;
$info['freelist_count'] = $freelist_count;
$info['used_pages'] = $page_count - $freelist_count;
$info['fragmentation'] = $page_count > 0 ? round(($freelist_count / $page_count) * 100, 2) : 0;
} catch (Exception $e) {
$info['error'] = $e->getMessage();
}
return $info;
}
if (isset($_POST['action']) && $_POST['action'] === 'optimize') {
$success = true;
$message = '';
try {
if (!file_exists($db_path)) {
throw new Exception('数据库文件不存在');
}
if (!is_writable($db_path)) {
throw new Exception('数据库文件不可写，请检查文件权限');
}
$before_info = getDatabaseInfo($db_path);
$before_size = $before_info['size'];
$timestamp = date('Y-m-d_H-i-s');
$backup_file = $backup_dir . '/database_backup_' . $timestamp . '.db';
if (!copy($db_path, $backup_file)) {
throw new Exception('创建数据库备份失败');
}
$db = new PDO('sqlite:' . $db_path);
$db->exec('VACUUM');
$db->exec('ANALYZE');
$db = null;
$after_info = getDatabaseInfo($db_path);
$after_size = $after_info['size'];
$saved_size = $before_size - $after_size;
$saved_percent = $before_size > 0 ? round(($saved_size / $before_size) * 100, 2) : 0;
$message = "✓ 数据库优化成功！<br>";
$message .= "优化前大小：" . number_format($before_size / 1024, 2) . " KB<br>";
$message .= "优化后大小：" . number_format($after_size / 1024, 2) . " KB<br>";
$message .= "节省空间：" . number_format($saved_size / 1024, 2) . " KB ({$saved_percent}%)<br>";
$message .= "备份文件：" . basename($backup_file);
} catch (Exception $e) {
$success = false;
$message = '优化失败：' . $e->getMessage();
}
$_SESSION['db_optimize_message'] = $message;
$_SESSION['db_optimize_success'] = $success;
header('Location: db_optimize.php');
exit;
}
if (isset($_POST['action']) && $_POST['action'] === 'restore') {
$backup_file = $_POST['backup_file'] ?? '';
$success = false;
$message = '';
if (empty($backup_file) || !file_exists($backup_file)) {
$message = '备份文件不存在';
} else {
if (copy($backup_file, $db_path)) {
$success = true;
$message = '恢复成功！';
} else {
$message = '恢复失败，请检查文件权限';
}
}
$_SESSION['db_optimize_message'] = $message;
$_SESSION['db_optimize_success'] = $success;
header('Location: db_optimize.php');
exit;
}
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
$backup_file = $_POST['backup_file'] ?? '';
$success = false;
$message = '';
if (empty($backup_file) || !file_exists($backup_file)) {
$message = '备份文件不存在';
} else {
if (unlink($backup_file)) {
$success = true;
$message = '备份文件已删除';
} else {
$message = '删除失败，请检查文件权限';
}
}
$_SESSION['db_optimize_message'] = $message;
$_SESSION['db_optimize_success'] = $success;
header('Location: db_optimize.php');
exit;
}
require 'layout_header.php';
$show_message = false;
$message_text = '';
$message_type = 'success';
if (isset($_SESSION['db_optimize_message'])) {
$message_text = $_SESSION['db_optimize_message'];
$message_type = $_SESSION['db_optimize_success'] ? 'success' : 'danger';
$show_message = true;
unset($_SESSION['db_optimize_message']);
unset($_SESSION['db_optimize_success']);
}
$db_info = getDatabaseInfo($db_path);
$backups = [];
if (is_dir($backup_dir)) {
$backup_files = glob($backup_dir . '/database_backup_*.db');
rsort($backup_files);
foreach ($backup_files as $file) {
$backups[] = [
'file' => $file,
'name' => basename($file),
'size' => filesize($file),
'time' => filemtime($file)
];
}
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-database"></i> 数据库优化</h3>
</div>
<!-- 功能说明 -->
<div class="alert alert-info">
<h5><i class="fas fa-info-circle"></i> 功能说明</h5>
<ul class="mb-0">
<li><strong>VACUUM优化</strong>：使用SQLite官方的VACUUM命令优化数据库</li>
<li><strong>100%安全</strong>：不会丢失任何数据，只是重新整理数据库文件</li>
<li><strong>自动备份</strong>：优化前自动创建数据库备份，可随时恢复</li>
<li><strong>回收空间</strong>：删除记录后留下的空白空间会被回收</li>
<li><strong>整理碎片</strong>：重新组织数据，提高查询效率</li>
<li><strong>优化索引</strong>：重建索引，提升性能</li>
</ul>
</div>
<!-- 数据库信息 -->
<div class="card mb-4">
<div class="card-header bg-primary text-white">
<i class="fas fa-info-circle"></i> 数据库信息
</div>
<div class="card-body">
<?php if ($db_info): ?>
<div class="row">
<div class="col-md-6">
<h6>基本信息</h6>
<table class="table table-sm">
<tr>
<td width="150"><strong>文件大小：</strong></td>
<td><?= number_format($db_info['size'] / 1024, 2) ?> KB (<?= $db_info['size_mb'] ?> MB)</td>
</tr>
<tr>
<td><strong>最后修改：</strong></td>
<td><?= date('Y-m-d H:i:s', $db_info['modified']) ?></td>
</tr>
<tr>
<td><strong>表数量：</strong></td>
<td><?= $db_info['table_count'] ?> 个</td>
</tr>
<tr>
<td><strong>文件权限：</strong></td>
<td>
<?php if ($db_info['readable'] && $db_info['writable']): ?>
<span class="badge bg-success">可读写</span>
<?php elseif ($db_info['readable']): ?>
<span class="badge bg-warning">只读</span>
<?php else: ?>
<span class="badge bg-danger">无权限</span>
<?php endif; ?>
</td>
</tr>
</table>
</div>
<div class="col-md-6">
<h6>存储信息</h6>
<table class="table table-sm">
<tr>
<td width="150"><strong>页面大小：</strong></td>
<td><?= $db_info['page_size'] ?> 字节</td>
</tr>
<tr>
<td><strong>总页面数：</strong></td>
<td><?= number_format($db_info['page_count']) ?></td>
</tr>
<tr>
<td><strong>已使用页面：</strong></td>
<td><?= number_format($db_info['used_pages']) ?></td>
</tr>
<tr>
<td><strong>空闲页面：</strong></td>
<td><?= number_format($db_info['freelist_count']) ?></td>
</tr>
<tr>
<td><strong>碎片率：</strong></td>
<td>
<?php if ($db_info['fragmentation'] > 20): ?>
<span class="badge bg-danger"><?= $db_info['fragmentation'] ?>%</span>
<small class="text-danger">建议优化</small>
<?php elseif ($db_info['fragmentation'] > 10): ?>
<span class="badge bg-warning"><?= $db_info['fragmentation'] ?>%</span>
<small class="text-warning">可以优化</small>
<?php else: ?>
<span class="badge bg-success"><?= $db_info['fragmentation'] ?>%</span>
<small class="text-success">状态良好</small>
<?php endif; ?>
</td>
</tr>
</table>
</div>
</div>
<h6 class="mt-3">数据表统计</h6>
<div class="table-responsive">
<table class="table table-sm table-hover">
<thead>
<tr>
<th>表名</th>
<th>记录数</th>
</tr>
</thead>
<tbody>
<?php foreach ($db_info['records'] as $table => $count): ?>
<tr>
<td><code><?= htmlspecialchars($table) ?></code></td>
<td><?= number_format($count) ?> 条</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="alert alert-danger">
<i class="fas fa-exclamation-triangle"></i> 无法读取数据库信息
</div>
<?php endif; ?>
</div>
</div>
<!-- 优化操作 -->
<div class="card mb-4">
<div class="card-header bg-success text-white">
<i class="fas fa-magic"></i> 优化数据库
</div>
<div class="card-body">
<!-- 消息显示区域 -->
<?php if ($show_message): ?>
<div class="alert alert-<?= $message_type ?> alert-dismissible fade show mb-3">
<i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
<?= $message_text ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<form method="post" onsubmit="return confirm('确定要优化数据库吗？\n\n✓ 优化前会自动创建备份\n✓ 使用SQLite官方VACUUM命令\n✓ 100%安全，不会丢失数据\n✓ 可随时恢复备份\n\n优化可能需要几秒钟时间。');">
<input type="hidden" name="action" value="optimize">
<div class="mb-3">
<h6>VACUUM 优化说明</h6>
<p>VACUUM 是 SQLite 官方提供的数据库优化命令，它会：</p>
<ul>
<li>✅ 重建数据库文件，回收删除记录后的空白空间</li>
<li>✅ 整理数据碎片，将数据重新组织为连续存储</li>
<li>✅ 优化索引结构，提高查询效率</li>
<li>✅ 减小文件体积，节省磁盘空间</li>
<li>✅ 提升读写性能，加快数据库操作速度</li>
</ul>
</div>
<div class="mb-3">
<h6>何时需要优化？</h6>
<ul>
<li>删除了大量数据后</li>
<li>数据库碎片率超过10%</li>
<li>数据库文件异常增大</li>
<li>查询速度明显变慢</li>
<li>定期维护（建议每月一次）</li>
</ul>
</div>
<div class="alert alert-success">
<h6><i class="fas fa-shield-alt"></i> 安全保证</h6>
<ul class="mb-0">
<li><strong>官方命令</strong> - 使用SQLite官方的VACUUM命令</li>
<li><strong>100%安全</strong> - 不会丢失任何数据</li>
<li><strong>自动备份</strong> - 优化前自动创建数据库备份</li>
<li><strong>可以恢复</strong> - 如有问题可立即恢复备份</li>
<li><strong>原子操作</strong> - 要么全部成功，要么全部回滚</li>
</ul>
</div>
<div class="alert alert-warning">
<h6><i class="fas fa-exclamation-triangle"></i> 注意事项</h6>
<ul class="mb-0">
<li>优化期间数据库会被锁定，无法进行其他操作</li>
<li>优化时间取决于数据库大小，通常几秒到几十秒</li>
<li>优化期间请勿关闭浏览器或刷新页面</li>
<li>建议在网站访问量较少时进行优化</li>
</ul>
</div>
<button type="submit" class="btn btn-success btn-lg">
<i class="fas fa-magic"></i> 开始优化数据库
</button>
</form>
</div>
</div>
<!-- 备份管理 -->
<div class="card">
<div class="card-header bg-info text-white">
<i class="fas fa-history"></i> 数据库备份
</div>
<div class="card-body">
<?php if (empty($backups)): ?>
<div class="alert alert-info">
<i class="fas fa-info-circle"></i> 暂无数据库备份。优化数据库时会自动创建备份。
</div>
<?php else: ?>
<div class="table-responsive">
<table class="table table-hover">
<thead>
<tr>
<th width="50">序号</th>
<th>备份文件名</th>
<th width="120">文件大小</th>
<th width="180">创建时间</th>
<th width="250">操作</th>
</tr>
</thead>
<tbody>
<?php foreach ($backups as $index => $backup): ?>
<tr>
<td><?= $index + 1 ?></td>
<td>
<code><?= htmlspecialchars($backup['name']) ?></code>
</td>
<td>
<?= number_format($backup['size'] / 1024, 2) ?> KB
</td>
<td>
<?= date('Y-m-d H:i:s', $backup['time']) ?>
</td>
<td>
<form method="post" style="display:inline;"
onsubmit="return confirm('确定要恢复此备份吗？\n\n⚠️ 当前数据库将被覆盖！\n⚠️ 此操作不可撤销！');">
<input type="hidden" name="action" value="restore">
<input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['file']) ?>">
<button type="submit" class="btn btn-sm btn-success" title="恢复备份">
<i class="fas fa-undo"></i> 恢复
</button>
</form>
<a href="<?= htmlspecialchars($backup['file']) ?>"
class="btn btn-sm btn-info"
download
title="下载备份">
<i class="fas fa-download"></i> 下载
</a>
<form method="post" style="display:inline;"
onsubmit="return confirm('确定要删除此备份吗？\n\n⚠️ 删除后无法恢复！\n⚠️ 请确保已有其他备份！\n⚠️ 建议先下载备份到本地！');">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['file']) ?>">
<button type="submit" class="btn btn-sm btn-danger" title="删除备份">
<i class="fas fa-trash"></i> 删除
</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div class="alert alert-secondary mt-3">
<i class="fas fa-lightbulb"></i> <strong>提示：</strong>建议定期清理旧的备份文件，释放磁盘空间。删除前请先下载重要备份到本地。
</div>
<?php endif; ?>
</div>
</div>
<!-- 使用说明 -->
<div class="card mt-4">
<div class="card-header bg-secondary text-white">
<i class="fas fa-question-circle"></i> 使用说明
</div>
<div class="card-body">
<h6>什么是 VACUUM？</h6>
<p>VACUUM 是 SQLite 数据库的优化命令，类似于磁盘碎片整理。当你删除数据时，SQLite 不会立即释放空间，而是标记为"可重用"。VACUUM 会重建整个数据库文件，回收这些空间。</p>
<h6 class="mt-3">优化效果</h6>
<ul>
<li><strong>减小文件体积</strong>：回收删除数据后的空白空间</li>
<li><strong>提升查询速度</strong>：数据连续存储，减少磁盘寻道时间</li>
<li><strong>优化索引</strong>：重建索引，提高查询效率</li>
<li><strong>整理碎片</strong>：消除数据碎片，提升整体性能</li>
</ul>
<h6 class="mt-3">优化频率建议</h6>
<ul>
<li><strong>定期优化</strong>：建议每月优化一次</li>
<li><strong>大量删除后</strong>：删除大量数据后立即优化</li>
<li><strong>碎片率高时</strong>：碎片率超过10%时优化</li>
<li><strong>性能下降时</strong>：感觉数据库变慢时优化</li>
</ul>
<h6 class="mt-3">如何恢复？</h6>
<ol>
<li>在 "数据库备份" 区域找到对应的备份文件</li>
<li>点击 "恢复" 按钮</li>
<li>确认后系统自动恢复数据库</li>
<li>页面自动刷新，恢复完成</li>
</ol>
<h6 class="mt-3">技术细节</h6>
<ul>
<li><strong>VACUUM命令</strong>：SQLite官方提供的优化命令</li>
<li><strong>ANALYZE命令</strong>：分析数据库统计信息，优化查询计划</li>
<li><strong>原子操作</strong>：优化过程是原子的，不会出现部分成功的情况</li>
<li><strong>临时文件</strong>：优化时会创建临时文件，需要足够的磁盘空间</li>
</ul>
<h6 class="mt-3">注意事项</h6>
<ul>
<li>优化期间数据库会被锁定，无法进行其他操作</li>
<li>优化时间取决于数据库大小</li>
<li>需要足够的磁盘空间（至少是数据库大小的2倍）</li>
<li>建议在网站访问量较少时进行</li>
<li>优化前会自动创建备份，可随时恢复</li>
</ul>
</div>
</div>
<?php require 'layout_footer.php'; ?>

