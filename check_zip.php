<?php
$zip = new ZipArchive();
if ($zip->open('Caipu_v1.0.0.zip') === TRUE) {
    echo "Files in zip:\n";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (strpos($name, 'data/') === 0) {
            echo $name . "\n";
        }
    }
    $zip->close();
} else {
    echo "Failed to open zip\n";
}
