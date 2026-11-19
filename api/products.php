<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/db.php";

$user_id = $_GET["user_id"] ?? "";

$stmt = $conn->prepare(
    "SELECT * FROM products WHERE user_id = ? ORDER BY id DESC"
);

$stmt->execute([$user_id]);

$products = [];

$product = $stmt->fetch(PDO::FETCH_ASSOC);

while ($product) {
    $products[] = $product;

    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

echo json_encode($products);