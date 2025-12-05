<?php
include "config.php";

if(isset($_POST['user_id']) && isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['email'])) {

    $user_id = $_POST['user_id'];
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $email = $conn->real_escape_string($_POST['email']);

    $sql = "UPDATE users SET firstname='$firstname', lastname='$lastname', email='$email' WHERE id=$user_id";

    if($conn->query($sql)) {
        echo json_encode([
            "success" => true,
            "message" => "Profile updated successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Update failed: " . $conn->error
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Missing parameters"
    ]);
}

$conn->close();
?>
