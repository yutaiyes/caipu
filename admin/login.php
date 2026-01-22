<?php
session_start();
define('ADMIN_ACCESS',true);
require_once'security.php';
$db=new PDO('sqlite:../data/data.db');
$error='';
$message='';
if(isset($_SESSION['profile_message'])){
$message=$_SESSION['profile_message'];
unset($_SESSION['profile_message']);
}
if($_POST){
$username=$_POST['username']??'';
$password=$_POST['password']??'';
if(!check_login_attempts($username)){
$error='登录失败次数过多，请15分钟后再试';
}else{
$stmt=$db->prepare("SELECT * FROM admin WHERE username=?");
$stmt->execute([$username]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);
if($user&&password_verify($password,$user['password'])){
clear_login_attempts($username);
session_regenerate_id(true);
$_SESSION['admin']=$user['username'];
$_SESSION['initiated']=true;
$_SESSION['last_activity']=time();
header('Location: index.php');
exit;
}else{
record_login_failure($username);
$remaining=5-($_SESSION['login_attempts'][$username]??0);
$error='账号或密码错误';
if($remaining>0&&$remaining<5){
$error.="（还有 {$remaining} 次尝试机会）";
}
}
}
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>后台登录 - 商用菜谱管理系统</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
<div class="login-card">
<div class="login-header">
<i class="fas fa-utensils fa-3x mb-3"></i>
<h4 class="mb-0">商用菜谱管理系统</h4>
<small>后台管理登录</small>
</div>
<div class="login-body">
<?php if(!empty($message)):?>
<div class="alert alert-success">
<i class="fas fa-check-circle"></i> <?=htmlspecialchars($message)?>
</div>
<?php endif;?>
<?php if(!empty($error)):?>
<div class="alert alert-danger">
<i class="fas fa-exclamation-circle"></i> <?=$error?>
</div>
<?php endif;?>
<form method="post">
<div class="mb-3">
<label class="form-label">
<i class="fas fa-user"></i> 用户名
</label>
<input class="form-control" name="username" placeholder="请输入用户名" required autofocus>
</div>
<div class="mb-3">
<label class="form-label">
<i class="fas fa-lock"></i> 密码
</label>
<input type="password" class="form-control" name="password" placeholder="请输入密码" required>
</div>
<button class="btn btn-primary w-100">
<i class="fas fa-sign-in-alt"></i> 登录
</button>
</form>
<div class="text-center mt-3 text-muted small">
<i class="fas fa-info-circle"></i> 默认账号：admin / 123456
</div>
</div>
</div>
</body>
</html>

