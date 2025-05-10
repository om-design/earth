<?php
// Ensure proper error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Define cache directory path relative to this script
$cacheDir = __DIR__ . '/suncache/';

// Verify directory exists
if (!is_dir($cacheDir)) {
    http_response_code(500);
    error_log("Error: Cache directory not found at " . $cacheDir);
    echo json_encode(["error" => "Cache directory not found"]);
    exit;
}

// Get all jpg files directly from the cache directory
$files = glob($cacheDir . '*.jpg');

if ($files === false) {
    http_response_code(500);
    error_log("Error: Failed to read cache directory at " . $cacheDir);
    echo json_encode(["error" => "Failed to read cache directory"]);
    exit;
}

// Extract just the filenames without the full path
$fileNames = array_map('basename', $files);

// Set proper JSON content type
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Return the list of files
echo json_encode($fileNames);