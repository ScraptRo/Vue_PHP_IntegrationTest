<?php
require_once 'db.php';

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!isset($data['id'])) {
        throw new Exception('Missing required fields.');
    }

    $assetId = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM assets WHERE id = :id');
    $stmt->execute([':id' => $assetId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to delete the asset or it does not exist.');
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>