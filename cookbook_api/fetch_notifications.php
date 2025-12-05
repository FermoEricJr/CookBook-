<?php
// fetch_notifications.php
require 'config.php';
header('Content-Type: application/json');

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id > 0) {
    // Get notifications for this specific user, newest first
    $sql = "SELECT id, title, message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = array();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    echo json_encode($notifications);
} else {
    echo json_encode([]);
}
?>