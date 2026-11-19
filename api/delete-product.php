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

$product_id = $_POST["product_id"] ?? "";
$user_id = $_POST["user_id"] ?? "";
 
if (empty($product_id) || empty($user_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Ürün veya kullanıcı bilgisi eksik."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "DELETE FROM products
     WHERE id = ?
     AND user_id = ?"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Ürün silinemedi."
    ]);
    exit;
}

$stmt->bind_param("ii", $product_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Ürün başarıyla silindi."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Ürün bulunamadı veya bu ürün sana ait değil."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Ürün silinemedi."
    ]);
}

$stmt->close();
$conn->close();