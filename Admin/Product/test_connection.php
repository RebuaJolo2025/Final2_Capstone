<?php
header('Content-Type: application/json');
ob_start();

try {
    // Test basic PHP functionality
    echo json_encode([
        'success' => true, 
        'message' => 'PHP is working!',
        'timestamp' => date('Y-m-d H:i:s'),
        'post_data' => $_POST
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
