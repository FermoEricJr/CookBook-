<?php
include "config.php";

// Get user_id from POST
if (!isset($_POST['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "User ID not provided"
    ]);
    exit;
}

$user_id = intval($_POST['user_id']);

// Fetch user info from database
$sql = "SELECT id, firstname, lastname, email, profile_image, bio FROM users WHERE id = $user_id LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // If bio is null in DB, return empty string
    if (!isset($user['bio'])) {
        $user['bio'] = "";
    }

    echo json_encode([
        "success" => true,
        "user" => $user
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
}

$conn->close();
?>
