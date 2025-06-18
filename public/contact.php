<?php
require_once __DIR__ . '/auth/db_connect.php';
require '../vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail_config = require __DIR__ . '/auth/mail_config.php';


$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($cart_count);
    $stmt->fetch();
    $stmt->close();
}

$user_email = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($user_email);
    $stmt->fetch();
    $stmt->close();
} else {
    $user_email = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message_text = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $mail_config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mail_config['smtp_user'];
        $mail->Password   = $mail_config['smtp_pass'];
        $mail->SMTPSecure = $mail_config['smtp_secure'];
        $mail->Port       = $mail_config['smtp_port'];

        $mail->setFrom($mail_config['from_email'], $mail_config['from_name']);
        $mail->addAddress($mail_config['to_email']);
        $mail->Subject = "Message from $email";
        $mail->Body    = "Name: $name\nEmail: $email\nMessage Content: $message_text";

        $mail->send();
        echo "<p style='color:green;text-align:center;'>Test email sent successfully!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;text-align:center;'>Failed to send test email: {$mail->ErrorInfo}</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us</title>
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/contact.css">
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
      <h2>Contact Us</h2>
      <p>If you have any questions or need assistance, please feel free to reach out to us using the form below:</p>
      <form method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($user_email); ?>">
        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="4" required></textarea>

        <button type="submit" name="send_test">Send Message</button>
      </form>
    </div>
    <script src="script/script.js"></script>
</body>
</html>