<?php
// 1. You must start the session before using $_SESSION
session_start(); 

include '../database/db.php';

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit;
}

$mail = $_POST["mail"];
$password = $_POST["password"];

// 2. Prepare the query using the ? placeholder
$stmt = $conn->prepare("SELECT * FROM users WHERE mail = ?");

if (!$stmt) {
    // PDO error handling
    die("SQL hatası: " . print_r($conn->errorInfo(), true));
}

// 3. Execute the statement by passing the variable in an array
// This automatically binds the ? to $mail
$stmt->execute([$mail]);

// 4. Fetch the result directly using PDO syntax
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If fetch() returns a row, the user exists
if ($user) {
    
    if (password_verify($password, $user["password"])) {
        
        $_SESSION["user"] = $user;

        if (isset($user["role"]) && $user["role"] == "admin") {
            header("Location: admin.php");
            exit;
        } else {
            header("Location: user.php");
            exit;
        }
        
    } else {
        echo "Giriş başarısız. Şifre yanlış.";
    }

} else {
    echo "Bu mail kayıtlı değil.";
}
?>