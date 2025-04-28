<?php
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['filePath'])) {
        throw new Exception('No file path provided');
    }

    $filePath = $data['filePath'];
    
    // بررسی امنیتی مسیر فایل
    if (strpos($filePath, '..') !== false || !strpos($filePath, 'uploads/products/')) {
        throw new Exception('Invalid file path');
    }

    if (file_exists($filePath) && unlink($filePath)) {
        echo json_encode([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete file');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}