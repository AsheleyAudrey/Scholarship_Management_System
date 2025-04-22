-- Create the Scholarship Database
CREATE DATABASE Scholarship_db;

-- Table for storing user accounts (students, reviewers, admins, sponsors)
CREATE TABLE Users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Student', 'Reviewer', 'Admin', 'Sponsor') NOT NULL,
    approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing student details
CREATE TABLE Students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(15),
    date_of_birth DATE,
    enrollment_date DATE NOT NULL,
    program VARCHAR(50) NOT NULL,
    gpa DECIMAL(3,2),
    status ENUM('Active', 'Graduated', 'Suspended', 'Withdrawn', 'Scholarship Awarded', 'Scholarship Revoked') DEFAULT 'Active',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Table for listing available scholarships (merged from both definitions)
CREATE TABLE Scholarships (
    scholarship_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    gpa DECIMAL(3,1) DEFAULT NULL,
    other_criteria VARCHAR(255) DEFAULT NULL,
    application_start DATE DEFAULT NULL,
    application_end DATE NOT NULL,
    status ENUM('Open', 'Closed', 'Awarded') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for storing scholarship applications (merged from both definitions)
CREATE TABLE Applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    scholarship_id INT NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Submitted', 'Pending', 'Under Review', 'Approved', 'Accepted', 'Rejected', 'Needs More Info') DEFAULT 'Submitted',
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (scholarship_id) REFERENCES Scholarships(scholarship_id) ON DELETE CASCADE
);

-- Table for storing documents (merged ApplicationDocuments and Documents)
CREATE TABLE Documents (
    document_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    document_type ENUM('Transcript', 'Recommendation Letter', 'Financial Statement', 'Other') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES Applications(application_id) ON DELETE CASCADE
);

-- Table for storing the review committee members
CREATE TABLE ReviewCommittee (
    reviewer_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('Senior Reviewer', 'Junior Reviewer') NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Table for storing reviews (merged from both definitions)
CREATE TABLE Reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score DECIMAL(5,2) CHECK (score BETWEEN 0 AND 100),
    comments TEXT,
    decision ENUM('Pending', 'Approved', 'Rejected', 'Needs More Info') DEFAULT 'Pending',
    FOREIGN KEY (application_id) REFERENCES Applications(application_id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES ReviewCommittee(reviewer_id) ON DELETE CASCADE
);

-- Table for storing notifications sent to users
CREATE TABLE Notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Unread', 'Read') DEFAULT 'Unread',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Insert sample data for each table:

-- Insert sample data into Users table
INSERT INTO Users (username, password, role, approval_status) VALUES
('Ju Win', 'password123', 'Admin', 'Approved'),
('Richlove Kin', 'password456', 'Reviewer', 'Approved'),
('Aubrey Love', 'password789', 'Reviewer', 'Approved'),
('Jolly Adams', 'password321', 'Student', 'Approved');

-- Insert sample data into Scholarships table
INSERT INTO Scholarships (name, amount, gpa, other_criteria, application_start, application_end, status) VALUES
('Full Tuition Scholarship', 50000.00, 3.0, 'Leadership', '2025-01-01', '2025-06-30', 'Open'),
('Merit-Based Scholarship', 15000.00, 3.8, 'STEM Major', '2025-03-01', '2025-05-15', 'Open'),
('Need-Based Scholarship', 10000.00, 3.0, 'Financial Need', '2025-02-01', '2025-07-01', 'Open');

-- Insert sample data into ReviewCommittee table
INSERT INTO ReviewCommittee (name, email, role, user_id) VALUES
('John Thompson', 'john.thompson@example.com', 'Senior Reviewer', 2),
('David Green', 'david.green@example.com', 'Junior Reviewer', 3),
('Pat White', 'pat.white@example.com', 'Junior Reviewer', 4);

-- Insert sample data into Students table
INSERT INTO Students 
(user_id, first_name, last_name, email, phone, date_of_birth, enrollment_date, program, gpa, status) VALUES 
(4, 'Amber', 'David', 'amberdavid@example.com', '1234567890', '2000-05-15', '2023-09-01', 'Accounting', 3.75, 'Active');

-- Insert sample data into Applications table
INSERT INTO Applications (student_id, scholarship_id, submission_date, status) VALUES 
(1, 1, '2025-03-01 10:00:00', 'Pending'),
(1, 2, '2025-03-02 12:00:00', 'Under Review'),
(1, 3, '2025-03-03 14:00:00', 'Approved');

-- Insert sample data into Documents table
INSERT INTO Documents (application_id, document_type, file_path) VALUES 
(1, 'Transcript', '/uploads/documents/transcript_1.pdf'),
(2, 'Recommendation Letter', '/uploads/documents/recommendation_2.pdf'),
(3, 'Financial Statement', '/uploads/documents/financial_3.pdf');

-- Insert sample data into Reviews table
INSERT INTO Reviews (application_id, reviewer_id, review_date, score, comments, decision) VALUES 
(1, 1, '2025-03-05 09:00:00', 85.50, 'Strong academic performance, meets all criteria.', 'Approved'),
(2, 2, '2025-03-06 11:00:00', 70.00, 'Financial need is unclear, requires more documents.', 'Needs More Info'),
(3, 3, '2025-03-07 13:00:00', 65.00, 'GPA is below the required threshold.', 'Rejected');

-- Insert sample data into Notifications table
INSERT INTO Notifications (user_id, message, status) VALUES 
(4, 'Your scholarship application has been received.', 'Unread'),
(4, 'A reviewer has requested additional documents.', 'Unread'),
(4, 'Your application has been approved! Congratulations!', 'Read');

-- SQL queries to show functionality:

-- Show all students in the system
SELECT * FROM Students;

-- Show all scholarships in the system
SELECT * FROM Scholarships;

-- List all applications with student’s name, scholarship applied for, and application status
SELECT a.application_id, s.first_name, s.last_name, sc.name AS scholarship_name, a.status
FROM Applications a
JOIN Students s ON a.student_id = s.student_id
JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id;

-- Show all approved applications with student and scholarship details
SELECT a.application_id, s.first_name, s.last_name, sc.name AS scholarship_name
FROM Applications a
JOIN Students s ON a.student_id = s.student_id
JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id
WHERE a.status IN ('Approved', 'Accepted');

-- Show all committee members and their roles
SELECT name, role
FROM ReviewCommittee;

-- Calculate the total number of scholarships that have been awarded
SELECT COUNT(*) AS total_awarded_scholarships
FROM Students
WHERE status = 'Scholarship Awarded';

-- Display student details and their scholarship application status
SELECT s.student_id, s.first_name, s.last_name, a.application_id, a.status 
FROM Students s
JOIN Applications a ON s.student_id = a.student_id;

-- Display scholarships that are currently open for applications
SELECT * FROM Scholarships WHERE status = 'Open';

-- List students who haven’t applied for any scholarships
SELECT s.student_id, s.first_name, s.last_name
FROM Students s
LEFT JOIN Applications a ON s.student_id = a.student_id
WHERE a.application_id IS NULL;