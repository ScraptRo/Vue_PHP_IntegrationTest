<?php
require_once 'db.php';

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Validate input
    if (!isset($data['id'])) {
        throw new Exception('Missing required fields.');
    }

    $channelId = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM publications WHERE id_dashboard = :id');
    $stmt->execute([':id' => $channelId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to delete the publications or it does not exist.');
    }

    // Delete the channel from the database
    $stmt = $pdo->prepare('DELETE FROM dashboards WHERE id = :id');
    $stmt->execute([':id' => $channelId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to delete the channel or it does not exist.');
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>