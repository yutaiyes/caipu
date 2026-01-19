<?php
// 紧急恢复脚本 - 不应被压缩
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>紧急恢复 - 商用菜谱库</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d9534f;
            border-bottom: 3px solid #d9534f;
            padding-bottom: 10px;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-danger {
            background: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
        .alert-success {
            background: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }
        .alert-info {
            background: #d9edf7;
            border: 1px solid #bce8f1;
            color: #31708f;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
        }
        .btn-primary {
            background: #5cb85c;
            color: white;
        }
        .btn-danger {
            background: #d9534f;
            color: white;
        }
        .btn:hover {
            opacity: 0.8;
        }
        .backup-list {
            list-style: none;
            padding: 0;
        }
        .backup-item {
            padding: 10px;
            margin: 5px 0;
            background: #f9f9f9;
            border-left: 4px solid #5cb85c;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        ol, ul {
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 紧急恢复系统</h1>
        
        <div class="alert alert-danger">
            <strong>检测到代码压缩问题！</strong><br>
            系统检测到代码已被压缩但出现错误，导致网站无法访问。
        </div>

        <?php
        $backup_dir = 'backups';
        $backups = [];
        
        if (is_dir($backup_dir)) {
            $backup_files = glob($backup_dir . '/backup_*.zip');
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
        
        // 处理恢复请求
        if (isset($_POST['restore']) && isset($_POST['backup_file'])) {
            $backup_file = $_POST['backup_file'];
            
            if (file_exists($backup_file) && class_exists('ZipArchive')) {
                $zip = new ZipArchive();
                if ($zip->open($backup_file) === TRUE) {
                    $zip->extractTo('./');
                    $zip->close();
                    
                    echo '<div class="alert alert-success">';
                    echo '<strong>✓ 恢复成功！</strong><br>';
                    echo '所有文件已从备份恢复。<br>';
                    echo '<a href="index.php" class="btn btn-primary">访问前端</a>';
                    echo '<a href="admin/login.php" class="btn btn-primary">访问后台</a>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-danger">恢复失败：无法打开备份文件</div>';
                }
            } else {
                echo '<div class="alert alert-danger">恢复失败：备份文件不存在或ZipArchive扩展未安装</div>';
            }
        }
        ?>

        <h2>📋 问题说明</h2>
        <div class="alert alert-info">
            <p><strong>原因：</strong>代码压缩功能的正则表达式规则有误，导致字符串内容被错误替换。</p>
            <p><strong>影响：</strong>前端和后台都无法正常访问。</p>
            <p><strong>解决方案：</strong>从备份恢复文件。</p>
        </div>

        <h2>🔧 恢复步骤</h2>
        
        <?php if (empty($backups)): ?>
        <div class="alert alert-danger">
            <strong>未找到备份文件！</strong><br>
            <p>在 <code>backups/</code> 目录中没有找到备份文件。</p>
            <p><strong>手动恢复方法：</strong></p>
            <ol>
                <li>如果您有其他备份（如FTP备份、服务器快照），请使用那些备份恢复</li>
                <li>如果使用Git版本控制，可以回退到之前的版本</li>
                <li>联系服务器管理员查看是否有自动备份</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="alert alert-success">
            <strong>找到 <?= count($backups) ?> 个备份文件</strong><br>
            请选择一个备份进行恢复（建议选择最新的备份）
        </div>

        <h3>可用备份列表：</h3>
        <ul class="backup-list">
            <?php foreach ($backups as $index => $backup): ?>
            <li class="backup-item">
                <strong><?= $index + 1 ?>. <?= htmlspecialchars($backup['name']) ?></strong><br>
                <small>
                    大小: <?= number_format($backup['size'] / 1024, 2) ?> KB | 
                    创建时间: <?= date('Y-m-d H:i:s', $backup['time']) ?>
                </small><br>
                <form method="post" style="margin-top: 10px;" onsubmit="return confirm('确定要恢复此备份吗？当前文件将被覆盖！');">
                    <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['file']) ?>">
                    <button type="submit" name="restore" class="btn btn-primary">
                        ✓ 恢复此备份
                    </button>
                    <a href="<?= htmlspecialchars($backup['file']) ?>" class="btn" download style="background: #5bc0de; color: white;">
                        ⬇ 下载备份
                    </a>
                </form>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <h2>📝 预防措施</h2>
        <div class="alert alert-info">
            <p><strong>为避免再次出现此问题，请：</strong></p>
            <ol>
                <li>恢复后，<strong>不要再使用代码压缩功能</strong></li>
                <li>如需压缩，请先在测试环境测试</li>
                <li>定期手动备份重要文件</li>
                <li>使用Git等版本控制系统</li>
                <li>考虑使用专业的代码压缩工具（如PHP Minifier）</li>
            </ol>
        </div>

        <h2>🆘 其他恢复方法</h2>
        <div class="alert alert-info">
            <h4>方法1：FTP恢复</h4>
            <ol>
                <li>通过FTP连接到服务器</li>
                <li>下载 <code>backups/</code> 目录中的备份文件</li>
                <li>解压备份文件</li>
                <li>上传解压后的文件覆盖现有文件</li>
            </ol>

            <h4>方法2：服务器快照恢复</h4>
            <ol>
                <li>登录服务器管理面板（如宝塔、cPanel）</li>
                <li>查找网站快照或备份功能</li>
                <li>选择压缩前的快照进行恢复</li>
            </ol>

            <h4>方法3：Git回退（如果使用Git）</h4>
            <pre><code>git log                    # 查看提交历史
git reset --hard [commit]  # 回退到指定提交
git push -f                # 强制推送（谨慎使用）</code></pre>
        </div>

        <h2>📞 需要帮助？</h2>
        <div class="alert alert-info">
            <p>如果以上方法都无法解决问题，请：</p>
            <ul>
                <li>检查服务器错误日志</li>
                <li>联系服务器管理员</li>
                <li>查看PHP错误信息</li>
            </ul>
        </div>
    </div>
</body>
</html>
