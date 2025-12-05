<?php
// rate_recipe.php
require 'db.php';
require 'helpers.php';

$user = auth_user($pdo);
if (!$user) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$recipe_id = $data['recipe_id'] ?? null;
$stars = (int)($data['stars'] ?? 0);

if (!$recipe_id || $stars < 1 || $stars > 5) { http_response_code(400); echo json_encode(['error'=>'Bad request']); exit; }

try {
    $stmt = $pdo->prepare("INSERT INTO ratings (user_id, recipe_id, stars) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE stars = VALUES(stars), created_at = CURRENT_TIMESTAMP");
    $stmt->execute([$user['id'], $recipe_id, $stars]);

    $r = $pdo->prepare("SELECT IFNULL(ROUND(AVG(stars),2),0) as avg, COUNT(*) as count FROM ratings WHERE recipe_id = ?");
    $r->execute([$recipe_id]); $rating = $r->fetch();
    echo json_encode(['success'=>true, 'avg'=>$rating['avg'], 'count'=>$rating['count']]);
} catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
?>
