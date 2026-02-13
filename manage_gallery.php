
<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in'])) {
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
if (isset($_POST['add_gallery'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // File upload logic
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/gallery/'; // Specify the upload directory
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . time() . '_' . $fileName;

        // Ensure the directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the file to the target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            // Store the file path in the database
            $stmt = $conn->prepare("INSERT INTO gallery (title, description, image_url) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $description, $targetFilePath);
            $stmt->execute();

        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
}
// deleting funtion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get the image path
    $stmt = $conn->prepare("SELECT image_url FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $filePath = $row['image_url'];

    // Delete the image file from the server
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete the database record
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: manage_gallery.php");
    exit();
}

// Fetch all gallery items
$galleryResult = $conn->query("SELECT * FROM gallery");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts for modern, clean typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS for the dashboard -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <style>
      /* General Styling for the page */
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f7f7f7;
}

#content {
    margin-left: 250px;
    padding: 20px;
    transition: margin-left 0.3s;
}

/* Sidebar styling for smaller screens */
@media (max-width: 991px) {
    #sidebar {
        width: 100%;
        height: auto;
        position: relative;
        margin-left: 0;
    }

    #content {
        margin-left: 0;
    }
}

/* Gallery card hover and basic styling */
.gallery-card {
    border-radius: 15px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    /* object-fit: ; */
}

.gallery-card:hover {
    transform: scale(1.05);
}

.gallery-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 15px 15px 0 0;
}

/* Add Card styling */
.add-card {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f8f9fa;
    cursor: pointer;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
    height: 250px;
    width: 100%;
    border-radius:15px;
}

.add-btn {
    font-size: 50px;
    color: #007bff;
    font-weight: bold;
    user-select: none;
}

.add-card:hover {
    transform: scale(1.05);
}

.add-card:hover .add-btn {
    color: #0056b3;
}

/* Gallery grid responsiveness */
@media (max-width: 768px) {
    .row-cols-md-3 {
        grid-template-columns: repeat(2, 1fr);
    }
    .row-cols-1 {
        grid-template-columns: 1fr;
    }

    .gallery-card {
        height: auto;
        margin-bottom: 15px;
    }
}

@media (max-width: 576px) {
    .gallery-card img {
        height: 150px;
    }
    .add-btn {
        font-size: 40px;
    }
}

    </style>
</head>
<body>
    <!-- Sidebar -->
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


     <!-- Main Content -->
     <div id="content">
        <div class="container">
            <h2 class="mb-4">Manage Gallery</h2>

            <!-- Gallery Grid -->
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <!-- Add New Card -->
                <div class="col">
                <div class="col">
                <div class="col">
    <!-- Add Card -->
    <div class="col-md-4">
        <div class="card add-card" data-bs-toggle="modal" data-bs-target="#addGalleryModal">
            <div class="card-body d-flex justify-content-center align-items-center">
                <span class="add-btn">+</span>
            </div>
        </div>
    </div>
</div>

</div>
                </div>

                <!-- Existing Gallery Items -->
                <?php while ($row = $galleryResult->fetch_assoc()) { ?>
                    <div class="col">
                        <div class="gallery-card">
                            <img src="<?php echo $row['image_url']; ?>" alt="Gallery Image">
                            <div class="p-3">
                                <h5><?php echo $row['title']; ?></h5>
                                <p><?php echo $row['description']; ?></p>
                                <a href="manage_gallery.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Add Gallery Modal -->
    <div class="modal fade" id="addGalleryModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5>Add Gallery Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Image</label>
                        <input type="file" name="image" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_gallery" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Prevent Form Resubmission on Refresh -->
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
