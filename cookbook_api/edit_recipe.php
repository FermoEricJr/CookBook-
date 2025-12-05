<?php
// edit_recipe.php
// 1. CLEAR HEADERS AND DISABLE HTML ERRORS
error_reporting(0); 
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

require 'config.php';

$response = array("success" => false, "message" => "Unknown error");

// 2. CHECK REQUEST METHOD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $ingredients = isset($_POST['ingredients']) ? trim($_POST['ingredients']) : '';

    if ($recipe_id > 0 && !empty($title)) {
        
        $new_image_url = null;

        // 3. HANDLE IMAGE UPLOAD
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
            $target_dir = "uploads/";
            
            // Create folder if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $image_name = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_name;
            
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // UPDATE THIS IP TO MATCH YOUR COMPUTER'S IP
                $server_ip = "http://26.78.102.240"; 
                $api_folder = "/cookbook_api"; 
                $new_image_url = $server_ip . $api_folder . "/" . $target_file;
            } else {
                $response["message"] = "Failed to move uploaded file. Check folder permissions.";
                echo json_encode($response);
                exit();
            }
        }

        // 4. UPDATE DATABASE
        if ($new_image_url) {
            // Update WITH new image
            $stmt = $conn->prepare("UPDATE recipes SET title=?, ingredients=?, image_path=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $ingredients, $new_image_url, $recipe_id);
        } else {
            // Update WITHOUT changing image
            $stmt = $conn->prepare("UPDATE recipes SET title=?, ingredients=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $ingredients, $recipe_id);
        }

        if ($stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Recipe updated successfully";
        } else {
            $response["message"] = "Database Error: " . $stmt->error;
        }
        $stmt->close();

    } else {
        $response["message"] = "Invalid ID or Title";
    }
} else {
    $response["message"] = "Invalid Request Method";
}

// 5. OUTPUT JSON
echo json_encode($response);
$conn->close();
?>