<?php
// get_recipes.php
require 'db.php';

$sql = "SELECT r.id, r.title, r.description, r.ingredients, r.steps, r.category, r.image_path, r.created_at,
            u.id as user_id, u.username, u.profile_image,
            IFNULL(ROUND( (SELECT AVG(stars) FROM ratings WHERE recipe_id = r.id),2), 0) as avg_rating,
            (SELECT COUNT(*) FROM ratings WHERE recipe_id = r.id) as rating_count
        FROM recipes r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();
echo json_encode($rows);
?>
