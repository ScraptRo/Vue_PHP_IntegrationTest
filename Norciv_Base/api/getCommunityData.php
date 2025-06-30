<?php
require_once 'db.php';
header('Content-Type: application/json');

try {
    if (!isset($_COOKIE['user_id'])) {
        throw new Exception('User not logged in.');
    }

    $userId = $_COOKIE['user_id'];
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    $communityId = $data['id'] ?? null;

    if (!$communityId) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Missing asset ID"]);
        exit;
    }

    // Fetch community details
    $stmt = $pdo->prepare('SELECT name, description FROM communities WHERE id = :id');
    $stmt->execute([':id' => $communityId]);
    $community = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$community) {
        throw new Exception('Community not found.');
    }

    // Check if the user is an organizer or a member
    $stmt = $pdo->prepare('SELECT 1 FROM community_members WHERE id_community = :id AND id_user = :user_id');
    $stmt->execute([':id' => $communityId, ':user_id' => $userId]);
    $isMember = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT 1 FROM community_members WHERE id_community = :id AND id_user = :user_id AND member_type = 0');
    $stmt->execute([':id' => $communityId, ':user_id' => $userId]);
    $isOrganizer = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT 1 FROM communities WHERE id = :id AND id_owner = :user_id');
    $stmt->execute([':id' => $communityId, ':user_id' => $userId]);
    $isOwner = $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT c.id_owner, c2.id_user as id_Uowner, u.username as owner, c.name, c.funds, c.members, c.description from communities c 
                            join creators c2 on c2.id = id_owner
                            join users u on c2.id_user = u.id
                            where c.id = :id');
    $stmt->execute([':id' => $communityId]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    $community['info'] = $info;
    $community['assets'] = [];
    $stmt = $pdo->prepare('SELECT id, id_creator, name, asset_type ,description, price, year_posted from assets where id_community = :id_community');
    $stmt->execute([':id_community' => $communityId]);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($assets === false) {
        $assets = [];
    }
    $community['assets'] = $assets;
    $stmt = $pdo->prepare('SELECT u.id, u.username as name, u.description 
                            from community_members cm
                            join users u on u.id = cm.id_user
                            where cm.member_type = 0 and cm.id_community = :id_community');
    $stmt->execute([':id_community' => $communityId]);
    $organizers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if($organizers === false) {
        $organizers = [];
    }
        $community['organizers'] = $organizers;
    // Fetch members if the user has access
    if ($isMember || $isOrganizer || $isOwner) {
        $stmt = $pdo->prepare('SELECT id, name, description, dash_type from dashboards d where id_communitie = :id');
        $stmt->execute([':id' => $communityId]);
        $dashboards = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($dashboards === false) {
            $dashboards = [];
        }
        $community['dashboards'] = $dashboards;
        $community['is_organizer'] = $isOrganizer ? true : false;
        $community['is_owner'] = $isOwner ? true : false;
        $access = 'enrolled';
    } else {
        $access = 'public';
    }

    echo json_encode(['status' => 'success', 'data' => $community, 'access' => $access]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}