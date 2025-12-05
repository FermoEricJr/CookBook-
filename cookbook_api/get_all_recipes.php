<?php
// get_all_recipes.php
require 'config.php';
header('Content-Type: application/json');

$current_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

$sql = "SELECT r.*, u.firstname, u.lastname, u.profile_image,
        (SELECT COUNT(*) FROM likes WHERE recipe_id = r.id) as total_likes,
        (SELECT COUNT(*) FROM likes WHERE recipe_id = r.id AND user_id = $current_user_id) as is_liked,
        (SELECT IFNULL(AVG(stars), 0) FROM ratings WHERE recipe_id = r.id) as avg_rating,
        (SELECT COUNT(*) FROM ratings WHERE recipe_id = r.id) as total_ratings
        FROM recipes r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.id DESC";

$result = $conn->query($sql);
$recipes = array();

while($row = $result->fetch_assoc()) {
    $recipes[] = $row;
}

echo json_encode(array("success" => true, "recipes" => $recipes));
$conn->close();
?>