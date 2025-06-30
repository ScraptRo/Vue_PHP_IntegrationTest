<?php
require_once 'db.php';
header('Content-Type: application/json');

try{
    
    $user = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : null;
    if (!$user) {
        echo json_encode(["status" => "error", "message" => "User not logged in."]);
        exit;;
    }
    $stmt = $pdo->prepare('SELECT id, name, description, members FROM communities order by id desc');
    $stmt->execute();
    $community = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($community === false) {
        $community = [];
    }
    $stmt = $pdo->prepare('SELECT u.id, u.username as name, u.description from users u
                            join creators c on c.id_user = u.id
                            where u.is_creator = 1
                            order by c.number_of_likes desc');
    $stmt->execute();
    $creators = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($creators === false) {
        $creators = [];
    }
    $data['communities'] = $community;
    $data['creators'] = $creators;
    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
    exit;
}
?>