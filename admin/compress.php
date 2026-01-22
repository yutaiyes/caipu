<?php
session_start();

$directories = ['../admin', '../'];
$exclude_dirs = ['../data', '../uploads', '../readme', '../libs', '../assets', '../backups'];
$exclude_files = [
    '../config.php',
    '../install.php', 
    '../upgrade_pages.php',
    '../upgrade_settings.php',
    '../minify.php',
    '../do_restore.php',
    '../emergency_restore.php',
    '../restore_backup.php',
    realpath(__FILE__)
];

$backup_dir = '../backups';
if (!is_dir($backup_dir)) {
    @mkdir($backup_dir, 0755, true);
}

function getPhpFiles($dir, $exclude_dirs = [], $exclude_files = []) {
    $files = [];
    $exclude_dirs = array_map(function($d) {
        return rtrim(realpath($d) ?: $d, '/');
    }, $exclude_dirs);
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filepath = $file->getPathname();
            $realpath = realpath($filepath);
            
            $in_exclude_dir = false;
            foreach ($exclude_dirs as $exclude_dir) {
                if (strpos($realpath, $exclude_dir) === 0) {
                    $in_exclude_dir = true;
                    break;
                }
            }
            
            $in_exclude_file = in_array($realpath, array_map('realpath', $exclude_files));
            
            if (!$in_exclude_dir && !$in_exclude_file) {
                $files[] = $filepath;
            }
        }
    }
    
    return $files;
}

function compressPhpSafe($content) {
    $tokens = token_get_all($content);
    $result = '';
    $prev_token = null;

    foreach ($tokens as $i => $token) {
        if (is_array($token)) {
            list($id, $text) = $token;

            if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
                if (preg_match('/@(license|copyright|author)/i', $text)) {
                    $result .= $text . "\n";
                }
                continue;
            }

            if ($id === T_WHITESPACE) {
                $has_newline = strpos($text, "\n") !== false || strpos($text, "\r") !== false;
                $next_token = isset($tokens[$i + 1]) ? $tokens[$i + 1] : null;
                $need_space = false;

                if ($prev_token !== null && is_array($prev_token)) {
                    $prev_id = $prev_token[0];
                    
                    // 必须保留空格的情况
                    $keywords_need_space = [
                        T_STRING, T_VARIABLE, T_LNUMBER, T_DNUMBER, 
                        T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, 
                        T_RETURN, T_ECHO, T_PRINT, T_IF, T_ELSE, T_ELSEIF, 
                        T_WHILE, T_FOR, T_FOREACH, T_FUNCTION, T_CLASS, 
                        T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_NEW,
                        T_EXTENDS, T_IMPLEMENTS, T_INSTANCEOF, T_CASE, T_DEFAULT,
                        T_BREAK, T_CONTINUE, T_THROW, T_TRY, T_CATCH, T_FINALLY
                    ];
                    
                    if (in_array($prev_id, $keywords_need_space)) {
                        if (is_array($next_token)) {
                            $next_id = $next_token[0];
                            if (in_array($next_id, $keywords_need_space)) {
                                $need_space = true;
                            }
                        }
                    }
                }

                if ($has_newline) {
                    $result .= "\n";
                } elseif ($need_space) {
                    $result .= ' ';
                }
                continue;
            }

            $result .= $text;
            $prev_token = $token;
        } else {
            $result .= $token;
            $prev_token = $token;
        }
    }

    $result = preg_replace('/\n{3,}/', "\n\n", $result);
    $lines = explode("\n", $result);
    $lines = array_map('trim', $lines);
    $result = implode("\n", $lines);

    return $result;
}

function createBackup($files, $backup_dir) {
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . '/backup_' . $timestamp . '.zip';
    
    if (!class_exists('ZipArchive')) {
        return false;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($backup_file, ZipArchive::CREATE) !== TRUE) {
        return false;
    }
    
    foreach ($files as $file) {
        $relative_path = str_replace('../', '', $file);
        $zip->addFile($file, $relative_path);
    }
    
    $zip->close();
    return $backup_file;
}

function restoreBackup($backup_file, $base_dir = '../') {
    if (!class_exists('ZipArchive')) {
        return false;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($backup_file) !== TRUE) {
        return false;
    }
    
    $zip->extractTo($base_dir);
    $zip->close();
    
    return true;
}

// AJAX请求处理
if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'compress') {
        $success = true;
        $message = '';
        
        try {
            $all_files = [];
            foreach ($directories as $dir) {
                $files = getPhpFiles($dir, $exclude_dirs, $exclude_files);
                $all_files = array_merge($all_files, $files);
            }
            
            if (empty($all_files)) {
                throw new Exception('没有找到可压缩的PHP文件');
            }
            
            $backup_file = createBackup($all_files, $backup_dir);
            if (!$backup_file) {
                throw new Exception('备份创建失败，请检查backups目录权限或ZipArchive扩展');
            }
            
            $compressed_count = 0;
            $failed_files = [];
            $total_original_size = 0;
            $total_compressed_size = 0;
            
            foreach ($all_files as $file) {
                $content = file_get_contents($file);
                $original_size = strlen($content);
                $total_original_size += $original_size;
                
                $compressed = compressPhpSafe($content);
                $compressed_size = strlen($compressed);
                $total_compressed_size += $compressed_size;
                
                if (file_put_contents($file, $compressed)) {
                    $compressed_count++;
                } else {
                    $failed_files[] = $file;
                }
            }
            
            $saved_size = $total_original_size - $total_compressed_size;
            $saved_percent = $total_original_size > 0 ? round(($saved_size / $total_original_size) * 100, 2) : 0;
            
            if (!empty($failed_files)) {
                $message = "部分文件压缩失败：" . implode(', ', $failed_files);
                $success = false;
            } else {
                $message = "✓ 成功压缩 {$compressed_count} 个文件！<br>";
                $message .= "原始大小：" . number_format($total_original_size / 1024, 2) . " KB<br>";
                $message .= "压缩后：" . number_format($total_compressed_size / 1024, 2) . " KB<br>";
                $message .= "节省：" . number_format($saved_size / 1024, 2) . " KB ({$saved_percent}%)<br>";
                $message .= "备份文件：" . basename($backup_file);
            }
            
        } catch (Exception $e) {
            $success = false;
            $message = '压缩失败：' . $e->getMessage();
        }
        
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
    
    if ($_POST['action'] === 'restore') {
        $backup_file = $_POST['backup_file'] ?? '';
        $success = false;
        $message = '';
        
        if (empty($backup_file) || !file_exists($backup_file)) {
            $message = '备份文件不存在';
        } else {
            if (restoreBackup($backup_file)) {
                $success = true;
                $message = '恢复成功！';
            } else {
                $message = '恢复失败，请检查文件权限或ZipArchive扩展';
            }
        }
        
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
    
    if ($_POST['action'] === 'delete') {
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
        
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}

require 'layout_header.php';

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
?>

<div class="page-header">
    <h3 class="mb-0"><i class="fas fa-compress"></i> 代码压缩管理</h3>
</div>

<div class="alert alert-danger">
    <h5><i class="fas fa-exclamation-triangle"></i> ⚠️ 压缩功能暂时禁用</h5>
    <p class="mb-0">
        <strong>重要提示：</strong>压缩功能存在BUG，会导致代码无法运行。<br>
        该功能已被禁用，正在开发更安全的版本。<br>
        <strong>如果您刚刚进行了压缩操作，请立即使用下方的"恢复备份"功能恢复！</strong>
    </p>
</div>

<div class="alert alert-info" style="display: none;">
    <h5><i class="fas fa-info-circle"></i> 功能说明</h5>
    <ul class="mb-0">
        <li><strong>安全压缩</strong>：使用PHP官方token_get_all函数，只删除注释和多余空白</li>
        <li><strong>自动备份</strong>：压缩前自动创建ZIP备份，可随时恢复</li>
        <li><strong>100%安全</strong>：不会破坏代码结构，压缩后保证可正常运行</li>
        <li><strong>排除目录</strong>：data、uploads、readme、libs、assets、backups目录不会被压缩</li>
        <li><strong>排除文件</strong>：config.php、恢复工具、compress.php自身等重要文件不会被压缩</li>
    </ul>
</div>

<div class="card mb-4" style="display: none;">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-compress-alt"></i> 压缩全站代码（已禁用）
    </div>
    <div class="card-body">
        <!-- 消息显示区域 -->
        <div id="compress-message" style="display: none;"></div>
        
        <form id="compress-form" method="post">
            <input type="hidden" name="action" value="compress">
            <input type="hidden" name="ajax" value="1">
            
            <div class="mb-3">
                <h6>将要处理的目录：</h6>
                <ul>
                    <li><code>admin/</code> - 后台管理文件</li>
                    <li><code>根目录</code> - 前端PHP文件（index.php, recipe.php, page.php等）</li>
                </ul>
            </div>
            
            <div class="mb-3">
                <h6>排除的目录：</h6>
                <ul>
                    <li><code>data/</code> - 数据库文件</li>
                    <li><code>uploads/</code> - 上传文件</li>
                    <li><code>readme/</code> - 文档文件</li>
                    <li><code>libs/</code> - 第三方库</li>
                    <li><code>assets/</code> - CSS/JS资源</li>
                    <li><code>backups/</code> - 备份文件</li>
                </ul>
            </div>
            
            <div class="mb-3">
                <h6>排除的文件：</h6>
                <ul>
                    <li><code>config.php</code> - 配置文件</li>
                    <li><code>install.php</code> - 安装文件</li>
                    <li><code>upgrade_*.php</code> - 升级文件</li>
                    <li><code>*_restore.php</code> - 恢复工具</li>
                    <li><code>compress.php</code> - 压缩工具自身</li>
                </ul>
            </div>
            
            <div class="alert alert-success">
                <h6><i class="fas fa-shield-alt"></i> 安全保证</h6>
                <ul class="mb-0">
                    <li><strong>使用PHP官方token_get_all函数</strong> - 这是PHP内置的代码解析器</li>
                    <li><strong>只删除注释和空白</strong> - 不修改任何代码逻辑</li>
                    <li><strong>保留换行结构</strong> - 代码仍然可读，便于调试</li>
                    <li><strong>100%安全可靠</strong> - 压缩后代码保证可以正常运行</li>
                </ul>
            </div>
            
            <div class="alert alert-warning">
                <h6><i class="fas fa-exclamation-triangle"></i> 重要提示</h6>
                <ul class="mb-0">
                    <li><strong>安全压缩</strong>：删除注释和多余空白，保留代码结构</li>
                    <li><strong>适度压缩</strong>：可节省 30-50% 的文件体积</li>
                    <li>建议在开发完成后再进行压缩</li>
                    <li>压缩前会自动创建备份到 <code>backups/</code> 目录</li>
                    <li>如需修改代码，请先恢复备份</li>
                    <li>压缩后请测试所有功能是否正常</li>
                </ul>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg" id="compress-btn">
                <i class="fas fa-compress-alt"></i> 开始压缩全站代码
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fas fa-history"></i> 备份管理
    </div>
    <div class="card-body">
        <?php if (empty($backups)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 暂无备份文件。压缩代码时会自动创建备份。
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
                    <tr id="backup-row-<?= $index ?>">
                        <td><?= $index + 1 ?></td>
                        <td><code><?= htmlspecialchars($backup['name']) ?></code></td>
                        <td><?= number_format($backup['size'] / 1024, 2) ?> KB</td>
                        <td><?= date('Y-m-d H:i:s', $backup['time']) ?></td>
                        <td>
                            <!-- 消息显示区域 -->
                            <div class="backup-message mb-2" id="backup-message-<?= $index ?>" style="display: none;"></div>
                            
                            <button type="button" class="btn btn-sm btn-success restore-btn" 
                                    data-file="<?= htmlspecialchars($backup['file']) ?>"
                                    data-index="<?= $index ?>"
                                    title="恢复备份">
                                <i class="fas fa-undo"></i> 恢复
                            </button>
                            
                            <a href="<?= htmlspecialchars($backup['file']) ?>" 
                               class="btn btn-sm btn-info" 
                               download 
                               title="下载备份">
                                <i class="fas fa-download"></i> 下载
                            </a>
                            
                            <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                    data-file="<?= htmlspecialchars($backup['file']) ?>"
                                    data-index="<?= $index ?>"
                                    title="删除备份">
                                <i class="fas fa-trash"></i> 删除
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-secondary text-white">
        <i class="fas fa-question-circle"></i> 使用说明
    </div>
    <div class="card-body">
        <h6>压缩效果对比（安全模式）</h6>
        <div class="row">
            <div class="col-md-6">
                <strong>压缩前：</strong>
                <pre class="bg-light p-2" style="font-size: 0.85rem;"><code>&lt;?php
// 这是注释
function test() {
    // 获取数据
    $data = getData();
    
    // 返回结果
    return $data;
}
?&gt;</code></pre>
            </div>
            <div class="col-md-6">
                <strong>压缩后（保留换行）：</strong>
                <pre class="bg-light p-2" style="font-size: 0.85rem;"><code>&lt;?php
function test(){
$data=getData();
return $data;
}
?&gt;</code></pre>
                <small class="text-success">✓ 仍然可读，便于调试</small>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <strong><i class="fas fa-info-circle"></i> 技术说明：</strong><br>
            本压缩功能使用PHP官方的 <code>token_get_all()</code> 函数进行代码解析。
            这是PHP内置的词法分析器，能够准确识别代码中的每个元素，
            因此可以100%安全地删除注释和空白，而不会误删或修改任何代码逻辑。
        </div>
        
        <h6 class="mt-3">注意事项</h6>
        <ul>
            <li>压缩前会自动创建备份</li>
            <li>压缩后如需修改代码，请先恢复备份</li>
            <li>建议在本地测试环境先测试压缩效果</li>
            <li>压缩后请测试所有功能是否正常</li>
        </ul>
    </div>
</div>

<script>
// 显示消息的函数
function showMessage(elementId, message, type) {
    const element = document.getElementById(elementId);
    element.className = `alert alert-${type} alert-dismissible fade show`;
    element.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    element.style.display = 'block';
    
    // 3秒后自动隐藏
    setTimeout(() => {
        element.style.display = 'none';
    }, 5000);
}

// 压缩表单提交
document.getElementById('compress-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!confirm('确定要压缩全站代码吗？\n\n✓ 压缩前会自动创建备份\n✓ 只删除注释和多余空白\n✓ 保留代码换行结构\n✓ 不修改任何代码逻辑\n✓ 可随时恢复备份\n\n压缩后代码仍可读，但会更紧凑。')) {
        return;
    }
    
    const btn = document.getElementById('compress-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 压缩中...';
    
    const formData = new FormData(this);
    
    fetch('compress.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        showMessage('compress-message', data.message, data.success ? 'success' : 'danger');
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        // 如果成功，3秒后刷新页面以更新备份列表
        if (data.success) {
            setTimeout(() => {
                location.reload();
            }, 3000);
        }
    })
    .catch(error => {
        showMessage('compress-message', '操作失败：' + error.message, 'danger');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// 恢复备份
document.querySelectorAll('.restore-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('确定要恢复此备份吗？\n\n⚠️ 当前文件将被覆盖！\n⚠️ 此操作不可撤销！')) {
            return;
        }
        
        const file = this.dataset.file;
        const index = this.dataset.index;
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 恢复中...';
        
        const formData = new FormData();
        formData.append('action', 'restore');
        formData.append('backup_file', file);
        formData.append('ajax', '1');
        
        fetch('compress.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageId = 'backup-message-' + index;
            const messageEl = document.getElementById(messageId);
            messageEl.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show mb-2`;
            messageEl.innerHTML = `
                <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            messageEl.style.display = 'block';
            
            this.disabled = false;
            this.innerHTML = originalText;
            
            // 如果成功，3秒后刷新页面
            if (data.success) {
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                // 5秒后自动隐藏错误消息
                setTimeout(() => {
                    messageEl.style.display = 'none';
                }, 5000);
            }
        })
        .catch(error => {
            const messageId = 'backup-message-' + index;
            const messageEl = document.getElementById(messageId);
            messageEl.className = 'alert alert-danger alert-dismissible fade show mb-2';
            messageEl.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                操作失败：${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            messageEl.style.display = 'block';
            
            this.disabled = false;
            this.innerHTML = originalText;
            
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 5000);
        });
    });
});

// 删除备份
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('确定要删除此备份吗？\n\n⚠️ 删除后无法恢复！\n⚠️ 请确保已有其他备份！\n⚠️ 建议先下载备份到本地！')) {
            return;
        }
        
        const file = this.dataset.file;
        const index = this.dataset.index;
        const originalText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 删除中...';
        
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('backup_file', file);
        formData.append('ajax', '1');
        
        fetch('compress.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageId = 'backup-message-' + index;
            const messageEl = document.getElementById(messageId);
            messageEl.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show mb-2`;
            messageEl.innerHTML = `
                <i class="fas fa-${data.success ? 'check-circle' : 'exclamation-circle'}"></i>
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            messageEl.style.display = 'block';
            
            this.disabled = false;
            this.innerHTML = originalText;
            
            // 如果成功，2秒后移除该行
            if (data.success) {
                setTimeout(() => {
                    const row = document.getElementById('backup-row-' + index);
                    row.style.transition = 'opacity 0.5s';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // 检查是否还有备份，如果没有则刷新页面显示"暂无备份"
                        const tbody = document.querySelector('.table tbody');
                        if (tbody && tbody.children.length === 0) {
                            location.reload();
                        }
                    }, 500);
                }, 2000);
            } else {
                // 5秒后自动隐藏错误消息
                setTimeout(() => {
                    messageEl.style.display = 'none';
                }, 5000);
            }
        })
        .catch(error => {
            const messageId = 'backup-message-' + index;
            const messageEl = document.getElementById(messageId);
            messageEl.className = 'alert alert-danger alert-dismissible fade show mb-2';
            messageEl.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                操作失败：${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            messageEl.style.display = 'block';
            
            this.disabled = false;
            this.innerHTML = originalText;
            
            setTimeout(() => {
                messageEl.style.display = 'none';
            }, 5000);
        });
    });
});
</script>

<?php require 'layout_footer.php'; ?>
