<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT id, firstname, lastname, password, profile_image 
        FROM users 
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $firstname, $lastname, $hash, $profile_image);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();

        if (password_verify($password, $hash)) {

            echo json_encode([
                "success" => true,
                "user" => [
                    "id" => $id,
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "email" => $email,
                    "profile_image" => $profile_image
                ]
            ]);

        } else {
            echo json_encode([
                "success" => false,
                "message" => "Incorrect password"
            ]);
        }

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Email not found"
        ]);
    }

    $stmt->close();
}

$conn->close();
?>
