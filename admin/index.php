<?php
require 'layout_header.php';

// 使用单个查询获取所有统计数据
try {
    $stats = $db->query("
        SELECT 
            COUNT(*) as total_recipes,
            COUNT(CASE WHEN is_public=1 THEN 1 END) as public_recipes,
            COUNT(CASE WHEN is_public=0 THEN 1 END) as private_recipes
        FROM recipes
    ")->fetch();
    
    $total_recipes = $stats['total_recipes'];
    $public_recipes = $stats['public_recipes'];
    $private_recipes = $stats['private_recipes'];
} catch (Exception $e) {
    $total_recipes = $public_recipes = $private_recipes = 0;
}

// 检查pages表是否存在
$pages_count = 0;
try {
    $pages_count = $db->query("SELECT COUNT(*) FROM pages")->fetchColumn();
} catch (Exception $e) {
    $pages_count = 0;
}

// 获取分类数量
$total_categories = 0;
try {
    $total_categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (Exception $e) {
    $total_categories = 0;
}

// 最近菜谱（限制查询字段）
$recent_recipes = [];
try {
    $recent_recipes = $db->query("SELECT id, title, created_at FROM recipes ORDER BY id DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $recent_recipes = [];
}

// 数据库大小
$db_size = 0;
if (defined('DB_PATH') && file_exists(DB_PATH)) {
    $db_size = filesize(DB_PATH);
}

// 备份数量
$backup_count = 0;
if (is_dir('../backups')) {
    $backup_files = glob('../backups/backup_*.zip');
    $backup_count = count($backup_files);
}
?>

<div class="page-header">
    <h3 class="mb-0"><i class="fas fa-chart-line"></i> 数据仪表板</h3>
</div>

<!-- 缓存清理提醒 -->
<?php if (isset($_COOKIE['cache_cleared']) && time() - $_COOKIE['cache_cleared'] < 300): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <h6><i class="fas fa-check-circle"></i> 缓存已清理</h6>
    <p class="mb-0">系统缓存已成功清理，伪静态规则已重新生成。请使用 <strong>Ctrl+F5</strong> 强制刷新浏览器测试。</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">总菜谱数</h6>
                    <h2 class="mb-0"><?= $total_recipes ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">公开菜谱</h6>
                    <h2 class="mb-0"><?= $public_recipes ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">分类数量</h6>
                    <h2 class="mb-0"><?= $total_categories ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">自定义页面</h6>
                    <h2 class="mb-0"><?= $pages_count ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">最近菜谱</h6>
                    <h2 class="mb-0"><?= count($recent_recipes) ?></h2>
                </div>
                <div class="fs-1 opacity-50">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 快捷功能入口 -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-th-large"></i> 快捷功能</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- 内容管理 -->
                    <div class="col-md-3 col-sm-6">
                        <a href="recipe_add.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-success">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>添加菜谱</h6>
                                    <small class="text-muted">创建新的菜谱</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="recipe_list.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-primary">
                                    <i class="fas fa-list"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>菜谱管理</h6>
                                    <small class="text-muted">查看和编辑菜谱</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="category.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-info">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>分类管理</h6>
                                    <small class="text-muted">管理菜谱分类</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="page_list.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-warning">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>页面管理</h6>
                                    <small class="text-muted">自定义页面</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- 系统设置 -->
                    <div class="col-md-3 col-sm-6">
                        <a href="site_settings.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-secondary">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>站点设置</h6>
                                    <small class="text-muted">网站基本信息</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="settings.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-dark">
                                    <i class="fas fa-sliders-h"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>系统设置</h6>
                                    <small class="text-muted">高级系统配置</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="settings.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-danger">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>安全设置</h6>
                                    <small class="text-muted">登录安全管理</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="profile.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-purple">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>个人资料</h6>
                                    <small class="text-muted">修改密码</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- 工具 -->
                    <div class="col-md-3 col-sm-6">
                        <a href="db_optimize.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-success">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>数据库优化</h6>
                                    <small class="text-muted">优化和备份</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="compress.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-info">
                                    <i class="fas fa-archive"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>备份管理</h6>
                                    <small class="text-muted">文件备份恢复</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <a href="compress_css.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-purple">
                                    <i class="fas fa-compress-alt"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>代码压缩</h6>
                                    <small class="text-muted">压缩CSS和JS文件</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="docs.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-primary">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>文档中心</h6>
                                    <small class="text-muted">使用帮助文档</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <a href="upload.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-warning">
                                    <i class="fas fa-upload"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>文件上传</h6>
                                    <small class="text-muted">上传图片文件</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                    <!-- 缓存管理 -->
                    <div class="col-md-3 col-sm-6">
                        <a href="clear_cache.php" class="text-decoration-none">
                            <div class="quick-link-card">
                                <div class="quick-link-icon bg-danger">
                                    <i class="fas fa-broom"></i>
                                </div>
                                <div class="quick-link-text">
                                    <h6>缓存管理</h6>
                                    <small class="text-muted">清理系统缓存</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 系统信息 -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> 系统信息</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td width="40%"><i class="fas fa-server text-primary"></i> PHP版本</td>
                        <td><strong><?= PHP_VERSION ?></strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-database text-success"></i> 数据库大小</td>
                        <td><strong><?= number_format($db_size / 1024, 2) ?> KB</strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-archive text-info"></i> 备份数量</td>
                        <td><strong><?= $backup_count ?> 个</strong></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-clock text-warning"></i> 服务器时间</td>
                        <td><strong><?= date('Y-m-d H:i:s') ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-link"></i> 快速链接</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="../index.php" target="_blank" class="list-group-item list-group-item-action">
                        <i class="fas fa-home text-primary"></i> 访问前台首页
                    </a>
                    <a href="../readme/" target="_blank" class="list-group-item list-group-item-action">
                        <i class="fas fa-folder-open text-warning"></i> 查看readme目录
                    </a>
                    <a href="debug.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-bug text-danger"></i> 系统调试信息
                    </a>
                    <a href="test.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-vial text-info"></i> 系统测试工具
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 最近菜谱 -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-clock"></i> 最近添加的菜谱</h5>
    </div>
    <div class="card-body">
        <?php if (empty($recent_recipes)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 暂无菜谱，<a href="recipe_add.php">点击添加第一个菜谱</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>菜名</th>
                        <th>成本价</th>
                        <th>售价</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_recipes as $r): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td>¥<?= number_format($r['cost_price'], 2) ?></td>
                        <td>¥<?= number_format($r['sell_price'], 2) ?></td>
                        <td>
                            <?php if ($r['is_public']): ?>
                            <span class="badge bg-success">公开</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">私有</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $r['created_at'] ?></td>
                        <td>
                            <a href="recipe_edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> 编辑
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'layout_footer.php'; ?>

