<?php
/**
 * 公共函数库
 * Common Functions
 */

// 12位固定长度编码函数
if (!function_exists('encode_id')) {
    function encode_id($id, $type = 'recipe') {
        // 将ID转换为36进制
        $base36 = base_convert($id, 10, 36);
        // 计算需要填充的长度（总共12位，前缀2位 + 有效数字）
        $pad_length = 10 - strlen($base36);

        if ($type === 'page') {
            // 页面编码：必须以A开头，第二位大写
            $prefix = 'A' . strtoupper(substr(md5($id . 'page'), 0, 1));
        } else {
            // 菜谱编码：不能以A开头
            $hash = strtoupper(md5($id . 'recipe'));
            $p1 = $hash[0];
            $p2 = $hash[1];
            if ($p1 === 'A') {
                $p1 = 'B';
            }
            // 第二位保持大写（避免混淆）
            $prefix = $p1 . $p2;
        }

        // 有效36进制部分保持大写（用于解码）
        $valid_part = strtoupper($base36);
        $padded = str_pad('', $pad_length, '0');

        return $prefix . $padded . $valid_part;
    }
}

// 解码函数
if (!function_exists('decode_id')) {
    function decode_id($encoded) {
        // 移除前2位前缀
        $body = substr($encoded, 2);
        // 提取有效的36进制字符（数字和大写字母）
        $cleaned = '';
        for ($i = 0; $i < strlen($body); $i++) {
            $char = $body[$i];
            // 只保留数字和大写字母（有效36进制字符）
            if (is_numeric($char) || (ctype_upper($char))) {
                $cleaned .= $char;
            }
        }
        // 转为小写进行解码
        $base36 = strtolower($cleaned);
        $base36 = ltrim($base36, '0');
        // 如果为空，返回0
        if (empty($base36)) {
            return 0;
        }
        // 转换回10进制
        return base_convert($base36, 36, 10);
    }
}

// 记录访问日志（已禁用以提升性能）
if (!function_exists('record_visit')) {
    function record_visit() {
        // 暂时禁用访问记录，避免数据库操作导致页面加载缓慢
        // 如需启用，请取消下方注释
        return;

        /*
        try {
            if (!file_exists(DB_PATH)) return;
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $request_uri = $_SERVER['REQUEST_URI'] ?? '/';

            // 记录日志
            $stmt = $db->prepare("INSERT INTO visit_logs (ip, user_agent, request_uri) VALUES (?, ?, ?)");
            $stmt->execute([$ip, $user_agent, $request_uri]);
        } catch (Exception $e) {
            // 忽略错误，以免影响主流程
        }
        */
    }
}

// 获取总访问量
if (!function_exists('get_total_visits')) {
    function get_total_visits() {
        global $db;
        try {
            if (!isset($db) || !$db) {
                if (!file_exists(DB_PATH)) {
                    return 0;
                }
                $db = new PDO('sqlite:' . DB_PATH);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            $visit_logs_exists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='visit_logs'")->fetchColumn();
            if ($visit_logs_exists) {
                return (int)$db->query("SELECT COUNT(*) FROM visit_logs")->fetchColumn();
            }
            $visits_exists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='visits'")->fetchColumn();
            if ($visits_exists) {
                return (int)$db->query("SELECT COUNT(*) FROM visits")->fetchColumn();
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

// CSS 压缩函数（安全版，保留Unicode字符）
if (!function_exists('minify_css')) {
    function minify_css($css) {
        // 移除多行注释
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // 保留 content 属性中的引号内容（避免破坏特殊字符）
        $css = preg_replace_callback('/content\s*:\s*([\'"])(.*?)\1/s', function($matches) {
            return 'content:' . $matches[1] . $matches[2] . $matches[1];
        }, $css);
        // 移除换行、制表符
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        // 多个空格转一个（但在content值内不压缩）
        $css = preg_replace('/\s+/', ' ', $css);
        // 移除符号周围空格（但不在引号内）
        $css = preg_replace('/\s*([\{\}:;,])\s*/', '$1', $css);
        return trim($css);
    }
}

// JS 压缩函数（安全版）
if (!function_exists('minify_js')) {
    function minify_js($js) {
        // 1. 移除多行注释
        $js = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $js);
        // 2. 移除空行，去除首尾空格
        $lines = explode("\n", $js);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines, function($line) { return $line !== ''; });
        // 3. 重新组合
        return implode("\n", $lines);
    }
}

// 重命名数据库文件
if (!function_exists('rename_database')) {
    function rename_database($new_name) {
        $data_dir = dirname(DB_PATH);
        $old_path = DB_PATH;
        $new_path = $data_dir . '/' . $new_name;
        
        // 验证新名称
        if (empty($new_name)) {
            return ['success' => false, 'message' => '数据库名称不能为空'];
        }
        
        // 检查扩展名
        if (!preg_match('/\.db$/i', $new_name)) {
            $new_name .= '.db';
            $new_path = $data_dir . '/' . $new_name;
        }
        
        // 检查是否与当前文件相同
        if ($old_path === $new_path) {
            return ['success' => false, 'message' => '新名称与当前名称相同'];
        }
        
        // 检查目标文件是否存在
        if (file_exists($new_path)) {
            return ['success' => false, 'message' => '该数据库名称已存在'];
        }
        
        // 关闭所有数据库连接
        try {
            $GLOBALS['db'] = null;
            Config::$db = null;
        } catch (Exception $e) {
            // 忽略
        }
        
        // 重命名文件
        if (!rename($old_path, $new_path)) {
            return ['success' => false, 'message' => '重命名失败，请检查文件权限'];
        }
        
        // 创建 .htaccess 保护数据库目录
        $htaccess_path = $data_dir . '/.htaccess';
        if (!file_exists($htaccess_path)) {
            file_put_contents($htaccess_path, "Deny from all\n");
        }
        
        return ['success' => true, 'message' => '数据库重命名成功', 'new_path' => $new_path];
    }
}

// 获取数据库文件列表
if (!function_exists('get_database_list')) {
    function get_database_list() {
        $data_dir = dirname(DB_PATH);
        $db_files = glob($data_dir . '/*.db');
        
        $list = [];
        foreach ($db_files as $file) {
            $list[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'is_current' => ($file === DB_PATH)
            ];
        }
        
        return $list;
    }
}
?>
