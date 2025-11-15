<?php
// Fix password issues for demo
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h1>Fixing Password Issues</h1>";

try {
    // Update all users to have plain text password "password" for demo
    $update_query = "UPDATE users SET password = 'password'";
    $stmt = $conn->prepare($update_query);
    
    if ($stmt->execute()) {
        echo "<p>✅ Updated all user passwords to 'password' for demo purposes</p>";
        
        // Show current users
        $users_query = "SELECT id, name, email, role FROM users";
        $users_stmt = $conn->prepare($users_query);
        $users_stmt->execute();
        $users = $users_stmt->fetchAll();
        
        echo "<h3>Current Users:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Name</th><th style='padding: 8px;'>Email</th><th style='padding: 8px;'>Role</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td style='padding: 8px;'>" . $user['id'] . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 8px;'>" . $user['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p><strong>All users now have password: 'password'</strong></p>";
        echo "<p><a href='login.php'>Go to Login Page</a></p>";
        
    } else {
        echo "<p>❌ Failed to update passwords</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
