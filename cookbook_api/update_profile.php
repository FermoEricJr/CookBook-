<?php
// update_profile.php
require 'db.php';
require 'helpers.php';

$user = auth_user($pdo);
if (!$user) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }

// This endpoint supports multipart (for image) OR JSON for text-only updates.
// If multipart: use $_POST and $_FILES; otherwise read JSON.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES)) {
    // multipart update (profile image + optional username/email/password)
    $username = $_POST['username'] ?? $user['username'];
    $email = $_POST['email'] ?? $user['email'];
    $password = $_POST['password'] ?? null;

    $profile_image = $user['profile_image'];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploads_dir = __DIR__ . '/uploads';
        if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);
        $tmp = $_FILES['profile_image']['tmp_name'];
        $name = unique_filename($_FILES['profile_image']['name']);
        $dest = $uploads_dir . '/' . $name;
        if (move_uploaded_file($tmp, $dest)) {
            $profile_image = 'uploads/'.$name;
        }
    }

    $params = [$username, $email, $profile_image, $user['id']];
    $sql = "UPDATE users SET username = ?, email = ?, profile_image = ? WHERE id = ?";
    if ($password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username = ?, email = ?, profile_image = ?, password = ? WHERE id = ?";
        $params = [$username, $email, $profile_image, $hash, $user['id']];
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success'=>true, 'user'=>['id'=>$user['id'],'username'=>$username,'email'=>$email,'profile_image'=>$profile_image]]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['error'=>$e->getMessage()]);
    }
} else {
    // JSON path: update fields without image
    $data = json_input();
    $username = $data['username'] ?? $user['username'];
    $email = $data['email'] ?? $user['email'];
    $password = $data['password'] ?? null;

    try {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hash, $user['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $user['id']]);
        }
        echo json_encode(['success'=>true, 'user'=>['id'=>$user['id'],'username'=>$username,'email'=>$email,'profile_image'=>$user['profile_image']]]);
    } catch (Exception $e) {
        http_response_code(500); echo json_encode(['error'=>$e->getMessage()]);
    }
}
?>
