<?php
header('Content-Type: application/json');
try{
    if (!isset($_COOKIE['user_id'])) {
        echo json_encode(['status' => 'not found', 'message' => 'Not logged in']);
        exit;
    }
    require_once 'db.php';
    $userId = $_COOKIE['user_id'];
    $creatorId = getCreatorId($userId);
    $stmt = $pdo->prepare('SELECT CONCAT(usr.username, "/", ast.name, ast.id) AS asset_path, ast.id, usr.username as creator, ast.name, ast.asset_type, ast.price, ast.year_posted FROM assets ast JOIN users usr ON usr.id = :id_user  WHERE ast.id_creator = :id_creator;');
    $stmt->execute([':id_user' => $userId, ':id_creator' => $creatorId]);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $assets]);
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
    exit;
}
?>