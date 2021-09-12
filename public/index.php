<?php

use Arris\App;
use Arris\AppLogger;
use Arris\AppRouter;
use Arris\DB;
use Arris\Exceptions\AppRouterException;
use Dotenv\Dotenv;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

try {
    define('PATH_CONFIG', dirname(__DIR__, 1) . '/config/');
    Dotenv::create( PATH_CONFIG, 'common.conf' )->load();

    $app = App::factory();
    $app->add('json', []);

    $app->add('sheets', [
        'markets'       =>  'sheet_markets.conf',
        'educational'   =>  'sheet_educational.conf',
        'persons'       =>  'sheet_persons.conf'
    ]);

    $app->add('config', []);

    // $app('sheets', 'xxx');

    DB::init(NULL, [
        'hostname'          =>  getenv('DB.HOST'),
        'database'          =>  getenv('DB.NAME'),
        'username'          =>  getenv('DB.USERNAME'),
        'password'          =>  getenv('DB.PASSWORD'),
        'port'              =>  getenv('DB.PORT'),
        'charset'           =>  'utf8mb4',
        'charset_collate'   =>  'utf8mb4_general_ci',
    ], AppLogger::scope('pdo'));
    $app->set('pdo', DB::getConnection());

    AppLogger::init('EcoParser', bin2hex(random_bytes(8)), [
        'default_logfile_path'      => dirname(__DIR__, 1) . '/logs/',
        'default_logfile_prefix'    => '/' . date_format(date_create(), 'Y-m-d') . '__'
    ] );
    AppRouter::init(AppLogger::addScope('router'));
    AppRouter::setDefaultNamespace('\EcoParser');

    AppRouter::get('/', 'API@about');
    AppRouter::get('/getTableData', 'API@getTableData');
    AppRouter::get('/getTableData/{name}', 'API@getTableData');
    AppRouter::get('/forceUpdate', 'API@forceUpdate');
    AppRouter::get('/getStats', 'API@getStats');

    AppRouter::dispatch();

} catch (AppRouterException $e) {
    (new \EcoParser\API())->error($e->getMessage());
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($app->get('json') , JSON_PRETTY_PRINT);

die;