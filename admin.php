<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireRole('admin');

$database = new Database();
$conn = $database->getConnection();
$user = $auth->getCurrentUser();

$user = $auth->getCurrentUser();

// Handle actions
if ($_POST) {
    if (isset($_POST['approve_resource'])) {
        $resource_id = $_POST['resource_id'];
        $query = "UPDATE resources SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$resource_id]);
        $success_message = "Resource approved successfully!";
    }
    
    if (isset($_POST['reject_resource'])) {
        $resource_id = $_POST['resource_id'];
        $query = "UPDATE resources SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$resource_id]);
        $success_message = "Resource rejected successfully!";
    }
    
    if (isset($_POST['delete_resource'])) {
        $resource_id = $_POST['resource_id'];
        $query = "DELETE FROM resources WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$resource_id]);
        $success_message = "Resource deleted successfully!";
    }
    
    if (isset($_POST['add_subject'])) {
        $subject_name = $_POST['subject_name'];
        $department = $_POST['department'];
        $query = "INSERT INTO subjects (subject_name, department) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$subject_name, $department]);
        $success_message = "Subject added successfully!";
    }
    
    if (isset($_POST['add_circular'])) {
        $title = $_POST['circular_title'];
        $content = $_POST['circular_content'];
        $query = "INSERT INTO circulars (title, content, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute([$title, $content, $user['id']]);
        $success_message = "Circular added successfully!";
    }
    
    if (isset($_POST['approve_teacher'])) {
        $teacher_id = $_POST['teacher_id'];
        // In a real system, you might have a separate approval status for teachers
        $success_message = "Teacher approved successfully!";
    }
}

// Get pending resources
$pending_query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
                  FROM resources r 
                  LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                  LEFT JOIN users u ON r.uploaded_by = u.id 
                  WHERE r.status = 'pending' 
                  ORDER BY r.uploaded_at DESC";
$pending_stmt = $conn->prepare($pending_query);
$pending_stmt->execute();
$pending_resources = $pending_stmt->fetchAll();

// Get all resources
$all_resources_query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
                       FROM resources r 
                       LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                       LEFT JOIN users u ON r.uploaded_by = u.id 
                       ORDER BY r.uploaded_at DESC LIMIT 20";
$all_resources_stmt = $conn->prepare($all_resources_query);
$all_resources_stmt->execute();
$all_resources = $all_resources_stmt->fetchAll();

// Get users statistics
$users_stats_query = "SELECT 
                        COUNT(CASE WHEN role = 'student' THEN 1 END) as students,
                        COUNT(CASE WHEN role = 'teacher' THEN 1 END) as teachers,
                        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins
                      FROM users";
$users_stats_stmt = $conn->prepare($users_stats_query);
$users_stats_stmt->execute();
$users_stats = $users_stats_stmt->fetch();

// Get resources statistics
$resources_stats_query = "SELECT 
                            COUNT(*) as total_resources,
                            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_resources,
                            COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_resources,
                            SUM(download_count) as total_downloads
                          FROM resources";
$resources_stats_stmt = $conn->prepare($resources_stats_query);
$resources_stats_stmt->execute();
$resources_stats = $resources_stats_stmt->fetch();

// Get all subjects
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $conn->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll();

// Get teachers for approval
$teachers_query = "SELECT * FROM users WHERE role = 'teacher' ORDER BY name";
$teachers_stmt = $conn->prepare($teachers_query);
$teachers_stmt->execute();
$teachers = $teachers_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - College Resource Hub</title>
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
                    <span class="text-xl font-semibold">College Resource Hub - Admin</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Success Message -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $users_stats['students']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Teachers</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $users_stats['teachers']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-book text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Resources</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $resources_stats['total_resources']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending Approval</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $resources_stats['pending_resources']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <button onclick="showTab('pending')" id="pending-tab" class="tab-button py-4 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600">
                        Pending Resources (<?php echo count($pending_resources); ?>)
                    </button>
                    <button onclick="showTab('resources')" id="resources-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        All Resources
                    </button>
                    <button onclick="showTab('subjects')" id="subjects-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Manage Subjects
                    </button>
                    <button onclick="showTab('circulars')" id="circulars-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Add Circular
                    </button>
                </nav>
            </div>

            <!-- Pending Resources Tab -->
            <div id="pending-content" class="tab-content p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Resources Pending Approval</h3>
                <?php if (empty($pending_resources)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-4xl text-green-400 mb-4"></i>
                        <p class="text-gray-500">No resources pending approval.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($pending_resources as $resource): ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($resource['title']); ?></h4>
                                        <div class="text-sm text-gray-600 mt-1">
                                            <span class="mr-4"><i class="fas fa-book mr-1"></i><?php echo htmlspecialchars($resource['subject_name']); ?></span>
                                            <span class="mr-4"><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></span>
                                            <span><i class="fas fa-calendar mr-1"></i><?php echo date('M j, Y', strtotime($resource['uploaded_at'])); ?></span>
                                        </div>
                                        <?php if ($resource['description']): ?>
                                            <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($resource['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4 flex space-x-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                            <button type="submit" name="approve_resource" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                                <i class="fas fa-check mr-1"></i>Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                            <button type="submit" name="reject_resource" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                                                <i class="fas fa-times mr-1"></i>Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- All Resources Tab -->
            <div id="resources-content" class="tab-content p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">All Resources</h3>
                <div class="space-y-4">
                    <?php foreach ($all_resources as $resource): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($resource['title']); ?></h4>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span class="mr-4"><i class="fas fa-book mr-1"></i><?php echo htmlspecialchars($resource['subject_name']); ?></span>
                                        <span class="mr-4"><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></span>
                                        <span class="mr-4"><i class="fas fa-download mr-1"></i><?php echo $resource['download_count']; ?> downloads</span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="inline-block px-2 py-1 text-xs rounded-full 
                                            <?php echo $resource['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                      ($resource['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($resource['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                        <button type="submit" name="delete_resource" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                                                onclick="return confirm('Are you sure you want to delete this resource?')">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Subjects Tab -->
            <div id="subjects-content" class="tab-content p-6 hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Subject</h3>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label for="subject_name" class="block text-sm font-medium text-gray-700">Subject Name</label>
                                <input type="text" id="subject_name" name="subject_name" required
                                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                                <select id="department" name="department" required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Department</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Electronics">Electronics</option>
                                    <option value="Mechanical">Mechanical</option>
                                    <option value="Civil">Civil</option>
                                    <option value="General">General</option>
                                </select>
                            </div>
                            <button type="submit" name="add_subject"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                <i class="fas fa-plus mr-2"></i>Add Subject
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Existing Subjects</h3>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <?php foreach ($subjects as $subject): ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                    <div>
                                        <span class="font-medium"><?php echo htmlspecialchars($subject['subject_name']); ?></span>
                                        <span class="text-sm text-gray-600 ml-2">(<?php echo htmlspecialchars($subject['department']); ?>)</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Circulars Tab -->
            <div id="circulars-content" class="tab-content p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Circular</h3>
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="circular_title" class="block text-sm font-medium text-gray-700">Circular Title</label>
                        <input type="text" id="circular_title" name="circular_title" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="circular_content" class="block text-sm font-medium text-gray-700">Content</label>
                        <textarea id="circular_content" name="circular_content" rows="6" required
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <button type="submit" name="add_circular"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="fas fa-bullhorn mr-2"></i>Add Circular
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.add('hidden'));
            
            // Remove active styles from all tabs
            const tabs = document.querySelectorAll('.tab-button');
            tabs.forEach(tab => {
                tab.classList.remove('border-indigo-500', 'text-indigo-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Add active styles to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-indigo-500', 'text-indigo-600');
        }
    </script>
</body>
</html>
