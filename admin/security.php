<?php
if (!defined('ADMIN_ACCESS')) {
die('Access Denied');
}
@ini_set('session.cookie_httponly', 1);
@ini_set('session.use_only_cookies', 1);
@ini_set('session.cookie_secure', 0);
function xss_clean($data) {
if (is_array($data)) {
return array_map('xss_clean', $data);
}
return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
function generate_csrf_token() {
if (!isset($_SESSION['csrf_token'])) {
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
return $_SESSION['csrf_token'];
}
function verify_csrf_token($token) {
return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function check_login_attempts($username) {
if (!isset($_SESSION['login_attempts'])) {
$_SESSION['login_attempts'] = [];
}
$attempts = $_SESSION['login_attempts'][$username] ?? 0;
if ($attempts >= 5) {
$lockout_time = $_SESSION['lockout_time'][$username] ?? 0;
if (time() - $lockout_time < 900) {
return false;
} else {
$_SESSION['login_attempts'][$username] = 0;
unset($_SESSION['lockout_time'][$username]);
}
}
return true;
}
function record_login_failure($username) {
if (!isset($_SESSION['login_attempts'][$username])) {
$_SESSION['login_attempts'][$username] = 0;
}
$_SESSION['login_attempts'][$username]++;
if ($_SESSION['login_attempts'][$username] >= 5) {
$_SESSION['lockout_time'][$username] = time();
}
}
function clear_login_attempts($username) {
if (isset($_SESSION['login_attempts'][$username])) {
unset($_SESSION['login_attempts'][$username]);
}
if (isset($_SESSION['lockout_time'][$username])) {
unset($_SESSION['lockout_time'][$username]);
}
}
function safe_query($db, $sql, $params = []) {
$stmt = $db->prepare($sql);
$stmt->execute($params);
return $stmt;
}

