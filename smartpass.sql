-- =====================================================================
-- SMART COLLEGE EVENT PASSPORT - MySQL Database Schema
-- For use with XAMPP / phpMyAdmin
-- =====================================================================

CREATE DATABASE IF NOT EXISTS smartpass
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE smartpass;

-- ---------------------------------------------------------------------
-- ADMINS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  admin_id       INT AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- STUDENTS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  student_id      INT AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(100) NOT NULL,
  register_number VARCHAR(30) NOT NULL UNIQUE,
  email           VARCHAR(150) NOT NULL UNIQUE,
  department      VARCHAR(50) NOT NULL,
  year            VARCHAR(20) NOT NULL,
  phone           VARCHAR(20) DEFAULT NULL,
  password_hash   VARCHAR(255) NOT NULL,
  profile_bio     TEXT DEFAULT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- EVENTS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS events (
  event_id    INT AUTO_INCREMENT PRIMARY KEY,
  event_name  VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  category    VARCHAR(50) NOT NULL,
  event_date  DATE NOT NULL,
  event_time  VARCHAR(30) NOT NULL,
  venue       VARCHAR(150) NOT NULL,
  organizer   VARCHAR(150) NOT NULL,
  capacity    INT NOT NULL DEFAULT 100,
  image       VARCHAR(255) DEFAULT NULL,
  status      VARCHAR(20) NOT NULL DEFAULT 'upcoming',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- REGISTRATIONS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS registrations (
  registration_id   INT AUTO_INCREMENT PRIMARY KEY,
  student_id        INT NOT NULL,
  event_id          INT NOT NULL,
  registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status            VARCHAR(20) NOT NULL DEFAULT 'registered',
  UNIQUE KEY uniq_reg (student_id, event_id),
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- ATTENDANCE
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS attendance (
  attendance_id   INT AUTO_INCREMENT PRIMARY KEY,
  student_id      INT NOT NULL,
  event_id        INT NOT NULL,
  attendance_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status          VARCHAR(20) NOT NULL DEFAULT 'present',
  UNIQUE KEY uniq_att (student_id, event_id),
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- STAMPS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS stamps (
  stamp_id    INT AUTO_INCREMENT PRIMARY KEY,
  student_id  INT NOT NULL,
  event_id    INT NOT NULL,
  earned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_stamp (student_id, event_id),
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- ACHIEVEMENTS (definitions)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS achievements (
  achievement_id  INT AUTO_INCREMENT PRIMARY KEY,
  code            VARCHAR(50) NOT NULL UNIQUE,
  name            VARCHAR(100) NOT NULL,
  description     VARCHAR(255) NOT NULL,
  icon            VARCHAR(50) NOT NULL DEFAULT 'medal'
);

-- ---------------------------------------------------------------------
-- STUDENT_ACHIEVEMENTS (unlocked achievements)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS student_achievements (
  student_achievement_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id            INT NOT NULL,
  achievement_id        INT NOT NULL,
  unlocked_date         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_ach (student_id, achievement_id),
  FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
  FOREIGN KEY (achievement_id) REFERENCES achievements(achievement_id) ON DELETE CASCADE
);

-- =====================================================================
-- SAMPLE DATA
-- Default password for all demo accounts: password123
-- (hash below corresponds to password_hash('password123', PASSWORD_DEFAULT))
-- =====================================================================

-- Admin account (password: admin123)
INSERT INTO admins (name, email, password_hash) VALUES
('Admin User', 'admin@smartpass.edu', '$2y$12$YYU474XzzqKa2zbUs0xwgen2IvyrJmXzURM5DCW.LF1g2eCvJyCr.');

-- Students (password for all: password123)
INSERT INTO students (name, register_number, email, department, year, phone, password_hash) VALUES
('Rahul Sharma',  'CS21B001', 'rahul@smartpass.edu',  'CSE', '3rd Year', '9876543210', '$2y$12$Q2mSlOw9XfgcS7ou6uJUBenSiGnAgLR/PRWUtYCObr5hTIK4UOXhO'),
('Ananya Verma',  'EC21B014', 'ananya@smartpass.edu', 'ECE', '3rd Year', '9876543211', '$2y$12$Q2mSlOw9XfgcS7ou6uJUBenSiGnAgLR/PRWUtYCObr5hTIK4UOXhO'),
('Teja Reddy',    'CS22B023', 'teja@smartpass.edu',   'CSE', '2nd Year', '9876543212', '$2y$12$Q2mSlOw9XfgcS7ou6uJUBenSiGnAgLR/PRWUtYCObr5hTIK4UOXhO'),
('Priya Nair',    'IT21B009', 'priya@smartpass.edu',  'IT',  '3rd Year', '9876543213', '$2y$12$Q2mSlOw9XfgcS7ou6uJUBenSiGnAgLR/PRWUtYCObr5hTIK4UOXhO'),
('Karan Mehta',   'ME22B031', 'karan@smartpass.edu',  'MECH','2nd Year', '9876543214', '$2y$12$Q2mSlOw9XfgcS7ou6uJUBenSiGnAgLR/PRWUtYCObr5hTIK4UOXhO');

-- Events
INSERT INTO events (event_name, description, category, event_date, event_time, venue, organizer, capacity, image, status) VALUES
('Web Development Workshop', 'Hands-on workshop on modern HTML, CSS and JavaScript fundamentals for building responsive websites.', 'Workshop', '2026-09-05', '10:00 AM', 'Seminar Hall 1', 'CSE Department', 80, 'web-workshop', 'upcoming'),
('AI & Machine Learning Seminar', 'Expert talk on the fundamentals of artificial intelligence and machine learning applications.', 'Seminar', '2026-09-12', '02:00 PM', 'Main Auditorium', 'AI Research Cell', 200, 'ai-seminar', 'upcoming'),
('Annual Cultural Fest', 'A grand celebration of music, dance, drama and art featuring performances from all departments.', 'Cultural', '2026-09-20', '06:00 PM', 'Open Air Theatre', 'Cultural Committee', 500, 'cultural-fest', 'upcoming'),
('Inter-College Hackathon', '36-hour coding marathon where teams build innovative solutions to real-world problems.', 'Competition', '2026-09-28', '09:00 AM', 'Innovation Lab', 'Coding Club', 120, 'hackathon', 'upcoming'),
('Sports Meet 2026', 'Annual athletics meet featuring track events, team sports and indoor games.', 'Sports', '2026-10-05', '08:00 AM', 'College Ground', 'Sports Department', 300, 'sports-meet', 'upcoming'),
('Photography Competition', 'Capture and showcase the beauty of campus life through your lens. Prizes for top entries.', 'Competition', '2026-10-12', '11:00 AM', 'Conference Hall', 'Photography Club', 60, 'photo-comp', 'upcoming'),
('Entrepreneurship Workshop', 'Learn the essentials of building a startup from idea validation to pitching investors.', 'Workshop', '2026-10-18', '10:30 AM', 'Seminar Hall 2', 'E-Cell', 100, 'entrepreneurship', 'upcoming'),
('Science Exhibition', 'Showcase of innovative student projects across engineering, science and technology domains.', 'Technical', '2026-10-25', '09:30 AM', 'Exhibition Hall', 'Science Forum', 150, 'science-expo', 'upcoming');

-- Achievements
INSERT INTO achievements (code, name, description, icon) VALUES
('first_step',       'First Step',        'Attend your first event.', 'flag'),
('event_explorer',   'Event Explorer',    'Attend 5 events.', 'compass'),
('campus_champion',  'Campus Champion',   'Attend 10 events.', 'crown'),
('tech_enthusiast',  'Tech Enthusiast',   'Attend 5 technical events.', 'code'),
('culture_lover',    'Culture Lover',     'Attend 5 cultural events.', 'palette'),
('all_rounder',      'All-Rounder',       'Participate in events from 3 different categories.', 'star');

-- Sample registrations (Rahul registered for first 3 events)
INSERT INTO registrations (student_id, event_id, status) VALUES
(1, 1, 'registered'),
(1, 2, 'registered'),
(1, 3, 'registered'),
(2, 2, 'registered'),
(2, 4, 'registered');

-- Sample attendance (Rahul attended event 1, got a stamp)
INSERT INTO attendance (student_id, event_id, status) VALUES
(1, 1, 'present');

INSERT INTO stamps (student_id, event_id) VALUES
(1, 1);

INSERT INTO student_achievements (student_id, achievement_id) VALUES
(1, 1);
