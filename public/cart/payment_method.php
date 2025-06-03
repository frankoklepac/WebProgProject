<?php

require_once __DIR__ . '/../auth/db_connect.php';

$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($cart_count);
    $stmt->fetch();
    $stmt->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['go_to_payment'])) {
    $_SESSION['checkout_address'] = [
        'street' => $_POST['street'],
        'house_number' => $_POST['house_number'],
        'postal_code' => $_POST['postal_code'],
        'city' => $_POST['city'],
        'country' => $_POST['country'],
        'contact' => $_POST['contact']
    ];
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Select Payment Method</title>
  <link rel="stylesheet" href="../styles/style.css">
  <link rel="stylesheet" href="../styles/payment_method.css">
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
  <div class="container">
    <form method="post" action="finalize_order.php" class="payment-method-form" id="paymentForm">
      <div>
        <input type="radio" id="cod" name="payment_method" value="cod" checked>
        <label for="cod"><b>Payment on Delivery</b></label>
      </div>
      <div>
        <input type="radio" id="online" name="payment_method" value="online">
        <label for="online"><b>Online Payment</b></label>
      </div>
      <div id="credit-card-fields" style="display:none; margin-top:16px;">
        <label for="cc_number"><b>Credit Card Number</b></label>
        <input type="text" id="cc_number" name="cc_number" maxlength="19" required>
        <label for="cc_expiry"><b>Expiry Date</b></label>
        <input type="text" id="cc_expiry" name="cc_expiry" placeholder="MM/YY" maxlength="5" required>
        <label for="cc_cvc"><b>CVC</b></label>
        <input type="text" id="cc_cvc" name="cc_cvc" maxlength="4" required>
      </div>
      <button type="submit" name="order_now">Order Now</button>
    </form>
  </div>
  <script src="../script/payment_method.js"></script>
</body>
</html>