<?php

header("Content-Type: application/json");

// db.php'nin kesin yolu
require_once dirname(__DIR__) . "/database/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Sadece POST isteği kullanılabilir."
    ]);
    exit;
}

$user_id = $_POST["user_id"] ?? null;
$product_id = $_POST["product_id"] ?? null;

if (!$user_id || !$product_id) {
    echo json_encode([
        "success" => false,
        "message" => "user_id ve product_id gerekli."
    ]);
    exit;
}

try {

    $stmt = $conn->prepare("
        DELETE FROM cart
        WHERE user_id = ? AND product_id = ?
    ");

    $stmt->execute([
        $user_id,
        $product_id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Ürün sepetten kaldırıldı."
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Ürün silinirken bir hata oluştu."
    ]);
}