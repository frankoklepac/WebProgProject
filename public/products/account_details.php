<?php
require_once __DIR__ . '/../auth/db_connect.php';


if (!isset($_GET['id'])) {
    echo "No account specified.";
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


$account_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT ga.*, u.username AS seller_name FROM game_accounts ga LEFT JOIN users u ON ga.seller_id = u.id WHERE ga.id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Account not found.";
    exit;
}
$account = $result->fetch_assoc();
$stmt->close();

$photos = [];
$photo_stmt = $conn->prepare("SELECT photo_path FROM game_account_photos WHERE account_id = ? ORDER BY id ASC");
$photo_stmt->bind_param("i", $account_id);
$photo_stmt->execute();
$photo_result = $photo_stmt->get_result();
while ($row = $photo_result->fetch_assoc()) {
    $photos[] = '../' . $row['photo_path'];
}
$photo_stmt->close();
if (empty($photos)) {
    $photos[] = '/data/images/default_account.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($account['game_name']); ?> - Account Details</title>
  <link rel="stylesheet" href="../styles/style.css">
  <link rel="stylesheet" href="../styles/account_details.css">
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

    <div class="account-details">
      <?php if ($account['status'] === 'approved'): ?>
        <?php if ($account['is_sold']): ?>
          <h2><?php echo htmlspecialchars($account['game_name']); ?> Account - Out of Stock</h2>
        <?php else: ?>
          <h2><?php echo htmlspecialchars($account['game_name']); ?> Account</h2>
        <?php endif; ?>
      <?php elseif ($account['status'] === 'rejected'): ?>
        <h2><?php echo htmlspecialchars($account['game_name']); ?> Account - Rejected</h2>
      <?php else: ?>
        <h2><?php echo htmlspecialchars($account['game_name']); ?> Account - Pending Approval</h2>
      <?php endif; ?>
      <div class="carousel-container">
        <button class="carousel-arrow left" onclick="prevImg()">
            <img src="../data/images/arrow_left.png" alt="Previous" style="width:32px;height:32px;">
        </button>
        <img id="carousel-img" src="<?php echo htmlspecialchars($photos[0]); ?>" class="carousel-img" alt="Account Photo">
        <button class="carousel-arrow right" onclick="nextImg()">
            <img src="../data/images/arrow_right.png" alt="Next" style="width:32px;height:32px;">
        </button>
      </div>
      <p><b>Price:</b> <?php echo number_format($account['price'], 2); ?> €</p>
      <p><b>Description:</b> <br> <?php echo nl2br(htmlspecialchars($account['description'])); ?></p>
      <?php if (!empty($account['seller_name'])): ?>
        <p><b>Seller:</b> <?php echo htmlspecialchars($account['seller_name']); ?></p>
      <?php endif; ?>
      <?php if (!$account['is_sold']): ?>
        <div class="account-actions">
          <a href="accounts.php">Back to Accounts</a>
          <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $account['seller_id']): ?>
            <button class="add-to-cart-btn" data-product-id="<?php echo $account['id']; ?>" data-product-type="account">Add to Cart</button>
          <?php else: ?>
            <button class="add-to-cart-btn disabled" disabled>You are selling this account</button>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div id="lightbox-overlay">
      <button class="carousel-arrow left" id="lightbox-prev">
        <img src="../data/images/arrow_left.png" alt="Previous" style="width:32px;height:32px;">
      </button>
      <img id="lightbox-img" src="" alt="Full Size">
      <button class="carousel-arrow right" id="lightbox-next">
        <img src="../data/images/arrow_right.png" alt="Next" style="width:32px;height:32px;">
      </button>
    </div>
    <script>
      const photos = <?php echo json_encode($photos); ?>;
    </script>
  <script src="../script/script.js"></script>
  <script src="../script/accounts.js"></script>
  <script src="../script/account_details.js"></script>
  </body>
</html>