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
            $prefix = 'A' . substr(md5($id . 'page'), 0, 1);
        } else {
            $hash = strtoupper(md5($id . 'recipe'));
            $p1 = $hash[0];
            $p2 = $hash[1];
            if ($p1 === 'A') $p1 = 'B';
            $prefix = $p1 . $p2;
        }

        $encoded = $prefix . str_pad($base36, $pad_length + strlen($base36), '0', STR_PAD_LEFT);
        $hash = md5($id . $type . $encoded);
        $hash_len = strlen($hash);
        $chars = str_split($encoded);
        foreach ($chars as $i => $char) {
            if (ctype_alpha($char)) {
                $hex = $hash[$i % $hash_len];
                if ((hexdec($hex) % 2) === 0) {
                    $chars[$i] = strtolower($char);
                } else {
                    $chars[$i] = strtoupper($char);
                }
            }
        }
        return implode('', $chars);
    }
}

// 解码函数
if (!function_exists('decode_id')) {
    function decode_id($encoded) {
        $base36 = substr(strtolower($encoded), 2);
        $base36 = ltrim($base36, '0');
        // 如果为空，返回0
        if (empty($base36)) {
            return 0;
        }
        // 转换回10进制
        return base_convert($base36, 36, 10);
    }
}
?>
