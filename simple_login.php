<?php
session_start();

// Simple manual login - no classes, just basic PHP
$message = '';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Direct MySQL connection
        $conn = new PDO("mysql:host=localhost;dbname=college_resource_hub", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Simple query
        $stmt = $conn->prepare("SELECT id, name, email, role, department FROM users WHERE email = ? AND password = ?");
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_department'] = $user['department'];
            
            $message = "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
            $message .= "<h3>✅ LOGIN SUCCESSFUL!</h3>";
            $message .= "<p>Welcome, " . htmlspecialchars($user['name']) . "!</p>";
            $message .= "<p>Role: " . ucfirst($user['role']) . "</p>";
            $message .= "<p>Redirecting to dashboard...</p>";
            $message .= "<script>setTimeout(() => { window.location.href = 'dashboard/" . $user['role'] . ".php'; }, 2000);</script>";
            $message .= "</div>";
            
        } else {
            $message = "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
            $message .= "<h3>❌ LOGIN FAILED</h3>";
            $message .= "<p>Invalid email or password.</p>";
            
            // Show what users exist
            $all_stmt = $conn->prepare("SELECT email, password, role FROM users");
            $all_stmt->execute();
            $all_users = $all_stmt->fetchAll();
            
            $message .= "<p><strong>Available users:</strong></p>";
            $message .= "<table border='1' style='border-collapse: collapse;'>";
            $message .= "<tr style='background: #f3f4f6;'><th style='padding: 8px;'>Email</th><th style='padding: 8px;'>Password</th><th style='padding: 8px;'>Role</th></tr>";
            foreach ($all_users as $u) {
                $message .= "<tr>";
                $message .= "<td style='padding: 8px;'>" . htmlspecialchars($u['email']) . "</td>";
                $message .= "<td style='padding: 8px; font-family: monospace;'>" . $u['password'] . "</td>";
                $message .= "<td style='padding: 8px;'>" . $u['role'] . "</td>";
                $message .= "</tr>";
            }
            $message .= "</table>";
            $message .= "</div>";
        }
        
    } catch (Exception $e) {
        $message = "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        $message .= "<h3>❌ DATABASE ERROR</h3>";
        $message .= "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        $message .= "<p><strong>Make sure XAMPP MySQL is running!</strong></p>";
        $message .= "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login - College Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-8">
            <i class="fas fa-graduation-cap text-4xl text-indigo-600 mb-4"></i>
            <h1 class="text-2xl font-bold text-gray-900">Simple Login</h1>
            <p class="text-gray-600">Direct database login test</p>
        </div>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" value="admin@college.edu" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" value="password" required 
                           class="mt-1 block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="button" onclick="togglePassword()" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i id="eyeIcon" class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Login
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <div class="text-sm text-gray-600">
                <strong>Demo Credentials:</strong>
            </div>
            <div class="text-xs text-gray-500 space-y-1">
                <div>Admin: admin@college.edu / password</div>
                <div>Teacher: sharma@college.edu / password</div>
                <div>Student: rahul@student.edu / password</div>
            </div>
        </div>

        <div class="mt-6 text-center space-y-2">
            <a href="diagnose_problem.php" class="text-red-600 hover:text-red-800 text-sm underline">
                🔍 Diagnose Problems
            </a>
            <br>
            <a href="index.php" class="text-indigo-600 hover:text-indigo-800 text-sm underline">
                🏠 Back to Home
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
