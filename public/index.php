<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkinBaazar</title>
  <link rel="stylesheet" href="styles/style.css">
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
      <a href="index.php">Home</a>
      <a href="products/currency.php">Game Currency</a>
      <a href="products/accounts.php">Game Accounts</a>
      <a href="contact.php">Contact Us</a>
    </div>
    <div class="nav-right">
      <div class="profile-btn">
        <img src="data/images/profile_icon.png" alt="Profile" class="profile-icon" id="profileIcon">
        <div class="profile-dropdown" id="profileDropdown">
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin/admin_panel.php">
              <div class="icon-wrap">
                <img src="data/images/panel_icon.png" class="small-icon" alt="Admin Panel">
              </div>
              <div>Admin Panel</div>
            </a>
          <?php endif; ?>
          <?php if (isset($_SESSION['username'])): ?>
            <a href="profile/your_orders.php">
              <div class="icon-wrap">
                <img src="data/images/order_icon.png" class="small-icon" alt="Orders">
              </div>
              <div>Your Orders</div>
            </a>
            <a href="profile/profile.php">
              <div>
                <img src="data/images/lock_icon.png" class="small-icon" alt="Profile">
              </div>
              <div>Your Profile and Security</div>
            </a>
            <a href="auth/logout.php" class="logout-link">Logout</a>
          <?php else: ?>
            <a href="auth/login.php">Login</a>
            <a href="auth/register.php">Register</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="cart">
        <a href="cart.php">
          <img src="data/images/cart_icon.png" alt="Cart" class="cart-icon">
        </a>
      </div>
    </div>
  </div>
  <div class="container">

  </div>
  <div class="footer">

  </div>

  <script src="script/script.js"></script>
</body>
</html>