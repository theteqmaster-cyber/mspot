<?php
// stream.php - Streams requested music file (supports subfolders) with proper headers
$musicDir = '/home/mphatic/Music'; // absolute path to music folder

if (!isset($_GET['file'])) {
    http_response_code(400);
    echo 'Missing file parameter';
    exit;
}

// Decode URL and sanitize to prevent directory traversal
$requested = rawurldecode($_GET['file']);
// Remove any leading slashes
$requested = ltrim($requested, '/\\');
// Collapse '..' segments – simple safe guard
if (strpos($requested, '..') !== false) {
    http_response_code(400);
    echo 'Invalid file path';
    exit;
}

$filePath = $musicDir . '/' . $requested;
$realPath = realpath($filePath);
if ($realPath === false || strpos($realPath, $musicDir) !== 0 || !is_file($realPath)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
$mimeTypes = [
    'mp3'  => 'audio/mpeg',
    'wav'  => 'audio/wav',
    'flac' => 'audio/flac',
    'aac'  => 'audio/aac',
    'ogg'  => 'audio/ogg',
];
$mime = $mimeTypes[$extension] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($realPath));
header('Accept-Ranges: bytes');

// Support HTTP Range requests for seeking
if (isset($_SERVER['HTTP_RANGE'])) {
    $rangeHeader = $_SERVER['HTTP_RANGE'];
    // e.g. bytes=0-1023
    list(, $range) = explode('=', $rangeHeader, 2);
    list($start, $end) = explode('-', $range, 2);
    $start = intval($start);
    $end = $end !== '' ? intval($end) : (filesize($realPath) - 1);
    $length = $end - $start + 1;
    header('HTTP/1.1 206 Partial Content');
    header("Content-Range: bytes $start-$end/" . filesize($realPath));
    header('Content-Length: ' . $length);
    
    $fh = fopen($realPath, 'rb');
    fseek($fh, $start);
    $chunkSize = 8192;
    $remaining = $length;
    while ($remaining > 0 && !feof($fh)) {
        if (connection_aborted()) break;
        $toRead = min($chunkSize, $remaining);
        $data = fread($fh, $toRead);
        echo $data;
        ob_flush();
        flush();
        $remaining -= strlen($data);
    }
    fclose($fh);
    exit;
}

// Stream whole file in chunks
$fh = fopen($realPath, 'rb');
$chunkSize = 8192;
while (!feof($fh)) {
    if (connection_aborted()) break;
    echo fread($fh, $chunkSize);
    ob_flush();
    flush();
}
fclose($fh);
?>
