<?php
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$error = '';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($auth->login($email, $password)) {
        $role = $_SESSION['user_role'];
        header("Location: dashboard/$role.php");
        exit();
    } else {
        $error = 'Invalid email or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Resource Hub - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <i class="fas fa-graduation-cap text-6xl text-indigo-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">College Resource Hub</h2>
                <p class="mt-2 text-sm text-gray-600">Sign in to access your resources</p>
            </div>
            
            <form class="mt-8 space-y-6 bg-white p-8 rounded-xl shadow-lg" method="POST">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="register.php" class="text-indigo-600 hover:text-indigo-500">
                        Don't have an account? Register here
                    </a>
                </div>
            </form>
            
            <!-- Demo Credentials -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                <h3 class="text-sm font-medium text-yellow-800 mb-2">Demo Credentials:</h3>
                <div class="text-xs text-yellow-700 space-y-1">
                    <div><strong>Admin:</strong> admin@college.edu / password</div>
                    <div><strong>Teacher:</strong> sharma@college.edu / password</div>
                    <div><strong>Student:</strong> rahul@student.edu / password</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
