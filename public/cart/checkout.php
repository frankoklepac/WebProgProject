<?php

require_once __DIR__ . '/../auth/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_now'])) {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $address = $_POST['address'];

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

                $order_stmt = $conn->prepare("INSERT INTO orders (buyer_id, currency_id, amount, price) VALUES (?, ?, ?, ?)");
                $order_stmt->bind_param("iiid", $user_id, $product_id, $quantity, $price);
                $order_stmt->execute();
                $order_stmt->close();
            } elseif ($product_type === 'account') {
                $prod_stmt = $conn->prepare("SELECT price FROM game_accounts WHERE id = ?");
                $prod_stmt->bind_param("i", $product_id);
                $prod_stmt->execute();
                $prod_stmt->bind_result($price);
                $prod_stmt->fetch();
                $prod_stmt->close();

                $order_stmt = $conn->prepare("INSERT INTO orders (buyer_id, account_id, amount, price) VALUES (?, ?, 1, ?)");
                $order_stmt->bind_param("iid", $user_id, $product_id, $price);
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


    }
    header("Location: ../index.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout</title>
  <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <div class="navbar">
    <div class="nav-left">
      <?php if (isset($_SESSION['username'])): ?>
        <h1>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?> to SkinBaazar</h1>
      <?php else: ?>
        <h1>Welcome to SkinBaazar</h1>
      <?php endif; ?>    
    </div>
    <div class="hamburger" id="hamburger">
      <span></span>
      <span></span>
      <span></span>
    </div>
    <div class="nav-center">
      <a href="../index.php">Home</a>
      <a href="../products/currency.php">Game Currency</a>
      <a href="../products/accounts.php">Game Accounts</a>
      <a href="../contact.php">Contact Us</a>
    </div>
    <div class="nav-right">
      <div class="profile-btn">
        <img src="../data/images/profile_icon.png" alt="Profile" class="profile-icon" id="profileIcon">
        <div class="profile-dropdown" id="profileDropdown">
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="../admin/admin_panel.php">
              <div class="icon-wrap">
                <img src="../data/images/panel_icon.png" class="small-icon" alt="Admin Panel">
              </div>
              <div>Admin Panel</div>
            </a>
          <?php endif; ?>
          <?php if (isset($_SESSION['username'])): ?>
            <a href="../profile/profile.php?section=orders">
              <div class="icon-wrap">
                <img src="../data/images/order_icon.png" class="small-icon" alt="Orders">
              </div>
              <div>Your Orders</div>
            </a>
            <a href="../profile/profile.php?section=profile">
              <div>
                <img src="../data/images/lock_icon.png" class="small-icon" alt="Profile">
              </div>
              <div>Your Profile and Security</div>
            </a>
            <a href="../auth/logout.php" class="logout-link">Logout</a>
          <?php else: ?>
            <a href="../auth/login.php">Login</a>
            <a href="../auth/register.php">Register</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="cart">
      <?php
        $cart_count = 0;
        if (isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($cart_count);
            $stmt->fetch();
            $stmt->close();
        }
        ?>
        <a href="../cart/cart.php">
          <div class="cart-icon-container">
            <img src="../data/images/cart_icon.png" alt="Cart" class="cart-icon">
            <?php if ($cart_count > 0): ?>
              <span class="cart-count"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </div>
        </a>
      </div>
    </div>
  </div>

  <div class="container" style="margin: 40px auto; max-width: 500px;">
    <h2>Checkout</h2>
    <form method="post">
      <label for="address"><b>Shipping Address:</b></label>
      <input type="text" id="address" name="address" required style="width:100%;margin-bottom:16px;">
      <button type="submit" name="order_now" style="width:100%;padding:12px 0;background:#28a745;color:#fff;border:none;border-radius:5px;font-size:1.1em;cursor:pointer;">Order Now</button>
    </form>
  </div>

</body>
</html>