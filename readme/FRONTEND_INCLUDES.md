# 前端公共文件使用指南

## 概述

为了保持前端页面的一致性，我们创建了公共的头部和底部文件：
- `includes/header.php` - 公共头部（导航栏）
- `includes/footer.php` - 公共底部（页脚）

## 文件结构

```
项目根目录/
├── includes/
│   ├── header.php    # 公共头部
│   └── footer.php    # 公共底部
├── index.php         # 首页（已更新）
├── recipe.php        # 菜谱详情页（待更新）
└── page.php          # 自定义页面（待更新）
```

## 使用方法

### 基本用法

```php
<?php
// 1. 引入配置和连接数据库
require_once 'config.php';
$db = new PDO('sqlite:data/data.db');

// 2. 设置页面特定变量（可选）
$page_title = '页面标题';  // 会显示为 "页面标题 - 网站标题"
$page_description = '页面描述';  // 用于SEO
$page_keywords = '关键词1,关键词2';  // 用于SEO
$page_slug = 'about';  // 用于高亮导航（自定义页面）

// 3. 引入公共头部
require_once 'includes/header.php';
?>

<!-- 你的页面内容 -->
<div class="container">
    <h1>页面内容</h1>
</div>

<?php
// 4. 引入公共底部
require_once 'includes/footer.php';
?>
```

### 可用变量

#### 在 header.php 中自动设置的变量：

- `$site_title` - 网站标题
- `$site_subtitle` - 网站副标题
- `$site_slogan` - 网站口号
- `$site_description` - 网站描述
- `$site_keywords` - 网站关键词
- `$site_author` - 网站作者
- `$geo_region` - 地理区域代码
- `$geo_placename` - 地理位置名称
- `$geo_position` - 地理坐标
- `$categories` - 分类列表（数组）
- `$pages` - 自定义页面列表（数组）
- `$current_page` - 当前页面文件名

#### 你可以设置的变量（在引入 header.php 之前）：

- `$page_title` - 页面标题（可选）
- `$page_description` - 页面描述（可选）
- `$page_keywords` - 页面关键词（可选）
- `$page_slug` - 页面slug（用于高亮导航，可选）
- `$extra_css` - 额外的CSS文件名（可选）
- `$extra_js` - 额外的JS文件名（可选）
- `$base_path` - 基础路径（子目录时使用，可选）

## 示例

### 示例1：简单页面

```php
<?php
require_once 'config.php';
$db = new PDO('sqlite:data/data.db');

$page_title = '关于我们';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1>关于我们</h1>
    <p>这是关于我们的页面内容...</p>
</div>

<?php require_once 'includes/footer.php'; ?>
```

### 示例2：带额外CSS的页面

```php
<?php
require_once 'config.php';
$db = new PDO('sqlite:data/data.db');

$page_title = '菜谱详情';
$extra_css = 'recipe-detail.css';  // 会加载 assets/css/recipe-detail.css
require_once 'includes/header.php';
?>

<div class="container py-5">
    <!-- 菜谱详情内容 -->
</div>

<?php require_once 'includes/footer.php'; ?>
```

### 示例3：自定义页面（高亮导航）

```php
<?php
require_once 'config.php';
$db = new PDO('sqlite:data/data.db');

$page_slug = 'about';  // 导航中对应的页面slug会被高亮
$page_title = '关于我们';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <!-- 页面内容 -->
</div>

<?php require_once 'includes/footer.php'; ?>
```

## 后端设置对应关系

所有后端设置（`admin/site_settings.php`）都会自动应用到前端：

| 后端设置 | 前端显示位置 |
|---------|------------|
| 网站标题 | 页面标题、导航栏、页脚 |
| 网站副标题 | 首页Hero区域 |
| 网站口号 | 首页Hero区域、页脚 |
| 网站描述 | SEO meta标签、页脚 |
| 网站关键词 | SEO meta标签 |
| 网站作者 | SEO meta标签、页脚版权 |
| 地理区域 | SEO meta标签、页脚统计 |
| 地理位置 | SEO meta标签、页脚统计 |
| 地理坐标 | SEO meta标签 |

## 导航高亮规则

- 首页：`$current_page == 'index.php'`
- 自定义页面：`$page_slug == $page['slug']`

## 注意事项

1. **必须先连接数据库**：header.php 需要查询分类和页面数据
2. **变量作用域**：header.php 中设置的变量在页面和 footer.php 中都可用
3. **路径问题**：如果页面在子目录中，需要设置 `$base_path` 变量
4. **数据库连接**：header.php 会检查 `$db` 变量，如果不存在会自动创建

## 更新其他页面

需要更新的页面：
- [ ] `recipe.php` - 菜谱详情页
- [ ] `page.php` - 自定义页面

更新步骤：
1. 移除原有的 HTML 头部和导航代码
2. 在顶部设置页面特定变量
3. 引入 `includes/header.php`
4. 保留页面主体内容
5. 移除原有的页脚代码
6. 引入 `includes/footer.php`

## 优势

✅ **一致性**：所有页面使用相同的导航和页脚  
✅ **易维护**：修改一处，全站生效  
✅ **SEO友好**：统一的meta标签管理  
✅ **后端联动**：后台设置自动应用到前端  
✅ **灵活性**：支持页面特定的标题、CSS、JS

## 相关文件

- `config.php` - 配置文件
- `admin/site_settings.php` - 后台网站设置
- `assets/css/frontend.css` - 前端主样式
- `assets/js/main.js` - 前端主脚本
