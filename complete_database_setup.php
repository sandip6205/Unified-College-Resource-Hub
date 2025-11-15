<?php
// Complete Database Setup - Create Everything from Scratch
echo "<h1>🔧 Complete Database Setup</h1>";

try {
    // Connect to MySQL without database first
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'college_resource_hub';
    
    echo "<p>🔍 Connecting to MySQL server...</p>";
    
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to MySQL server!</p>";
    
    // Create database if it doesn't exist
    echo "<p>🔧 Creating database '$dbname'...</p>";
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");
    
    echo "<p>✅ Database '$dbname' created/selected!</p>";
    
    // Create users table
    echo "<p>🔧 Creating users table...</p>";
    $users_table = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'teacher', 'admin') NOT NULL,
        department VARCHAR(100),
        semester INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($users_table);
    
    // Create subjects table
    echo "<p>🔧 Creating subjects table...</p>";
    $subjects_table = "
    CREATE TABLE IF NOT EXISTS subjects (
        subject_id INT AUTO_INCREMENT PRIMARY KEY,
        subject_name VARCHAR(100) NOT NULL,
        department VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($subjects_table);
    
    // Create resources table
    echo "<p>🔧 Creating resources table...</p>";
    $resources_table = "
    CREATE TABLE IF NOT EXISTS resources (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        subject_id INT,
        resource_type ENUM('notes', 'assignment', 'syllabus', 'pyq', 'video') DEFAULT 'notes',
        file_url VARCHAR(500),
        uploaded_by INT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        semester INT DEFAULT 1,
        chapter VARCHAR(100),
        tags TEXT,
        download_count INT DEFAULT 0,
        rating_avg DECIMAL(3,2) DEFAULT 0.00,
        rating_count INT DEFAULT 0,
        bookmark_count INT DEFAULT 0,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
    )";
    $conn->exec($resources_table);
    
    // Create other necessary tables
    $other_tables = [
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(200),
            message TEXT,
            type VARCHAR(50) DEFAULT 'info',
            resource_id INT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (resource_id) REFERENCES resources(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS circulars (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            content TEXT,
            uploaded_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (uploaded_by) REFERENCES users(id)
        )",
        
        "CREATE TABLE IF NOT EXISTS download_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            resource_id INT,
            downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (resource_id) REFERENCES resources(id)
        )"
    ];
    
    foreach ($other_tables as $table_sql) {
        $conn->exec($table_sql);
    }
    
    echo "<p>✅ All tables created successfully!</p>";
    
    // Clear existing users and insert fresh demo data
    echo "<p>🔧 Clearing existing users and inserting demo data...</p>";
    $conn->exec("DELETE FROM users");
    
    // Insert demo users with plain text passwords
    $demo_users = [
        ['Admin User', 'admin@college.edu', 'password', 'admin', 'Administration'],
        ['Dr. Sharma', 'sharma@college.edu', 'password', 'teacher', 'Computer Science'],
        ['Rahul Kumar', 'rahul@student.edu', 'password', 'student', 'Computer Science']
    ];
    
    $insert_user = "INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_user);
    
    foreach ($demo_users as $user) {
        $stmt->execute($user);
    }
    
    // Insert demo subjects
    $conn->exec("DELETE FROM subjects");
    $demo_subjects = [
        ['C Programming', 'Computer Science'],
        ['Java Programming', 'Computer Science'],
        ['Data Structures', 'Computer Science'],
        ['Mathematics', 'General'],
        ['Physics', 'Science']
    ];
    
    $insert_subject = "INSERT INTO subjects (subject_name, department) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_subject);
    
    foreach ($demo_subjects as $subject) {
        $stmt->execute($subject);
    }
    
    echo "<p>✅ Demo data inserted successfully!</p>";
    
    // Test admin login
    echo "<h3>🧪 Testing Admin Login:</h3>";
    $test_query = "SELECT id, name, email, role, department FROM users WHERE email = ? AND password = ?";
    $test_stmt = $conn->prepare($test_query);
    $test_stmt->execute(['admin@college.edu', 'password']);
    $admin = $test_stmt->fetch();
    
    if ($admin) {
        echo "<div style='background: #f0fdf4; border: 2px solid #22c55e; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>✅ SUCCESS! Database Setup Complete!</h3>";
        echo "<p><strong>Admin user verified:</strong></p>";
        echo "<ul>";
        echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
        echo "<li><strong>Name:</strong> " . htmlspecialchars($admin['name']) . "</li>";
        echo "<li><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</li>";
        echo "<li><strong>Role:</strong> " . $admin['role'] . "</li>";
        echo "</ul>";
        echo "</div>";
        
        // Show all users
        echo "<h3>👥 All Demo Users Created:</h3>";
        $all_users_query = "SELECT id, name, email, role, department FROM users ORDER BY role, name";
        $all_users_stmt = $conn->prepare($all_users_query);
        $all_users_stmt->execute();
        $all_users = $all_users_stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
        echo "<tr style='background: #f3f4f6;'>";
        echo "<th style='padding: 10px; text-align: left;'>ID</th>";
        echo "<th style='padding: 10px; text-align: left;'>Name</th>";
        echo "<th style='padding: 10px; text-align: left;'>Email</th>";
        echo "<th style='padding: 10px; text-align: left;'>Password</th>";
        echo "<th style='padding: 10px; text-align: left;'>Role</th>";
        echo "<th style='padding: 10px; text-align: left;'>Department</th>";
        echo "</tr>";
        
        foreach ($all_users as $user) {
            $role_color = '';
            switch ($user['role']) {
                case 'admin': $role_color = 'background: #fecaca;'; break;
                case 'teacher': $role_color = 'background: #ddd6fe;'; break;
                case 'student': $role_color = 'background: #bfdbfe;'; break;
            }
            
            echo "<tr style='$role_color'>";
            echo "<td style='padding: 10px;'>" . $user['id'] . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td style='padding: 10px; font-family: monospace;'>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='padding: 10px; font-family: monospace; font-weight: bold;'>password</td>";
            echo "<td style='padding: 10px; font-weight: bold;'>" . ucfirst($user['role']) . "</td>";
            echo "<td style='padding: 10px;'>" . htmlspecialchars($user['department']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>🎉 Ready to Use!</h3>";
        echo "<p><strong>All demo credentials use password: 'password'</strong></p>";
        echo "<ul style='line-height: 1.8;'>";
        echo "<li><strong>Admin:</strong> admin@college.edu / password</li>";
        echo "<li><strong>Teacher:</strong> sharma@college.edu / password</li>";
        echo "<li><strong>Student:</strong> rahul@student.edu / password</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<div style='margin: 30px 0;'>";
        echo "<a href='login.php' style='background: #dc2626; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; margin-right: 15px; display: inline-block;'>🔐 Login Now</a>";
        echo "<a href='index.php' style='background: #059669; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; display: inline-block;'>🏠 Home Page</a>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
        echo "<h3>❌ Something went wrong!</h3>";
        echo "<p>Admin user was not created properly.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 2px solid #f87171; padding: 20px; margin: 20px 0; border-radius: 8px;'>";
    echo "<h3>❌ Database Setup Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Make sure:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>Port 3306 is not blocked</li>";
    echo "</ul>";
    echo "</div>";
}
?>
