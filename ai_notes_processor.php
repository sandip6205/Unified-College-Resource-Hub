<?php
require_once __DIR__ . '/config/database.php';

class AINotesProcessor {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Process PDF and generate AI summary, questions, MCQs
     */
    public function processPDF($resource_id, $file_path) {
        try {
            // Extract text from PDF
            $pdf_text = $this->extractPDFText($file_path);
            
            if (empty($pdf_text)) {
                throw new Exception("Could not extract text from PDF");
            }
            
            // Generate AI content
            $summary = $this->generateSummary($pdf_text);
            $important_questions = $this->generateImportantQuestions($pdf_text);
            $mcqs = $this->generateMCQs($pdf_text);
            $chapter_explanation = $this->generateChapterExplanation($pdf_text);
            
            // Save to database
            $this->saveAIContent($resource_id, $summary, $important_questions, $mcqs, $chapter_explanation, $pdf_text);
            
            return [
                'success' => true,
                'summary' => $summary,
                'questions' => $important_questions,
                'mcqs' => $mcqs,
                'explanation' => $chapter_explanation
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Extract text from PDF using simple method
     */
    private function extractPDFText($file_path) {
        // For demo purposes, we'll simulate PDF text extraction
        // In production, you'd use libraries like pdf2text, pdftotext, or PDF parsers
        
        $sample_texts = [
            "Introduction to Programming\n\nProgramming is the process of creating instructions for computers to execute. It involves writing code using programming languages like C, Java, Python, etc.\n\nKey Concepts:\n1. Variables - Store data values\n2. Functions - Reusable blocks of code\n3. Loops - Repeat code execution\n4. Conditionals - Make decisions in code\n\nData Types:\n- Integer: Whole numbers\n- Float: Decimal numbers\n- String: Text data\n- Boolean: True/False values\n\nControl Structures:\nIf-else statements allow programs to make decisions based on conditions. Loops like for and while enable repetitive execution of code blocks.\n\nFunctions help organize code into manageable, reusable components. They can accept parameters and return values.\n\nObject-Oriented Programming introduces concepts like classes, objects, inheritance, and polymorphism for better code organization.",
            
            "Data Structures and Algorithms\n\nData structures are ways of organizing and storing data efficiently. Common data structures include:\n\n1. Arrays - Fixed-size sequential collections\n2. Linked Lists - Dynamic linear data structures\n3. Stacks - Last In First Out (LIFO) structure\n4. Queues - First In First Out (FIFO) structure\n5. Trees - Hierarchical data structures\n6. Graphs - Network of connected nodes\n\nAlgorithms are step-by-step procedures for solving problems:\n\nSorting Algorithms:\n- Bubble Sort: Simple but inefficient O(n²)\n- Quick Sort: Efficient divide-and-conquer O(n log n)\n- Merge Sort: Stable sorting algorithm O(n log n)\n\nSearching Algorithms:\n- Linear Search: Sequential search O(n)\n- Binary Search: Efficient search on sorted data O(log n)\n\nTime Complexity measures algorithm efficiency in terms of input size. Space Complexity measures memory usage.",
            
            "Database Management Systems\n\nA Database Management System (DBMS) is software that manages databases. It provides an interface between users and the database.\n\nKey Components:\n1. Database Engine - Core service for accessing data\n2. Database Schema - Structure and organization\n3. Query Processor - Interprets and executes queries\n4. Transaction Manager - Ensures ACID properties\n\nRelational Databases:\nBased on relational model using tables, rows, and columns. SQL (Structured Query Language) is used for operations.\n\nNormalization:\nProcess of organizing data to reduce redundancy:\n- 1NF: Eliminate repeating groups\n- 2NF: Remove partial dependencies\n- 3NF: Remove transitive dependencies\n\nACID Properties:\n- Atomicity: All or nothing transactions\n- Consistency: Database remains in valid state\n- Isolation: Concurrent transactions don't interfere\n- Durability: Committed changes are permanent"
        ];
        
        // Return a random sample text for demo
        return $sample_texts[array_rand($sample_texts)];
    }
    
    /**
     * Generate AI summary using simple text processing
     */
    private function generateSummary($text) {
        $sentences = preg_split('/[.!?]+/', $text);
        $sentences = array_filter(array_map('trim', $sentences));
        
        // Extract key sentences (first sentence of each paragraph and sentences with keywords)
        $keywords = ['introduction', 'important', 'key', 'main', 'concept', 'definition', 'algorithm', 'structure'];
        $summary_sentences = [];
        
        foreach ($sentences as $sentence) {
            if (strlen($sentence) > 20) {
                foreach ($keywords as $keyword) {
                    if (stripos($sentence, $keyword) !== false) {
                        $summary_sentences[] = $sentence;
                        break;
                    }
                }
            }
        }
        
        // If no keyword matches, take first few sentences
        if (empty($summary_sentences)) {
            $summary_sentences = array_slice($sentences, 0, 3);
        }
        
        return implode('. ', array_slice($summary_sentences, 0, 5)) . '.';
    }
    
    /**
     * Generate important questions based on content
     */
    private function generateImportantQuestions($text) {
        $questions = [];
        
        // Extract topics and generate questions
        if (stripos($text, 'programming') !== false) {
            $questions = [
                "What is programming and why is it important?",
                "Explain the different data types used in programming.",
                "What are the main control structures in programming?",
                "How do functions help in code organization?",
                "What is the difference between variables and constants?"
            ];
        } elseif (stripos($text, 'data structure') !== false) {
            $questions = [
                "What are data structures and why are they important?",
                "Compare and contrast arrays and linked lists.",
                "Explain the working of stack and queue data structures.",
                "What is the time complexity of different sorting algorithms?",
                "How does binary search work and what are its advantages?"
            ];
        } elseif (stripos($text, 'database') !== false) {
            $questions = [
                "What is a Database Management System (DBMS)?",
                "Explain the ACID properties of database transactions.",
                "What is normalization and why is it important?",
                "Describe the different types of database relationships.",
                "What is the difference between SQL and NoSQL databases?"
            ];
        } else {
            $questions = [
                "What are the main concepts covered in this chapter?",
                "Explain the key terminology and definitions.",
                "What are the practical applications of these concepts?",
                "How do these concepts relate to real-world scenarios?",
                "What are the advantages and disadvantages discussed?"
            ];
        }
        
        return $questions;
    }
    
    /**
     * Generate Multiple Choice Questions
     */
    private function generateMCQs($text) {
        $mcqs = [];
        
        if (stripos($text, 'programming') !== false) {
            $mcqs = [
                [
                    'question' => 'Which of the following is NOT a programming data type?',
                    'options' => ['Integer', 'String', 'Boolean', 'Algorithm'],
                    'correct' => 3,
                    'explanation' => 'Algorithm is a procedure, not a data type.'
                ],
                [
                    'question' => 'What does LIFO stand for in programming?',
                    'options' => ['Last In First Out', 'Last In Final Out', 'Linear In First Out', 'Loop In Function Out'],
                    'correct' => 0,
                    'explanation' => 'LIFO stands for Last In First Out, used in stack data structure.'
                ],
                [
                    'question' => 'Which control structure is used for making decisions?',
                    'options' => ['Loop', 'Function', 'If-else', 'Variable'],
                    'correct' => 2,
                    'explanation' => 'If-else statements are used for conditional execution.'
                ]
            ];
        } elseif (stripos($text, 'data structure') !== false) {
            $mcqs = [
                [
                    'question' => 'What is the time complexity of binary search?',
                    'options' => ['O(n)', 'O(log n)', 'O(n²)', 'O(1)'],
                    'correct' => 1,
                    'explanation' => 'Binary search has O(log n) time complexity as it divides the search space in half each time.'
                ],
                [
                    'question' => 'Which data structure follows FIFO principle?',
                    'options' => ['Stack', 'Queue', 'Array', 'Tree'],
                    'correct' => 1,
                    'explanation' => 'Queue follows First In First Out (FIFO) principle.'
                ],
                [
                    'question' => 'What is the worst-case time complexity of bubble sort?',
                    'options' => ['O(n)', 'O(log n)', 'O(n log n)', 'O(n²)'],
                    'correct' => 3,
                    'explanation' => 'Bubble sort has O(n²) worst-case time complexity.'
                ]
            ];
        } elseif (stripos($text, 'database') !== false) {
            $mcqs = [
                [
                    'question' => 'Which of the following is NOT an ACID property?',
                    'options' => ['Atomicity', 'Consistency', 'Isolation', 'Accessibility'],
                    'correct' => 3,
                    'explanation' => 'ACID properties are Atomicity, Consistency, Isolation, and Durability.'
                ],
                [
                    'question' => 'What does SQL stand for?',
                    'options' => ['Structured Query Language', 'Simple Query Language', 'Standard Query Language', 'System Query Language'],
                    'correct' => 0,
                    'explanation' => 'SQL stands for Structured Query Language.'
                ],
                [
                    'question' => 'Which normal form eliminates partial dependencies?',
                    'options' => ['1NF', '2NF', '3NF', 'BCNF'],
                    'correct' => 1,
                    'explanation' => 'Second Normal Form (2NF) eliminates partial dependencies.'
                ]
            ];
        } else {
            $mcqs = [
                [
                    'question' => 'What is the main topic of this chapter?',
                    'options' => ['Programming', 'Mathematics', 'Science', 'General Knowledge'],
                    'correct' => 0,
                    'explanation' => 'Based on the content analysis.'
                ]
            ];
        }
        
        return $mcqs;
    }
    
    /**
     * Generate detailed chapter explanation
     */
    private function generateChapterExplanation($text) {
        $lines = explode("\n", $text);
        $explanation = "## Chapter Overview\n\n";
        
        // Extract main topics
        $topics = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 5 && (preg_match('/^\d+\./', $line) || preg_match('/^[A-Z][a-z]+:/', $line))) {
                $topics[] = $line;
            }
        }
        
        if (!empty($topics)) {
            $explanation .= "### Key Topics Covered:\n";
            foreach ($topics as $topic) {
                $explanation .= "- " . $topic . "\n";
            }
            $explanation .= "\n";
        }
        
        $explanation .= "### Detailed Explanation:\n\n";
        $explanation .= "This chapter provides a comprehensive overview of the subject matter. ";
        $explanation .= "The content is structured to build understanding progressively, starting with fundamental concepts and advancing to more complex topics.\n\n";
        
        $explanation .= "### Learning Objectives:\n";
        $explanation .= "- Understand the core concepts and terminology\n";
        $explanation .= "- Apply theoretical knowledge to practical scenarios\n";
        $explanation .= "- Analyze and solve related problems\n";
        $explanation .= "- Develop critical thinking skills in the subject area\n\n";
        
        $explanation .= "### Study Tips:\n";
        $explanation .= "1. Review the summary regularly\n";
        $explanation .= "2. Practice with the provided MCQs\n";
        $explanation .= "3. Attempt the important questions\n";
        $explanation .= "4. Create mind maps for better retention\n";
        $explanation .= "5. Discuss concepts with peers for deeper understanding\n";
        
        return $explanation;
    }
    
    /**
     * Save AI-generated content to database
     */
    private function saveAIContent($resource_id, $summary, $questions, $mcqs, $explanation, $ocr_content) {
        try {
            // Create AI content table if it doesn't exist
            $this->createAIContentTable();
            
            // Insert AI content
            $insert_query = "INSERT INTO ai_content (resource_id, summary, important_questions, mcqs, chapter_explanation, ocr_content) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_query);
            
            $questions_json = json_encode($questions);
            $mcqs_json = json_encode($mcqs);
            
            $insert_stmt->execute([$resource_id, $summary, $questions_json, $mcqs_json, $explanation, $ocr_content]);
            
            // Update resource with OCR content for search
            try {
                $update_resource = "UPDATE resources SET ocr_content = ? WHERE id = ?";
                $update_stmt = $this->conn->prepare($update_resource);
                $update_stmt->execute([$ocr_content, $resource_id]);
            } catch (Exception $e) {
                // Continue even if OCR update fails
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to save AI content: " . $e->getMessage());
        }
    }
    
    /**
     * Get AI content for a resource
     */
    public function getAIContent($resource_id) {
        try {
            // Create table if it doesn't exist
            $this->createAIContentTable();
            
            $query = "SELECT * FROM ai_content WHERE resource_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$resource_id]);
            
            $result = $stmt->fetch();
            if ($result) {
                $result['important_questions'] = json_decode($result['important_questions'], true);
                $result['mcqs'] = json_decode($result['mcqs'], true);
            }
            
            return $result;
        } catch (Exception $e) {
            // If table doesn't exist or other error, return null
            return null;
        }
    }
    
    /**
     * Create AI content table if it doesn't exist
     */
    private function createAIContentTable() {
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
        $this->conn->exec($create_table);
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $processor = new AINotesProcessor();
    
    switch ($_POST['action']) {
        case 'process_pdf':
            $resource_id = $_POST['resource_id'] ?? 0;
            $file_path = $_POST['file_path'] ?? '';
            
            if ($resource_id && $file_path) {
                $result = $processor->processPDF($resource_id, $file_path);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing parameters']);
            }
            break;
            
        case 'get_ai_content':
            $resource_id = $_POST['resource_id'] ?? 0;
            
            if ($resource_id) {
                $content = $processor->getAIContent($resource_id);
                echo json_encode(['success' => true, 'content' => $content]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Missing resource ID']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>
