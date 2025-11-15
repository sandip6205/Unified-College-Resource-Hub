<?php
// Direct Login Test - Bypass Auth Class
session_start();
require_once __DIR__ . '/config/database.php';

echo "<h1>🔍 Direct Login Test</h1>";

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<p><strong>Testing login with:</strong></p>";
    echo "<ul>";
    echo "<li>Email: " . htmlspecialchars($email) . "</li>";
    echo "<li>Password: " . htmlspecialchars($password) . "</li>";
    echo "</ul>";
    
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Direct database query
        $query = "SELECT id, name, email, role, department FROM users WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
            echo "<h3>✅ LOGIN SUCCESSFUL!</h3>";
            echo "<p><strong>User found:</strong></p>";
            echo "<ul>";
            echo "<li><strong>ID:</strong> " . $user['id'] . "</li>";
            echo "<li><strong>Name:</strong> " . htmlspecialchars($user['name']) . "</li>";
            echo "<li><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</li>";
            echo "<li><strong>Role:</strong> " . $user['role'] . "</li>";
            echo "<li><strong>Department:</strong> " . htmlspecialchars($user['department']) . "</li>";
            echo "</ul>";
            
            // Set session manually
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_department'] = $user['department'];
            
            echo "<p><strong>Session set! Redirecting to dashboard...</strong></p>";
            echo "<script>setTimeout(() => { window.location.href = 'dashboard/" . $user['role'] . ".php'; }, 2000);</script>";
            echo "</div>";
            
        } else {
            echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
            echo "<h3>❌ LOGIN FAILED!</h3>";
            echo "<p>No user found with these credentials.</p>";
            
            // Show what users exist
            $all_query = "SELECT email, password, role FROM users";
            $all_stmt = $conn->prepare($all_query);
            $all_stmt->execute();
            $all_users = $all_stmt->fetchAll();
            
            echo "<p><strong>Available users in database:</strong></p>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr style='background: #f3f4f6;'><th style='padding: 8px;'>Email</th><th style='padding: 8px;'>Password</th><th style='padding: 8px;'>Role</th></tr>";
            foreach ($all_users as $u) {
                echo "<tr>";
                echo "<td style='padding: 8px;'>" . htmlspecialchars($u['email']) . "</td>";
                echo "<td style='padding: 8px; font-family: monospace;'>" . $u['password'] . "</td>";
                echo "<td style='padding: 8px;'>" . $u['role'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>❌ Database Error</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Login Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6 text-center">Direct Login Test</h2>
        
        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="admin@college.edu" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" value="password" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Test Direct Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="emergency_admin_fix.php" class="text-red-600 hover:text-red-800 underline">Run Emergency Fix First</a>
        </div>
        
        <div class="mt-4 text-center">
            <a href="login.php" class="text-indigo-600 hover:text-indigo-800 underline">Back to Normal Login</a>
        </div>
    </div>
</body>
</html>
