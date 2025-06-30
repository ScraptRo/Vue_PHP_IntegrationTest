<?php
require_once 'db.php';

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Validate input
    if (!isset($data['id']) || !isset($data['message'])) {
        throw new Exception('Missing required fields.');
    }

    $publicationId = $data['id'];
    $message = trim($data['message']);
    $userId = $_COOKIE['user_id'] ?? null; // Assuming user ID is stored in a cookie

    if (!$userId) {
        throw new Exception('User not authenticated.');
    }

    if (empty($message)) {
        throw new Exception('Message cannot be empty.');
    }

    // Update the publication in the database
    $stmt = $pdo->prepare('UPDATE publications SET message = :message WHERE id = :id AND id_user = :user_id');
    $stmt->execute([
        ':message' => $message,
        ':id' => $publicationId,
        ':user_id' => $userId,
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to update the message or no permission.');
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>