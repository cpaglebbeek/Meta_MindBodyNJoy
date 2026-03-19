<?php
// tracker.php — MindBodyNJoy bezoekersrapport
// Ontvangt POST van de website-JS, combineert met server-side data en mailt rapport.

$allowed_origins = [
    'https://mindbodynjoy.nl',
    'https://www.mindbodynjoy.nl',
    'https://staging.mindbodynjoy.nl',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// CORS headers
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!in_array($origin, $allowed_origins)) {
    http_response_code(403);
    exit;
}

// Parse body
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];

// Server-side data
$ip_raw = $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CF_CONNECTING_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'onbekend';
$ip        = trim(explode(',', $ip_raw)[0]);
$server_ua = $_SERVER['HTTP_USER_AGENT']      ?? 'onbekend';
$ref_hdr   = $_SERVER['HTTP_REFERER']         ?? '';
$acc_lang  = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
$timestamp = date('d-m-Y H:i:s') . ' (UTC+' . date('P') . ')';

// Client-side data (met fallbacks)
$page        = htmlspecialchars($data['url']        ?? 'onbekend', ENT_QUOTES);
$referrer    = htmlspecialchars($data['referrer']   ?: ($ref_hdr ?: '(direct)'), ENT_QUOTES);
$screen      = htmlspecialchars(($data['screenWidth']   ?? '?') . ' × ' . ($data['screenHeight']   ?? '?'), ENT_QUOTES);
$viewport    = htmlspecialchars(($data['viewportWidth'] ?? '?') . ' × ' . ($data['viewportHeight'] ?? '?'), ENT_QUOTES);
$language    = htmlspecialchars($data['language']   ?? $acc_lang ?: 'onbekend', ENT_QUOTES);
$languages   = htmlspecialchars($data['languages']  ?? '', ENT_QUOTES);
$timezone    = htmlspecialchars($data['timezone']   ?? 'onbekend', ENT_QUOTES);
$tz_offset   = htmlspecialchars($data['timezoneOffset'] ?? 'onbekend', ENT_QUOTES);
$color_depth = htmlspecialchars($data['colorDepth'] ?? 'onbekend', ENT_QUOTES);
$pixel_ratio = htmlspecialchars($data['pixelRatio'] ?? 'onbekend', ENT_QUOTES);
$platform    = htmlspecialchars($data['platform']   ?? 'onbekend', ENT_QUOTES);
$cookies     = !empty($data['cookiesEnabled']) ? 'Ja' : 'Nee';
$dnt         = ($data['doNotTrack'] ?? '0') === '1' ? 'Ja' : 'Nee';
$connection  = htmlspecialchars($data['connectionType'] ?? 'onbekend', ENT_QUOTES);
$load_time   = isset($data['loadTime']) && $data['loadTime'] > 0
               ? round($data['loadTime'] / 1000, 2) . ' s'
               : 'onbekend';
$client_ts   = htmlspecialchars($data['timestamp']  ?? '', ENT_QUOTES);

// HTML rapport
$html = <<<HTML
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <title>Bezoekersrapport MindBodyNJoy</title>
</head>
<body style="margin:0;padding:20px;background:#f0ebe8;font-family:Arial,Helvetica,sans-serif">
<div style="max-width:620px;margin:0 auto;background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08)">

  <!-- Header -->
  <div style="background:#6d3f8a;padding:22px 28px">
    <h2 style="margin:0;color:#fff;font-size:17px;font-weight:600">🌿 MindBodyNJoy — Bezoekersrapport</h2>
    <p style="margin:5px 0 0;color:#e2d6f0;font-size:12px">{$timestamp}</p>
  </div>

  <!-- Pagina -->
  <div style="padding:22px 28px 0">
    <h3 style="margin:0 0 10px;color:#6d3f8a;font-size:13px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #ede9fe;padding-bottom:6px">Pagina</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <tr>
        <td style="padding:5px 0;color:#888;width:170px;vertical-align:top">URL</td>
        <td style="padding:5px 0;word-break:break-all"><a href="{$page}" style="color:#6d3f8a">{$page}</a></td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Referrer</td>
        <td style="padding:5px 8px">{$referrer}</td>
      </tr>
      <tr>
        <td style="padding:5px 0;color:#888">Laadtijd</td>
        <td style="padding:5px 0">{$load_time}</td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Client tijdstip</td>
        <td style="padding:5px 8px">{$client_ts}</td>
      </tr>
    </table>
  </div>

  <!-- Netwerk -->
  <div style="padding:18px 28px 0">
    <h3 style="margin:0 0 10px;color:#6d3f8a;font-size:13px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #ede9fe;padding-bottom:6px">Netwerk</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <tr>
        <td style="padding:5px 0;color:#888;width:170px">IP-adres</td>
        <td style="padding:5px 0"><strong>{$ip}</strong></td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Verbindingstype</td>
        <td style="padding:5px 8px">{$connection}</td>
      </tr>
    </table>
  </div>

  <!-- Browser & Apparaat -->
  <div style="padding:18px 28px 0">
    <h3 style="margin:0 0 10px;color:#6d3f8a;font-size:13px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #ede9fe;padding-bottom:6px">Browser &amp; Apparaat</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <tr>
        <td style="padding:5px 0;color:#888;width:170px;vertical-align:top">User Agent</td>
        <td style="padding:5px 0;font-size:11px;word-break:break-all;color:#555">{$server_ua}</td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Platform</td>
        <td style="padding:5px 8px">{$platform}</td>
      </tr>
      <tr>
        <td style="padding:5px 0;color:#888">Schermresolutie</td>
        <td style="padding:5px 0">{$screen}</td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Viewport</td>
        <td style="padding:5px 8px">{$viewport}</td>
      </tr>
      <tr>
        <td style="padding:5px 0;color:#888">Kleurdiepte</td>
        <td style="padding:5px 0">{$color_depth} bit</td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Pixel ratio</td>
        <td style="padding:5px 8px">{$pixel_ratio}</td>
      </tr>
    </table>
  </div>

  <!-- Taal & Locatie -->
  <div style="padding:18px 28px 0">
    <h3 style="margin:0 0 10px;color:#6d3f8a;font-size:13px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #ede9fe;padding-bottom:6px">Taal &amp; Locatie</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <tr>
        <td style="padding:5px 0;color:#888;width:170px">Voorkeurstaal</td>
        <td style="padding:5px 0">{$language}</td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Alle talen</td>
        <td style="padding:5px 8px">{$languages}</td>
      </tr>
      <tr>
        <td style="padding:5px 0;color:#888">Tijdzone</td>
        <td style="padding:5px 0">{$timezone}</td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">UTC-offset</td>
        <td style="padding:5px 8px">+{$tz_offset} uur</td>
      </tr>
    </table>
  </div>

  <!-- Privacy -->
  <div style="padding:18px 28px 0">
    <h3 style="margin:0 0 10px;color:#6d3f8a;font-size:13px;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid #ede9fe;padding-bottom:6px">Privacy-instellingen</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <tr>
        <td style="padding:5px 0;color:#888;width:170px">Cookies ingeschakeld</td>
        <td style="padding:5px 0">{$cookies}</td>
      </tr>
      <tr style="background:#fdf8ff">
        <td style="padding:5px 8px;color:#888">Do Not Track</td>
        <td style="padding:5px 8px">{$dnt}</td>
      </tr>
    </table>
  </div>

  <!-- Footer -->
  <div style="margin:22px 28px 0;padding:14px 0;border-top:1px solid #ede9fe;font-size:11px;color:#aaa">
    Automatisch rapport · MindBodyNJoy Tracker v1.0 · {$timestamp}
  </div>

</div>
</body>
</html>
HTML;

// Bootstrap WordPress zodat wp_mail() (+ Site Mailer plugin) beschikbaar is
define('SHORTINIT', false);
require_once __DIR__ . '/../wp-load.php';

$to      = 'cglebbeek@gmail.com';
$subject = 'Bezoek mindbodynjoy.nl — ' . date('d-m-Y H:i');
$wp_headers = [
    'Content-Type: text/html; charset=UTF-8',
    'From: MindBodyNJoy Tracker <noreply@mindbodynjoy.nl>',
];

wp_mail($to, $subject, $html, $wp_headers);

http_response_code(204);
exit;
