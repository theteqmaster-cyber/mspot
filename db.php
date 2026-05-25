<?php
/**
 * db.php - JSON flat-file storage helper for MSpot.
 * Avoids database driver dependencies (like PDO SQLite) which might not be installed.
 * Storage file: data/stats.json
 */

define('STATS_FILE', __DIR__ . '/data/stats.json');

function getStats(): array {
    $filePath = STATS_FILE;
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (!file_exists($filePath)) {
        return ['counts' => [], 'tracks' => []];
    }

    $fp = fopen($filePath, 'r');
    if (!$fp) return ['counts' => [], 'tracks' => []];

    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    $data = json_decode($content, true);
    return is_array($data) ? $data : ['counts' => [], 'tracks' => []];
}

function saveStats(array $data): bool {
    $filePath = STATS_FILE;
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $fp = fopen($filePath, 'c+');
    if (!$fp) return false;

    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    fclose($fp);
    return false;
}
