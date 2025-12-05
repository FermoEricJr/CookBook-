<?php
// create_recipe.php
require 'db.php';
require 'helpers.php';

$user = auth_user($pdo);
if (!$user) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

// Expect multipart/form-data: title, description, ingredients, steps, category, image (optional)
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$ingredients = $_POST['ingredients'] ?? '';
$steps = $_POST['steps'] ?? '';
$category = $_POST['category'] ?? null;

if (!$title) { http_response_code(400); echo json_encode(['error'=>'Missing title']); exit; }

$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploads_dir = __DIR__.'/uploads';
    if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);
    $tmp = $_FILES['image']['tmp_name'];
    $name = unique_filename($_FILES['image']['name']);
    $dest = $uploads_dir . '/' . $name;
    if (move_uploaded_file($tmp, $dest)) {
        $image_path = 'uploads/'.$name;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO recipes (user_id, title, description, ingredients, steps, category, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user['id'], $title, $description, $ingredients, $steps, $category, $image_path]);
    $id = (int)$pdo->lastInsertId();
    echo json_encode(['success'=>true, 'id'=>$id, 'image_path'=>$image_path]);
} catch (Exception $e) {
    http_response_code(500); echo json_encode(['error'=>$e->getMessage()]);
}
?>
