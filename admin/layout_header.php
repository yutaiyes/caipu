<?php
// 引入配置文件（包含时区设置）
require_once '../config.php';

// 生成前端HTTP基础URL（用于后台预览）
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// 使用 config.php 中计算的 BASE_URI，它已经自动处理了目录更名的情况
define('FRONTEND_BASE_URL', $protocol . '://' . $host . BASE_URI);

if(session_status()===PHP_SESSION_NONE){
session_start();
}
if(!defined('ADMIN_ACCESS')){
define('ADMIN_ACCESS',true);
}
require_once'security.php';
if(!isset($_SESSION['admin'])){
header('Location: login.php');
exit;
}
// 设置管理员登录状态，供前端检测
$_SESSION['admin_logged_in'] = true;
$db=new PDO('sqlite:../data/data.db');

// 获取 compress_css 设置（使用 Config::get 而不是直接查询数据库）
$compress_css_setting = Config::get('compress_css', '0');

$current_page=basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>商用菜谱管理后台</title>
<?php
$use_min = ($compress_css_setting === '1');
$css_ext = $use_min ? '.min.css' : '.css';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/admin<?= $css_ext ?>">
<?php if (isset($extra_css)): ?>
<link rel="stylesheet" href="../assets/css/<?= str_replace('.css', '', $extra_css) ?><?= $css_ext ?>">
<?php endif; ?>
</head>
<body>
<!-- 顶部导航栏（移动端） -->
<nav class="navbar navbar-dark bg-gradient d-md-none">
<div class="container-fluid">
<span class="navbar-brand">
<i class="fas fa-utensils"></i> 菜谱管理
</span>
<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
<span class="navbar-toggler-icon"></span>
</button>
</div>
</nav>
<div class="container-fluid">
<div class="row">
<!-- 侧边栏（桌面端） -->
<div class="col-md-2 px-0 sidebar d-none d-md-block">
<div class="text-center py-4">
<h4 class="text-white mb-0"><i class="fas fa-utensils"></i> 菜谱管理</h4>
<small class="text-white-50">欢迎，<?=htmlspecialchars($_SESSION['admin'])?></small>
<?php if(defined('ENVIRONMENT_MODE') && ENVIRONMENT_MODE === 'development'): ?>
<div class="mt-2">
<span class="badge bg-warning text-dark">
<i class="fas fa-code"></i> 开发模式
</span>
</div>
<?php endif; ?>
</div>
<nav class="nav flex-column">
<a class="nav-link <?=$current_page=='index.php'?'active':''?>" href="index.php">
<i class="fas fa-chart-line"></i> 仪表板
</a>
<a class="nav-link <?=$current_page=='settings.php'?'active':''?>" href="settings.php">
<i class="fas fa-cog"></i> 系统设置
</a>
<a class="nav-link <?=$current_page=='site_settings.php'?'active':''?>" href="site_settings.php">
<i class="fas fa-globe"></i> 网站设置
</a>
<a class="nav-link <?=$current_page=='stats.php'?'active':''?>" href="stats.php">
<i class="fas fa-chart-bar"></i> 访问统计
</a>
<a class="nav-link <?=$current_page=='recipe_list.php'?'active':''?>" href="recipe_list.php">
<i class="fas fa-utensils"></i> 菜谱列表
</a>
<a class="nav-link <?=$current_page=='recipe_add.php'?'active':''?>" href="recipe_add.php">
<i class="fas fa-plus-circle"></i> 新增菜谱
</a>
<a class="nav-link <?=$current_page=='category.php'?'active':''?>" href="category.php">
<i class="fas fa-tags"></i> 分类管理
</a>
<a class="nav-link <?=in_array($current_page,['page_list.php','page_add.php','page_edit.php'])?'active':''?>" href="page_list.php">
<i class="fas fa-file-alt"></i> 页面管理
</a>
<a class="nav-link <?=$current_page=='compress.php'?'active':''?>" href="compress.php">
<i class="fas fa-compress"></i> 代码压缩
</a>
<a class="nav-link <?=$current_page=='db_optimize.php'?'active':''?>" href="db_optimize.php">
<i class="fas fa-database"></i> 数据库优化
</a>
<a class="nav-link <?=$current_page=='docs.php'?'active':''?>" href="docs.php">
<i class="fas fa-book"></i> 文档中心
</a>
<a class="nav-link <?=$current_page=='readme.php'?'active':''?>" href="readme.php">
<i class="fas fa-file-code"></i> 开发文档
</a>
<a class="nav-link <?=$current_page=='debug.php'?'active':''?>" href="debug.php">
<i class="fas fa-bug"></i> 程序调试
</a>
<a class="nav-link <?=$current_page=='profile.php'?'active':''?>" href="profile.php">
<i class="fas fa-key"></i> 修改密码
</a>
<hr class="text-white-50 mx-3">
<a class="nav-link text-warning" href="logout.php">
<i class="fas fa-sign-out-alt"></i> 退出登录
</a>
</nav>
</div>
<!-- 侧边栏（移动端 Offcanvas） -->
<div class="offcanvas offcanvas-start sidebar-mobile" tabindex="-1" id="sidebarMenu">
<div class="offcanvas-header">
<h5 class="offcanvas-title text-white">
<i class="fas fa-utensils"></i> 菜谱管理
</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
</div>
<div class="offcanvas-body">
<div class="text-center mb-3">
<small class="text-white-50">欢迎，<?=htmlspecialchars($_SESSION['admin'])?></small>
</div>
<nav class="nav flex-column">
<a class="nav-link <?=$current_page=='index.php'?'active':''?>" href="index.php">
<i class="fas fa-chart-line"></i> 仪表板
</a>
<a class="nav-link <?=$current_page=='recipe_list.php'?'active':''?>" href="recipe_list.php">
<i class="fas fa-utensils"></i> 菜谱列表
</a>
<a class="nav-link <?=$current_page=='recipe_add.php'?'active':''?>" href="recipe_add.php">
<i class="fas fa-plus-circle"></i> 新增菜谱
</a>
<a class="nav-link <?=$current_page=='category.php'?'active':''?>" href="category.php">
<i class="fas fa-tags"></i> 分类管理
</a>
<a class="nav-link <?=in_array($current_page,['page_list.php','page_add.php','page_edit.php'])?'active':''?>" href="page_list.php">
<i class="fas fa-file-alt"></i> 页面管理
</a>
<a class="nav-link <?=$current_page=='settings.php'?'active':''?>" href="settings.php">
<i class="fas fa-cog"></i> 系统设置
</a>
<a class="nav-link <?=$current_page=='site_settings.php'?'active':''?>" href="site_settings.php">
<i class="fas fa-globe"></i> 网站设置
</a>
<a class="nav-link <?=$current_page=='compress.php'?'active':''?>" href="compress.php">
<i class="fas fa-compress"></i> 代码压缩
</a>
<a class="nav-link <?=$current_page=='db_optimize.php'?'active':''?>" href="db_optimize.php">
<i class="fas fa-database"></i> 数据库优化
</a>
<a class="nav-link <?=$current_page=='docs.php'?'active':''?>" href="docs.php">
<i class="fas fa-book"></i> 文档中心
</a>
<a class="nav-link <?=$current_page=='readme.php'?'active':''?>" href="readme.php">
<i class="fas fa-file-code"></i> 开发文档
</a>
<a class="nav-link <?=$current_page=='debug.php'?'active':''?>" href="debug.php">
<i class="fas fa-bug"></i> 程序调试
</a>
<a class="nav-link <?=$current_page=='profile.php'?'active':''?>" href="profile.php">
<i class="fas fa-key"></i> 修改密码
</a>
<hr class="text-white-50 mx-3">
<a class="nav-link text-warning" href="logout.php">
<i class="fas fa-sign-out-alt"></i> 退出登录
</a>
</nav>
</div>
</div>
<!-- 主内容区 -->
<div class="col-md-10 content-wrapper">
<div class="p-4">
<?php if(defined('ENVIRONMENT_MODE') && ENVIRONMENT_MODE === 'development'): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
<i class="fas fa-exclamation-triangle"></i> 
<strong>开发模式已启用</strong> - 系统正在显示详细的错误信息和调试信息。生产环境请在 
<a href="site_settings.php" class="alert-link">网站设置</a> 中切换为生产模式。
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

