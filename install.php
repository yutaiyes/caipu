<?php
// 设置时区
date_default_timezone_set('Asia/Shanghai');

$dbFile = __DIR__ . '/data/data.db';

// 检查是否已安装
if (file_exists($dbFile)) {
    header('Location: index.php');
    exit;
}

// 处理安装请求
if (isset($_POST['install'])) {
    try {
        @mkdir(__DIR__ . '/data', 0777, true);
        
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
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
            ['关于我们', 'about', '# 关于我们\n\n欢迎来到商用菜谱库！\n\n我们致力于为餐饮行业提供专业的菜谱管理解决方案。'],
            ['联系我们', 'contact', '# 联系我们\n\n如有任何问题，欢迎联系我们。\n\n- 邮箱：contact@example.com\n- 电话：123-456-7890'],
            ['隐私政策', 'privacy', '# 隐私政策\n\n我们重视您的隐私保护。'],
        ];
        
        $stmt = $db->prepare("INSERT INTO pages (title, slug, content, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($pages as $index => $page) {
            $stmt->execute([$page[0], $page[1], $page[2], $index + 1]);
        }
        
        $install_success = true;
        
    } catch (Exception $e) {
        $install_error = $e->getMessage();
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
                    
                    <div class="alert alert-success">
                        <h5><i class="fas fa-info-circle"></i> 安装信息</h5>
                        <ul class="mb-0">
                            <li>✅ 数据库创建成功</li>
                            <li>✅ 数据表初始化完成</li>
                            <li>✅ 默认数据插入成功</li>
                            <li>✅ 系统配置完成</li>
                        </ul>
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
                    
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-star"></i> 系统功能
                        </div>
                        <div class="card-body">
                            <ul class="feature-list">
                                <li><i class="fas fa-utensils"></i> 菜谱管理 - 支持Markdown编辑，图片上传</li>
                                <li><i class="fas fa-tags"></i> 分类管理 - 灵活的分类体系</li>
                                <li><i class="fas fa-file-alt"></i> 页面管理 - 自定义页面内容</li>
                                <li><i class="fas fa-globe"></i> 网站设置 - SEO优化，GEO信息</li>
                                <li><i class="fas fa-compress"></i> 代码压缩 - 安全的代码优化</li>
                                <li><i class="fas fa-database"></i> 数据库优化 - VACUUM优化</li>
                                <li><i class="fas fa-book"></i> 文档中心 - 完整的使用文档</li>
                            </ul>
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
