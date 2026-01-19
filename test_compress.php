<?php
function compressPhpSafe($content) {
$tokens = token_get_all($content);
$result = '';
foreach ($tokens as $token) {
if (is_array($token)) {
list($id, $text) = $token;
if ($id === T_COMMENT || $id === T_DOC_COMMENT) {
if (preg_match('/@(license|copyright|author)/i', $text)) {
$result .= $text;
} else {
if (strpos($text, "\n") !== false) {
$result .= "\n";
}
}
continue;
}
if ($id === T_WHITESPACE) {
if (strpos($text, "\n") !== false || strpos($text, "\r") !== false) {
$result .= "\n";
} else {
$result .= ' ';
}
continue;
}
$result .= $text;
} else {
$result .= $token;
}
}
$result = preg_replace("/\n{3,}/", "\n\n", $result);
$lines = explode("\n", $result);
$lines = array_map('trim', $lines);
$lines = array_filter($lines, function($line) {
return $line !== '';
});
return implode("\n", $lines) . "\n";
}
$test1 = '<?php
// 这是单行注释
function test() {
// 获取数据
$data = getData();
// 返回结果
return $data;
}
?>';
echo "测试用例1：基本注释和空白\n";
echo "==========================\n";
echo "原始代码：\n";
echo $test1;
echo "\n\n压缩后：\n";
$compressed1 = compressPhpSafe($test1);
echo $compressed1;
echo "\n";
$test2 = '<?php
$url = "http://example.com";  // URL中的//
$color = "#FF0000";           // 颜色代码
$comment = "这是 // 注释符号";
$regex = "/pattern/";         // 正则表达式
?>';
echo "\n测试用例2：字符串中的特殊字符\n";
echo "================================\n";
echo "原始代码：\n";
echo $test2;
echo "\n\n压缩后：\n";
$compressed2 = compressPhpSafe($test2);
echo $compressed2;
echo "\n";
$test3 = '<?php
/**
* 这是多行注释
* @author 作者名
* @license MIT
*/
function example() {
/* 这是块注释 */
return true;
}
?>';
echo "\n测试用例3：多行注释\n";
echo "====================\n";
echo "原始代码：\n";
echo $test3;
echo "\n\n压缩后：\n";
$compressed3 = compressPhpSafe($test3);
echo $compressed3;
echo "\n";
$test4 = '<?php
// 配置文件
define("DB_HOST", "localhost");
define("DB_NAME", "test");
// 数据库类
class Database {
// 连接属性
private $conn;
// 构造函数
public function __construct() {
// 创建连接
$this->conn = new PDO("mysql:host=" . DB_HOST);
}
// 查询方法
public function query($sql) {
// 执行查询
return $this->conn->query($sql);
}
}
?>';
echo "\n测试用例4：复杂代码结构\n";
echo "========================\n";
echo "原始代码：\n";
echo $test4;
echo "\n\n压缩后：\n";
$compressed4 = compressPhpSafe($test4);
echo $compressed4;
echo "\n";
echo "\n语法验证\n";
echo "========\n";
try {
$result1 = @eval('?>' . $compressed1);
echo "✓ 测试用例1 语法正确\n";
} catch (ParseError $e) {
echo "✗ 测试用例1 语法错误: " . $e->getMessage() . "\n";
}
try {
$result2 = @eval('?>' . $compressed2);
echo "✓ 测试用例2 语法正确\n";
} catch (ParseError $e) {
echo "✗ 测试用例2 语法错误: " . $e->getMessage() . "\n";
}
try {
$result3 = @eval('?>' . $compressed3);
echo "✓ 测试用例3 语法正确\n";
} catch (ParseError $e) {
echo "✗ 测试用例3 语法错误: " . $e->getMessage() . "\n";
}
try {
$result4 = @eval('?>' . $compressed4);
echo "✓ 测试用例4 语法正确\n";
} catch (ParseError $e) {
echo "✗ 测试用例4 语法错误: " . $e->getMessage() . "\n";
}
echo "\n所有测试完成！\n";
?>

