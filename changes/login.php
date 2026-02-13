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

// Initialize error message
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get input values
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validation: Check if fields are empty
    if (empty($username) || empty($password)) {
        $error_message = "Both username and password are required.";
    } else {
        // Query the database
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'admin') {
                    header("Location: dashboard.php");
                    exit();
                } elseif ($user['role'] == 'teacher') {
                    header("Location: teacher_dashboard.php");
                    exit();
                }
            } else {
                $error_message = "Incorrect password. Please try again.";
            }
        } else {
            $error_message = "No account found with that username.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Login</h2>
            <p>Enter your credentials to access your account.</p>
            <?php if ($error_message): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
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
            <div class="signup-link">
                Don't have an account? <a href="#">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>
