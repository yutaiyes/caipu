<?php
require_once 'config.php';

// 12位固定长度编码函数已移至 includes/functions.php

if (!file_exists(DB_PATH)) {
    header('Location: install.php');
    exit;
}
$db = new PDO('sqlite:' . DB_PATH);

// 页面特定变量
$page_title = '首页';

// 搜索和筛选
$category_filter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// 构建查询条件
$where_conditions = ["is_public=1"];
$params = [];
if ($category_filter) {
    $where_conditions[] = "category_id = ?";
    $params[] = $category_filter;
}
if ($search_keyword) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ? OR content LIKE ?)";
    $search_like = '%' . $search_keyword . '%';
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
}
$where = implode(' AND ', $where_conditions);

$count_stmt = $db->prepare("SELECT COUNT(*) FROM recipes WHERE $where");
$count_stmt->execute($params);
$total_results = (int)$count_stmt->fetchColumn();
$total_pages = $total_results > 0 ? (int)ceil($total_results / PER_PAGE) : 1;
if ($current_page > $total_pages && $total_results > 0) {
    $current_page = $total_pages;
}
$offset = ($current_page - 1) * PER_PAGE;

$list_stmt = $db->prepare("SELECT r.id, r.title, r.description, r.cost_price, r.sell_price, r.is_public, r.cover, r.category_id, c.name as category_name
    FROM recipes r
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE $where
    ORDER BY r.id DESC
    LIMIT :limit OFFSET :offset");
foreach ($params as $index => $value) {
    $list_stmt->bindValue($index + 1, $value);
}
$list_stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$list_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$list_stmt->execute();
$list = $list_stmt->fetchAll();

$categories = $db->query("SELECT c.*, COUNT(r.id) as recipe_count
    FROM categories c
    LEFT JOIN recipes r ON c.id = r.category_id AND r.is_public=1
    GROUP BY c.id
    ORDER BY c.name")->fetchAll();

// 引入公共头部
require_once 'includes/header.php';

// 获取统计数据（header.php中已经获取了categories）
$total_recipes = $db->query("SELECT COUNT(*) FROM recipes WHERE is_public=1")->fetchColumn();
$total_categories = count($categories);
$avg_price = $db->query("SELECT AVG(sell_price) FROM recipes WHERE is_public=1 AND sell_price>0")->fetchColumn();
$footer_total_recipes = $total_recipes;
$footer_total_categories = $total_categories;
$footer_avg_price = $avg_price;
?>

<!-- Hero 区域 -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="hero-title">
            <i class="fas fa-book-open"></i> <?= htmlspecialchars($site_title) ?>
        </h1>
        <p class="hero-subtitle"><?= htmlspecialchars($site_subtitle) ?></p>
        <?php if ($site_slogan): ?>
        <p class="hero-slogan">
            <i class="fas fa-quote-left"></i> <?= htmlspecialchars($site_slogan) ?> <i class="fas fa-quote-right"></i>
        </p>
        <?php endif; ?>
        <div class="stats-box">
            <div class="stat-item">
                <i class="fas fa-book stat-icon"></i>
                <span class="stat-number"><?= $total_recipes ?></span>
                <span class="stat-label">道菜谱</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-tags stat-icon"></i>
                <span class="stat-number"><?= $total_categories ?></span>
                <span class="stat-label">个分类</span>
            </div>
            <?php if ($avg_price > 0): ?>
            <div class="stat-item">
                <i class="fas fas fa-yen-sign stat-icon"></i>
                <span class="stat-number"><?= number_format($avg_price, 0) ?></span>
                <span class="stat-label">平均售价</span>
            </div>
            <?php endif; ?>
        </div>
        <!-- 搜索框 -->
        <div class="search-box">
            <form method="get" action="index.php" class="search-form">
                <?php if ($category_filter): ?>
                <input type="hidden" name="cat" value="<?= $category_filter ?>">
                <?php endif; ?>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="搜索菜谱名称、描述或内容..." 
                           value="<?= htmlspecialchars($search_keyword) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                </div>
            </form>
            <?php if ($search_keyword): ?>
            <div class="search-result-info">
                <i class="fas fa-info-circle"></i> 
                搜索 "<?= htmlspecialchars($search_keyword) ?>" 找到 <?= $total_results ?> 个结果
                <a href="index.php<?= $category_filter ? '?cat=' . $category_filter : '' ?>" class="ms-2">
                    <i class="fas fa-times"></i> 清除搜索
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container pb-5">
    <!-- 分类筛选 -->
    <?php if (!empty($categories)): ?>
    <div class="category-filter">
        <h5 class="mb-3"><i class="fas fa-filter"></i> 分类筛选</h5>
        <a href="index.php<?= $search_keyword ? '?search=' . urlencode($search_keyword) : '' ?>" 
           class="category-badge <?= !$category_filter ? 'active' : '' ?>">
            <i class="fas fa-th"></i> 全部
            <span class="badge bg-secondary"><?= $total_recipes ?></span>
        </a>
        <?php foreach ($categories as $cat): ?>
        <?php if ($cat['recipe_count'] > 0): ?>
        <a href="?cat=<?= $cat['id'] ?><?= $search_keyword ? '&search=' . urlencode($search_keyword) : '' ?>" 
           class="category-badge <?= $category_filter == $cat['id'] ? 'active' : '' ?>">
            <i class="fas fa-tag"></i> <?= htmlspecialchars($cat['name']) ?>
            <span class="badge bg-secondary"><?= $cat['recipe_count'] ?></span>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- 菜谱列表 -->
    <?php if (!empty($list)): ?>
    <div class="recipes-header">
        <h4>
            <i class="fas fa-utensils"></i> 
            <?php if ($category_filter): ?>
            <?php
            $current_cat = array_filter($categories, function($c) use ($category_filter) {
                return $c['id'] == $category_filter;
            });
            $current_cat = reset($current_cat);
            echo htmlspecialchars($current_cat['name'] ?? '未知分类');
            ?>
            <?php else: ?>
            全部菜谱
            <?php endif; ?>
        </h4>
        <span class="recipes-count">共 <?= $total_results ?> 道菜谱</span>
    </div>
    <div class="row" id="recipe-list">
        <?php foreach ($list as $r): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="recipe-card" id="recipe-<?= $r['id'] ?>">
                <div class="recipe-image" style="background-image: url('image.php?file=<?= htmlspecialchars($r['cover'] ?? '') ?>');">
                    <?php if (!$r['cover']): ?>
                    <img src="assets/images/placeholder.jpg" alt="<?= htmlspecialchars($r['title']) ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php endif; ?>
                    <div class="recipe-overlay">
                        <div class="recipe-category"><?= htmlspecialchars($r['category_name'] ?: '未分类') ?></div>
                        <?php if ($r['is_public']): ?>
                        <span class="badge bg-success">公开</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">私有</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="recipe-content">
                    <h5 class="recipe-title"><?= htmlspecialchars($r['title']) ?></h5>
                    <p class="recipe-description"><?= nl2br(htmlspecialchars(mb_substr(strip_tags($r['description']), 0, 100))) ?></p>
                    <div class="recipe-meta">
                        <div class="recipe-price">
                            <?php if ($r['sell_price'] > 0): ?>
                            <span class="price-main">¥<?= number_format($r['sell_price'], 2) ?></span>
                            <?php if ($r['cost_price'] > 0): ?>
                            <small class="price-cost">成本 ¥<?= number_format($r['cost_price'], 2) ?></small>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="price-free">免费查看</span>
                            <?php endif; ?>
                        </div>
                                <?php
                                // 根据伪静态设置显示对应格式的URL
                                $rewrite_enabled = Config::get('rewrite_enabled', '0') === '1';
                                $base12 = encode_id($r['id']);
                                if ($rewrite_enabled) {
                                    // 开启伪静态：显示伪静态URI
                                    $view_url = $base12 . '.html';
                                } else {
                                    // 关闭伪静态：显示动态地址（base12位）
                                    $view_url = 'recipe.php?base=' . $base12;
                                }
                                ?>
                                <a href="<?= $view_url ?>" class="btn-view-recipe">
                                    <i class="fas fa-arrow-right"></i> 查看详情
                                </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h4>暂无菜谱</h4>
        <?php if ($search_keyword): ?>
        <p>没有找到包含 "<?= htmlspecialchars($search_keyword) ?>" 的菜谱</p>
        <a href="index.php<?= $category_filter ? '?cat=' . $category_filter : '' ?>" class="btn btn-primary mt-3">
            <i class="fas fa-times"></i> 清除搜索
        </a>
        <?php elseif ($category_filter): ?>
        <p>该分类下还没有菜谱，请选择其他分类或查看全部</p>
        <a href="index.php" class="btn btn-primary mt-3">
            <i class="fas fa-home"></i> 返回首页
        </a>
        <?php else: ?>
        <p>还没有任何菜谱，请联系管理员添加</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
<div class="container pb-5">
    <nav aria-label="分页">
        <ul class="pagination justify-content-center">
            <?php
            $query_params = [];
            if ($category_filter) $query_params['cat'] = $category_filter;
            if ($search_keyword) $query_params['search'] = $search_keyword;
            ?>
            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                <?php $query_params['page'] = max(1, $current_page - 1); ?>
                <a class="page-link" href="index.php?<?= http_build_query($query_params) ?>">&laquo;</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                <?php $query_params['page'] = $i; ?>
                <a class="page-link" href="index.php?<?= http_build_query($query_params) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                <?php $query_params['page'] = min($total_pages, $current_page + 1); ?>
                <a class="page-link" href="index.php?<?= http_build_query($query_params) ?>">&raquo;</a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
