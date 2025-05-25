<?php

require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$gameIconMap = [
    'League of Legends' => 'lol_icon.png',
    'Fortnite' => 'fortnite_icon.png',
    'World of Tanks' => 'wot_icon.png',
    'Pokemon GO' => 'pokemongo_icon.png',
    'Marvel Rival' => 'marvelrivals_icon.png',
];

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($cart_count);
    $stmt->fetch();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart - Checkout</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/cart.css">
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
  <div class="content">
    <div class="cart-container">
      <?php if ($cart_count == 0): ?>
        <div class="empty-cart">
          <h2>Your cart is empty</h2>
          <p>Browse our products and add items to your cart.</p>
          <a href="../products/currency.php" class="shop-btn">Shop Now</a>
        </div>
      <?php else: ?>
        <h2>Your Cart</h2>
        <?php if ($result->num_rows === 0): ?>
          <p>Your cart is empty.</p>
        <?php else: ?>
            <div class="cart-list">
            <?php
            while ($row = $result->fetch_assoc()) {
                $type = $row['product_type'];
                $product_id = $row['product_id'];
                $quantity = $row['quantity'];
                $price = 0;
                $name = '';
                $imgSrc = '';
                if ($type === 'currency') {
                    $prod_stmt = $conn->prepare("SELECT currency_name, amount, price, image_path FROM game_currency WHERE id = ?");
                    $prod_stmt->bind_param("i", $product_id);
                    $prod_stmt->execute();
                    $prod_stmt->bind_result($currency_name, $amount, $prod_price, $image_path);
                    if ($prod_stmt->fetch()) {
                        $name = htmlspecialchars($amount . ' ' . $currency_name);
                        $price = $prod_price;
                        $imgSrc = $image_path ? htmlspecialchars($image_path) : '../data/images/default_currency.png';
                    }
                    $prod_stmt->close();
                } elseif ($type === 'account') {
                    $prod_stmt = $conn->prepare("SELECT game_name, price FROM game_accounts WHERE id = ?");
                    $prod_stmt->bind_param("i", $product_id);
                    $prod_stmt->execute();
                    $prod_stmt->bind_result($game_name, $prod_price);
                    if ($prod_stmt->fetch()) {
                        $name = htmlspecialchars($game_name . ' Account');
                        $price = $prod_price;
                        $icon_file = isset($gameIconMap[$game_name]) ? $gameIconMap[$game_name] : strtolower(preg_replace('/\s+/', '', $game_name)) . '_icon.png';
                        $icon_path = '../data/images/' . $icon_file;
                        if (!file_exists(__DIR__ . '/../data/images/' . $icon_file)) {
                            $icon_path = '../data/images/default_game_icon.png';
                        }
                        $imgSrc = $icon_path;
                    }
                    $prod_stmt->close();
                }
                $total = $price * $quantity;
                $grand_total += $total;
                ?>
                <div class="cart-item">
                    <div class="cart-item-left">
                        <img src="<?php echo $imgSrc; ?>" class="cart-thumb" alt="Product" />
                        <div class="cart-item-info">
                            <div class="cart-item-name"><?php echo $name; ?></div>
                            <div class="cart-item-qty">
                                <form method="post" action="update_cart.php" style="display:inline;">
                                    <input type="hidden" name="action" value="decrease">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <input type="hidden" name="product_type" value="<?php echo $type; ?>">
                                    <button type="submit" <?php if ($quantity <= 1) echo "disabled"; ?>>-</button>
                                </form>
                                <span class="cart-qty-num"><?php echo $quantity; ?></span>
                                <form method="post" action="update_cart.php" style="display:inline;">
                                    <input type="hidden" name="action" value="increase">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <input type="hidden" name="product_type" value="<?php echo $type; ?>">
                                    <button type="submit">+</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="cart-item-right">
                        <form method="post" action="update_cart.php" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="product_type" value="<?php echo $type; ?>">
                            <button type="submit" class="cart-delete-btn" title="Remove">&times;</button>
                        </form>
                        <div class="cart-item-price"><?php echo number_format($total, 2); ?> €</div>
                    </div>
                </div>
            <?php } ?>
            </div>
        <?php endif; ?>
      </div>
      <div class="cart-checkout">
        <div class="cart-grand-total">
          <b>Total: <?php echo number_format($grand_total, 2); ?> €</b>
        </div>
        <form method="post" action="checkout.php">
            <button type="submit" class="checkout-btn">Proceed to Checkout</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

</body>
</html>