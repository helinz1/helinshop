<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/config.php";

$id = $_GET["id"] ?? "";

if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "Ürün ID bulunamadı."
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

$conn->set_charset("utf8mb4");

$stmt = $conn->prepare("
    SELECT
        products.id,
        products.title,
        products.price,
        products.description,
        products.image,
        products.created_at,
        products.category_id,

        users.name AS user_name,

        categories.name AS category_name

    FROM products

    INNER JOIN users
        ON products.user_id = users.id

    LEFT JOIN categories
        ON products.category_id = categories.id

    WHERE products.id = ?

    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Sorgu hazırlanamadı."
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();

if ($product) {

    // Eski ürünlerde kategori seçilmemiş olabilir.
    if (
        empty($product["category_name"])
    ) {
        $product["category_name"] =
            "Kategorisiz";
    }

    echo json_encode([
        "success" => true,
        "product" => $product
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Ürün bulunamadı."
    ]);
}

$stmt->close();
$conn->close();

exit;