<?php
// Setup AI Chatbot Database
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

echo "<h1>🤖 Setting up AI Chatbot</h1>";

try {
    // Read and execute chatbot schema
    $schema = file_get_contents(__DIR__ . '/database/chatbot_schema.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }
    
    echo "<p>✅ Chatbot database tables created successfully!</p>";
    
    // Check if tables exist
    $tables = ['chat_sessions', 'chat_messages', 'chatbot_knowledge'];
    foreach ($tables as $table) {
        $query = "SELECT COUNT(*) as count FROM $table";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>✅ Table '$table' created with {$result['count']} records</p>";
    }
    
    echo "<h3>🎉 Chatbot Setup Complete!</h3>";
    echo "<p>The AI chatbot is now available on all pages with the following features:</p>";
    echo "<ul style='margin-left: 20px;'>";
    echo "<li>🤖 <strong>Intelligent responses</strong> - Context-aware AI assistant</li>";
    echo "<li>💬 <strong>Floating chat widget</strong> - Available on all pages</li>";
    echo "<li>🔍 <strong>Resource help</strong> - Find and download study materials</li>";
    echo "<li>📤 <strong>Upload assistance</strong> - Help teachers upload resources</li>";
    echo "<li>🔐 <strong>Login support</strong> - Account and authentication help</li>";
    echo "<li>⚙️ <strong>Admin guidance</strong> - System management assistance</li>";
    echo "<li>📱 <strong>Mobile responsive</strong> - Works on all devices</li>";
    echo "<li>🎯 <strong>Quick replies</strong> - Fast response buttons</li>";
    echo "</ul>";
    
    echo "<h3>🚀 Test the Chatbot:</h3>";
    echo "<p>Go to any page and click the chat button in the bottom-right corner!</p>";
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='index.php' style='background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏠 Home Page</a>";
    echo "<a href='login.php' style='background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔐 Login Page</a>";
    echo "<a href='dashboard/student.php' style='background: #dc2626; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👨‍🎓 Student Dashboard</a>";
    echo "</div>";
    
    echo "<h3>💡 Try asking the chatbot:</h3>";
    echo "<ul style='margin-left: 20px;'>";
    echo "<li>\"Hello\" - Get a welcome message</li>";
    echo "<li>\"Find resources\" - Get help finding study materials</li>";
    echo "<li>\"Upload help\" - Learn how to upload files</li>";
    echo "<li>\"Login help\" - Get login assistance</li>";
    echo "<li>\"What subjects are available?\" - See available subjects</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error setting up chatbot: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure you have imported the main database schema first.</p>";
}
?>
