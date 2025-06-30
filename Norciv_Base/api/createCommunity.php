<?php
try{
    require_once '../api/db.php';
    header('Content-Type: application/json');
    require_once 'db.php';
    if (!isset($_COOKIE['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }
    $communityName = $_POST['name'] ?? null;
    $description = $_POST['description'] ?? null;
    $organizers = isset($_POST['organizers']) ? json_decode($_POST['organizers'], true) : null;

    if ($organizers === null || !is_array($organizers)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid organizers data']);
        exit;
    }
    $creatorId = getCreatorId($_COOKIE['user_id']);
    if($communityName === null || $description === null || $organizers === null) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $communityName]);
        exit;
    }
    if ($creatorId === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to database query']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO communities(id_owner, name, members, description) values(:id, :name, :members, :description)');
    $stmt->execute([
        ":id" => $creatorId,
        ":name" => $communityName,
        ":members" => count($organizers),
        ":description" => $description
    ]);
    
    $stmt = $pdo->prepare('SELECT id FROM communities ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    $communityId = $stmt->fetchColumn();
    if ($communityId === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch community ID']);
        exit;
    }
    foreach ($organizers as $organizer) {
        $stmt = $pdo->prepare('INSERT INTO community_members(id_community, id_user) values(:id, :user)');
        $stmt->execute([
            ":id" => $communityId,
            ":user" => $organizer
        ]);
    }
    $targetDir = __DIR__ . '/../Communities_Folders/' . $communityName . '/';
    if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to create directory']);
        exit;
    }
    // Handle icon image
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        $iconPath = $targetDir . 'icon.png';
        if (!move_uploaded_file($tmpName, $iconPath)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
            exit;
        }
    }
    echo json_encode(['status' => 'success', 'message' => 'Community created successfully', 'communityId' => $communityId]);
    exit;
}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error' . $e->getMessage()]);
    exit;
}
?>