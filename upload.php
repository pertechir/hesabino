<?php
if (!file_exists('uploads/products')) {
    mkdir('uploads/products', 0777, true);
}

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file['name']);
    $destination = 'uploads/products/' . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        echo json_encode(['success' => true, 'filename' => $fileName]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'خطا در آپلود فایل']);
    }
}
?>