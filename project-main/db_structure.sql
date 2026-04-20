-- Database structure for Student Event Management System
CREATE DATABASE IF NOT EXISTS student_events;
USE student_events;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    student_id VARCHAR(20) UNIQUE,
    phone VARCHAR(15),
    role ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    venue VARCHAR(200) NOT NULL,
    organizer VARCHAR(100) NOT NULL,
    max_participants INT DEFAULT 50,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- Registrations table
CREATE TABLE registrations (
    reg_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    UNIQUE KEY unique_registration (user_id, event_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) 
VALUES ('Admin', 'admin@university.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample events
INSERT INTO events (title, description, date, time, venue, organizer, max_participants, created_by) VALUES
('Web Development Workshop', 'Learn modern web development with HTML, CSS, and JavaScript', '2025-11-20', '10:00:00', 'Computer Lab A', 'CS Department', 30, 1),
('AI/ML Hackathon', '24-hour hackathon focusing on artificial intelligence and machine learning', '2025-11-25', '09:00:00', 'Innovation Hub', 'Tech Society', 50, 1),
('Career Fair 2025', 'Meet with top employers and explore career opportunities', '2025-12-01', '13:00:00', 'Main Auditorium', 'Career Services', 200, 1),
('Database Design Seminar', 'Advanced database design principles and best practices', '2025-11-18', '14:00:00', 'Lecture Hall B', 'Database Club', 40, 1),
('Mobile App Development', 'Introduction to mobile app development for Android and iOS', '2025-12-05', '09:30:00', 'Computer Lab C', 'Mobile Dev Society', 25, 1);