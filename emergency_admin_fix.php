<?php
// Emergency Admin Fix - Direct Database Fix
require_once __DIR__ . '/config/database.php';

echo "<h1>🚨 Emergency Admin Login Fix</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p>🔍 Checking database connection...</p>";
    
    // First, let's see what's in the users table
    $check_query = "SELECT * FROM users";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute();
    $all_users = $check_stmt->fetchAll();
    
    echo "<p>✅ Database connected. Found " . count($all_users) . " users.</p>";
    
    if (empty($all_users)) {
        echo "<p>❌ No users found in database! Creating admin user...</p>";
        
        // Create admin user from scratch
        $create_admin = "INSERT INTO users (name, email, role, department, password) VALUES (?, ?, ?, ?, ?)";
        $create_stmt = $conn->prepare($create_admin);
        $create_stmt->execute(['Admin User', 'admin@college.edu', 'admin', 'Administration', 'password']);
        
        echo "<p>✅ Admin user created!</p>";
    } else {
        echo "<h3>👥 Current Users in Database:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f3f4f6;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Name</th><th style='padding: 8px;'>Email</th><th style='padding: 8px;'>Role</th><th style='padding: 8px;'>Password</th></tr>";
        
        foreach ($all_users as $user) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $user['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 8px;'>" . $user['role'] . "</td>";
            echo "<td style='padding: 8px;'>" . substr($user['password'], 0, 20) . "...</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Force update ALL passwords to 'password'
    echo "<p>🔧 Updating all passwords to 'password'...</p>";
    $update_all = "UPDATE users SET password = 'password'";
    $update_stmt = $conn->prepare($update_all);
    $update_stmt->execute();
    
    // Ensure admin user exists with correct credentials
    $admin_exists = "SELECT id FROM users WHERE email = 'admin@college.edu'";
    $admin_stmt = $conn->prepare($admin_exists);
    $admin_stmt->execute();
    
    if (!$admin_stmt->fetch()) {
        echo "<p>🔧 Creating admin user...</p>";
        $insert_admin = "INSERT INTO users (name, email, role, department, password) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_admin);
        $insert_stmt->execute(['Admin User', 'admin@college.edu', 'admin', 'Administration', 'password']);
    } else {
        echo "<p>✅ Admin user exists, updating password...</p>";
        $update_admin = "UPDATE users SET password = 'password' WHERE email = 'admin@college.edu'";
        $update_admin_stmt = $conn->prepare($update_admin);
        $update_admin_stmt->execute();
    }
    
    // Test the login directly
    echo "<h3>🧪 Testing Login Directly:</h3>";
    
    $test_query = "SELECT id, name, email, role, department FROM users WHERE email = ? AND password = ?";
    $test_stmt = $conn->prepare($test_query);
    $test_stmt->execute(['admin@college.edu', 'password']);
    $test_result = $test_stmt->fetch();
    
    if ($test_result) {
        echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>✅ SUCCESS! Admin Login Works!</h3>";
        echo "<p><strong>Admin Details:</strong></p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $test_result['id'] . "</li>";
        echo "<li><strong>Name:</strong> " . htmlspecialchars($test_result['name']) . "</li>";
        echo "<li><strong>Email:</strong> " . htmlspecialchars($test_result['email']) . "</li>";
        echo "<li><strong>Role:</strong> " . $test_result['role'] . "</li>";
        echo "<li><strong>Department:</strong> " . htmlspecialchars($test_result['department']) . "</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h3>🚀 Ready to Login!</h3>";
        echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<p><strong>Use these exact credentials:</strong></p>";
        echo "<ul style='font-family: monospace; font-size: 16px;'>";
        echo "<li><strong>Email:</strong> admin@college.edu</li>";
        echo "<li><strong>Password:</strong> password</li>";
        echo "</ul>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>❌ Still Not Working!</h3>";
        echo "<p>There might be a deeper issue. Let me check the auth system...</p>";
        echo "</div>";
    }
    
    // Show final user list
    echo "<h3>📋 Final User List:</h3>";
    $final_query = "SELECT id, name, email, role, password FROM users ORDER BY role, name";
    $final_stmt = $conn->prepare($final_query);
    $final_stmt->execute();
    $final_users = $final_stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f3f4f6;'><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Name</th><th style='padding: 8px;'>Email</th><th style='padding: 8px;'>Role</th><th style='padding: 8px;'>Password</th></tr>";
    
    foreach ($final_users as $user) {
        $row_color = '';
        if ($user['role'] === 'admin') $row_color = 'background: #fecaca;';
        
        echo "<tr style='$row_color'>";
        echo "<td style='padding: 8px;'>" . $user['id'] . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($user['name']) . "</td>";
        echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td style='padding: 8px;'><strong>" . $user['role'] . "</strong></td>";
        echo "<td style='padding: 8px; font-family: monospace;'>" . $user['password'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='margin: 30px 0;'>";
    echo "<a href='login.php' style='background: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; display: inline-block;'>🔐 Try Login Now</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Make sure:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL is running</li>";
    echo "<li>Database 'college_resource_hub' exists</li>";
    echo "<li>Database schema has been imported</li>";
    echo "</ul>";
    echo "</div>";
}
?>
