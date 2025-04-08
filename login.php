<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
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
            height: 789px;
            background-color: #fff;
            display: flex;
            flex-direction: column; /* Stack heading and container vertically */
            justify-content: center;
            align-items: center;
            position: relative;
        }
        h2 {
            color: #2D3748; /* Darker shade for heading */
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px; /* Space between heading and container */
        }
        .login-container {
            width: 400px; /* Reduced width to match the design */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            text-align: center;
            padding: 40px;
            box-sizing: border-box;
        }
        p {
            color: #718096; /* Lighter gray for subtext */
            font-size: 16px;
            margin-bottom: 30px;
        }
        .input-field {
            width: 100%; /* Full width to match design */
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #E2E8F0; /* Light gray border */
            border-radius: 5px;
            font-size: 16px;
            color: #4A5568;
            box-sizing: border-box;
        }
        .input-field::placeholder {
            color: #A0AEC0; /* Placeholder color */
        }
        .login-btn {
            width: 100%; /* Full width to match design */
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
        <h2>Welcome, Log into your account</h2> <!-- Moved outside the container -->
        <div class="login-container">
            <p>It is our great pleasure to have you on board!</p>
            <input type="text" class="input-field" placeholder="Enter Username">
            <input type="password" class="input-field" placeholder="Enter Password">
            <button class="login-btn">Login</button>
            <div class="signup-link">
                Already have an account? <a href="#">Sign up</a>
            </div>
    </div>
    </div>
</body>
</html>