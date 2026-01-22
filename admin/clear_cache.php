<?php
/**
 * 管理后台缓存清理工具
 * 统一的缓存管理界面，保持UI一致性
 */

require_once 'layout_header.php';

// 获取当前管理目录路径
$admin_path = defined('ADMIN_DIR') ? ADMIN_DIR : 'admin';

// 处理缓存清理
if ($_POST['action'] == 'clear_cache') {
    echo '<div class="alert alert-success">';
    echo '<h5><i class="fas fa-check-circle"></i> 清理完成</h5>';
    echo '<ul class="mb-0">';
    
    // 重新生成.htaccess文件（如果启用了伪静态）
    $htaccess_file = '../.htaccess';
    if (file_exists($htaccess_file)) {
        // 备份现有文件
        $backup_file = '../.htaccess.backup.' . date('YmdHis');
        copy($htaccess_file, $backup_file);
        echo '<li>✓ 已备份现有.htaccess文件</li>';
        
        // 重新生成伪静态规则
        $htaccess_content = <<<EOT
# 商用菜谱库伪静态规则 v2.0
# Recipe System URL Rewrite Rules
# 支持12位固定长度编码
# 生成时间: {date('Y-m-d H:i:s')}

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /

# 如果请求的是真实存在的文件或目录，直接访问
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# 菜谱详情页: 12位编码.html -> recipe.php?base=编码
# 注意：菜谱编码不以A开头，避免与页面冲突
RewriteRule ^([B-Z][A-Z0-9]{11})\.html$ recipe.php?base=$1 [L,QSA]

# 兼容旧格式: /recipe/数字ID.html -> recipe.php?id=数字ID
RewriteRule ^recipe/([0-9]+)\.html$ recipe.php?id=$1 [L,QSA]

# 分类页面: /category/数字ID.html -> index.php?cat=数字ID
RewriteRule ^category/([0-9]+)\.html$ index.php?cat=$1 [L,QSA]

# 自定义页面: 12位编码.html -> page.php?base=编码
# 注意：页面编码以A开头，与菜谱区分
RewriteRule ^([A][A-Z0-9]{11})\.html$ page.php?base=$1 [L,QSA]

# 兼容旧格式: /page/标识.html -> page.php?slug=标识
RewriteRule ^page/([a-zA-Z0-9_-]+)\.html$ page.php?slug=$1 [L,QSA]

# 首页: /index.html -> index.php
RewriteRule ^index\.html$ index.php [L,QSA]

</IfModule>

# 安全设置
<FilesMatch "\.(db|bak|sql|log|ini)$">
Order allow,deny
Deny from all
</FilesMatch>

# 防止目录浏览
Options -Indexes

# 设置默认字符编码
AddDefaultCharset UTF-8
EOT;
        
        if (file_put_contents($htaccess_file, $htaccess_content)) {
            echo '<li>✓ 已重新生成.htaccess伪静态规则</li>';
        } else {
            echo '<li>✗ 重新生成.htaccess文件失败</li>';
        }
    }
    
    // 清理opcache（如果启用）
    if (function_exists('opcache_reset')) {
        opcache_reset();
        echo '<li>✓ 已清理OPcache</li>';
    }
    
    // 清理APC缓存（如果启用）
    if (function_exists('apc_clear_cache')) {
        apc_clear_cache();
        echo '<li>✓ 已清理APC缓存</li>';
    }
    
    echo '</ul>';
    echo '</div>';
    
    // 设置缓存清理标记
    setcookie('cache_cleared', time(), time() + 3600, '/');
    
}
?>

<div class="page-header">
    <h3 class="mb-0"><i class="fas fa-broom"></i> 缓存管理</h3>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-tools"></i> 系统缓存清理
        </h5>
    </div>
    <div class="card-body">
        <?php
        if ($_POST['action'] != 'clear_cache') {
        ?>
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> 缓存清理说明</h5>
            <ul class="mb-0">
                <li><strong>伪静态规则更新：</strong>后台修改伪静态设置后，需要重新生成.htaccess文件</li>
                <li><strong>服务器缓存：</strong>PHP OPcache、APC等缓存可能缓存了旧的代码</li>
                <li><strong>浏览器缓存：</strong>浏览器可能缓存了旧的页面或CSS/JS文件</li>
                <li><strong>CDN缓存：</strong>如果使用了CDN，需要清理CDN缓存</li>
            </ul>
        </div>

        <form method="post" onsubmit="return confirm('确定要清理所有缓存吗？这将重新生成伪静态规则。');">
            <input type="hidden" name="action" value="clear_cache">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-broom"></i> 一键清理所有缓存
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> 返回首页
                </a>
            </div>
        </form>
        <?php
        }
        ?>
    </div>
</div>

<!-- 调试指南 -->
<div class="card mt-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="fas fa-tools"></i> 伪静态调试指南
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-check-circle text-success"></i> 正常访问标志</h6>
                <ul class="small">
                    <li>菜谱：<code>540000000001.html</code></li>
                    <li>页面：<code>A10000000001.html</code></li>
                    <li>分类：<code>category/1.html</code></li>
                    <li>页面能正常显示，无404错误</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-exclamation-triangle text-danger"></i> 常见问题</h6>
                <ul class="small">
                    <li>404错误：伪静态规则未生效</li>
                    <li>显示PHP源码：Apache未正确处理</li>
                    <li>旧格式URL：缓存未清理</li>
                    <li>Internal Server Error：.htaccess语法错误</li>
                </ul>
            </div>
        </div>

        <h6 class="mt-3"><i class="fas fa-step-forward"></i> 调试步骤</h6>
        <ol class="small">
            <li><strong>清理缓存：</strong>点击上方"一键清理所有缓存"按钮</li>
            <li><strong>检查.htaccess：</strong>确认根目录存在.htaccess文件</li>
            <li><strong>测试菜谱：</strong>访问任意菜谱，检查URL格式</li>
            <li><strong>测试页面：</strong>访问任意页面，检查URL格式</li>
            <li><strong>查看源码：</strong>F12查看Network请求，确认重定向正确</li>
            <li><strong>检查日志：</strong>查看Apache错误日志</li>
        </ol>

        <div class="alert alert-warning mt-3">
            <h6><i class="fas fa-lightbulb"></i> 温馨提示</h6>
            <ul class="mb-0 small">
                <li><strong>服务器要求：</strong>需要Apache + mod_rewrite模块</li>
                <li><strong>权限设置：</strong>网站目录需要AllowOverride All</li>
                <li><strong>缓存问题：</strong>修改设置后务必清理缓存</li>
                <li><strong>浏览器强制刷新：</strong>Ctrl+F5 或 Cmd+Shift+R</li>
                <li><strong>移动端测试：</strong>手机浏览器可能缓存更严重</li>
                <li><strong>CDN用户：</strong>需要额外清理CDN缓存</li>
            </ul>
        </div>
    </div>
</div>

<style>
.d-grid {
    display: grid;
    gap: 0.5rem;
}
.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>

<?php require_once 'layout_footer.php'; ?>
