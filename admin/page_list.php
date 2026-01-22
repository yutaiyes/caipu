<?php
require'layout_header.php';
try{
$pages=$db->query("SELECT * FROM pages ORDER BY sort_order, id DESC")->fetchAll();
}catch(Exception $e){
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-file-alt"></i> 页面管理</h3>
</div>
<div class="card">
<div class="card-body">
<div class="alert alert-warning">
<h5><i class="fas fa-exclamation-triangle"></i> 需要升级数据库</h5>
<p>页面管理功能需要升级数据库。请按以下步骤操作：</p>
<ol>
<li>在浏览器中访问：<code>../upgrade_pages.php</code></li>
<li>等待升级完成</li>
<li>删除 <code>upgrade_pages.php</code> 文件</li>
<li>刷新本页面</li>
</ol>
<a href="../upgrade_pages.php" class="btn btn-warning mt-3" target="_blank">
<i class="fas fa-arrow-up"></i> 立即升级数据库
</a>
</div>
</div>
</div>
<?php
require'layout_footer.php';
exit;
}
if(isset($_GET['delete'])){
$id=(int)$_GET['delete'];
$db->exec("DELETE FROM pages WHERE id=$id");
echo "<script>alert('删除成功！');location.href='page_list.php';</script>";
exit;
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-file-alt"></i> 页面管理</h3>
</div>
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">
<span><i class="fas fa-list"></i> 页面列表</span>
<a href="page_add.php" class="btn btn-sm btn-primary">
<i class="fas fa-plus"></i> 新增页面
</a>
</div>
<div class="card-body">
<?php if(empty($pages)):?>
<div class="alert alert-info">
<i class="fas fa-info-circle"></i> 暂无页面，点击右上角新增页面
</div>
<?php else:?>
<div class="table-responsive">
<table class="table table-hover">
<thead>
<tr>
<th width="50">ID</th>
<th>标题</th>
<th width="120">URL标识</th>
<th width="120">12位编码</th>
<th width="100">类型</th>
<th width="80">状态</th>
<th width="80">排序</th>
<th width="150">创建时间</th>
<th width="180">操作</th>
</tr>
</thead>
<tbody>
<?php foreach($pages as $p):?>
<tr>
<td><?=$p['id']?></td>
<td>
<strong><?=htmlspecialchars($p['title'])?></strong>
</td>
<td>
<code><?=htmlspecialchars($p['slug'])?></code>
</td>
<td>
<code style="font-size: 11px;"><?=encode_id($p['id'], 'page')?></code>
</td>
<td>
<?php
$types=[
'about'=>'关于',
'privacy'=>'隐私',
'contact'=>'联系',
'partnership'=>'合作',
'custom'=>'自定义'
];
echo $types[$p['type']]??'自定义';
?>
</td>
<td>
<?php if($p['is_public']):?>
<span class="badge bg-success">公开</span>
<?php else:?>
<span class="badge bg-secondary">隐藏</span>
<?php endif;?>
</td>
<td><?=$p['sort_order']?></td>
<td class="text-muted small">
<?=date('Y-m-d H:i',strtotime($p['created_at']))?>
</td>
<td>
<?php
// 根据伪静态设置显示对应格式的预览URL
$rewrite_enabled = Config::get('rewrite_enabled', '0') === '1';
$base12 = encode_id($p['id'], 'page');
if ($rewrite_enabled) {
    // 开启伪静态：显示伪静态URI
    $preview_url = FRONTEND_BASE_URL . $base12 . ".html";
} else {
    // 关闭伪静态：显示动态地址（base12位）
    $preview_url = FRONTEND_BASE_URL . "page.php?base=" . $base12;
}
?>
<a href="<?=$preview_url?>" target="_blank" class="btn btn-sm btn-success" title="预览">
<i class="fas fa-eye"></i>
</a>
<a href="<?=FRONTEND_BASE_URL?>page.php?slug=<?=$p['slug']?>" target="_blank" class="btn btn-sm btn-info" title="兼容预览">
<i class="fas fa-link"></i>
</a>
<a href="page_edit.php?id=<?=$p['id']?>" class="btn btn-sm btn-primary" title="编辑">
<i class="fas fa-edit"></i>
</a>
<a href="?delete=<?=$p['id']?>"
class="btn btn-sm btn-danger"
onclick="return confirm('确定删除此页面吗？')" title="删除">
<i class="fas fa-trash"></i>
</a>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
</div>
<?php endif;?>
</div>
</div>
<?php require'layout_footer.php';?>

