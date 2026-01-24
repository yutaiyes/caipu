<?php
session_start();
require_once '../config.php';

// 检查登录状态
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['success' => 0, 'message' => '未登录']);
    exit;
}

// 处理删除请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $filename = $_POST['filename'] ?? '';
        if (empty($filename)) {
            throw new Exception('文件名不能为空');
        }
        
        // 安全检查：防止路径遍历
        $filename = basename($filename);
        $filepath = '../uploads/images/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new Exception('文件不存在');
        }
        
        if (!unlink($filepath)) {
            throw new Exception('删除失败');
        }
        
        echo json_encode([
            'success' => 1,
            'message' => '删除成功'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 处理重命名请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rename') {
    try {
        $oldname = $_POST['oldname'] ?? '';
        $newname = $_POST['newname'] ?? '';
        
        if (empty($oldname) || empty($newname)) {
            throw new Exception('文件名不能为空');
        }
        
        // 安全检查
        $oldname = basename($oldname);
        $newname = basename($newname);
        
        // 检查新文件名格式
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $newname)) {
            throw new Exception('文件名只能包含字母、数字、下划线、连字符和点');
        }
        
        $oldpath = '../uploads/images/' . $oldname;
        $newpath = '../uploads/images/' . $newname;
        
        if (!file_exists($oldpath)) {
            throw new Exception('原文件不存在');
        }
        
        if (file_exists($newpath)) {
            throw new Exception('目标文件名已存在');
        }
        
        if (!rename($oldpath, $newpath)) {
            throw new Exception('重命名失败');
        }
        
        echo json_encode([
            'success' => 1,
            'message' => '重命名成功',
            'newname' => $newname,
            'newurl' => '/uploads/images/' . $newname
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 处理文件上传
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    try {
        $file = $_FILES['file'];
        
        // 检查上传错误
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('文件上传失败，错误代码: ' . $file['error']);
        }
        
        // 检查文件大小（最大5MB）
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            throw new Exception('文件大小超过限制（最大5MB）');
        }
        
        // 检查文件类型
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception('不支持的文件类型，仅支持: JPG, PNG, GIF, WebP');
        }
        
        // 获取文件扩展名
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed_exts)) {
            throw new Exception('不支持的文件扩展名');
        }
        
        // 创建上传目录
        $upload_dir = '../uploads/images';
        if (!is_dir($upload_dir)) {
            @mkdir($upload_dir, 0755, true);
        }
        
        // 生成唯一文件名
        $filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $filepath = $upload_dir . '/' . $filename;
        
        // 移动文件
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('文件保存失败');
        }
        
        // 返回成功响应
        echo json_encode([
            'success' => 1,
            'url' => '/uploads/images/' . $filename,
            'message' => '上传成功'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => 0,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// 如果不是POST请求，显示上传页面
define('ADMIN_ACCESS', true);
require_once 'security.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$db = new PDO('sqlite:' . DB_PATH);

// 获取已上传的图片列表
$upload_dir = '../uploads/images';
$images = [];
if (is_dir($upload_dir)) {
    $files = glob($upload_dir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    rsort($files); // 最新的在前
    foreach ($files as $file) {
        $images[] = [
            'name' => basename($file),
            'url' => '/uploads/images/' . basename($file),
            'size' => filesize($file),
            'time' => filemtime($file)
        ];
    }
}

require 'layout_header.php';
?>

<div class="page-header">
    <h3 class="mb-0"><i class="fas fa-upload"></i> 文件上传管理</h3>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>上传说明：</strong>支持 WebP、JPG、PNG、GIF 格式图片（推荐使用WebP格式以获得更好压缩效果），单个文件最大 5MB。
</div>

<!-- 上传区域 -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-cloud-upload-alt"></i> 上传图片</h5>
    </div>
    <div class="card-body">
        <form id="upload-form" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file-input" class="form-label">选择图片文件</label>
                <input type="file" class="form-control" id="file-input" name="file" accept="image/webp,image/jpeg,image/png,image/gif" required>
                <div class="form-text">支持格式：WebP, JPG, PNG, GIF（优先WebP） | 最大大小：5MB</div>
            </div>
            
            <div id="upload-message" style="display: none;"></div>
            
            <div class="mb-3" id="preview-container" style="display: none;">
                <label class="form-label">预览</label>
                <div>
                    <img id="preview-image" src="" alt="预览" style="max-width: 300px; max-height: 300px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="upload-btn">
                <i class="fas fa-upload"></i> 开始上传
            </button>
            
            <div class="progress mt-3" id="upload-progress" style="display: none;">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>
        </form>
    </div>
</div>

<!-- 已上传的图片 -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-images"></i> 已上传的图片 (<?= count($images) ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($images)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 暂无上传的图片
        </div>
        <?php else: ?>
        <div class="row g-3 image-grid">
            <?php foreach ($images as $img): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                <div class="card h-100 image-card">
                    <img src="<?= htmlspecialchars($img['url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($img['name']) ?>" style="height: 200px; object-fit: cover; cursor: pointer;" onclick="window.open('<?= htmlspecialchars($img['url']) ?>', '_blank')">
                    <div class="card-body">
                        <h6 class="card-title text-truncate" title="<?= htmlspecialchars($img['name']) ?>">
                            <?= htmlspecialchars($img['name']) ?>
                        </h6>
                        <p class="card-text small text-muted mb-2">
                            <i class="fas fa-weight"></i> <?= number_format($img['size'] / 1024, 2) ?> KB<br>
                            <i class="fas fa-clock"></i> <?= date('Y-m-d H:i', $img['time']) ?>
                        </p>
                        <div class="image-actions">
                            <button class="btn btn-sm btn-outline-primary copy-url-btn" data-url="<?= htmlspecialchars($img['url']) ?>">
                                <i class="fas fa-copy"></i> <span class="d-none d-sm-inline">复制URL</span>
                            </button>
                            <div class="btn-group btn-group-sm w-100">
                                <a href="<?= htmlspecialchars($img['url']) ?>" target="_blank" class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">查看</span>
                                </a>
                                <button class="btn btn-outline-warning rename-btn" data-filename="<?= htmlspecialchars($img['name']) ?>">
                                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">重命名</span>
                                </button>
                                <button class="btn btn-outline-danger delete-btn" data-filename="<?= htmlspecialchars($img['name']) ?>">
                                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">删除</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// 文件预览
document.getElementById('file-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-image').src = e.target.result;
            document.getElementById('preview-container').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('preview-container').style.display = 'none';
    }
});

// 上传表单提交
document.getElementById('upload-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('file-input');
    const file = fileInput.files[0];
    
    if (!file) {
        showMessage('请选择要上传的文件', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    
    const btn = document.getElementById('upload-btn');
    const progress = document.getElementById('upload-progress');
    const progressBar = progress.querySelector('.progress-bar');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 上传中...';
    progress.style.display = 'block';
    progressBar.style.width = '0%';
    
    const xhr = new XMLHttpRequest();
    
    // 上传进度
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percent = (e.loaded / e.total) * 100;
            progressBar.style.width = percent + '%';
        }
    });
    
    // 上传完成
    xhr.addEventListener('load', function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> 开始上传';
        progress.style.display = 'none';
        
        try {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                showMessage('上传成功！URL: ' + response.url, 'success');
                // 3秒后刷新页面
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                showMessage('上传失败：' + response.message, 'danger');
            }
        } catch (e) {
            showMessage('上传失败：服务器响应错误', 'danger');
        }
    });
    
    // 上传错误
    xhr.addEventListener('error', function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload"></i> 开始上传';
        progress.style.display = 'none';
        showMessage('上传失败：网络错误', 'danger');
    });
    
    xhr.open('POST', 'upload.php', true);
    xhr.send(formData);
});

// 显示消息
function showMessage(message, type) {
    const messageEl = document.getElementById('upload-message');
    messageEl.className = `alert alert-${type} alert-dismissible fade show`;
    messageEl.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    messageEl.style.display = 'block';
    
    // 5秒后自动隐藏
    setTimeout(() => {
        messageEl.style.display = 'none';
    }, 5000);
}

// 复制URL
document.querySelectorAll('.copy-url-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        const fullUrl = window.location.origin + url;
        
        navigator.clipboard.writeText(fullUrl).then(() => {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> 已复制';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success');
            
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-primary');
            }, 2000);
        }).catch(() => {
            alert('复制失败，请手动复制：' + fullUrl);
        });
    });
});

// 删除图片
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filename = this.dataset.filename;
        
        if (!confirm('确定要删除图片 "' + filename + '" 吗？\n\n此操作不可恢复！')) {
            return;
        }
        
        const card = this.closest('.col-lg-3');
        const originalBtn = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        fetch('upload.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete&filename=' + encodeURIComponent(filename)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 淡出动画后移除
                card.style.transition = 'opacity 0.3s';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    // 检查是否还有图片
                    const remaining = document.querySelectorAll('.col-lg-3').length;
                    if (remaining === 0) {
                        location.reload();
                    }
                }, 300);
                showMessage('删除成功', 'success');
            } else {
                this.disabled = false;
                this.innerHTML = originalBtn;
                showMessage('删除失败：' + data.message, 'danger');
            }
        })
        .catch(error => {
            this.disabled = false;
            this.innerHTML = originalBtn;
            showMessage('删除失败：网络错误', 'danger');
        });
    });
});

// 重命名图片
document.querySelectorAll('.rename-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const oldname = this.dataset.filename;
        const nameWithoutExt = oldname.substring(0, oldname.lastIndexOf('.'));
        const ext = oldname.substring(oldname.lastIndexOf('.'));
        
        const newname = prompt('请输入新的文件名（不含扩展名）：', nameWithoutExt);
        
        if (newname === null || newname.trim() === '') {
            return;
        }
        
        if (newname === nameWithoutExt) {
            alert('文件名未改变');
            return;
        }
        
        const fullNewname = newname.trim() + ext;
        
        const card = this.closest('.card');
        const originalBtn = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        fetch('upload.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=rename&oldname=' + encodeURIComponent(oldname) + '&newname=' + encodeURIComponent(fullNewname)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 更新卡片信息
                card.querySelector('.card-title').textContent = data.newname;
                card.querySelector('.card-title').title = data.newname;
                card.querySelector('img').src = data.newurl;
                card.querySelector('.copy-url-btn').dataset.url = data.newurl;
                card.querySelector('.rename-btn').dataset.filename = data.newname;
                card.querySelector('.delete-btn').dataset.filename = data.newname;
                
                this.disabled = false;
                this.innerHTML = originalBtn;
                showMessage('重命名成功', 'success');
            } else {
                this.disabled = false;
                this.innerHTML = originalBtn;
                showMessage('重命名失败：' + data.message, 'danger');
            }
        })
        .catch(error => {
            this.disabled = false;
            this.innerHTML = originalBtn;
            showMessage('重命名失败：网络错误', 'danger');
        });
    });
});
</script>

<?php require 'layout_footer.php'; ?>
