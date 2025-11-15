-- AI Chatbot Database Schema
USE college_resource_hub;

-- Chat Sessions Table
CREATE TABLE IF NOT EXISTS chat_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    session_id VARCHAR(100) NOT NULL,
    user_role ENUM('student', 'teacher', 'admin', 'guest') DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Chat Messages Table
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    sender ENUM('user', 'bot') NOT NULL,
    message_type ENUM('text', 'quick_reply', 'resource_link') DEFAULT 'text',
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_created_at (created_at)
);

-- Chatbot Knowledge Base
CREATE TABLE IF NOT EXISTS chatbot_knowledge (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    keywords TEXT NOT NULL,
    response TEXT NOT NULL,
    context_data JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default knowledge base
INSERT INTO chatbot_knowledge (category, keywords, response, context_data) VALUES
('greeting', 'hello,hi,hey,good morning,good afternoon,good evening', 'Hello! 👋 I''m your College Resource Hub assistant. How can I help you today?', '{"quick_replies": ["Find Resources", "Upload Help", "Account Help", "Contact Admin"]}'),

('resources', 'find,search,download,notes,syllabus,pyq,previous year,study material', 'I can help you find study resources! 📚 What subject are you looking for? You can also use the search filters on the student dashboard.', '{"quick_replies": ["C Programming", "Java", "Python", "Mathematics", "All Subjects"]}'),

('upload', 'upload,add,submit,share,teacher', 'To upload resources: 📤\n1. Login as a teacher\n2. Go to Teacher Dashboard\n3. Fill the upload form\n4. Select your file\n5. Click Upload Resource\n\nNeed help with any specific step?', '{"quick_replies": ["Login Help", "File Types", "Upload Error"]}'),

('login', 'login,password,account,access,signin', 'Having trouble logging in? 🔐\n\nDemo Credentials:\n• Student: rahul@student.edu / password\n• Teacher: sharma@college.edu / password\n• Admin: admin@college.edu / password\n\nTry the show password button if you''re having trouble!', '{"quick_replies": ["Register Account", "Forgot Password", "Role Help"]}'),

('subjects', 'subjects,courses,topics,c programming,java,python,mathematics,physics,chemistry', 'Available subjects in our system: 📖\n• C Programming\n• Java Programming\n• Python Programming\n• Data Structures\n• Database Management\n• Mathematics\n• Physics\n• Chemistry\n\nWhich subject interests you?', '{"quick_replies": ["View Resources", "Upload to Subject", "Add New Subject"]}'),

('admin', 'admin,approve,manage,delete,users,control', 'Admin functions: ⚙️\n• Approve/reject resources\n• Manage users\n• Add subjects\n• Post circulars\n• System statistics\n\nLogin as admin to access these features!', '{"quick_replies": ["Admin Login", "User Management", "Resource Management"]}'),

('help', 'help,support,problem,issue,error,trouble', 'I''m here to help! 🆘 Common topics:\n• Finding and downloading resources\n• Uploading files (teachers)\n• Account and login issues\n• System navigation\n• Admin functions\n\nWhat specific help do you need?', '{"quick_replies": ["Technical Issue", "How to Use", "Contact Support"]}'),

('technical', 'error,bug,not working,broken,problem,issue,crash', 'Sorry you''re experiencing technical issues! 🔧\n\nCommon solutions:\n1. Refresh the page\n2. Clear browser cache\n3. Check internet connection\n4. Try different browser\n\nIf problems persist, contact your system administrator.', '{"quick_replies": ["Refresh Page", "Clear Cache", "Contact Admin"]}'),

('goodbye', 'bye,goodbye,thanks,thank you,exit,quit', 'You''re welcome! 😊 Feel free to ask if you need more help. Have a great day studying! 📚✨', '{"quick_replies": ["Ask Another Question", "Close Chat"]}');
