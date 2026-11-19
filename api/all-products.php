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

$conn->set_charset("utf8mb4");

$sql = "
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
ORDER BY products.id DESC
";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Ürünler alınamadı."
    ]);
    exit;
}

$products = [];

while ($row = $result->fetch_assoc()) {

    $imagePath = $row["image"] ?? "";
    $imageUrl = "";

    if (!empty($imagePath)) {

        $normalizedImagePath = str_replace(
            "\\",
            "/",
            trim($imagePath)
        );

        if (
            preg_match(
                "#^https?://#i",
                $normalizedImagePath
            )
        ) {
            $imageUrl = $normalizedImagePath;

        } else {

            $normalizedImagePath = ltrim(
                $normalizedImagePath,
                "/"
            );

            $imageUrl =
                APP_URL . "/" .
                $normalizedImagePath;
        }
    }

    $row["image_url"] = $imageUrl;

    if (empty($row["category_name"])) {
        $row["category_name"] = "Kategorisiz";
    }

    $products[] = $row;
}

echo json_encode([
    "success" => true,
    "products" => $products
], JSON_UNESCAPED_UNICODE);

$conn->close();