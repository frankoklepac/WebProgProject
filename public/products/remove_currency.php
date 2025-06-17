<?php
require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: currency.php");
    exit;
}

if (isset($_POST['currency_id'])) {
    $currency_id = intval($_POST['currency_id']);

    $stmt = $conn->prepare("DELETE FROM game_currency WHERE id = ?");
    $stmt->bind_param("i", $currency_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: currency.php");
exit;