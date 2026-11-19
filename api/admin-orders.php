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

    $user_id = $_GET["user_id"] ?? "";

    if (empty($user_id)) {
        echo json_encode([
            "success" => false,
            "message" => "user_id gerekli."
        ]);
        exit;
    }

    // İsteği yapan kullanıcıyı kontrol et
    $userStmt = $conn->prepare("
        SELECT id, role
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();

    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "Kullanıcı bulunamadı."
        ]);
        exit;
    }

    // Sadece admin
    if ($user["role"] !== "admin") {
        http_response_code(403);

        echo json_encode([
            "success" => false,
            "message" => "Bu işlem için yetkiniz yok."
        ]);
        exit;
    }

    // Tüm siparişleri getir
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
        ORDER BY id DESC
    ");

    $stmt->execute();

    $result = $stmt->get_result();

    $orders = [];

    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    echo json_encode([
        "success" => true,
        "orders" => $orders
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Sunucu hatası.",
    ]);
}