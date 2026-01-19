<?php
require 'layout_header.php';
$message = '';
$error = '';
if ($_POST) {
$old_pwd = $_POST['old_password'];
$new_pwd = $_POST['new_password'];
$confirm_pwd = $_POST['confirm_password'];
$stmt = $db->prepare("SELECT * FROM admin WHERE username=?");
$stmt->execute([$_SESSION['admin']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!password_verify($old_pwd, $user['password'])) {
$error = '旧密码错误！';
} elseif (strlen($new_pwd) < 6) {
$error = '新密码长度不能少于6位！';
} elseif ($new_pwd !== $confirm_pwd) {
$error = '两次输入的新密码不一致！';
} else {
$new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE admin SET password=? WHERE username=?");
$stmt->execute([$new_hash, $_SESSION['admin']]);
$message = '密码修改成功！';
}
}
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-key"></i> 修改密码</h3>
</div>
<div class="row justify-content-center">
<div class="col-md-6">
<div class="card">
<div class="card-body">
<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show">
<i class="fas fa-check-circle"></i> <?= $message ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
<i class="fas fa-exclamation-circle"></i> <?= $error ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<form method="post">
<div class="mb-3">
<label class="form-label">当前用户名</label>
<input type="text" class="form-control"
value="<?= htmlspecialchars($_SESSION['admin']) ?>" disabled>
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
<?php require 'layout_footer.php'; ?>

