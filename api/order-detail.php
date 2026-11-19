<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/config.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASSWORD,
        DB_NAME
    );

    $conn->set_charset("utf8mb4");

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        echo json_encode([
            "success" => false,
            "message" => "Sadece GET isteği kabul edilir."
        ]);
        exit;
    }

    $order_id = $_GET["order_id"] ?? "";
    $user_id = $_GET["user_id"] ?? "";

    if (empty($order_id) || empty($user_id)) {
        echo json_encode([
            "success" => false,
            "message" => "order_id ve user_id gerekli."
        ]);
        exit;
    }


    // =====================================================
    // SİPARİŞ BİLGİLERİ
    // =====================================================

    $stmt = $conn->prepare("
        SELECT
            id,
            user_id,
            total_price,
            status,
            customer_name,
            customer_phone,
            customer_email,
            city,
            district,
            neighborhood,
            address,
            postal_code,
            payment_method,
            created_at
        FROM orders
        WHERE id = ? AND user_id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "ii",
        $order_id,
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $order = $result->fetch_assoc();


    if (!$order) {
        echo json_encode([
            "success" => false,
            "message" => "Sipariş bulunamadı."
        ]);
        exit;
    }


    // =====================================================
    // SİPARİŞTEKİ ÜRÜNLER
    // =====================================================

    $itemStmt = $conn->prepare("
        SELECT
            id,
            order_id,
            product_id,
            title,
            price,
            quantity
        FROM order_items
        WHERE order_id = ?
        ORDER BY id ASC
    ");

    $itemStmt->bind_param(
        "i",
        $order_id
    );

    $itemStmt->execute();

    $itemResult =
        $itemStmt->get_result();

    $items = [];

    while (
        $row =
        $itemResult->fetch_assoc()
    ) {
        $items[] = $row;
    }


    echo json_encode([
        "success" => true,
        "order" => $order,
        "items" => $items
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Sunucu hatası.",
    ]);
}