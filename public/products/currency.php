<?php

require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

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
  <title>Game Currency</title>
  <link rel="stylesheet" href="../styles/style.css">
  <link rel="stylesheet" href="../styles/currency.css">
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
      <a href="currency.php">Game Currency</a>
      <a href="accounts.php">Game Accounts</a>
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

  <div class="currency-content">
    <div class="currency-sidebar">
      <button class="currency-category active" data-game="all">
        <div class="section-wrap">
          <img src="../data/images/all_icon.png" class="section-icon" alt="All Items">
          <div class="sidebar-text">All Games</div>
        </div>
      </button>
      <button class="currency-category" data-game="league_of_legends">
        <div class="section-wrap">
          <img src="../data/images/lol_icon.png" class="section-icon" alt="LoL Currencies">
          <div class="sidebar-text">League of Legends</div>
        </div>
      </button>
      <button class="currency-category" data-game="world_of_tanks">
        <div class="section-wrap">
          <img src="../data/images/wot_icon.png" class="section-icon" alt="WoT Currencies">
          <div class="sidebar-text">World of Tanks</div>
        </div>
      </button>
      <button class="currency-category" data-game="fortnite">
        <div class="section-wrap">
          <img src="../data/images/fortnite_icon.png" class="section-icon" alt="Fortnite Currencies">
          <div class="sidebar-text">Fortnite</div>
        </div>
      </button>
      <button class="currency-category" data-game="pokemon_go">
        <div class="section-wrap">
          <img src="../data/images/pokemongo_icon.png" class="section-icon" alt="Pokemon GO Currencies">
          <div class="sidebar-text">Pokemon GO!</div>
        </div>
      </button>
      <button class="currency-category" data-game="marvel_rivals">
        <div class="section-wrap">
          <img src="../data/images/marvelrivals_icon.png" class="section-icon" alt="Marvel Rivals Currencies">
          <div class="sidebar-text">Marvel Rivals</div>
        </div>
      </button>
    </div>
    <div class="currency-main">
      <div class="currency-header">
        <h2 id="currency-header-title">Game Currency</h2>
        <div class="sorting">
          <div style="margin: 24px 0;">
            <label for="currency-select"><b>Show prices in:</b></label>
            <select id="currency-select">
              <option value="EUR" selected>Euro (€)</option>
              <option value="USD">US Dollar ($)</option>
              <option value="KRW">South Korean Won (₩)</option>
              <option value="JPY">Japanese Yen (¥)</option>
              <option value="CHF">Swiss Franc (Fr)</option>
            </select>
          </div>
            <div>
              <label for="sort-select"><b>Sort by:</b></label>
              <select id="sort-select">
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
              </select>
            </div>
        </div>
      </div>
      <div class="currency-list">
        <?php
        $result = $conn->query("SELECT * FROM game_currency ORDER BY created_at DESC");
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
        ?>
          <div class="currency-card" data-category="<?php echo strtolower(str_replace(' ', '_', $row['game_name'])); ?>"
              data-created-at="<?php echo htmlspecialchars($row['created_at']); ?>">
            <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Currency Image" class="currency-img">
            <div class="currency-name">
              <?php echo htmlspecialchars($row['amount']) . ' ' . htmlspecialchars($row['currency_name']); ?>
            </div>
            <div class="currency-price"
                data-base-price="<?php echo htmlspecialchars($row['price']); ?>">
              <?php echo number_format($row['price'], 2); ?> €
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <form method="post" action="remove_currency.php" onsubmit="return confirm('Are you sure you want to remove this currency?');" style="display:inline;">
                <input type="hidden" name="currency_id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="remove-product-btn">Remove Product</button>
              </form>
            <?php else: ?>
             <button class="add-to-cart-btn" data-product-id="<?php echo $row['id']; ?>" data-product-type="currency">Add to Cart</button>
            <?php endif; ?>
          </div>
        <?php
            endwhile;
        else:
            echo "<p>No currency available.</p>";
        endif;
        ?>
      </div>
    </div>
  </div>
  <script src="../script/script.js"></script>
  <script src="../script/currency.js"></script>
  <script src="../script/config.js"></script>
</body>
</html>