<?php
require_once 'layout_header.php';

// 获取统计数据
$db = new PDO('sqlite:../data/data.db');

// 总访问量
$total_visits = $db->query("SELECT COUNT(*) FROM visit_logs")->fetchColumn();

// 今日访问量
$today_visits = $db->query("SELECT COUNT(*) FROM visit_logs WHERE date(created_at, 'localtime') = date('now', 'localtime')")->fetchColumn();

// IP排行 (前20)
$ip_stats = $db->query("SELECT ip, COUNT(*) as count, MAX(created_at) as last_visit, user_agent FROM visit_logs GROUP BY ip ORDER BY count DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);

// 最近访问 (前50)
$recent_visits = $db->query("SELECT * FROM visit_logs ORDER BY created_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="page-header">
    <div class="row align-items-center">
        <div class="col">
            <h3 class="mb-0"><i class="fas fa-chart-bar"></i> 访问统计</h3>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> 刷新
            </button>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center">
                <h5 class="card-title">总访问量</h5>
                <h2 class="display-4"><?= number_format($total_visits) ?></h2>
                <p class="card-text">Total Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center">
                <h5 class="card-title">今日访问</h5>
                <h2 class="display-4"><?= number_format($today_visits) ?></h2>
                <p class="card-text">Today's Visits</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- IP排行 -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list-ol"></i> 访问最频繁的IP (Top 20)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>IP地址</th>
                            <th class="text-center">次数</th>
                            <th class="text-end">最后访问</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ip_stats as $row): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark font-monospace"><?= htmlspecialchars($row['ip']) ?></span>
                                <br>
                                <small class="text-muted" title="<?= htmlspecialchars($row['user_agent']) ?>">
                                    <?= mb_substr(htmlspecialchars($row['user_agent']), 0, 30) ?>...
                                </small>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge bg-primary rounded-pill"><?= $row['count'] ?></span>
                            </td>
                            <td class="text-end align-middle small">
                                <?= date('m-d H:i', strtotime($row['last_visit'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ip_stats)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">暂无数据</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 最近访问记录 -->
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> 最近访问记录 (Top 50)</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>时间</th>
                            <th>IP地址</th>
                            <th>访问页面</th>
                            <th>客户端信息 (UA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_visits as $row): ?>
                        <tr>
                            <td class="small text-nowrap"><?= date('m-d H:i:s', strtotime($row['created_at'])) ?></td>
                            <td><span class="badge bg-light text-dark font-monospace"><?= htmlspecialchars($row['ip']) ?></span></td>
                            <td class="small text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($row['request_uri']) ?>">
                                <?= htmlspecialchars($row['request_uri']) ?>
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['user_agent']) ?>">
                                <?= htmlspecialchars($row['user_agent']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_visits)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">暂无数据</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'layout_footer.php'; ?>
