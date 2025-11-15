<?php
session_start();

// Simple admin dashboard test
echo "<h1>🧪 Admin Dashboard Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Not Logged In</h3>";
    echo "<p>You need to login first as admin.</p>";
    echo "<a href='simple_login.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>Login as Admin</a>";
    echo "</div>";
    exit;
}

// Check if user is admin
if ($_SESSION['user_role'] !== 'admin') {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Access Denied</h3>";
    echo "<p>You need admin role to access this page.</p>";
    echo "<p>Your current role: " . htmlspecialchars($_SESSION['user_role']) . "</p>";
    echo "</div>";
    exit;
}

echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
echo "<h3>✅ Admin Access Granted!</h3>";
echo "<p><strong>Logged in as:</strong></p>";
echo "<ul>";
echo "<li><strong>Name:</strong> " . htmlspecialchars($_SESSION['user_name']) . "</li>";
echo "<li><strong>Email:</strong> " . htmlspecialchars($_SESSION['user_email']) . "</li>";
echo "<li><strong>Role:</strong> " . htmlspecialchars($_SESSION['user_role']) . "</li>";
echo "<li><strong>Department:</strong> " . htmlspecialchars($_SESSION['user_department']) . "</li>";
echo "</ul>";
echo "</div>";

// Test database connection
try {
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p>✅ Database connection successful</p>";
    
    // Test basic queries
    $users_query = "SELECT COUNT(*) as count FROM users";
    $users_stmt = $conn->prepare($users_query);
    $users_stmt->execute();
    $user_count = $users_stmt->fetch()['count'];
    
    echo "<p>✅ Found $user_count users in database</p>";
    
    $resources_query = "SELECT COUNT(*) as count FROM resources";
    $resources_stmt = $conn->prepare($resources_query);
    $resources_stmt->execute();
    $resource_count = $resources_stmt->fetch()['count'];
    
    echo "<p>✅ Found $resource_count resources in database</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>🚀 Admin Dashboard Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='dashboard/admin.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>📊 Original Admin Dashboard</a>";
echo "<a href='dashboard/admin_new.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>✨ New Admin Dashboard</a>";
echo "</div>";

echo "<h3>🔧 Troubleshooting:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='diagnose_problem.php' style='background: #f59e0b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>🔍 Diagnose Issues</a>";
echo "<a href='simple_login.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;'>🔐 Login Page</a>";
echo "</div>";
?>
