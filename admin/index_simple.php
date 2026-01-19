<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
$_SESSION['admin'] = 'admin';
echo "<!DOCTYPE html>";
echo "<html><head><title>测试页面</title></head><body>";
echo "<h1>管理后台测试</h1>";
echo "<p>如果你看到这个页面，说明基本功能正常。</p>";
echo "<p>当前用户: " . htmlspecialchars($_SESSION['admin']) . "</p>";
echo "<p><a href='index.php'>访问正式后台</a></p>";
echo "</body></html>";

