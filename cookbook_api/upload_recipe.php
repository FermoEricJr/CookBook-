<?php
// 1. Set JSON Header
header('Content-Type: application/json; charset=utf-8');

// 2. Disable error printing (prevents HTML warnings from breaking JSON)
error_reporting(0);
ini_set('display_errors', 0);

// 3. Database Connection
if (!file_exists('db.php')) {
    echo json_encode(["success" => false, "message" => "Server Error: db.php not found"]);
    exit;
}
require 'config.php';

// 4. Check Connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database Connection Failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 5. Get Inputs (using Null Coalescing for safety)
    $title       = isset($_POST['title']) ? trim($_POST['title']) : '';
    $ingredients = isset($_POST['ingredients']) ? trim($_POST['ingredients']) : '';
    $user_id     = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    $final_image_url = null;

    // 6. Handle Image Upload
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        
        $target_dir = "uploads/";

        // Create directory if missing
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                echo json_encode(["success" => false, "message" => "Server failed to create upload folder"]);
                exit;
            }
        }

        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate Extension
        $allowed = ["jpg", "jpeg", "png", "webp"];
        if (!in_array($imageFileType, $allowed)) {
            echo json_encode(["success" => false, "message" => "Only JPG, JPEG, PNG, WEBP allowed"]);
            exit;
        }

        // Move File
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Generate URL
            $server_ip = "http://26.78.102.240"; 
            $api_folder = "/cookbook_api"; 
            $final_image_url = $server_ip . $api_folder . "/" . $target_file;
        } else {
            echo json_encode(["success" => false, "message" => "Failed to write file. Check server permissions."]);
            exit;
        }
    }

    // 7. Insert into Database
    if ($user_id > 0) {
        $stmt = $conn->prepare("INSERT INTO recipes (user_id, title, ingredients, image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $ingredients, $final_image_url);

        if ($stmt->execute()) {
            echo json_encode([
                "success" => true, 
                "message" => "Recipe uploaded successfully",
                "image_url" => $final_image_url
            ]);
        } else {
            // Check for User ID error (Foreign Key Constraint)
            if ($conn->errno == 1452) {
                echo json_encode(["success" => false, "message" => "User ID ($user_id) does not exist in the database."]);
            } else {
                echo json_encode(["success" => false, "message" => "DB Error: " . $stmt->error]);
            }
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid User ID. Please Log In again."]);
    }
    
    $conn->close();

} else {
    echo json_encode(["success" => false, "message" => "Invalid Request Method"]);
}
?>