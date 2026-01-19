<?php
session_start();
date_default_timezone_set('Asia/Shanghai');
$db_path = '../data/data.db';
if (isset($_POST['action']) && $_POST['action'] === 'save') {
$success = true;
$message = '';
try {
$db = new PDO('sqlite:' . $db_path);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$settings = [
'site_title' => $_POST['site_title'] ?? '',
'site_subtitle' => $_POST['site_subtitle'] ?? '',
'site_slogan' => $_POST['site_slogan'] ?? '',
'site_description' => $_POST['site_description'] ?? '',
'site_keywords' => $_POST['site_keywords'] ?? '',
'site_author' => $_POST['site_author'] ?? '',
'geo_region' => $_POST['geo_region'] ?? '',
'geo_placename' => $_POST['geo_placename'] ?? '',
'geo_position' => $_POST['geo_position'] ?? '',
'enable_readme_browse' => isset($_POST['enable_readme_browse']) ? '1' : '0',
];
$stmt = $db->prepare("UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE key = ?");
foreach ($settings as $key => $value) {
$stmt->execute([$value, $key]);
}
$message = '✓ 网站设置保存成功！';
} catch (Exception $e) {
$success = false;
$message = '保存失败：' . $e->getMessage();
}
$show_message = true;
$message_type = $success ? 'success' : 'danger';
$message_text = $message;
}
require 'layout_header.php';
$db = new PDO('sqlite:' . $db_path);
$settings = [];
$table_exists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
if (!$table_exists) {
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-cog"></i> 网站设置</h3>
</div>
<div class="alert alert-warning">
<h5><i class="fas fa-exclamation-triangle"></i> 数据库表未初始化</h5>
<p>settings表尚未创建，请先运行数据库升级脚本。</p>
<p><a href="../upgrade_settings.php" class="btn btn-primary" target="_blank">
<i class="fas fa-database"></i> 点击运行升级脚本
</a></p>
<p class="mb-0"><small>或在命令行运行：<code>php upgrade_settings.php</code></small></p>
</div>
<?php
require 'layout_footer.php';
exit;
}
$result = $db->query("SELECT key, value FROM settings");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
$settings[$row['key']] = $row['value'];
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-cog"></i> 网站设置</h3>
</div>
<!-- 功能说明 -->
<div class="alert alert-info">
<h5><i class="fas fa-info-circle"></i> 功能说明</h5>
<ul class="mb-0">
<li><strong>网站标题</strong>：显示在浏览器标签页和搜索结果中</li>
<li><strong>网站副标题</strong>：显示在网站标题下方，简短说明</li>
<li><strong>网站口号</strong>：品牌宣传语，吸引用户</li>
<li><strong>网站描述</strong>：显示在搜索结果摘要中，建议150-160字符</li>
<li><strong>网站关键词</strong>：帮助搜索引擎理解网站内容，用逗号分隔</li>
<li><strong>地理信息</strong>：用于本地SEO优化，帮助搜索引擎了解网站的地理位置</li>
</ul>
</div>
<!-- 网站设置表单 -->
<div class="card mb-4">
<div class="card-header bg-primary text-white">
<i class="fas fa-globe"></i> 基本设置
</div>
<div class="card-body">
<form method="post">
<input type="hidden" name="action" value="save">
<div class="mb-3">
<label class="form-label"><i class="fas fa-heading"></i> 网站标题</label>
<input type="text" name="site_title" class="form-control"
value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>"
placeholder="例如：商用菜谱库" required>
<small class="form-text text-muted">显示在浏览器标签页和搜索结果中</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-heading"></i> 网站副标题</label>
<input type="text" name="site_subtitle" class="form-control"
value="<?= htmlspecialchars($settings['site_subtitle'] ?? '') ?>"
placeholder="例如：专业的商用菜谱管理系统">
<small class="form-text text-muted">显示在网站标题下方，简短说明网站定位</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-quote-left"></i> 网站口号 (Slogan)</label>
<input type="text" name="site_slogan" class="form-control"
value="<?= htmlspecialchars($settings['site_slogan'] ?? '') ?>"
placeholder="例如：让美食触手可及">
<small class="form-text text-muted">品牌宣传语，吸引用户，可用于首页展示</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-align-left"></i> 网站描述 (Meta Description)</label>
<textarea name="site_description" class="form-control" rows="3"
placeholder="例如：专业的商用菜谱管理系统，提供海量菜谱资源" required><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
<small class="form-text text-muted">建议150-160字符，显示在搜索结果摘要中，影响点击率</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-tags"></i> 网站关键词 (Meta Keywords)</label>
<input type="text" name="site_keywords" class="form-control"
value="<?= htmlspecialchars($settings['site_keywords'] ?? '') ?>"
placeholder="例如：菜谱,美食,烹饪,食谱,商用菜谱">
<small class="form-text text-muted">用逗号分隔，建议5-10个关键词，帮助搜索引擎理解网站内容</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-user"></i> 网站作者 (Meta Author)</label>
<input type="text" name="site_author" class="form-control"
value="<?= htmlspecialchars($settings['site_author'] ?? '') ?>"
placeholder="例如：商用菜谱库">
<small class="form-text text-muted">网站所有者或管理者名称</small>
</div>
<hr class="my-4">
<h5 class="mb-3"><i class="fas fa-book"></i> 文档中心设置</h5>
<div class="mb-3">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="enable_readme_browse"
id="enable_readme_browse" <?= ($settings['enable_readme_browse'] ?? '0') == '1' ? 'checked' : '' ?>>
<label class="form-check-label" for="enable_readme_browse">
<i class="fas fa-folder-open"></i> 启用 readme 目录浏览
</label>
</div>
<small class="form-text text-muted">
开启后，访问 /readme/ 将显示文档列表页面；关闭后（默认），将重定向到后台文档中心
</small>
</div>
<hr class="my-4">
<h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> 地理信息 (GEO SEO)</h5>
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">地理区域代码 (geo.region)</label>
<input type="text" name="geo_region" class="form-control"
value="<?= htmlspecialchars($settings['geo_region'] ?? '') ?>"
placeholder="例如：CN (中国)">
<small class="form-text text-muted">ISO 3166-1 国家代码，如：CN, US, JP</small>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">地理位置名称 (geo.placename)</label>
<input type="text" name="geo_placename" class="form-control"
value="<?= htmlspecialchars($settings['geo_placename'] ?? '') ?>"
placeholder="例如：中国,北京">
<small class="form-text text-muted">城市或地区名称</small>
</div>
</div>
<div class="mb-3">
<label class="form-label">地理坐标 (geo.position)</label>
<input type="text" name="geo_position" class="form-control"
value="<?= htmlspecialchars($settings['geo_position'] ?? '') ?>"
placeholder="例如：39.9042;116.4074 (纬度;经度)">
<small class="form-text text-muted">格式：纬度;经度，可选填。例如北京：39.9042;116.4074</small>
</div>
<div class="alert alert-secondary">
<h6><i class="fas fa-lightbulb"></i> 地理信息说明</h6>
<ul class="mb-0">
<li><strong>geo.region</strong>：国家/地区代码，帮助搜索引擎了解网站的目标地区</li>
<li><strong>geo.placename</strong>：具体城市或地区名称，用于本地搜索优化</li>
<li><strong>geo.position</strong>：精确的地理坐标，可选填，用于地图服务</li>
<li>这些信息会添加到网页的meta标签中，提升本地SEO效果</li>
</ul>
</div>
<button type="submit" class="btn btn-primary btn-lg">
<i class="fas fa-save"></i> 保存设置
</button>
<!-- 消息显示区域 -->
<?php if (isset($show_message) && $show_message): ?>
<div class="alert alert-<?= $message_type ?> alert-dismissible fade show mt-3">
<i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
<?= $message_text ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
</form>
</div>
</div>
<!-- 使用说明 -->
<div class="card">
<div class="card-header bg-secondary text-white">
<i class="fas fa-question-circle"></i> SEO优化建议
</div>
<div class="card-body">
<h6>网站标题优化</h6>
<ul>
<li>长度控制在50-60字符以内</li>
<li>包含主要关键词</li>
<li>简洁明了，吸引点击</li>
</ul>
<h6 class="mt-3">网站描述优化</h6>
<ul>
<li>长度控制在150-160字符</li>
<li>包含2-3个关键词</li>
<li>描述网站核心价值</li>
<li>吸引用户点击</li>
</ul>
<h6 class="mt-3">关键词选择</h6>
<ul>
<li>选择与网站内容相关的关键词</li>
<li>包含核心关键词和长尾关键词</li>
<li>避免关键词堆砌</li>
<li>定期根据数据调整</li>
</ul>
<h6 class="mt-3">地理信息优化</h6>
<ul>
<li>如果是本地业务，务必填写地理信息</li>
<li>有助于在本地搜索中获得更好排名</li>
<li>提升Google Maps等地图服务的可见性</li>
</ul>
<h6 class="mt-3">常用国家代码</h6>
<div class="row">
<div class="col-md-6">
<ul>
<li><strong>CN</strong> - 中国</li>
<li><strong>US</strong> - 美国</li>
<li><strong>JP</strong> - 日本</li>
<li><strong>KR</strong> - 韩国</li>
</ul>
</div>
<div class="col-md-6">
<ul>
<li><strong>GB</strong> - 英国</li>
<li><strong>FR</strong> - 法国</li>
<li><strong>DE</strong> - 德国</li>
<li><strong>SG</strong> - 新加坡</li>
</ul>
</div>
</div>
</div>
</div>
<?php require 'layout_footer.php'; ?>

