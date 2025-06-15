<?php

require_once __DIR__ . '/../auth/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../auth/login.php");
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    header("Location: profile.php?section=profile&pwmsg=empty");
    exit;
}

if ($new_password !== $confirm_password) {
    header("Location: profile.php?section=profile&pwmsg=nomatch");
    exit;
}

if ($new_password === $current_password) {
    header("Location: profile.php?section=profile&pwmsg=same");
    exit;
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($current_password, $hash)) {
    header("Location: profile.php?section=profile&pwmsg=wrongcurrent");
    exit;
}

$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $new_hash, $user_id);
if ($stmt->execute()) {
    header("Location: profile.php?section=profile&pwmsg=success");
    exit;
} else {
    header("Location: profile.php?section=profile&pwmsg=error");
    exit;
}
?>