<?php
require_once 'db.php'; // Connect to database
header('Content-Type: application/json');
try{
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    if(!isset($_COOKIE['user_id'])){
        echo json_encode(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
    }
    $id_asset = $data['id_asset'] ?? exit(json_encode(['status' => 'error', 'message' => 'Product ID is required.']));
    $stmt = $pdo->prepare("DELETE FROM bought_items WHERE id_user = :id_user AND id_asset = :id_asset");
    $stmt->execute([
        ':id_user' => $_COOKIE['user_id'],
        ':id_asset' => $id_asset,
    ]);
    $response = [
        'status' => 'success',
        'message' => 'Product removed from cart successfully.',
    ];
    echo json_encode($response);
}catch (Exception $e) {
    echo json_encode(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
    exit;
}
?>