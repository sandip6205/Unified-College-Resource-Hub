<?php
// Add sample resources for testing
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h1>Adding Sample Data</h1>";

try {
    // Get teacher ID
    $teacher_query = "SELECT id FROM users WHERE role = 'teacher' LIMIT 1";
    $teacher_stmt = $conn->prepare($teacher_query);
    $teacher_stmt->execute();
    $teacher = $teacher_stmt->fetch();
    
    if (!$teacher) {
        echo "<p>❌ No teacher found. Please make sure you have imported the database schema.</p>";
        exit;
    }
    
    $teacher_id = $teacher['id'];
    echo "<p>✅ Found teacher with ID: $teacher_id</p>";
    
    // Get subject IDs
    $subjects_query = "SELECT subject_id, subject_name FROM subjects";
    $subjects_stmt = $conn->prepare($subjects_query);
    $subjects_stmt->execute();
    $subjects = $subjects_stmt->fetchAll();
    
    if (empty($subjects)) {
        echo "<p>❌ No subjects found. Please make sure you have imported the database schema.</p>";
        exit;
    }
    
    echo "<p>✅ Found " . count($subjects) . " subjects</p>";
    
    // Sample resources data
    $sample_resources = [
        [
            'title' => 'Introduction to C Programming - Chapter 1',
            'subject_id' => 1, // C Programming
            'chapter' => 'Chapter 1',
            'description' => 'Basic concepts of C programming including variables, data types, and operators.',
            'tags' => '["programming", "c", "basics", "chapter1"]',
            'file_type' => 'pdf'
        ],
        [
            'title' => 'Java OOP Concepts - Complete Notes',
            'subject_id' => 2, // Java Programming
            'chapter' => 'Object Oriented Programming',
            'description' => 'Comprehensive notes on Object-Oriented Programming concepts in Java.',
            'tags' => '["java", "oop", "classes", "objects"]',
            'file_type' => 'pdf'
        ],
        [
            'title' => 'Python Data Structures - Arrays and Lists',
            'subject_id' => 3, // Python Programming
            'chapter' => 'Data Structures',
            'description' => 'Understanding arrays, lists, and their operations in Python.',
            'tags' => '["python", "data-structures", "arrays", "lists"]',
            'file_type' => 'doc'
        ],
        [
            'title' => 'Database Normalization - 1NF to 3NF',
            'subject_id' => 5, // Database Management
            'chapter' => 'Normalization',
            'description' => 'Complete guide to database normalization from 1NF to 3NF with examples.',
            'tags' => '["database", "normalization", "1nf", "2nf", "3nf"]',
            'file_type' => 'ppt'
        ],
        [
            'title' => 'Mathematics - Calculus Formulas',
            'subject_id' => 6, // Mathematics
            'chapter' => 'Calculus',
            'description' => 'Important calculus formulas and their applications.',
            'tags' => '["mathematics", "calculus", "formulas", "derivatives"]',
            'file_type' => 'pdf'
        ]
    ];
    
    // Insert sample resources
    $insert_query = "INSERT INTO resources (title, subject_id, chapter, description, tags, file_url, file_type, uploaded_by, status, download_count) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved', ?)";
    
    $inserted = 0;
    foreach ($sample_resources as $resource) {
        // Check if resource already exists
        $check_query = "SELECT id FROM resources WHERE title = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([$resource['title']]);
        
        if ($check_stmt->fetch()) {
            echo "<p>⚠️ Resource '{$resource['title']}' already exists, skipping...</p>";
            continue;
        }
        
        $file_url = 'uploads/demo_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $resource['title']) . '.' . $resource['file_type'];
        $download_count = rand(5, 50); // Random download count for demo
        
        $stmt = $conn->prepare($insert_query);
        if ($stmt->execute([
            $resource['title'],
            $resource['subject_id'],
            $resource['chapter'],
            $resource['description'],
            $resource['tags'],
            $file_url,
            $resource['file_type'],
            $teacher_id,
            $download_count
        ])) {
            $inserted++;
            echo "<p>✅ Added: {$resource['title']}</p>";
        } else {
            echo "<p>❌ Failed to add: {$resource['title']}</p>";
        }
    }
    
    echo "<h3>✅ Successfully added $inserted sample resources!</h3>";
    echo "<p><a href='dashboard/student.php'>Go to Student Dashboard</a></p>";
    echo "<p><a href='test_student.php'>Run Student Debug Test</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
