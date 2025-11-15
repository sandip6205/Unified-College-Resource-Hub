<?php
session_start();
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function login($email, $password) {
        $query = "SELECT id, name, email, role, department FROM users WHERE email = ? AND password = ?";
        $stmt = $this->conn->prepare($query);
        
        // For demo purposes, using simple password check. In production, use password_hash/password_verify
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$email, $password]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_department'] = $user['department'];
            return true;
        }
        return false;
    }
    
    public function register($name, $email, $password, $role = 'student', $department = '') {
        $query = "INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        // For demo purposes, storing plain password. In production, use password_hash
        return $stmt->execute([$name, $email, $password, $role, $department]);
    }
    
    public function logout() {
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header("Location: ../login.php");
            exit();
        }
    }
    
    public function requireRole($required_role) {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== $required_role) {
            header("Location: ../dashboard/" . $_SESSION['user_role'] . ".php");
            exit();
        }
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role'],
                'department' => $_SESSION['user_department']
            ];
        }
        return null;
    }
}
?>
