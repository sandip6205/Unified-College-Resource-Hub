<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$user = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $resource_id = $input['resource_id'] ?? 0;
    
    if (!$resource_id) {
        echo json_encode(['success' => false, 'error' => 'Resource ID required']);
        exit;
    }
    
    try {
        // Check if bookmark exists
        $check_query = "SELECT id FROM bookmarks WHERE user_id = ? AND resource_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([$user['id'], $resource_id]);
        
        if ($check_stmt->fetch()) {
            // Remove bookmark
            $delete_query = "DELETE FROM bookmarks WHERE user_id = ? AND resource_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->execute([$user['id'], $resource_id]);
            
            // Update bookmark count
            $update_query = "UPDATE resources SET bookmark_count = bookmark_count - 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->execute([$resource_id]);
            
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Add bookmark
            $insert_query = "INSERT INTO bookmarks (user_id, resource_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->execute([$user['id'], $resource_id]);
            
            // Update bookmark count
            $update_query = "UPDATE resources SET bookmark_count = bookmark_count + 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->execute([$resource_id]);
            
            echo json_encode(['success' => true, 'action' => 'added']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
