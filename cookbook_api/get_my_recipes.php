<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);

    // Select only the columns we need
    $result = $conn->query("
        SELECT id, title, ingredients, image_path
        FROM recipes
        WHERE user_id = $user_id
        ORDER BY id DESC
    ");

    $response = array();

    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }

    echo json_encode([
        "success" => true,
        "recipes" => $response
    ]);
}

$conn->close();
?>
