<?php
// Chatbot Integration Include File
// Add this to any page to include the AI chatbot

// Only include chatbot assets once
if (!defined('CHATBOT_INCLUDED')) {
    define('CHATBOT_INCLUDED', true);
    
    // Get the base URL for assets
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . '://' . $host;
    
    // Determine the correct path based on current directory
    $current_dir = dirname($_SERVER['SCRIPT_NAME']);
    $asset_path = '';
    
    // Adjust path based on directory depth
    if (strpos($current_dir, '/dashboard') !== false) {
        $asset_path = '../assets/';
    } elseif (strpos($current_dir, '/api') !== false) {
        $asset_path = '../assets/';
    } else {
        $asset_path = 'assets/';
    }
    
    echo '
    <!-- AI Chatbot Styles -->
    <link rel="stylesheet" href="' . $asset_path . 'chatbot.css">
    
    <!-- AI Chatbot Script -->
    <script src="' . $asset_path . 'chatbot.js"></script>
    
    <!-- Chatbot Initialization -->
    <script>
        // Initialize chatbot with context
        document.addEventListener("DOMContentLoaded", function() {
            // Add page context to chatbot
            if (window.collegeChatBot) {
                window.collegeChatBot.pageContext = {
                    page: "' . basename($_SERVER['SCRIPT_NAME'], '.php') . '",
                    url: "' . $_SERVER['REQUEST_URI'] . '",
                    timestamp: ' . time() . '
                };
            }
        });
    </script>
    ';
}
?>
