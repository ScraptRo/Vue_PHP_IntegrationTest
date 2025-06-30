<?php
require_once 'db.php';

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Validate input
    if (!isset($data['id']) || (!isset($data['name']) && !isset($data['description']))) {
        throw new Exception('Missing required fields.');
    }

    $channelId = $data['id'];
    $name = isset($data['name']) ? trim($data['name']) : null;
    $description = isset($data['description']) ? trim($data['description']) : null;

    if (!$name && !$description) {
        throw new Exception('No updates provided.');
    }

    $fields = [];
    $params = [':id' => $channelId];

    if ($name) {
        $fields[] = 'name = :name';
        $params[':name'] = $name;
    }

    if ($description) {
        $fields[] = 'description = :description';
        $params[':description'] = $description;
    }

    $query = 'UPDATE dashboards SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>