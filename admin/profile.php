<?php
session_start();
require'../config.php';
if(!isset($_SESSION['admin'])){
header('Location: login.php');
exit;
}

// 演示模式检查
if(is_demo_mode() && $_SERVER['REQUEST_METHOD']==='POST'){
    $_SESSION['profile_error']='演示模式下禁止修改密码！';
    header('Location: profile.php');
    exit;
}

$db=new PDO('sqlite:'.DB_PATH);
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
if($_SERVER['REQUEST_METHOD']==='POST'){
$old_pwd=$_POST['old_password']??'';
$new_pwd=$_POST['new_password']??'';
$confirm_pwd=$_POST['confirm_password']??'';
if(empty($old_pwd)||empty($new_pwd)||empty($confirm_pwd)){
$_SESSION['profile_error']='所有字段都必须填写！';
}else{
$stmt=$db->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->execute([$_SESSION['admin']]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$user){
$_SESSION['profile_error']='用户不存在！';
}elseif(!password_verify($old_pwd,$user['password'])){
$_SESSION['profile_error']='旧密码错误！';
}elseif(strlen($new_pwd)<6){
$_SESSION['profile_error']='新密码长度不能少于6位！';
}elseif($new_pwd!==$confirm_pwd){
$_SESSION['profile_error']='两次输入的新密码不一致！';
}else{
try {
    // 检查数据库目录是否有写权限（SQLite需要目录写权限来创建日志文件）
    $db_dir = dirname(DB_PATH);
    if (!is_writable($db_dir)) {
        throw new Exception("数据库目录 ({$db_dir}) 不可写，请检查服务器权限！");
    }
    if (!is_writable(DB_PATH)) {
        throw new Exception("数据库文件 (" . DB_PATH . ") 不可写，请检查服务器权限！");
    }

    $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE admin SET password = ? WHERE username = ?");
    if ($stmt->execute([$new_hash, $_SESSION['admin']])) {
        $_SESSION['profile_message'] = '密码修改成功！请重新登录。';
        unset($_SESSION['admin']);
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['profile_error'] = '密码修改失败，请重试！';
    }
} catch (Exception $e) {
    $_SESSION['profile_error'] = '系统错误：' . $e->getMessage();
}
}
}
header('Location: profile.php');
exit;
}
require'layout_header.php';
$message=$_SESSION['profile_message']??'';
$error=$_SESSION['profile_error']??'';
unset($_SESSION['profile_message']);
unset($_SESSION['profile_error']);
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-key"></i> 修改密码</h3>
</div>
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card">
<div class="card-body">
<?php if($message):?>
<div class="alert alert-success alert-dismissible fade show">
<i class="fas fa-check-circle"></i> <?=$message?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif;?>
<?php if($error):?>
<div class="alert alert-danger alert-dismissible fade show">
<i class="fas fa-exclamation-circle"></i> <?=$error?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif;?>
<form method="post">
<div class="mb-3">
<label class="form-label">当前用户名</label>
<input type="text" class="form-control"
value="<?=htmlspecialchars($_SESSION['admin'])?>" disabled>
</div>
<div class="mb-3">
<label class="form-label">旧密码 <span class="text-danger">*</span></label>
<input type="password" class="form-control" name="old_password" required>
</div>
<div class="mb-3">
<label class="form-label">新密码 <span class="text-danger">*</span></label>
<input type="password" class="form-control" name="new_password"
placeholder="至少6位字符" required>
<small class="text-muted">密码长度至少6位</small>
</div>
<div class="mb-3">
<label class="form-label">确认新密码 <span class="text-danger">*</span></label>
<input type="password" class="form-control" name="confirm_password"
placeholder="再次输入新密码" required>
</div>
<button type="submit" class="btn btn-primary w-100">
<i class="fas fa-key"></i> 修改密码
</button>
</form>
</div>
</div>
<div class="card mt-3">
<div class="card-body">
<h6 class="mb-3"><i class="fas fa-info-circle"></i> 安全提示</h6>
<ul class="mb-0 text-muted small">
<li>密码长度建议至少8位，包含字母、数字和特殊字符</li>
<li>定期更换密码可以提高账户安全性</li>
<li>不要使用过于简单或容易被猜到的密码</li>
<li>修改密码后需要重新登录</li>
</ul>
</div>
</div>
</div>
</div>
<?php require'layout_footer.php';?>

