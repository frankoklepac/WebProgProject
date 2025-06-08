<?php

require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$product_type = $_POST['product_type'] ?? '';
$action = $_POST['action'] ?? '';

if ($product_id <= 0 || !in_array($product_type, ['currency', 'account'])) {
    header("Location: cart.php");
    exit;
}

if ($action === 'increase' && $product_type === 'currency') {
    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id=? AND product_type=? AND product_id=?");
    $stmt->bind_param("isi", $user_id, $product_type, $product_id);
    $stmt->execute();
    $stmt->close();
} elseif ($action === 'decrease' && $product_type === 'currency') {
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id=? AND product_type=? AND product_id=?");
    $stmt->bind_param("isi", $user_id, $product_type, $product_id);
    $stmt->execute();
    $stmt->bind_result($quantity);
    if ($stmt->fetch() && $quantity > 1) {
        $stmt->close();
        $update = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id=? AND product_type=? AND product_id=?");
        $update->bind_param("isi", $user_id, $product_type, $product_id);
        $update->execute();
        $update->close();
    } else {
        $stmt->close();
    }
} elseif ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_type=? AND product_id=?");
    $stmt->bind_param("isi", $user_id, $product_type, $product_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: cart.php");
exit;