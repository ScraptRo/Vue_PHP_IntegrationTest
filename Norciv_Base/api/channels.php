<?php
require_once 'db.php';
header('Content-Type: application/json');
try{
    $userId = $_COOKIE['user_id'];
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    $action = $data['action'] ?? null;
    $communityId = $data['communityId'] ?? null;
    $name = $data['name'] ?? null;
    if( !$action || !$communityId) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing action or community ID"]);
        exit;
    }
    switch($action){
        case 'create':
            if (!$name) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Missing channel name"]);
                exit;
            }
            $stmt = $pdo->prepare('INSERT INTO dashboards (id_communitie, name) VALUES (:communityId, :name)');
            $stmt->execute([':communityId' => $communityId, ':name' => $name]);
            echo json_encode(["status" => "success", "message" => "Channel created successfully", "result" => ["id" => $pdo->lastInsertId(), "name" => $name, "description" => "", "dash_type" => "public"]]);
            break;

        case 'delete':
            // Dont use this yet, it will delete all the dashboards of the community
            $stmt = $pdo->prepare('DELETE FROM dashboards WHERE id_community = :communityId AND id_user = :userId');
            $stmt->execute([':communityId' => $communityId, ':userId' => $userId]);
            echo json_encode(["status" => "success", "message" => "Channel deleted successfully"]);
            break;

        case 'list':
            $stmt = $pdo->prepare('SELECT * FROM channels WHERE id_community = :communityId');
            $stmt->execute([':communityId' => $communityId]);
            $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["status" => "success", "channels" => $channels]);
            break;

        default:
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
    }
    exit;
}catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    exit;
}
?>