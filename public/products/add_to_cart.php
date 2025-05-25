<?php


require_once __DIR__ . '/../auth/db_connect.php';

$user_id = $_SESSION['user_id'];
$product_type = $_POST['product_type'] ?? '';
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = 1;

if ($product_id <= 0 || !in_array($product_type, ['currency', 'account'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

$stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_type=? AND product_id=?");
$stmt->bind_param("isi", $user_id, $product_type, $product_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    if ($product_type === 'currency') {
        $update = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id=? AND product_type=? AND product_id=?");
        $update->bind_param("isi", $user_id, $product_type, $product_id);
        $update->execute();
        $update->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'This account is already in your cart.']);
        $stmt->close();
        exit;
    }
} else {
    $insert = $conn->prepare("INSERT INTO cart (user_id, product_type, product_id, quantity) VALUES (?, ?, ?, ?)");
    $insert->bind_param("isii", $user_id, $product_type, $product_id, $quantity);
    $insert->execute();
    $insert->close();
}
$stmt->close();

$count_stmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_stmt->bind_result($cart_count);
$count_stmt->fetch();
$count_stmt->close();

echo json_encode(['success' => true, 'cart_count' => $cart_count]);