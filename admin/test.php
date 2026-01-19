<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "1. 开始测试...<br>";
if (session_status() === PHP_SESSION_NONE) {
session_start();
echo "2. Session 启动成功<br>";
} else {
echo "2. Session 已经启动<br>";
}
define('ADMIN_ACCESS', true);
echo "3. 常量定义成功<br>";
try {
require_once 'security.php';
echo "4. security.php 加载成功<br>";
} catch (Exception $e) {
echo "4. security.php 加载失败: " . $e->getMessage() . "<br>";
}
try {
$db = new PDO('sqlite:../data/data.db');
echo "5. 数据库连接成功<br>";
} catch (Exception $e) {
echo "5. 数据库连接失败: " . $e->getMessage() . "<br>";
}
$_SESSION['test'] = 'Hello';
echo "6. Session 变量设置成功: " . $_SESSION['test'] . "<br>";
echo "<br>所有测试完成！";

