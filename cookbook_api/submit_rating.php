<?php
// submit_rating.php
require 'config.php';
header('Content-Type: application/json');

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
$stars = isset($_POST['stars']) ? (int)$_POST['stars'] : 0;

if ($user_id > 0 && $recipe_id > 0 && $stars > 0) {
    
    // Insert or Update Rating (ON DUPLICATE KEY UPDATE handles re-rating)
    $sql = "INSERT INTO ratings (user_id, recipe_id, stars) VALUES ($user_id, $recipe_id, $stars)
            ON DUPLICATE KEY UPDATE stars=$stars";

    if ($conn->query($sql)) {
        
        // --- NOTIFICATION LOGIC START ---
        $owner_query = $conn->query("SELECT user_id, title FROM recipes WHERE id=$recipe_id");
        if ($row = $owner_query->fetch_assoc()) {
            $owner_id = $row['user_id'];
            $recipe_title = $row['title'];

            if ($owner_id != $user_id) {
                $liker_query = $conn->query("SELECT firstname FROM users WHERE id=$user_id");
                $liker_name = ($liker_query->fetch_assoc())['firstname'] ?? "Someone";

                $message = "$liker_name rated '$recipe_title' $stars stars!";
                
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'New Rating', ?)");
                $stmt->bind_param("is", $owner_id, $message);
                $stmt->execute();
            }
        }
        // --- NOTIFICATION LOGIC END ---

        echo json_encode(array("success" => true));
    } else {
        echo json_encode(array("success" => false, "message" => "DB Error"));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Invalid Input"));
}
?>