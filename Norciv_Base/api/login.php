<?php
require_once 'db.php';

// Read raw POST body
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

$username = $data['username'] ?? exit(json_encode(['status' => 'error', 'message' => 'Username is required.']));
$password = $data['password'] ?? exit(json_encode(['status' => 'error', 'message' => 'Password is required.']));
$remember = $data['remember'] ?? exit(json_encode(['status' => 'error', 'message' => 'Remember is invalid.']));

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND pass_word = :password");
$stmt->execute([
    ':username' => $username,
    ':password' => $password,
]);

if ($stmt->rowCount() > 0) {
    $valid_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $CookieLiftime = 24 * 60 * 60; // 30 days in seconds
    if ($remember) {
        $CookieLiftime = 86400 * 30; // 30 days in seconds
    }
    setcookie('user_id',$valid_user['id'], time() + $CookieLiftime, "/");
    
    $response = [
        'status' => 'success',
        'message' => 'Login successful!',
    ];
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid username or password.',
    ];
}

// Set response headers
header('Content-Type: application/json');
echo json_encode($response);
