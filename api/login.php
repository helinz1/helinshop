<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../database/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Sadece POST isteği kabul edilir."
    ]);
    exit;
}

$mail = $_POST["mail"] ?? "";
$password = $_POST["password"] ?? "";

if (empty($mail) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Mail ve şifre boş bırakılamaz."
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE mail = ?");
$stmt->execute([$mail]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Bu mail kayıtlı değil."
    ]);
    exit;
}

if (password_verify($password, $user["password"])) {
    echo json_encode([
        "success" => true,
        "message" => "Hoş geldiniz",
        "user" => [
            "id" => $user["id"],
            "name" => $user["name"],
            "mail" => $user["mail"],
            "role" => $user["role"]
        ]
    ]);
    exit;
}

echo json_encode([
    "success" => false,
    "message" => "Şifre yanlış."
]);

exit;