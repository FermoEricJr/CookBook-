<?php
// update_recipe.php
require 'db.php';
require 'helpers.php';

$user = auth_user($pdo);
if (!$user) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

// Allow multipart (image) or JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES)) {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $ingredients = $_POST['ingredients'] ?? null;
    $steps = $_POST['steps'] ?? null;
    $category = $_POST['category'] ?? null;

    if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }

    // confirm owner
    $check = $pdo->prepare("SELECT user_id, image_path FROM recipes WHERE id = ?");
    $check->execute([$id]); $r = $check->fetch();
    if (!$r) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
    if ($r['user_id'] != $user['id']) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }

    $image_path = $r['image_path'];
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
        $stmt = $pdo->prepare("UPDATE recipes SET title = ?, description = ?, ingredients = ?, steps = ?, category = ?, image_path = ? WHERE id = ?");
        $stmt->execute([$title, $description, $ingredients, $steps, $category, $image_path, $id]);
        echo json_encode(['success'=>true]);
    } catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
} else {
    // JSON update
    $data = json_input();
    $id = $data['id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error'=>'Missing id']); exit; }
    // owner check
    $check = $pdo->prepare("SELECT user_id FROM recipes WHERE id = ?");
    $check->execute([$id]); $r = $check->fetch();
    if (!$r) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }
    if ($r['user_id'] != $user['id']) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }

    $title = $data['title'] ?? null;
    $description = $data['description'] ?? null;
    $ingredients = $data['ingredients'] ?? null;
    $steps = $data['steps'] ?? null;
    $category = $data['category'] ?? null;

    try {
        $stmt = $pdo->prepare("UPDATE recipes SET title = ?, description = ?, ingredients = ?, steps = ?, category = ? WHERE id = ?");
        $stmt->execute([$title, $description, $ingredients, $steps, $category, $id]);
        echo json_encode(['success'=>true]);
    } catch (Exception $e) { http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
}
?>
