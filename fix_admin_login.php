<?php
// Fix Admin Login Issue
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h1>🔧 Fixing Admin Login Issue</h1>";

try {
    // Check current admin user
    $check_query = "SELECT id, name, email, role, password FROM users WHERE email = 'admin@college.edu'";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute();
    $admin = $check_stmt->fetch();
    
    if ($admin) {
        echo "<p>✅ Admin user found: " . htmlspecialchars($admin['name']) . " (" . htmlspecialchars($admin['email']) . ")</p>";
        echo "<p>Current password hash: " . substr($admin['password'], 0, 20) . "...</p>";
        
        // Update admin password to plain text "password"
        $update_query = "UPDATE users SET password = 'password' WHERE email = 'admin@college.edu'";
        $update_stmt = $conn->prepare($update_query);
        
        if ($update_stmt->execute()) {
            echo "<p>✅ Admin password updated to plain text 'password'</p>";
        } else {
            echo "<p>❌ Failed to update admin password</p>";
        }
    } else {
        echo "<p>❌ Admin user not found. Creating new admin user...</p>";
        
        // Create new admin user
        $insert_query = "INSERT INTO users (name, email, role, department, password) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        
        if ($insert_stmt->execute(['Admin User', 'admin@college.edu', 'admin', 'Administration', 'password'])) {
            echo "<p>✅ New admin user created successfully!</p>";
        } else {
            echo "<p>❌ Failed to create admin user</p>";
        }
    }
    
    // Also fix teacher and student passwords
    $fix_all_query = "UPDATE users SET password = 'password'";
    $fix_all_stmt = $conn->prepare($fix_all_query);
    
    if ($fix_all_stmt->execute()) {
        echo "<p>✅ All user passwords updated to 'password' for demo purposes</p>";
    }
    
    // Verify admin login
    $verify_query = "SELECT id, name, email, role FROM users WHERE email = 'admin@college.edu' AND password = 'password'";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->execute();
    $verified_admin = $verify_stmt->fetch();
    
    if ($verified_admin) {
        echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>✅ Admin Login Fixed Successfully!</h3>";
        echo "<p><strong>Admin Credentials:</strong></p>";
        echo "<ul style='margin-left: 20px;'>";
        echo "<li><strong>Email:</strong> admin@college.edu</li>";
        echo "<li><strong>Password:</strong> password</li>";
        echo "<li><strong>Role:</strong> " . $verified_admin['role'] . "</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h3>🚀 Test Admin Login:</h3>";
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='login.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🔐 Login Page</a>";
        echo "<a href='dashboard/admin.php' style='background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>⚙️ Admin Dashboard</a>";
        echo "</div>";
        
        echo "<h3>📋 All Demo Credentials:</h3>";
        echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<ul style='margin-left: 20px; line-height: 1.8;'>";
        echo "<li><strong>Admin:</strong> admin@college.edu / password</li>";
        echo "<li><strong>Teacher:</strong> sharma@college.edu / password</li>";
        echo "<li><strong>Student:</strong> rahul@student.edu / password</li>";
        echo "</ul>";
        echo "</div>";
        
    } else {
        echo "<p>❌ Admin login verification failed. Please check the database.</p>";
    }
    
    // Show all users for debugging
    echo "<h3>👥 All Users in Database:</h3>";
    $all_users_query = "SELECT id, name, email, role, department FROM users ORDER BY role, name";
    $all_users_stmt = $conn->prepare($all_users_query);
    $all_users_stmt->execute();
    $all_users = $all_users_stmt->fetchAll();
    
    if ($all_users) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #f3f4f6;'>";
        echo "<th style='padding: 8px; text-align: left;'>ID</th>";
        echo "<th style='padding: 8px; text-align: left;'>Name</th>";
        echo "<th style='padding: 8px; text-align: left;'>Email</th>";
        echo "<th style='padding: 8px; text-align: left;'>Role</th>";
        echo "<th style='padding: 8px; text-align: left;'>Department</th>";
        echo "</tr>";
        
        foreach ($all_users as $user) {
            $role_color = '';
            switch ($user['role']) {
                case 'admin': $role_color = 'background: #fecaca; color: #991b1b;'; break;
                case 'teacher': $role_color = 'background: #ddd6fe; color: #5b21b6;'; break;
                case 'student': $role_color = 'background: #bfdbfe; color: #1e40af;'; break;
            }
            
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $user['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 8px; $role_color'>" . ucfirst($user['role']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['department']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error fixing admin login: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></p>";
}
?>
