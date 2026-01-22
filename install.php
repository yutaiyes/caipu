<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

$dbFile = __DIR__ . '/data/data.db';

// 检查是否已安装
if (file_exists($dbFile)) {
    header('Location: index.php');
    exit;
}

// 自动安装模式
$auto_install = !isset($_GET['manual']);

// 处理安装请求
if ($auto_install || isset($_POST['install'])) {
    $install_steps = [];
    $current_step = 0;
    
    try {
        // 步骤1：创建数据目录
        $current_step = 1;
        $install_steps[] = ['step' => 1, 'name' => '创建数据目录', 'status' => 'processing'];
        @mkdir(__DIR__ . '/data', 0777, true);
        @mkdir(__DIR__ . '/uploads', 0777, true);
        @mkdir(__DIR__ . '/backups', 0777, true);
        $install_steps[0]['status'] = 'success';
        
        // 步骤2：创建数据库
        $current_step = 2;
        $install_steps[] = ['step' => 2, 'name' => '创建数据库', 'status' => 'processing'];
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $install_steps[1]['status'] = 'success';
        
        // 步骤3：创建数据表
        $current_step = 3;
        $install_steps[] = ['step' => 3, 'name' => '创建数据表', 'status' => 'processing'];
        
        /* 菜谱表 */
        $db->exec("
        CREATE TABLE recipes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            content TEXT NOT NULL,
            cover TEXT,
            category_id INTEGER,
            cost_price REAL DEFAULT 0,
            sell_price REAL DEFAULT 0,
            is_public INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ");
        
        /* 分类表 */
        $db->exec("
        CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        );
        ");
        
        /* 管理员 */
        $db->exec("
        CREATE TABLE admin (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            password TEXT
        );
        ");
        
        /* 页面表 */
        $db->exec("
        CREATE TABLE pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            content TEXT NOT NULL,
            type TEXT DEFAULT 'custom',
            is_public INTEGER DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ");
        
        /* 设置表 */
        $db->exec("
        CREATE TABLE settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key TEXT NOT NULL UNIQUE,
            value TEXT,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        ");
        
        $install_steps[2]['status'] = 'success';
        
        // 步骤4：插入默认数据
        $current_step = 4;
        $install_steps[] = ['step' => 4, 'name' => '插入默认数据', 'status' => 'processing'];
        
        /* 默认管理员：admin / 123456 */
        $pwd = password_hash('123456', PASSWORD_DEFAULT);
        $db->prepare("INSERT INTO admin (username,password) VALUES (?,?)")
           ->execute(['admin', $pwd]);
        
        /* 插入默认设置 */
        $default_settings = [
            ['site_title', '商用菜谱库', '网站标题'],
            ['site_subtitle', '专业的商用菜谱管理系统', '网站副标题'],
            ['site_slogan', '让美食触手可及', '网站口号'],
            ['site_description', '专业的商用菜谱管理系统', '网站描述'],
            ['site_keywords', '菜谱,美食,烹饪,食谱,商用菜谱', '网站关键词'],
            ['site_author', '商用菜谱库', '网站作者'],
            ['geo_region', 'CN', '地理区域代码'],
            ['geo_placename', '中国', '地理位置名称'],
            ['geo_position', '', '地理坐标 (纬度;经度)'],
            ['rewrite_enabled', '0', '是否开启伪静态 (1:开启, 0:关闭)'],
        ];
        
        $stmt = $db->prepare("INSERT INTO settings (key, value, description) VALUES (?, ?, ?)");
        foreach ($default_settings as $setting) {
            $stmt->execute($setting);
        }
        
        /* 插入示例分类 */
        $categories = ['川菜', '粤菜', '湘菜', '鲁菜', '苏菜', '浙菜', '闽菜', '徽菜'];
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat]);
        }
        
        /* 插入示例页面 */
        $pages = [
            ['关于我们', 'about', '# 关于我们\n\n欢迎来到商用菜谱库！\n\n我们致力于为餐饮行业提供专业的菜谱管理解决方案。', 'about', 1],
            ['联系我们', 'contact', '# 联系我们\n\n如有任何问题，欢迎联系我们。\n\n- 邮箱：contact@example.com\n- 电话：123-456-7890', 'contact', 2],
            ['隐私政策', 'privacy', '# 隐私政策\n\n我们重视您的隐私保护。', 'privacy', 3],
            ['合作伙伴', 'partnership', '# 合作伙伴\n\n欢迎与我们合作的优秀企业！\n\n## 合作企业\n\n### 餐饮服务\n- **优质食材供应商** - 提供新鲜食材\n- **厨房设备商** - 专业厨房设备\n\n### 技术支持\n- **系统开发公司** - 技术支持\n- **网络服务商** - 网络保障\n\n## 合作咨询\n\n如有合作意向，欢迎联系我们！', 'partnership', 4],
        ];
        
        $stmt = $db->prepare("INSERT INTO pages (title, slug, content, type, is_public, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($pages as $page) {
            $stmt->execute($page);
        }
        
        $install_steps[3]['status'] = 'success';
        
        // 步骤5：完成安装
        $current_step = 5;
        $install_steps[] = ['step' => 5, 'name' => '完成安装', 'status' => 'success'];
        
        $install_success = true;
        
    } catch (Exception $e) {
        $install_error = $e->getMessage();
        if (isset($install_steps[$current_step - 1])) {
            $install_steps[$current_step - 1]['status'] = 'error';
            $install_steps[$current_step - 1]['error'] = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - 商用菜谱库</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .install-container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
        }
        .install-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .install-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        .install-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .install-body {
            padding: 40px;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            text-align: center;
            margin-bottom: 30px;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list i {
            color: #667eea;
            margin-right: 10px;
            width: 20px;
        }
        .btn-custom {
            padding: 15px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-outline-custom {
            border: 2px solid #667eea;
            color: #667eea;
            background: white;
        }
        .btn-outline-custom:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }
        .install-progress {
            margin: 30px 0;
        }
        .progress-step {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .progress-step.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .progress-step.processing {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .progress-step.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .progress-step-icon {
            font-size: 24px;
            margin-right: 15px;
            width: 30px;
            text-align: center;
        }
        .progress-step.success .progress-step-icon {
            color: #28a745;
        }
        .progress-step.processing .progress-step-icon {
            color: #ffc107;
            animation: spin 1s linear infinite;
        }
        .progress-step.error .progress-step-icon {
            color: #dc3545;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .progress-step-name {
            flex: 1;
            font-weight: 500;
        }
        .url-box {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .url-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 10px 0;
            background: white;
            border-radius: 5px;
        }
        .url-item i {
            font-size: 24px;
            margin-right: 15px;
            color: #667eea;
        }
        .url-item-content {
            flex: 1;
        }
        .url-item-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .url-item-link {
            font-size: 16px;
            font-weight: 600;
            color: #667eea;
            text-decoration: none;
        }
        .url-item-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            .install-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <h1><i class="fas fa-utensils"></i> 商用菜谱库</h1>
                <p>专业的商用菜谱管理系统</p>
            </div>
            
            <div class="install-body">
                <?php if (isset($install_success) && $install_success): ?>
                    <!-- 安装成功页面 -->
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <h2 class="text-center mb-4">🎉 安装成功！</h2>
                    
                    <!-- 安装进度 -->
                    <?php if (!empty($install_steps)): ?>
                    <div class="install-progress">
                        <h5 class="mb-3"><i class="fas fa-tasks"></i> 安装进度</h5>
                        <?php foreach ($install_steps as $step): ?>
                        <div class="progress-step <?= $step['status'] ?>">
                            <div class="progress-step-icon">
                                <?php if ($step['status'] == 'success'): ?>
                                <i class="fas fa-check-circle"></i>
                                <?php elseif ($step['status'] == 'processing'): ?>
                                <i class="fas fa-spinner"></i>
                                <?php else: ?>
                                <i class="fas fa-times-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="progress-step-name">
                                步骤 <?= $step['step'] ?>: <?= $step['name'] ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- 访问链接 -->
                    <div class="url-box">
                        <h5 class="mb-3"><i class="fas fa-link"></i> 访问地址</h5>
                        <?php
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                        $host = $_SERVER['HTTP_HOST'];
                        $base_url = $protocol . $host . dirname($_SERVER['PHP_SELF']);
                        $base_url = rtrim($base_url, '/');
                        $frontend_url = $base_url . '/index.php';
                        $backend_url = $base_url . '/admin/';
                        ?>
                        <div class="url-item">
                            <i class="fas fa-home"></i>
                            <div class="url-item-content">
                                <div class="url-item-label">前台首页</div>
                                <a href="<?= $frontend_url ?>" class="url-item-link" target="_blank">
                                    <?= $frontend_url ?>
                                </a>
                            </div>
                        </div>
                        <div class="url-item">
                            <i class="fas fa-user-shield"></i>
                            <div class="url-item-content">
                                <div class="url-item-label">管理后台</div>
                                <a href="<?= $backend_url ?>" class="url-item-link" target="_blank">
                                    <?= $backend_url ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-key"></i> 默认管理员账号
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>用户名：</strong><code class="ms-2">admin</code>
                                </div>
                                <div class="col-md-6">
                                    <strong>密码：</strong><code class="ms-2">123456</code>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>安全提示：</strong>请登录后台后立即修改默认密码！
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-secondary">
                        <h6><i class="fas fa-lightbulb"></i> 下一步操作</h6>
                        <ol class="mb-0">
                            <li>点击下方按钮进入系统</li>
                            <li>使用默认账号登录管理后台</li>
                            <li>修改管理员密码（后台 → 修改密码）</li>
                            <li>配置网站设置（后台 → 网站设置）</li>
                            <li>开始添加菜谱内容</li>
                        </ol>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="index.php" class="btn btn-outline-custom btn-custom">
                            <i class="fas fa-home"></i> 进入首页
                        </a>
                        <a href="admin/login.php" class="btn btn-primary-custom btn-custom">
                            <i class="fas fa-sign-in-alt"></i> 进入管理后台
                        </a>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt"></i> 
                            建议：安装成功后请删除 <code>install.php</code> 文件以提高安全性
                        </small>
                    </div>
                    
                <?php elseif (isset($install_error)): ?>
                    <!-- 安装失败页面 -->
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-circle"></i> 安装失败</h5>
                        <p class="mb-0"><?= htmlspecialchars($install_error) ?></p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="install.php" class="btn btn-primary-custom btn-custom">
                            <i class="fas fa-redo"></i> 重新安装
                        </a>
                    </div>
                    
                <?php else: ?>
                    <!-- 安装向导页面 -->
                    <h2 class="text-center mb-4">欢迎使用商用菜谱库</h2>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> 安装说明</h5>
                        <p class="mb-0">点击下方按钮开始安装，系统将自动创建数据库并初始化数据。</p>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-check-circle"></i> 系统要求
                        </div>
                        <div class="card-body">
                            <ul class="feature-list">
                                <li><i class="fas fa-check text-success"></i> PHP 7.4 或更高版本</li>
                                <li><i class="fas fa-check text-success"></i> PDO SQLite 扩展</li>
                                <li><i class="fas fa-check text-success"></i> ZipArchive 扩展（用于备份功能）</li>
                                <li><i class="fas fa-check text-success"></i> data 目录可写权限</li>
                                <li><i class="fas fa-check text-success"></i> uploads 目录可写权限</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-star"></i> 主要功能
                        </div>
                        <div class="card-body">
                            <ul class="feature-list">
                                <li><i class="fas fa-utensils"></i> 菜谱管理 - 支持Markdown编辑，图片上传</li>
                                <li><i class="fas fa-tags"></i> 分类管理 - 灵活的分类体系</li>
                                <li><i class="fas fa-file-alt"></i> 页面管理 - 自定义页面内容</li>
                                <li><i class="fas fa-globe"></i> 网站设置 - SEO优化，GEO信息</li>
                                <li><i class="fas fa-compress"></i> 代码压缩 - 安全的代码优化</li>
                                <li><i class="fas fa-database"></i> 数据库优化 - VACUUM优化</li>
                            </ul>
                        </div>
                    </div>
                    
                    <form method="post" class="text-center">
                        <button type="submit" name="install" class="btn btn-primary-custom btn-custom">
                            <i class="fas fa-rocket"></i> 开始安装
                        </button>
                    </form>
                    
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <p class="text-white">
                <i class="fas fa-heart"></i> 
                商用菜谱库 v1.0 - 专业的菜谱管理解决方案
            </p>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
