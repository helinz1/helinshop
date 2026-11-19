<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

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
$order_id = $_GET["order_id"] ?? null;

if (!$user_id || !$order_id) {
    echo json_encode([
        "success" => false,
        "message" => "user_id ve order_id gerekli."
    ]);
    exit;
}


/* =========================
   ADMIN KONTROLÜ
========================= */

$stmt = $conn->prepare(
    "SELECT role FROM users WHERE id = ?"
);

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user["role"] !== "admin") {
    echo json_encode([
        "success" => false,
        "message" => "Bu işlem için yetkiniz yok."
    ]);
    exit;
}


/* =========================
   SİPARİŞİ GETİR
========================= */

$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
");

$stmt->execute([$order_id]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo json_encode([
        "success" => false,
        "message" => "Sipariş bulunamadı."
    ]);
    exit;
}


/* =========================
   SİPARİŞ ÜRÜNLERİ
========================= */

$stmt = $conn->prepare("
    SELECT
        oi.id,
        oi.product_id,
        oi.quantity,
        oi.price,
        p.title,
        p.image
    FROM order_items oi
    LEFT JOIN products p
        ON p.id = oi.product_id
    WHERE oi.order_id = ?
");

$stmt->execute([$order_id]);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   CEVAP
========================= */

echo json_encode([
    "success" => true,
    "order" => $order,
    "items" => $items
]);

exit;