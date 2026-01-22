<?php
require 'config.php';
require 'libs/Parsedown.php';

if (!file_exists(DB_PATH)) {
    header('Location: install.php');
    exit;
}

$db = new PDO('sqlite:' . DB_PATH);

// 获取菜谱ID或时间戳
$id = intval($_GET['id'] ?? 0);
$timestamp = intval($_GET['t'] ?? 0);
$base = $_GET['base'] ?? '';

// 如果有base参数，解码为ID
if ($base) {
    $id = decode_id($base);
}

// 支持通过base参数访问12位编码
// 根据伪静态设置，如果通过id访问且base参数不存在，则重定向
$rewrite_enabled = Config::get('rewrite_enabled', '0') === '1';
if (isset($_GET['id']) && !isset($_GET['base'])) {
    $base12 = encode_id($id);
    if ($rewrite_enabled) {
        // 开启伪静态：重定向到伪静态URI
        $new_url = $base12 . '.html';
    } else {
        // 关闭伪静态：重定向到动态地址（base12位）
        $new_url = 'recipe.php?base=' . $base12;
    }
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $new_url);
    exit;
}

// 查询菜谱数据
if ($timestamp > 0) {
    $data = $db->query("SELECT r.*, c.name as category_name
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.id
        WHERE strftime('%s', r.created_at) = '$timestamp' AND r.is_public=1
        ORDER BY r.id DESC LIMIT 1")->fetch();
} else {
    $data = $db->query("SELECT r.*, c.name as category_name
        FROM recipes r
        LEFT JOIN categories c ON r.category_id = c.id
        WHERE r.id=$id AND r.is_public=1")->fetch();
}

if (!$data) {
    header('Location: index.php');
    exit;
}

// 解析Markdown内容
$Parsedown = new Parsedown();
$html = $Parsedown->text($data['content']);

// 获取相关菜谱
$related = [];
if ($data['category_id']) {
    $current_id = $data['id'];
    $related = $db->query("SELECT *, strftime('%s', created_at) as timestamp FROM recipes
        WHERE category_id={$data['category_id']}
        AND id != $current_id
        AND is_public=1
        ORDER BY id DESC
        LIMIT 3")->fetchAll();
}

// 设置页面特定变量
$page_title = $data['title'];
$page_description = mb_substr(strip_tags($data['content']), 0, 150) . '...';
$page_keywords = $data['title'] . ',' . ($data['category_name'] ?? '');
$extra_css = 'recipe-detail.css';

// 引入公共头部
require_once 'includes/header.php';
?>

<!-- 面包屑导航 -->
<div class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i> 首页</a></li>
                <?php if ($data['category_name']): ?>
                <li class="breadcrumb-item"><a href="/?cat=<?= $data['category_id'] ?>"><?= htmlspecialchars($data['category_name']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($data['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- 主内容区 -->
        <div class="col-lg-9">
            <!-- 菜谱头部 -->
            <div class="recipe-header">
                <h1 class="recipe-title">
                    <i class="fas fa-utensils"></i>
                    <?= htmlspecialchars($data['title']) ?>
                </h1>
                <?php if ($data['description']): ?>
                <p class="lead text-muted"><?= htmlspecialchars($data['description']) ?></p>
                <?php endif; ?>
                <div class="recipe-meta-bar">
                    <?php if ($data['category_name']): ?>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <div class="meta-label">分类</div>
                            <div class="meta-value"><?= htmlspecialchars($data['category_name']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($data['cost_price'] > 0): ?>
                    <div class="meta-item">
                        <i class="fas fa-coins"></i>
                        <div>
                            <div class="meta-label">成本价</div>
                            <div class="meta-value">¥<?= number_format($data['cost_price'], 2) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($data['sell_price'] > 0): ?>
                    <div class="meta-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <div>
                            <div class="meta-label">售价</div>
                            <div class="meta-value">¥<?= number_format($data['sell_price'], 2) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($data['sell_price'] > 0 && $data['cost_price'] > 0): ?>
                    <div class="meta-item">
                        <i class="fas fa-chart-line"></i>
                        <div>
                            <div class="meta-label">利润</div>
                            <div class="meta-value text-success">
                                ¥<?= number_format($data['sell_price'] - $data['cost_price'], 2) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="action-buttons">
                    <a href="<?= $data['category_id'] ? '?cat=' . $data['category_id'] : '' ?>#recipe-<?= $data['id'] ?>" class="btn-back">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                    <button onclick="window.print()" class="btn-print">
                        <i class="fas fa-print"></i> 打印菜谱
                    </button>
                </div>
            </div>
            <!-- 菜谱内容 -->
            <div class="recipe-content">
                <div class="markdown-body">
                    <?= $html ?>
                </div>

                <!-- 底部返回按钮 -->
                <div class="mt-5 text-center action-buttons-bottom">
                    <a href="<?= $data['category_id'] ? '?cat=' . $data['category_id'] : '' ?>#recipe-<?= $data['id'] ?>" class="btn-back">
                        <i class="fas fa-arrow-left"></i> 返回列表
                    </a>
                    <button onclick="window.print()" class="btn-print ml-3">
                        <i class="fas fa-print"></i> 打印菜谱
                    </button>
                </div>
            </div>
        </div>
        <!-- 侧边栏 -->
        <div class="col-lg-3">
            <!-- 价格信息 -->
            <?php if ($data['sell_price'] > 0): ?>
            <div class="price-box mb-4">
                <div class="price-label">建议售价</div>
                <div class="price-value">¥<?= number_format($data['sell_price'], 2) ?></div>
                <?php if ($data['cost_price'] > 0): ?>
                <div class="mt-2 small">
                    成本：¥<?= number_format($data['cost_price'], 2) ?><br>
                    利润：¥<?= number_format($data['sell_price'] - $data['cost_price'], 2) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <!-- 相关菜谱 -->
            <?php if (!empty($related)): ?>
            <div class="related-recipes">
                <h5 class="mb-3">
                    <i class="fas fa-list"></i> 相关菜谱
                </h5>
                <?php foreach ($related as $r): ?>
                <?php
                // 根据伪静态设置显示对应格式的URL
                $base12 = encode_id($r['id']);
                if ($rewrite_enabled) {
                    // 开启伪静态：显示伪静态URI
                    $related_url = $base12 . '.html';
                } else {
                    // 关闭伪静态：显示动态地址（base12位）
                    $related_url = 'recipe.php?base=' . $base12;
                }
                ?>
                <a href="<?= $related_url ?>" class="related-recipe-card">
                    <div class="related-recipe-title">
                        <?= htmlspecialchars($r['title']) ?>
                    </div>
                    <div class="related-recipe-price">
                        ¥<?= number_format($r['sell_price'], 2) ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
