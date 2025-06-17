<?php
require_once __DIR__ . '/../auth/db_connect.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'user') {
    header('Location: ../index.php');
    exit;
}

$gameIconMap = [
    'League of Legends' => 'lol_icon.png',
    'Fortnite' => 'fortnite_icon.png',
    'World of Tanks' => 'wot_icon.png',
    'Pokemon GO' => 'pokemongo_icon.png',
    'Marvel Rivals' => 'marvelrivals_icon.png',
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

$user_stmt = $conn->prepare("SELECT username, email, date_of_birth, phone_number, created_at FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->bind_result($username, $email, $dob, $phone, $created_at);
$user_stmt->fetch();
$user_stmt->close();

$listings_stmt = $conn->prepare("
    SELECT ga.*, gap.photo_path
    FROM game_accounts ga
    LEFT JOIN (
        SELECT account_id, MIN(id) as min_photo_id
        FROM game_account_photos
        GROUP BY account_id
    ) first_photos ON ga.id = first_photos.account_id
    LEFT JOIN game_account_photos gap ON gap.id = first_photos.min_photo_id
    WHERE ga.seller_id = ?
    ORDER BY ga.created_at DESC
");
$listings_stmt->bind_param("i", $user_id);
$listings_stmt->execute();
$listings_result = $listings_stmt->get_result();
$listings = [];
while ($row = $listings_result->fetch_assoc()) {
    $listings[] = $row;
}
$listings_stmt->close();


$pwmsg = $_GET['pwmsg'] ?? '';
$pwmsg_text = '';
if ($pwmsg === 'empty') $pwmsg_text = '<div class="error">Please fill in all fields.</div>';
elseif ($pwmsg === 'same') $pwmsg_text = '<div class="error">New password cannot be the same as the current password.</div>';
elseif ($pwmsg === 'nomatch') $pwmsg_text = '<div class="error">New passwords do not match.</div>';
elseif ($pwmsg === 'wrongcurrent') $pwmsg_text = '<div class="error">Current password is incorrect.</div>';
elseif ($pwmsg === 'success') $pwmsg_text = '<div class="success">Password changed successfully!</div>';
elseif ($pwmsg === 'error') $pwmsg_text = '<div class="error">An error occurred. Please try again.</div>';

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($_SESSION['username']); ?>'s Profile</title>
  <link rel="stylesheet" href="../styles/style.css">
  <link rel="stylesheet" href="../styles/profile.css">
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
      <button onclick="showSection('sell-account')">
        <div class="section-wrap">
          <img src="../data/images/sell_icon.png" alt="Sell Account" class="section-icon">
          <div class="sidebar-text">Sell an Account</div>
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
        <div class="profile-info-container">
          <div class="profile-info">
            <h2>Your Profile Information</h2>
            <form id="profile-form" method="post" action="save_profile.php" autocomplete="off">
              <label for="username">Username:</label><br>
              <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required><br>
              <label for="email">Email:</label><br>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly><br>
              <label for="dob">Date of Birth:</label><br>
              <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>"><br>
              <label for="phone">Phone Number:</label><br>
              <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>"><br>
              <label for="created_at">Member Since:</label><br>
              <input type="text" id="created_at" name="created_at" value="<?php echo htmlspecialchars((new DateTime($created_at))->format('d.m.Y')); ?>" readonly><br>
              <button type="submit">Save Changes</button>
            </form>
          </div>
          <div class="password-change">
            <h2>Change Password</h2>
            <?php echo $pwmsg_text; ?>
            <form id="password-form" method="post" action="change_password.php" autocomplete="off">
              <label for="current_password">Current Password:</label><br>
              <input type="password" id="current_password" name="current_password" required><br>
              <label for="new_password">New Password:</label><br>
              <input type="password" id="new_password" name="new_password" required><br>
              <label for="confirm_password">Confirm New Password:</label><br>
              <input type="password" id="confirm_password" name="confirm_password" required><br>
              <button type="submit">Change Password</button>
            </form>
          </div>
        </div>
      </div>
      <div id="listings" class="profile-section">
        <h2>Your Listings</h2>
          <div class="listings-section">
              <?php if (empty($listings)): ?>
                  <p>You have no listings yet.</p>
              <?php else: ?>
                  <?php foreach ($listings as $listing): ?>
                      <div class="listing-card">
                          <img src="<?php echo htmlspecialchars('/WebProgProject/public/' . ($listing['photo_path'] ?? 'data/images/default_account.png')); ?>"
                              alt="Account Image"
                              class="currency-img"
                              style="width:100px;height:100px;object-fit:cover;">
                          <div class="listing-title"><?php echo htmlspecialchars($listing['game_name']); ?></div>
                          <div class="listing-price">€<?php echo number_format($listing['price'], 2); ?></div>
                          <div class="listing-status">
                              <?php
                              if ($listing['is_sold']) {
                                  echo 'Sold';
                              } elseif ($listing['status'] === 'approved') {
                                  echo 'Approved';
                              } elseif ($listing['status'] === 'pending') {
                                  echo 'Pending Approval';
                              } elseif ($listing['status'] === 'rejected') {
                                  echo 'Rejected';
                              } else {
                                  echo 'Unknown';
                              }
                              ?>
                          </div>
                          <div class="listing-actions">
                              <a href="../products/account_details.php?id=<?php echo $listing['id']; ?>">View</a>
                          </div>
                      </div>
                  <?php endforeach; ?>
              <?php endif; ?>
          </div>
      </div>
      <div id="sell-account" class="profile-section">
        <form class="profile-form" enctype="multipart/form-data">
          <h2>Sell an Account</h2>
          <label for="account_game">Game:</label>
          <select id="account_game" name="account_game" required>
            <option value="">Select a game</option>
            <option value="League of Legends">League of Legends</option>
            <option value="Pokemon GO">Pokemon GO</option>
            <option value="Fortnite">Fortnite</option>
            <option value="World of Tanks">World of Tanks</option>
            <option value="Marvel Rivals">Marvel Rivals</option>
          </select>
          <label for="account_photos">Account Photos (up to 3):</label>
          <input type="file" id="account_photos" name="account_photos[]" accept="image/*" multiple required>
          
          <label for="account_description">Description:</label>
          <textarea id="account_description" name="account_description" required></textarea>
          
          <label for="account_price">Price (€):</label>
          <input type="number" id="account_price" name="account_price" required step="0.01" min="0">
          
          <button type="submit">Submit Account for Review</button>
        </form>
      </div>
    </div>
  </div>
  <script>
    window.orderDetailsData = <?php echo json_encode($order_tiles); ?>;
    if (window.location.search.includes('pwmsg=')) {
      const url = new URL(window.location);
      url.searchParams.delete('pwmsg');
      window.history.replaceState({}, '', url);

      setTimeout(function() {
        var msg = document.querySelector('.error, .success');
        if (msg) msg.style.display = 'none';
      }, 2000);
    }
  </script>
  <script src="../script/script.js"></script>
  <script src="../script/profile.js"></script>
</body>
</html>