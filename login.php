<?php

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "montessori";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Debug: Check user details
            echo "<pre>";
            print_r($user); // Remove this after debugging
            echo "</pre>";

            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    header("Location: dashboard.php");
                    exit();
                } elseif ($user['role'] == 'teacher') {
                    header("Location: teacher_dashboard.php");
                    exit();
                } else {
                    // Handle unexpected roles
                    header("Location: index.php");
                    exit();
                }
            } else {
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $error_message = "No account found with that username Please contact the Admin.";
        }
    } else {
        $error_message = "Both fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #a2c4fc, #d7e8fe);
            font-family: 'Poppins', sans-serif;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .login-card {
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            background-color: rgba(255, 255, 255, 0.1); /* Transparent background */
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px 30px;
            max-width: 350px; /* Reduced width for compact design */
            width: 100%;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-card h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .login-card p {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        .form-group input {
            width: 90%; /* Reduced width for compact design */
            padding: 10px; /* Reduced padding for smaller input fields */
            font-size: 14px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            outline: none;
            transition: border 0.3s ease;
        }

        .form-group input:focus {
            border: 1px solid #5893d4;
            box-shadow: 0px 0px 5px rgba(88, 147, 212, 0.5);
        }

        .btn-primary {
            width: 90%; /* Matches input field width */
            padding: 12px;
            background-color: #5893d4;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #407bbf;
        }

        .error-message {
            background-color: rgba(255, 0, 0, 0.1);
            color: red;
            font-size: 14px;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .signup-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .signup-link a {
            color: #5893d4;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #407bbf;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access your account.</p>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-primary">Log In</button>
            </form>
            
           
        </div>
    </div>
</body>
</html>
