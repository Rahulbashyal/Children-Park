<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "montessori";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['description'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // Use prepared statements to insert data safely
    $stmt = $conn->prepare("INSERT INTO events (title, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $description);

    if ($stmt->execute()) {
        $news_id = $stmt->insert_id;
        $notification_message = "New Notice added: $title";

        // Notify all teachers
        $result = $conn->query("SELECT uid FROM users WHERE role = 'teacher'");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $user_id = $row['uid'];
                $notification_stmt = $conn->prepare(
                    "INSERT INTO notifications (user_id, message, status, is_read) VALUES (?, ?, 'unread', 0)"
                );
                $notification_stmt->bind_param("is", $user_id, $notification_message);
                $notification_stmt->execute();
            }
        } else {
            echo "No teachers found to notify.";
        }
    } else {
        echo "Error adding notice: " . $stmt->error;
    }
}

// Fetch data for display
$eventsResult = $conn->query("SELECT * FROM events");
$usersResult = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
       /* Body and General Styles */
body {
    font-family: 'Inter', sans-serif;
    margin: 0;
    padding: 0;
    background: linear-gradient(135deg, #f5f5f5, #eaeaea); /* Subtle grey gradient */
    color: #333;
    display: flex;
    min-height: 100vh;
    overflow-x: hidden;
}

/* Sidebar Styling */
#sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(to bottom right, #f5a418, #f23a12) ;
    backdrop-filter: blur(12px); /* Glass effect */
    border-right: 1px solid rgba(200, 200, 200, 0.3); /* Soft border */
    display: flex;
    flex-direction: column;
    padding-top: 20px;
    color: rgba(255, 255, 255, 0.6);
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.05);
}

#sidebar .sidebar-header {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    padding: 20px 0;
    border-bottom: 1px solid rgba(200, 200, 200, 0.3);
}

#sidebar ul {
    list-style: none;
    padding: 0;
}

#sidebar ul li {
    font-size: 16px;
    padding: 15px 20px;
    margin: 8px 15px;
    border-radius: 8px;
    color:#fff;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

#sidebar ul li:hover {
    background: #fff; /* Subtle hover effect */
    transform: scale(1.02);
    color: #333;
}

#sidebar ul li.active {
    background: rgba(0, 0, 0, 0.1); /* Subtle active effect */
    color: #000;
    font-weight: bold;
}

#sidebar ul li a {
    text-decoration: none;
    color: inherit;
    width: 100%;
    display: flex;
    align-items: center;
}
#sidebar ul li i {
    margin-right: 14px;
    font-size: 18px;
    color: #fff;
    transition: color 0.3s ease;
}
#sidebar ul li:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: scale(1.02);
    color: #333;
}

#sidebar ul li:hover i {
    color: #333;
}

/* Active menu item */
#sidebar ul li.active {
    background: rgba(0, 0, 0, 0.1);
    color: #000;
    font-weight: bold;
}

#sidebar ul li.active i {
    color: #000;
}
/* Logout Button */
#sidebar .logout-btn {
    margin-top: auto;
    margin: 20px;
    padding: 15px;
    color: white;
    border-radius: 8px;
    width: 83% ;
    position: absolute;
    bottom: 0;
    text-align: center;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

#sidebar .logout-btn:hover{
    background: #c0392b;
    color:#fff;
    transform: scale(1.1);
}

/* Main Content */
#content {
    margin-left: 260px;
    padding: 40px;
    width: calc(100% - 260px);
    color: #333;
    /* background:linear-gradient(to bottom right, #f5a418, #f23a12) ;  */
}

/* Glassmorphism Form/Card */
.card,
.form {
    background: rgba(255, 255, 255, 0.4); /* Slightly transparent white */
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.6); /* Glassy border */
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    color: #333;
    margin-bottom: 20px;
}

.card h4,
.form h4 {
    color: #333;
    font-weight: bold;
    font-size: 18px;
}

input[type="text"],
input[type="number"],
select,
textarea {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.6); /* Transparent input field */
    color: #333;
    box-shadow: inset 0px 4px 6px rgba(0, 0, 0, 0.1);
}

input[type="text"]:focus,
input[type="number"]:focus,
select:focus,
textarea:focus {
    border-color: #666;
    outline: none;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
}

/* Buttons */
.btn {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: bold;
    /* color:; */
    background-color:linear-gradient(to bottom right, #f5a418, #f23a12) !important ;
    border: 1px solid black;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.btn-primary {
    background:linear-gradient(to bottom right, #f5a418, #f23a12) ; /* Blue primary */
    box-shadow: 0px 4px 8px rgba(52, 152, 219, 0.4);
}

.btn-primary:hover {
    background: #2980b9;
    transform: scale(1.05);
}

.btn-danger {
    background:linear-gradient(to bottom right, #f5a418, #f23a12) ;  /* Red for delete */
}

.btn-danger:hover {
    background: #c0392b;
    transform: scale(1.05);
}

/* Table */
.table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    background: rgba(255, 255, 255, 0.5);
    color: #333;
    backdrop-filter: blur(8px);
    border-radius: 10px;
}

.table th {
    background: rgba(0, 0, 0, 0.1);
    color: #333;
    font-weight: bold;
    padding: 15px;
}

.table td {
    padding: 15px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

/* Responsive Adjustments */
@media screen and (max-width: 768px) {
    #sidebar {
        width: 220px;
    }

    #content {
        margin-left: 220px;
    }
}

@media screen and (max-width: 576px) {
    #sidebar {
        position: absolute;
        width: 100%;
    }

    #content {
        margin-left: 0;
    }
}
.navbar{
    background:linear-gradient(to bottom right, #f5a418, #f23a12) ; 
    border-radius:16px;
}
.navbar-brand{
    color:#fff;
    font-weight:600;

}
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="bg-dark">
        <div class="sidebar-header">
            <i class="fas fa-user-circle user-logo"></i> Montessori Admin
        </div>
        <ul class="list-unstyled components">
    <li>
        <a href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </li>
    <li>
        <a href="manage_users.php">
            <i class="fas fa-users"></i> Manage Users
        </a>
    </li>
    <li>
        <a href="manage_news.php">
            <i class="fas fa-newspaper"></i> Manage Notice
        </a>
    </li>
    <li>
        <a href="manage_popup.php">
            <i class="fas fa-bullhorn"></i> Manage Popup
        </a>
    </li>
    <li>
        <a href="manage_gallery.php">
            <i class="fas fa-image"></i> Manage Gallery
        </a>
    </li>
    <li class="bck">
        <a href="index.php">
            <i class="fas fa-arrow-left"></i> Back to Website
        </a>
    </li>
    <li class="logout logout-btn">
        <a href="dashboard.php?logout=true">
            <i class="fas  fa-sign-out-alt"></i> Logout
        </a>
    </li>
</ul>

    </nav>

    <!-- Content -->
    <div id="content">
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Dashboard</span>
            </div>
        </nav>
        <div class="container-fluid my-4">
            <!-- Users Table -->
            <div class="card mb-4">
                <div class="card-header">Manage Users</div>
                <div class="card-body">
                    <a href="manage_users.php" class="btn btn-custom mb-3">Add New User</a>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $usersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['uid']) ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- News Table -->
            <div class="card mb-4">
                <div class="card-header">Manage Notice</div>
                <div class="card-body">
                    <a href="manage_news.php" class="btn btn-custom mb-3">Add Notice</a>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Event ID</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($event = $eventsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($event['eid']) ?></td>
                                    <td><?= htmlspecialchars($event['title']) ?></td>
                                    <td><?= htmlspecialchars($event['date']) ?></td>
                                    <td><?= htmlspecialchars($event['description']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
