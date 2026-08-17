<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) { exit('No direct script access allowed'); }

$env = require APP_ROOT . '/.env.php';
$GLOBALS['config'] = $env;

date_default_timezone_set($env['app']['timezone'] ?? 'Asia/Jakarta');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('avsec_logbook_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

spl_autoload_register(function(string $class): void {
    $paths = [
        APP_ROOT . '/core/' . $class . '.php',
        APP_ROOT . '/app/controllers/' . $class . '.php',
        APP_ROOT . '/app/models/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (is_file($file)) { require_once $file; return; }
    }
});

require_once APP_ROOT . '/core/helpers.php';

set_exception_handler(function(Throwable $e): void {
    http_response_code(500);
    $message = ($GLOBALS['config']['app']['debug'] ?? false) ? $e->getMessage() : 'Terjadi kesalahan pada aplikasi.';
    error_log('['.date('c').'] '.$e->getMessage()."\n".$e->getTraceAsString()."\n", 3, APP_ROOT.'/storage/logs/error.log');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Error</title><style>body{font-family:Arial;margin:40px;color:#1f2937}.box{max-width:800px;padding:24px;border:1px solid #ddd;border-radius:12px}</style></head><body><div class="box"><h2>Aplikasi mengalami kesalahan</h2><p>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</p></div></body></html>';
});

require_once APP_ROOT . '/core/migrations.php';
run_app_migrations();
