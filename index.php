<?php
declare(strict_types=1);

const APP_ROOT = __DIR__;

if (!file_exists(APP_ROOT . '/.env.php')) {
    header('Location: install.php');
    exit;
}

require APP_ROOT . '/core/bootstrap.php';

$router = new Router();
$router->dispatch();
