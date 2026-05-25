<?php
/**
 * art.php - Extracts embedded ID3v2 APIC cover art with disk-cache.
 *
 * Flow:
 *   1. Check cache/art/{hash}.{ext} → serve immediately (no re-parsing)
 *   2. Parse ID3v2 APIC frame from audio file → write to cache → serve
 *   3. If no art found → 204 No Content (client shows placeholder)
 *
 * Cache lives at: <project>/cache/art/
 */

$musicDir = '/home/mphatic/Music';
$cacheDir = __DIR__ . '/cache/art';

// ── Security ──────────────────────────────────────────────────────────────────
if (empty($_GET['file'])) { http_response_code(400); exit; }
$requested = ltrim(rawurldecode($_GET['file']), '/\\');
if (strpos($requested, '..') !== false) { http_response_code(400); exit; }
$filePath = realpath($musicDir . '/' . $requested);
if (!$filePath || strpos($filePath, $musicDir) !== 0 || !is_file($filePath)) {
    http_response_code(404); exit;
}

// ── Cache check ───────────────────────────────────────────────────────────────
$hash      = md5($filePath);
$cacheBase = $cacheDir . '/' . $hash;

// Try jpeg first, then png (most common embedded art formats)
foreach (['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'] as $ext => $mime) {
    $cachePath = $cacheBase . '.' . $ext;
    if (is_file($cachePath)) {
        sendCached($cachePath, $mime);
        exit;
    }
}

// Check sentinel "no art" file
if (is_file($cacheBase . '.none')) {
    http_response_code(204);
    exit;
}

// ── Extract ───────────────────────────────────────────────────────────────────
$image = extractCoverArt($filePath);
if ($image === null) {
    // Mark as "no art" with a tiny sentinel file so we don't re-parse next time
    @mkdir($cacheDir, 0755, true);
    @touch($cacheBase . '.none');
    http_response_code(204);
    exit;
}

// ── Save to cache & serve ─────────────────────────────────────────────────────
@mkdir($cacheDir, 0755, true);
$ext  = ($image['mime'] === 'image/png') ? 'png' : (($image['mime'] === 'image/webp') ? 'webp' : 'jpg');
$savePath = $cacheBase . '.' . $ext;
file_put_contents($savePath, $image['data']);

sendCached($savePath, $image['mime']);

// ── Helpers ───────────────────────────────────────────────────────────────────
function sendCached(string $path, string $mime): void {
    $size = filesize($path);
    $etag = '"' . md5($path . $size) . '"';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . $size);
    header('Cache-Control: public, max-age=604800, immutable'); // 7 days
    header('ETag: ' . $etag);
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
        http_response_code(304);
        return;
    }
    readfile($path);
}

function extractCoverArt(string $filePath): ?array {
    $fh = @fopen($filePath, 'rb');
    if (!$fh) return null;

    $header = fread($fh, 10);
    if (strlen($header) < 10 || substr($header, 0, 3) !== 'ID3') {
        fclose($fh);
        return null;
    }

    $majorVersion = ord($header[3]);
    $flags        = ord($header[5]);
    $hasExtHeader = ($flags & 0x40) !== 0;
    $tagSize      = synchsafeToInt(substr($header, 6, 4));

    // Cap tag read at 20 MB to prevent memory issues on corrupt files
    $safeSize = min($tagSize, 20 * 1024 * 1024);
    $tagData  = fread($fh, $safeSize);
    fclose($fh);
    if ($tagData === false || strlen($tagData) < 4) return null;

    $offset = 0;
    if ($majorVersion >= 3 && $hasExtHeader) {
        $extSize = ($majorVersion === 4)
            ? synchsafeToInt(substr($tagData, 0, 4))
            : unpack('N', substr($tagData, 0, 4))[1];
        $offset += $extSize;
    }

    $frameIdLen = ($majorVersion >= 3) ? 4 : 3;

    while ($offset < strlen($tagData) - ($frameIdLen + 6)) {
        $frameId = substr($tagData, $offset, $frameIdLen);
        if ($frameId === str_repeat("\x00", $frameIdLen)) break;
        $offset += $frameIdLen;

        if ($majorVersion >= 3) {
            $frameSize = ($majorVersion === 4)
                ? synchsafeToInt(substr($tagData, $offset, 4))
                : unpack('N', substr($tagData, $offset, 4))[1];
            $offset += 6; // 4 size + 2 flags
        } else {
            $sz = substr($tagData, $offset, 3);
            $frameSize = (ord($sz[0]) << 16) | (ord($sz[1]) << 8) | ord($sz[2]);
            $offset += 3;
        }

        if ($frameSize <= 0 || $offset + $frameSize > strlen($tagData)) break;

        $frameData = substr($tagData, $offset, $frameSize);
        $offset   += $frameSize;

        if ($frameId !== 'APIC' && $frameId !== 'PIC') continue;

        $pos = 1; // skip encoding byte
        if ($frameId === 'APIC') {
            $nullPos = strpos($frameData, "\x00", $pos);
            if ($nullPos === false) continue;
            $mimeType = strtolower(substr($frameData, $pos, $nullPos - $pos));
            $pos = $nullPos + 2; // skip null + picture type
            $enc = ord($frameData[0]);
            $nullSeq = ($enc === 1 || $enc === 2) ? "\x00\x00" : "\x00";
            $descEnd = strpos($frameData, $nullSeq, $pos);
            if ($descEnd === false) continue;
            $pos = $descEnd + strlen($nullSeq);
        } else {
            $imgFmt   = strtolower(substr($frameData, $pos, 3));
            $mimeType = match($imgFmt) { 'jpg' => 'image/jpeg', 'png' => 'image/png', default => 'image/jpeg' };
            $pos += 4; // 3 format + 1 picture type
            $nullPos = strpos($frameData, "\x00", $pos);
            if ($nullPos === false) continue;
            $pos = $nullPos + 1;
        }

        if ($mimeType === 'image/jpg' || empty($mimeType)) $mimeType = 'image/jpeg';

        $imageData = substr($frameData, $pos);
        if (strlen($imageData) < 16) continue;

        return ['mime' => $mimeType, 'data' => $imageData];
    }

    return null;
}

function synchsafeToInt(string $bytes): int {
    $n = 0;
    for ($i = 0; $i < strlen($bytes); $i++) {
        $n = ($n << 7) | (ord($bytes[$i]) & 0x7F);
    }
    return $n;
}
