-- Create the Scholarship Database
CREATE DATABASE Scholarship_db;
USE Scholarship_db;

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

-- Table for listing available scholarships
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

-- Table for storing scholarship applications
CREATE TABLE Applications (
    application_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    scholarship_id INT NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    document_url VARCHAR(255) DEFAULT NULL,
    finantial_statement_url VARCHAR(255) DEFAULT NULL,
    recommendation_letter_url VARCHAR(255) DEFAULT NULL,
    assigned_reviewer_id INT DEFAULT NULL,
    status ENUM('Submitted', 'Pending', 'Under Review', 'Approved', 'Accepted', 'Rejected', 'Needs More Info') DEFAULT 'Submitted',
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (scholarship_id) REFERENCES Scholarships(scholarship_id) ON DELETE CASCADE
);



CREATE TABLE Document (
    document_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    type ENUM('Transcript', 'Recommendation Letter', 'Financial Statement', 'Other') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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

-- Table for storing reviews
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


CREATE TABLE FraudLogs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    log_type ENUM('Flagged Application', 'User Activity') NOT NULL,
    reason VARCHAR(255) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    flagged_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Under Review', 'Cleared') DEFAULT 'Under Review',
    FOREIGN KEY (application_id) REFERENCES Applications(application_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE SET NULL
);


CREATE TABLE funds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  total_balance DECIMAL(12,2) NOT NULL
);


CREATE TABLE transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  type ENUM('Allocation','Disbursement') NOT NULL,
  scholarship_id INT NULL,
  student VARCHAR(255) NULL,
  amount DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_scholarship
    FOREIGN KEY (scholarship_id) REFERENCES Scholarships(scholarship_id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ReviewerSettings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    new_assignment TINYINT(1) DEFAULT 1,
    deadline_reminder TINYINT(1) DEFAULT 1,
    system_updates TINYINT(1) DEFAULT 0,
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
INSERT INTO Applications (student_id, scholarship_id, submission_date, document_url, status) VALUES 
(1, 1, '2025-03-01 10:00:00', '/uploads/new_documents/transcript_new.pdf', 'Pending'),
(1, 2, '2025-03-02 12:00:00', '/uploads/new_documents/recommendation_new.pdf', 'Under Review'),
(1, 3, '2025-03-03 14:00:00', '/uploads/new_documents/financial_new.pdf', 'Approved');

-- Insert sample data into Document table
INSERT INTO Document (url, type) VALUES
('/uploads/new_documents/transcript_new.pdf', 'Transcript'),
('/uploads/new_documents/recommendation_new.pdf', 'Recommendation Letter'),
('/uploads/new_documents/financial_new.pdf', 'Financial Statement');


INSERT INTO Document (application_id, url, type, created_at, updated_at)
VALUES 
(1, '/uploads/new_documents/transcript_new.pdf', 'Transcript', NOW(), NOW()),
(2, '/uploads/new_documents/recommendation_new.pdf', 'Recommendation Letter', NOW(), NOW()),
(3, '/uploads/new_documents/financial_new.pdf', 'Financial Statement', NOW(), NOW());


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


-- Insert sample flagged applications
INSERT INTO FraudLogs (application_id, user_id, log_type, reason, details, ip_address, flagged_date, status) VALUES
(1, 4, 'Flagged Application', 'Duplicate Document Detected', 'Transcript matches another submission', '192.168.1.1', '2025-04-05 10:00:00', 'Under Review'),
(2, 4, 'Flagged Application', 'Multiple Applications', 'Multiple submissions from same IP', '192.168.1.2', '2025-04-06 14:30:00', 'Under Review'),
(3, 4, 'Flagged Application', 'Suspicious IP Address', 'IP linked to multiple accounts', '192.168.1.3', '2025-04-07 12:00:00', 'Cleared');

-- Insert sample user activity logs
INSERT INTO FraudLogs (user_id, log_type, reason, details, ip_address, flagged_date) VALUES
(1, 'User Activity', 'Login', 'Successful login', '192.168.1.1', '2025-04-05 10:00:00'),
(4, 'User Activity', 'Document Upload', 'Duplicate document detected', '192.168.1.2', '2025-04-05 10:15:00'),
(4, 'User Activity', 'Application Submission', 'Multiple submissions from same IP', '192.168.1.2', '2025-04-06 14:30:00');

INSERT INTO transactions (type, scholarship_id, student, amount)
VALUES ('Allocation', NULL, 'System', 5000.00);

UPDATE Scholarships 
SET allocated_amount = allocated_amount + 2000 
WHERE scholarship_id = 1;

INSERT INTO transactions (type, scholarship_id, student, amount)
VALUES ('Disbursement', 1, 'John Doe', 1000.00);

INSERT INTO funds (id, total_balance) 
VALUES (1, 0.00);

UPDATE funds 
SET total_balance = total_balance + 10000 
WHERE id = 1;



-- SQL queries to show functionality:

-- Show all students in the system
SELECT * FROM Students;

-- Show all scholarships in the system
SELECT * FROM Scholarships;

-- List all applications with student’s name, scholarship applied for, and application status
SELECT a.application_id, s.first_name, s.last_name, sc.name AS scholarship_name, a.status, a.document_url
FROM Applications a
JOIN Students s ON a.student_id = s.student_id
JOIN Scholarships sc ON a.scholarship_id = sc.scholarship_id;

-- Show all approved applications with student and scholarship details
SELECT a.application_id, s.first_name, s.last_name, sc.name AS scholarship_name, a.document_url
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
SELECT s.student_id, s.first_name, s.last_name, a.application_id, a.status, a.document_url
FROM Students s
JOIN Applications a ON s.student_id = a.student_id;

-- Display scholarships that are currently open for applications
SELECT * FROM Scholarships WHERE status = 'Open';

-- List students who haven’t applied for any scholarships
SELECT s.student_id, s.first_name, s.last_name
FROM Students s
LEFT JOIN Applications a ON s.student_id = a.student_id
WHERE a.application_id IS NULL;

SELECT * FROM Applications WHERE application_id IN (1, 2, 3);
SELECT * FROM Students WHERE student_id = 1;
SELECT * FROM Users WHERE user_id IN (1, 4);



ALTER TABLE Users
ADD student_id INT DEFAULT NULL,
ADD FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE SET NULL;

UPDATE Users SET student_id = 1 WHERE user_id = 4;

SELECT u.user_id, u.username, u.role, u.student_id, s.first_name, s.last_name
FROM Users u
LEFT JOIN Students s ON u.student_id = s.student_id
WHERE u.role = 'Student' AND u.approval_status = 'Approved';


ALTER TABLE scholarships 
ADD COLUMN allocated_amount DECIMAL(12,2) DEFAULT 0,
ADD COLUMN target_amount DECIMAL(12,2) DEFAULT 0;
