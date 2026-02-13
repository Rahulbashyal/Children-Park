<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'montessori');

// Check for any connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the uploads directory exists, if not, create it
$uploadDirectory = 'uploads/';
if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['popup_image'])) {
    $imageName = basename($_FILES['popup_image']['name']);
    $imageTmpName = $_FILES['popup_image']['tmp_name'];
    $uploadFilePath = $uploadDirectory . $imageName;

    // Move uploaded file to the uploads directory
    if (move_uploaded_file($imageTmpName, $uploadFilePath)) {
        // Update the image path in the database
        $sql = "UPDATE popup_ad SET image_path = '$uploadFilePath' WHERE id = 1";
        if ($conn->query($sql) === TRUE) {
            $message = "Popup image updated successfully.";
        } else {
            $message = "Failed to update the database: " . $conn->error;
        }
    } else {
        $message = "Failed to upload the image.";
    }
}

// Fetch the current popup image
$sql = "SELECT image_path FROM popup_ad WHERE id = 1";
$result = $conn->query($sql);
$currentImage = $result->fetch_assoc()['image_path'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Popup Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <style>
        /* Custom styles for consistency across pages */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }
        
        .user-logo {
            font-size: 2em;
            line-height: 1.5;
        }
        
        #content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .container {
            margin-top: 50px;
        }
        h2, h4 {
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
        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.05);
        }
        .card-header {
            background-color: #ff7f0e;
            color: #fff;
            font-weight: 600;
        }
        /* Responsive styles */
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
        }
        @media (max-width: 576px) {
            #sidebar ul li {
                padding: 8px;
            }
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

    <div id="content">
        <div class="container">
            <h2>Popup Ad Manager</h2>
            <?php if (isset($message)): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="popup_image" class="form-label">Choose Popup Image</label>
                    <input type="file" class="form-control" name="popup_image" id="popup_image" required>
                </div>
                <button type="submit" class="btn btn-custom">Update Popup Image</button>
            </form>
            <div class="mt-4">
                <h4>Current Popup Image:</h4>
                <?php if (!empty($currentImage)): ?>
                    <img src="<?php echo $currentImage; ?>" alt="Popup Ad" style="max-width: 200px;">
                <?php else: ?>
                    <p>No popup image uploaded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
