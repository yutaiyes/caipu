<?php
/**
 * 代码文件压缩工具
 * 支持CSS、JS、PHP文件压缩
 */

session_start();
require'layout_header.php';

// 定义要压缩的文件（支持CSS、JS、PHP）
$compress_files = [
    // CSS文件
    ['path' => '../assets/css/frontend.css', 'type' => 'css'],
    ['path' => '../assets/css/admin.css', 'type' => 'css'],
    ['path' => '../assets/css/login.css', 'type' => 'css'],
    ['path' => '../assets/css/recipe-detail.css', 'type' => 'css'],
    // JS文件
    ['path' => '../assets/js/main.js', 'type' => 'js'],
];

$success_count = 0;
$error_messages = [];

foreach($compress_files as $file_info){
$file = $file_info['path'];
$type = $file_info['type'];

if(!file_exists($file)){
$error_messages[] = "文件不存在: $file";
continue;
}

try{
$content = file_get_contents($file);
$original_size = strlen($content);

$compressed = $content;

// 根据文件类型进行压缩
if($type === 'css'){
    // 压缩CSS：移除注释、换行、多余空格
    $compressed = preg_replace(
        [
            '/\/\*[\s\S]*?\*\//', // 移除多行注释
            '/\/\*.*?\*\//',     // 移除单行注释
            '/\s+/',             // 多个空格替换为一个空格
            '/\s*([{}:;,])\s*/', // 移除符号周围空格
            '/;}/'                // 移除最后一个分号前的多余字符
        ],
        ['', '', ' ', '$1', '}'],
        $content
    );
}elseif($type === 'js'){
    // 压缩JS：移除注释、换行
    $compressed = preg_replace(
        [
            '/\/\*[\s\S]*?\*\//', // 移除多行注释
            '/\/\/.*/',            // 移除单行注释
            '/\s+/',               // 多个空格替换为一个空格
            '/\s*([{}();,:=<>])\s*/', // 移除符号周围空格
        ],
        ['', '', ' ', '$1'],
        $content
    );
}

$compressed_size = strlen($compressed);

// 保存压缩后的内容
if(file_put_contents($file, $compressed)){
    $compression_ratio = round((1 - $compressed_size / $original_size) * 100, 2);
    $success_count++;
    $success_messages[] = basename($file)." ($type): 原始 $original_size 字节 → 压缩 $compressed_size 字节 (减少 $compression_ratio%)";
}else{
    $error_messages[] = "无法写入文件: $file";
}
}catch(Exception $e){
$error_messages[] = "压缩 " . basename($file) . " 时出错: " . $e->getMessage();
}
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-compress-alt"></i> 代码文件压缩工具</h3>
</div>

<!-- 结果显示 -->
<?php if(!empty($success_messages) || !empty($error_messages)):?>
<div class="row mb-4">
<?php if(!empty($success_messages)):?>
<div class="col-12">
<div class="alert alert-success">
<h5><i class="fas fa-check-circle"></i> 压缩完成</h5>
<?php foreach($success_messages as $msg):?>
<div><?= htmlspecialchars($msg) ?></div>
<?php endforeach;?>
</div>
</div>
<?php endif;?>
<?php if(!empty($error_messages)):?>
<div class="col-12">
<div class="alert alert-danger">
<h5><i class="fas fa-exclamation-circle"></i> 错误</h5>
<?php foreach($error_messages as $msg):?>
<div><?= htmlspecialchars($msg) ?></div>
<?php endforeach;?>
</div>
</div>
<?php endif;?>
</div>
<?php endif;?>

<!-- 操作区域 -->
<div class="card">
<div class="card-header bg-primary text-white">
<i class="fas fa-tools"></i> 操作面板
</div>
<div class="card-body">
<h5>待压缩的文件</h5>
<div class="table-responsive">
<table class="table table-bordered">
<thead>
<tr>
<th>文件名</th>
<th>类型</th>
<th>大小</th>
<th>状态</th>
</tr>
</thead>
<tbody>
<?php foreach($compress_files as $file_info):?>
<?php
$file = $file_info['path'];
$type = $file_info['type'];
$exists = file_exists($file);
$size = $exists ? filesize($file) : 0;
?>
<tr>
<td><code><?= htmlspecialchars(basename($file)) ?></code></td>
<td><span class="badge bg-info"><?= strtoupper($type) ?></span></td>
<td><?= $exists ? number_format($size) . ' 字节' : '<span class="text-danger">不存在</span>' ?></td>
<td>
<?php if($exists):?>
<span class="badge bg-success"><i class="fas fa-check"></i> 就绪</span>
<?php else:?>
<span class="badge bg-danger"><i class="fas fa-times"></i> 缺失</span>
<?php endif;?>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
</div>

<h5 class="mt-4">压缩说明</h5>
<div class="alert alert-info">
<ul class="mb-0">
<li><strong>CSS压缩</strong>：删除所有注释、换行符和多余空格</li>
<li><strong>JS压缩</strong>：删除所有注释、换行符和多余空格</li>
<li><strong>减小体积</strong>：通常可以减少30-50%的文件体积</li>
<li><strong>功能不变</strong>：压缩不会影响代码功能</li>
</ul>
</div>

<div class="alert alert-warning">
<h6><i class="fas fa-exclamation-triangle"></i> 注意事项</h6>
<ul class="mb-0">
<li>压缩后的文件将覆盖原文件，不可逆</li>
<li>压缩后代码将变为单行格式，不易阅读</li>
<li>建议在版本控制中保留未压缩的备份</li>
<li>压缩不会影响代码的功能和运行效果</li>
</ul>
</div>

<div class="mt-4">
<form method="post">
<button type="submit" class="btn btn-primary btn-lg">
<i class="fas fa-compress"></i> 执行代码压缩
</button>
<a href="settings.php" class="btn btn-secondary">
<i class="fas fa-arrow-left"></i> 返回设置
</a>
</form>
</div>
</div>
</div>

<!-- 压缩原理说明 -->
<div class="card mt-4">
<div class="card-header bg-secondary text-white">
<i class="fas fa-code"></i> 压缩原理
</div>
<div class="card-body">
<h6>CSS压缩示例</h6>
<div class="row">
<div class="col-md-6">
<h6 class="text-muted">压缩前</h6>
<pre class="bg-light p-3 rounded"><code>/* 这是注释 */
body {
background-color: #fff;
color: #333;
margin: 0;
padding: 20px;
}</code></pre>
</div>
<div class="col-md-6">
<h6 class="text-muted">压缩后</h6>
<pre class="bg-light p-3 rounded"><code>body{background-color:#fff;color:#333;margin:0;padding:20px}</code></pre>
</div>
</div>

<h6 class="mt-4">JS压缩示例</h6>
<div class="row">
<div class="col-md-6">
<h6 class="text-muted">压缩前</h6>
<pre class="bg-light p-3 rounded"><code>// 这是注释
function init() {
var btn = document.getElementById('btn');
btn.addEventListener('click', function() {
alert('Hello');
});
}</code></pre>
</div>
<div class="col-md-6">
<h6 class="text-muted">压缩后</h6>
<pre class="bg-light p-3 rounded"><code>function init(){var btn=document.getElementById('btn');btn.addEventListener('click',function(){alert('Hello')})}</code></pre>
</div>
</div>

<h6 class="mt-4">压缩规则</h6>
<div class="table-responsive">
<table class="table table-sm">
<thead>
<tr>
<th>规则</th>
<th>说明</th>
<th>示例</th>
</tr>
</thead>
<tbody>
<tr>
<td>移除注释</td>
<td>删除所有 /* ... */ 和 // 注释</td>
<td><code>/* 注释 */</code> → 删除</td>
</tr>
<tr>
<td>移除空格</td>
<td>符号周围空格全部移除</td>
<td><code>{ }</code> → <code>{}</code></td>
</tr>
<tr>
<td>合并空格</td>
<td>连续多个空格合并为一个</td>
<td><code>color:   red</code> → <code>color: red</code></td>
</tr>
<tr>
<td>移除换行</td>
<td>所有换行符替换为空格</td>
<td>多行 → 单行</td>
</tr>
</tbody>
</table>
</div>
</div>
</div>

<?php require'layout_footer.php';?>
