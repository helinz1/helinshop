<?php
include("../database/db.php");

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION["user"]["id"];
$message = "";

$stmt = $conn->prepare("SELECT id, name, mail, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Kullanıcı bulunamadı!";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $mail = $_POST["mail"];
    $new_password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    $profileImagePath = $user["profile_image"] ?? "";

    if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] == 0) {

        $imageOriginalName = $_FILES["profile_image"]["name"];
        $imageTmp = $_FILES["profile_image"]["tmp_name"];

        $imageExtension = pathinfo($imageOriginalName, PATHINFO_EXTENSION);
        $imageName = time() . "_" . uniqid() . "." . $imageExtension;

        $profileImagePath = "uploads/" . $imageName;
        $uploadPath = "../" . $profileImagePath;

        move_uploaded_file($imageTmp, $uploadPath);
    }

    if (!empty($new_password)) {

        if ($new_password != $confirm_password) {
            $message = "Şifreler eşleşmiyor.";
        } else {

            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

            $update = $conn->prepare(
                "UPDATE users 
                 SET name = ?, mail = ?, password = ?, profile_image = ? 
                 WHERE id = ?"
            );

            $update->bind_param("ssssi", $name, $mail, $hashedPassword, $profileImagePath, $id);

            if ($update->execute()) {
                $_SESSION["user"]["name"] = $name;
                $_SESSION["user"]["mail"] = $mail;
                $_SESSION["user"]["profile_image"] = $profileImagePath;

                header("Location: user.php?id=" . $id);
                exit;
            } else {
                $message = "Güncelleme başarısız.";
            }
        }

    } else {

        $update = $conn->prepare(
            "UPDATE users 
             SET name = ?, mail = ?, profile_image = ? 
             WHERE id = ?"
        );

        $update->bind_param("sssi", $name, $mail, $profileImagePath, $id);

        if ($update->execute()) {
            $_SESSION["user"]["name"] = $name;
            $_SESSION["user"]["mail"] = $mail;
            $_SESSION["user"]["profile_image"] = $profileImagePath;

            header("Location: user.php?id=" . $id);
            exit;
        } else {
            $message = "Güncelleme başarısız.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">

<div class="admin-container">
    <div class="admin-card user-view-card">

        <h2>Edit Profile</h2>

        <?php if ($message != ""): ?>
            <p class="error-message"><?= $message; ?></p>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="edit-form">

            <h3 class="form-section-title">👤 Profile Information</h3>

            <div class="input-box">
                <input 
                    type="text" 
                    name="name" 
                    value="<?= $user['name']; ?>" 
                    required
                >
            </div>

            <div class="input-box">
                <input 
                    type="email" 
                    name="mail" 
                    value="<?= $user['mail']; ?>" 
                    required
                >
            </div>

            <div class="input-box">
                <label class="upload-label">Profile Photo</label>

                <input
                    type="file"
                    name="profile_image"
                    accept="image/*">
            </div>

            <div class="form-divider"></div>

            <h3 class="form-section-title">🔒 Change Password</h3>

            <div class="input-box">
                <input 
                    type="password" 
                    name="new_password" 
                    placeholder="New Password"
                >
            </div>

            <div class="input-box">
                <input 
                    type="password" 
                    name="confirm_password" 
                    placeholder="Confirm Password"
                >
            </div>

            <button type="submit" class="save-btn">Save Changes</button>

        </form>

        <a href="user.php" class="back-btn">Back to Profile</a>

    </div>
</div>

</body>
</html>