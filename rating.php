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
    $rating = $input['rating'] ?? 0;
    $review = $input['review'] ?? '';
    
    if (!$resource_id || !$rating || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'error' => 'Valid resource ID and rating (1-5) required']);
        exit;
    }
    
    try {
        $conn->beginTransaction();
        
        // Insert or update rating
        $upsert_query = "INSERT INTO ratings (user_id, resource_id, rating, review) 
                        VALUES (?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review)";
        $upsert_stmt = $conn->prepare($upsert_query);
        $upsert_stmt->execute([$user['id'], $resource_id, $rating, $review]);
        
        // Update resource rating statistics
        $stats_query = "UPDATE resources SET 
                       rating_avg = (SELECT AVG(rating) FROM ratings WHERE resource_id = ?),
                       rating_count = (SELECT COUNT(*) FROM ratings WHERE resource_id = ?)
                       WHERE id = ?";
        $stats_stmt = $conn->prepare($stats_query);
        $stats_stmt->execute([$resource_id, $resource_id, $resource_id]);
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Rating submitted successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
