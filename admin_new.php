<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireRole('admin');

$database = new Database();
$conn = $database->getConnection();
$user = $auth->getCurrentUser();

// Handle form submissions
$success_message = '';
$error_message = '';

// Handle resource approval/rejection
if ($_POST && isset($_POST['action']) && isset($_POST['resource_id'])) {
    $resource_id = $_POST['resource_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $update_query = "UPDATE resources SET status = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        
        if ($update_stmt->execute([$status, $resource_id])) {
            $success_message = "Resource " . $action . "d successfully!";
        } else {
            $error_message = "Failed to " . $action . " resource.";
        }
    }
}

// Handle circular upload
if ($_POST && isset($_POST['add_circular'])) {
    $title = $_POST['circular_title'] ?? '';
    $content = $_POST['circular_content'] ?? '';
    
    if ($title && $content) {
        $circular_query = "INSERT INTO circulars (title, content, uploaded_by) VALUES (?, ?, ?)";
        $circular_stmt = $conn->prepare($circular_query);
        
        if ($circular_stmt->execute([$title, $content, $user['id']])) {
            $success_message = "Circular added successfully!";
        } else {
            $error_message = "Failed to add circular.";
        }
    } else {
        $error_message = "Please fill in all circular fields.";
    }
}

// Handle subject addition
if ($_POST && isset($_POST['add_subject'])) {
    $subject_name = $_POST['subject_name'] ?? '';
    $department = $_POST['department'] ?? '';
    
    if ($subject_name && $department) {
        $subject_query = "INSERT INTO subjects (subject_name, department) VALUES (?, ?)";
        $subject_stmt = $conn->prepare($subject_query);
        
        if ($subject_stmt->execute([$subject_name, $department])) {
            $success_message = "Subject added successfully!";
        } else {
            $error_message = "Failed to add subject.";
        }
    } else {
        $error_message = "Please fill in all subject fields.";
    }
}

// Get statistics
$stats = [];

// Total users
$users_query = "SELECT COUNT(*) as total, role FROM users GROUP BY role";
$users_stmt = $conn->prepare($users_query);
$users_stmt->execute();
$user_stats = $users_stmt->fetchAll();

foreach ($user_stats as $stat) {
    $stats[$stat['role']] = $stat['total'];
}

// Total resources
$resources_query = "SELECT COUNT(*) as total FROM resources";
$resources_stmt = $conn->prepare($resources_query);
$resources_stmt->execute();
$stats['total_resources'] = $resources_stmt->fetch()['total'];

// Pending resources
$pending_query = "SELECT COUNT(*) as total FROM resources WHERE status = 'pending'";
$pending_stmt = $conn->prepare($pending_query);
$pending_stmt->execute();
$stats['pending_resources'] = $pending_stmt->fetch()['total'];

// Get pending resources
$pending_resources_query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
                          FROM resources r 
                          LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                          LEFT JOIN users u ON r.uploaded_by = u.id 
                          WHERE r.status = 'pending' 
                          ORDER BY r.uploaded_at DESC";
$pending_resources_stmt = $conn->prepare($pending_resources_query);
$pending_resources_stmt->execute();
$pending_resources = $pending_resources_stmt->fetchAll();

// Get all resources
$all_resources_query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
                       FROM resources r 
                       LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                       LEFT JOIN users u ON r.uploaded_by = u.id 
                       ORDER BY r.uploaded_at DESC 
                       LIMIT 20";
$all_resources_stmt = $conn->prepare($all_resources_query);
$all_resources_stmt->execute();
$all_resources = $all_resources_stmt->fetchAll();

// Get all subjects
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $conn->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll();

// Get recent circulars
$circulars_query = "SELECT c.*, u.name as uploaded_by_name 
                   FROM circulars c 
                   LEFT JOIN users u ON c.uploaded_by = u.id 
                   ORDER BY c.created_at DESC 
                   LIMIT 10";
$circulars_stmt = $conn->prepare($circulars_query);
$circulars_stmt->execute();
$circulars = $circulars_stmt->fetchAll();

// Get all users
$all_users_query = "SELECT id, name, email, role, department, created_at FROM users ORDER BY created_at DESC";
$all_users_stmt = $conn->prepare($all_users_query);
$all_users_stmt->execute();
$all_users = $all_users_stmt->fetchAll();
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
                    <i class="fas fa-user-shield text-2xl text-red-600 mr-3"></i>
                    <span class="text-xl font-semibold">Admin Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-2xl font-bold"><?php echo $stats['student'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Teachers</p>
                        <p class="text-2xl font-bold"><?php echo $stats['teacher'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Resources</p>
                        <p class="text-2xl font-bold"><?php echo $stats['total_resources']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Pending Approval</p>
                        <p class="text-2xl font-bold"><?php echo $stats['pending_resources']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <button onclick="showTab('pending')" id="pending-tab" class="tab-button py-4 px-1 border-b-2 border-red-500 font-medium text-sm text-red-600">
                        Pending Resources (<?php echo count($pending_resources); ?>)
                    </button>
                    <button onclick="showTab('resources')" id="resources-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        All Resources
                    </button>
                    <button onclick="showTab('users')" id="users-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Manage Users
                    </button>
                    <button onclick="showTab('subjects')" id="subjects-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Subjects
                    </button>
                    <button onclick="showTab('circulars')" id="circulars-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        Circulars
                    </button>
                </nav>
            </div>

            <!-- Pending Resources Tab -->
            <div id="pending-content" class="tab-content p-6">
                <h3 class="text-lg font-semibold mb-4">Pending Resources</h3>
                <?php if (empty($pending_resources)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-check-circle text-4xl mb-4"></i>
                        <p>No pending resources. All caught up!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($pending_resources as $resource): ?>
                            <div class="border rounded-lg p-4 bg-yellow-50">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-lg"><?php echo htmlspecialchars($resource['title']); ?></h4>
                                        <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($resource['description']); ?></p>
                                        <div class="flex items-center text-sm text-gray-500 space-x-4">
                                            <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></span>
                                            <span><i class="fas fa-book mr-1"></i><?php echo htmlspecialchars($resource['subject_name']); ?></span>
                                            <span><i class="fas fa-tag mr-1"></i><?php echo ucfirst($resource['resource_type']); ?></span>
                                            <span><i class="fas fa-calendar mr-1"></i><?php echo date('M j, Y', strtotime($resource['uploaded_at'])); ?></span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                                <i class="fas fa-check mr-1"></i>Approve
                                            </button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
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
                <h3 class="text-lg font-semibold mb-4">All Resources</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Title</th>
                                <th class="px-4 py-2 text-left">Subject</th>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-left">Uploaded By</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Downloads</th>
                                <th class="px-4 py-2 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_resources as $resource): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($resource['title']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($resource['subject_name']); ?></td>
                                    <td class="px-4 py-2"><?php echo ucfirst($resource['resource_type']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            <?php 
                                            echo $resource['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                ($resource['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                            ?>">
                                            <?php echo ucfirst($resource['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2"><?php echo $resource['download_count']; ?></td>
                                    <td class="px-4 py-2"><?php echo date('M j, Y', strtotime($resource['uploaded_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Tab -->
            <div id="users-content" class="tab-content p-6 hidden">
                <h3 class="text-lg font-semibold mb-4">All Users</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Email</th>
                                <th class="px-4 py-2 text-left">Role</th>
                                <th class="px-4 py-2 text-left">Department</th>
                                <th class="px-4 py-2 text-left">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_users as $u): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2"><?php echo $u['id']; ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($u['name']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            <?php 
                                            echo $u['role'] === 'admin' ? 'bg-red-100 text-red-800' : 
                                                ($u['role'] === 'teacher' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'); 
                                            ?>">
                                            <?php echo ucfirst($u['role']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($u['department']); ?></td>
                                    <td class="px-4 py-2"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Subjects Tab -->
            <div id="subjects-content" class="tab-content p-6 hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Subjects</h3>
                    <button onclick="showAddSubjectForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-1"></i>Add Subject
                    </button>
                </div>

                <!-- Add Subject Form -->
                <div id="add-subject-form" class="hidden mb-6 p-4 border rounded-lg bg-gray-50">
                    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="subject_name" placeholder="Subject Name" required 
                               class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <input type="text" name="department" placeholder="Department" required 
                               class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" name="add_subject" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            Add Subject
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($subjects as $subject): ?>
                        <div class="border rounded-lg p-4 bg-white">
                            <h4 class="font-semibold"><?php echo htmlspecialchars($subject['subject_name']); ?></h4>
                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($subject['department']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Circulars Tab -->
            <div id="circulars-content" class="tab-content p-6 hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Circulars</h3>
                    <button onclick="showAddCircularForm()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-1"></i>Add Circular
                    </button>
                </div>

                <!-- Add Circular Form -->
                <div id="add-circular-form" class="hidden mb-6 p-4 border rounded-lg bg-gray-50">
                    <form method="POST" class="space-y-4">
                        <input type="text" name="circular_title" placeholder="Circular Title" required 
                               class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <textarea name="circular_content" placeholder="Circular Content" rows="4" required 
                                  class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        <button type="submit" name="add_circular" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            Add Circular
                        </button>
                    </form>
                </div>

                <div class="space-y-4">
                    <?php foreach ($circulars as $circular): ?>
                        <div class="border rounded-lg p-4 bg-white">
                            <h4 class="font-semibold text-lg"><?php echo htmlspecialchars($circular['title']); ?></h4>
                            <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($circular['content']); ?></p>
                            <div class="text-sm text-gray-500">
                                By <?php echo htmlspecialchars($circular['uploaded_by_name']); ?> on 
                                <?php echo date('M j, Y g:i A', strtotime($circular['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.add('hidden'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab-button');
            tabs.forEach(tab => {
                tab.classList.remove('border-red-500', 'text-red-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Add active class to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-red-500', 'text-red-600');
        }

        function showAddSubjectForm() {
            const form = document.getElementById('add-subject-form');
            form.classList.toggle('hidden');
        }

        function showAddCircularForm() {
            const form = document.getElementById('add-circular-form');
            form.classList.toggle('hidden');
        }
    </script>
</body>
</html>
