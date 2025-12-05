<?php
include "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("
        SELECT id, title, ingredients, image 
        FROM recipes 
        WHERE user_id = ?
        ORDER BY id DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $recipes = [];

    while ($row = $result->fetch_assoc()) {
        $row['image'] = $row['image'] 
            ? "http://192.168.1.3/cookbook_api/uploads/" . $row['image']
            : null;

        $recipes[] = $row;
    }

    echo json_encode([
        "success" => true,
        "recipes" => $recipes
    ]);
}

$conn->close();
?>
