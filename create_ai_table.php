<?php
// Create AI Content Table and Sample Data
require_once __DIR__ . '/config/database.php';

echo "<h1>🔧 Creating AI Content Table</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p>✅ Database connected successfully</p>";
    
    // Create AI content table
    echo "<p>🔧 Creating ai_content table...</p>";
    
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
    echo "<p>✅ AI content table created successfully!</p>";
    
    // Check if resource ID 7 exists
    $resource_query = "SELECT id, title FROM resources WHERE id = 7";
    $resource_stmt = $conn->prepare($resource_query);
    $resource_stmt->execute();
    $resource = $resource_stmt->fetch();
    
    if ($resource) {
        echo "<p>✅ Found resource ID 7: " . htmlspecialchars($resource['title']) . "</p>";
        
        // Check if AI content already exists for resource 7
        $existing_query = "SELECT id FROM ai_content WHERE resource_id = 7";
        $existing_stmt = $conn->prepare($existing_query);
        $existing_stmt->execute();
        $existing = $existing_stmt->fetch();
        
        if ($existing) {
            echo "<p>ℹ️ AI content already exists for resource ID 7</p>";
        } else {
            echo "<p>🤖 Creating AI content for resource ID 7...</p>";
            
            // Sample AI content
            $summary = "This comprehensive programming resource covers fundamental concepts including variables, data types, control structures, functions, and basic algorithms. The material provides step-by-step explanations with practical examples to help students understand core programming principles and develop problem-solving skills.";
            
            $questions = [
                "What are the different primitive data types in programming and how are they used?",
                "Explain the concept of variables and how they differ from constants.",
                "How do conditional statements (if-else) control program execution flow?",
                "What is the purpose of loops in programming and when should each type be used?",
                "Describe how functions help organize code and improve reusability."
            ];
            
            $mcqs = [
                [
                    "question" => "Which of the following is NOT a primitive data type?",
                    "options" => ["int", "float", "boolean", "array"],
                    "correct" => 3,
                    "explanation" => "Array is a composite/reference data type, not a primitive data type. Primitive types include int, float, char, and boolean."
                ],
                [
                    "question" => "What is the correct syntax for a for loop in most programming languages?",
                    "options" => ["for (init; condition; increment)", "for (condition; init; increment)", "for (increment; condition; init)", "for (condition; increment; init)"],
                    "correct" => 0,
                    "explanation" => "The standard for loop syntax is: for (initialization; condition; increment/decrement)."
                ],
                [
                    "question" => "Which operator is used for assignment in most programming languages?",
                    "options" => ["==", "=", "!=", "=>"],
                    "correct" => 1,
                    "explanation" => "The '=' operator is used for assignment, while '==' is used for comparison."
                ]
            ];
            
            $explanation = "## Programming Fundamentals - Complete Guide\n\n### Chapter Overview\nThis chapter introduces essential programming concepts that form the foundation of software development.\n\n### Key Topics Covered:\n\n**1. Variables and Data Types**\n- Understanding memory allocation\n- Primitive vs reference types\n- Variable naming conventions\n- Scope and lifetime\n\n**2. Control Structures**\n- Conditional statements (if, else, switch)\n- Loops (for, while, do-while)\n- Break and continue statements\n- Nested structures\n\n**3. Functions and Procedures**\n- Function definition and calling\n- Parameters and arguments\n- Return values\n- Local vs global scope\n\n**4. Basic Algorithms**\n- Sequential processing\n- Decision making\n- Iteration patterns\n- Problem decomposition\n\n### Learning Objectives:\nBy the end of this chapter, students will be able to:\n- Declare and use variables of different data types\n- Implement conditional logic using if-else statements\n- Create loops for repetitive tasks\n- Write and call functions effectively\n- Solve basic programming problems\n\n### Practical Applications:\n- User input validation\n- Mathematical calculations\n- Data processing\n- Menu-driven programs\n- Simple games and utilities\n\n### Study Tips:\n1. Practice writing code for each concept\n2. Trace through program execution step by step\n3. Experiment with different data types\n4. Build small projects combining multiple concepts\n5. Debug code to understand common errors";
            
            $ocr_content = "programming fundamentals variables data types control structures functions loops conditional statements if else while for arrays algorithms problem solving debugging syntax";
            
            // Insert AI content
            $insert_query = "INSERT INTO ai_content (resource_id, summary, important_questions, mcqs, chapter_explanation, ocr_content) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            
            $questions_json = json_encode($questions);
            $mcqs_json = json_encode($mcqs);
            
            if ($insert_stmt->execute([7, $summary, $questions_json, $mcqs_json, $explanation, $ocr_content])) {
                echo "<p>✅ AI content created successfully for resource ID 7!</p>";
                
                // Update resource with OCR content
                try {
                    $update_resource = "UPDATE resources SET ocr_content = ? WHERE id = 7";
                    $update_stmt = $conn->prepare($update_resource);
                    $update_stmt->execute([$ocr_content]);
                    echo "<p>✅ Resource updated with OCR content!</p>";
                } catch (Exception $e) {
                    echo "<p>⚠️ Could not update resource OCR content (table might not have ocr_content column)</p>";
                }
            } else {
                echo "<p>❌ Failed to create AI content</p>";
            }
        }
    } else {
        echo "<p>❌ Resource ID 7 not found. Let me show available resources:</p>";
        
        $all_resources_query = "SELECT id, title FROM resources ORDER BY id DESC LIMIT 10";
        $all_resources_stmt = $conn->prepare($all_resources_query);
        $all_resources_stmt->execute();
        $all_resources = $all_resources_stmt->fetchAll();
        
        echo "<h3>📋 Available Resources:</h3>";
        foreach ($all_resources as $res) {
            echo "<p>ID " . $res['id'] . ": " . htmlspecialchars($res['title']) . "</p>";
        }
    }
    
    echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>✅ Setup Complete!</h3>";
    echo "<p>AI content table created and sample data added.</p>";
    echo "</div>";
    
    echo "<h3>🚀 Test AI Summary Now:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='dashboard/ai_notes_summary.php?id=7' style='background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>🤖 View AI Summary (ID 7)</a>";
    echo "<a href='dashboard/student.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>📚 Student Dashboard</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
