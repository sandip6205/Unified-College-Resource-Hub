-- Enhanced College Resource Hub Database Schema
-- Supporting all advanced features

USE college_resource_hub;

-- Enhanced Users Table
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(15);
ALTER TABLE users ADD COLUMN IF NOT EXISTS semester INT DEFAULT 1;
ALTER TABLE users ADD COLUMN IF NOT EXISTS course VARCHAR(50);
ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_document VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS preferences JSON;

-- Enhanced Resources Table
ALTER TABLE resources ADD COLUMN IF NOT EXISTS semester INT;
ALTER TABLE resources ADD COLUMN IF NOT EXISTS resource_type ENUM('notes', 'assignment', 'syllabus', 'pyq', 'video', 'slides') DEFAULT 'notes';
ALTER TABLE resources ADD COLUMN IF NOT EXISTS file_size BIGINT;
ALTER TABLE resources ADD COLUMN IF NOT EXISTS ai_summary TEXT;
ALTER TABLE resources ADD COLUMN IF NOT EXISTS ai_questions JSON;
ALTER TABLE resources ADD COLUMN IF NOT EXISTS rating_avg DECIMAL(3,2) DEFAULT 0.00;
ALTER TABLE resources ADD COLUMN IF NOT EXISTS rating_count INT DEFAULT 0;
ALTER TABLE resources ADD COLUMN IF NOT EXISTS bookmark_count INT DEFAULT 0;
ALTER TABLE resources ADD COLUMN IF NOT EXISTS view_count INT DEFAULT 0;

-- Bookmarks Table
CREATE TABLE IF NOT EXISTS bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, resource_id)
);

-- Ratings Table
CREATE TABLE IF NOT EXISTS ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (user_id, resource_id)
);

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    resource_id INT NOT NULL,
    comment TEXT NOT NULL,
    parent_id INT NULL,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
);

-- Attendance System Tables
CREATE TABLE IF NOT EXISTS attendance_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    session_name VARCHAR(100) NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    qr_code VARCHAR(255),
    location_lat DECIMAL(10, 8),
    location_lng DECIMAL(11, 8),
    location_radius INT DEFAULT 100,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attendance_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    method ENUM('qr', 'manual', 'geolocation') DEFAULT 'manual',
    location_lat DECIMAL(10, 8),
    location_lng DECIMAL(11, 8),
    is_present BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (session_id, student_id)
);

-- Discussion Forum Tables
CREATE TABLE IF NOT EXISTS forum_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    subject_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS forum_threads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_pinned BOOLEAN DEFAULT FALSE,
    is_locked BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    reply_count INT DEFAULT 0,
    last_reply_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    thread_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    parent_id INT NULL,
    is_solution BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES forum_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES forum_replies(id) ON DELETE CASCADE
);

-- Exam Planner Tables
CREATE TABLE IF NOT EXISTS exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_id INT NOT NULL,
    exam_name VARCHAR(100) NOT NULL,
    exam_type ENUM('internal', 'external', 'assignment', 'quiz') DEFAULT 'internal',
    exam_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    total_marks INT DEFAULT 100,
    syllabus TEXT,
    instructions TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS study_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    exam_id INT NOT NULL,
    plan_data JSON,
    progress JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

-- Enhanced Notifications Table
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS notification_type ENUM('resource', 'circular', 'exam', 'attendance', 'forum', 'general') DEFAULT 'general';
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS action_url VARCHAR(255);
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS metadata JSON;

-- Analytics Tables
CREATE TABLE IF NOT EXISTS user_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'download', 'upload', 'view', 'search', 'bookmark', 'comment', 'rating') NOT NULL,
    resource_id INT NULL,
    metadata JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE SET NULL,
    INDEX idx_user_activity (user_id, activity_type),
    INDEX idx_created_at (created_at)
);

-- Timetable Tables
CREATE TABLE IF NOT EXISTS timetables (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    semester INT NOT NULL,
    timetable_data JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Settings Table for System Configuration
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert Default Forum Categories
INSERT IGNORE INTO forum_categories (name, description) VALUES
('General Discussion', 'General questions and discussions'),
('Technical Help', 'Technical issues and solutions'),
('Study Groups', 'Form and join study groups'),
('Career Guidance', 'Career advice and opportunities'),
('Campus Life', 'Campus events and activities');

-- Insert Default System Settings
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'College Resource Hub', 'string', 'Website name'),
('max_file_size', '50', 'number', 'Maximum file upload size in MB'),
('attendance_radius', '100', 'number', 'Attendance marking radius in meters'),
('ai_summary_enabled', 'true', 'boolean', 'Enable AI summary generation'),
('dark_mode_enabled', 'true', 'boolean', 'Enable dark mode support'),
('forum_enabled', 'true', 'boolean', 'Enable discussion forum'),
('attendance_enabled', 'true', 'boolean', 'Enable attendance system');

-- Create Indexes for Better Performance
CREATE INDEX IF NOT EXISTS idx_resources_type ON resources(resource_type);
CREATE INDEX IF NOT EXISTS idx_resources_semester ON resources(semester);
CREATE INDEX IF NOT EXISTS idx_resources_rating ON resources(rating_avg);
CREATE INDEX IF NOT EXISTS idx_bookmarks_user ON bookmarks(user_id);
CREATE INDEX IF NOT EXISTS idx_comments_resource ON comments(resource_id);
CREATE INDEX IF NOT EXISTS idx_attendance_student ON attendance_records(student_id);
CREATE INDEX IF NOT EXISTS idx_forum_threads_category ON forum_threads(category_id);

-- Sample Data for Testing
INSERT IGNORE INTO forum_categories (name, description, subject_id) VALUES
('C Programming Help', 'Questions about C programming', 1),
('Java Discussions', 'Java programming discussions', 2),
('Python Learning', 'Python tutorials and help', 3),
('Database Queries', 'Database management help', 5);

-- Sample Exam Data
INSERT IGNORE INTO exams (subject_id, exam_name, exam_type, exam_date, start_time, end_time, total_marks, created_by) VALUES
(1, 'Mid-term Exam - C Programming', 'internal', '2024-12-20', '10:00:00', '12:00:00', 50, 2),
(2, 'Final Exam - Java Programming', 'external', '2024-12-25', '09:00:00', '12:00:00', 100, 2),
(3, 'Python Assignment', 'assignment', '2024-12-15', NULL, NULL, 25, 2);

COMMIT;
