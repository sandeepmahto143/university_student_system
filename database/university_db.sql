-- =============================================================
--  University Student Information and Academic Management System
--  Database : university_db   (MySQL / MariaDB - XAMPP / WAMP)
-- =============================================================
--  How to import:
--    1. Start Apache and MySQL in the XAMPP Control Panel.
--    2. Open http://localhost/phpmyadmin
--    3. Click "Import" -> choose this file -> click "Go".
-- =============================================================

CREATE DATABASE IF NOT EXISTS university_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE university_db;

-- -------------------------------------------------------------
--  Table: users   (Module 1 - Login & Registration)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    full_name   VARCHAR(100)  NOT NULL,
    username    VARCHAR(50)   NOT NULL,
    email       VARCHAR(100)  NOT NULL,
    password    VARCHAR(255)  NOT NULL,            -- stored as a bcrypt hash
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
--  Table: students   (Module 2 & 3 - Student Management)
-- -------------------------------------------------------------
DROP TABLE IF EXISTS students;
CREATE TABLE students (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    reg_no          VARCHAR(20)   NOT NULL,         -- Register Number
    name            VARCHAR(100)  NOT NULL,         -- Student Name
    course          VARCHAR(30)   NOT NULL,         -- Course
    semester        TINYINT UNSIGNED NOT NULL,      -- Semester (1-8)
    email           VARCHAR(100)  NOT NULL,         -- Email ID
    contact_number  VARCHAR(15)   NOT NULL,         -- Contact Number
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                  ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_students_reg_no (reg_no),
    UNIQUE KEY uq_students_email (email),
    CONSTRAINT chk_semester CHECK (semester BETWEEN 1 AND 8)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
--  Default login account
--    Username : admin
--    Password : admin123
-- -------------------------------------------------------------
INSERT INTO users (full_name, username, email, password) VALUES
('Administrator', 'admin', 'admin@university.edu',
 '$2y$12$bgQlnKgy/r9s7rCryxJYkOS/q85EYUAYhnDwXor06wGtvDOyC0RNm');

-- -------------------------------------------------------------
--  Sample student records
-- -------------------------------------------------------------
INSERT INTO students (reg_no, name, course, semester, email, contact_number) VALUES
('U03BC24S0001', 'Aarav Sharma',     'BCA',   5, 'aarav.sharma@example.com',   '9876543210'),
('U03BC24S0002', 'Diya Patel',       'BCA',   5, 'diya.patel@example.com',     '9845012345'),
('U03BC24S0003', 'Rohan Gowda',      'BCA',   3, 'rohan.gowda@example.com',    '9900112233'),
('U03BB24S0004', 'Ananya Reddy',     'BBA',   3, 'ananya.reddy@example.com',   '9731456789'),
('U03BM24S0005', 'Karthik Nair',     'B.Com', 1, 'karthik.nair@example.com',   '9620987654'),
('U03BS24S0006', 'Sneha Iyer',       'B.Sc',  5, 'sneha.iyer@example.com',     '9886543201'),
('U03MC24S0007', 'Vikram Singh',     'MCA',   2, 'vikram.singh@example.com',   '9008765432'),
('U03BC24S0008', 'Meera Krishnan',   'BCA',   1, 'meera.krishnan@example.com', '9741234567');
