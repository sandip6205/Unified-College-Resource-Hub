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
    <?php include_once 'includes/chatbot.php'; ?>
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
                    <a href="simple_login.php" class="text-indigo-600 hover:text-indigo-800 font-medium">Quick Login</a>
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
                Your one-stop destination for academic resources with AI-powered features. Access notes, syllabus, previous year questions, 
                smart search, AI summaries, and stay updated with college circulars - all in one place.
            </p>
            <div class="flex justify-center space-x-4 mb-8">
                <a href="simple_login.php" class="bg-indigo-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-rocket mr-2"></i>Quick Start
                </a>
                <a href="login.php" class="bg-green-600 text-white px-8 py-3 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
                <a href="register.php" class="border border-indigo-600 text-indigo-600 px-8 py-3 rounded-lg text-lg font-medium hover:bg-indigo-50 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Register
                </a>
            </div>
            
            <!-- Quick Access Features -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
                <a href="setup_enhanced_features.php" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                    <i class="fas fa-cogs text-2xl text-blue-600 mb-2"></i>
                    <p class="text-sm font-medium text-gray-800">Setup System</p>
                </a>
                <a href="create_ai_table.php" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                    <i class="fas fa-robot text-2xl text-purple-600 mb-2"></i>
                    <p class="text-sm font-medium text-gray-800">AI Features</p>
                </a>
                <a href="test_admin_dashboard.php" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                    <i class="fas fa-user-shield text-2xl text-red-600 mb-2"></i>
                    <p class="text-sm font-medium text-gray-800">Admin Test</p>
                </a>
                <a href="diagnose_problem.php" class="bg-white p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow text-center">
                    <i class="fas fa-stethoscope text-2xl text-green-600 mb-2"></i>
                    <p class="text-sm font-medium text-gray-800">Diagnostics</p>
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
                        <li><i class="fas fa-check text-green-500 mr-2"></i>🤖 AI-powered notes summary</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>🔍 Smart search with OCR</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>📝 Interactive MCQs</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>📚 Enhanced filtering system</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>⭐ Bookmarks & favorites</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>📊 Smart attendance system</li>
                    </ul>
                </div>

                <!-- Teacher Features -->
                <div class="bg-green-50 rounded-xl p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chalkboard-teacher text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">For Teachers</h3>
                    <ul class="text-gray-600 space-y-2 text-left">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>📄 Enhanced upload system</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>📋 Multiple resource types</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>🤖 Auto AI content generation</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>📊 Advanced analytics</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>✅ Attendance management</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>🏷️ Smart tagging system</li>
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

    <!-- New Features Showcase -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">🚀 Latest Enhanced Features</h2>
                <p class="text-lg text-gray-600">Cutting-edge technology for modern education</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-robot text-xl text-purple-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">AI Notes Summary</h3>
                    <p class="text-gray-600 text-sm mb-4">Automatic PDF analysis with summaries, questions, and MCQs</p>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded">AI Powered</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">Interactive</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-search text-xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Smart Search & Filters</h3>
                    <p class="text-gray-600 text-sm mb-4">Advanced filtering by subject, semester, teacher, year, and OCR content</p>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">OCR Search</span>
                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Multi-Filter</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-upload text-xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Enhanced Upload System</h3>
                    <p class="text-gray-600 text-sm mb-4">Organized uploads for Notes, Syllabus, Previous Papers, Assignments</p>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded">Multi-Type</span>
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs rounded">Organized</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-qrcode text-xl text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Smart Attendance</h3>
                    <p class="text-gray-600 text-sm mb-4">QR code and geo-location based attendance marking system</p>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded">QR Code</span>
                        <span class="px-2 py-1 bg-teal-100 text-teal-800 text-xs rounded">Geo-Location</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-star text-xl text-yellow-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Bookmarks & Ratings</h3>
                    <p class="text-gray-600 text-sm mb-4">Save favorites and rate resources with comments</p>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 bg-pink-100 text-pink-800 text-xs rounded">Favorites</span>
                        <span class="px-2 py-1 bg-cyan-100 text-cyan-800 text-xs rounded">Reviews</span>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-chart-line text-xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Advanced Analytics</h3>
                    <p class="text-gray-600 text-sm mb-4">Comprehensive dashboards with usage statistics and insights</p>
                    <div class="flex space-x-2">
                        <span class="px-2 py-1 bg-violet-100 text-violet-800 text-xs rounded">Analytics</span>
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs rounded">Insights</span>
                    </div>
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
