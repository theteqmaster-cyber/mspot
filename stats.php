<?php
/**
 * stats.php - Play-count API for MSpot.
 *
 * GET  stats.php          → returns all play counts as JSON
 * POST stats.php          → increments count for a track, returns new count
 *       body: { "url": "stream.php?file=...", "name": "Track Name" }
 */

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // ── Return all play counts (url → count) ──────────────────────────────────
    $data = getStats();
    $counts = $data['counts'] ?? [];
    
    // Sort counts descending
    arsort($counts);
    
    // Cast counts to integer
    $map = [];
    foreach ($counts as $url => $count) {
        $map[$url] = (int)$count;
    }
    
    echo json_encode(['counts' => $map]);
    exit;
}

if ($method === 'POST') {
    // ── Increment count for a track ───────────────────────────────────────────
    $body = json_decode(file_get_contents('php://input'), true);
    $url  = $body['url']  ?? null;
    $name = $body['name'] ?? '';

    if (!$url) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing url']);
        exit;
    }

    $data = getStats();
    if (!isset($data['counts'])) {
        $data['counts'] = [];
    }
    if (!isset($data['tracks'])) {
        $data['tracks'] = [];
    }

    // Increment count
    $newCount = ($data['counts'][$url] ?? 0) + 1;
    $data['counts'][$url] = $newCount;

    // Track metadata
    $data['tracks'][$url] = [
        'name' => $name,
        'last_played' => time()
    ];

    saveStats($data);

    echo json_encode(['url' => $url, 'count' => $newCount]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
