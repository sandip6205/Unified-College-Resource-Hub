<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$user = $auth->getCurrentUser();

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get unread notification count
    $count_query = "SELECT COUNT(*) as count FROM notifications 
                   WHERE (user_id = ? OR user_role = ?) AND seen = FALSE";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute([$user['id'], $user['role']]);
    $count = $count_stmt->fetch()['count'];
    
    echo json_encode(['count' => (int)$count]);
    
} catch (Exception $e) {
    echo json_encode(['count' => 0, 'error' => $e->getMessage()]);
}
?>
