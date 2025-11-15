<?php
// Simple AI Chatbot API - Fixed Version
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't show errors in JSON response

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple error handling
try {
    require_once __DIR__ . '/../config/database.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

class ChatBot {
    private $conn;
    private $auth;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->auth = new Auth();
    }
    
    public function processMessage($message, $sessionId) {
        try {
            // Get or create session
            $session = $this->getOrCreateSession($sessionId);
            
            // Save user message
            $this->saveMessage($sessionId, $message, 'user');
            
            // Generate bot response
            $response = $this->generateResponse($message, $session);
            
            // Save bot response
            $this->saveMessage($sessionId, $response['message'], 'bot', $response['type'], $response['metadata']);
            
            return [
                'success' => true,
                'response' => $response
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function getOrCreateSession($sessionId) {
        // Check if session exists
        $query = "SELECT * FROM chat_sessions WHERE session_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();
        
        if (!$session) {
            // Create new session
            $userId = null;
            $userRole = 'guest';
            
            if ($this->auth->isLoggedIn()) {
                $user = $this->auth->getCurrentUser();
                $userId = $user['id'];
                $userRole = $user['role'];
            }
            
            $insertQuery = "INSERT INTO chat_sessions (session_id, user_id, user_role) VALUES (?, ?, ?)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->execute([$sessionId, $userId, $userRole]);
            
            $session = [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'user_role' => $userRole
            ];
        }
        
        return $session;
    }
    
    private function saveMessage($sessionId, $message, $sender, $type = 'text', $metadata = null) {
        $query = "INSERT INTO chat_messages (session_id, message, sender, message_type, metadata) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$sessionId, $message, $sender, $type, json_encode($metadata)]);
    }
    
    private function generateResponse($message, $session) {
        $message = strtolower(trim($message));
        
        // Get knowledge base responses
        $query = "SELECT * FROM chatbot_knowledge WHERE is_active = TRUE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $knowledge = $stmt->fetchAll();
        
        $bestMatch = null;
        $highestScore = 0;
        
        foreach ($knowledge as $item) {
            $keywords = explode(',', strtolower($item['keywords']));
            $score = 0;
            
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (strpos($message, $keyword) !== false) {
                    $score += strlen($keyword); // Longer matches get higher scores
                }
            }
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $item;
            }
        }
        
        if ($bestMatch) {
            $contextData = json_decode($bestMatch['context_data'], true);
            
            // Add personalized context based on user role
            $response = $this->personalizeResponse($bestMatch['response'], $session);
            
            return [
                'message' => $response,
                'type' => 'text',
                'metadata' => $contextData,
                'category' => $bestMatch['category']
            ];
        }
        
        // Default response if no match found
        return [
            'message' => "I'm not sure about that, but I'm here to help! 🤔 Try asking about:\n• Finding resources\n• Uploading files\n• Login help\n• System features\n\nOr type 'help' for more options.",
            'type' => 'text',
            'metadata' => [
                'quick_replies' => ['Help', 'Find Resources', 'Login Help', 'Contact Support']
            ],
            'category' => 'fallback'
        ];
    }
    
    private function personalizeResponse($response, $session) {
        $userRole = $session['user_role'];
        
        // Add role-specific information
        switch ($userRole) {
            case 'student':
                if (strpos($response, 'resources') !== false) {
                    $response .= "\n\n💡 As a student, you can download all approved resources from your dashboard!";
                }
                break;
                
            case 'teacher':
                if (strpos($response, 'upload') !== false) {
                    $response .= "\n\n👨‍🏫 You have teacher privileges to upload and manage resources!";
                }
                break;
                
            case 'admin':
                if (strpos($response, 'admin') !== false) {
                    $response .= "\n\n⚡ You have full admin access to manage the entire system!";
                }
                break;
        }
        
        return $response;
    }
    
    public function getChatHistory($sessionId, $limit = 20) {
        $query = "SELECT * FROM chat_messages WHERE session_id = ? ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$sessionId, $limit]);
        return array_reverse($stmt->fetchAll());
    }
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['message']) || !isset($input['session_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }
    
    $chatbot = new ChatBot();
    $result = $chatbot->processMessage($input['message'], $input['session_id']);
    
    echo json_encode($result);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['history'])) {
    $sessionId = $_GET['session_id'] ?? '';
    
    if (!$sessionId) {
        echo json_encode(['success' => false, 'error' => 'Session ID required']);
        exit;
    }
    
    $chatbot = new ChatBot();
    $history = $chatbot->getChatHistory($sessionId);
    
    echo json_encode(['success' => true, 'history' => $history]);
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
