<?php
/**
 * 演示模式检查脚本
 * 在需要保护的操作文件顶部包含此文件
 */

// 演示模式检查函数
function check_demo_mode() {
    if (is_demo_mode()) {
        $current_file = basename($_SERVER['PHP_SELF']);
        $redirect_url = $current_file;

        // 针对不同操作的返回页面
        if (strpos($current_file, '_add.php') !== false || strpos($current_file, '_edit.php') !== false) {
            // 添加/编辑页面
            $base_file = str_replace(['_add.php', '_edit.php'], '_list.php', $current_file);
            $redirect_url = $base_file . '?demo_error=add';
        }

        header('Location: ' . $redirect_url);
        exit;
    }
}

// POST 操作的演示模式检查
function check_demo_mode_post() {
    if (is_demo_mode() && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $current_file = basename($_SERVER['PHP_SELF']);
        $base_file = str_replace(['_add.php', '_edit.php', '_add', '_edit'], '_list.php', $current_file);
        header('Location: ' . $base_file . '?demo_error=post');
        exit;
    }
}
?>
