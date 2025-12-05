<?php
// toggle_like.php
require 'config.php';
header('Content-Type: application/json');

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;

if ($user_id > 0 && $recipe_id > 0) {
    // 1. Check if already liked
    $check = $conn->query("SELECT id FROM likes WHERE user_id=$user_id AND recipe_id=$recipe_id");

    if ($check->num_rows > 0) {
        // UNLIKE: Remove from database
        $conn->query("DELETE FROM likes WHERE user_id=$user_id AND recipe_id=$recipe_id");
        $action = "unliked";
    } else {
        // LIKE: Add to database
        $conn->query("INSERT INTO likes (user_id, recipe_id) VALUES ($user_id, $recipe_id)");
        $action = "liked";

        // --- NOTIFICATION LOGIC START ---
        // Find who owns the recipe
        $owner_query = $conn->query("SELECT user_id, title FROM recipes WHERE id=$recipe_id");
        if ($row = $owner_query->fetch_assoc()) {
            $owner_id = $row['user_id'];
            $recipe_title = $row['title'];

            // Only notify if the liker is NOT the owner
            if ($owner_id != $user_id) {
                // Get liker's name for the message
                $liker_query = $conn->query("SELECT firstname FROM users WHERE id=$user_id");
                $liker_name = ($liker_query->fetch_assoc())['firstname'] ?? "Someone";

                $message = "$liker_name liked your recipe '$recipe_title'";
                
                // Secure Insert
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'New Like', ?)");
                $stmt->bind_param("is", $owner_id, $message);
                $stmt->execute();
            }
        }
        // --- NOTIFICATION LOGIC END ---
    }
    echo json_encode(array("success" => true, "action" => $action));
} else {
    echo json_encode(array("success" => false, "message" => "Invalid ID"));
}
?>