<?php
require'layout_header.php';
?>
<div class="page-header">
<h3 class="mb-0"><i class="fas fa-file-code"></i> 开发文档</h3>
</div>
<div class="card">
<div class="card-body">
<!-- 项目概述 -->
<section class="mb-5">
<h4 class="text-primary mb-3"><i class="fas fa-info-circle"></i> 项目概述</h4>
<div class="alert alert-info">
<p class="mb-0">
本项目为 <strong>PHP + SQLite</strong> 开发的轻量级菜谱展示网站，分为前台用户访问区和后台管理员管理区，
实现菜谱展示、分类浏览、菜谱增删改查、分类管理等核心功能，无框架轻量化开发，部署简单。
</p>
</div>
</section>
<!-- 核心技术栈 -->
<section class="mb-5">
<h4 class="text-primary mb-3"><i class="fas fa-layer-group"></i> 核心技术栈</h4>
<div class="row">
<div class="col-md-6 mb-3">
<div class="d-flex align-items-center p-3 bg-light rounded">
<i class="fas fa-code text-primary fs-3 me-3"></i>
<div>
<strong>前端</strong><br>
<small class="text-muted">HTML5 + CSS + JavaScript</small>
</div>
</div>
</div>
<div class="col-md-6 mb-3">
<div class="d-flex align-items-center p-3 bg-light rounded">
<i class="fas fa-server text-success fs-3 me-3"></i>
<div>
<strong>后端</strong><br>
<small class="text-muted">原生PHP (无框架)</small>
</div>
</div>
</div>
<div class="col-md-6 mb-3">
<div class="d-flex align-items-center p-3 bg-light rounded">
<i class="fas fa-database text-warning fs-3 me-3"></i>
<div>
<strong>数据库</strong><br>
<small class="text-muted">SQLite 3 (data.db)</small>
</div>
</div>
</div>
<div class="col-md-6 mb-3">
<div class="d-flex align-items-center p-3 bg-light rounded">
<i class="fas fa-file-alt text-info fs-3 me-3"></i>
<div>
<strong>Markdown解析</strong><br>
<small class="text-muted">Parsedown</small>
</div>
</div>
</div>
</div>
</section>
<!-- 项目目录结构 -->
<section class="mb-5">
<h4 class="text-primary mb-3"><i class="fas fa-folder-tree"></i> 项目目录结构</h4>
<div class="bg-dark text-light p-4 rounded" style="font-family: 'Courier New', monospace;">
<div class="text-warning">recipe/</div>
<div class="ms-3">
<div class="text-info">├── index.php <span class="text-muted">// 前台首页</span></div>
<div class="text-info">├── recipe.php <span class="text-muted">// 菜谱详情页</span></div>
<div class="text-info">├── install.php <span class="text-muted">// 安装程序</span></div>
<div class="text-warning mt-2">├── admin/ <span class="text-muted">// 后台管理</span></div>
<div class="ms-3">
<div class="text-info">├── login.php <span class="text-muted">// 登录页面</span></div>
<div class="text-info">├── index.php <span class="text-muted">// 管理首页</span></div>
<div class="text-info">├── recipe_*.php <span class="text-muted">// 菜谱管理</span></div>
<div class="text-info">├── category.php <span class="text-muted">// 分类管理</span></div>
<div class="text-info">├── docs.php <span class="text-muted">// 文档中心</span></div>
<div class="text-info">└── profile.php <span class="text-muted">// 修改密码</span></div>
</div>
<div class="text-warning mt-2">├── data/ <span class="text-muted">// 数据库</span></div>
<div class="ms-3">
<div class="text-success">└── data.db <span class="text-muted">// SQLite数据库</span></div>
</div>
<div class="text-warning mt-2">├── assets/ <span class="text-muted">// 静态资源</span></div>
<div class="ms-3">
<div class="text-info">├── css/ <span class="text-muted">// 样式文件</span></div>
<div class="text-info">└── js/ <span class="text-muted">// 脚本文件</span></div>
</div>
<div class="text-warning mt-2">├── uploads/ <span class="text-muted">// 上传文件</span></div>
<div class="ms-3">
<div class="text-info">└── images/ <span class="text-muted">// 图片目录</span></div>
</div>
<div class="text-warning mt-2">└── libs/ <span class="text-muted">// 第三方库</span></div>
<div class="ms-3">
<div class="text-info">└── Parsedown.php <span class="text-muted">// Markdown解析</span></div>
</div>
</div>
</div>
</section>
<!-- 功能模块 -->
<section class="mb-5">
<h4 class="text-primary mb-3"><i class="fas fa-puzzle-piece"></i> 功能模块</h4>
<div class="row">
<div class="col-md-6">
<div class="card border-primary mb-3">
<div class="card-header bg-primary text-white">
<i class="fas fa-user"></i> 前台用户模块
</div>
<div class="card-body">
<ul class="mb-0">
<li>菜谱列表展示</li>
<li>菜谱详情查看</li>
<li>分类筛选</li>
<li>Markdown内容渲染</li>
</ul>
</div>
</div>
</div>
<div class="col-md-6">
<div class="card border-danger mb-3">
<div class="card-header bg-danger text-white">
<i class="fas fa-shield-alt"></i> 后台管理模块
</div>
<div class="card-body">
<ul class="mb-0">
<li>管理员登录验证</li>
<li>菜谱增删改查</li>
<li>分类管理</li>
<li>图片上传</li>
</ul>
</div>
</div>
</div>
</div>
</section>
<!-- 数据库表结构 -->
<section class="mb-5">
<h4 class="text-primary mb-3"><i class="fas fa-table"></i> 数据库表结构</h4>
<div class="table-responsive">
<table class="table table-bordered">
<thead class="table-light">
<tr>
<th>表名</th>
<th>说明</th>
<th>主要字段</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>recipes</code></td>
<td>菜谱表</td>
<td>id, title, description, content, category_id, cost_price, sell_price, is_public</td>
</tr>
<tr>
<td><code>categories</code></td>
<td>分类表</td>
<td>id, name</td>
</tr>
<tr>
<td><code>admin</code></td>
<td>管理员表</td>
<td>id, username, password</td>
</tr>
</tbody>
</table>
</div>
</section>
<!-- 快速链接 -->
<section>
<h4 class="text-primary mb-3"><i class="fas fa-link"></i> 快速链接</h4>
<div class="d-flex flex-wrap gap-2">
<a href="docs.php" class="btn btn-outline-primary">
<i class="fas fa-book"></i> 文档中心
</a>
<a href="debug.php" class="btn btn-outline-warning">
<i class="fas fa-bug"></i> 程序调试
</a>
<a href="../" class="btn btn-outline-success" target="_blank">
<i class="fas fa-home"></i> 前台首页
</a>
<a href="https://github.com/erusev/parsedown" class="btn btn-outline-secondary" target="_blank">
<i class="fab fa-github"></i> Parsedown
</a>
</div>
</section>
</div>
</div>
<?php require'layout_footer.php';?>

