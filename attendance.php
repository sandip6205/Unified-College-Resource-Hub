<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$auth = new Auth();
$auth->requireLogin();

$database = new Database();
$conn = $database->getConnection();
$user = $auth->getCurrentUser();

// Handle attendance marking
if ($_POST && isset($_POST['mark_attendance'])) {
    $session_id = $_POST['session_id'];
    $method = $_POST['method'] ?? 'manual';
    $lat = $_POST['latitude'] ?? null;
    $lng = $_POST['longitude'] ?? null;
    
    try {
        // Check if session exists and is active
        $session_query = "SELECT * FROM attendance_sessions WHERE id = ? AND is_active = TRUE";
        $session_stmt = $conn->prepare($session_query);
        $session_stmt->execute([$session_id]);
        $session = $session_stmt->fetch();
        
        if ($session) {
            // Check if already marked
            $check_query = "SELECT id FROM attendance_records WHERE session_id = ? AND student_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->execute([$session_id, $user['id']]);
            
            if (!$check_stmt->fetch()) {
                // Mark attendance
                $insert_query = "INSERT INTO attendance_records (session_id, student_id, method, location_lat, location_lng) 
                               VALUES (?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->execute([$session_id, $user['id'], $method, $lat, $lng]);
                
                $success_message = "Attendance marked successfully!";
            } else {
                $error_message = "Attendance already marked for this session.";
            }
        } else {
            $error_message = "Invalid or inactive session.";
        }
    } catch (Exception $e) {
        $error_message = "Error marking attendance: " . $e->getMessage();
    }
}

// Get active sessions for students
if ($user['role'] === 'student') {
    $sessions_query = "SELECT ats.*, s.subject_name, u.name as teacher_name 
                      FROM attendance_sessions ats
                      JOIN subjects s ON ats.subject_id = s.subject_id
                      JOIN users u ON ats.teacher_id = u.id
                      WHERE ats.is_active = TRUE AND ats.session_date = CURDATE()
                      ORDER BY ats.start_time ASC";
} else {
    // For teachers - show their sessions
    $sessions_query = "SELECT ats.*, s.subject_name,
                      (SELECT COUNT(*) FROM attendance_records ar WHERE ar.session_id = ats.id) as attendance_count
                      FROM attendance_sessions ats
                      JOIN subjects s ON ats.subject_id = s.subject_id
                      WHERE ats.teacher_id = ? 
                      ORDER BY ats.session_date DESC, ats.start_time DESC";
}

$sessions_stmt = $conn->prepare($sessions_query);
if ($user['role'] === 'teacher') {
    $sessions_stmt->execute([$user['id']]);
} else {
    $sessions_stmt->execute();
}
$sessions = $sessions_stmt->fetchAll();

// Get attendance history for students
if ($user['role'] === 'student') {
    $history_query = "SELECT ar.*, ats.session_name, ats.session_date, s.subject_name, u.name as teacher_name
                     FROM attendance_records ar
                     JOIN attendance_sessions ats ON ar.session_id = ats.id
                     JOIN subjects s ON ats.subject_id = s.subject_id
                     JOIN users u ON ats.teacher_id = u.id
                     WHERE ar.student_id = ?
                     ORDER BY ats.session_date DESC, ar.marked_at DESC
                     LIMIT 20";
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->execute([$user['id']]);
    $attendance_history = $history_stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <?php include_once '../includes/chatbot.php'; ?>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-qrcode text-2xl text-indigo-600 mr-3"></i>
                    <span class="text-xl font-semibold">Smart Attendance</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo $user['role']; ?>.php" class="text-indigo-600 hover:text-indigo-800">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 px-4">
        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($user['role'] === 'student'): ?>
            <!-- Student View -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Active Sessions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">
                        <i class="fas fa-clock mr-2"></i>Today's Sessions
                    </h2>
                    
                    <?php if (empty($sessions)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-4"></i>
                            <p>No active sessions today</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($sessions as $session): ?>
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold"><?php echo htmlspecialchars($session['session_name']); ?></h3>
                                            <p class="text-gray-600"><?php echo htmlspecialchars($session['subject_name']); ?></p>
                                            <p class="text-sm text-gray-500">
                                                Teacher: <?php echo htmlspecialchars($session['teacher_name']); ?>
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                Time: <?php echo date('g:i A', strtotime($session['start_time'])); ?> - 
                                                <?php echo date('g:i A', strtotime($session['end_time'])); ?>
                                            </p>
                                        </div>
                                        <div class="flex flex-col space-y-2">
                                            <button onclick="markAttendanceQR(<?php echo $session['id']; ?>)" 
                                                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                                <i class="fas fa-qrcode mr-1"></i>QR Scan
                                            </button>
                                            <button onclick="markAttendanceGeo(<?php echo $session['id']; ?>)" 
                                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                                <i class="fas fa-map-marker-alt mr-1"></i>Location
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Attendance History -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">
                        <i class="fas fa-history mr-2"></i>Attendance History
                    </h2>
                    
                    <?php if (empty($attendance_history)): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-clipboard-list text-4xl mb-4"></i>
                            <p>No attendance records yet</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($attendance_history as $record): ?>
                                <div class="border-l-4 border-green-500 pl-4 py-2">
                                    <p class="font-medium"><?php echo htmlspecialchars($record['session_name']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($record['subject_name']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo date('M j, Y g:i A', strtotime($record['marked_at'])); ?> 
                                        (<?php echo ucfirst($record['method']); ?>)
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <!-- Teacher View -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>My Attendance Sessions
                    </h2>
                    <button onclick="createSession()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-plus mr-1"></i>Create Session
                    </button>
                </div>
                
                <?php if (empty($sessions)): ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-chalkboard text-4xl mb-4"></i>
                        <p>No sessions created yet</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left">Session Name</th>
                                    <th class="px-4 py-2 text-left">Subject</th>
                                    <th class="px-4 py-2 text-left">Date</th>
                                    <th class="px-4 py-2 text-left">Time</th>
                                    <th class="px-4 py-2 text-left">Attendance</th>
                                    <th class="px-4 py-2 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sessions as $session): ?>
                                    <tr class="border-t">
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($session['session_name']); ?></td>
                                        <td class="px-4 py-2"><?php echo htmlspecialchars($session['subject_name']); ?></td>
                                        <td class="px-4 py-2"><?php echo date('M j, Y', strtotime($session['session_date'])); ?></td>
                                        <td class="px-4 py-2">
                                            <?php echo date('g:i A', strtotime($session['start_time'])); ?> - 
                                            <?php echo date('g:i A', strtotime($session['end_time'])); ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                                                <?php echo $session['attendance_count'] ?? 0; ?> students
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex space-x-2">
                                                <button onclick="showQRCode(<?php echo $session['id']; ?>)" 
                                                        class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                                <button onclick="viewAttendance(<?php echo $session['id']; ?>)" 
                                                        class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- QR Code Modal -->
    <div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">QR Code for Attendance</h3>
                    <button onclick="closeQRModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="text-center">
                    <div id="qrcode" class="mb-4"></div>
                    <p class="text-sm text-gray-600">Students can scan this QR code to mark attendance</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for attendance marking -->
    <form id="attendanceForm" method="POST" style="display: none;">
        <input type="hidden" name="mark_attendance" value="1">
        <input type="hidden" name="session_id" id="sessionId">
        <input type="hidden" name="method" id="method">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
    </form>

    <script>
        function markAttendanceQR(sessionId) {
            // Simulate QR code scanning
            if (confirm('Scan QR code to mark attendance?')) {
                document.getElementById('sessionId').value = sessionId;
                document.getElementById('method').value = 'qr';
                document.getElementById('attendanceForm').submit();
            }
        }

        function markAttendanceGeo(sessionId) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('sessionId').value = sessionId;
                    document.getElementById('method').value = 'geolocation';
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('attendanceForm').submit();
                }, function(error) {
                    alert('Location access required for geo-attendance');
                });
            } else {
                alert('Geolocation not supported by this browser');
            }
        }

        function showQRCode(sessionId) {
            const qrData = `${window.location.origin}/college-hub/dashboard/attendance.php?scan=${sessionId}`;
            
            // Clear previous QR code
            document.getElementById('qrcode').innerHTML = '';
            
            // Generate new QR code
            QRCode.toCanvas(document.getElementById('qrcode'), qrData, {
                width: 200,
                height: 200
            });
            
            document.getElementById('qrModal').classList.remove('hidden');
        }

        function closeQRModal() {
            document.getElementById('qrModal').classList.add('hidden');
        }

        function createSession() {
            window.location.href = 'create_attendance_session.php';
        }

        function viewAttendance(sessionId) {
            window.location.href = `attendance_report.php?session=${sessionId}`;
        }

        // Handle QR scan from URL
        const urlParams = new URLSearchParams(window.location.search);
        const scanSessionId = urlParams.get('scan');
        if (scanSessionId) {
            markAttendanceQR(scanSessionId);
        }
    </script>
</body>
</html>
