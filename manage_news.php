<?php
session_start(); // Start the session to use session variables

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "montessori";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding news
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $date = $conn->real_escape_string($_POST['date']);
    $description = $conn->real_escape_string($_POST['description']);
    $imagePath = null;

    // Validate date (prevent past dates)
    if ($date < date('Y-m-d')) {
        $_SESSION['error_message'] = "The date cannot be in the past.";
        header("Location: manage_news.php");
        exit();
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageType = $_FILES['image']['type'];
        $imageSize = $_FILES['image']['size'];
        $uploadDir = 'uploads/news_images/';

        if (!in_array($imageType, $allowedTypes)) {
            $_SESSION['error_message'] = "Invalid image type. Only JPEG, PNG, or GIF are allowed.";
            header("Location: manage_news.php");
            exit();
        }

        if ($imageSize > $maxFileSize) {
            $_SESSION['error_message'] = "Image size exceeds the limit of 2MB.";
            header("Location: manage_news.php");
            exit();
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imagePath = $uploadDir . uniqid() . "_" . $imageName;

        if (!move_uploaded_file($imageTmpName, $imagePath)) {
            $_SESSION['error_message'] = "Failed to upload the image.";
            header("Location: manage_news.php");
            exit();
        }
    }

    // Insert news
    $stmt = $conn->prepare("INSERT INTO events (title, date, description, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $date, $description, $imagePath);

    if ($stmt->execute()) {
        // Send notifications to teachers
        $getTeachers = "SELECT uid FROM users WHERE role = 'teacher'";
        $teachersResult = $conn->query($getTeachers);

        if ($teachersResult->num_rows > 0) {
            $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            while ($teacher = $teachersResult->fetch_assoc()) {
                $teacher_id = $teacher['uid'];
                $notification = "New News Added: $title";
                $notifyStmt->bind_param("is", $teacher_id, $notification);
                $notifyStmt->execute();
            }
            $notifyStmt->close();
        }

        $_SESSION['success_message'] = "News added successfully and notifications sent!";
    } else {
        $_SESSION['error_message'] = "Error adding news: " . $stmt->error;
    }

    $stmt->close();
    header("Location: manage_news.php");
    exit();
}

// Handle deletion of news
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Use prepared statements for deletion
    $stmt = $conn->prepare("DELETE FROM events WHERE eid = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "News deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting news: " . $stmt->error;
    }

    $stmt->close();
    header("Location: manage_news.php");
    exit();
}

// Handle updating news
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_news'])) {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $date = $conn->real_escape_string($_POST['date']);
    $description = $conn->real_escape_string($_POST['description']);

    // Validate date (prevent past dates)
    if ($date < date('Y-m-d')) {
        $_SESSION['error_message'] = "The date cannot be in the past.";
        header("Location: manage_news.php");
        exit();
    }

    $stmt = $conn->prepare("UPDATE events SET title = ?, date = ?, description = ? WHERE eid = ?");
    $stmt->bind_param("sssi", $title, $date, $description, $id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "News updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating news: " . $stmt->error;
    }

    $stmt->close();
    header("Location: manage_news.php");
    exit();
}

// Fetch all news
$sql = "SELECT * FROM events ORDER BY date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Matching the head section from dashboard.php -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notice</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts for modern, clean typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS for the dashboard -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }


        #content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
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
            background-color: #333;
            color: #fff;
            font-weight: 600;
        }

        h4 {
            margin-top: 20px;
            font-weight: 600;
        }

        .btn-custom {
            background-color: #ff7f0e;
            color: white;
            border-radius: 20px;
        }

        .btn-custom:hover {
            background-color: #e56707;
            color: white;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }
        .fade-out {
            opacity: 0;
            transition: opacity 1s ease-out;
        }

        .alert {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 50%; /* Adjust the width as needed */
    z-index: 1050; /* Ensures it appears on top of other elements */
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

        /* Responsiveness */
        @media (max-width: 991px) {
            #sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            #sidebar ul li {
                display: inline-block;
                margin-right: 10px;
            }

            #content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            #sidebar ul li {
                font-size: 16px;
                padding: 10px;
            }

            #content {
                padding: 10px;
            }

            .table {
                font-size: 14px;
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
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

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


    <!-- Main content -->
    <div id="content">
        <!-- Navbar -->
        <nav class="navbar navbar-light bg-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Manage Notice</span>
            </div>
        </nav>
        <div class="container-fluid my-4">
   
</div>


          <!-- Add News Section -->
<div class="card mb-4">
    <div class="card-header">Add Notice</div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <!-- Hidden input to identify form submission -->
            <input type="hidden" name="add_news" value="1">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-custom">Add Notice</button>
        </form>
    </div>
</div>


            <!-- Existing News Section -->
           <!-- Existing News Section -->
           <div class="card mb-4">
    <div class="card-header">Existing Notice</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Image</th> <!-- New Image Column -->
                       
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['eid'] ?></td>
                                <td><?= $row['title'] ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td>
                                    <?php if (!empty($row['image_path'])): ?>
                                        <img src="<?= $row['image_path'] ?>" alt="News Image" style="width: 100px; height: auto; border-radius: 8px;">
                                    <?php else: ?>
                                        <span>No Image</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Update button to trigger the modal -->
                                    <td>
    <!-- Update button with inline margin -->
    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?= $row['eid'] ?>" style="margin-bottom: 8px;">Update</button>

    <!-- Delete button -->
    <a href="manage_news.php?delete_id=<?= $row['eid'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this news?');">Delete</a>
</td>

                                </td>
                            </tr>

                            <!-- Update Modal -->
                            <div class="modal fade" id="updateModal<?= $row['eid'] ?>" tabindex="-1" aria-labelledby="updateModalLabel<?= $row['eid'] ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="updateModalLabel<?= $row['eid'] ?>">Update Notice</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="manage_news.php">
                                                <!-- Hidden input to identify the update action and pass the news ID -->
                                                <input type="hidden" name="update_news" value="1">
                                                <input type="hidden" name="id" value="<?= $row['eid'] ?>">

                                                <div class="mb-3">
                                                    <label for="title" class="form-label">Title</label>
                                                    <input type="text" class="form-control" name="title" value="<?= $row['title'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="date" class="form-label">Date</label>
                                                    <input type="date" class="form-control" name="date" value="<?= $row['date'] ?>" required min="<?php echo date('Y-m-d'); ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description" class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" required><?= $row['description'] ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-custom">Update Notice</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No notice found.</td> <!-- Adjust colspan for the new column -->
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

        </div>
    </div>
    <?php
    // Handle form submission for adding news
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_news'])) {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $description = $_POST['description'];

    // Insert news into the events table
    $sql = "INSERT INTO events (title, date, description) VALUES ('$title', '$date', '$description')";
    if ($conn->query($sql) === TRUE) {
        // Get all teachers
        $getTeachers = "SELECT uid FROM users WHERE role = 'teacher'";
        $teachersResult = $conn->query($getTeachers);

        // Create notification for each teacher
        if ($teachersResult->num_rows > 0) {
            while ($teacher = $teachersResult->fetch_assoc()) {
                $teacher_id = $teacher['uid'];
                $notification = "New Notice Added: $title";
                $notifySql = "INSERT INTO notifications (user_id, message) VALUES ('$teacher_id', '$notification')";
                $conn->query($notifySql);
            }
        }

        echo "Notice added and notifications sent!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

    ?>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Prevent form resubmission on page refresh -->
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        function showAlert(id) {
    const alert = document.getElementById(id);
    alert.style.display = 'block';
    
        }

    // Show and fade out the alert after a specific action (add, update, delete)
    window.onload = function () {
        var alertElement = document.querySelector('.alert');
        if (alertElement) {
            // Show the alert
            alertElement.style.display = 'block';
            
            // Set timeout to add the fade-out animation after 3 seconds
            setTimeout(function () {
                alertElement.classList.add('fade-out');
            }, 3000);

            // Remove the alert from the DOM after 6 seconds (3 seconds for delay + 3 seconds for fade-out)
            setTimeout(function () {
                alertElement.style.display = 'none';
            }, 6000);
        }
    };

    </script>
</body>
</html>
