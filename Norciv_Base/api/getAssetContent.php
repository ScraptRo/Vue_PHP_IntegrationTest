<?php
require_once 'db.php';
header('Content-Type: application/json');
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$assetId = $data['id'] ?? null;
if (!$assetId) {
  http_response_code(400);
  echo json_encode(["status" => "error", "message" => "Missing asset ID"]);
  exit;
}

$stmt = $pdo->prepare('SELECT CONCAT(u.username, "/", a.name, a.id) AS asset_path, a.name, a.description, a.price, a.asset_type, u.username, bi.status as rights, a.year_posted
from assets a 
join creators c on c.id = a.id_creator 
join users u on u.id = c.id_user
left join bought_items bi on bi.id_asset = a.id and bi.id_user = :id_user
where a.id = :id 
limit 1;');
$stmt->execute([':id' => $assetId, ':id_user' => $_COOKIE['user_id'] ?? null]);
$assetData = $stmt->fetch(PDO::FETCH_ASSOC);

$assetPath = __DIR__ . '/../Users_Folders/' . $assetData['asset_path'] . '/';
if (!is_dir($assetPath)) {
  http_response_code(404);
  echo json_encode(["status" => "error", "message" => "Asset folder not found: $assetPath"]);
  exit;
}

$iconFile = $assetPath . "icon.png";
$iconUrl = file_exists($iconFile) ? '/Norciv_Base/Users_Folders/' . $assetData['asset_path'] . '/icon.png' : "/../static/default-icon.png";

$specifications = [];
foreach (scandir($assetPath) as $entry) {
  $specFolder = "$assetPath/$entry";
  if ($entry === '.' || $entry === '..' || !is_dir($specFolder)) continue;
  if ($entry === 'icon.png') continue;

  $files = [];
  foreach (scandir($specFolder) as $file) {
    if ($file === '.' || $file === '..') continue;
    $files[] = [
      "name" => $file,
      "url" => "/assets/$assetId/$entry/$file"
    ];
  }

  $specifications[] = [
    "name" => $entry,
    "files" => $files
  ];
}

$response = [
  "status" => "success",
  "data" => [
    "title" => $assetData['name'],
    "creator" => $assetData['username'],
    "description" => $assetData['description'],
    "iconUrl" => $iconUrl,
    "specifications" => $specifications,
    "year_posted" => $assetData['year_posted'],
    "price" => $assetData['price'],
    "assetType" => $assetData['asset_type'],
    "rights" => $assetData['rights'],
  ]
];

echo json_encode($response);
?>
