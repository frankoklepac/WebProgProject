<?php

require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($passwordHash);
        if ($stmt->fetch()) {
            if (password_verify($current_password, $passwordHash)) {
                $newHash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt->close();
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $newHash, $user_id);
                if ($stmt->execute()) {
                    $success = "Password changed successfully.";
                } else {
                    $error = "Failed to update password.";
                }
            } else {
                $error = "Current password is incorrect.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password</title>
</head>
<body>
  <?php
  if (!empty($error)) echo "<p style='color:red;'>$error</p>";
  if (!empty($success)) echo "<p style='color:green;'>$success</p>";
  ?>
  <h2>Change Password</h2>
  <form method="post" action="">
    <label>Current Password:</label><br>
    <input type="password" name="current_password" required><br>
    <label>New Password:</label><br>
    <input type="password" name="new_password" required><br>
    <label>Confirm New Password:</label><br>
    <input type="password" name="confirm_password" required><br>
    <button type="submit">Change Password</button>
  </form>
  <p><a href="profile.php">Back to Profile</a></p>
</body>
</html>