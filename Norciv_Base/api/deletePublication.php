<?php
require_once 'db.php';
header('Content-Type: application/json');
try{
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    $stmt = $pdo->prepare('SELECT id_user FROM publications WHERE id = :id');
    $stmt->execute([
        ':id' => $data['id']
    ]);
    $publication = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$publication) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Publication not found']);
        exit;
    }
    $userId = $_COOKIE['user_id'] ?? null; // Assuming user ID is stored in a cookie
    if (!$userId) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
        exit;
    }
    if ($publication['id_user'] != $userId) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'You are not allowed to delete this publication']);
        exit;
    }
    // Insert the new publication into the database
    $stmt = $pdo->prepare('DELETE FROM publications WHERE id = :id');
    $stmt->execute([
        ':id' => $data['id']
    ]);
    echo json_encode(['status' => 'success', 'message' => 'Publication deleted successfully']);

}catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>