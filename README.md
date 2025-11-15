# College Resource Hub - Complete System

A comprehensive web-based platform for managing college resources with role-based access for Students, Teachers, and Admins.

## 🚀 Features

### 👥 User Roles

**Students:**
- Download notes, syllabus, and previous year questions
- Search and filter resources by subject and chapter
- View important circulars and deadlines
- Get notifications for new uploads
- Track download history

**Teachers:**
- Upload course materials (PDF, DOC, PPT, Images)
- Organize resources by subject and chapter
- Add titles, descriptions, and tags
- Track download statistics
- Manage uploaded resources

**Admins:**
- Approve/reject teacher uploads
- Manage all resources
- Create subjects and categories
- Post important circulars
- User management and system oversight

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3 (TailwindCSS), JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Icons:** Font Awesome 6.0
- **Server:** Apache (XAMPP)

## 📋 Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web browser
- Text editor (optional)

### Step 1: Setup XAMPP
1. Download and install XAMPP
2. Start Apache and MySQL services
3. Open phpMyAdmin (http://localhost/phpmyadmin)

### Step 2: Database Setup
1. Create a new database named `college_resource_hub`
2. Import the SQL schema from `database/schema.sql`
3. Or run the SQL commands directly in phpMyAdmin

### Step 3: File Setup
1. Copy all project files to `C:\xampp\htdocs\colloge rou\`
2. Ensure proper folder structure:
```
colloge rou/
├── config/
│   └── database.php
├── dashboard/
│   ├── admin.php
│   ├── student.php
│   └── teacher.php
├── database/
│   └── schema.sql
├── includes/
│   └── auth.php
├── uploads/ (create this folder)
├── download.php
├── index.php
├── login.php
├── logout.php
├── register.php
└── README.md
```

### Step 4: Create Uploads Directory
```bash
mkdir uploads
chmod 755 uploads
```

### Step 5: Database Configuration
Update `config/database.php` if needed:
```php
private $host = 'localhost';
private $db_name = 'college_resource_hub';
private $username = 'root';
private $password = '';
```

## 🎯 Usage

### Access the System
1. Open browser and go to: `http://localhost/colloge%20rou/`
2. Use demo credentials or register new account

### Demo Credentials
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@college.edu | password |
| Teacher | sharma@college.edu | password |
| Student | rahul@student.edu | password |

## 📊 Database Schema

### Users Table
- `id` (Primary Key)
- `name`, `email`, `role`, `department`, `password`
- `created_at`, `updated_at`

### Resources Table
- `id` (Primary Key)
- `title`, `subject_id`, `chapter`, `description`
- `tags` (JSON), `file_url`, `file_type`
- `uploaded_by`, `status`, `download_count`

### Subjects Table
- `subject_id` (Primary Key)
- `subject_name`, `department`

### Notifications Table
- `id` (Primary Key)
- `user_id`, `user_role`, `message`
- `resource_id`, `timestamp`, `seen`

### Circulars Table
- `id` (Primary Key)
- `title`, `content`, `pdf_url`
- `created_by`, `date`

## 🔧 System Flow

1. **Registration/Login** → Role-based dashboard redirect
2. **Student Dashboard** → Search, filter, download resources
3. **Teacher Dashboard** → Upload resources, manage uploads
4. **Admin Dashboard** → Approve resources, manage system
5. **Notification System** → Auto-notify students of new uploads

## 🎨 UI Features

- **Responsive Design** - Works on desktop, tablet, mobile
- **Modern UI** - Clean, professional interface using TailwindCSS
- **Role-based Navigation** - Different dashboards for each user type
- **Search & Filter** - Advanced filtering by subject, type, chapter
- **Real-time Stats** - Download counts, user statistics
- **Notification System** - Visual indicators for new content

## 🔒 Security Features

- Role-based access control
- Session management
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- File upload validation

## 🚀 Deployment

### For Production:
1. Use proper password hashing (password_hash/password_verify)
2. Configure secure file upload directory
3. Set up SSL certificate
4. Configure proper database credentials
5. Enable error logging
6. Set up backup system

### For Demo/Hackathon:
- System is ready to use as-is
- All features functional
- Sample data included
- Demo credentials provided

## 📱 Mobile Responsive

The system is fully responsive and works seamlessly on:
- Desktop computers
- Tablets
- Mobile phones
- Different screen sizes

## 🎯 Perfect for Hackathons

- **Complete working system** in under 2 hours
- **All major features** implemented
- **Beautiful UI** with modern design
- **Role-based functionality** working
- **Demo data** included
- **Easy setup** process

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📄 License

This project is open source and available under the MIT License.

## 🆘 Support

For issues or questions:
1. Check the README
2. Verify database connection
3. Ensure XAMPP services are running
4. Check file permissions

---

**Built with ❤️ for educational purposes**
