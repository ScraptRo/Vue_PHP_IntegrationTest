<?php
require_once 'db.php';

try {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    // Validate input
    if (!isset($data['id'])) {
        throw new Exception('Missing required fields.');
    }

    $communityId = $data['id'];

    $stmt = $pdo->prepare('DELETE FROM community_members WHERE id_community = :id');
    $stmt->execute([':id' => $communityId]);

    $stmt = $pdo->prepare('SELECT id FROM dashboards WHERE id_communitie = :communityId');
    $stmt->execute([':communityId' => $communityId]);
    $dashboardIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($dashboardIds)) {
        $placeholders = implode(',', array_fill(0, count($dashboardIds), '?'));
        $stmt = $pdo->prepare("DELETE FROM publications WHERE id_dashboard IN ($placeholders)");
        $stmt->execute($dashboardIds);
    }
    $stmt = $pdo->prepare('DELETE FROM dashboards WHERE id_communitie = :communityId');
    $stmt->execute([':communityId' => $communityId]);


    // Delete the community from the database
    $stmt = $pdo->prepare('DELETE FROM communities WHERE id = :id');
    $stmt->execute([':id' => $communityId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Failed to delete the community or it does not exist.');
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>