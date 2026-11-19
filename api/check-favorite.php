<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode([
        "success" => false,
        "message" => "Sadece GET isteği kabul edilir."
    ]);
    exit;
}

$user_id = $_GET["user_id"] ?? null;
$product_id = $_GET["product_id"] ?? null;

if (!$user_id || !$product_id) {
    echo json_encode([
        "success" => false,
        "message" => "user_id ve product_id gerekli."
    ]);
    exit;
}

try {

    $stmt = $conn->prepare("
        SELECT id
        FROM favorites
        WHERE user_id = ?
        AND product_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $user_id,
        $product_id
    ]);

    $favorite = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "is_favorite" => $favorite ? true : false
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Favori durumu kontrol edilemedi."
    ]);
}

exit;