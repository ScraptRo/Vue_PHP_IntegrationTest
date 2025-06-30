<?php
require_once 'db.php'; // Connect to database

try {
    // Read raw POST body
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (!isset($_COOKIE['user_id'])) {
        throw new Exception('User not logged in.');
    }
    foreach ($data['assets'] as $value) {
        if (!is_numeric($value)) {
            throw new Exception('Invalid asset ID: ' . $value);
        }
        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare('INSERT INTO bought_items (id_user, id_asset, status)
                            VALUES (:id_user, :id_asset, :status)
                            ON DUPLICATE KEY UPDATE
                            status = VALUES(status)');
        $stmt->execute([
                ":id_user" => $_COOKIE['user_id'],
                ":id_asset" =>  $value,
                ":status" => 1
        ]);
    }
    
    // Fetch the result (if needed)
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return success response
    echo json_encode(['status' => 'success', 'data' => $result]);
} catch (Exception $e) {
    // Return error response
    echo json_encode(['status' => 'error', 'message' => 'Internal Server Error: ' . $e->getMessage()]);
    http_response_code(500); // Internal Server Error
}
?>