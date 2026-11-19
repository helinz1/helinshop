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
$product_id = trim( $_POST["product_id"] ?? "");

if (
    empty($user_id) ||
    empty($product_id)
) {
    echo json_encode([
        "success" => false,
        "message" => "Kullanıcı veya ürün bilgisi eksik."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, quantity
    FROM cart
    WHERE user_id = ?
    AND product_id = ?
");

$stmt->bind_param("ii", $user_id, $product_id);

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

if($row) {

    $updateStmt = $conn->prepare("
        UPDATE cart
        SET quantity = quantity + 1
        WHERE user_id = ?
        AND product_id = ?
    ");

    $updateStmt->bind_param("ii", $user_id, $product_id);

    $updateStmt->execute();
}else {
     $insertStmt = $conn->prepare("
        INSERT INTO cart (
            user_id,
            product_id,
            quantity
        )
        VALUES (?, ?, 1)
    ");

    $insertStmt->bind_param(
        "ii",
        $user_id,
        $product_id
    );

    $insertStmt->execute();
}
echo json_encode([
    "success" => true,
    "message" => "Ürün sepete eklendi."
]);

$stmt->close();

if (isset($updateStmt)) {
    $updateStmt->close();
}

if (isset($insertStmt)) {
    $insertStmt->close();
}

$conn->close();
