<?php

require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
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
            <a href="../profile/your_orders.php">
              <div class="icon-wrap">
                <img src="../data/images/order_icon.png" class="small-icon" alt="Orders">
              </div>
              <div>Your Orders</div>
            </a>
            <a href="../profile/profile.php">
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
        <a href="../cart.php">
          <img src="../data/images/cart_icon.png" alt="Cart" class="cart-icon">
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
        <h2 id="currency-header-title">All Game Accounts</h2>
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
        $sql = "
          SELECT ga.*, gap.photo_path
          FROM game_accounts ga
          LEFT JOIN (
              SELECT account_id, MIN(id) as min_photo_id
              FROM game_account_photos
              GROUP BY account_id
          ) first_photos ON ga.id = first_photos.account_id
          LEFT JOIN game_account_photos gap ON gap.id = first_photos.min_photo_id
          ORDER BY ga.created_at DESC
        ";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <div class="currency-card"
               data-category="<?php echo strtolower(str_replace(' ', '_', $row['game_name'])); ?>"
               data-created-at="<?php echo htmlspecialchars($row['created_at']); ?>">
          <img src="<?php echo htmlspecialchars('/WebProgProject/public/' . ($row['photo_path'] ?? 'data/images/default_account.png')); ?>"
                 alt="Account Image"
                 class="currency-img"
                 style="width:100px;height:100px;object-fit:cover;">
            <div class="currency-name">
              <?php echo htmlspecialchars($row['game_name']); ?>
            </div>
            <div class="currency-price"
                 data-base-price="<?php echo htmlspecialchars($row['price']); ?>">
              <?php echo number_format($row['price'], 2); ?> €
            </div>
            <div class="currency-description">
              <?php echo nl2br(htmlspecialchars($row['description'])); ?>
            </div>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <form method="post" action="remove_account.php" onsubmit="return confirm('Are you sure you want to remove this currency?');" style="display:inline;">
                <input type="hidden" name="account_id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="remove-product-btn">Remove Product</button>
              </form>
            <?php else: ?>
             <button class="add-to-cart-btn">Add to Cart</button>
            <?php endif; ?>
          </div>
        <?php
          endwhile;
        else:
          echo "<p>No accounts available.</p>";
        endif;
        ?>
      </div>
    </div>
  </div>
  <script src="../script/script.js"></script>
  <script src="../script/accounts.js"></script>
  <script src="../script/config.js"></script>
</body>
</html>