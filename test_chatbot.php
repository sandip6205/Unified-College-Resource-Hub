<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Chatbot - College Resource Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php include_once 'includes/chatbot.php'; ?>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full text-center">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">🤖 Chatbot Test Page</h1>
                <p class="text-gray-600 mb-6">This page is for testing the AI chatbot functionality.</p>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-blue-800 mb-2">How to Test:</h3>
                    <ol class="text-left text-blue-700 space-y-1">
                        <li>1. Look for the blue chat button (💬) in the bottom-right corner</li>
                        <li>2. Click the button to open the chat window</li>
                        <li>3. Try typing these messages:</li>
                    </ol>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-semibold text-green-800 mb-2">Try These Messages:</h4>
                        <ul class="text-left text-green-700 text-sm space-y-1">
                            <li>• "Hello"</li>
                            <li>• "Find resources"</li>
                            <li>• "Upload help"</li>
                            <li>• "Login help"</li>
                            <li>• "What subjects are available?"</li>
                        </ul>
                    </div>
                    
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h4 class="font-semibold text-purple-800 mb-2">Expected Features:</h4>
                        <ul class="text-left text-purple-700 text-sm space-y-1">
                            <li>• Instant responses</li>
                            <li>• Quick reply buttons</li>
                            <li>• Helpful information</li>
                            <li>• Smooth animations</li>
                            <li>• Mobile responsive</li>
                        </ul>
                    </div>
                </div>
                
                <div class="flex justify-center space-x-4">
                    <a href="index.php" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-home mr-2"></i>Home Page
                    </a>
                    <a href="login.php" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login Page
                    </a>
                </div>
                
                <div class="mt-6 text-sm text-gray-500">
                    <p>If the chatbot doesn't appear, check the browser console for errors.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Manual test button -->
    <div class="fixed top-4 right-4">
        <button onclick="testChatbotAPI()" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
            Test API Directly
        </button>
    </div>
    
    <script>
        // Direct API test function
        async function testChatbotAPI() {
            try {
                const response = await fetch('api/simple_chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: 'hello',
                        session_id: 'test_123'
                    })
                });
                
                const data = await response.json();
                console.log('API Response:', data);
                
                if (data.success) {
                    alert('✅ Chatbot API is working!\\n\\nResponse: ' + data.response.message.substring(0, 100) + '...');
                } else {
                    alert('❌ API Error: ' + data.error);
                }
            } catch (error) {
                console.error('API Test Error:', error);
                alert('❌ Failed to connect to API: ' + error.message);
            }
        }
        
        // Check if chatbot loaded
        setTimeout(() => {
            if (window.collegeChatBot) {
                console.log('✅ Chatbot loaded successfully');
            } else {
                console.error('❌ Chatbot failed to load');
                alert('⚠️ Chatbot widget did not load. Check console for errors.');
            }
        }, 2000);
    </script>
</body>
</html>
