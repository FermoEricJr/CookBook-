<?php
// helpers.php
require_once 'db.php';

function json_input() {
    return json_decode(file_get_contents('php://input'), true);
}

function respond($data) {
    echo json_encode($data);
    exit;
}

/**
 * Simple auth: expects Authorization: Bearer <token>
 * For this starter token == user_id returned on login/register.
 * In production, replace with JWT.
 */
function get_bearer_token() {
    $headers = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    if (!$headers) return null;
    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }
    return null;
}

function auth_user($pdo) {
    $token = get_bearer_token();
    if (!$token) return null;
    // token is user_id in this starter
    $stmt = $pdo->prepare("SELECT id, username, email, profile_image FROM users WHERE id = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    return $user ?: null;
}

/** Safe file name builder */
function unique_filename($original) {
    $ext = pathinfo($original, PATHINFO_EXTENSION);
    $safe = preg_replace("/[^a-zA-Z0-9-_\.]/","_", pathinfo($original, PATHINFO_FILENAME));
    return time() . "_" . $safe . ($ext ? ".".$ext : "");
}
?>
