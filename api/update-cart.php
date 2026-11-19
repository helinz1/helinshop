<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Sadece POST isteği kabul edilir."
    ]);
    exit;
}

$conn = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME
);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Veritabanı bağlantı hatası."
    ]);
    exit;
}

$user_id = trim($_POST["user_id"] ?? "");
$product_id = trim($_POST["product_id"] ?? "");
$quantity = trim($_POST["quantity"] ?? "");

if (
    empty($user_id) ||
    empty($product_id) ||
    empty($quantity)
) {
    echo json_encode([
        "success" => false,
        "message" => "Sepet bilgileri eksik."
    ]);
    exit;
}

$stmt = $conn->prepare("
    UPDATE cart
    SET quantity = ?
    WHERE user_id = ?
    AND product_id = ?
");

$stmt->bind_param(
    "iii",
    $quantity,
    $user_id,
    $product_id
);

$stmt->execute();

echo json_encode([
    "success" => true,
    "message" => "Sepet güncellendi."
]);

$stmt->close();
$conn->close();