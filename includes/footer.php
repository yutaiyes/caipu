</div>
<!-- 主内容区域结束 -->

<!-- 页脚 -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><i class="fas fa-utensils"></i> <?= htmlspecialchars($site_title) ?></h5>
                <p class="text-muted"><?= htmlspecialchars($site_description) ?></p>
                <?php if ($site_slogan): ?>
                <p class="text-muted fst-italic">
                    <i class="fas fa-quote-left"></i> <?= htmlspecialchars($site_slogan) ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 mb-3">
                <h6><i class="fas fa-link"></i> 快速链接</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= isset($base_path) ? $base_path : '' ?>"><i class="fas fa-home"></i> 首页</a></li>
                <?php if (!empty($pages)): ?>
                <?php foreach ($pages as $footer_page): ?>
                <?php
                    // 生成页面URL
                    if (Config::get('rewrite_enabled') === '1') {
                        $page_url = encode_id($footer_page['id'], 'page') . '.html';
                    } else {
                        $page_url = 'page.php?base=' . encode_id($footer_page['id'], 'page');
                    }
                    $page_url = (isset($base_path) ? $base_path : '') . $page_url;
                ?>
                <li>
                    <a href="<?= $page_url ?>">
                        <i class="fas fa-file-alt"></i> <?= htmlspecialchars($footer_page['title']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
                <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6><i class="fas fa-chart-bar"></i> 网站统计</h6>
                <ul class="list-unstyled text-muted">
                    <?php
                    $total_recipes = isset($footer_total_recipes) ? $footer_total_recipes : $db->query("SELECT COUNT(*) FROM recipes WHERE is_public=1")->fetchColumn();
                    $total_categories = isset($footer_total_categories) ? $footer_total_categories : count($categories);
                    $avg_price = isset($footer_avg_price) ? $footer_avg_price : $db->query("SELECT AVG(sell_price) FROM recipes WHERE is_public=1 AND sell_price>0")->fetchColumn();
                    ?>
                    <li><i class="fas fa-book"></i> 菜谱总数：<?= $total_recipes ?> 道</li>
                    <li><i class="fas fa-tags"></i> 分类总数：<?= $total_categories ?> 个</li>
                    <?php if ($avg_price > 0): ?>
                    <li><i class="fas fa-yen-sign"></i> 平均售价：¥<?= number_format($avg_price, 2) ?></li>
                    <?php endif; ?>
                    <?php if ($geo_placename): ?>
                    <li><i class="fas fa-map-marker-alt"></i> 服务地区：<?= htmlspecialchars($geo_placename) ?></li>
                    <?php endif; ?>
                    <?php if (Config::get('show_total_visits') === '1'): ?>
                    <li><i class="fas fa-users"></i> 总访问量：<?= number_format(get_total_visits()) ?> 次</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p class="text-muted small mb-0">
                © <?= date('Y') ?> <?= htmlspecialchars($site_title) ?>. All rights reserved.
                <?php if ($site_author): ?>
                | 由 <?= htmlspecialchars($site_author) ?> 提供
                <?php endif; ?>
            </p>
        </div>
    </div>
</footer>

<!-- 返回顶部按钮 -->
<button id="backToTop" class="back-to-top" title="返回顶部">
    <i class="fas fa-arrow-up"></i>
</button>

<script src="https://cdn.staticfile.org/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="<?= isset($base_path) ? $base_path : '' ?>assets/js/main.js"></script>
<?php if (isset($extra_js)): ?>
<script src="<?= isset($base_path) ? $base_path : '' ?>assets/js/<?= $extra_js ?>"></script>
<?php endif; ?>
<script>
// 返回顶部功能
const backToTopBtn = document.getElementById('backToTop');

if (backToTopBtn) {
    // 滚动时显示/隐藏按钮
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    // 点击返回顶部
    backToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}
</script>
</body>
</html>
