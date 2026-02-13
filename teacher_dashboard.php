<?php
session_start(); // Start the session at the beginning

// Check if the user is logged in and if their role is 'teacher'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teacher') {
    // If not logged in as a teacher, redirect to the login page
    header("Location: index.php");
    exit();
}

// Check if the logout parameter is present in the URL
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    // Destroy the session
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session

    // Redirect to the login page or home page
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'montessori');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch unread notifications count for the teacher (all teachers share notifications here)
$sql_unread = "SELECT COUNT(*) as unread_count FROM notifications WHERE is_read = 0 AND status = 'unread'";
$result_unread = $conn->query($sql_unread);

$unread_count = 0;
if ($result_unread && $result_unread->num_rows > 0) {
    $row = $result_unread->fetch_assoc();
    $unread_count = $row['unread_count'];
}

// Fetch all notifications to display in the modal
$sql_notifications = "SELECT * FROM notifications ORDER BY created_at DESC";
$result_notifications = $conn->query($sql_notifications);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/sidebar.css">

    <!-- Custom CSS -->
    <style>
       

        .user-logo {
            font-size: 2em;
            line-height: 1.5;
        }

        /* Notification section */
        .notification-bell {
            position: relative;
            font-size: 24px;
            cursor: pointer;
        }

        .notification-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        .dropdown-menu {
            max-height: 300px;
            overflow-y: auto;
        }

        .welcome-message {
            margin-left: 10px;
        }

        .logout-link {
            color: #ff0000;
        }

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

#content {
            margin-left: 260px;
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            font-weight: 600;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #e56707;
            color: #fff;
            font-weight: 600;
        }

        h4 {
            margin-top: 20px;
            font-weight: 600;
        }

        .btn-custom {
            background-color: #e56707;
            color: white;
            border-radius: 20px;
        }

        .btn-custom:hover {
            background-color: #cf5a05;
            color: white;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .news-img img {
        height: 180px; /* Slightly smaller image height for tablets */
    }

    .news{
        margin-top:320px;
    }
            .news-item {
                margin-bottom: 20px;
            }

            .student-list {
                font-size: 14px;
            }

            .attendance-table {
                font-size: 12px;
            }
            .news h1 {
            font-size: 2rem;
        }

        .news h4 {
            font-size: 1.2rem;
        }
        #sidebar {
                position: fixed;
                width: 100%;
                height: auto;
                top: 0;
                left: 0;
                background-color: rgba(255, 255, 255, 0.9);
            }

            #content {
                margin-left: 0;
            }

            .news-item {
                margin-bottom: 20px;
            }
        
        }

        @media (max-width: 576px) {
            .table {
                font-size: 12px;
            }

            .card {
                font-size: 14px;
            }

            #sidebar ul li {
                padding: 8px;
            }
            .news-img img {
        height: 150px; /* Even smaller image height for mobile screens */
    }
    .news{
        margin-top:170px;
    }
    .news-item {
        margin-bottom: 15px; /* Reduce bottom margin on smaller screens */
    }
    .news-img img {
        height: 150px; /* Even smaller image height for mobile screens */
    }
    
    .news-item {
        margin-bottom: 15px; /* Reduce bottom margin on smaller screens */
    }
            
        }
        .notification-bell {
            position: relative;
            font-size: 24px;
            cursor: pointer;
        }

        .notification-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        .notification-modal .modal-content {
            max-height: 500px;
            overflow-y: auto;
        }

        .notification-card {
            border: 1px solid #ddd;
            margin-bottom: 10px;
            border-radius: 8px;
            padding: 10px;
        }

        .notification-card.unread {
            background-color: #f9f9f9;
        }
        /* News Section */
        .news-item {
            background-color: #e56707;
        }

        .news-img img {
            max-width: 100%;
            height: auto;
        }

        .news-text h4 {
            color: #333;
        }

        .news-text p {
            color: #555;
        }
        .news {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
    }

    .news h4, 
    .news h1 {
        font-family: 'Poppins', sans-serif;
    }

    /* Glassmorphic News Card Styling */
    .news-item {
        background: rgba(255, 255, 255, 0.6); /* Glass effect */
        backdrop-filter: blur(10px); /* Blur effect */
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 15px;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .news-item:hover {
        transform: scale(1.05); /* Slightly grow on hover */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); /* Soft shadow */
    }

    .news-img img {
        transition: transform 0.3s ease-in-out;
    }

    .news-item:hover .news-img img {
        transform: scale(1.1); /* Zoom-in effect on hover */
    }

    .news-text h4 {
        font-weight: 600;
        color: #0d6efd;
    }

    .news-text p {
        font-size: 0.95rem;
        color: #6c757d;
    }

    .stretched-link {
        font-weight: bold;
        transition: color 0.3s ease-in-out;
    }

    .stretched-link:hover {
        color: #0a58ca;
    }

  
   
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="bg-dark">
        <div class="sidebar-header">
            <i class="fas fa-user-circle user-logo"></i>
            <span class="ml-2 welcome-message">Welcome, <strong><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?></strong></span>
        </div>

        <ul class="list-unstyled components">
            <li>
                <a href="#">Dashboard</a>
            </li>
            <li>
                <a href="news.php">Notice Update</a>
            </li>
            <li class="bck">
                <a href="index.php">Back to Website</a>
            </li>
            <li class="logout logout-btn">
                <a href="teacher_dashboard.php?logout=true">Logout</a>
            </li>
        </ul>
    </nav>

   <!-- Main Content -->
   <div id="content">
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Teacher Dashboard</span>
                <!-- Bell Icon -->
                <div class="notification-bell" id="notificationBell" data-bs-toggle="modal" data-bs-target="#notificationModal">
                    <i class="fas fa-bell"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-count"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Notifications Modal -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg notification-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">Notifications</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php if ($result_notifications && $result_notifications->num_rows > 0): ?>
                            <?php while ($notification = $result_notifications->fetch_assoc()): ?>
                                <div class="notification-card <?php echo $notification['is_read'] == 0 ? 'unread' : ''; ?>">
                                    <p><strong><?php echo $notification['message']; ?></strong></p>
                                    <small class="text-muted"><?php echo $notification['created_at']; ?></small>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No notifications available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        

        <!-- News Section -->
<div class="container-fluid news py-5 bg-light">
    <div class="container py-5">
        <!-- Section Heading -->
        <div class="mx-auto text-center">
            <h4 class="text-primary mb-4 border-bottom border-primary border-2 d-inline-block p-2">Our Notice</h4>
            <h1 class="mb-5 display-3">Our Latest Notice</h1>
        </div>
        
       <!-- News Cards -->
<div class="  row g-5 justify-content-center">
    <?php
    $conn = new mysqli('localhost', 'root', '', 'montessori');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM events ORDER BY eid DESC LIMIT 5";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            ?>
            <div class="col-md-6 col-lg-4">
                <!-- Glassmorphic News Card -->
                <div class="news-item rounded shadow-lg position-relative">
    <div class="news-img rounded-top overflow-hidden">
        <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
            <img class="img-fluid w-100" src="<?= $row['image_path'] ?>" alt="<?= htmlspecialchars($row['title']); ?>" style="object-fit: cover; height: 200px;">
        <?php else: ?>
            <!-- Fallback image if no image is found or path is incorrect -->
            <img class="img-fluid w-100" src="path/to/default-image.jpg" alt="Default News Image" style="object-fit: cover; height: 200px;">
        <?php endif; ?>
    </div>
    <div class="news-text p-4 position-relative">
        <h4 class="text-primary mb-3"><?= htmlspecialchars($row['title']); ?></h4>
        <p class="text-secondary mb-3"><?= htmlspecialchars($row['description']); ?></p>
        <a href="#" class="stretched-link text-decoration-none text-primary">Read More</a>
    </div>
</div>

</div>
            <?php
        }
    } else {
        echo "<p class='text-center'>No news items found.</p>";
    }

    $conn->close();
    ?>
</div>

    </div>
</div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
