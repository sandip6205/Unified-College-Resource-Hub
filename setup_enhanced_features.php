<?php
// Setup Enhanced College Resource Hub Features
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h1>🚀 Setting up Enhanced College Resource Hub</h1>";

try {
    // Read and execute enhanced schema
    $schema = file_get_contents(__DIR__ . '/database/enhanced_schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $executed = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(USE|--)/i', trim($statement))) {
            try {
                $conn->exec($statement);
                $executed++;
            } catch (Exception $e) {
                // Skip if table/column already exists
                if (!strpos($e->getMessage(), 'already exists') && !strpos($e->getMessage(), 'Duplicate column')) {
                    throw $e;
                }
            }
        }
    }
    
    echo "<p>✅ Enhanced database schema updated! ($executed statements executed)</p>";
    
    // Verify new tables
    $new_tables = [
        'bookmarks', 'ratings', 'comments', 'attendance_sessions', 
        'attendance_records', 'forum_categories', 'forum_threads', 
        'forum_replies', 'exams', 'study_plans', 'user_activities', 
        'timetables', 'system_settings'
    ];
    
    foreach ($new_tables as $table) {
        $query = "SELECT COUNT(*) as count FROM $table";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>✅ Table '$table' ready with {$result['count']} records</p>";
    }
    
    echo "<h3>🎉 Enhanced Features Setup Complete!</h3>";
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h4>🌟 New Features Available:</h4>";
    echo "<ul style='margin-left: 20px; line-height: 1.8;'>";
    
    echo "<li>📚 <strong>Enhanced Dashboards</strong> - Role-based with advanced features</li>";
    echo "<li>🔍 <strong>Smart Search & Filters</strong> - Subject, semester, type, teacher filters</li>";
    echo "<li>📱 <strong>Smart Attendance System</strong> - QR code + geo-location marking</li>";
    echo "<li>🤖 <strong>AI Notes Summary</strong> - Auto-generate summaries and questions</li>";
    echo "<li>⭐ <strong>Bookmarking & Favorites</strong> - Save important resources</li>";
    echo "<li>💬 <strong>Comments & Ratings</strong> - Rate and review resources</li>";
    echo "<li>🗣️ <strong>Discussion Forum</strong> - Subject-wise doubt solving</li>";
    echo "<li>📅 <strong>Exam Planner</strong> - Smart reminders and study plans</li>";
    echo "<li>📊 <strong>Advanced Analytics</strong> - Detailed usage statistics</li>";
    echo "<li>✅ <strong>Teacher Verification</strong> - ID verification system</li>";
    echo "<li>🌙 <strong>Dark Mode</strong> - Light/dark theme toggle</li>";
    echo "<li>🤖 <strong>Enhanced AI Chatbot</strong> - Context-aware assistance</li>";
    
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🚀 Test the Enhanced Features:</h3>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='dashboard/enhanced_student.php' style='background: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>👨‍🎓 Enhanced Student Dashboard</a>";
    echo "<a href='dashboard/attendance.php' style='background: #059669; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-right: 10px; display: inline-block;'>📱 Smart Attendance</a>";
    echo "<a href='dashboard/forum.php' style='background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block;'>💬 Discussion Forum</a>";
    echo "</div>";
    
    echo "<h3>💡 Key Improvements:</h3>";
    echo "<div style='background: #f0fdf4; border: 1px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<ul style='margin-left: 20px; line-height: 1.6;'>";
    echo "<li><strong>Enhanced User Experience</strong> - Modern UI with dark mode support</li>";
    echo "<li><strong>Smart Features</strong> - AI-powered summaries and intelligent search</li>";
    echo "<li><strong>Mobile Responsive</strong> - Works perfectly on all devices</li>";
    echo "<li><strong>Real-time Features</strong> - Live notifications and updates</li>";
    echo "<li><strong>Advanced Analytics</strong> - Detailed insights and reporting</li>";
    echo "<li><strong>Security Enhanced</strong> - Better authentication and verification</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>📋 Next Steps:</h3>";
    echo "<ol style='margin-left: 20px; line-height: 1.8;'>";
    echo "<li>Test the enhanced student dashboard</li>";
    echo "<li>Try the smart attendance system</li>";
    echo "<li>Explore the discussion forum</li>";
    echo "<li>Test bookmarking and rating features</li>";
    echo "<li>Check the AI chatbot improvements</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p>❌ Error setting up enhanced features: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace: <pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre></p>";
}
?>
