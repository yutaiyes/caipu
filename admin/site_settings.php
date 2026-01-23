<?php
session_start();
require_once '../includes/functions.php';
$db_path='../data/data.db';
if(isset($_POST['action'])&&$_POST['action']==='save'){
$success=true;
$message='';
try{
$db=new PDO('sqlite:'.$db_path);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$settings=[
'site_title'=>$_POST['site_title']??'',
'site_subtitle'=>$_POST['site_subtitle']??'',
'site_slogan'=>$_POST['site_slogan']??'',
'site_description'=>$_POST['site_description']??'',
'site_keywords'=>$_POST['site_keywords']??'',
'site_author'=>$_POST['site_author']??'',
'geo_region'=>$_POST['geo_region']??'',
'geo_placename'=>$_POST['geo_placename']??'',
'geo_position'=>$_POST['geo_position']??'',
'enable_readme_browse'=>isset($_POST['enable_readme_browse'])?'1':'0',
'show_total_visits'=>isset($_POST['show_total_visits'])?'1':'0',
'environment_mode'=>$_POST['environment_mode']??'production',
'compress_css'=>isset($_POST['compress_css'])?'1':'0',
'demo_mode'=>isset($_POST['demo_mode'])?'1':'0',
];
$stmt=$db->prepare("UPDATE settings SET value = ?, updated_at = CURRENT_TIMESTAMP WHERE key = ?");
foreach($settings as $key=>$value){
$stmt->execute([$value,$key]);
}
// 检查并插入不存在的设置项
foreach($settings as $key=>$value){
$check=$db->prepare("SELECT COUNT(*) FROM settings WHERE key = ?");
$check->execute([$key]);
if($check->fetchColumn()==0){
$desc='';
if($key=='environment_mode') $desc='环境模式：production或development';
if($key=='enable_readme_browse') $desc='是否启用readme目录浏览';
if($key=='show_total_visits') $desc='是否在前台底部显示总访问量';
if($key=='compress_css') $desc='是否压缩CSS文件为单行格式';
if($key=='demo_mode') $desc='演示模式：禁止提交和修改权限';
$insert=$db->prepare("INSERT INTO settings (key, value, description) VALUES (?, ?, ?)");
$insert->execute([$key,$value,$desc]);
}
}
// 如果启用了CSS压缩，执行压缩
if(isset($settings['compress_css']) && $settings['compress_css'] === '1'){
    $base_dir = dirname(__DIR__);
    $css_files = [
        $base_dir . '/assets/css/frontend.css',
        $base_dir . '/assets/css/admin.css',
        $base_dir . '/assets/css/login.css',
        $base_dir . '/assets/css/recipe-detail.css'
    ];
    foreach($css_files as $css_file){
        if(file_exists($css_file)){
            $content = file_get_contents($css_file);
            // 使用公共函数压缩CSS
            $compressed = minify_css($content);
            // 生成 .min.css 文件
            $min_file = str_replace('.css', '.min.css', $css_file);
            file_put_contents($min_file, $compressed);
        }
    }
$message='✓ 网站设置保存成功！CSS文件已压缩为 .min.css。';
}else{
$message='✓ 网站设置保存成功！';
}
}catch(Exception $e){
$success=false;
$message='保存失败：'.$e->getMessage();
}
$show_message=true;
$message_type=$success?'success':'danger';
$message_text=$message;
}
require'layout_header.php';
$db=new PDO('sqlite:'.$db_path);
$settings=[];
$table_exists=$db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
if(!$table_exists){
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
require'layout_footer.php';
exit;
}
$result=$db->query("SELECT key, value FROM settings");
while($row=$result->fetch(PDO::FETCH_ASSOC)){
$settings[$row['key']]=$row['value'];
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
value="<?=htmlspecialchars($settings['site_title']??'')?>"
placeholder="例如：商用菜谱库" required>
<small class="form-text text-muted">显示在浏览器标签页和搜索结果中</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-heading"></i> 网站副标题</label>
<input type="text" name="site_subtitle" class="form-control"
value="<?=htmlspecialchars($settings['site_subtitle']??'')?>"
placeholder="例如：专业的商用菜谱管理系统">
<small class="form-text text-muted">显示在网站标题下方，简短说明网站定位</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-quote-left"></i> 网站口号 (Slogan)</label>
<input type="text" name="site_slogan" class="form-control"
value="<?=htmlspecialchars($settings['site_slogan']??'')?>"
placeholder="例如：让美食触手可及">
<small class="form-text text-muted">品牌宣传语，吸引用户，可用于首页展示</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-align-left"></i> 网站描述 (Meta Description)</label>
<textarea name="site_description" class="form-control" rows="3"
placeholder="例如：专业的商用菜谱管理系统，提供海量菜谱资源" required><?=htmlspecialchars($settings['site_description']??'')?></textarea>
<small class="form-text text-muted">建议150-160字符，显示在搜索结果摘要中，影响点击率</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-tags"></i> 网站关键词 (Meta Keywords)</label>
<input type="text" name="site_keywords" class="form-control"
value="<?=htmlspecialchars($settings['site_keywords']??'')?>"
placeholder="例如：菜谱,美食,烹饪,食谱,商用菜谱">
<small class="form-text text-muted">用逗号分隔，建议5-10个关键词，帮助搜索引擎理解网站内容</small>
</div>
<div class="mb-3">
<label class="form-label"><i class="fas fa-user"></i> 网站作者 (Meta Author)</label>
<input type="text" name="site_author" class="form-control"
value="<?=htmlspecialchars($settings['site_author']??'')?>"
placeholder="例如：商用菜谱库">
<small class="form-text text-muted">网站所有者或管理者名称</small>
</div>
<hr class="my-4">
<h5 class="mb-3"><i class="fas fa-cogs"></i> 系统环境设置</h5>
<div class="mb-3">
<label class="form-label"><i class="fas fa-server"></i> 环境模式</label>
<select name="environment_mode" class="form-select" id="environment_mode">
<option value="production" <?=($settings['environment_mode']??'production')=='production'?'selected':''?>>
生产环境 (Production)
</option>
<option value="development" <?=($settings['environment_mode']??'production')=='development'?'selected':''?>>
开发环境 (Development)
</option>
</select>
<small class="form-text text-muted">
选择当前运行环境。生产环境会隐藏错误信息，开发环境会显示详细调试信息。
</small>
</div>
<div class="alert alert-warning" id="dev-mode-warning" style="display: none;">
<h6><i class="fas fa-exclamation-triangle"></i> 开发环境警告</h6>
<ul class="mb-0">
<li><strong>错误显示</strong>：将显示详细的PHP错误信息</li>
<li><strong>调试信息</strong>：将显示SQL查询和系统调试信息</li>
<li><strong>安全风险</strong>：可能暴露敏感信息，不建议在生产环境使用</li>
<li><strong>性能影响</strong>：调试模式可能影响系统性能</li>
</ul>
</div>
<div class="alert alert-success" id="prod-mode-info" style="display: none;">
<h6><i class="fas fa-check-circle"></i> 生产环境特性</h6>
<ul class="mb-0">
<li><strong>错误隐藏</strong>：不显示PHP错误信息，保护系统安全</li>
<li><strong>性能优化</strong>：关闭调试功能，提升系统性能</li>
<li><strong>日志记录</strong>：错误将记录到日志文件而不是显示</li>
<li><strong>推荐使用</strong>：正式上线后应使用生产环境模式</li>
</ul>
</div>
<script>
document.getElementById('environment_mode').addEventListener('change', function() {
    const devWarning = document.getElementById('dev-mode-warning');
    const prodInfo = document.getElementById('prod-mode-info');
    if (this.value === 'development') {
        devWarning.style.display = 'block';
        prodInfo.style.display = 'none';
    } else {
        devWarning.style.display = 'none';
        prodInfo.style.display = 'block';
    }
});
// 页面加载时触发一次
document.getElementById('environment_mode').dispatchEvent(new Event('change'));
</script>
<hr class="my-4">
<h5 class="mb-3"><i class="fas fa-sliders-h"></i> 其他设置</h5>
<div class="mb-3">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="show_total_visits"
id="show_total_visits" <?=($settings['show_total_visits']??'0')=='1'?'checked':''?>>
<label class="form-check-label" for="show_total_visits">
<i class="fas fa-chart-line"></i> 在前台底部显示总访问量
</label>
</div>
<small class="form-text text-muted">
开启后，将在页面底部显示网站的总访问量统计
</small>
</div>
<div class="mb-3">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="enable_readme_browse"
id="enable_readme_browse" <?=($settings['enable_readme_browse']??'0')=='1'?'checked':''?>>
<label class="form-check-label" for="enable_readme_browse">
<i class="fas fa-folder-open"></i> 启用 readme 目录浏览
</label>
</div>
<small class="form-text text-muted">
开启后，访问 /readme/ 将显示文档列表页面；关闭后（默认），将重定向到后台文档中心
</small>
</div>
<div class="mb-3">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="demo_mode"
id="demo_mode" <?=($settings['demo_mode']??'0')=='1'?'checked':''?>>
<label class="form-check-label" for="demo_mode">
<i class="fas fa-ban"></i> 演示模式
</label>
</div>
<small class="form-text text-muted">
开启后，将禁止所有提交和修改操作（添加、编辑、删除），用于演示目的
</small>
</div>
<hr class="my-4">
<h5 class="mb-3"><i class="fas fa-compress-alt"></i> 性能优化设置</h5>
<div class="mb-3">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="compress_css"
id="compress_css" <?=($settings['compress_css']??'0')=='1'?'checked':''?>>
<label class="form-check-label" for="compress_css">
<i class="fas fa-compress"></i> 压缩CSS文件（单行格式）
</label>
</div>
<small class="form-text text-muted">
启用后，所有CSS文件将被压缩为单行格式，减小文件体积，提升加载速度。建议在生产环境启用。
</small>
<div class="mt-2">
<a href="compress_css.php" class="btn btn-outline-secondary btn-sm">
<i class="fas fa-compress"></i> 手动压缩CSS
</a>
</div>
</div>
<hr class="my-4">
<h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> 地理信息 (GEO SEO)</h5>
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">地理区域代码 (geo.region)</label>
<input type="text" name="geo_region" class="form-control"
value="<?=htmlspecialchars($settings['geo_region']??'')?>"
placeholder="例如：CN (中国)">
<small class="form-text text-muted">ISO 3166-1 国家代码，如：CN, US, JP</small>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">地理位置名称 (geo.placename)</label>
<input type="text" name="geo_placename" class="form-control"
value="<?=htmlspecialchars($settings['geo_placename']??'')?>"
placeholder="例如：中国,北京">
<small class="form-text text-muted">城市或地区名称</small>
</div>
</div>
<div class="mb-3">
<label class="form-label">地理坐标 (geo.position)</label>
<input type="text" name="geo_position" class="form-control"
value="<?=htmlspecialchars($settings['geo_position']??'')?>"
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
<?php if(isset($show_message)&&$show_message):?>
<div class="alert alert-<?=$message_type?> alert-dismissible fade show mt-3">
<i class="fas fa-<?=$message_type=='success'?'check-circle':'exclamation-circle'?>"></i>
<?=$message_text?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif;?>
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
<?php require'layout_footer.php';?>

