<?php
try{
    require_once 'db.php'; // Connect to database
    if (!isset($_COOKIE['user_id'])) {
        throw new Exception('User not logged in.');
    }
    $creatorId = getCreatorId($_COOKIE['user_id']);
    $stmt = $pdo->prepare('SELECT id, description, members, name from communities c where id_owner = :id;');
    $stmt->execute([":id" => $creatorId]);
    $result = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT c.id, cm.enrolment_date, c.id_owner, c.name, c.members, c.description from community_members cm 
                            join communities c on c.id = cm.id_community
                            where id_user = :id_user;');
    $stmt->execute([":id_user" => $_COOKIE['user_id']]);
    $communityMembers = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'organized' => $result, 'joined' => $communityMembers]);
}catch(Exception $e){
    echo json_encode(['status' => 'error', 'data' => $e->getMessage()]);
    exit();
}
?>