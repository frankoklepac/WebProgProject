<?php

$currency_added = false;

require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['add_currency'])) {
        $game = $input['game'] ?? '';
        $currency_name = $input['currency_name'] ?? '';
        $amount = $input['amount'] ?? 0;
        $price = $input['price'] ?? 0.0;
        $image_path = $input['image_path'] ?? null;
        $added_by = $_SESSION['user_id'];

        if (empty($game) || empty($currency_name) || $amount <= 0 || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO game_currency (game_name, currency_name, amount, price, image_path, added_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssidsi", $game, $currency_name, $amount, $price, $image_path, $added_by);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Currency added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
        }
        $stmt->close();
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_account'])) {
    $game = $_POST['account_game'] ?? '';
    $description = $_POST['account_description'] ?? '';
    $price = $_POST['account_price'] ?? 0.0;
    $seller_id = $_SESSION['user_id'];
    $status = ($_SESSION['role'] === 'admin') ? 'approved' : 'pending'; 


    if (empty($game) || empty($description) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO game_accounts (game_name, description, price, seller_id, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $game, $description, $price, $seller_id, $status);
    if ($stmt->execute()) {
        $account_id = $stmt->insert_id;

        if (!empty($_FILES['account_photos']['name'][0])) {
            $upload_dir = __DIR__ . '/../uploads/accounts/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $photos = $_FILES['account_photos'];
            for ($i = 0; $i < min(3, count($photos['name'])); $i++) {
                if ($photos['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($photos['name'][$i], PATHINFO_EXTENSION);
                    $filename = uniqid('acc_', true) . '.' . $ext;
                    $target_file = $upload_dir . $filename;
                    if (move_uploaded_file($photos['tmp_name'][$i], $target_file)) {
                        $photo_path = 'uploads/accounts/' . $filename;
                        $stmt_photo = $conn->prepare("INSERT INTO game_account_photos (account_id, photo_path) VALUES (?, ?)");
                        $stmt_photo->bind_param("is", $account_id, $photo_path);
                        $stmt_photo->execute();
                        $stmt_photo->close();
                    }
                }
            }
        }
        echo json_encode(['success' => true, 'message' => 'Game account successfully added!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    $stmt->close();
    exit;
}

$pending_stmt = $conn->prepare("SELECT ga.id, ga.game_name, ga.description, ga.price, ga.status, u.username AS seller_name FROM game_accounts ga LEFT JOIN users u ON ga.seller_id = u.id WHERE ga.status = 'pending' ORDER BY ga.created_at DESC");
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();
$pending_accounts = [];
while ($row = $pending_result->fetch_assoc()) {
    $photos = [];
    $photo_stmt = $conn->prepare("SELECT photo_path FROM game_account_photos WHERE account_id = ? ORDER BY id ASC");
    $photo_stmt->bind_param("i", $row['id']);
    $photo_stmt->execute();
    $photo_result = $photo_stmt->get_result();
    while ($photo_row = $photo_result->fetch_assoc()) {
        $photos[] = '../' . $photo_row['photo_path'];
    }
    $photo_stmt->close();
    if (empty($photos)) {
        $photos[] = '../data/images/default_account.png';
    }
    $row['photos'] = $photos;
    $pending_accounts[] = $row;
}
$pending_stmt->close();


$result = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");

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
            <a href="../profile/profile.php">
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
        <form class="admin-form">
          <label for="game">Game:</label>
          <select id="game" name="game" required>
            <option value="">Select a game</option>
            <option value="League of Legends">League of Legends</option>
            <option value="Pokemon GO">Pokemon GO</option>
            <option value="Fortnite">Fortnite</option>
            <option value="World of Tanks">World of Tanks</option>
            <option value="Marvel Rivals">Marvel Rivals</option>
          </select>
          <input type="hidden" id="image_path" name="image_path">

          <label for="currency_name">Currency:</label>
          <div class="currency-info">
            <img id="currency_image_preview" src="" alt="Currency Image" style="width:45px;height:45px; display:none;">
            <input type="text" id="currency_name" name="currency_name" required readonly>
          </div>

          <label for="amount">Amount:</label>
          <input type="number" id="amount" name="amount" required min="1">

          <label for="price">Price (€):</label>
          <input type="number" id="price" name="price" required step="0.01" min="0">

          <button type="submit">Add Currency</button>
        </form>
      </div>
      <div id="add-account" class="admin-section">
        <h2>Add Game Account</h2>
        <form class="admin-form" enctype="multipart/form-data">
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

          <button type="submit">Add Account</button>
        </form>
      </div>
      <div id="approve-listings" class="admin-section">
        <h2>Approve Listings</h2>
        <?php if (empty($pending_accounts)): ?>
          <p>No pending listings to approve.</p>
        <?php else: ?>
          <div class="listings-section">
            <?php foreach ($pending_accounts as $account): ?>
              <div class="listing-card">
                <h3><?php echo htmlspecialchars($account['game_name']); ?> Account</h3>
                <div class="listing-photos">
                  <?php foreach ($account['photos'] as $photo): ?>
                    <img src="<?php echo htmlspecialchars($photo); ?>" alt="Account Photo" style="width:50px; height:50px; margin-right:5px;">
                  <?php endforeach; ?>
                </div>
                <p> <?php echo number_format($account['price'], 2); ?> €</p>
                <p><?php echo htmlspecialchars($account['description']); ?></p>
                <p>Seller: <?php echo htmlspecialchars($account['seller_name']); ?></p>
                <div class="listing-actions">
                  <button class="approve-btn" data-account-id="<?php echo $account['id']; ?>">Approve</button>
                  <button class="reject-btn" data-account-id="<?php echo $account['id']; ?>">Reject</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div id="user-list" class="admin-section">
        <h2>User List</h2>
        <table class="user-list-table">
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
  <script src="../script/admin.js"></script>
  <script src="../script/script.js"></script>
</body>
</html>