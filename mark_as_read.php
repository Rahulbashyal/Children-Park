<?php
// Get the notification ID from the URL
$notification_id = $_GET['id'];

// Mark the notification as read
$sql = "UPDATE notifications SET status = 'read' WHERE id = '$notification_id'";
if ($conn->query($sql) === TRUE) {
    header("Location: dashboard_teacher.php"); // Redirect back to the dashboard
} else {
    echo "Error updating notification: " . $conn->error;
}
?>
