<?php
$cacheDir = 'suncache/';

if (!is_dir($cacheDir)) {
    http_response_code(500);
    echo json_encode(["error" => "Cache directory not found"]);
    error_log("Error: Cache directory not found");
    exit;
}

$files = glob($cacheDir . '*.jpg');

if ($files === false) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to read cache directory"]);
    error_log("Error: Failed to read cache directory");
    exit;
}

$fileNames = array_map('basename', $files);

error_log("Files found: " . json_encode($fileNames)); // Log files

header('Content-Type: application/json');
echo json_encode($fileNames);