<?php
include "./Database/db.php";

// Run logic ONLY when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare and execute secure SQL query
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if login is successful
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $role = $user['role'];

        echo "<script>alert('Login successful');</script>";

        // Redirect based on role
        if ($role === "Admin") {
            echo "<script>window.location.href = 'admin/dashboard.php';</script>";
        }
        else if ($role === "Reviewer") {
            echo "<script>window.location.href = 'reviewer/dashboard.php';</script>";
        }
        else {
            echo "<script>window.location.href = 'student/dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid credentials');</script>";
    }
}
?>

<!-- HTML starts AFTER PHP to prevent early rendering -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        /* [Your CSS remains unchanged] */
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
            height: 789px;
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
        .login-container {
            width: 400px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 40px;
            box-sizing: border-box;
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
        .login-btn {
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
        .login-btn:hover {
            background-color: #0056b3;
        }
        .signup-link {
            margin-top: 20px;
            font-size: 14px;
            color: #718096;
        }
        .signup-link a {
            color: #007bff;
            text-decoration: none;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="viewport">
        <h2>Welcome, Log into your account</h2>
        <form class="login-container" action="" method="POST">
            <p>It is our great pleasure to have you on board!</p>
            <input type="text" class="input-field" placeholder="Enter Username" name="username" required>
            <input type="password" class="input-field" placeholder="Enter Password" name="password" required>
            <button class="login-btn" type="submit">Login</button>
            <div class="signup-link">
                Already have an account? <a href="signup.php">Sign up</a>
            </div>
        </form>
    </div>
</body>
</html>
