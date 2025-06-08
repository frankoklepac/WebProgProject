<?php

require_once __DIR__ . '/../auth/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}
$username = $_POST['username'] ?? null;
if (empty($username)) {
    header("Location: profile.php?error=1");
    exit;
}

$date_of_birth = $_POST['dob'] ?? null;
$phone_number = $_POST['phone'] ?? null;


$stmt = $conn->prepare("UPDATE users SET username = ?, date_of_birth = ?, phone_number = ? WHERE id = ?");
$stmt->bind_param("sssi", $username, $date_of_birth, $phone_number, $user_id);

if ($stmt->execute()) {
    header("Location: profile.php?section=profile");
    exit;
} else {
    header("Location: profile.php?section=profile");
    exit;
}
$stmt->close();
?>