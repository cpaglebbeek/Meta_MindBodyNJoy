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
// ───────────────────────────────────────────────────────────────────────────

$headers  = getallheaders();
$auth     = $headers['Authorization'] ?? ($headers['authorization'] ?? '');
$auth     = str_replace('\\!', '!', $auth);   // Hostinger CDN escapet ! → \!
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

if (!file_exists(CONTENT_FILE)) {
    http_response_code(500);
    echo json_encode(['error' => 'content.json niet gevonden']);
    exit;
}

$data = json_decode(file_get_contents(CONTENT_FILE), true);

if (!isset($data['content'][$field])) {
    http_response_code(400);
    echo json_encode(['error' => "Onbekend veld: $field"]);
    exit;
}

// ─── Acties ────────────────────────────────────────────────────────────────

if ($action === 'save') {
    // Nieuwe versie toevoegen — activeert NIET automatisch in een omgeving
    $new_version = [
        'value'     => $body['value'] ?? '',
        'timestamp' => date('c'),
        'note'      => $body['note'] ?? ''
    ];
    array_unshift($data['content'][$field]['versions'], $new_version);
    $data['content'][$field]['versions'] = array_slice(
        $data['content'][$field]['versions'], 0, MAX_VERSIONS
    );
    // Verschuif active_index mee (versies schuiven op door unshift)
    foreach (ENVS as $env) {
        $current = $data['content'][$field]['active_index'][$env] ?? 0;
        $new_idx = min($current + 1, MAX_VERSIONS - 1);
        $data['content'][$field]['active_index'][$env] = $new_idx;
    }
    $data['last_updated'] = date('c');

} elseif ($action === 'activate') {
    $env   = $body['env'] ?? '';
    $index = (int)($body['index'] ?? 0);

    if (!in_array($env, ENVS)) {
        http_response_code(400);
        echo json_encode(['error' => "Onbekende omgeving: $env"]);
        exit;
    }

    $max = count($data['content'][$field]['versions']) - 1;
    if ($index < 0 || $index > $max) {
        http_response_code(400);
        echo json_encode(['error' => "Ongeldige versie-index: $index"]);
        exit;
    }

    $data['content'][$field]['active_index'][$env] = $index;
    $data['last_updated'] = date('c');

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
    http_response_code(500);
    echo json_encode(['error' => 'Schrijven naar content.json mislukt']);
    exit;
}

echo json_encode(['success' => true, 'data' => $data]);
