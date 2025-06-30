<?php
require_once 'db.php';
header('Content-Type: application/json');
try{
    if (!isset($_COOKIE['user_id'])) {
        throw new Exception('User not logged in.');
    }
    $userId = $_COOKIE['user_id'];
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    $stmt = $pdo->prepare('INSERT into community_members(id_user, id_community, member_type) values(:id_user, :id_community, 1)');
    $stmt->execute([':id_community' => $data['communityID'], ':id_user' => $userId]);
    $stmt = $pdo->prepare('UPDATE communities c 
                            SET members = members + 1
                            WHERE id = :id_community;');
    $stmt->execute([':id_community' => $data['communityID']]);
    echo json_encode(["status" => "success", "message" => "Community data fetched successfully."]);
    exit;
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>