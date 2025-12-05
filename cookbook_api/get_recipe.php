<?php
// get_recipe.php
require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

$stmt = $pdo->prepare("SELECT r.*, u.username, u.profile_image FROM recipes r JOIN users u ON r.user_id=u.id WHERE r.id = ?");
$stmt->execute([$id]);
$recipe = $stmt->fetch();
if (!$recipe) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }

$ratingStmt = $pdo->prepare("SELECT IFNULL(ROUND(AVG(stars),2),0) as avg, COUNT(*) as count FROM ratings WHERE recipe_id = ?");
$ratingStmt->execute([$id]); $rating = $ratingStmt->fetch();

$recipe['avg_rating'] = $rating['avg'];
$recipe['rating_count'] = $rating['count'];

echo json_encode($recipe);
?>
