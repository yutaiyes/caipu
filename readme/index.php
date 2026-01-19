<?php
/**
 * README 目录首页
 * 根据后台设置显示不同内容
 */

// 检查是否启用 readme 目录浏览
$db_path = __DIR__ . '/../data/data.db';
$enable_readme_browse = false;

if (file_exists($db_path)) {
    try {
        $db = new PDO('sqlite:' . $db_path);
        $stmt = $db->prepare("SELECT value FROM settings WHERE key = 'enable_readme_browse'");
        $stmt->execute();
        $result = $stmt->fetchColumn();
        $enable_readme_browse = ($result === '1' || $result === 'true');
    } catch (Exception $e) {
        // 如果出错，默认关闭
        $enable_readme_browse = false;
    }
}

// 如果未启用，重定向到后台文档中心
if (!$enable_readme_browse) {
    header("Location: ../admin/docs.php");
    exit;
}

// 以下是 readme 目录浏览功能
$docs = glob(__DIR__ . '/*.md');
sort($docs);

$current = $_GET['doc'] ?? 'README';
$file = __DIR__ . '/' . $current . '.md';

if (!file_exists($file)) {
    $file = __DIR__ . '/README.md';
}

require __DIR__ . '/../libs/Parsedown.php';
$Parsedown = new Parsedown();
$content = $Parsedown->text(file_get_contents($file));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>项目文档 - 商用菜谱管理系统</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/github-markdown-css/5.5.0/github-markdown.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        .navbar {
            background: var(--primary-gradient);
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 700;
            color: white !important;
        }
        
        .sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 20px;
            margin-bottom: 20px;
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
        }
        
        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .doc-link {
            display: block;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 8px;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .doc-link:hover {
            background: var(--primary-gradient);
            color: white;
            transform: translateX(5px);
        }
        
        .doc-link.active {
            background: var(--primary-gradient);
            color: white;
            font-weight: 500;
        }
        
        .doc-link i {
            width: 20px;
            margin-right: 8px;
        }
        
        .content-wrapper {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .markdown-body {
            max-width: 100%;
            font-size: 1rem;
            line-height: 1.8;
        }
        
        .markdown-body h1,
        .markdown-body h2 {
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .markdown-body code {
            background: #f6f8fa;
            padding: 2px 6px;
            border-radius: 3px;
        }
        
        .markdown-body pre {
            background: #f6f8fa;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary-gradient);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .back-to-top:hover {
            transform: translateY(-5px);
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 20px;
            }
            
            .sidebar {
                position: relative;
                top: 0;
                max-height: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-utensils"></i> 商用菜谱库
            </a>
            <div class="ms-auto">
                <a class="nav-link d-inline-block text-white" href="../index.php">
                    <i class="fas fa-home"></i> 返回首页
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-3">
                <div class="sidebar">
                    <div class="sidebar-title">
                        <i class="fas fa-book"></i> 文档目录
                    </div>
                    <?php
                    foreach ($docs as $doc) {
                        $name = basename($doc, '.md');
                        
                        // 根据文件名设置图标
                        $icon = 'fa-file-alt';
                        if ($name == 'README') {
                            $icon = 'fa-home';
                        } elseif (strpos($name, '01_') === 0) {
                            $icon = 'fa-rocket';
                        } elseif (strpos($name, '02_') === 0) {
                            $icon = 'fa-book-open';
                        } elseif (strpos($name, '03_') === 0) {
                            $icon = 'fa-cog';
                        } elseif (strpos($name, '04_') === 0) {
                            $icon = 'fa-magic';
                        } elseif (strpos($name, '05_') === 0) {
                            $icon = 'fa-shield-alt';
                        } elseif (strpos($name, '06_') === 0) {
                            $icon = 'fa-wrench';
                        } elseif (strpos($name, '07_') === 0) {
                            $icon = 'fa-history';
                        } elseif (strpos($name, '08_') === 0) {
                            $icon = 'fa-question-circle';
                        } elseif (strpos($name, '09_') === 0) {
                            $icon = 'fa-code';
                        } elseif (strpos($name, '10_') === 0) {
                            $icon = 'fa-phone';
                        }
                        
                        $active = ($current == $name) ? 'active' : '';
                        $title = str_replace(['_', '-'], ' ', $name);
                        
                        echo "<a href='?doc={$name}' class='doc-link {$active}'>";
                        echo "<i class='fas {$icon}'></i>{$title}";
                        echo "</a>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="content-wrapper">
                    <div class="markdown-body">
                        <?= $content ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="back-to-top" id="backToTop" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
        <i class="fas fa-arrow-up"></i>
    </div>
    
    <script>
        window.addEventListener('scroll', function() {
            const btn = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                btn.style.display = 'flex';
            } else {
                btn.style.display = 'none';
            }
        });
    </script>
</body>
</html>
