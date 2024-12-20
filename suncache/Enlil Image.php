<?php
// Configuration
$jsonUrl = 'https://services.swpc.noaa.gov/products/animations/enlil.json';
$baseUrl = 'https://services.swpc.noaa.gov/';
$cacheDir = './'; // Current directory for caching
$additionalImages = [
    'lmsal_sun' => 'https://suntoday.lmsal.com/sdomedia/SunInTime/2023/07/30/f0131pfssnolines.jpg',
    'nasa_sdo_sun' => 'https://sdo.gsfc.nasa.gov/assets/img/latest/latest_1024_211193171.jpg'
];

// Function to fetch and cache images
function cacheImage($imageUrl, $cachePath, $resizeWidth = null, $resizeHeight = null) {
    echo "Fetching: $imageUrl\n";
    $imageContent = @file_get_contents($imageUrl);
    if ($imageContent === false) {
        echo "Failed to fetch: $imageUrl\n";
        return false;
    }

    if ($resizeWidth && $resizeHeight) {
        $image = @imagecreatefromstring($imageContent);
        if (!$image) {
            echo "Failed to process image for resizing: $imageUrl\n";
            return false;
        }
        $resizedImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, imagesx($image), imagesy($image));
        imagejpeg($resizedImage, $cachePath);
        imagedestroy($image);
        imagedestroy($resizedImage);
    } else {
        file_put_contents($cachePath, $imageContent);
    }

    echo "Successfully cached: " . basename($cachePath) . "\n";
    return true;
}

// Fetch JSON data
$jsonData = @file_get_contents($jsonUrl);
if ($jsonData === false) {
    die("Failed to fetch JSON data from $jsonUrl\n");
}

$imageUrls = json_decode($jsonData, true);
if (!is_array($imageUrls)) {
    die("Failed to parse JSON data\n");
}

// Get existing cached files
$existingFiles = glob($cacheDir . 'enlil_*.jpg'); // Only ENLIL images
$existingTimestamps = array_map(function($file) {
    if (preg_match('/\d{8}T\d{6}/', $file, $matches)) {
        return $matches[0]; // Extract timestamp
    }
    return null;
}, $existingFiles);
$existingTimestamps = array_filter($existingTimestamps); // Remove nulls

// Cache ENLIL images
$successCount = 0;
$failCount = 0;

foreach ($imageUrls as $imageData) {
    $imageUrl = $baseUrl . $imageData['url'];
    $fileName = basename($imageUrl);
    $cachePath = $cacheDir . $fileName;

    // Extract timestamp from the filename
    if (preg_match('/\d{8}T\d{6}/', $fileName, $matches)) {
        $timestamp = $matches[0];
        if (in_array($timestamp, $existingTimestamps)) {
            echo "Skipping (already cached): $fileName\n";
            continue; // Skip already cached files
        }
    }

    if (cacheImage($imageUrl, $cachePath)) {
        $successCount++;
    } else {
        $failCount++;
    }
}

// Cache additional sun images (always overwrite these)
foreach ($additionalImages as $name => $url) {
    $cachePath = $cacheDir . $name . '.jpg';
    if (cacheImage($url, $cachePath, 900, 900)) {
        $successCount++;
    } else {
        $failCount++;
    }
}

echo "Caching complete. Successes: $successCount, Failures: $failCount\n";
