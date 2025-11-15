<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user = $auth->getCurrentUser();

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($_POST['action'] === 'mark_all_read') {
        // Mark all notifications as read for this user
        $update_query = "UPDATE notifications SET seen = TRUE 
                        WHERE (user_id = ? OR user_role = ?) AND seen = FALSE";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([$user['id'], $user['role']]);
        
        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
