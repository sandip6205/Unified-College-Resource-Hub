<?php
// Create Sample Notifications for Testing
require_once __DIR__ . '/config/database.php';

echo "<h1>🔔 Creating Sample Notifications</h1>";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Create notifications table if it doesn't exist
    echo "<p>🔧 Creating notifications table...</p>";
    
    $create_table = "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NULL,
        user_role VARCHAR(20) NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
        seen BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_user_role (user_role),
        INDEX idx_seen (seen)
    )";
    
    $conn->exec($create_table);
    echo "<p>✅ Notifications table created!</p>";
    
    // Get a student user ID for targeted notifications
    $student_query = "SELECT id FROM users WHERE role = 'student' LIMIT 1";
    $student_stmt = $conn->prepare($student_query);
    $student_stmt->execute();
    $student = $student_stmt->fetch();
    $student_id = $student ? $student['id'] : null;
    
    // Sample notifications
    $sample_notifications = [
        // General notifications for all students
        [
            'user_id' => null,
            'user_role' => 'student',
            'title' => 'New AI Summary Feature Available!',
            'message' => 'Experience our new AI-powered notes summary with automatic question generation and MCQs.',
            'type' => 'success'
        ],
        [
            'user_id' => null,
            'user_role' => 'student',
            'title' => 'Enhanced Search System',
            'message' => 'Try our new smart search with OCR content search, advanced filters, and quick tags.',
            'type' => 'info'
        ],
        [
            'user_id' => null,
            'user_role' => 'student',
            'title' => 'Exam Schedule Updated',
            'message' => 'Mid-semester examination schedule has been updated. Check the exam planner for details.',
            'type' => 'warning'
        ],
        [
            'user_id' => null,
            'user_role' => 'student',
            'title' => 'New Resources Added',
            'message' => 'Fresh study materials for Data Structures and Programming Fundamentals have been uploaded.',
            'type' => 'info'
        ],
        [
            'user_id' => null,
            'user_role' => 'student',
            'title' => 'Smart Attendance System Live',
            'message' => 'QR code and geo-location based attendance marking is now available for all classes.',
            'type' => 'success'
        ]
    ];
    
    // Add specific notification for the student if found
    if ($student_id) {
        $sample_notifications[] = [
            'user_id' => $student_id,
            'user_role' => null,
            'title' => 'Welcome to College Resource Hub!',
            'message' => 'Your account has been set up successfully. Explore AI summaries, smart search, and more features.',
            'type' => 'success'
        ];
    }
    
    // Insert sample notifications
    echo "<p>🔔 Adding sample notifications...</p>";
    
    $insert_query = "INSERT INTO notifications (user_id, user_role, title, message, type, seen) VALUES (?, ?, ?, ?, ?, FALSE)";
    $insert_stmt = $conn->prepare($insert_query);
    
    $inserted_count = 0;
    foreach ($sample_notifications as $notification) {
        if ($insert_stmt->execute([
            $notification['user_id'],
            $notification['user_role'],
            $notification['title'],
            $notification['message'],
            $notification['type']
        ])) {
            $inserted_count++;
        }
    }
    
    echo "<p>✅ Added $inserted_count sample notifications!</p>";
    
    // Show notification statistics
    $stats_query = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN seen = FALSE THEN 1 END) as unread,
                        COUNT(CASE WHEN user_role = 'student' THEN 1 END) as for_students
                    FROM notifications";
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->execute();
    $stats = $stats_stmt->fetch();
    
    echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>🔔 Notification System Ready!</h3>";
    echo "<p><strong>Statistics:</strong></p>";
    echo "<ul style='margin-left: 20px;'>";
    echo "<li>📊 Total notifications: " . $stats['total'] . "</li>";
    echo "<li>🔴 Unread notifications: " . $stats['unread'] . "</li>";
    echo "<li>👥 Student notifications: " . $stats['for_students'] . "</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🚀 Test Notification Bell:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='dashboard/student.php' style='background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>📚 Student Dashboard</a>";
    echo "<a href='simple_login.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>🔐 Login as Student</a>";
    echo "</div>";
    
    echo "<h3>💡 How to Test:</h3>";
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<ol style='margin-left: 20px; line-height: 1.6;'>";
    echo "<li>1. Login as a student (rahul@student.edu / password)</li>";
    echo "<li>2. Look for the notification bell 🔔 in the top navigation</li>";
    echo "<li>3. You should see a red badge with the number of unread notifications</li>";
    echo "<li>4. Click the bell to see the dropdown with notifications</li>";
    echo "<li>5. Click 'Mark all as read' to clear the badge</li>";
    echo "<li>6. The bell auto-refreshes every 30 seconds</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "<h3>🔧 Features:</h3>";
    echo "<div style='background: #e0f2fe; border: 1px solid #0288d1; padding: 15px; margin: 15px 0; border-radius: 8px;'>";
    echo "<ul style='margin-left: 20px; line-height: 1.6;'>";
    echo "<li>🔔 <strong>Interactive Bell:</strong> Clickable with animated badge</li>";
    echo "<li>📋 <strong>Dropdown Menu:</strong> Shows recent notifications</li>";
    echo "<li>🎨 <strong>Color Coded:</strong> Different icons for info, success, warning, error</li>";
    echo "<li>✅ <strong>Mark as Read:</strong> Individual or bulk mark as read</li>";
    echo "<li>🔄 <strong>Auto Refresh:</strong> Updates count every 30 seconds</li>";
    echo "<li>👤 <strong>User Specific:</strong> Shows notifications for user or role</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Error Creating Notifications</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
