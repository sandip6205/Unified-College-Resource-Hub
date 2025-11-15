<?php
// Setup Enhanced Upload System
require_once __DIR__ . '/config/database.php';

echo "<h1>🚀 Setting up Enhanced Upload System</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Add resource_type and semester columns if they don't exist
    echo "<p>🔧 Adding resource_type and semester columns...</p>";
    
    $alter_queries = [
        "ALTER TABLE resources ADD COLUMN IF NOT EXISTS resource_type ENUM('notes', 'syllabus', 'pyq', 'assignment') DEFAULT 'notes'",
        "ALTER TABLE resources ADD COLUMN IF NOT EXISTS semester INT DEFAULT 1"
    ];
    
    foreach ($alter_queries as $query) {
        try {
            $conn->exec($query);
        } catch (Exception $e) {
            // Column might already exist
        }
    }
    
    echo "<p>✅ Database schema updated!</p>";
    
    // Create organized upload directories
    echo "<p>🔧 Creating organized upload directories...</p>";
    
    $upload_dirs = [
        __DIR__ . '/uploads/notes/',
        __DIR__ . '/uploads/syllabus/',
        __DIR__ . '/uploads/pyq/',
        __DIR__ . '/uploads/assignment/'
    ];
    
    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "<p>✅ Created directory: " . basename($dir) . "</p>";
        }
    }
    
    // Add sample resources for each type
    echo "<p>🔧 Adding sample resources...</p>";
    
    $sample_resources = [
        [
            'title' => 'C Programming Fundamentals',
            'description' => 'Complete notes covering variables, functions, loops, and arrays',
            'resource_type' => 'notes',
            'semester' => 1,
            'chapter' => 'Chapter 1-5',
            'tags' => 'programming, c language, basics'
        ],
        [
            'title' => 'Computer Science Syllabus 2024',
            'description' => 'Complete syllabus for Computer Science Engineering',
            'resource_type' => 'syllabus',
            'semester' => 1,
            'chapter' => 'Full Curriculum',
            'tags' => 'syllabus, curriculum, cse'
        ],
        [
            'title' => 'Data Structures Previous Year Papers',
            'description' => 'Collection of previous year question papers',
            'resource_type' => 'pyq',
            'semester' => 3,
            'chapter' => 'All Units',
            'tags' => 'previous year, questions, data structures'
        ],
        [
            'title' => 'Programming Assignment - Loops',
            'description' => 'Practice assignment on loops and conditional statements',
            'resource_type' => 'assignment',
            'semester' => 1,
            'chapter' => 'Chapter 3',
            'tags' => 'assignment, loops, practice'
        ]
    ];
    
    // Get a teacher ID for sample data
    $teacher_query = "SELECT id FROM users WHERE role = 'teacher' LIMIT 1";
    $teacher_stmt = $conn->prepare($teacher_query);
    $teacher_stmt->execute();
    $teacher = $teacher_stmt->fetch();
    
    if ($teacher) {
        $teacher_id = $teacher['id'];
        
        // Get a subject ID
        $subject_query = "SELECT subject_id FROM subjects LIMIT 1";
        $subject_stmt = $conn->prepare($subject_query);
        $subject_stmt->execute();
        $subject = $subject_stmt->fetch();
        
        if ($subject) {
            $subject_id = $subject['subject_id'];
            
            foreach ($sample_resources as $resource) {
                $insert_query = "INSERT INTO resources (title, description, subject_id, resource_type, semester, uploaded_by, chapter, tags, status, file_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?)";
                $insert_stmt = $conn->prepare($insert_query);
                
                $file_url = 'uploads/' . $resource['resource_type'] . '/sample_' . $resource['resource_type'] . '.pdf';
                
                $insert_stmt->execute([
                    $resource['title'],
                    $resource['description'],
                    $subject_id,
                    $resource['resource_type'],
                    $resource['semester'],
                    $teacher_id,
                    $resource['chapter'],
                    $resource['tags'],
                    $file_url
                ]);
            }
            
            echo "<p>✅ Sample resources added!</p>";
        }
    }
    
    echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>✅ Enhanced Upload System Ready!</h3>";
    echo "<p><strong>Features now available:</strong></p>";
    echo "<ul style='margin-left: 20px; line-height: 1.8;'>";
    echo "<li>📄 <strong>Notes Upload</strong> - PDF, DOC, PPT files</li>";
    echo "<li>📋 <strong>Syllabus Upload</strong> - PDF, DOC files</li>";
    echo "<li>📊 <strong>Previous Year Papers</strong> - PDF, DOC, Images</li>";
    echo "<li>📝 <strong>Assignments</strong> - PDF, DOC, TXT files</li>";
    echo "<li>🗂️ <strong>Organized Storage</strong> - Files sorted by type</li>";
    echo "<li>📚 <strong>Semester Support</strong> - Resources tagged by semester</li>";
    echo "<li>🏷️ <strong>Enhanced Tagging</strong> - Better categorization</li>";
    echo "<li>✅ <strong>Admin Approval</strong> - Quality control workflow</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🚀 Test the Enhanced Upload System:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='dashboard/teacher_enhanced.php' style='background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>👨‍🏫 Enhanced Teacher Dashboard</a>";
    echo "<a href='dashboard/teacher.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>📚 Original Teacher Dashboard</a>";
    echo "</div>";
    
    echo "<h3>💡 Upload System Features:</h3>";
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<p><strong>Teachers can now upload:</strong></p>";
    echo "<ul style='margin-left: 20px; line-height: 1.6;'>";
    echo "<li><strong>📄 Notes:</strong> Lecture notes, study materials (PDF, DOC, PPT)</li>";
    echo "<li><strong>📋 Syllabus:</strong> Course curriculum, subject outlines (PDF, DOC)</li>";
    echo "<li><strong>📊 Previous Year Papers:</strong> Question papers, sample tests (PDF, DOC, Images)</li>";
    echo "<li><strong>📝 Assignments:</strong> Homework, practice problems (PDF, DOC, TXT)</li>";
    echo "</ul>";
    echo "<p><strong>Each upload includes:</strong> Title, Subject, Semester, Chapter, Description, Tags</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Error Setting Up Enhanced Upload System</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
