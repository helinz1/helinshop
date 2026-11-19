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

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode([
            "success" => false,
            "message" => "Sadece POST isteği kabul edilir."
        ]);
        exit;
    }

    $user_id = $_POST["user_id"] ?? "";
    $order_id = $_POST["order_id"] ?? "";
    $status = trim($_POST["status"] ?? "");

    if (
        empty($user_id) ||
        empty($order_id) ||
        $status === ""
    ) {
        echo json_encode([
            "success" => false,
            "message" => "user_id, order_id ve status gerekli."
        ]);
        exit;
    }

    // Kullanıcı admin mi?
    $userStmt = $conn->prepare("
        SELECT role
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $userStmt->bind_param(
        "i",
        $user_id
    );

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

    if ($user["role"] !== "admin") {
        echo json_encode([
            "success" => false,
            "message" => "Bu işlem için yetkiniz yok."
        ]);
        exit;
    }

    $allowedStatuses = [
        "Hazırlanıyor",
        "Kargoya Verildi",
        "Teslim Edildi",
        "İptal Edildi"
    ];

    if (!in_array($status, $allowedStatuses, true)) {
        echo json_encode([
            "success" => false,
            "message" => "Geçersiz sipariş durumu."
        ]);
        exit;
    }

    // Sipariş var mı?
    $checkStmt = $conn->prepare("
        SELECT id
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");

    $checkStmt->bind_param(
        "i",
        $order_id
    );

    $checkStmt->execute();

    $checkResult = $checkStmt->get_result();

    if (!$checkResult->fetch_assoc()) {
        echo json_encode([
            "success" => false,
            "message" => "Sipariş bulunamadı."
        ]);
        exit;
    }

    // Durumu güncelle
    $stmt = $conn->prepare("
        UPDATE orders
        SET status = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "si",
        $status,
        $order_id
    );

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Sipariş durumu güncellendi.",
        "order_id" => (int)$order_id,
        "status" => $status
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Sunucu hatası.",
    ]);
}