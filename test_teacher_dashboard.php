<?php
session_start();

echo "<h1>🧪 Teacher Dashboard Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Not Logged In</h3>";
    echo "<p>You need to login first as teacher.</p>";
    echo "<a href='simple_login.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>Login as Teacher</a>";
    echo "</div>";
    exit;
}

// Check if user is teacher
if ($_SESSION['user_role'] !== 'teacher') {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Access Denied</h3>";
    echo "<p>You need teacher role to access this page.</p>";
    echo "<p>Your current role: " . htmlspecialchars($_SESSION['user_role']) . "</p>";
    echo "</div>";
    exit;
}

echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h3>✅ Teacher Access Granted!</h3>";
echo "<p><strong>Logged in as:</strong></p>";
echo "<ul>";
echo "<li><strong>Name:</strong> " . htmlspecialchars($_SESSION['user_name']) . "</li>";
echo "<li><strong>Email:</strong> " . htmlspecialchars($_SESSION['user_email']) . "</li>";
echo "<li><strong>Role:</strong> " . htmlspecialchars($_SESSION['user_role']) . "</li>";
echo "<li><strong>Department:</strong> " . htmlspecialchars($_SESSION['user_department']) . "</li>";
echo "</ul>";
echo "</div>";

// Test PHP syntax of teacher dashboard
echo "<h3>🔧 Testing Teacher Dashboard Files:</h3>";

$files_to_test = [
    'dashboard/teacher.php' => 'Original Teacher Dashboard',
    'dashboard/teacher_enhanced.php' => 'Enhanced Teacher Dashboard'
];

foreach ($files_to_test as $file => $name) {
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 6px;'>";
    echo "<strong>$name:</strong> ";
    
    if (file_exists($file)) {
        // Test PHP syntax
        $output = [];
        $return_var = 0;
        exec("php -l \"$file\" 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<span style='color: green;'>✅ Syntax OK</span>";
        } else {
            echo "<span style='color: red;'>❌ Syntax Error:</span>";
            echo "<pre style='background: #fef2f2; padding: 10px; margin: 5px 0;'>" . implode("\n", $output) . "</pre>";
        }
    } else {
        echo "<span style='color: orange;'>⚠️ File not found</span>";
    }
    echo "</div>";
}

echo "<h3>🚀 Teacher Dashboard Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='dashboard/teacher.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>📊 Original Teacher Dashboard</a>";
echo "<a href='dashboard/teacher_enhanced.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>✨ Enhanced Teacher Dashboard</a>";
echo "</div>";

echo "<h3>🔧 Setup Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='setup_enhanced_upload.php' style='background: #f59e0b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>🚀 Setup Enhanced Upload</a>";
echo "<a href='simple_login.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;'>🔐 Login Page</a>";
echo "</div>";

// Test database connection
try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p>✅ Database connection successful</p>";
    
    // Test subjects table
    $subjects_query = "SELECT COUNT(*) as count FROM subjects";
    $subjects_stmt = $conn->prepare($subjects_query);
    $subjects_stmt->execute();
    $subject_count = $subjects_stmt->fetch()['count'];
    
    echo "<p>✅ Found $subject_count subjects in database</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
