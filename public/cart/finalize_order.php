<?php

require_once __DIR__ . '/../auth/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_now'])) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['checkout_address'])) {
        $user_id = $_SESSION['user_id'];
        $addressData = $_SESSION['checkout_address'];

        $address = $addressData['street'] . " " . $addressData['house_number'];

        $cart_stmt = $conn->prepare("SELECT product_type, product_id, quantity FROM cart WHERE user_id = ?");
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();

        while ($item = $cart_result->fetch_assoc()) {
            $product_type = $item['product_type'];
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = 0;

            if ($product_type === 'currency') {
                $prod_stmt = $conn->prepare("SELECT price FROM game_currency WHERE id = ?");
                $prod_stmt->bind_param("i", $product_id);
                $prod_stmt->execute();
                $prod_stmt->bind_result($price);
                $prod_stmt->fetch();
                $prod_stmt->close();

                $order_stmt = $conn->prepare("INSERT INTO orders (buyer_id, currency_id, amount, price, address) VALUES (?, ?, ?, ?, ?)");
                $order_stmt->bind_param("iiids", $user_id, $product_id, $quantity, $price, $address);
                $order_stmt->execute();
                $order_stmt->close();
            } elseif ($product_type === 'account') {
                $prod_stmt = $conn->prepare("SELECT price FROM game_accounts WHERE id = ?");
                $prod_stmt->bind_param("i", $product_id);
                $prod_stmt->execute();
                $prod_stmt->bind_result($price);
                $prod_stmt->fetch();
                $prod_stmt->close();

                $order_stmt = $conn->prepare("INSERT INTO orders (buyer_id, account_id, amount, price, address) VALUES (?, ?, 1, ?, ?)");
                $order_stmt->bind_param("iids", $user_id, $product_id, $price, $address);
                $order_stmt->execute();
                $order_stmt->close();

                $update = $conn->prepare("UPDATE game_accounts SET is_sold = 1 WHERE id = ?");
                $update->bind_param("i", $product_id);
                $update->execute();
                $update->close();
            }
        }
        $cart_stmt->close();

        $del_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $del_stmt->bind_param("i", $user_id);
        $del_stmt->execute();
        $del_stmt->close();

        unset($_SESSION['checkout_address']);
    }
    header("Location: ../index.php");
    exit;
}
?>