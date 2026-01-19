<?php
@mkdir('../uploads/images', 0777, true);
$file = $_FILES['file'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$name = date('YmdHis') . rand(1000, 9999) . '.' . $ext;
move_uploaded_file($file['tmp_name'], "../uploads/images/$name");
echo json_encode([
'success' => 1,
'url' => "/uploads/images/$name"
]);

