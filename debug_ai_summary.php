<?php
session_start();

echo "<h1>🔧 AI Summary Debug Tool</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Not Logged In</h3>";
    echo "<p>Please login first to access AI summary.</p>";
    echo "<a href='simple_login.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>Login</a>";
    echo "</div>";
    exit;
}

require_once __DIR__ . '/config/database.php';

$resource_id = $_GET['id'] ?? 7;

echo "<h2>🧪 Testing Resource ID: $resource_id</h2>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p>✅ Database connection successful</p>";
    
    // Check if resource exists
    $resource_query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
                      FROM resources r 
                      LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                      LEFT JOIN users u ON r.uploaded_by = u.id 
                      WHERE r.id = ?";
    $resource_stmt = $conn->prepare($resource_query);
    $resource_stmt->execute([$resource_id]);
    $resource = $resource_stmt->fetch();
    
    if ($resource) {
        echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<h3>✅ Resource Found</h3>";
        echo "<p><strong>Title:</strong> " . htmlspecialchars($resource['title']) . "</p>";
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($resource['subject_name']) . "</p>";
        echo "<p><strong>File URL:</strong> " . htmlspecialchars($resource['file_url']) . "</p>";
        echo "<p><strong>Uploaded by:</strong> " . htmlspecialchars($resource['uploaded_by_name']) . "</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
        echo "<h3>❌ Resource Not Found</h3>";
        echo "<p>Resource with ID $resource_id does not exist.</p>";
        echo "</div>";
        
        // Show available resources
        $all_resources_query = "SELECT id, title FROM resources ORDER BY id DESC LIMIT 10";
        $all_resources_stmt = $conn->prepare($all_resources_query);
        $all_resources_stmt->execute();
        $all_resources = $all_resources_stmt->fetchAll();
        
        echo "<h3>📋 Available Resources:</h3>";
        foreach ($all_resources as $res) {
            echo "<p><a href='debug_ai_summary.php?id=" . $res['id'] . "' style='color: #3b82f6;'>ID " . $res['id'] . ": " . htmlspecialchars($res['title']) . "</a></p>";
        }
        exit;
    }
    
    // Check if AI content table exists
    echo "<h3>🔧 Checking AI Content Table...</h3>";
    
    try {
        $check_table = "SHOW TABLES LIKE 'ai_content'";
        $check_stmt = $conn->prepare($check_table);
        $check_stmt->execute();
        $table_exists = $check_stmt->fetch();
        
        if ($table_exists) {
            echo "<p>✅ AI content table exists</p>";
            
            // Check if AI content exists for this resource
            $ai_query = "SELECT * FROM ai_content WHERE resource_id = ?";
            $ai_stmt = $conn->prepare($ai_query);
            $ai_stmt->execute([$resource_id]);
            $ai_content = $ai_stmt->fetch();
            
            if ($ai_content) {
                echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
                echo "<h3>✅ AI Content Found</h3>";
                echo "<p><strong>Summary:</strong> " . substr(htmlspecialchars($ai_content['summary']), 0, 100) . "...</p>";
                echo "<p><strong>Questions Count:</strong> " . count(json_decode($ai_content['important_questions'], true)) . "</p>";
                echo "<p><strong>MCQs Count:</strong> " . count(json_decode($ai_content['mcqs'], true)) . "</p>";
                echo "<p><strong>Created:</strong> " . $ai_content['created_at'] . "</p>";
                echo "</div>";
            } else {
                echo "<div style='background: #fef3c7; border: 2px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
                echo "<h3>⚠️ No AI Content Found</h3>";
                echo "<p>AI content needs to be generated for this resource.</p>";
                echo "<button onclick='generateAIContent()' style='background: #7c3aed; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;'>Generate AI Content</button>";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
            echo "<h3>❌ AI Content Table Missing</h3>";
            echo "<p>The ai_content table doesn't exist. Run setup first.</p>";
            echo "<a href='setup_ai_notes.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>Run Setup</a>";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<p>❌ Error checking AI table: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // Test AI processor
    echo "<h3>🤖 Testing AI Processor...</h3>";
    
    if (file_exists(__DIR__ . '/ai_notes_processor.php')) {
        echo "<p>✅ AI processor file exists</p>";
        
        try {
            require_once __DIR__ . '/ai_notes_processor.php';
            $processor = new AINotesProcessor();
            echo "<p>✅ AI processor class loaded successfully</p>";
            
            // Test processing
            if ($resource && !$ai_content) {
                echo "<p>🔧 Generating AI content...</p>";
                $file_path = __DIR__ . '/' . $resource['file_url'];
                $result = $processor->processPDF($resource_id, $file_path);
                
                if ($result['success']) {
                    echo "<p>✅ AI content generated successfully!</p>";
                } else {
                    echo "<p>❌ AI processing failed: " . htmlspecialchars($result['error']) . "</p>";
                }
            }
            
        } catch (Exception $e) {
            echo "<p>❌ AI processor error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p>❌ AI processor file missing</p>";
    }
    
    echo "<h3>🚀 Test Links:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='dashboard/ai_notes_summary.php?id=$resource_id' style='background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🤖 Try AI Summary</a>";
    echo "<a href='setup_ai_notes.php' style='background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🔧 Run Setup</a>";
    echo "<a href='dashboard/student.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>📚 Student Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<script>
function generateAIContent() {
    alert('Generating AI content... This may take a few moments.');
    fetch('ai_notes_processor.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=process_pdf&resource_id=$resource_id&file_path=" . ($resource['file_url'] ?? '') . "'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('AI content generated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Network error: ' + error);
    });
}
</script>";
?>
