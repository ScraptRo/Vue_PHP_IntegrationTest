<?php
    require_once '../api/db.php';
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare("SELECT id, date_of_birth, email, is_creator, assets_bought, Register_Date FROM users WHERE users.username = '" . $data['username'] . "';");
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode(['status' => 'success', 'data' => $user]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>