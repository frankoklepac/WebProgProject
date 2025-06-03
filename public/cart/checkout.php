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

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout</title>
  <link rel="stylesheet" href="../styles/style.css">
  <link rel="stylesheet" href="../styles/checkout.css">
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
    <h2>Checkout</h2>
    <div>
      <div class="checkout-form">
        <form method="post" action="payment_method.php" class="styled-checkout-form">
          <div class="row-fields">
            <div style="flex:2;">
              <label for="street"><b>Street Name</b></label>
              <input type="text" id="street" name="street" required>
            </div>
            <div style="flex:1; min-width:90px;">
              <label for="house_number"><b>House Number</b></label>
              <input type="text" id="house_number" name="house_number" required>
            </div>
          </div>

          <div class="row-fields">
            <div style="flex:1; min-width:90px;">
              <label for="postal_code"><b>Postal Code</b></label>
              <input type="text" id="postal_code" name="postal_code" required>
            </div>
            <div style="flex:2;">
              <label for="city"><b>City</b></label>
              <input type="text" id="city" name="city" required>
            </div>
          </div>

          <label for="country"><b>Country</b></label>
          <input type="text" id="country" name="country" required>

          <label for="contact"><b>Contact Number</b></label>
          <input type="text" id="contact" name="contact" required>

          <button type="submit" name="go_to_payment">Go to payment</button>
        </form>
      </div>
    </div>
  </div>

</body>
</html>