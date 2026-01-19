# README 目录浏览功能说明

## 功能概述

系统提供了 readme 目录浏览的开关功能，管理员可以在后台控制是否允许用户直接浏览 readme 目录下的文档列表。

## 功能位置

**后台路径**：管理后台 → 网站设置 → 文档中心设置

## 功能说明

### 关闭状态（默认）
- 访问 `/readme/` 或 `/readme/index.php` 时，自动重定向到后台文档中心 (`/admin/docs.php`)
- 用户无法直接浏览 readme 目录的文档列表
- 适合不希望公开文档目录的场景

### 开启状态
- 访问 `/readme/` 或 `/readme/index.php` 时，显示美观的文档列表页面
- 用户可以直接浏览和查看所有文档
- 提供侧边栏导航和 Markdown 渲染
- 适合希望公开文档的场景

## 使用方法

### 1. 首次使用（数据库升级）

如果您是从旧版本升级，需要先运行数据库升级脚本：

```bash
php upgrade_settings.php
```

或在浏览器中访问：
```
http://your-domain.com/upgrade_settings.php
```

### 2. 修改设置

1. 登录管理后台
2. 点击左侧菜单 "网站设置"
3. 找到 "文档中心设置" 部分
4. 勾选或取消勾选 "启用 readme 目录浏览"
5. 点击 "保存设置" 按钮

### 3. 测试功能

**测试关闭状态**：
1. 确保开关处于关闭状态（未勾选）
2. 访问 `/readme/`
3. 应该自动跳转到 `/admin/docs.php`

**测试开启状态**：
1. 勾选开关并保存
2. 访问 `/readme/`
3. 应该显示文档列表页面

## 技术实现

### 数据库字段

```sql
key: enable_readme_browse
value: '0' (关闭) 或 '1' (开启)
description: 启用readme目录浏览（0=关闭，1=开启）
```

### 相关文件

- `readme/index.php` - 文档浏览页面，包含开关检测逻辑
- `admin/site_settings.php` - 后台设置页面，包含开关控制
- `upgrade_settings.php` - 数据库升级脚本

### 工作流程

1. 用户访问 `/readme/index.php`
2. 脚本连接数据库，读取 `enable_readme_browse` 设置
3. 如果值为 '0' 或不存在，重定向到 `/admin/docs.php`
4. 如果值为 '1'，显示文档列表页面

## 安全建议

- **默认关闭**：出于安全考虑，默认状态为关闭
- **内部文档**：如果文档包含敏感信息，建议保持关闭状态
- **公开文档**：如果希望用户自助查看文档，可以开启此功能

## 常见问题

### Q: 修改设置后没有生效？
A: 请清除浏览器缓存后重试，或使用无痕模式测试。

### Q: 开启后显示空白页面？
A: 检查 readme 目录下是否有 .md 文档文件，至少需要有 README.md。

### Q: 数据库升级失败？
A: 确保 data/data.db 文件存在且有写入权限，可以手动运行 SQL：
```sql
INSERT INTO settings (key, value, description) 
VALUES ('enable_readme_browse', '0', '启用readme目录浏览（0=关闭，1=开启）');
```

### Q: 如何完全禁止访问 readme 目录？
A: 可以在 .htaccess 中添加规则：
```apache
<Directory "readme">
    Require all denied
</Directory>
```

## 更新日志

- **2026-01-18**: 初始版本，添加 readme 目录浏览开关功能
