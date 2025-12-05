<?php
// Prevent any HTML error output
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header explicitly
header('Content-Type: application/json; charset=utf-8');

require 'config.php'; // Ensure db.php does not have a closing tag with spaces after it!

$response = array("success" => false, "message" => "Unknown error");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;

    if ($recipe_id > 0) {
        $stmt = $conn->prepare("DELETE FROM recipes WHERE id = ?");
        $stmt->bind_param("i", $recipe_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response["success"] = true;
                $response["message"] = "Recipe deleted";
            } else {
                $response["message"] = "Recipe not found or already deleted";
            }
        } else {
            $response["message"] = "Database Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response["message"] = "Invalid Recipe ID";
    }
} else {
    $response["message"] = "Invalid Request Method";
}

// Clear any previous output buffer and output JSON
ob_clean();
echo json_encode($response);
$conn->close();
?>