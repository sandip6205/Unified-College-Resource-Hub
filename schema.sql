-- College Resource Hub Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS college_resource_hub;
USE college_resource_hub;

-- Users Table (Students, Teachers, Admins)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    department VARCHAR(50),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subjects Table
CREATE TABLE subjects (
    subject_id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Resources Table (Notes, Syllabus, PYQs)
CREATE TABLE resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    subject_id INT,
    chapter VARCHAR(100),
    description TEXT,
    tags JSON,
    file_url VARCHAR(500) NOT NULL,
    file_type ENUM('pdf', 'doc', 'ppt', 'image') NOT NULL,
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    download_count INT DEFAULT 0,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Notifications Table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    user_role ENUM('student', 'teacher', 'admin') NULL,
    message TEXT NOT NULL,
    resource_id INT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    seen BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (resource_id) REFERENCES resources(id)
);

-- Circulars Table
CREATE TABLE circulars (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    pdf_url VARCHAR(500),
    created_by INT,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Download History Table (Track student downloads)
CREATE TABLE download_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    resource_id INT,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (resource_id) REFERENCES resources(id)
);

-- Insert sample data
-- Default admin user
INSERT INTO users (name, email, role, department, password) VALUES 
('Admin User', 'admin@college.edu', 'admin', 'Administration', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample subjects
INSERT INTO subjects (subject_name, department) VALUES 
('C Programming', 'Computer Science'),
('Java Programming', 'Computer Science'),
('Python Programming', 'Computer Science'),
('Data Structures', 'Computer Science'),
('Database Management', 'Computer Science'),
('Mathematics', 'General'),
('Physics', 'Science'),
('Chemistry', 'Science');

-- Sample teacher
INSERT INTO users (name, email, role, department, password) VALUES 
('Dr. Sharma', 'sharma@college.edu', 'teacher', 'Computer Science', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample student
INSERT INTO users (name, email, role, department, password) VALUES 
('Rahul Kumar', 'rahul@student.edu', 'student', 'Computer Science', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
