<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireRole('teacher');

$database = new Database();
$conn = $database->getConnection();
$user = $auth->getCurrentUser();

$success_message = '';
$error_message = '';

// Handle Enhanced Upload System
if ($_POST && isset($_POST['upload_resource'])) {
    $title = $_POST['title'] ?? '';
    $subject_id = $_POST['subject_id'] ?? '';
    $resource_type = $_POST['resource_type'] ?? 'notes';
    $semester = $_POST['semester'] ?? 1;
    $chapter = $_POST['chapter'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if ($title && $subject_id && isset($_FILES['resource_file'])) {
        $file = $_FILES['resource_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Validate file type based on resource type
            $allowed_types = [
                'notes' => ['pdf', 'doc', 'docx', 'ppt', 'pptx'],
                'syllabus' => ['pdf', 'doc', 'docx'],
                'pyq' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
                'assignment' => ['pdf', 'doc', 'docx', 'txt']
            ];
            
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($file_extension, $allowed_types[$resource_type] ?? [])) {
                $error_message = "Invalid file type for " . ucfirst($resource_type) . ". Allowed: " . implode(', ', $allowed_types[$resource_type] ?? []);
            } else {
                // Create organized upload directory
                $upload_dir = __DIR__ . '/../uploads/' . $resource_type . '/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $filename = $resource_type . '_' . uniqid() . '_' . time() . '.' . $file_extension;
                $file_path = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // Insert into database
                    $insert_query = "INSERT INTO resources (title, description, subject_id, resource_type, semester, file_url, uploaded_by, chapter, tags, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
                    $insert_stmt = $conn->prepare($insert_query);
                    
                    $file_url = 'uploads/' . $resource_type . '/' . $filename;
                    
                    if ($insert_stmt->execute([$title, $description, $subject_id, $resource_type, $semester, $file_url, $user['id'], $chapter, $tags])) {
                        $success_message = ucfirst($resource_type) . " uploaded successfully! It will be available after admin approval.";
                        
                        // Create notification for admin
                        try {
                            $notification_query = "INSERT INTO notifications (user_id, title, message, type) 
                                                 SELECT id, 'New Resource Pending Approval', 
                                                 CONCAT('Teacher ', ?, ' uploaded a new ', ?, ': ', ?), 'info'
                                                 FROM users WHERE role = 'admin'";
                            $notification_stmt = $conn->prepare($notification_query);
                            $notification_stmt->execute([$user['name'], $resource_type, $title]);
                        } catch (Exception $e) {
                            // Continue even if notification fails
                        }
                        
                    } else {
                        $error_message = "Failed to save resource to database.";
                    }
                } else {
                    $error_message = "Failed to upload file.";
                }
            }
        } else {
            $error_message = "File upload error: " . $file['error'];
        }
    } else {
        $error_message = "Please fill in all required fields and select a file.";
    }
}

// Get teacher's uploaded resources
$resources_query = "SELECT r.*, s.subject_name FROM resources r 
                   LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                   WHERE r.uploaded_by = ? ORDER BY r.uploaded_at DESC";
$resources_stmt = $conn->prepare($resources_query);
$resources_stmt->execute([$user['id']]);
$resources = $resources_stmt->fetchAll();

// Get subjects for dropdown
$subjects_query = "SELECT * FROM subjects ORDER BY subject_name";
$subjects_stmt = $conn->prepare($subjects_query);
$subjects_stmt->execute();
$subjects = $subjects_stmt->fetchAll();

// Get upload statistics by type
$stats_query = "SELECT 
                    resource_type,
                    COUNT(*) as count,
                    SUM(download_count) as downloads
                FROM resources 
                WHERE uploaded_by = ? 
                GROUP BY resource_type";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute([$user['id']]);
$type_stats = $stats_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get overall stats
$overall_stats_query = "SELECT 
                        COUNT(*) as total_uploads,
                        SUM(download_count) as total_downloads,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_uploads,
                        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_uploads
                        FROM resources WHERE uploaded_by = ?";
$overall_stats_stmt = $conn->prepare($overall_stats_query);
$overall_stats_stmt->execute([$user['id']]);
$overall_stats = $overall_stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Teacher Dashboard - College Resource Hub</title>
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
                    <i class="fas fa-chalkboard-teacher text-2xl text-indigo-600 mr-3"></i>
                    <span class="text-xl font-semibold">Enhanced Teacher Dashboard</span>
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
        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-upload text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Uploads</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $overall_stats['total_uploads']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Approved</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $overall_stats['approved_uploads']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $overall_stats['pending_uploads']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-download text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Downloads</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $overall_stats['total_downloads']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Upload Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">
                <i class="fas fa-cloud-upload-alt mr-2 text-indigo-600"></i>Upload New Resource
            </h2>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Resource Type Selection -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resource Type</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <label class="resource-type-card cursor-pointer">
                                <input type="radio" name="resource_type" value="notes" checked class="sr-only">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-blue-500 transition-colors">
                                    <i class="fas fa-file-alt text-2xl text-blue-600 mb-2"></i>
                                    <p class="font-medium">📄 Notes</p>
                                    <p class="text-xs text-gray-500">PDF, DOC, PPT</p>
                                </div>
                            </label>
                            
                            <label class="resource-type-card cursor-pointer">
                                <input type="radio" name="resource_type" value="syllabus" class="sr-only">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-green-500 transition-colors">
                                    <i class="fas fa-list-alt text-2xl text-green-600 mb-2"></i>
                                    <p class="font-medium">📋 Syllabus</p>
                                    <p class="text-xs text-gray-500">PDF, DOC</p>
                                </div>
                            </label>
                            
                            <label class="resource-type-card cursor-pointer">
                                <input type="radio" name="resource_type" value="pyq" class="sr-only">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-purple-500 transition-colors">
                                    <i class="fas fa-history text-2xl text-purple-600 mb-2"></i>
                                    <p class="font-medium">📊 Previous Year</p>
                                    <p class="text-xs text-gray-500">PDF, DOC, Images</p>
                                </div>
                            </label>
                            
                            <label class="resource-type-card cursor-pointer">
                                <input type="radio" name="resource_type" value="assignment" class="sr-only">
                                <div class="border-2 border-gray-200 rounded-lg p-4 text-center hover:border-red-500 transition-colors">
                                    <i class="fas fa-tasks text-2xl text-red-600 mb-2"></i>
                                    <p class="font-medium">📝 Assignment</p>
                                    <p class="text-xs text-gray-500">PDF, DOC, TXT</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" required 
                               placeholder="Enter resource title..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                        <select name="subject_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Subject</option>
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['subject_id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select name="semester" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <?php for($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Chapter/Unit</label>
                        <input type="text" name="chapter" 
                               placeholder="e.g., Chapter 1, Unit 2..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" 
                              placeholder="Brief description of the resource..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                    <input type="text" name="tags" 
                           placeholder="programming, loops, functions (comma separated)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- File Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload File *</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="resource_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="resource_file" name="resource_file" type="file" required class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500" id="file-type-info">
                                PDF, DOC, DOCX, PPT, PPTX up to 10MB
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="upload_resource" 
                            class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 transition-colors">
                        <i class="fas fa-upload mr-2"></i>Upload Resource
                    </button>
                </div>
            </form>
        </div>

        <!-- My Uploads -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">
                <i class="fas fa-folder mr-2 text-indigo-600"></i>My Uploads
            </h2>
            
            <?php if (empty($resources)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-folder-open text-4xl mb-4"></i>
                    <p>No resources uploaded yet. Upload your first resource above!</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Title</th>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-left">Subject</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Downloads</th>
                                <th class="px-4 py-2 text-left">Uploaded</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        <div>
                                            <p class="font-medium"><?php echo htmlspecialchars($resource['title']); ?></p>
                                            <?php if ($resource['chapter']): ?>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($resource['chapter']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            <?php 
                                            $type_colors = [
                                                'notes' => 'bg-blue-100 text-blue-800',
                                                'syllabus' => 'bg-green-100 text-green-800',
                                                'pyq' => 'bg-purple-100 text-purple-800',
                                                'assignment' => 'bg-red-100 text-red-800'
                                            ];
                                            echo $type_colors[$resource['resource_type']] ?? 'bg-gray-100 text-gray-800';
                                            ?>">
                                            <?php 
                                            $type_icons = [
                                                'notes' => '📄',
                                                'syllabus' => '📋',
                                                'pyq' => '📊',
                                                'assignment' => '📝'
                                            ];
                                            echo $type_icons[$resource['resource_type']] ?? '📄';
                                            echo ' ' . ucfirst($resource['resource_type']); 
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($resource['subject_name']); ?></td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            <?php 
                                            echo $resource['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                ($resource['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                            ?>">
                                            <?php echo ucfirst($resource['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2"><?php echo $resource['download_count'] ?? 0; ?></td>
                                    <td class="px-4 py-2"><?php echo date('M j, Y', strtotime($resource['uploaded_at'])); ?></td>
                                    <td class="px-4 py-2">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Resource type selection
        document.querySelectorAll('input[name="resource_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove active class from all cards
                document.querySelectorAll('.resource-type-card > div').forEach(card => {
                    card.classList.remove('border-blue-500', 'border-green-500', 'border-purple-500', 'border-red-500', 'bg-blue-50', 'bg-green-50', 'bg-purple-50', 'bg-red-50');
                    card.classList.add('border-gray-200');
                });
                
                // Add active class to selected card
                const selectedCard = this.parentElement.querySelector('div');
                const colors = {
                    'notes': ['border-blue-500', 'bg-blue-50'],
                    'syllabus': ['border-green-500', 'bg-green-50'],
                    'pyq': ['border-purple-500', 'bg-purple-50'],
                    'assignment': ['border-red-500', 'bg-red-50']
                };
                
                selectedCard.classList.remove('border-gray-200');
                selectedCard.classList.add(...colors[this.value]);
                
                // Update file type info
                const fileTypeInfo = {
                    'notes': 'PDF, DOC, DOCX, PPT, PPTX up to 10MB',
                    'syllabus': 'PDF, DOC, DOCX up to 10MB',
                    'pyq': 'PDF, DOC, DOCX, JPG, PNG up to 10MB',
                    'assignment': 'PDF, DOC, DOCX, TXT up to 10MB'
                };
                
                document.getElementById('file-type-info').textContent = fileTypeInfo[this.value];
            });
        });

        // File upload preview
        document.getElementById('resource_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileInfo = document.getElementById('file-type-info');
                fileInfo.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            }
        });
    </script>
</body>
</html>
