<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();

$resource_id = $_GET['id'] ?? 0;
$user = $auth->getCurrentUser();

if (!$resource_id) {
    header("Location: dashboard/" . $user['role'] . ".php");
    exit();
}

// Get resource details
$query = "SELECT * FROM resources WHERE id = ? AND status = 'approved'";
$stmt = $conn->prepare($query);
$stmt->execute([$resource_id]);
$resource = $stmt->fetch();

if (!$resource) {
    header("Location: dashboard/" . $user['role'] . ".php");
    exit();
}

// Update download count
$update_query = "UPDATE resources SET download_count = download_count + 1 WHERE id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->execute([$resource_id]);

// Record download history for students
if ($user['role'] === 'student') {
    $history_query = "INSERT INTO download_history (user_id, resource_id) VALUES (?, ?)";
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->execute([$user['id'], $resource_id]);
}

// For demo purposes, we'll redirect to a placeholder file
// In production, you would serve the actual file from secure storage
$file_path = $resource['file_url'];

// If file exists, serve it
if (file_exists($file_path)) {
    $file_info = pathinfo($file_path);
    $file_name = $resource['title'] . '.' . $file_info['extension'];
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . filesize($file_path));
    
    readfile($file_path);
    exit();
} else {
    // For demo - create a sample file content
    $file_content = "Sample content for: " . $resource['title'] . "\n\n";
    $file_content .= "Subject: " . $resource['subject_name'] . "\n";
    $file_content .= "Description: " . $resource['description'] . "\n";
    $file_content .= "Uploaded by: " . $resource['uploaded_by_name'] . "\n";
    $file_content .= "Upload date: " . $resource['uploaded_at'] . "\n\n";
    $file_content .= "This is a demo file. In production, this would be the actual uploaded file.";
    
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $resource['title'] . '.txt"');
    header('Content-Length: ' . strlen($file_content));
    
    echo $file_content;
    exit();
}
?>
