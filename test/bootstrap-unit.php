<?php declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$loader = new \Composer\Autoload\ClassLoader();
// Module.php lives at the module root; sub-namespace classes live under src/
$loader->addPsr4('Banner\\', [dirname(__DIR__), dirname(__DIR__) . '/src']);
$loader->addPsr4('BannerTest\\', __DIR__ . '/BannerTest');
$loader->register();

error_reporting(E_ALL);
ini_set('display_errors', '1');
