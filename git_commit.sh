# Git 提交脚本
# 需要在安装了Git的环境中运行

# 1. 添加所有更改的文件
git add .

# 2. 提交更改
git commit -m "feat: 实现图片防盗链功能和封面图优化

- 新增图片防盗链控制器(image.php)，防止外部盗链
- 封面图改为背景图片展示，确保尺寸一致
- 优化详情页封面图自适应显示
- 更新.htaccess和Nginx规则支持防盗链
- 删除docs目录，将防盗链说明整合到README
- 修复防盗链域名验证逻辑

主要文件：
- image.php - 图片防盗链控制器
- assets/css/frontend.css - 封面图样式优化
- admin/settings.php - 伪静态规则更新
- README.md - 添加防盗链说明"

# 3. 推送到远程仓库
git push origin main

echo "代码已推送到GitHub！"
