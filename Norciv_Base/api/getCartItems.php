<?php
require_once 'db.php'; // Connect to database
try {
    if (!isset($_COOKIE['user_id'])) {
        throw new Exception('User not logged in.');
    }
    $stmt = $pdo->prepare('SELECT  a.id, a.name, u.username as creator, a.asset_type, a.price from bought_items bi join assets a on a.id = bi.id_asset join users u on u.id = bi.id_user where bi.id_user = :id and bi.status = 0');
    $stmt->execute([":id" => $_COOKIE['user_id']]);
    $stmt->execute();
    $result = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $result]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'data' => 'Failed to fetch cart items.']);
}
?>