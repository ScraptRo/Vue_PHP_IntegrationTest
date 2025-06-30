<?php
header('Content-Type: application/json');
require_once 'db.php'; // Connect to database
// Read raw POST body
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// INSERT INTO users(username, email, pass_word, date_of_birth) values(:username, :email, :password, :date_of_birth)
try{
    $username = $data['username'] ?? exit(json_encode(['status' => 'error', 'message' => 'Incorect username.']));
    $email = $data['email'] ?? exit(json_encode(['status' => 'error', 'message' => 'Incorect email.']));
    $dateofBirth = $data['dateOfBirth'] ?? exit(json_encode(['status' => 'error', 'message' => 'Incorect birth.']));
    $password = $data['password'] ?? exit(json_encode(['status' => 'error', 'message' => 'Incorect password.']));
    $remember = $data['remember'] ?? exit(json_encode(['status' => 'error', 'message' => 'Incorect remember.']));

    $stmt = $pdo->prepare("INSERT INTO users(username, email, pass_word, date_of_birth) values(:username, :email, :password, :date_of_birth)");
    if($stmt->execute([':username' => $username,':email' => $email,':password' => $password,':date_of_birth' => $dateofBirth,]) === false) {
        echo json_encode(['status' => 'error', 'message' => 'User already exists.']);
        exit;
    }
    $targetDir = __DIR__ . '/../Users_Folders/' . $username;
    if(!mkdir($targetDir, 0777, true)){
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to create directory']);
        exit;
    }
    // Path to the default picture
    $defaultPicturePath = __DIR__ . '/../static/DefaultPicture.jpg';
    $targetPicturePath = $targetDir . '/profile.jpg';

    // Copy the default picture to the user's folder
    if (!copy($defaultPicturePath, $targetPicturePath)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to copy default picture']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id FROM assets ORDER BY id DESC LIMIT 1;");
    $stmt->execute([]);

    if ($stmt->rowCount() > 0) {
    $valid_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $CookieLiftime = 24 * 60 * 60; // 30 days in seconds
    if ($remember) {
        $CookieLiftime = 86400 * 30; // 30 days in seconds
    }
    setcookie('user_id',$valid_user['id'], time() + $CookieLiftime, "/"); // Set cookie for 30 days
    
    $response = [
        'status' => 'success',
        'message' => 'Register successful!',
    ];
    }else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid register data.',
    ];
    }

    echo json_encode($response);
    exit;
    }
    catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
?>