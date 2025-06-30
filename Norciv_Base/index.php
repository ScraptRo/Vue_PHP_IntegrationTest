<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$basePath = '/Norciv_Base';
$path = str_replace($basePath, '', $uri);

// Normal static file serving
$fullPath = __DIR__ . $path;
if (file_exists($fullPath) && !is_dir($fullPath)) {
    $mime = mime_content_type($fullPath);
    header("Content-Type: $mime");
    readfile($fullPath);
    exit;
}

// Vue router fallback
readfile(__DIR__ . '/index.html');
