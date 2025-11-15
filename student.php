<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireRole('student');

$database = new Database();
$conn = $database->getConnection();

$user = $auth->getCurrentUser();

// Get enhanced search and filter parameters
$search = $_GET['search'] ?? '';
$subject_filter = $_GET['subject'] ?? '';
$type_filter = $_GET['type'] ?? '';
$semester_filter = $_GET['semester'] ?? '';
$teacher_filter = $_GET['teacher'] ?? '';
$year_filter = $_GET['year'] ?? '';
$ocr_search = $_GET['ocr_search'] ?? '';

// Build query with filters
$query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
          FROM resources r 
          LEFT JOIN subjects s ON r.subject_id = s.subject_id 
          LEFT JOIN users u ON r.uploaded_by = u.id 
          WHERE r.status = 'approved'";

$params = [];

if ($search) {
    $query .= " AND (r.title LIKE ? OR r.description LIKE ? OR r.tags LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($subject_filter) {
    $query .= " AND r.subject_id = ?";
    $params[] = $subject_filter;
}

if ($type_filter) {
    $query .= " AND r.resource_type = ?";
    $params[] = $type_filter;
}

if ($semester_filter) {
    $query .= " AND r.semester = ?";
    $params[] = $semester_filter;
}

if ($teacher_filter) {
    $query .= " AND r.uploaded_by = ?";
    $params[] = $teacher_filter;
}

if ($year_filter) {
    $query .= " AND YEAR(r.uploaded_at) = ?";
    $params[] = $year_filter;
}

if ($ocr_search) {
    $query .= " AND (r.ocr_content LIKE ? OR r.title LIKE ? OR r.description LIKE ?)";
    $ocr_param = "%$ocr_search%";
    $params[] = $ocr_param;
    $params[] = $ocr_param;
    $params[] = $ocr_param;
}

$query .= " ORDER BY r.uploaded_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll();

// Get subjects for filter dropdown
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $conn->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll();

// Get recent circulars
$circulars_query = "SELECT c.*, u.name as created_by_name FROM circulars c 
                   LEFT JOIN users u ON c.created_by = u.id 
                   ORDER BY c.date DESC LIMIT 5";
$circulars_stmt = $conn->prepare($circulars_query);
$circulars_stmt->execute();
$circulars = $circulars_stmt->fetchAll();

// Create notifications table if it doesn't exist
try {
    $create_notifications = "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NULL,
        user_role VARCHAR(20) NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
        seen BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_user_role (user_role),
        INDEX idx_seen (seen)
    )";
    $conn->exec($create_notifications);
} catch (Exception $e) {
    // Table might already exist
}

// Get notifications count
$notifications_query = "SELECT COUNT(*) as count FROM notifications 
                       WHERE (user_id = ? OR user_role = 'student') AND seen = FALSE";
$notifications_stmt = $conn->prepare($notifications_query);
$notifications_stmt->execute([$user['id']]);
$notifications_count = $notifications_stmt->fetch()['count'];

// Get recent notifications
$recent_notifications_query = "SELECT n.* FROM notifications n 
                              WHERE (n.user_id = ? OR n.user_role = 'student') 
                              ORDER BY n.timestamp DESC 
                              LIMIT 10";
$recent_notifications_stmt = $conn->prepare($recent_notifications_query);
$recent_notifications_stmt->execute([$user['id']]);
$recent_notifications = $recent_notifications_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - College Resource Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php include_once '../includes/chatbot.php'; ?>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-2xl text-indigo-600 mr-3"></i>
                    <span class="text-xl font-semibold">College Resource Hub</span>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Notification Bell -->
                    <div class="relative">
                        <button onclick="toggleNotifications()" class="relative p-2 text-gray-600 hover:text-gray-800 focus:outline-none">
                            <i class="fas fa-bell text-lg"></i>
                            <?php if ($notifications_count > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                                    <?php echo $notifications_count; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                                    <?php if ($notifications_count > 0): ?>
                                        <button onclick="markAllAsRead()" class="text-sm text-indigo-600 hover:text-indigo-800">
                                            Mark all as read
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="max-h-96 overflow-y-auto">
                                <?php if (empty($recent_notifications)): ?>
                                    <div class="p-4 text-center text-gray-500">
                                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                                        <p>No notifications yet</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_notifications as $notification): ?>
                                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 <?php echo !$notification['seen'] ? 'bg-blue-50' : ''; ?>">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 mr-3">
                                                    <?php
                                                    $icon_colors = [
                                                        'info' => 'text-blue-500',
                                                        'success' => 'text-green-500',
                                                        'warning' => 'text-yellow-500',
                                                        'error' => 'text-red-500'
                                                    ];
                                                    $icon_class = $icon_colors[$notification['type']] ?? 'text-blue-500';
                                                    ?>
                                                    <i class="fas fa-info-circle <?php echo $icon_class; ?>"></i>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($notification['title']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-600 mt-1">
                                                        <?php echo htmlspecialchars($notification['message']); ?>
                                                    </p>
                                                    <p class="text-xs text-gray-400 mt-2">
                                                        <?php echo date('M j, Y g:i A', strtotime($notification['timestamp'])); ?>
                                                    </p>
                                                </div>
                                                <?php if (!$notification['seen']): ?>
                                                    <div class="flex-shrink-0 ml-2">
                                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($recent_notifications)): ?>
                                <div class="p-3 border-t border-gray-200 text-center">
                                    <a href="notifications.php" class="text-sm text-indigo-600 hover:text-indigo-800">
                                        View all notifications
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Smart Search + Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">
                <i class="fas fa-search mr-2 text-indigo-600"></i>Smart Search + Filters
            </h2>
            <form method="GET" class="space-y-4">
                <!-- Primary Search Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keyword Search</label>
                        <input type="text" name="search" placeholder="Search in title, description, tags..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OCR Content Search</label>
                        <input type="text" name="ocr_search" placeholder="Search inside PDF content..." 
                               value="<?php echo htmlspecialchars($ocr_search); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <!-- Advanced Filters Row -->
                <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <select name="subject" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Subjects</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['subject_id']; ?>" 
                                        <?php echo $subject_filter == $subject['subject_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select name="semester" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Semesters</option>
                            <?php for($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $semester_filter == $i ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">File Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Types</option>
                            <option value="notes" <?php echo $type_filter == 'notes' ? 'selected' : ''; ?>>📄 Notes</option>
                            <option value="assignment" <?php echo $type_filter == 'assignment' ? 'selected' : ''; ?>>📝 Assignment</option>
                            <option value="syllabus" <?php echo $type_filter == 'syllabus' ? 'selected' : ''; ?>>📋 Syllabus</option>
                            <option value="pyq" <?php echo $type_filter == 'pyq' ? 'selected' : ''; ?>>📊 Previous Year</option>
                            <option value="video" <?php echo $type_filter == 'video' ? 'selected' : ''; ?>>🎥 Video</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teacher</label>
                        <select name="teacher" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Teachers</option>
                            <?php 
                            $teachers_query = "SELECT DISTINCT u.id, u.name FROM users u 
                                             JOIN resources r ON u.id = r.uploaded_by 
                                             WHERE u.role = 'teacher' ORDER BY u.name";
                            $teachers_stmt = $conn->prepare($teachers_query);
                            $teachers_stmt->execute();
                            $teachers = $teachers_stmt->fetchAll();
                            foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>" 
                                        <?php echo $teacher_filter == $teacher['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($teacher['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                        <select name="year" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Years</option>
                            <?php 
                            $current_year = date('Y');
                            for($year = $current_year; $year >= $current_year - 5; $year--): ?>
                                <option value="<?php echo $year; ?>" <?php echo $year_filter == $year ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                    </div>
                </div>

                <!-- Quick Filter Tags -->
                <div class="flex flex-wrap gap-2 pt-2">
                    <span class="text-sm text-gray-600">Quick Filters:</span>
                    <a href="?type=notes" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm hover:bg-blue-200">📄 Notes</a>
                    <a href="?type=assignment" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm hover:bg-green-200">📝 Assignments</a>
                    <a href="?type=pyq" class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm hover:bg-purple-200">📊 Previous Year</a>
                    <a href="?type=video" class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm hover:bg-red-200">🎥 Videos</a>
                    <a href="?" class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm hover:bg-gray-200">🔄 Clear All</a>
                </div>

                <!-- Active Filters Display -->
                <?php if ($search || $subject_filter || $type_filter || $semester_filter || $teacher_filter || $year_filter || $ocr_search): ?>
                <div class="border-t pt-3 mt-3">
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-sm text-gray-600">Active Filters:</span>
                        <?php if ($search): ?>
                            <span class="px-2 py-1 bg-indigo-100 text-indigo-800 rounded text-sm">
                                Search: "<?php echo htmlspecialchars($search); ?>"
                            </span>
                        <?php endif; ?>
                        <?php if ($ocr_search): ?>
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm">
                                OCR: "<?php echo htmlspecialchars($ocr_search); ?>"
                            </span>
                        <?php endif; ?>
                        <?php if ($subject_filter): ?>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                Subject: <?php 
                                $subject_name = '';
                                foreach($subjects as $s) {
                                    if($s['subject_id'] == $subject_filter) {
                                        $subject_name = $s['subject_name'];
                                        break;
                                    }
                                }
                                echo htmlspecialchars($subject_name);
                                ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($type_filter): ?>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">
                                Type: <?php echo ucfirst($type_filter); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($semester_filter): ?>
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-sm">
                                Semester: <?php echo $semester_filter; ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($year_filter): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-sm">
                                Year: <?php echo $year_filter; ?>
                            </span>
                        <?php endif; ?>
                        <a href="?" class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-sm hover:bg-gray-200">
                            <i class="fas fa-times mr-1"></i>Clear All
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content - Resources -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i class="fas fa-book mr-2 text-indigo-600"></i>Available Resources
                        </h2>
                    </div>
                    <div class="p-6">
                        <?php if (empty($resources)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-500">No resources found matching your criteria.</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($resources as $resource): ?>
                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">
                                                    <?php echo htmlspecialchars($resource['title']); ?>
                                                </h3>
                                                <div class="flex items-center space-x-4 text-sm text-gray-600 mb-2">
                                                    <span><i class="fas fa-book mr-1"></i><?php echo htmlspecialchars($resource['subject_name']); ?></span>
                                                    <span><i class="fas fa-file mr-1"></i><?php echo strtoupper($resource['file_type']); ?></span>
                                                    <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></span>
                                                    <span><i class="fas fa-calendar mr-1"></i><?php echo date('M j, Y', strtotime($resource['uploaded_at'])); ?></span>
                                                </div>
                                                <?php if ($resource['description']): ?>
                                                    <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($resource['description']); ?></p>
                                                <?php endif; ?>
                                                <?php if ($resource['chapter']): ?>
                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                        Chapter: <?php echo htmlspecialchars($resource['chapter']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4 flex space-x-2">
                                                <a href="ai_notes_summary.php?id=<?php echo $resource['id']; ?>" 
                                                   class="inline-flex items-center px-3 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700">
                                                    <i class="fas fa-robot mr-2"></i>AI Summary
                                                </a>
                                                <a href="../download.php?id=<?php echo $resource['id']; ?>" 
                                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                                                    <i class="fas fa-download mr-2"></i>Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Recent Circulars -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-bullhorn mr-2 text-yellow-600"></i>Recent Circulars
                        </h3>
                    </div>
                    <div class="p-6">
                        <?php if (empty($circulars)): ?>
                            <p class="text-gray-500 text-sm">No circulars available.</p>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($circulars as $circular): ?>
                                    <div class="border-l-4 border-yellow-400 pl-4">
                                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($circular['title']); ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo date('M j, Y', strtotime($circular['date'])); ?></p>
                                        <?php if ($circular['pdf_url']): ?>
                                            <a href="<?php echo htmlspecialchars($circular['pdf_url']); ?>" 
                                               class="text-indigo-600 hover:text-indigo-800 text-sm">
                                                <i class="fas fa-external-link-alt mr-1"></i>View PDF
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-chart-bar mr-2 text-green-600"></i>Quick Stats
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Resources:</span>
                            <span class="font-semibold"><?php echo count($resources); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Your Department:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($user['department']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Notification functionality
        function toggleNotifications() {
            const dropdown = document.getElementById('notificationsDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close notifications when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationsDropdown');
            const button = event.target.closest('button[onclick="toggleNotifications()"]');
            
            if (!button && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Mark all notifications as read
        function markAllAsRead() {
            fetch('mark_notifications_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error marking notifications as read');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Auto-refresh notification count every 30 seconds
        setInterval(function() {
            fetch('get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.animate-pulse');
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                    } else {
                        // Create badge if it doesn't exist
                        const bellButton = document.querySelector('button[onclick="toggleNotifications()"]');
                        const newBadge = document.createElement('span');
                        newBadge.className = 'absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse';
                        newBadge.textContent = data.count;
                        bellButton.appendChild(newBadge);
                    }
                } else {
                    if (badge) {
                        badge.remove();
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching notification count:', error);
            });
        }, 30000);
    </script>
</body>
</html>
