<?php
// Simple AI Chatbot API - Reliable Version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple chatbot responses without database dependency
class SimpleChatBot {
    private $responses = [
        'greeting' => [
            'keywords' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'start'],
            'response' => "Hello! 👋 I'm your College Resource Hub assistant. How can I help you today?",
            'quick_replies' => ['Find Resources', 'Upload Help', 'Login Help', 'Contact Admin']
        ],
        'resources' => [
            'keywords' => ['find', 'search', 'download', 'notes', 'syllabus', 'pyq', 'previous year', 'study material', 'resources'],
            'response' => "I can help you find study resources! 📚\n\nTo find resources:\n1. Go to Student Dashboard\n2. Use the search bar\n3. Filter by subject or type\n4. Click download on any resource\n\nWhat subject are you looking for?",
            'quick_replies' => ['C Programming', 'Java', 'Python', 'Mathematics']
        ],
        'upload' => [
            'keywords' => ['upload', 'add', 'submit', 'share', 'teacher', 'file'],
            'response' => "To upload resources as a teacher: 📤\n\n1. Login as a teacher\n2. Go to Teacher Dashboard\n3. Fill the upload form:\n   - Title\n   - Subject\n   - Chapter (optional)\n   - Description\n   - Tags\n4. Select your file\n5. Click 'Upload Resource'\n\nNeed help with any specific step?",
            'quick_replies' => ['Login Help', 'File Types', 'Teacher Dashboard']
        ],
        'login' => [
            'keywords' => ['login', 'password', 'account', 'access', 'signin', 'credentials'],
            'response' => "Having trouble logging in? 🔐\n\n**Demo Credentials:**\n• Student: rahul@student.edu / password\n• Teacher: sharma@college.edu / password\n• Admin: admin@college.edu / password\n\n**Tips:**\n- Use the show password button (👁️)\n- Make sure caps lock is off\n- Try refreshing the page",
            'quick_replies' => ['Register Account', 'Forgot Password', 'Show Password']
        ],
        'subjects' => [
            'keywords' => ['subjects', 'courses', 'topics', 'c programming', 'java', 'python', 'mathematics', 'physics', 'chemistry'],
            'response' => "Available subjects in our system: 📖\n\n• C Programming\n• Java Programming\n• Python Programming\n• Data Structures\n• Database Management\n• Mathematics\n• Physics\n• Chemistry\n\nWhich subject interests you?",
            'quick_replies' => ['View Resources', 'Upload to Subject', 'Student Dashboard']
        ],
        'admin' => [
            'keywords' => ['admin', 'approve', 'manage', 'delete', 'users', 'control'],
            'response' => "Admin functions: ⚙️\n\n• Approve/reject resources\n• Manage users and subjects\n• Post important circulars\n• View system statistics\n• Delete inappropriate content\n\nLogin as admin to access these features!\n\nAdmin: admin@college.edu / password",
            'quick_replies' => ['Admin Login', 'User Management', 'Resource Management']
        ],
        'help' => [
            'keywords' => ['help', 'support', 'problem', 'issue', 'how', 'what', 'guide'],
            'response' => "I'm here to help! 🆘\n\n**Common topics:**\n• Finding and downloading resources\n• Uploading files (teachers)\n• Account and login issues\n• System navigation\n• Admin functions\n\nWhat specific help do you need?",
            'quick_replies' => ['Find Resources', 'Upload Help', 'Login Help', 'Technical Issue']
        ],
        'technical' => [
            'keywords' => ['error', 'bug', 'not working', 'broken', 'problem', 'issue', 'crash', 'fix'],
            'response' => "Sorry you're experiencing technical issues! 🔧\n\n**Quick fixes:**\n1. Refresh the page (F5)\n2. Clear browser cache\n3. Check internet connection\n4. Try a different browser\n5. Disable browser extensions\n\nIf problems persist, contact your system administrator.",
            'quick_replies' => ['Refresh Page', 'Clear Cache', 'Contact Admin']
        ],
        'goodbye' => [
            'keywords' => ['bye', 'goodbye', 'thanks', 'thank you', 'exit', 'quit', 'close'],
            'response' => "You're welcome! 😊 Feel free to ask if you need more help. Have a great day studying! 📚✨",
            'quick_replies' => ['Ask Another Question', 'Find Resources']
        ]
    ];
    
    public function processMessage($message) {
        $message = strtolower(trim($message));
        
        if (empty($message)) {
            return $this->getDefaultResponse();
        }
        
        $bestMatch = null;
        $highestScore = 0;
        
        foreach ($this->responses as $category => $data) {
            $score = 0;
            
            foreach ($data['keywords'] as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $score += strlen($keyword);
                }
            }
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $data;
            }
        }
        
        if ($bestMatch && $highestScore > 0) {
            return [
                'success' => true,
                'response' => [
                    'message' => $bestMatch['response'],
                    'type' => 'text',
                    'metadata' => [
                        'quick_replies' => $bestMatch['quick_replies']
                    ]
                ]
            ];
        }
        
        return $this->getDefaultResponse();
    }
    
    private function getDefaultResponse() {
        return [
            'success' => true,
            'response' => [
                'message' => "I'm not sure about that, but I'm here to help! 🤔\n\nTry asking about:\n• Finding resources\n• Uploading files\n• Login help\n• System features\n\nOr type 'help' for more options.",
                'type' => 'text',
                'metadata' => [
                    'quick_replies' => ['Help', 'Find Resources', 'Login Help', 'Upload Help']
                ]
            ]
        ];
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['message'])) {
            echo json_encode(['success' => false, 'error' => 'Message is required']);
            exit;
        }
        
        $chatbot = new SimpleChatBot();
        $result = $chatbot->processMessage($input['message']);
        
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => 'Sorry, I encountered an error. Please try again.'
        ]);
    }
    
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Only POST requests are supported'
    ]);
}
?>
