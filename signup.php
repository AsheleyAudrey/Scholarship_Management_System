<?php
include "./Database/db.php";

// Run logic ONLY when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $program = trim($_POST['program']);

    // Validate inputs
    if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($email) || empty($program)) {
        echo "<script>alert('Please fill all required fields');</script>";
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT * FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Username or email already exists');</script>";
        } else {
            // Insert into Users table
            $user_sql = "INSERT INTO Users (username, password, role) VALUES (?, ?, 'Student')";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("ss", $username, $password);
            
            if ($user_stmt->execute()) {
                $user_id = $conn->insert_id;

                // Insert into Students table
                $student_sql = "INSERT INTO Students (user_id, first_name, last_name, email, phone, date_of_birth, enrollment_date, program, status) 
                               VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, 'Active')";
                $student_stmt = $conn->prepare($student_sql);
                $student_stmt->bind_param("issssss", $user_id, $first_name, $last_name, $email, $phone, $date_of_birth, $program);

                if ($student_stmt->execute()) {
                    echo "<script>alert('Signup successful! Please login.'); window.location.href = 'login.php';</script>";
                } else {
                    echo "<script>alert('Error creating student profile');</script>";
                    // Rollback user creation
                    $delete_sql = "DELETE FROM Users WHERE user_id = ?";
                    $delete_stmt = $conn->prepare($delete_sql);
                    $delete_stmt->bind_param("i", $user_id);
                    $delete_stmt->execute();
                }
            } else {
                echo "<script>alert('Error creating user account');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .viewport {
            width: 1440px;
            height: 689px;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        h2 {
            color: #2D3748;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .signup-container {
            width: 500px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 40px;
            box-sizing: border-box;
            height: 600px;
            display: grid;
            
        }
        p {
            color: #718096;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .input-field {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #E2E8F0;
            border-radius: 5px;
            font-size: 16px;
            color: #4A5568;
            box-sizing: border-box;
        }
        .input-field::placeholder {
            color: #A0AEC0;
        }
        .signup-btn {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
        }
        .signup-btn:hover {
            background-color: #0056b3;
        }
        .login-link {
            margin-top: 20px;
            font-size: 14px;
            color: #718096;
        }
        .login-link a {
            color: #007bff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="viewport">
        <h2>Create Your Student Account</h2>
        <form class="signup-container" action="" method="POST">
            <p>Join our academic community today!</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <input type="text" class="input-field" placeholder="Enter Username" name="username" required>
                <input type="password" class="input-field" placeholder="Enter Password" name="password" required>
                <input type="text" class="input-field" placeholder="First Name" name="first_name" required>
                <input type="text" class="input-field" placeholder="Last Name" name="last_name" required>
                <input type="email" class="input-field" placeholder="Email" name="email" required>
                <input type="tel" class="input-field" placeholder="Phone (optional)" name="phone">
                <input type="date" class="input-field" placeholder="Date of Birth (optional)" name="date_of_birth">
                <input type="text" class="input-field" placeholder="Program of Study" name="program" required>
            </div>
            <button class="signup-btn" type="submit">Sign Up</button>
            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </form>
    </div>
</body>
</html>