<?php
// Database setup script for Children Park Montessori System

$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection without database
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h2>Setting up Children Park Montessori Database...</h2>";
    
    // Read and execute SQL file
    $sql = file_get_contents('database_setup.sql');
    
    if ($conn->multi_query($sql)) {
        do {
            // Store first result set
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        
        echo "<p style='color: green;'>✓ Database and tables created successfully!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error creating database: " . $conn->error . "</p>";
    }
    
    // Create necessary directories
    $directories = [
        'uploads',
        'uploads/gallery',
        'uploads/news_images'
    ];
    
    echo "<h3>Creating Upload Directories...</h3>";
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0777, true)) {
                echo "<p style='color: green;'>✓ Created directory: $dir</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to create directory: $dir</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ Directory already exists: $dir</p>";
        }
    }
    
    echo "<h3>Default Login Credentials:</h3>";
    echo "<p><strong>Admin:</strong><br>";
    echo "Username: admin<br>";
    echo "Password: password</p>";
    
    echo "<p><strong>Teacher:</strong><br>";
    echo "Username: teacher1<br>";
    echo "Password: password</p>";
    
    echo "<p style='color: orange;'><strong>Note:</strong> Please change these default passwords after first login!</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Visit <a href='login.php'>login.php</a> to access the system</li>";
    echo "<li>Use admin credentials to access the dashboard</li>";
    echo "<li>Upload images to the gallery and manage content</li>";
    echo "</ul>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup Complete</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2 { color: #333; }
        h3 { color: #666; }
        p { margin: 10px 0; }
        ul { margin: 10px 0; }
    </style>
</head>
<body>
</body>
</html>