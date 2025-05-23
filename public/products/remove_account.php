<?php

session_start();
require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: accounts.php");
    exit;
}

if (isset($_POST['account_id'])) {
    $account_id = intval($_POST['account_id']);

    $photo_stmt = $conn->prepare("SELECT photo_path FROM game_account_photos WHERE account_id = ?");
    $photo_stmt->bind_param("i", $account_id);
    $photo_stmt->execute();
    $photo_stmt->bind_result($photo_path);
    while ($photo_stmt->fetch()) {
        $full_path = realpath(__DIR__ . '/../' . $photo_path);
        if ($full_path && file_exists($full_path)) {
            unlink($full_path); 
        }
    }
    $photo_stmt->close();
    $stmt = $conn->prepare("DELETE FROM game_accounts WHERE id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: accounts.php");
exit;
