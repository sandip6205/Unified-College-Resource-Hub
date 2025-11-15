<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireRole('teacher');

$database = new Database();
$conn = $database->getConnection();

$user = $auth->getCurrentUser();

// Handle actions
if ($_POST) {
    // Handle file upload
    if (isset($_POST['upload_resource'])) {
        $title = $_POST['title'] ?? '';
        $subject_id = $_POST['subject_id'] ?? '';
        $chapter = $_POST['chapter'] ?? '';
        $tags = $_POST['tags'] ?? '';
        $description = $_POST['description'] ?? '';
        
        $upload_success = false;
        $file_url = '';
        $file_type = '';
        
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['resource_file'];
            
            // Create uploads directory if it doesn't exist
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $file_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $upload_success = true;
                $file_url = 'uploads/' . $filename;
                $file_type = $file_extension;
            } else {
                $error_message = "Failed to upload file.";
            }
        } else {
            $error_message = "Please select a file to upload.";
        }
        
        if ($upload_success && !empty($title) && !empty($subject_id)) {
            // Convert tags to JSON
            $tags_array = array_map('trim', explode(',', $tags));
            $tags_json = json_encode($tags_array);
            
            $insert_query = "INSERT INTO resources (title, subject_id, chapter, description, tags, file_url, file_type, uploaded_by, status) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
            $insert_stmt = $conn->prepare($insert_query);
            
            if ($insert_stmt->execute([$title, $subject_id, $chapter, $description, $tags_json, $file_url, $file_type, $user['id']])) {
                $success_message = "Resource uploaded successfully!";
                
                // Create notification for students
                try {
                    $notification_query = "INSERT INTO notifications (user_role, message, resource_id) VALUES ('student', ?, ?)";
                    $notification_stmt = $conn->prepare($notification_query);
                    $notification_stmt->execute(["New resource uploaded: $title", $conn->lastInsertId()]);
                } catch (Exception $e) {
                    // Continue even if notification fails
                }
            } else {
                $error_message = "Failed to save resource to database.";
            }
        } else if ($upload_success) {
            $error_message = "Please fill in all required fields.";
        }
    }
    
    // Handle edit resource
    if (isset($_POST['edit_resource'])) {
        $resource_id = $_POST['resource_id'];
        $title = $_POST['edit_title'];
        $subject_id = $_POST['edit_subject_id'];
        $chapter = $_POST['edit_chapter'];
        $description = $_POST['edit_description'];
        $tags = $_POST['edit_tags'];
        
        $tags_array = array_map('trim', explode(',', $tags));
        $tags_json = json_encode($tags_array);
        
        $update_query = "UPDATE resources SET title = ?, subject_id = ?, chapter = ?, description = ?, tags = ? WHERE id = ? AND uploaded_by = ?";
        $update_stmt = $conn->prepare($update_query);
        
        if ($update_stmt->execute([$title, $subject_id, $chapter, $description, $tags_json, $resource_id, $user['id']])) {
            $success_message = "Resource updated successfully!";
        } else {
            $error_message = "Failed to update resource.";
        }
    }
    
    // Handle delete resource
    if (isset($_POST['delete_resource'])) {
        $resource_id = $_POST['resource_id'];
        
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // Get file path to delete physical file
            $file_query = "SELECT file_url FROM resources WHERE id = ? AND uploaded_by = ?";
            $file_stmt = $conn->prepare($file_query);
            $file_stmt->execute([$resource_id, $user['id']]);
            $file_result = $file_stmt->fetch();
            
            if ($file_result) {
                // Delete related records first (to handle foreign key constraints)
                
                // Delete from download_history
                $delete_history_query = "DELETE FROM download_history WHERE resource_id = ?";
                $delete_history_stmt = $conn->prepare($delete_history_query);
                $delete_history_stmt->execute([$resource_id]);
                
                // Delete from notifications
                $delete_notifications_query = "DELETE FROM notifications WHERE resource_id = ?";
                $delete_notifications_stmt = $conn->prepare($delete_notifications_query);
                $delete_notifications_stmt->execute([$resource_id]);
                
                // Finally delete the resource
                $delete_query = "DELETE FROM resources WHERE id = ? AND uploaded_by = ?";
                $delete_stmt = $conn->prepare($delete_query);
                
                if ($delete_stmt->execute([$resource_id, $user['id']])) {
                    // Try to delete physical file
                    $file_path = __DIR__ . '/../' . $file_result['file_url'];
                    if (file_exists($file_path) && strpos($file_result['file_url'], 'uploads/') === 0) {
                        unlink($file_path);
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    $success_message = "Resource deleted successfully!";
                } else {
                    $conn->rollback();
                    $error_message = "Failed to delete resource from database.";
                }
            } else {
                $conn->rollback();
                $error_message = "Resource not found or you don't have permission to delete it.";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error deleting resource: " . $e->getMessage();
        }
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

// Get upload statistics
$stats_query = "SELECT 
                    COUNT(*) as total_uploads,
                    SUM(download_count) as total_downloads,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_uploads
                FROM resources WHERE uploaded_by = ?";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute([$user['id']]);
$stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - College Resource Hub</title>
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
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Success/Error Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Debug info for POST data -->
        <?php if ($_POST && isset($_POST['delete_resource'])): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                <strong>Debug:</strong> Delete request received for resource ID: <?php echo htmlspecialchars($_POST['resource_id'] ?? 'Not set'); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-upload text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Uploads</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_uploads']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-download text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Downloads</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['total_downloads']; ?></p>
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
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $stats['pending_uploads']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Upload Form -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Upload New Resource
                    </h2>
                </div>
                <div class="p-6">
                    <form method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" id="title" name="title" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="e.g., Class 1: Introduction to C Programming Notes">
                        </div>
                        
                        <div>
                            <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                            <select id="subject_id" name="subject_id" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?php echo $subject['subject_id']; ?>">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="chapter" class="block text-sm font-medium text-gray-700">Chapter (Optional)</label>
                            <input type="text" id="chapter" name="chapter"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="e.g., Chapter 1: Basics">
                        </div>
                        
                        <div>
                            <label for="file_type" class="block text-sm font-medium text-gray-700">File Type</label>
                            <select id="file_type" name="file_type" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="pdf">PDF</option>
                                <option value="doc">Document</option>
                                <option value="ppt">Presentation</option>
                                <option value="image">Image</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700">Tags (comma-separated)</label>
                            <input type="text" id="tags" name="tags"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="e.g., unit1, notes, important">
                        </div>
                        
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Brief description of the resource..."></textarea>
                        </div>
                        
                        <div>
                            <label for="resource_file" class="block text-sm font-medium text-gray-700">Upload File (Optional)</label>
                            <input type="file" id="resource_file" name="resource_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png,.gif"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, DOC, PPT, Images. Leave empty for demo resource.</p>
                        </div>
                        
                        <button type="submit" name="upload_resource"
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-upload mr-2"></i>Upload Resource
                        </button>
                    </form>
                </div>
            </div>

            <!-- My Uploads -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-list mr-2 text-green-600"></i>My Uploads
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($resources)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-500">No resources uploaded yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            <?php foreach ($resources as $resource): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                            <div class="text-sm text-gray-600 mt-1">
                                                <span class="mr-4"><i class="fas fa-book mr-1"></i><?php echo htmlspecialchars($resource['subject_name']); ?></span>
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
                                        <div class="ml-4 flex space-x-2">
                                            <button onclick="openEditModal(<?php echo $resource['id']; ?>, '<?php echo addslashes($resource['title']); ?>', <?php echo $resource['subject_id']; ?>, '<?php echo addslashes($resource['chapter']); ?>', '<?php echo addslashes($resource['description']); ?>', '<?php echo addslashes(implode(', ', json_decode($resource['tags'] ?? '[]'))); ?>')" 
                                                    class="text-blue-600 hover:text-blue-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="inline" onsubmit="return confirmDelete('<?php echo addslashes($resource['title']); ?>')">
                                                <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                                <button type="submit" name="delete_resource" class="text-red-600 hover:text-red-800 p-1 rounded" title="Delete Resource">
                                                    <i class="fas fa-trash text-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Resource</h3>
                </div>
                <form method="POST" class="p-6 space-y-4">
                    <input type="hidden" id="edit_resource_id" name="resource_id">
                    
                    <div>
                        <label for="edit_title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" id="edit_title" name="edit_title" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="edit_subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                        <select id="edit_subject_id" name="edit_subject_id" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <?php foreach ($subjects as $subject): ?>
                                <option value="<?php echo $subject['subject_id']; ?>">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="edit_chapter" class="block text-sm font-medium text-gray-700">Chapter</label>
                        <input type="text" id="edit_chapter" name="edit_chapter"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="edit_tags" class="block text-sm font-medium text-gray-700">Tags</label>
                        <input type="text" id="edit_tags" name="edit_tags"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="edit_description" name="edit_description" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeEditModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" name="edit_resource"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                            Update Resource
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id, title, subjectId, chapter, description, tags) {
            document.getElementById('edit_resource_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_subject_id').value = subjectId;
            document.getElementById('edit_chapter').value = chapter || '';
            document.getElementById('edit_description').value = description || '';
            document.getElementById('edit_tags').value = tags || '';
            document.getElementById('editModal').classList.remove('hidden');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
        
        function confirmDelete(title) {
            return confirm('Are you sure you want to delete "' + title + '"?\n\nThis action cannot be undone and will permanently remove the resource and its file.');
        }
        
        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
