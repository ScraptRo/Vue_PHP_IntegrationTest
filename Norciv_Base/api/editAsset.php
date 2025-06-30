<?php
require_once 'db.php';

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    if (!isset($data['id']) || (!isset($data['title']) && !isset($data['description']))) {
        throw new Exception('Missing required fields.');
    }

    $assetId = $data['id'];
    $title = isset($data['title']) ? trim($data['title']) : null;
    $description = isset($data['description']) ? trim($data['description']) : null;

    $fields = [];
    $params = [':id' => $assetId];

    if ($title) {
        $fields[] = 'title = :title';
        $params[':title'] = $title;
    }

    if ($description) {
        $fields[] = 'description = :description';
        $params[':description'] = $description;
    }

    $query = 'UPDATE assets SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>