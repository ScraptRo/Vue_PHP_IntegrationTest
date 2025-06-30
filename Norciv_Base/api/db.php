<?php
// Database config
$host = 'localhost';       // Database server
$db   = 'Norciv_DB';   // Database name
$user = 'AUTO_Norcvi_DB';   // Database username
$pass = '1234';   // Database password
$charset = 'utf8mb4';

// DSN = Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
];

// Create PDO instance
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

function getUserName($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function getCreatorId($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM creators WHERE id_user = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}
?>
