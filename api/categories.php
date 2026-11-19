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

try {

    $stmt = $conn->prepare("
        SELECT id, name
        FROM categories
        ORDER BY name ASC
    ");

    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "categories" => $categories
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "success" => false,
        "message" => "Kategoriler alınamadı."
    ]);

}

exit;
