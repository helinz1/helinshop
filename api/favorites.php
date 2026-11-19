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


if (!$user_id) {

    echo json_encode([
        "success" => false,
        "message" => "user_id gerekli."
    ]);

    exit;
}


try {

    $stmt = $conn->prepare("
        SELECT
            p.id,
            p.title,
            p.price,
            p.description,
            p.image,
            p.created_at,
            p.category_id,
            u.name AS user_name,
            c.name AS category_name

        FROM favorites f

        INNER JOIN products p
            ON f.product_id = p.id

        INNER JOIN users u
            ON p.user_id = u.id

        LEFT JOIN categories c
            ON p.category_id = c.id

        WHERE f.user_id = ?

        ORDER BY f.created_at DESC
    ");


    $stmt->execute([
        $user_id
    ]);


    $favorites =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


    // ========================================
    // ÜRÜNLERİ DÜZENLE
    // ========================================

    foreach ($favorites as &$product) {

        // Kategori yoksa
        if (empty($product["category_name"])) {

            $product["category_name"] =
                "Kategorisiz";
        }


        // ========================================
        // IMAGE URL
        // ========================================

        if (!empty($product["image"])) {

            $imagePath =
                str_replace(
                    "\\",
                    "/",
                    $product["image"]
                );


            $imagePath =
                ltrim(
                    $imagePath,
                    "/"
                );


            $product["image_url"] =
                APP_URL . "/" .
                $imagePath;

        } else {

            $product["image_url"] =
                null;
        }
    }


    echo json_encode([
        "success" => true,
        "favorites" => $favorites
    ]);


} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Favoriler alınamadı."
    ]);
}


exit;