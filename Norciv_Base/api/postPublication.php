<?php
require_once 'db.php';

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Validate input
    if (!isset($data['dashboard']) || !isset($data['communitieID']) || !isset($data['message'])) {
        throw new Exception('Missing required fields.');
    }

    $dashboardId = $data['dashboard'];
    $communityId = $data['communitieID'];
    $message = trim($data['message']);
    $userId = $_COOKIE['user_id'] ?? null; // Assuming user ID is stored in a cookie

    if (!$userId) {
        throw new Exception('User not authenticated.');
    }

    if (empty($message)) {
        throw new Exception('Message cannot be empty.');
    }

    // Insert the new publication into the database
    $stmt = $pdo->prepare('INSERT INTO publications (id_dashboard, id_user, message) 
                           VALUES (:id_dashboard, :id_user, :message)');
    $stmt->execute([
        ':id_dashboard' => $dashboardId,
        ':id_user' => $userId,
        ':message' => $message,
    ]);

    // Fetch the newly created publication
    $newPublicationId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('SELECT p.id, u.username as poster_name, u.id as poster_id, p.message, p.post_at 
                           FROM publications p 
                           JOIN users u ON u.id = p.id_user 
                           WHERE p.id = :id');
    $stmt->execute([':id' => $newPublicationId]);
    $newPublication = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $newPublication]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>