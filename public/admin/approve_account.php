<?php
require_once __DIR__ . '/../auth/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
    $account_id = $input['account_id'] ?? 0;
    $action = $input['action'] ?? '';
    $reason = $input['reason'] ?? null;

    if ($account_id <= 0 || !in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE game_accounts SET status = ? WHERE id = ?");
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt->bind_param("si", $status, $account_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => "Account $status successfully!"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
    }
    $stmt->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit;
?>