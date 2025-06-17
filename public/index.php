<?php
require_once __DIR__ . '/auth/db_connect.php';

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
  <title>SkinBaazar</title>
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/index.css">
</head>
<body>
  <div class="navbar">
    <div class="nav-left">
      <?php if (isset($_SESSION['username'])): ?>
        <h1>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
      <?php else: ?>
        <h1>Hello, guest!</h1>
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
            <a href="profile/profile.php?section=orders">
              <div class="icon-wrap">
                <img src="data/images/order_icon.png" class="small-icon" alt="Orders">
              </div>
              <div>Your Orders</div>
            </a>
            <a href="profile/profile.php?section=profile">
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
        <a href="cart/cart.php">
          <div class="cart-icon-container">
            <img src="data/images/cart_icon.png" alt="Cart" class="cart-icon">
            <?php if ($cart_count > 0): ?>
              <span class="cart-count"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </div>
        </a>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="welcome">
      <h1 class="animate-fly-up">Welcome to SkinBaazar</h1>
      <p class="animate-fly-up">Your one-stop shop for game accounts and in-game currencies.</p>
    </div>
    <div class="currency-showcase">
      <div class="image-showcase-grid">
        <img src="data/images/currency/fortnite_vbucks.png" alt="Fortnite V-Bucks" class="showcase-img animate-fly-left">
        <img src="data/images/currency/lol_riotpoints.png" alt="Riot Points" class="showcase-img animate-fly-left">
        <img src="data/images/currency/marvelrivals_lattice.png" alt="Lattice" class="showcase-img animate-fly-left">
        <img src="data/images/currency/pokemongo_pokecoins.png" alt="PokeCoins" class="showcase-img animate-fly-left">
        <img src="data/images/currency/wot_gold.png" alt="WoT Gold" class="showcase-img animate-fly-left">
      </div>
      <div class="currency-text-showcase">
        <h2 class="animate-fly-up">Game Currency</h2>
        <p class="animate-fly-up">Buy in-game currencies securely and easily.</p>
        <p class="animate-fly-up">Buy in-game currencies such as: </p>
        <ul class="animate-fly-up" >
          <li class="animate-fly-up">V-Bucks</li>
          <li class="animate-fly-up">Riot Points</li>
          <li class="animate-fly-up">Lattice</li>
          <li class="animate-fly-up">PokeCoins</li>
          <li class="animate-fly-up">Gold</li>
        </ul>
        <a href="products/currency.php" class="btn animate-fly-up">Order Now!</a>
        
      </div>
    </div>
    <div class="accounts-showcase">
      <div class="accounts-text-showcase">
        <h2 class="animate-fly-up">Game Accounts</h2>
        <p class="animate-fly-up">Buy game accounts securely and easily.</p>
        <p class="animate-fly-up">We offer accounts for popular games such as:</p>
        <ul class="animate-fly-up">
          <li class="animate-fly-up">Fortnite</li>
          <li class="animate-fly-up">League of Legends</li>
          <li class="animate-fly-up">Marvel Rivals</li>
          <li class="animate-fly-up">Pokemon Go</li>
          <li class="animate-fly-up">World of Tanks</li>
        </ul>
        <a href="products/accounts.php" class="btn animate-fly-up">Order Now!</a>
      </div>
      <div class="image-showcase">
        <div class="image-showcase-grid">
          <img src="data/images/fortnite_icon.png" alt="Fortnite V-Bucks" class="showcase-img animate-fly-right">
          <img src="data/images/lol_icon.png" alt="Riot Points" class="showcase-img animate-fly-right">
          <img src="data/images/marvelrivals_icon.png" alt="Lattice" class="showcase-img animate-fly-right">
          <img src="data/images/pokemongo_icon.png" alt="PokeCoins" class="showcase-img animate-fly-right">
          <img src="data/images/wot_icon.png" alt="WoT Gold" class="showcase-img animate-fly-right">
        </div>
      </div>
    </div>

    <div class="sell-showcase">
      <div>
        <img src="data/images/sell_icon.png" alt="Sell Icon" class="showcase-img animate-fly-up">
        <h2 class="animate-fly-up">Sell Your Game Accounts</h2>
        <p class="animate-fly-up">Have a game account you no longer use? Sell it on SkinBaazar and earn money!</p>
        <a href="profile/profile.php?section=sell-account" class="btn animate-fly-up">Sell Now!</a>
      </div>
    </div>
  </div>
  <div class="footer">
    <p>
      SkinBaazar is not affiliated with any game developers or publishers. For educational purposes only. 
    </p>
  </div>

  <script src="script/script.js"></script>
  <script src="script/index.js"></script>

</body>
</html>