<?php
// Setup AI Notes Summary System
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/ai_notes_processor.php';

echo "<h1>🤖 Setting up AI Notes Summary System</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Create AI content table
    echo "<p>🔧 Creating AI content table...</p>";
    
    $create_table = "CREATE TABLE IF NOT EXISTS ai_content (
        id INT PRIMARY KEY AUTO_INCREMENT,
        resource_id INT NOT NULL,
        summary TEXT,
        important_questions JSON,
        mcqs JSON,
        chapter_explanation TEXT,
        ocr_content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_resource_id (resource_id)
    )";
    
    $conn->exec($create_table);
    echo "<p>✅ AI content table created!</p>";
    
    // Add OCR content column to resources table if it doesn't exist
    echo "<p>🔧 Adding OCR content column to resources table...</p>";
    
    try {
        $add_ocr_column = "ALTER TABLE resources ADD COLUMN IF NOT EXISTS ocr_content TEXT";
        $conn->exec($add_ocr_column);
        echo "<p>✅ OCR content column added!</p>";
    } catch (Exception $e) {
        echo "<p>ℹ️ OCR content column already exists</p>";
    }
    
    // Process existing resources with AI
    echo "<p>🔧 Processing existing resources with AI...</p>";
    
    $processor = new AINotesProcessor();
    
    // Get resources that don't have AI content yet
    $resources_query = "SELECT r.id, r.title, r.file_url 
                       FROM resources r 
                       LEFT JOIN ai_content ac ON r.id = ac.resource_id 
                       WHERE ac.id IS NULL AND r.file_url IS NOT NULL 
                       LIMIT 5";
    $resources_stmt = $conn->prepare($resources_query);
    $resources_stmt->execute();
    $resources = $resources_stmt->fetchAll();
    
    $processed_count = 0;
    foreach ($resources as $resource) {
        echo "<p>📄 Processing: " . htmlspecialchars($resource['title']) . "</p>";
        
        $file_path = __DIR__ . '/' . $resource['file_url'];
        $result = $processor->processPDF($resource['id'], $file_path);
        
        if ($result['success']) {
            $processed_count++;
            echo "<p>✅ AI content generated successfully!</p>";
        } else {
            echo "<p>⚠️ Processing failed: " . htmlspecialchars($result['error']) . "</p>";
        }
    }
    
    echo "<p>✅ Processed $processed_count resources with AI!</p>";
    
    // Create sample AI content for demo
    echo "<p>🔧 Creating sample AI content...</p>";
    
    $sample_ai_data = [
        'summary' => 'This chapter provides a comprehensive introduction to programming concepts including variables, functions, loops, and conditional statements. The content covers fundamental data types, control structures, and object-oriented programming principles essential for software development.',
        'questions' => [
            'What are the fundamental data types in programming?',
            'Explain the difference between variables and constants.',
            'How do control structures help in program flow?',
            'What is the importance of functions in programming?',
            'Describe the key principles of object-oriented programming.'
        ],
        'mcqs' => [
            [
                'question' => 'Which of the following is NOT a programming data type?',
                'options' => ['Integer', 'String', 'Boolean', 'Algorithm'],
                'correct' => 3,
                'explanation' => 'Algorithm is a procedure or set of instructions, not a data type.'
            ],
            [
                'question' => 'What does OOP stand for?',
                'options' => ['Object Oriented Programming', 'Only One Program', 'Open Operation Process', 'Optimal Output Processing'],
                'correct' => 0,
                'explanation' => 'OOP stands for Object Oriented Programming, a programming paradigm based on objects.'
            ]
        ],
        'explanation' => "## Programming Fundamentals\n\nThis chapter introduces the core concepts of programming that form the foundation of software development.\n\n### Key Topics:\n- Variables and Data Types\n- Control Structures\n- Functions and Procedures\n- Object-Oriented Concepts\n\n### Learning Objectives:\nBy the end of this chapter, students will understand how to create programs using fundamental programming constructs and apply object-oriented principles in their code."
    ];
    
    // Insert sample AI content for resources that don't have it
    $sample_query = "INSERT IGNORE INTO ai_content (resource_id, summary, important_questions, mcqs, chapter_explanation) 
                    SELECT r.id, ?, ?, ?, ?
                    FROM resources r 
                    LEFT JOIN ai_content ac ON r.id = ac.resource_id 
                    WHERE ac.id IS NULL 
                    LIMIT 3";
    
    $sample_stmt = $conn->prepare($sample_query);
    $sample_stmt->execute([
        $sample_ai_data['summary'],
        json_encode($sample_ai_data['questions']),
        json_encode($sample_ai_data['mcqs']),
        $sample_ai_data['explanation']
    ]);
    
    echo "<p>✅ Sample AI content created!</p>";
    
    echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>🤖 AI Notes Summary System Ready!</h3>";
    echo "<p><strong>Features now available:</strong></p>";
    echo "<ul style='margin-left: 20px; line-height: 1.8;'>";
    echo "<li>📄 <strong>AI Summary</strong> - Automatic content summarization</li>";
    echo "<li>❓ <strong>Important Questions</strong> - AI-generated practice questions</li>";
    echo "<li>📝 <strong>MCQs</strong> - Multiple choice questions with explanations</li>";
    echo "<li>📖 <strong>Chapter Explanation</strong> - Detailed content breakdown</li>";
    echo "<li>🔍 <strong>OCR Search</strong> - Search inside PDF content</li>";
    echo "<li>🎯 <strong>Interactive Learning</strong> - Engaging study experience</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🚀 Test AI Notes Summary:</h3>";
    
    // Get a sample resource for testing
    $test_resource_query = "SELECT r.id, r.title FROM resources r INNER JOIN ai_content ac ON r.id = ac.resource_id LIMIT 1";
    $test_resource_stmt = $conn->prepare($test_resource_query);
    $test_resource_stmt->execute();
    $test_resource = $test_resource_stmt->fetch();
    
    echo "<div style='margin: 20px 0;'>";
    if ($test_resource) {
        echo "<a href='dashboard/ai_notes_summary.php?id=" . $test_resource['id'] . "' style='background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🤖 View AI Summary</a>";
    }
    echo "<a href='dashboard/student.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>📚 Student Dashboard</a>";
    echo "</div>";
    
    echo "<h3>💡 How to Use AI Notes Summary:</h3>";
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<p><strong>For Students:</strong></p>";
    echo "<ul style='margin-left: 20px; line-height: 1.6;'>";
    echo "<li>1. Go to Student Dashboard</li>";
    echo "<li>2. Click on any resource</li>";
    echo "<li>3. Look for 'AI Summary' button or link</li>";
    echo "<li>4. Explore Summary, Questions, MCQs, and Explanations</li>";
    echo "<li>5. Use MCQs for self-assessment</li>";
    echo "</ul>";
    echo "<p><strong>For Teachers:</strong></p>";
    echo "<ul style='margin-left: 20px; line-height: 1.6;'>";
    echo "<li>• Upload PDFs through Enhanced Teacher Dashboard</li>";
    echo "<li>• AI automatically processes new uploads</li>";
    echo "<li>• Students get instant AI-powered study materials</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🔧 Technical Features:</h3>";
    echo "<div style='background: #e0f2fe; border: 1px solid #0288d1; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<ul style='margin-left: 20px; line-height: 1.6;'>";
    echo "<li><strong>🧠 Smart Text Processing:</strong> Extracts key concepts from PDFs</li>";
    echo "<li><strong>📝 Auto-Summary:</strong> Generates concise content summaries</li>";
    echo "<li><strong>❓ Question Generation:</strong> Creates relevant practice questions</li>";
    echo "<li><strong>🎯 MCQ Creation:</strong> Multiple choice with detailed explanations</li>";
    echo "<li><strong>📖 Chapter Analysis:</strong> Structured learning breakdown</li>";
    echo "<li><strong>🔍 OCR Integration:</strong> Searchable PDF content</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Error Setting Up AI Notes System</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
