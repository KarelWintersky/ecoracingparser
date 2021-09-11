<?php

use Arris\AppLogger;
use Arris\AppRouter;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$app = \Arris\App::factory();
$app->add('json', []);

AppLogger::init('EcoParser', bin2hex(random_bytes(8)), [
    'default_logfile_path'      => dirname(__DIR__, 1) . '/logs/',
    'default_logfile_prefix'    => '/' . date_format(date_create(), 'Y-m-d') . '__'
] );
AppRouter::init(AppLogger::addScope('router'));
AppRouter::setDefaultNamespace('\EcoParser');

AppRouter::get('/', 'API@about');
AppRouter::get('/getTableData', 'API@getTableData');
AppRouter::get('/forceUpdate', 'API@forceUpdate');
AppRouter::get('/getStats', 'API@getStats');

AppRouter::dispatch();

header('Content-Type: application/json; charset=utf-8');

echo json_encode($app->get('json') , JSON_PRETTY_PRINT);

die;