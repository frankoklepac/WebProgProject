<?php
require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_account'])) {
    $game = $_POST['account_game'] ?? '';
    $description = $_POST['account_description'] ?? '';
    $price = $_POST['account_price'] ?? 0.0;
    $seller_id = $_SESSION['user_id'];
    $status = 'pending';

    if (empty($game) || empty($description) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO game_accounts (game_name, description, price, seller_id, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $game, $description, $price, $seller_id, $status);
    if ($stmt->execute()) {
        $account_id = $stmt->insert_id;

        if (!empty($_FILES['account_photos']['name'][0])) {
            $upload_dir = __DIR__ . '/../Uploads/accounts/';
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
                        $photo_path = 'Uploads/accounts/' . $filename;
                        $stmt_photo = $conn->prepare("INSERT INTO game_account_photos (account_id, photo_path) VALUES (?, ?)");
                        $stmt_photo->bind_param("is", $account_id, $photo_path);
                        $stmt_photo->execute();
                        $stmt_photo->close();
                    }
                }
            }
        }
        echo json_encode(['success' => true, 'message' => 'Game account submitted for review!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit;
?>