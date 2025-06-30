<?php
header('Content-Type: application/json');
require_once 'db.php'; // Connect to database
try {
    if (!isset($_COOKIE['user_id'])) {
        echo json_encode(['status' => 'not found', 'message' => 'Not logged in']);
        exit;
    }
    $userId = $_COOKIE['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);

    $searchName = $input['searchName'] ?? '';
    $category = $input['category'] ?? 'All';
    $minPrice = $input['minPrice'] ?? 0;
    $maxPrice = $input['maxPrice'] ?? 100;

    $query = 'SELECT 
        A.id, 
        A.name, 
        U.username AS creator, 
        CONCAT(U.username, "/", A.name, A.id) AS asset_path, 
        A.price, 
        A.asset_type,
        A.year_posted,
        bi.status as owning
        FROM Assets A
        JOIN Creators C ON A.id_creator = C.id
        JOIN Users U ON C.id_user = U.id
        LEFT JOIN bought_items bi ON bi.id_asset = A.id AND bi.id_user = :id1
        WHERE C.id_user <> :id2';

    $params = [':id1' => $userId, ':id2' => $userId];

    if (!empty($searchName)) {
        $query .= ' AND A.name LIKE :searchName';
        $params[':searchName'] = '%' . $searchName . '%';
    }

    if ($category !== -1) {
        $query .= ' AND A.asset_type = :category';
        $params[':category'] = $category;
    }

    $query .= ' AND A.price BETWEEN :minPrice AND :maxPrice';
    $params[':minPrice'] = $minPrice;
    $params[':maxPrice'] = $maxPrice;

    $query .= ' ORDER BY A.id DESC LIMIT 30';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $assets]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage(),
    ]);
    exit;
}
?>