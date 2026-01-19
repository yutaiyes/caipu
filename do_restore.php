<?php
// 紧急恢复脚本 - 最小化版本
// 此文件不应被压缩，因为它是恢复工具

error_reporting(E_ALL);
ini_set('display_errors', 1);

$backup_dir = 'backups';
$message = '';
$success = false;

// 处理恢复
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup'])) {
    $backup_file = $_POST['backup'];
    
    if (file_exists($backup_file)) {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($backup_file) === TRUE) {
                $zip->extractTo('./');
                $zip->close();
                $success = true;
                $message = '恢复成功！请刷新页面或访问首页。';
            } else {
                $message = '无法打开备份文件';
            }
        } else {
            $message = 'ZipArchive扩展未安装';
        }
    } else {
        $message = '备份文件不存在';
    }
}

// 获取备份列表
$backups = [];
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '/backup_*.zip');
    rsort($files);
    foreach ($files as $file) {
        $backups[] = [
            'path' => $file,
            'name' => basename($file),
            'size' => filesize($file),
            'time' => filemtime($file)
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>紧急恢复</title>
<style>
body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5}
.box{background:#fff;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#d9534f;border-bottom:3px solid #d9534f;padding-bottom:10px}
.success{background:#dff0d8;border:1px solid #d6e9c6;color:#3c763d;padding:15px;margin:20px 0;border-radius:5px}
.error{background:#f2dede;border:1px solid #ebccd1;color:#a94442;padding:15px;margin:20px 0;border-radius:5px}
.info{background:#d9edf7;border:1px solid #bce8f1;color:#31708f;padding:15px;margin:20px 0;border-radius:5px}
.backup{background:#f9f9f9;padding:15px;margin:10px 0;border-left:4px solid #5cb85c}
button{background:#5cb85c;color:#fff;border:none;padding:10px 20px;border-radius:5px;cursor:pointer;font-size:16px}
button:hover{background:#4cae4c}
a{color:#337ab7;text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="box">
<h1>🚨 紧急恢复</h1>

<?php if ($message): ?>
    <div class="<?php echo $success ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
        <?php if ($success): ?>
            <br><br>
            <a href="index.php">访问前端</a> | 
            <a href="admin/login.php">访问后台</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (empty($backups)): ?>
    <div class="error">
        <strong>未找到备份文件</strong><br>
        请查看 <a href="restore.html">详细恢复指南</a>
    </div>
<?php else: ?>
    <div class="info">
        找到 <?php echo count($backups); ?> 个备份文件
    </div>
    
    <h2>选择备份恢复：</h2>
    
    <?php foreach ($backups as $backup): ?>
    <div class="backup">
        <strong><?php echo htmlspecialchars($backup['name']); ?></strong><br>
        大小: <?php echo number_format($backup['size'] / 1024, 2); ?> KB | 
        时间: <?php echo date('Y-m-d H:i:s', $backup['time']); ?><br>
        <form method="post" style="margin-top:10px" onsubmit="return confirm('确定恢复此备份？')">
            <input type="hidden" name="backup" value="<?php echo htmlspecialchars($backup['path']); ?>">
            <button type="submit">恢复此备份</button>
        </form>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="info" style="margin-top:30px">
    <strong>其他恢复方法：</strong><br>
    <a href="restore.html">查看详细恢复指南</a>
</div>

</div>
</body>
</html>
