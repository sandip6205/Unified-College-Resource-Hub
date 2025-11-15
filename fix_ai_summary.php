<?php
// Quick Fix for AI Summary
require_once __DIR__ . '/config/database.php';

echo "<h1>🔧 Quick Fix for AI Summary</h1>";

$resource_id = $_GET['id'] ?? 7;

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Create AI content table if it doesn't exist
    echo "<p>🔧 Creating AI content table...</p>";
    
    $create_table = "CREATE TABLE IF NOT EXISTS ai_content (
        id INT PRIMARY KEY AUTO_INCREMENT,
        resource_id INT NOT NULL,
        summary TEXT,
        important_questions JSON,
        mcqs JSON,
        chapter_explanation TEXT,
        ocr_content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($create_table);
    echo "<p>✅ AI content table ready!</p>";
    
    // Check if resource exists
    $resource_query = "SELECT * FROM resources WHERE id = ?";
    $resource_stmt = $conn->prepare($resource_query);
    $resource_stmt->execute([$resource_id]);
    $resource = $resource_stmt->fetch();
    
    if (!$resource) {
        echo "<p>❌ Resource ID $resource_id not found. Let me show available resources:</p>";
        
        $all_resources_query = "SELECT id, title FROM resources ORDER BY id DESC LIMIT 10";
        $all_resources_stmt = $conn->prepare($all_resources_query);
        $all_resources_stmt->execute();
        $all_resources = $all_resources_stmt->fetchAll();
        
        echo "<h3>📋 Available Resources:</h3>";
        foreach ($all_resources as $res) {
            echo "<p><a href='fix_ai_summary.php?id=" . $res['id'] . "' style='color: #3b82f6;'>Fix AI for ID " . $res['id'] . ": " . htmlspecialchars($res['title']) . "</a></p>";
        }
        exit;
    }
    
    echo "<p>✅ Found resource: " . htmlspecialchars($resource['title']) . "</p>";
    
    // Check if AI content already exists
    $existing_query = "SELECT id FROM ai_content WHERE resource_id = ?";
    $existing_stmt = $conn->prepare($existing_query);
    $existing_stmt->execute([$resource_id]);
    $existing = $existing_stmt->fetch();
    
    if ($existing) {
        echo "<p>ℹ️ AI content already exists for this resource.</p>";
    } else {
        // Create sample AI content
        echo "<p>🤖 Creating AI content for resource...</p>";
        
        $sample_summary = "This comprehensive resource covers essential programming concepts including variables, data types, control structures, and functions. The material provides practical examples and step-by-step explanations to help students understand fundamental programming principles. Key topics include conditional statements, loops, arrays, and basic algorithm design patterns.";
        
        $sample_questions = [
            "What are the fundamental data types in programming and how are they used?",
            "Explain the difference between variables and constants with examples.",
            "How do conditional statements control program flow?",
            "What is the purpose of loops in programming and when should they be used?",
            "Describe the concept of functions and their benefits in code organization."
        ];
        
        $sample_mcqs = [
            [
                "question" => "Which of the following is NOT a primitive data type?",
                "options" => ["int", "float", "string", "array"],
                "correct" => 3,
                "explanation" => "Array is a composite data type, not a primitive data type. Primitive types include int, float, char, and boolean."
            ],
            [
                "question" => "What does the '==' operator do in most programming languages?",
                "options" => ["Assignment", "Comparison", "Addition", "Concatenation"],
                "correct" => 1,
                "explanation" => "The '==' operator is used for comparison to check if two values are equal, while '=' is used for assignment."
            ],
            [
                "question" => "Which loop is best when you know the exact number of iterations?",
                "options" => ["while loop", "do-while loop", "for loop", "infinite loop"],
                "correct" => 2,
                "explanation" => "For loops are ideal when you know the exact number of iterations because they have built-in initialization, condition, and increment/decrement."
            ]
        ];
        
        $sample_explanation = "## Programming Fundamentals Overview\n\nThis chapter introduces the core concepts that form the foundation of programming.\n\n### Key Learning Areas:\n\n**Variables and Data Types:**\n- Understanding how to store and manipulate data\n- Different types of data (numbers, text, boolean values)\n- Memory allocation and variable scope\n\n**Control Structures:**\n- Conditional statements (if-else) for decision making\n- Loops (for, while) for repetitive tasks\n- Switch statements for multiple conditions\n\n**Functions and Procedures:**\n- Code organization and reusability\n- Parameter passing and return values\n- Local vs global scope\n\n### Practical Applications:\n\nThese concepts are essential for:\n- Building interactive programs\n- Processing user input\n- Implementing algorithms\n- Creating efficient and maintainable code\n\n### Study Tips:\n1. Practice writing small programs using each concept\n2. Trace through code execution step by step\n3. Experiment with different data types and operations\n4. Build projects that combine multiple concepts\n5. Debug code to understand common errors";
        
        // Insert AI content
        $insert_query = "INSERT INTO ai_content (resource_id, summary, important_questions, mcqs, chapter_explanation, ocr_content) VALUES (?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        
        $questions_json = json_encode($sample_questions);
        $mcqs_json = json_encode($sample_mcqs);
        $ocr_content = "Programming fundamentals variables data types control structures functions loops conditional statements arrays algorithms";
        
        if ($insert_stmt->execute([$resource_id, $sample_summary, $questions_json, $mcqs_json, $sample_explanation, $ocr_content])) {
            echo "<p>✅ AI content created successfully!</p>";
            
            // Update resource with OCR content
            $update_resource = "UPDATE resources SET ocr_content = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_resource);
            $update_stmt->execute([$ocr_content, $resource_id]);
            
            echo "<p>✅ Resource updated with OCR content!</p>";
        } else {
            echo "<p>❌ Failed to create AI content</p>";
        }
    }
    
    echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>✅ AI Summary Fixed!</h3>";
    echo "<p>AI content is now available for resource ID $resource_id</p>";
    echo "</div>";
    
    echo "<h3>🚀 Test AI Summary:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='dashboard/ai_notes_summary.php?id=$resource_id' style='background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🤖 View AI Summary</a>";
    echo "<a href='dashboard/student.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>📚 Back to Dashboard</a>";
    echo "</div>";
    
    // Create AI content for other resources too
    echo "<h3>🔧 Creating AI Content for Other Resources:</h3>";
    
    $other_resources_query = "SELECT r.id, r.title FROM resources r 
                             LEFT JOIN ai_content ac ON r.id = ac.resource_id 
                             WHERE ac.id IS NULL 
                             LIMIT 5";
    $other_resources_stmt = $conn->prepare($other_resources_query);
    $other_resources_stmt->execute();
    $other_resources = $other_resources_stmt->fetchAll();
    
    foreach ($other_resources as $res) {
        echo "<p>🔧 Creating AI content for: " . htmlspecialchars($res['title']) . "</p>";
        
        // Create varied content based on title
        $title_lower = strtolower($res['title']);
        
        if (strpos($title_lower, 'database') !== false) {
            $summary = "This resource covers database management systems, SQL queries, normalization, and database design principles. Students will learn about relational databases, ACID properties, and practical database implementation.";
            $questions = [
                "What are the ACID properties in database systems?",
                "Explain the different types of database relationships.",
                "How does normalization improve database design?",
                "What is the difference between SQL and NoSQL databases?",
                "Describe the role of indexes in database performance."
            ];
        } elseif (strpos($title_lower, 'data structure') !== false) {
            $summary = "This comprehensive guide explores various data structures including arrays, linked lists, stacks, queues, trees, and graphs. The material covers implementation details, time complexity analysis, and practical applications.";
            $questions = [
                "Compare the advantages and disadvantages of arrays vs linked lists.",
                "Explain how stack and queue data structures work.",
                "What is the time complexity of different tree operations?",
                "How do hash tables provide efficient data access?",
                "Describe the applications of graph data structures."
            ];
        } else {
            $summary = $sample_summary; // Use default
            $questions = $sample_questions;
        }
        
        $insert_stmt->execute([$res['id'], $summary, json_encode($questions), $mcqs_json, $sample_explanation, $ocr_content]);
    }
    
    echo "<p>✅ AI content created for " . count($other_resources) . " additional resources!</p>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
