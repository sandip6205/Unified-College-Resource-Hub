<?php
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// If user is already logged in, redirect to their dashboard
if ($auth->isLoggedIn()) {
    $role = $_SESSION['user_role'];
    header("Location: dashboard/$role.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Resource Hub - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-2xl text-indigo-600 mr-3"></i>
                    <span class="text-xl font-semibold">College Resource Hub</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Login</a>
                    <a href="register.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto py-16 px-4">
        <div class="text-center">
            <h1 class="text-5xl font-extrabold text-gray-900 mb-6">
                Welcome to <span class="text-indigo-600">College Resource Hub</span>
            </h1>
            <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                Your one-stop destination for academic resources. Access notes, syllabus, previous year questions, 
                and stay updated with college circulars - all in one place.
            </p>
            <div class="flex justify-center space-x-4">
                <a href="login.php" class="bg-indigo-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Get Started
                </a>
                <a href="register.php" class="border border-indigo-600 text-indigo-600 px-8 py-3 rounded-lg text-lg font-medium hover:bg-indigo-50 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Everything You Need for Academic Success</h2>
                <p class="text-lg text-gray-600">Designed for students, teachers, and administrators</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Student Features -->
                <div class="bg-blue-50 rounded-xl p-8 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-graduate text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">For Students</h3>
                    <ul class="text-gray-600 space-y-2 text-left">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Download notes and study materials</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Access previous year questions</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Filter by subject and chapter</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Stay updated with circulars</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Get instant notifications</li>
                    </ul>
                </div>

                <!-- Teacher Features -->
                <div class="bg-green-50 rounded-xl p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chalkboard-teacher text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">For Teachers</h3>
                    <ul class="text-gray-600 space-y-2 text-left">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Upload course materials easily</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Organize by subject and chapter</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Track download statistics</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Manage your uploads</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Add descriptions and tags</li>
                    </ul>
                </div>

                <!-- Admin Features -->
                <div class="bg-purple-50 rounded-xl p-8 text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-shield text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">For Admins</h3>
                    <ul class="text-gray-600 space-y-2 text-left">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Approve and manage resources</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Manage subjects and categories</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Post important circulars</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Monitor system usage</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>User management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-lg text-gray-600">Simple steps to get started</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-indigo-600">1</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Register</h3>
                    <p class="text-gray-600">Create your account as Student, Teacher, or Admin</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-indigo-600">2</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Login</h3>
                    <p class="text-gray-600">Access your personalized dashboard</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-indigo-600">3</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Explore</h3>
                    <p class="text-gray-600">Upload or download resources based on your role</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-indigo-600">4</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Succeed</h3>
                    <p class="text-gray-600">Achieve your academic goals with ease</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Demo Credentials Section -->
    <div class="bg-yellow-50 py-12">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Try the Demo</h2>
            <p class="text-gray-600 mb-6">Use these credentials to explore different user roles:</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <h3 class="font-semibold text-gray-900 mb-2">Admin Access</h3>
                    <p class="text-sm text-gray-600 mb-2">Email: admin@college.edu</p>
                    <p class="text-sm text-gray-600">Password: password</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <h3 class="font-semibold text-gray-900 mb-2">Teacher Access</h3>
                    <p class="text-sm text-gray-600 mb-2">Email: sharma@college.edu</p>
                    <p class="text-sm text-gray-600">Password: password</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md">
                    <h3 class="font-semibold text-gray-900 mb-2">Student Access</h3>
                    <p class="text-sm text-gray-600 mb-2">Email: rahul@student.edu</p>
                    <p class="text-sm text-gray-600">Password: password</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-graduation-cap text-2xl text-indigo-400 mr-3"></i>
                <span class="text-xl font-semibold">College Resource Hub</span>
            </div>
            <p class="text-gray-400">Empowering education through technology</p>
        </div>
    </footer>
</body>
</html>
