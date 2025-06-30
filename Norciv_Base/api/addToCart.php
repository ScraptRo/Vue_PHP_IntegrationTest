<?php
require_once 'db.php'; // Connect to database
try{
    // Read raw POST body
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    if (!isset($_COOKIE['user_id'])) {
        throw new Exception('User not logged in.');
    }
    $stmt = $pdo->prepare('INSERT INTO bought_items(id_user, id_asset) VALUES(:id_user, :id_asset)');
    $stmt->execute([":id_user" => $_COOKIE['user_id'], ":id_asset" => $data['asset_id']]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $result]);
}catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Internal Server Error: ' . $e->getMessage()]);
    http_response_code(500); // Internal Server Error
}
?>