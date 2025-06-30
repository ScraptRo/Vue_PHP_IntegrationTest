<?php
require_once 'db.php'; // Connect to database
try{
    if (!isset($_COOKIE['user_id'])) {
        throw new Exception('User not logged in.');
    }
    $stmt = $pdo->prepare('SELECT 
	                    CONCAT(u.username, "/", a.name, a.id) AS asset_path,
	                        a.id,
	                        a.name,
	                        u.username as creator,
	                        a.price,
	                        a.asset_type,
                            a.year_posted,
	                        bi.status as owning
                        from bought_items bi 
                        join assets a on a.id = bi.id_asset 
                        join creators c on c.id = a.id_creator
                        join users u on u.id = c.id_user
                        where bi.id_user = :id and bi.status = 1
                        order by bi.id desc
                        limit 20');
    $stmt->execute([
        ":id" => $_COOKIE['user_id']
    ]);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'assets' => $assets]);
    http_response_code(200); // OK
}catch (Exception $e) {
    // Return error response
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Internal Server Error: ' . $e->getMessage()]);
}


?>