<?php
// Display current PHP settings
echo "PHP Upload Configuration:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n\n";

// Simple file upload test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $uploadedFile = $_FILES['file'];
    echo "File Upload Test:\n";
    echo "Name: " . $uploadedFile['name'] . "\n";
    echo "Size: " . $uploadedFile['size'] . " bytes (" . round($uploadedFile['size'] / 1024 / 1024, 2) . " MB)\n";
    echo "Type: " . $uploadedFile['type'] . "\n";
    echo "Error: " . $uploadedFile['error'] . "\n";
    
    if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
        echo "Upload successful!\n";
    } else {
        echo "Upload failed with error code: " . $uploadedFile['error'] . "\n";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Upload Test</title>
</head>
<body>
    <h1>PHP Upload Test</h1>
    <pre><?php 
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    echo "post_max_size: " . ini_get('post_max_size') . "\n";
    ?></pre>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html> 