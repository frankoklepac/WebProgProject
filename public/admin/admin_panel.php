<?php

require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_POST['add_currency'])) {
    $game = $_POST['game'];
    $currency_name = $_POST['currency_name'];
    $amount = $_POST['amount'];
    $price = $_POST['price'];
    $added_by = $_SESSION['user_id'];
    $image_path = $_POST['image_path'] ?? null;

    $stmt = $conn->prepare("INSERT INTO game_currency (game_name, currency_name, amount, price, image_path, added_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidsi", $game, $currency_name, $amount, $price, $image_path, $added_by);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['add_account'])) {
    $game = $_POST['account_game'];
    $description = $_POST['account_description'];
    $price = $_POST['account_price'];
    $seller_id = $_SESSION['user_id']; 
    
    if ($game && $description && $price) {
        $stmt = $conn->prepare("INSERT INTO game_accounts (game_name, description, price, seller_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $game, $description, $price, $seller_id);
        if ($stmt->execute()) {
            echo "<span style='color:green;'>Game account successfully added!</span>";
        } else {
            echo "<span style='color:red;'>Database error. Please try again.</span>";
        }
        $stmt->close();
    } else {
        echo "<span style='color:red;'>Not all fields were filled.</span>";
    }
}

$result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="../styles/style.css">
  <link rel="stylesheet" href="../styles/admin.css">
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
            <a href="admin_panel.php">
              <div class="icon-wrap">
                <img src="../data/images/panel_icon.png" class="small-icon" alt="Admin Panel">
              </div>
              <div>Admin Panel</div>
            </a>
          <?php endif; ?>
          <?php if (isset($_SESSION['username'])): ?>
            <a href="profile/your_orders.php">
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
  <div class="admin-content">
   <div class="admin-sidebar">
      <button onclick="showSection('add-currency')">
        <div class="section-wrap">
          <img src="../data/images/add_icon.png" alt="" class="section-icon">
          <div class="sidebar-text">Add Game Currency</div>
        </div>
      </button>
      <button onclick="showSection('add-account')">
        <div class="section-wrap">
          <img src="../data/images/add_icon.png" alt="" class="section-icon">
          <div class="sidebar-text">Add Game Account</div>
        </div>
      </button>
      <button onclick="showSection('approve-listings')">
        <div class="section-wrap">
          <img src="../data/images/approve_icon.png" alt="" class="section-icon">
          <div class="sidebar-text">Approve Listings</div>
        </div>
      </button>
      <button onclick="showSection('user-list')">
        <div class="section-wrap">
          <img src="../data/images/user_list_icon.png" alt="" class="section-icon">
          <div class="sidebar-text">User List</div>
        </div>
      </button>
    </div>
    <div class="admin-main">
      <div id="add-currency" class="admin-section active">
        <h2>Add Game Currency</h2>
        <p>Use this form to add new game currency to the database.</p>
        <form method="post" enctype="multipart/form-data">
          <label for="game">Game:</label>
          <select id="game" name="game" required>
            <option value="">Select a game</option>
            <option value="League of Legends">League of Legends</option>
            <option value="Pokemon GO">Pokemon GO</option>
            <option value="Fortnite">Fortnite</option>
            <option value="World of Tanks">World of Tanks</option>
            <option value="Marvel Rivals">Marvel Rivals</option>
          </select>

          <label for="currency_name">Currency Name:</label>
          <input type="text" id="currency_name" name="currency_name" required readonly>

          <label for="amount">Amount:</label>
          <input type="number" id="amount" name="amount" required min="1">

          <label for="price">Price:</label>
          <input type="number" id="price" name="price" required step="0.01" min="0">

          <input type="hidden" id="image_path" name="image_path">
          <label for="currency_image_preview">Currency Image:</label>
          <img id="currency_image_preview" src="" alt="Currency Image" style="width:60px;height:60px; display:none;">

          <button type="submit" name="add_currency">Add Currency</button>
        </form>
      </div>
      <div id="add-account" class="admin-section">
        <h2>Add Game Account</h2>
        <form method="post">
          <label for="account_game">Game:</label>
          <select id="account_game" name="account_game" required>
            <option value="">Select a game</option>
            <option value="League of Legends">League of Legends</option>
            <option value="Pokemon GO">Pokemon GO</option>
            <option value="Fortnite">Fortnite</option>
            <option value="World of Tanks">World of Tanks</option>
            <option value="Marvel Rivals">Marvel Rivals</option>
          </select>

          <label for="account_description">Description:</label>
          <textarea id="account_description" name="account_description" required></textarea>

          <label for="account_price">Price:</label>
          <input type="number" id="account_price" name="account_price" required step="0.01" min="0">

          <button type="submit" name="add_account">Add Account</button>
        </form>
      </div>
      <div id="approve-listings" class="admin-section" style="display:none;">

      </div>
      <div id="user-list" class="admin-section">
        <h2>User List</h2>
        <table border="1" cellpadding="8" cellspacing="0">
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
          </tr>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['id']); ?></td>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['role']); ?></td>
              <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            </tr>
          <?php endwhile; ?>
        </table>
</div>
    </div>


  </div>
  <script src="../script/admin.js"></script>
  <script src="../script/script.js"></script>
</body>
</html>