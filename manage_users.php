<?php
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

// Handle form submission using PRG pattern to avoid duplicate data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash password

    // Insert user into the database
    $sql = "INSERT INTO users (username, role, password) VALUES ('$username', '$role', '$password')";
    if ($conn->query($sql) === TRUE) {
        // Redirect to avoid form resubmission on refresh
        header("Location: manage_users.php?success=1");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle update password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    $sql = "UPDATE users SET password='$new_password' WHERE uid='$user_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_users.php?update=1");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Handle delete user
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE uid='$user_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_users.php?deleted=1");
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch users from the database
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
   <!-- Bootstrap 5 CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts for modern, clean typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS for the dashboard -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Include Dashboard Styles -->
    <link rel="stylesheet" href="./css/sidebar.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f7f7;
        }

       /* Main Content */
#content {
    margin-left: 260px;
    padding: 40px;
    width: calc(100% - 260px);
    color: #333;
}


        .card {
            border-radius: 12px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color:black;
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


    <div id="content">
        <div class="container-fluid mt-4">
            <div class="card">
                <div class="card-header">
                    Manage Users
                </div>
                <div class="card-body">
                    <!-- Success Message -->
<div id="alert-success" class="alert alert-success" role="alert" style="display:none;">User added successfully!</div>
<div id="alert-update" class="alert alert-success" role="alert" style="display:none;">Password updated successfully!</div>
<div id="alert-delete" class="alert alert-success" role="alert" style="display:none;">User deleted successfully!</div>

                    <!-- Add User Form -->
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="teacher">Teacher</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="add_user">Add User</button>
                    </form>

                    <!-- Display Existing Users -->
                    <h3 class="mt-5">Existing Users</h3>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['uid'] ?></td>
                                        <td><?= $row['username'] ?></td>
                                        <td><?= $row['role'] ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal" data-userid="<?= $row['uid'] ?>">Edit</button>
                                            <a href="manage_users.php?delete=<?= $row['uid'] ?>" class="btn btn-sm btn-outline-danger">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Password Change -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" id="user_id" name="user_id">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="update_password">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript to show alerts and handle modal interactions -->
    <script>
       

        // Fill in user ID when edit button is clicked
        document.querySelectorAll('[data-userid]').forEach(button => {
            button.addEventListener('click', function() {
                var userId = this.getAttribute('data-userid');
                var userField = document.getElementById('user_id');
                userField.value = userId;
            });
        });
        
        function showAlert(id) {
    const alert = document.getElementById(id);
    alert.style.display = 'block';  // Show the alert

    // Automatically hide the alert after 3 seconds
    setTimeout(() => {
        alert.classList.add('fade-out');  // Start fading out
        setTimeout(() => {
            alert.style.display = 'none';  // Fully hide after fade-out
            alert.classList.remove('fade-out');  // Reset class for next time
        }, 1000);  // Time for fade-out transition
    }, 3000);  // Wait for 3 seconds before fading out
}

// Display success, update, or delete alerts if conditions are met
<?php if (isset($_GET['success'])): ?>
    showAlert('alert-success');
<?php elseif (isset($_GET['update'])): ?>
    showAlert('alert-update');
<?php elseif (isset($_GET['deleted'])): ?>
    showAlert('alert-delete');
<?php endif; ?>

    </script>
</body>

</html>
