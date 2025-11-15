<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user = $auth->getCurrentUser();

// Get enhanced data
$search = $_GET['search'] ?? '';
$subject_filter = $_GET['subject'] ?? '';
$type_filter = $_GET['type'] ?? '';
$semester_filter = $_GET['semester'] ?? '';

// Build enhanced query
$query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name,
          (SELECT COUNT(*) FROM bookmarks b WHERE b.resource_id = r.id AND b.user_id = ?) as is_bookmarked,
          (SELECT rating FROM ratings rt WHERE rt.resource_id = r.id AND rt.user_id = ?) as user_rating
          FROM resources r 
          LEFT JOIN subjects s ON r.subject_id = s.subject_id 
          LEFT JOIN users u ON r.uploaded_by = u.id 
          WHERE r.status = 'approved'";

$params = [$user['id'], $user['id']];

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

$query .= " ORDER BY r.uploaded_at DESC LIMIT 20";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll();

// Get subjects
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $conn->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll();

// Get bookmarks
$bookmarks_query = "SELECT r.*, s.subject_name FROM bookmarks b 
                   JOIN resources r ON b.resource_id = r.id 
                   JOIN subjects s ON r.subject_id = s.subject_id 
                   WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 10";
$bookmarks_stmt = $conn->prepare($bookmarks_query);
$bookmarks_stmt->execute([$user['id']]);
$bookmarks = $bookmarks_stmt->fetchAll();

// Get upcoming exams
$exams_query = "SELECT e.*, s.subject_name FROM exams e 
               JOIN subjects s ON e.subject_id = s.subject_id 
               WHERE e.exam_date >= CURDATE() ORDER BY e.exam_date ASC LIMIT 5";
$exams_stmt = $conn->prepare($exams_query);
$exams_stmt->execute();
$upcoming_exams = $exams_stmt->fetchAll();

// Get recent activity
$activity_query = "SELECT ua.*, r.title as resource_title FROM user_activities ua 
                  LEFT JOIN resources r ON ua.resource_id = r.id 
                  WHERE ua.user_id = ? ORDER BY ua.created_at DESC LIMIT 10";
$activity_stmt = $conn->prepare($activity_query);
$activity_stmt->execute([$user['id']]);
$recent_activity = $activity_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php include_once '../includes/chatbot.php'; ?>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-2xl text-indigo-600 mr-3"></i>
                    <span class="text-xl font-semibold dark:text-white">Enhanced Student Hub</span>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="toggleDarkMode()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                    <span class="text-gray-600 dark:text-gray-300">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-bookmark text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Bookmarks</p>
                        <p class="text-2xl font-bold dark:text-white"><?php echo count($bookmarks); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Upcoming Exams</p>
                        <p class="text-2xl font-bold dark:text-white"><?php echo count($upcoming_exams); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-download text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Downloads</p>
                        <p class="text-2xl font-bold dark:text-white"><?php echo count($recent_activity); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-star text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Semester</p>
                        <p class="text-2xl font-bold dark:text-white"><?php echo $user['semester'] ?? '1'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Search -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 dark:text-white">
                <i class="fas fa-search mr-2"></i>Smart Search & Filters
            </h2>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="search" placeholder="Search resources..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                
                <select name="subject" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['subject_id']; ?>" 
                                <?php echo $subject_filter == $subject['subject_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="type" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Types</option>
                    <option value="notes" <?php echo $type_filter == 'notes' ? 'selected' : ''; ?>>Notes</option>
                    <option value="assignment" <?php echo $type_filter == 'assignment' ? 'selected' : ''; ?>>Assignments</option>
                    <option value="syllabus" <?php echo $type_filter == 'syllabus' ? 'selected' : ''; ?>>Syllabus</option>
                    <option value="pyq" <?php echo $type_filter == 'pyq' ? 'selected' : ''; ?>>Previous Year</option>
                    <option value="video" <?php echo $type_filter == 'video' ? 'selected' : ''; ?>>Videos</option>
                </select>
                
                <select name="semester" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Semesters</option>
                    <?php for($i = 1; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $semester_filter == $i ? 'selected' : ''; ?>>
                            Semester <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Resources List -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 dark:text-white">
                        <i class="fas fa-books mr-2"></i>Available Resources
                    </h2>
                    
                    <?php if (empty($resources)): ?>
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-folder-open text-4xl mb-4"></i>
                            <p>No resources found. Try adjusting your search criteria.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($resources as $resource): ?>
                                <div class="border dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center mb-2">
                                                <h3 class="font-semibold text-lg dark:text-white"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                                <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                                    <?php echo ucfirst($resource['resource_type'] ?? 'notes'); ?>
                                                </span>
                                                <?php if ($resource['is_bookmarked']): ?>
                                                    <i class="fas fa-bookmark text-yellow-500 ml-2" title="Bookmarked"></i>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="text-gray-600 dark:text-gray-300 mb-2"><?php echo htmlspecialchars($resource['description']); ?></p>
                                            
                                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                                                <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></span>
                                                <span><i class="fas fa-book mr-1"></i><?php echo htmlspecialchars($resource['subject_name']); ?></span>
                                                <span><i class="fas fa-download mr-1"></i><?php echo $resource['download_count']; ?></span>
                                                <span><i class="fas fa-star mr-1"></i><?php echo number_format($resource['rating_avg'], 1); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-col space-y-2 ml-4">
                                            <a href="../download.php?id=<?php echo $resource['id']; ?>" 
                                               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-center">
                                                <i class="fas fa-download mr-1"></i>Download
                                            </a>
                                            
                                            <button onclick="toggleBookmark(<?php echo $resource['id']; ?>)" 
                                                    class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                                                <i class="fas fa-bookmark mr-1"></i>
                                                <?php echo $resource['is_bookmarked'] ? 'Remove' : 'Bookmark'; ?>
                                            </button>
                                            
                                            <button onclick="openRatingModal(<?php echo $resource['id']; ?>)" 
                                                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                                                <i class="fas fa-star mr-1"></i>Rate
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Bookmarks -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 dark:text-white">
                        <i class="fas fa-bookmark mr-2"></i>My Bookmarks
                    </h3>
                    <?php if (empty($bookmarks)): ?>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No bookmarks yet</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach (array_slice($bookmarks, 0, 5) as $bookmark): ?>
                                <div class="border-l-4 border-yellow-500 pl-3 py-2">
                                    <p class="font-medium text-sm dark:text-white"><?php echo htmlspecialchars($bookmark['title']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($bookmark['subject_name']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Upcoming Exams -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 dark:text-white">
                        <i class="fas fa-calendar-alt mr-2"></i>Upcoming Exams
                    </h3>
                    <?php if (empty($upcoming_exams)): ?>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">No upcoming exams</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($upcoming_exams as $exam): ?>
                                <div class="border-l-4 border-red-500 pl-3 py-2">
                                    <p class="font-medium text-sm dark:text-white"><?php echo htmlspecialchars($exam['exam_name']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <?php echo htmlspecialchars($exam['subject_name']); ?> - 
                                        <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 dark:text-white">
                        <i class="fas fa-bolt mr-2"></i>Quick Actions
                    </h3>
                    <div class="space-y-2">
                        <a href="forum.php" class="block w-full bg-indigo-600 text-white text-center py-2 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-comments mr-2"></i>Discussion Forum
                        </a>
                        <a href="attendance.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-qrcode mr-2"></i>Mark Attendance
                        </a>
                        <a href="timetable.php" class="block w-full bg-purple-600 text-white text-center py-2 rounded-lg hover:bg-purple-700">
                            <i class="fas fa-calendar mr-2"></i>My Timetable
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }

        // Load dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        // Bookmark toggle
        async function toggleBookmark(resourceId) {
            try {
                const response = await fetch('../api/bookmark.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ resource_id: resourceId })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                alert('Error toggling bookmark');
            }
        }

        // Rating modal
        function openRatingModal(resourceId) {
            // Implementation for rating modal
            const rating = prompt('Rate this resource (1-5 stars):');
            if (rating && rating >= 1 && rating <= 5) {
                submitRating(resourceId, rating);
            }
        }

        async function submitRating(resourceId, rating) {
            try {
                const response = await fetch('../api/rating.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ resource_id: resourceId, rating: rating })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                alert('Error submitting rating');
            }
        }
    </script>
</body>
</html>
