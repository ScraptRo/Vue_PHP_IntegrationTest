<?php
header('Content-Type: application/json');
if (!isset($_COOKIE['user_id'])) {
    echo json_encode(['status' => 'not found', 'message' => 'Not logged in']);
    exit;
}
require_once 'db.php'; // Connect to database
$userId = (int)$_COOKIE['user_id'];
// Fetch full user data
try {
    $pdo->beginTransaction();

    $stmt1 = $pdo->prepare('INSERT INTO creators(id_user) VALUES (:id)');
    $stmt1->execute([':id' => $userId]);

    $stmt2 = $pdo->prepare('UPDATE users SET is_creator = true WHERE id = :id');
    $stmt2->execute([':id' => $userId]);

    $pdo->commit();
    echo json_encode([
        'status' => 'success',
        'message' => 'User upgraded to creator successfully'
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    $errors = $stmt->errorInfo();
    echo json_encode([
        'status' => 'error',
        'message' => $errors[2]
    ]);
    http_response_code(500);
}
?>