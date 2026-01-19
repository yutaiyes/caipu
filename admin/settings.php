<?php
require 'layout_header.php';
$config_file = '../config.php';
$htaccess_file = '../.htaccess';
$current_admin_dir = defined('ADMIN_DIR') ? ADMIN_DIR : 'admin';
$rewrite_enabled = file_exists($htaccess_file);
if ($_POST) {
$success_messages = [];
$error_messages = [];
if (isset($_POST['admin_dir']) && $_POST['admin_dir'] != $current_admin_dir) {
$new_admin_dir = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['admin_dir']);
if (empty($new_admin_dir)) {
$error_messages[] = '管理目录名称不能为空，只能包含字母、数字、下划线和横线';
} elseif ($new_admin_dir == $current_admin_dir) {
} else {
if (file_exists('../' . $new_admin_dir)) {
$error_messages[] = '目录 "' . $new_admin_dir . '" 已存在，请使用其他名称';
} else {
if (rename('../' . $current_admin_dir, '../' . $new_admin_dir)) {
$config_content = file_get_contents($config_file);
$config_content = preg_replace(
"/define\('ADMIN_DIR',\s*'[^']*'\);/",
"define('ADMIN_DIR', '$new_admin_dir');",
$config_content
);
file_put_contents($config_file, $config_content);
$success_messages[] = '管理目录已成功重命名为: ' . $new_admin_dir;
$success_messages[] = '新的访问地址: ' . $_SERVER['HTTP_HOST'] . '/' . $new_admin_dir . '/';
$success_messages[] = '请使用新地址访问后台（3秒后自动跳转）';
echo "<script>setTimeout(function(){ location.href='../$new_admin_dir/settings.php'; }, 3000);</script>";
} else {
$error_messages[] = '目录重命名失败，请检查文件权限';
}
}
}
}
if (isset($_POST['enable_rewrite'])) {
$enable = $_POST['enable_rewrite'] == '1';
if ($enable) {
$htaccess_content = <<<EOT
# 商用菜谱库伪静态规则
# Recipe System URL Rewrite Rules
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
# 如果请求的是真实存在的文件或目录，直接访问
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# 菜谱详情页: /recipe/数字ID.html -> recipe.php?id=数字ID
RewriteRule ^recipe/([0-9]+)\.html$ recipe.php?id=$1 [L,QSA]
# 分类页面: /category/数字ID.html -> index.php?cat=数字ID
RewriteRule ^category/([0-9]+)\.html$ index.php?cat=$1 [L,QSA]
# 自定义页面: /page/标识.html -> page.php?slug=标识
RewriteRule ^page/([a-zA-Z0-9_-]+)\.html$ page.php?slug=$1 [L,QSA]
# 首页: /index.html -> index.php
RewriteRule ^index\.html$ index.php [L,QSA]
</IfModule>
# 安全设置
<FilesMatch "\.(db|bak|sql|log)$">
Order allow,deny
Deny from all
</FilesMatch>
# 防止目录浏览
Options -Indexes
# 字符编码
AddDefaultCharset UTF-8
EOT;
if (file_put_contents($htaccess_file, $htaccess_content)) {
$success_messages[] = '伪静态已启用，.htaccess文件已创建';
} else {
$error_messages[] = '无法创建.htaccess文件，请检查根目录写入权限';
}
} else {
if (file_exists($htaccess_file)) {
if (unlink($htaccess_file)) {
$success_messages[] = '伪静态已禁用，.htaccess文件已删除';
} else {
$error_messages[] = '无法删除.htaccess文件，请手动删除';
}
}
}
}
if (!empty($success_messages)) {
echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
foreach ($success_messages as $msg) {
echo '<div><i class="fas fa-check-circle"></i> ' . htmlspecialchars($msg) . '</div>';
}
echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}
if (!empty($error_messages)) {
echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
foreach ($error_messages as $msg) {
echo '<div><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($msg) . '</div>';
}
echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}
if (!empty($success_messages) && !isset($_POST['admin_dir'])) {
echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
}
}
$current_admin_dir = defined('ADMIN_DIR') ? ADMIN_DIR : 'admin';
$rewrite_enabled = file_exists($htaccess_file);
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-cog"></i> 系统设置</h3>
</div>
<!-- 安全设置 -->
<div class="card mb-4">
<div class="card-header bg-danger text-white">
<i class="fas fa-shield-alt"></i> 安全设置
</div>
<div class="card-body">
<form method="post" onsubmit="return confirm('确定要修改管理目录吗？修改后需要使用新地址访问后台！');">
<div class="row">
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">
<i class="fas fa-folder"></i> 管理目录名称
<span class="text-danger">*</span>
</label>
<input type="text" class="form-control" name="admin_dir"
value="<?= htmlspecialchars($current_admin_dir) ?>"
pattern="[a-zA-Z0-9_-]+"
placeholder="例如：my_admin_2026"
required>
<small class="text-muted">
只能包含字母、数字、下划线和横线。修改后立即生效。
</small>
</div>
</div>
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">当前访问地址</label>
<div class="alert alert-info mb-0">
<code><?= $_SERVER['HTTP_HOST'] ?>/<?= htmlspecialchars($current_admin_dir) ?>/</code>
</div>
</div>
</div>
</div>
<div class="alert alert-warning">
<h6><i class="fas fa-exclamation-triangle"></i> 重要提示</h6>
<ul class="mb-0">
<li>修改管理目录名称可以有效防止恶意扫描和暴力破解</li>
<li>修改后请立即使用新地址访问后台，旧地址将无法访问</li>
<li>建议使用复杂的目录名称，如：my_secret_admin_2026</li>
<li>请牢记新的目录名称，否则将无法访问后台</li>
</ul>
</div>
<button type="submit" class="btn btn-danger">
<i class="fas fa-save"></i> 保存并重命名目录
</button>
</form>
</div>
</div>
<!-- 伪静态设置 -->
<div class="card mb-4">
<div class="card-header bg-primary text-white">
<i class="fas fa-link"></i> 伪静态设置
</div>
<div class="card-body">
<form method="post">
<div class="mb-3">
<label class="form-label">
<i class="fas fa-toggle-on"></i> 启用伪静态
</label>
<select class="form-select" name="enable_rewrite">
<option value="0" <?= !$rewrite_enabled ? 'selected' : '' ?>>禁用</option>
<option value="1" <?= $rewrite_enabled ? 'selected' : '' ?>>启用</option>
</select>
<small class="text-muted">
启用后将创建.htaccess文件，需要服务器支持mod_rewrite模块
</small>
</div>
<div class="mb-3">
<label class="form-label">当前状态</label>
<div>
<?php if ($rewrite_enabled): ?>
<span class="badge bg-success">
<i class="fas fa-check-circle"></i> 已启用
</span>
<small class="text-muted ms-2">.htaccess文件已存在</small>
<?php else: ?>
<span class="badge bg-secondary">
<i class="fas fa-times-circle"></i> 未启用
</span>
<small class="text-muted ms-2">使用默认URL格式</small>
<?php endif; ?>
</div>
</div>
<button type="submit" class="btn btn-primary">
<i class="fas fa-save"></i> 保存设置
</button>
</form>
</div>
</div>
<!-- 伪静态规则说明 -->
<div class="card mb-4">
<div class="card-header bg-info text-white">
<i class="fas fa-info-circle"></i> 伪静态规则说明
</div>
<div class="card-body">
<h6>URL格式对照表</h6>
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
<th width="30%">页面类型</th>
<th width="35%">原始URL</th>
<th width="35%">伪静态URL</th>
</tr>
</thead>
<tbody>
<tr>
<td><i class="fas fa-home"></i> 首页</td>
<td><code>index.php</code></td>
<td><code>index.html</code></td>
</tr>
<tr>
<td><i class="fas fa-utensils"></i> 菜谱详情</td>
<td><code>recipe.php?id=1</code></td>
<td><code>recipe/1.html</code></td>
</tr>
<tr>
<td><i class="fas fa-tag"></i> 分类页面</td>
<td><code>index.php?cat=2</code></td>
<td><code>category/2.html</code></td>
</tr>
<tr>
<td><i class="fas fa-file-alt"></i> 自定义页面</td>
<td><code>page.php?slug=about</code></td>
<td><code>page/about.html</code></td>
</tr>
</tbody>
</table>
</div>
<h6 class="mt-4">服务器要求</h6>
<ul>
<li><strong>Apache服务器</strong>：需要启用 <code>mod_rewrite</code> 模块</li>
<li><strong>权限要求</strong>：网站根目录需要有写入权限（创建.htaccess文件）</li>
<li><strong>AllowOverride</strong>：需要设置为 <code>All</code> 或 <code>FileInfo</code></li>
</ul>
<h6 class="mt-4">检查mod_rewrite是否启用</h6>
<div class="alert alert-secondary">
<p class="mb-2"><strong>方法1：查看phpinfo()</strong></p>
<p class="mb-2">在 <a href="debug.php" target="_blank">程序调试</a> 页面查看Apache模块列表</p>
<p class="mb-2 mt-3"><strong>方法2：命令行检查（Linux）</strong></p>
<code>apache2ctl -M | grep rewrite</code>
<p class="small text-muted mb-0">如果显示 <code>rewrite_module</code> 则表示已启用</p>
</div>
<h6 class="mt-4">启用mod_rewrite（如果未启用）</h6>
<div class="alert alert-secondary">
<p class="mb-2"><strong>Ubuntu/Debian:</strong></p>
<code>sudo a2enmod rewrite</code><br>
<code>sudo systemctl restart apache2</code>
<p class="mb-2 mt-3"><strong>CentOS/RHEL:</strong></p>
<p class="small text-muted mb-0">编辑 <code>/etc/httpd/conf/httpd.conf</code>，取消注释 <code>LoadModule rewrite_module modules/mod_rewrite.so</code></p>
<code>sudo systemctl restart httpd</code>
</div>
<h6 class="mt-4">配置AllowOverride</h6>
<div class="alert alert-secondary">
<p class="mb-2">编辑Apache配置文件（通常是 <code>/etc/apache2/sites-available/000-default.conf</code>）</p>
<pre class="mb-0"><code>&lt;Directory /var/www/html&gt;
Options Indexes FollowSymLinks
AllowOverride All
Require all granted
&lt;/Directory&gt;</code></pre>
<p class="small text-muted mt-2 mb-0">修改后重启Apache: <code>sudo systemctl restart apache2</code></p>
</div>
<h6 class="mt-4">宝塔面板配置（推荐）</h6>
<div class="alert alert-success">
<p class="mb-2"><strong>宝塔面板用户请按以下步骤操作：</strong></p>
<ol class="mb-2">
<li>登录宝塔面板</li>
<li>进入 <strong>网站</strong> 管理</li>
<li>找到您的网站，点击 <strong>设置</strong></li>
<li>选择 <strong>伪静态</strong> 选项卡</li>
<li>在下拉菜单中选择 <strong>自定义</strong></li>
<li>粘贴以下规则并保存</li>
</ol>
<p class="mb-2"><strong>Apache规则（宝塔默认）：</strong></p>
<pre class="mb-3"><code>RewriteEngine On
RewriteBase /
# 如果请求的是真实存在的文件或目录，直接访问
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# 菜谱详情页
RewriteRule ^recipe/([0-9]+)\.html$ recipe.php?id=$1 [L,QSA]
# 分类页面
RewriteRule ^category/([0-9]+)\.html$ index.php?cat=$1 [L,QSA]
# 自定义页面
RewriteRule ^page/([a-zA-Z0-9_-]+)\.html$ page.php?slug=$1 [L,QSA]
# 首页
RewriteRule ^index\.html$ index.php [L,QSA]</code></pre>
<p class="mb-2"><strong>Nginx规则（如果使用Nginx）：</strong></p>
<pre class="mb-0"><code>location / {
try_files $uri $uri/ @rewrite;
}
location @rewrite {
rewrite ^/recipe/([0-9]+)\.html$ /recipe.php?id=$1 last;
rewrite ^/category/([0-9]+)\.html$ /index.php?cat=$1 last;
rewrite ^/page/([a-zA-Z0-9_-]+)\.html$ /page.php?slug=$1 last;
rewrite ^/index\.html$ /index.php last;
}</code></pre>
<p class="small text-muted mt-2 mb-0">
<i class="fas fa-lightbulb"></i> 提示：宝塔面板会自动处理配置，无需手动重启服务器
</p>
</div>
<h6 class="mt-4">测试伪静态是否生效</h6>
<ol>
<li>启用伪静态设置</li>
<li>访问前端首页，点击任意菜谱</li>
<li>查看浏览器地址栏，如果显示 <code>recipe/数字.html</code> 格式则表示成功</li>
<li>如果显示404错误，请检查服务器配置</li>
</ol>
<h6 class="mt-4">常见问题</h6>
<div class="accordion" id="faqAccordion">
<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
启用后显示404错误？
</button>
</h2>
<div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">
<p><strong>可能原因：</strong></p>
<ul>
<li>mod_rewrite模块未启用</li>
<li>AllowOverride设置不正确</li>
<li>.htaccess文件权限问题</li>
</ul>
<p><strong>解决方案：</strong></p>
<ol>
<li>检查并启用mod_rewrite模块</li>
<li>确保AllowOverride设置为All</li>
<li>检查.htaccess文件是否存在且可读</li>
<li>重启Apache服务器</li>
</ol>
</div>
</div>
</div>
<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
Nginx服务器如何配置？
</button>
</h2>
<div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">
<p>Nginx不支持.htaccess文件，需要在nginx配置文件中添加rewrite规则：</p>
<pre><code>location / {
try_files $uri $uri/ @rewrite;
}
location @rewrite {
rewrite ^/recipe/([0-9]+)\.html$ /recipe.php?id=$1 last;
rewrite ^/category/([0-9]+)\.html$ /index.php?cat=$1 last;
rewrite ^/page/([a-zA-Z0-9_-]+)\.html$ /page.php?slug=$1 last;
rewrite ^/index\.html$ /index.php last;
}</code></pre>
<p class="mb-0">修改后重启Nginx: <code>sudo systemctl restart nginx</code></p>
</div>
</div>
</div>
<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
是否必须启用伪静态？
</button>
</h2>
<div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
<div class="accordion-body">
<p><strong>不是必须的。</strong>系统在不启用伪静态的情况下也能正常运行。</p>
<p><strong>启用伪静态的好处：</strong></p>
<ul>
<li>URL更美观、更易记</li>
<li>对SEO更友好</li>
<li>隐藏技术实现细节</li>
</ul>
<p class="mb-0"><strong>不启用的情况：</strong>使用原始URL格式（如 recipe.php?id=1），功能完全正常。</p>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- 系统信息 -->
<div class="card">
<div class="card-header bg-secondary text-white">
<i class="fas fa-server"></i> 系统信息
</div>
<div class="card-body">
<div class="row">
<div class="col-md-6">
<table class="table table-sm">
<tr>
<td width="40%"><strong>PHP版本</strong></td>
<td><?= phpversion() ?></td>
</tr>
<tr>
<td><strong>服务器软件</strong></td>
<td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
</tr>
<tr>
<td><strong>当前管理目录</strong></td>
<td><code><?= htmlspecialchars($current_admin_dir) ?></code></td>
</tr>
</table>
</div>
<div class="col-md-6">
<table class="table table-sm">
<tr>
<td width="40%"><strong>伪静态状态</strong></td>
<td>
<?php if ($rewrite_enabled): ?>
<span class="badge bg-success">已启用</span>
<?php else: ?>
<span class="badge bg-secondary">未启用</span>
<?php endif; ?>
</td>
</tr>
<tr>
<td><strong>根目录可写</strong></td>
<td>
<?php if (is_writable('..')): ?>
<span class="badge bg-success">是</span>
<?php else: ?>
<span class="badge bg-danger">否</span>
<?php endif; ?>
</td>
</tr>
<tr>
<td><strong>配置文件</strong></td>
<td>
<?php if (file_exists($config_file)): ?>
<span class="badge bg-success">存在</span>
<?php else: ?>
<span class="badge bg-danger">不存在</span>
<?php endif; ?>
</td>
</tr>
</table>
</div>
</div>
</div>
</div>
<?php require 'layout_footer.php'; ?>

