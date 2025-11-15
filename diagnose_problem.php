<?php
echo "<h1>🔍 System Diagnosis</h1>";
echo "<p>Let's find out exactly what's wrong...</p>";

// Test 1: PHP Version
echo "<h3>1. PHP Information</h3>";
echo "<p>✅ PHP Version: " . phpversion() . "</p>";
echo "<p>✅ PHP is working!</p>";

// Test 2: MySQL Extension
echo "<h3>2. MySQL Extensions</h3>";
if (extension_loaded('pdo')) {
    echo "<p>✅ PDO extension loaded</p>";
} else {
    echo "<p>❌ PDO extension NOT loaded</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p>✅ PDO MySQL extension loaded</p>";
} else {
    echo "<p>❌ PDO MySQL extension NOT loaded</p>";
}

// Test 3: MySQL Connection
echo "<h3>3. MySQL Connection Test</h3>";
try {
    $conn = new PDO("mysql:host=localhost", "root", "");
    echo "<p>✅ MySQL server connection successful!</p>";
    
    // Test database creation
    try {
        $conn->exec("CREATE DATABASE IF NOT EXISTS college_resource_hub");
        echo "<p>✅ Database 'college_resource_hub' created/exists</p>";
        
        $conn->exec("USE college_resource_hub");
        echo "<p>✅ Using database 'college_resource_hub'</p>";
        
        // Test table creation
        $create_users = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('student', 'teacher', 'admin') NOT NULL,
            department VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $conn->exec($create_users);
        echo "<p>✅ Users table created/exists</p>";
        
        // Check if admin exists
        $check_admin = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE email = 'admin@college.edu'");
        $check_admin->execute();
        $admin_count = $check_admin->fetch()['count'];
        
        if ($admin_count == 0) {
            echo "<p>⚠️ Admin user doesn't exist. Creating...</p>";
            $insert_admin = $conn->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
            $insert_admin->execute(['Admin User', 'admin@college.edu', 'password', 'admin', 'Administration']);
            echo "<p>✅ Admin user created!</p>";
        } else {
            echo "<p>✅ Admin user exists</p>";
            
            // Update password to make sure it's 'password'
            $update_admin = $conn->prepare("UPDATE users SET password = 'password' WHERE email = 'admin@college.edu'");
            $update_admin->execute();
            echo "<p>✅ Admin password updated to 'password'</p>";
        }
        
        // Test login query
        echo "<h3>4. Login Test</h3>";
        $login_test = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ? AND password = ?");
        $login_test->execute(['admin@college.edu', 'password']);
        $admin_user = $login_test->fetch();
        
        if ($admin_user) {
            echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
            echo "<h3>✅ SUCCESS! Everything is working!</h3>";
            echo "<p><strong>Admin login test successful:</strong></p>";
            echo "<ul>";
            echo "<li>ID: " . $admin_user['id'] . "</li>";
            echo "<li>Name: " . htmlspecialchars($admin_user['name']) . "</li>";
            echo "<li>Email: " . htmlspecialchars($admin_user['email']) . "</li>";
            echo "<li>Role: " . $admin_user['role'] . "</li>";
            echo "</ul>";
            echo "</div>";
            
            // Show all users
            echo "<h3>5. All Users in Database</h3>";
            $all_users = $conn->prepare("SELECT id, name, email, role, password FROM users");
            $all_users->execute();
            $users = $all_users->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f3f4f6;'>";
            echo "<th style='padding: 8px;'>ID</th>";
            echo "<th style='padding: 8px;'>Name</th>";
            echo "<th style='padding: 8px;'>Email</th>";
            echo "<th style='padding: 8px;'>Password</th>";
            echo "<th style='padding: 8px;'>Role</th>";
            echo "</tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td style='padding: 8px;'>" . $user['id'] . "</td>";
                echo "<td style='padding: 8px;'>" . htmlspecialchars($user['name']) . "</td>";
                echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td style='padding: 8px; font-family: monospace;'>" . $user['password'] . "</td>";
                echo "<td style='padding: 8px;'>" . $user['role'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
            echo "<h3>🎉 Ready to Login!</h3>";
            echo "<p><strong>Use these credentials:</strong></p>";
            echo "<p style='font-family: monospace; font-size: 16px;'>";
            echo "<strong>Email:</strong> admin@college.edu<br>";
            echo "<strong>Password:</strong> password";
            echo "</p>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
            echo "<h3>❌ Login Test Failed</h3>";
            echo "<p>Could not find admin user with email 'admin@college.edu' and password 'password'</p>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Database operation error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ MySQL Connection Failed</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h4>Possible Solutions:</h4>";
    echo "<ol>";
    echo "<li><strong>Start XAMPP:</strong> Make sure XAMPP Control Panel is open and MySQL is started (green light)</li>";
    echo "<li><strong>Check Port:</strong> MySQL should be running on port 3306</li>";
    echo "<li><strong>Check Firewall:</strong> Make sure port 3306 is not blocked</li>";
    echo "<li><strong>Restart Services:</strong> Stop and start MySQL in XAMPP</li>";
    echo "</ol>";
    echo "</div>";
}

// Test 4: File Permissions
echo "<h3>6. File System Test</h3>";
$test_file = __DIR__ . '/test_write.txt';
if (file_put_contents($test_file, 'test')) {
    echo "<p>✅ File write permissions OK</p>";
    unlink($test_file);
} else {
    echo "<p>❌ File write permissions issue</p>";
}

// Test 5: Session Test
echo "<h3>7. Session Test</h3>";
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test'])) {
    echo "<p>✅ Sessions are working</p>";
} else {
    echo "<p>❌ Sessions not working</p>";
}

echo "<hr>";
echo "<h3>🚀 Next Steps:</h3>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='login.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🔐 Try Login</a>";
echo "<a href='direct_login_test.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>🧪 Direct Login Test</a>";
echo "</div>";
?>
