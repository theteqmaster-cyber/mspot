<?php
// api.php - Returns JSON list of all music files under /home/mphatic/Music (recursive).
$musicDir = '/home/mphatic/Music'; // absolute path to music folder
if (!is_dir($musicDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Music directory not found at /home/mphatic/music']);
    exit;
}

$allowedExt = ['mp3','wav','flac','aac','ogg'];
$files = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($musicDir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $fileInfo) {
    if (!$fileInfo->isFile()) continue;
    $ext = strtolower($fileInfo->getExtension());
    if (!in_array($ext, $allowedExt)) continue;
    // Relative path from $musicDir (preserve subfolders)
    $relativePath = substr($fileInfo->getPathname(), strlen($musicDir) + 1);
    $files[] = [
        'name'   => pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME),
        'url'    => "stream.php?file=" . rawurlencode($relativePath),
        'artUrl' => "art.php?file="   . rawurlencode($relativePath)
    ];
}
header('Content-Type: application/json');
echo json_encode(['tracks' => $files]);
?>
