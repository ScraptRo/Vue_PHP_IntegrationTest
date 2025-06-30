<?php
header('Content-Type: application/json');
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
if (!isset($_COOKIE['user_id'])) {
    echo json_encode(['status' => 'not found', 'message' => 'Not logged in']);
    exit;
}
require_once 'db.php'; // Connect to database
if(isset($data['id'])){
    $userId = $data['id'];
} else {
    // If no ID is provided, use the user ID from the cookie
    if (!isset($_COOKIE['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }
    $userId = $_COOKIE['user_id'];
}
// Fetch full user data
$stmt = $pdo->prepare('SELECT username, is_creator, assets_bought FROM users WHERE id = :id');
$stmt->execute([':id' => $userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
if ($userData) {
    $UserFilePath = __DIR__ . '/../Users_Folders/' . $userData['username'] . '/profile.jpg';
    echo json_encode([
        'status' => 'success',
        'user' => [
            'id' => $userId,
            'name' => $userData['username'],
            'role' => (bool)$userData['is_creator'] ? 'creator' : 'user',
            'profilePicture' => file_exists($UserFilePath) ? '/Norciv_Base/Users_Folders/' . $userData['username'] . '/profile.jpg' : '/Norciv_Base/static/DefaultPicture.png',
            'assetsBought' => (int)$userData['assets_bought'],
            'assetsPublished' => 0
        ]
    ]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
