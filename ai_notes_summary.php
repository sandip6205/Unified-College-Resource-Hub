<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../ai_notes_processor.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();
$user = $auth->getCurrentUser();

$processor = new AINotesProcessor();

// Get resource ID from URL
$resource_id = $_GET['id'] ?? 0;

if (!$resource_id) {
    header('Location: student.php');
    exit;
}

// Get resource details
$resource_query = "SELECT r.*, s.subject_name, u.name as uploaded_by_name 
                  FROM resources r 
                  LEFT JOIN subjects s ON r.subject_id = s.subject_id 
                  LEFT JOIN users u ON r.uploaded_by = u.id 
                  WHERE r.id = ?";
$resource_stmt = $conn->prepare($resource_query);
$resource_stmt->execute([$resource_id]);
$resource = $resource_stmt->fetch();

if (!$resource) {
    header('Location: student.php');
    exit;
}

// Get or generate AI content
$ai_content = $processor->getAIContent($resource_id);

// If no AI content exists, generate it
if (!$ai_content && $resource['file_url']) {
    $file_path = __DIR__ . '/../' . $resource['file_url'];
    if (file_exists($file_path)) {
        $result = $processor->processPDF($resource_id, $file_path);
        if ($result['success']) {
            $ai_content = $processor->getAIContent($resource_id);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Notes Summary - <?php echo htmlspecialchars($resource['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <?php include_once '../includes/chatbot.php'; ?>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?php echo $user['role']; ?>.php" class="flex items-center">
                        <i class="fas fa-robot text-2xl text-purple-600 mr-3"></i>
                        <span class="text-xl font-semibold">AI Notes Summary</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $user['role']; ?>.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../logout.php" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Resource Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($resource['title']); ?></h1>
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                        <span><i class="fas fa-book mr-1"></i><?php echo htmlspecialchars($resource['subject_name']); ?></span>
                        <span><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($resource['uploaded_by_name']); ?></span>
                        <span><i class="fas fa-calendar mr-1"></i><?php echo date('M j, Y', strtotime($resource['uploaded_at'])); ?></span>
                        <?php if ($resource['chapter']): ?>
                            <span><i class="fas fa-bookmark mr-1"></i><?php echo htmlspecialchars($resource['chapter']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($resource['description']): ?>
                        <p class="text-gray-700"><?php echo htmlspecialchars($resource['description']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="ml-6">
                    <a href="../download.php?id=<?php echo $resource['id']; ?>" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-download mr-2"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>

        <?php if ($ai_content): ?>
            <!-- AI Content Tabs -->
            <div class="bg-white rounded-lg shadow-md mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6">
                        <button onclick="showTab('summary')" id="summary-tab" class="tab-button py-4 px-1 border-b-2 border-purple-500 font-medium text-sm text-purple-600">
                            <i class="fas fa-file-alt mr-2"></i>Summary
                        </button>
                        <button onclick="showTab('questions')" id="questions-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-question-circle mr-2"></i>Important Questions
                        </button>
                        <button onclick="showTab('mcqs')" id="mcqs-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-list-check mr-2"></i>MCQs
                        </button>
                        <button onclick="showTab('explanation')" id="explanation-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <i class="fas fa-book-open mr-2"></i>Chapter Explanation
                        </button>
                    </nav>
                </div>

                <!-- Summary Tab -->
                <div id="summary-content" class="tab-content p-6">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">
                        <i class="fas fa-magic text-purple-600 mr-2"></i>AI-Generated Summary
                    </h3>
                    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
                        <p class="text-gray-800 leading-relaxed"><?php echo htmlspecialchars($ai_content['summary']); ?></p>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-2">
                                <i class="fas fa-lightbulb mr-2"></i>Key Insights
                            </h4>
                            <ul class="text-blue-700 space-y-1">
                                <li>• Comprehensive coverage of core concepts</li>
                                <li>• Practical examples and applications</li>
                                <li>• Step-by-step explanations</li>
                                <li>• Real-world relevance</li>
                            </ul>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-800 mb-2">
                                <i class="fas fa-target mr-2"></i>Learning Outcomes
                            </h4>
                            <ul class="text-green-700 space-y-1">
                                <li>• Understand fundamental principles</li>
                                <li>• Apply concepts to solve problems</li>
                                <li>• Analyze different approaches</li>
                                <li>• Develop critical thinking skills</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Important Questions Tab -->
                <div id="questions-content" class="tab-content p-6 hidden">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">
                        <i class="fas fa-question-circle text-blue-600 mr-2"></i>Important Questions for Practice
                    </h3>
                    <div class="space-y-4">
                        <?php foreach ($ai_content['important_questions'] as $index => $question): ?>
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-1">
                                        <?php echo $index + 1; ?>
                                    </span>
                                    <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($question); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-semibold text-yellow-800 mb-2">
                            <i class="fas fa-tips mr-2"></i>Study Tips
                        </h4>
                        <ul class="text-yellow-700 space-y-1">
                            <li>• Practice answering these questions in your own words</li>
                            <li>• Create detailed answers with examples</li>
                            <li>• Discuss these questions with classmates</li>
                            <li>• Use these as revision checkpoints</li>
                        </ul>
                    </div>
                </div>

                <!-- MCQs Tab -->
                <div id="mcqs-content" class="tab-content p-6 hidden">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">
                        <i class="fas fa-list-check text-green-600 mr-2"></i>Multiple Choice Questions
                    </h3>
                    <div class="space-y-6" id="mcq-container">
                        <?php foreach ($ai_content['mcqs'] as $index => $mcq): ?>
                            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                                <div class="mb-4">
                                    <h4 class="font-semibold text-gray-800 mb-3">
                                        <span class="bg-green-500 text-white rounded-full w-6 h-6 inline-flex items-center justify-center text-sm font-bold mr-2">
                                            <?php echo $index + 1; ?>
                                        </span>
                                        <?php echo htmlspecialchars($mcq['question']); ?>
                                    </h4>
                                    
                                    <div class="space-y-2">
                                        <?php foreach ($mcq['options'] as $opt_index => $option): ?>
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer mcq-option" data-mcq="<?php echo $index; ?>" data-option="<?php echo $opt_index; ?>">
                                                <input type="radio" name="mcq_<?php echo $index; ?>" value="<?php echo $opt_index; ?>" class="mr-3">
                                                <span class="font-medium mr-2"><?php echo chr(65 + $opt_index); ?>.</span>
                                                <span><?php echo htmlspecialchars($option); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="hidden mcq-explanation bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg" id="explanation_<?php echo $index; ?>">
                                    <p class="text-blue-800">
                                        <strong>Correct Answer:</strong> <?php echo chr(65 + $mcq['correct']); ?>. <?php echo htmlspecialchars($mcq['options'][$mcq['correct']]); ?>
                                    </p>
                                    <p class="text-blue-700 mt-2">
                                        <strong>Explanation:</strong> <?php echo htmlspecialchars($mcq['explanation']); ?>
                                    </p>
                                </div>
                                
                                <button onclick="checkAnswer(<?php echo $index; ?>, <?php echo $mcq['correct']; ?>)" 
                                        class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 check-btn" 
                                        id="check_<?php echo $index; ?>">
                                    Check Answer
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <button onclick="resetMCQs()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            <i class="fas fa-redo mr-2"></i>Reset All
                        </button>
                    </div>
                </div>

                <!-- Chapter Explanation Tab -->
                <div id="explanation-content" class="tab-content p-6 hidden">
                    <h3 class="text-xl font-semibold mb-4 text-gray-800">
                        <i class="fas fa-book-open text-indigo-600 mr-2"></i>Detailed Chapter Explanation
                    </h3>
                    <div class="prose max-w-none">
                        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded-r-lg" id="explanation-text">
                            <?php echo nl2br(htmlspecialchars($ai_content['chapter_explanation'])); ?>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-red-50 p-4 rounded-lg text-center">
                            <i class="fas fa-brain text-2xl text-red-600 mb-2"></i>
                            <h4 class="font-semibold text-red-800">Conceptual Learning</h4>
                            <p class="text-red-700 text-sm">Deep understanding of core principles</p>
                        </div>
                        
                        <div class="bg-yellow-50 p-4 rounded-lg text-center">
                            <i class="fas fa-tools text-2xl text-yellow-600 mb-2"></i>
                            <h4 class="font-semibold text-yellow-800">Practical Application</h4>
                            <p class="text-yellow-700 text-sm">Real-world implementation skills</p>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg text-center">
                            <i class="fas fa-chart-line text-2xl text-purple-600 mb-2"></i>
                            <h4 class="font-semibold text-purple-800">Progressive Learning</h4>
                            <p class="text-purple-700 text-sm">Building knowledge step by step</p>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- No AI Content Available -->
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="mb-6">
                    <i class="fas fa-robot text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">AI Processing Required</h3>
                    <p class="text-gray-600">AI summary and analysis is not yet available for this resource.</p>
                </div>
                
                <button onclick="processResource()" id="process-btn" 
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-magic mr-2"></i>Generate AI Summary
                </button>
                
                <div id="processing-status" class="hidden mt-4">
                    <div class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Processing PDF with AI...
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.add('hidden'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab-button');
            tabs.forEach(tab => {
                tab.classList.remove('border-purple-500', 'text-purple-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Add active class to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-purple-500', 'text-purple-600');
        }

        // MCQ functionality
        function checkAnswer(mcqIndex, correctAnswer) {
            const selectedOption = document.querySelector(`input[name="mcq_${mcqIndex}"]:checked`);
            
            if (!selectedOption) {
                alert('Please select an answer first!');
                return;
            }
            
            const selectedValue = parseInt(selectedOption.value);
            const options = document.querySelectorAll(`label[data-mcq="${mcqIndex}"]`);
            
            // Reset all options
            options.forEach(option => {
                option.classList.remove('bg-green-100', 'bg-red-100', 'border-green-500', 'border-red-500');
            });
            
            // Highlight correct and incorrect answers
            options.forEach((option, index) => {
                if (index === correctAnswer) {
                    option.classList.add('bg-green-100', 'border-green-500');
                } else if (index === selectedValue && selectedValue !== correctAnswer) {
                    option.classList.add('bg-red-100', 'border-red-500');
                }
            });
            
            // Show explanation
            document.getElementById(`explanation_${mcqIndex}`).classList.remove('hidden');
            
            // Disable check button
            const checkBtn = document.getElementById(`check_${mcqIndex}`);
            checkBtn.disabled = true;
            checkBtn.textContent = selectedValue === correctAnswer ? '✓ Correct!' : '✗ Incorrect';
            checkBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            checkBtn.classList.add(selectedValue === correctAnswer ? 'bg-green-500' : 'bg-red-500');
        }

        function resetMCQs() {
            // Reset all MCQ states
            const options = document.querySelectorAll('.mcq-option');
            options.forEach(option => {
                option.classList.remove('bg-green-100', 'bg-red-100', 'border-green-500', 'border-red-500');
            });
            
            // Hide all explanations
            const explanations = document.querySelectorAll('.mcq-explanation');
            explanations.forEach(exp => exp.classList.add('hidden'));
            
            // Reset all radio buttons
            const radios = document.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => radio.checked = false);
            
            // Reset all check buttons
            const checkBtns = document.querySelectorAll('.check-btn');
            checkBtns.forEach(btn => {
                btn.disabled = false;
                btn.textContent = 'Check Answer';
                btn.classList.remove('bg-green-500', 'bg-red-500');
                btn.classList.add('bg-green-600', 'hover:bg-green-700');
            });
        }

        // Process resource with AI
        function processResource() {
            const processBtn = document.getElementById('process-btn');
            const statusDiv = document.getElementById('processing-status');
            
            processBtn.style.display = 'none';
            statusDiv.classList.remove('hidden');
            
            // Simulate AI processing (in real implementation, this would be an AJAX call)
            setTimeout(() => {
                location.reload();
            }, 3000);
        }

        // Convert markdown to HTML for explanation
        document.addEventListener('DOMContentLoaded', function() {
            const explanationText = document.getElementById('explanation-text');
            if (explanationText && typeof marked !== 'undefined') {
                const markdownText = explanationText.textContent;
                explanationText.innerHTML = marked.parse(markdownText);
            }
        });
    </script>
</body>
</html>
