<?php
// Test Admin Login Functionality
require_once __DIR__ . '/includes/auth.php';

echo "<h1>🧪 Testing Admin Login</h1>";

try {
    $auth = new Auth();
    
    // Test admin login
    $email = 'admin@college.edu';
    $password = 'password';
    
    echo "<p>Testing login with:</p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> $email</li>";
    echo "<li><strong>Password:</strong> $password</li>";
    echo "</ul>";
    
    $login_result = $auth->login($email, $password);
    
    if ($login_result) {
        echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>✅ Admin Login Successful!</h3>";
        
        $user = $auth->getCurrentUser();
        echo "<p><strong>Logged in as:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Name:</strong> " . htmlspecialchars($user['name']) . "</li>";
        echo "<li><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</li>";
        echo "<li><strong>Role:</strong> " . htmlspecialchars($user['role']) . "</li>";
        echo "<li><strong>Department:</strong> " . htmlspecialchars($user['department']) . "</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h3>🚀 Admin Dashboard Access:</h3>";
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='dashboard/admin.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>⚙️ Go to Admin Dashboard</a>";
        echo "<a href='logout.php' style='background: #6b7280; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>🚪 Logout</a>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fef2f2; border: 1px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>❌ Admin Login Failed!</h3>";
        echo "<p>The login credentials are not working. This could be due to:</p>";
        echo "<ul>";
        echo "<li>Incorrect password in database</li>";
        echo "<li>Admin user doesn't exist</li>";
        echo "<li>Database connection issue</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<p><strong>🔧 Fix Steps:</strong></p>";
        echo "<ol>";
        echo "<li><a href='fix_admin_login.php' style='color: #dc2626; text-decoration: underline;'>Run Admin Login Fix</a></li>";
        echo "<li>Import the database schema if not done</li>";
        echo "<li>Check database connection</li>";
        echo "</ol>";
    }
    
    // Test other users too
    echo "<h3>🧪 Testing Other Demo Users:</h3>";
    
    $test_users = [
        ['email' => 'sharma@college.edu', 'password' => 'password', 'role' => 'Teacher'],
        ['email' => 'rahul@student.edu', 'password' => 'password', 'role' => 'Student']
    ];
    
    foreach ($test_users as $test_user) {
        // Logout first
        $auth->logout();
        
        $test_result = $auth->login($test_user['email'], $test_user['password']);
        
        if ($test_result) {
            echo "<p>✅ <strong>{$test_user['role']} Login:</strong> {$test_user['email']} - SUCCESS</p>";
        } else {
            echo "<p>❌ <strong>{$test_user['role']} Login:</strong> {$test_user['email']} - FAILED</p>";
        }
        
        $auth->logout();
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 1px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Error Testing Login</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . " (Line: " . $e->getLine() . ")</p>";
    echo "</div>";
}

echo "<hr style='margin: 30px 0;'>";
echo "<h3>🔗 Quick Links:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='fix_admin_login.php' style='background: #f59e0b; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>🔧 Fix Admin Login</a>";
echo "<a href='login.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>🔐 Login Page</a>";
echo "<a href='index.php' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;'>🏠 Home Page</a>";
echo "</div>";
?>
