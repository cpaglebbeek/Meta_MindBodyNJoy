<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

// ─── Configuratie ──────────────────────────────────────────────────────────
define('SECRET_TOKEN', 'repeldildo!');
define('CONTENT_FILE', __DIR__ . '/content.json');
define('MAX_VERSIONS',  5);
define('ENVS', ['productie', 'staging']);
define('PURGE_TOKEN',  'purge_mnj_cache_2026');
define('PURGE_URL',    'https://mindbodynjoy.nl/beheer/purge-cache.php');
// ───────────────────────────────────────────────────────────────────────────

$headers = getallheaders();
$auth    = $headers['Authorization'] ?? ($headers['authorization'] ?? '');
$auth    = str_replace('\\!', '!', $auth);   // Hostinger CDN escapet ! → \!
if ($auth !== 'Bearer ' . SECRET_TOKEN) {
    http_response_code(401);
    echo json_encode(['error' => 'Niet geautoriseerd']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Alleen POST toegestaan']);
    exit;
}

$body   = json_decode(file_get_contents('php://input'), true);
$action = $body['action'] ?? '';
$field  = $body['field']  ?? '';

// ─── Ping (auth check zonder side effects) ─────────────────────────────────
if ($action === 'ping') {
    echo json_encode(['success' => true]);
    exit;
}

// ─── Promote all (staging → productie voor alle velden) ────────────────────
if ($action === 'promote_all') {
    if (!file_exists(CONTENT_FILE)) {
        http_response_code(500); echo json_encode(['error' => 'content.json niet gevonden']); exit;
    }
    $data = json_decode(file_get_contents(CONTENT_FILE), true);
    $now  = date('c');
    foreach ($data['content'] as $f => &$fd) {
        $stag_idx = $fd['active_index']['staging'] ?? 0;
        $fd['active_index']['productie'] = $stag_idx;
        $fd['versions'][$stag_idx]['deployed_to']['productie'] = $now;
    }
    unset($fd);
    $data['last_updated'] = $now;
    file_put_contents(CONTENT_FILE,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    flush_cache('productie');
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// ─── Veld validatie (vereist voor acties hieronder) ────────────────────────
if (!file_exists(CONTENT_FILE)) {
    http_response_code(500); echo json_encode(['error' => 'content.json niet gevonden']); exit;
}

$data = json_decode(file_get_contents(CONTENT_FILE), true);

if (!isset($data['content'][$field])) {
    http_response_code(400);
    echo json_encode(['error' => "Onbekend veld: $field"]);
    exit;
}

// ─── Acties ────────────────────────────────────────────────────────────────

if ($action === 'save') {
    $new_version = [
        'value'       => $body['value'] ?? '',
        'timestamp'   => date('c'),
        'note'        => $body['note'] ?? '',
        'deployed_to' => ['staging' => null, 'productie' => null],
    ];
    array_unshift($data['content'][$field]['versions'], $new_version);
    $data['content'][$field]['versions'] = array_slice(
        $data['content'][$field]['versions'], 0, MAX_VERSIONS
    );
    foreach (ENVS as $env) {
        $cur = $data['content'][$field]['active_index'][$env] ?? 0;
        $data['content'][$field]['active_index'][$env] = min($cur + 1, MAX_VERSIONS - 1);
    }
    $data['last_updated'] = date('c');
    $envs_to_flush = [];

} elseif ($action === 'activate') {
    $env   = $body['env'] ?? '';
    $index = (int)($body['index'] ?? 0);

    if (!in_array($env, ENVS)) {
        http_response_code(400); echo json_encode(['error' => "Onbekende omgeving: $env"]); exit;
    }
    $max = count($data['content'][$field]['versions']) - 1;
    if ($index < 0 || $index > $max) {
        http_response_code(400); echo json_encode(['error' => "Ongeldige versie-index: $index"]); exit;
    }

    $data['content'][$field]['active_index'][$env] = $index;
    $data['content'][$field]['versions'][$index]['deployed_to'][$env] = date('c');
    $data['last_updated'] = date('c');
    $envs_to_flush = [$env];

} elseif ($action === 'promote') {
    // Staging actieve versie → productie
    $stag_idx = $data['content'][$field]['active_index']['staging'] ?? 0;
    $data['content'][$field]['active_index']['productie'] = $stag_idx;
    $data['content'][$field]['versions'][$stag_idx]['deployed_to']['productie'] = date('c');
    $data['last_updated'] = date('c');
    $envs_to_flush = ['productie'];

} else {
    http_response_code(400);
    echo json_encode(['error' => "Onbekende actie: $action"]);
    exit;
}

// ─── Schrijven ─────────────────────────────────────────────────────────────
$written = file_put_contents(
    CONTENT_FILE,
    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
);

if ($written === false) {
    http_response_code(500); echo json_encode(['error' => 'Schrijven naar content.json mislukt']); exit;
}

// ─── Cache flushen ─────────────────────────────────────────────────────────
foreach ($envs_to_flush as $env_flush) {
    flush_cache($env_flush);
}

echo json_encode(['success' => true, 'data' => $data]);

// ─── Cache flush helper ────────────────────────────────────────────────────
function flush_cache(string $env): void {
    $url = PURGE_URL . '?token=' . PURGE_TOKEN . '&env=' . urlencode($env);
    $ctx = stream_context_create(['http' => [
        'method'  => 'GET',
        'timeout' => 5,
        'ignore_errors' => true,
    ]]);
    @file_get_contents($url, false, $ctx);
}
