<?php
try {
    header('Content-Type: application/json');
    require_once 'db.php';

    if (!isset($_COOKIE['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    $assetName = $_POST['title'] ?? null;
    $description = $_POST['description'] ?? null;
    $assetType = $_POST['type'] ?? null;
    $assetPrice = $_POST['price'] ?? null;
    $userName = getUserName($_COOKIE['user_id']);
    $creatorId = getCreatorId($_COOKIE['user_id']);

    if ($userName === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to database query']);
        exit;
    }
    if (empty($assetName)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO assets(id_creator, name, asset_type, description, price) values(:id, :name, :type, :description, :price)');
    $stmt->execute([
        ":id" => $creatorId,
        ":name" => $assetName,
        ":type" => $assetType,
        ":description" => $description,
        ":price" => $assetPrice
    ]);

    $stmt = $pdo->prepare('SELECT id FROM assets ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    $assetId = $stmt->fetchColumn();
    if ($assetId === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch asset ID']);
        exit;
    }

    $targetDir = __DIR__ . '/../Users_Folders/' . $userName . '/' . $assetName . $assetId . '/';
    if (!mkdir($targetDir, 0777, true) && !is_dir($targetDir)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to create directory']);
        exit;
    }

    // Handle icon image
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        $iconPath = $targetDir . 'icon.png';
        if (!move_uploaded_file($tmpName, $iconPath)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
            exit;
        }
    }

    // Handle specifications (name & nested files)
    $specifications = $_POST['specifications'] ?? [];
    foreach ($specifications as $i => $spec) {
        $specName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $spec['name']);
        $specFolder = $targetDir . $specName . '/';

        if (!is_dir($specFolder) && !mkdir($specFolder, 0777, true)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create specification folder']);
            exit;
        }

        // Handle nested files
        if (isset($_FILES['specifications']['name'][$i]['files'])) {
            $fileCount = count($_FILES['specifications']['name'][$i]['files']);

            for ($j = 0; $j < $fileCount; $j++) {
                $tmpName = $_FILES['specifications']['tmp_name'][$i]['files'][$j];
                $fileName = basename($_FILES['specifications']['name'][$i]['files'][$j]);
                $targetPath = $specFolder . $fileName;

                if (!move_uploaded_file($tmpName, $targetPath)) {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => "Failed to upload file: $fileName"]);
                    exit;
                }
            }
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Asset and files uploaded successfully.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    exit;
}
?>
