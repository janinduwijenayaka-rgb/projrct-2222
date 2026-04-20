# Student Event Management System

A comprehensive web application for managing university student events, built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features

### Front-End Features
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- **Interactive Forms**: Client-side validation with JavaScript
- **Dynamic Content**: Real-time event filtering and search
- **Modern UI**: Clean, professional design with smooth animations

### Back-End Features
- **User Authentication**: Secure login/registration system
- **Event Management**: Full CRUD operations for events
- **Role-Based Access**: Student and Admin user roles
- **Event Registration**: Students can register/cancel event participation
- **Dashboard**: Comprehensive admin dashboard with statistics

### Security Features
- **Password Hashing**: Secure password storage using PHP's password_hash()
- **Prepared Statements**: SQL injection protection with PDO
- **Session Management**: Secure user session handling
- **Input Validation**: Both client-side and server-side validation

## Database Structure

### Tables
1. **users**: User accounts (students and admins)
2. **events**: Event information and details
3. **registrations**: Event registration records

## Installation Instructions

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Steps

1. **Clone/Download the project**
   ```
   Place the project folder in your web server's document root
   (e.g., C:\xampp\htdocs\project for XAMPP)
   ```

2. **Database Setup**
   - Start Apache and MySQL services in XAMPP
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database structure from `db_structure.sql`

3. **Configuration**
   - Update database credentials in `includes/db.php` if needed
   - Default settings work for standard XAMPP installation

4. **Access the Application**
   - Main site: http://localhost/project/
   - Admin panel: http://localhost/project/admin/

### Default Login Credentials

**Admin Account:**
- Email: admin@university.edu
- Password: admin123

**Student Account:**
- Register a new account or create one manually

## File Structure

```
project/
├── index.php                 # Home page
├── login.php                 # Student login
├── register.php              # Student registration
├── events.php                # Events listing
├── event_details.php         # User's registered events
├── logout.php                # Logout functionality
├── db_structure.sql          # Database schema
├── README.md                 # This file
├── includes/
│   ├── db.php               # Database connection
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── assets/
│   ├── css/
│   │   └── styles.css       # Main stylesheet
│   └── js/
│       └── scripts.js       # JavaScript functionality
└── admin/
    ├── login.php            # Admin login
    ├── dashboard.php        # Admin dashboard
    ├── add_event.php        # Add new event
    ├── edit_event.php       # Edit existing event
    └── delete_event.php     # Delete event
```

## Key Features Demonstrated

### HTML5 & CSS3
- Semantic HTML structure
- Responsive design with CSS Grid and Flexbox
- CSS animations and transitions
- Bootstrap 5 integration

### JavaScript & DOM Manipulation
- Form validation
- Dynamic content filtering
- AJAX functionality (registration)
- Event listeners and DOM manipulation

### PHP & Server-Side Logic
- Session management
- User authentication
- Database operations with PDO
- Input sanitization and validation

### MySQL Database
- Relational database design
- Foreign key constraints
- Complex queries with JOINs
- Data integrity and normalization

## Assignment Requirements Met

✅ **Front-End (HTML, CSS, JavaScript)**
- Home page with event overview
- Event listing page with filtering
- Registration forms with validation
- Responsive layout using Bootstrap
- Client-side form validation

✅ **Back-End (PHP, MySQL)**
- Three relational tables (users, events, registrations)
- User login and registration system
- Event CRUD operations for admins
- Registration data insertion
- Display of registered participants
- Prepared statements for security

✅ **Additional Features**
- Search and filter events
- Admin dashboard with analytics
- Event registration management
- Responsive design for all devices
- Secure authentication system

## Usage Guide

### For Students:
1. Register for an account
2. Browse available events
3. Register for events of interest
4. View your registered events in "My Events"
5. Cancel registrations if needed

### For Admins:
1. Login with admin credentials
2. View dashboard statistics
3. Add new events
4. Edit existing events
5. Delete events (removes all registrations)

## Browser Compatibility
- Chrome (recommended)
- Firefox
- Safari
- Edge

## Security Considerations
- All user inputs are validated and sanitized
- Passwords are hashed using PHP's password_hash()
- SQL queries use prepared statements
- Session security measures implemented
- XSS protection through htmlspecialchars()

## Future Enhancements
- Email confirmation system
- Event categories and tags
- Advanced reporting and analytics
- File upload for event images
- Calendar integration
- Mobile app development

---

**Developed for**: Database and Web Systems Course  
**Technologies**: HTML5, CSS3, JavaScript, PHP, MySQL, Bootstrap 5