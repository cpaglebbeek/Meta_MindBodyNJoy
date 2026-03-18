<?php
/**
 * purge-cache.php — LiteSpeed Cache leegmaken voor productie of staging
 * Geplaatst in: public_html/beheer/
 * Aangeroepen door: save.php na elke activate/promote actie
 *
 * Gebruik: GET /beheer/purge-cache.php?token=TOKEN&env=productie|staging
 */

define('PURGE_TOKEN', 'purge_mnj_cache_2026');

if (!isset($_GET['token']) || $_GET['token'] !== PURGE_TOKEN) {
    http_response_code(403);
    exit(json_encode(['error' => 'Forbidden']));
}

$env     = $_GET['env'] ?? 'productie';
$wp_path = $env === 'staging'
    ? dirname(__FILE__) . '/../staging/'
    : dirname(__FILE__) . '/../';

header('Content-Type: application/json');

if (!file_exists($wp_path . 'wp-load.php')) {
    echo json_encode(['error' => 'wp-load.php niet gevonden', 'path' => $wp_path]);
    exit;
}

// Voorkom WP redirects en header conflicts
if (!defined('DOING_CRON'))    define('DOING_CRON',    true);
if (!defined('DOING_AJAX'))    define('DOING_AJAX',    true);
if (!defined('ABSPATH'))       define('ABSPATH', $wp_path);

try {
    ob_start();
    require_once $wp_path . 'wp-load.php';
    ob_end_clean();

    // WP object cache
    wp_cache_flush();

    // LiteSpeed Cache (plugin actief op Hostinger)
    do_action('litespeed_purge_all');

    // W3 Total Cache (fallback)
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }

    // WP Super Cache (fallback)
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }

    echo json_encode(['success' => true, 'env' => $env]);

} catch (Throwable $e) {
    echo json_encode(['error' => $e->getMessage(), 'env' => $env]);
}
