<?php
require_once __DIR__ . '/../auth/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../auth/login.php');
    exit;
}

$gameIconMap = [
    'League of Legends' => 'lol_icon.png',
    'Fortnite' => 'fortnite_icon.png',
    'World of Tanks' => 'wot_icon.png',
    'Pokemon GO' => 'pokemongo_icon.png',
    'Marvel Rival' => 'marvelrivals_icon.png',
];

$stmt = $conn->prepare("SELECT * FROM orders WHERE buyer_id = ? ORDER BY purchased_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders_by_time = [];
while ($row = $result->fetch_assoc()) {
    $productName = '';
    if ($row['account_id']) {
        $prod_stmt = $conn->prepare("SELECT game_name FROM game_accounts WHERE id = ?");
        $prod_stmt->bind_param("i", $row['account_id']);
        $prod_stmt->execute();
        $prod_stmt->bind_result($game_name);
        $prod_stmt->fetch();
        $productName = htmlspecialchars($game_name . " Account");
        $prod_stmt->close();
    } elseif ($row['currency_id']) {
        $prod_stmt = $conn->prepare("SELECT currency_name, amount FROM game_currency WHERE id = ?");
        $prod_stmt->bind_param("i", $row['currency_id']);
        $prod_stmt->execute();
        $prod_stmt->bind_result($currency_name, $amount);
        $prod_stmt->fetch();
        $productName = htmlspecialchars($amount . " " . $currency_name);
        $prod_stmt->close();
    }
    $row['product_name'] = $productName;
    $orders_by_time[$row['purchased_at']][] = $row;
}

$order_tiles = [];
foreach ($orders_by_time as $timestamp => $orders) {
    $date = new DateTime($timestamp);
    $dateStr = $date->format('d.m.Y');
    $orderId = $orders[0]['id'];
    $imagesHtml = '';
    $products = [];
    $totalPrice = 0;

    foreach ($orders as $order) {
        if ($order['account_id']) {
              $acc_stmt = $conn->prepare("SELECT game_name FROM game_accounts WHERE id = ?");
            $acc_stmt->bind_param("i", $order['account_id']);
            $acc_stmt->execute();
            $acc_stmt->bind_result($game_name);
            $acc_stmt->fetch();
            $acc_stmt->close();

            if (isset($gameIconMap[$game_name])) {
                $icon_file = $gameIconMap[$game_name];
            } else {
                $icon_file = strtolower(preg_replace('/\s+/', '', $game_name)) . '_icon.png';
            }
            $icon_path = '../data/images/' . $icon_file;
            if (!file_exists(__DIR__ . '/../data/images/' . $icon_file)) {
                $icon_path = '../data/images/default_game_icon.png';
            }
            $imagesHtml .= "<img src='$icon_path' class='order-thumb' alt='Game Icon' />";
        } elseif ($order['currency_id']) {
            $img_stmt = $conn->prepare("SELECT image_path FROM game_currency WHERE id = ? LIMIT 1");
            $img_stmt->bind_param("i", $order['currency_id']);
            $img_stmt->execute();
            $img_stmt->bind_result($image_path);
            if ($img_stmt->fetch() && $image_path) {
                $imgSrc = htmlspecialchars($image_path);
            } else {
                $imgSrc = '../data/images/default_currency.png';
            }
            $img_stmt->close();
            $imagesHtml .= "<img src='$imgSrc' class='order-thumb' alt='Product' />";
        }
        $products[] = [
            'name' => $order['product_name'],
            'amount' => $order['amount'],
            'price' => $order['price']
        ];
        $totalPrice += $order['price'] * $order['amount'];
    }

    $order_tiles[] = [
        'timestamp' => $timestamp,
        'dateStr' => $dateStr,
        'orderId' => $orderId,
        'imagesHtml' => $imagesHtml,
        'products' => $products,
        'total' => $totalPrice,
        'address' => $orders[0]['address']
    ];
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
  <title><?php echo htmlspecialchars($_SESSION['username']); ?>'s Profile</title>
  <link rel="stylesheet" href="../styles/style.css">
  <link rel="stylesheet" href="../styles/profile.css">
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
            <a href="profile.php">
              <div class="icon-wrap">
                <img src="../data/images/order_icon.png" class="small-icon" alt="Orders">
              </div>
              <div>Your Orders</div>
            </a>
            <a href="profile.php?section=profile">
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
  <div class="profile-container">
    <div class="profile-sidebar">
      <button onclick="showSection('orders')">
        <div class="section-wrap">
          <img src="../data/images/order_icon.png" alt="Profile" class="section-icon">
          <div class="sidebar-text">Your Orders</div>   
        </div>
      </button>
      <button onclick="showSection('profile')">
        <div class="section-wrap">
          <img src="../data/images/lock_icon.png" alt="Profile" class="section-icon">
          <div class="sidebar-text">Your Profile and Security</div>
        </div>
      </button>
      <button onclick="showSection('listings')">
        <div class="section-wrap">
          <img src="../data/images/list_icon.png" alt="Cart" class="section-icon">
          <div class="sidebar-text">Your Listings</div>
        </div>
      </button>
    </div>
    <div class="profile-main">
      <div id="orders" class="profile-section active">
        <h2>Your Orders</h2>
        <?php
        if (empty($order_tiles)) {
            echo "<p>You have no orders yet.</p>";
        } else {
            foreach ($order_tiles as $tile) {
                echo '
                <div class="order-tile" onclick="showSingleOrder(\'order-details-' . $tile['timestamp'] . '\')">
                    <div class="order-date"><b>Order from ' . $tile['dateStr'] . '</b></div>
                    <div class="order-id"><span class="order-id-highlight">sb-order-' . $tile['orderId'] . '</span></div>
                    <div class="order-images">' . $tile['imagesHtml'] . '</div>
                </div>
                ';
            }
        }
        ?>
      </div>
      <div id="order-details-section" class="profile-section" style="display:none;">
        <button onclick="showSection('orders')" class="back-btn">&larr; Back to all orders</button>
        <div class="single-order-header">
          <div>
            <span class="single-order-date"></span>
            <span class="single-order-id"></span>
          </div>
        </div>
        <div class="single-order-address"></div>
        <div class="single-order-images"></div>
        <div class="single-order-products">
          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th>Amount</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <div class="single-order-total">
          <b>Total: 0.00 €</b>
        </div>
      </div>
      <div id="profile" class="profile-section">
        <h2>Your Profile and Security</h2>
        <!-- Profile details go here -->
      </div>
      <div id="listings" class="profile-section">
        <h2>Your Listings</h2>
        <!-- Listings details go here -->
      </div>
    </div>
  </div>
  <script>
    window.orderDetailsData = <?php echo json_encode($order_tiles); ?>;
  </script>
  <script src="../script/script.js"></script>
  <script src="../script/profile.js"></script>
</body>
</html>