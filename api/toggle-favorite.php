<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Sadece POST isteği kabul edilir."
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

    // Önce bu ürün zaten favoride mi kontrol et
    $checkStmt = $conn->prepare("
        SELECT id
        FROM favorites
        WHERE user_id = ?
        AND product_id = ?
        LIMIT 1
    ");

    $checkStmt->execute([
        $user_id,
        $product_id
    ]);

    $favorite = $checkStmt->fetch(PDO::FETCH_ASSOC);


    // Eğer zaten favorideyse sil
    if ($favorite) {

        $deleteStmt = $conn->prepare("
            DELETE FROM favorites
            WHERE user_id = ?
            AND product_id = ?
        ");

        $deleteStmt->execute([
            $user_id,
            $product_id
        ]);

        echo json_encode([
            "success" => true,
            "is_favorite" => false,
            "message" => "Ürün favorilerden çıkarıldı."
        ]);

        exit;
    }


    // Favoride değilse ekle
    $insertStmt = $conn->prepare("
        INSERT INTO favorites (
            user_id,
            product_id
        )
        VALUES (?, ?)
    ");

    $insertStmt->execute([
        $user_id,
        $product_id
    ]);

    echo json_encode([
        "success" => true,
        "is_favorite" => true,
        "message" => "Ürün favorilere eklendi."
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Favori işlemi sırasında hata oluştu."
    ]);
}

exit;