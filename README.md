# 🥗 商用菜谱库管理系统 (Commercial Recipe Management System)

一个轻量级、无需 MySQL、开箱即用的 PHP + SQLite 菜谱管理系统。专为中小型餐饮企业或个人开发者设计，支持 Markdown 编辑、12位伪静态 ID、A4 打印优化等特性。

## ✨ 核心特性

- **📥 立即下载**：[Caipu_vlatest](https://github.com/yutaiyes/caipu/releases) (最新稳定版)
- **轻量架构**：原生 PHP + SQLite，无需复杂的数据库配置，部署极简。
- **菜谱管理**：支持 Markdown 编辑、分类管理、成本/售价计算、公开/私有状态控制。
- **伪静态支持**：独创 12 位混合大小写干扰编码 ID（如 `540000000001.html`），既保护真实 ID 又兼顾 SEO 友好。
- **打印优化**：专门针对 A4 纸张优化的打印样式，支持智能缩放和双栏布局，确保内容适配单页打印。
- **单页管理**：支持关于我们、联系方式等自定义单页内容管理。
- **响应式设计**：适配移动端和桌面端，提供流畅的浏览体验。
- **安全机制**：内置 CSRF 保护、输入过滤、数据库备份功能。

## 🚀 快速开始

### 1. 环境要求
- PHP 7.4+
- SQLite 3 扩展 (pdo_sqlite)
- Apache/Nginx (Apache 需开启 mod_rewrite 以支持伪静态)
- 推荐扩展：mbstring, gd, zip

### 2. 安装步骤
1. **下载源码**：克隆或下载本项目到本地。
2. **上传文件**：将所有文件上传至 Web 服务器根目录。
3. **权限设置**：确保 `data/` 和 `uploads/` 目录具有写入权限 (Linux 下建议 755 或 777)。
4. **初始化数据库**：
   - **全新安装**：访问 `http://your-domain.com/install.php`，按照向导设置管理员账号并初始化。
5. **登录后台**：
   - 访问地址：`http://your-domain.com/admin/`
   - 默认账号：`admin`
   - 默认密码：`123456`

### 3. 伪静态配置 (可选但推荐)
本项目支持美观的 12 位 ID 伪静态 URL，能显著提升 URL 的专业度和 SEO 效果。
详细规则请参考：[伪静态规则说明](readme/11_伪静态规则说明.md)

**Apache 配置**：
项目根目录已包含 `.htaccess` 文件，确保服务器配置中开启了 `AllowOverride All` 即可自动生效。

**Nginx 配置**：
请参考 `readme/11_伪静态规则说明.md` 中的 Nginx 配置示例。

## 📂 目录结构

```text
.
├── admin/          # 后台管理系统源码
├── assets/         # 前端静态资源 (CSS, JS)
├── data/           # 数据库存储目录
│   ├── empty.db    # 空数据库模板 (部署用)
│   └── data.db     # 实际运行数据库 (自动生成)
├── includes/       # 核心函数库与公共组件
├── libs/           # 第三方类库 (Parsedown)
├── readme/         # 详细说明文档
├── uploads/        # 图片上传目录
├── config.php      # 核心配置文件
├── install.php     # 安装向导脚本
├── recipe.php      # 菜谱详情页控制器
└── index.php       # 首页控制器
```

## 📄 文档中心

详细文档请查阅 `readme/` 目录：

- [01_快速开始](readme/01_快速开始.md)
- [02_功能使用指南](readme/02_功能使用指南.md)
- [03_系统设置说明](readme/03_系统设置说明.md)
- [11_伪静态规则说明](readme/11_伪静态规则说明.md)

## 🤝 贡献与支持

如果您在使用过程中遇到问题，可以查看 [故障排除](readme/06_故障排除.md) 或提交 Issue。

## 📜 许可证
本项目采用 MIT 许可证，原文与中文说明如下。

### MIT License (English)
MIT License

Copyright (c) 2026 yutaiyes

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

### MIT 许可（中文说明）
MIT 许可协议

版权所有 (c) 2026 yutaiyes

特此免费授予任何获得本软件及相关文档文件（以下简称“软件”）副本的人，
不受限制地处理本软件的权利，包括但不限于使用、复制、修改、合并、出版、
发行、再许可和/或销售软件副本的权利，并允许向其提供软件的人这样做，
但须符合以下条件：

上述版权声明和本许可声明应包含在本软件的所有副本或重要部分中。

本软件按“原样”提供，不提供任何明示或暗示的保证，包括但不限于
对适销性、特定用途适用性和非侵权性的保证。在任何情况下，作者或版权
持有人均不对因软件或软件的使用或其他交易而产生的任何索赔、损害或
其他责任承担责任，无论这些责任是因合同、侵权还是其他原因引起的。
