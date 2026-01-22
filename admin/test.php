<?php
// 引入配置文件（包含时区设置）
require_once '../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 测试结果数组
$tests = [];

// 先启动session（layout_header也会检查）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Session 测试
$tests[] = ['name' => 'Session 状态', 'status' => 'success', 'message' => 'Session 已启动'];

// 2. 常量定义测试（将在layout_header中定义）
$tests[] = ['name' => '常量定义', 'status' => 'info', 'message' => 'ADMIN_ACCESS 常量将由系统定义'];

// 3. Security.php 加载测试（将在layout_header中加载）
$tests[] = ['name' => 'Security 模块', 'status' => 'info', 'message' => 'security.php 将由系统加载'];

// 4. 数据库连接测试
try {
    $db = new PDO('sqlite:../data/data.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tests[] = ['name' => '数据库连接', 'status' => 'success', 'message' => 'SQLite 数据库连接成功'];
    
    // 测试数据库表
    $tables = ['recipes', 'categories', 'admin', 'pages', 'settings'];
    $existing_tables = [];
    foreach ($tables as $table) {
        try {
            $db->query("SELECT 1 FROM $table LIMIT 1");
            $existing_tables[] = $table;
        } catch (Exception $e) {
            // 表不存在
        }
    }
    $tests[] = ['name' => '数据库表检查', 'status' => 'info', 'message' => '已存在的表: ' . implode(', ', $existing_tables)];
    
} catch (Exception $e) {
    $tests[] = ['name' => '数据库连接', 'status' => 'danger', 'message' => '数据库连接失败: ' . $e->getMessage()];
}

// 5. Session 变量测试
$_SESSION['test'] = 'Hello World';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'Hello World') {
    $tests[] = ['name' => 'Session 变量', 'status' => 'success', 'message' => 'Session 变量读写正常: ' . $_SESSION['test']];
} else {
    $tests[] = ['name' => 'Session 变量', 'status' => 'danger', 'message' => 'Session 变量读写失败'];
}

// 6. PHP 环境测试
$tests[] = ['name' => 'PHP 版本', 'status' => 'info', 'message' => 'PHP ' . PHP_VERSION];
$tests[] = ['name' => 'PHP SAPI', 'status' => 'info', 'message' => php_sapi_name()];

// 7. 扩展检查
$required_extensions = ['pdo', 'pdo_sqlite', 'zip', 'mbstring', 'json'];
$loaded_extensions = [];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        $loaded_extensions[] = $ext;
    } else {
        $missing_extensions[] = $ext;
    }
}
if (empty($missing_extensions)) {
    $tests[] = ['name' => 'PHP 扩展', 'status' => 'success', 'message' => '所有必需扩展已加载: ' . implode(', ', $loaded_extensions)];
} else {
    $tests[] = ['name' => 'PHP 扩展', 'status' => 'warning', 'message' => '缺少扩展: ' . implode(', ', $missing_extensions)];
}

// 8. 文件权限测试
$writable_dirs = ['../data', '../uploads', '../backups'];
$permission_tests = [];
foreach ($writable_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $permission_tests[] = $dir . ' ✓';
        } else {
            $permission_tests[] = $dir . ' ✗ (不可写)';
        }
    } else {
        $permission_tests[] = $dir . ' ✗ (不存在)';
    }
}
$all_writable = !preg_match('/✗/', implode('', $permission_tests));
$tests[] = [
    'name' => '目录权限', 
    'status' => $all_writable ? 'success' : 'warning', 
    'message' => implode('<br>', $permission_tests)
];

// 9. 内存和时区
$tests[] = ['name' => '内存限制', 'status' => 'info', 'message' => ini_get('memory_limit')];
$tests[] = ['name' => '时区设置', 'status' => 'info', 'message' => date_default_timezone_get()];

require 'layout_header.php';
?>

<div class="page-header">
    <h3 class="mb-0"><i class="fas fa-vial"></i> 系统测试工具</h3>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    <strong>测试说明：</strong>此页面用于检测系统环境、数据库连接、PHP配置等关键功能是否正常运行。
</div>

<!-- 测试结果统计 -->
<div class="row mb-4">
    <?php
    $success_count = count(array_filter($tests, function($t) { return $t['status'] === 'success'; }));
    $warning_count = count(array_filter($tests, function($t) { return $t['status'] === 'warning'; }));
    $danger_count = count(array_filter($tests, function($t) { return $t['status'] === 'danger'; }));
    $info_count = count(array_filter($tests, function($t) { return $t['status'] === 'info'; }));
    ?>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">通过测试</h6>
                    <h2 class="mb-0"><?= $success_count ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">警告项</h6>
                    <h2 class="mb-0"><?= $warning_count ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">失败项</h6>
                    <h2 class="mb-0"><?= $danger_count ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">信息项</h6>
                    <h2 class="mb-0"><?= $info_count ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-info-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 测试结果详情 -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-list-check"></i> 测试结果详情</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="50">序号</th>
                        <th width="200">测试项目</th>
                        <th width="120">状态</th>
                        <th>详细信息</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $index => $test): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><strong><?= htmlspecialchars($test['name']) ?></strong></td>
                        <td>
                            <?php
                            $badge_class = 'secondary';
                            $icon = 'circle';
                            switch ($test['status']) {
                                case 'success':
                                    $badge_class = 'success';
                                    $icon = 'check-circle';
                                    break;
                                case 'warning':
                                    $badge_class = 'warning';
                                    $icon = 'exclamation-triangle';
                                    break;
                                case 'danger':
                                    $badge_class = 'danger';
                                    $icon = 'times-circle';
                                    break;
                                case 'info':
                                    $badge_class = 'info';
                                    $icon = 'info-circle';
                                    break;
                            }
                            ?>
                            <span class="badge bg-<?= $badge_class ?>">
                                <i class="fas fa-<?= $icon ?>"></i>
                                <?= strtoupper($test['status']) ?>
                            </span>
                        </td>
                        <td><?= $test['message'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 系统信息 -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-server"></i> 服务器信息</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><i class="fas fa-desktop text-primary"></i> 操作系统</td>
                        <td><strong><?= PHP_OS ?></strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-code text-success"></i> PHP 版本</td>
                        <td><strong><?= PHP_VERSION ?></strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-cog text-info"></i> PHP SAPI</td>
                        <td><strong><?= php_sapi_name() ?></strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-globe text-warning"></i> 服务器软件</td>
                        <td><strong><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-sliders-h"></i> PHP 配置</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><i class="fas fa-memory text-primary"></i> 内存限制</td>
                        <td><strong><?= ini_get('memory_limit') ?></strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-upload text-success"></i> 上传限制</td>
                        <td><strong><?= ini_get('upload_max_filesize') ?></strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-clock text-info"></i> 执行时间</td>
                        <td><strong><?= ini_get('max_execution_time') ?>s</strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-globe-asia text-warning"></i> 时区</td>
                        <td><strong><?= date_default_timezone_get() ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 快速操作 -->
<div class="card mt-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-tools"></i> 快速操作</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <a href="debug.php" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="quick-link-icon bg-danger">
                            <i class="fas fa-bug"></i>
                        </div>
                        <div class="quick-link-text">
                            <h6>调试信息</h6>
                            <small class="text-muted">查看详细调试</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="db_optimize.php" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="quick-link-icon bg-success">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="quick-link-text">
                            <h6>数据库优化</h6>
                            <small class="text-muted">优化数据库</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="compress.php" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="quick-link-icon bg-info">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div class="quick-link-text">
                            <h6>备份管理</h6>
                            <small class="text-muted">备份恢复</small>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-3">
                <a href="index.php" class="text-decoration-none">
                    <div class="quick-link-card">
                        <div class="quick-link-icon bg-primary">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="quick-link-text">
                            <h6>返回首页</h6>
                            <small class="text-muted">管理后台</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require 'layout_footer.php'; ?>
