<?php
// Simple test file to debug student dashboard issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Student Dashboard Debug Test</h1>";

try {
    echo "<p>✅ PHP is working</p>";
    
    // Test database connection
    require_once __DIR__ . '/config/database.php';
    echo "<p>✅ Database config loaded</p>";
    
    $database = new Database();
    $conn = $database->getConnection();
    echo "<p>✅ Database connection established</p>";
    
    // Test auth
    require_once __DIR__ . '/includes/auth.php';
    echo "<p>✅ Auth system loaded</p>";
    
    $auth = new Auth();
    echo "<p>✅ Auth object created</p>";
    
    if ($auth->isLoggedIn()) {
        $user = $auth->getCurrentUser();
        echo "<p>✅ User is logged in: " . htmlspecialchars($user['name']) . " (" . $user['role'] . ")</p>";
        
        if ($user['role'] !== 'student') {
            echo "<p>❌ User is not a student. Role: " . $user['role'] . "</p>";
            echo "<p>Please login as a student to access the student dashboard.</p>";
        } else {
            echo "<p>✅ User has student role</p>";
        }
    } else {
        echo "<p>❌ User is not logged in</p>";
        echo "<p><a href='login.php'>Please login first</a></p>";
    }
    
    // Test database tables
    $tables = ['users', 'subjects', 'resources', 'notifications', 'circulars'];
    foreach ($tables as $table) {
        $query = "SELECT COUNT(*) as count FROM $table";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>✅ Table '$table' has {$result['count']} records</p>";
    }
    
    // Test resources query
    $resources_query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
                       FROM resources r 
                       LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                       LEFT JOIN users u ON r.uploaded_by = u.id 
                       WHERE r.status = 'approved' 
                       ORDER BY r.uploaded_at DESC";
    $resources_stmt = $conn->prepare($resources_query);
    $resources_stmt->execute();
    $resources = $resources_stmt->fetchAll();
    echo "<p>✅ Found " . count($resources) . " approved resources</p>";
    
    if (count($resources) > 0) {
        echo "<h3>Sample Resources:</h3><ul>";
        foreach (array_slice($resources, 0, 3) as $resource) {
            echo "<li>" . htmlspecialchars($resource['title']) . " - " . htmlspecialchars($resource['subject_name']) . "</li>";
        }
        echo "</ul>";
    }
    
    echo "<p><strong>All tests passed! The student dashboard should work.</strong></p>";
    echo "<p><a href='dashboard/student.php'>Try Student Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></p>";
}
?>
