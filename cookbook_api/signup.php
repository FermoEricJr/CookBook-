<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email already registered"
        ]);
        exit;
    }

    // Insert new user
    $stmt = $conn->prepare("
        INSERT INTO users (firstname, lastname, email, password)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $firstname, $lastname, $email, $password);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Account created successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error creating account"
        ]);
    }

    $stmt->close();
}

$conn->close();
?>
