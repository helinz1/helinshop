<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/config.php";

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

$user_id = $_GET["user_id"] ?? "";

if (empty($user_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Kullanıcı bilgisi eksik."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT
        cart.id,
        cart.quantity,
        products.id AS product_id,
        products.title,
        products.price,
        products.image
    FROM cart
    INNER JOIN products
        ON cart.product_id = products.id
    WHERE cart.user_id = ?
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$cart = [];

while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
}

echo json_encode([
    "success" => true,
    "cart" => $cart
]);

$stmt->close();
$conn->close();