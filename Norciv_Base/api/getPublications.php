<?php
require_once 'db.php';
try{
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);

    $stmt = $pdo->prepare('SELECT p.id, u.username as poster_name, u.id as poster_id, p.message, p.post_at from publications p 
                            join users u on u.id = p.id_user
                            where id_dashboard = :id_dashboard
                            order by p.post_at asc');
    $stmt->execute([':id_dashboard' => $data['id_dashboard']]);
    echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
}catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
?>