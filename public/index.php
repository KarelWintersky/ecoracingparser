<?php

use Arris\App;
use Arris\AppLogger;
use Arris\AppRouter;
use Arris\DB;
use Arris\Exceptions\AppRouterException;
use Dotenv\Dotenv;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

try {
    define('PATH_ENV', dirname(__DIR__, 1) . '/config/');
    Dotenv::create( PATH_ENV, 'common.conf' )->load();

    $app = App::factory();
    $app->add('json', []);

    $app->add('sheets', [
        'markets'   =>  [
            'spreadsheet_id'    =>  '1I2HPoFo7ApLauvWb1SnuwCiOr1onqqPNxNjt73dALrI',
            'list_id'           =>  'Лист1',
            'rangeHead'         =>  'B2:E2',
            'rangeAmount'       =>  'E1',
            'rangeData'         =>  'B3:E10000',
            'title'             =>  'Экогонка сотрудники магазинов МЕГА 2021'
        ],
        'educational'   =>  [
            'spreadsheet_id'    =>  '1_yICjeY_YONTwCdwd2LQGIwyCghTD7TK5i4MFQPq8tg',
            'list_id'           =>  'Лист1',
            'rangeHead'         =>  'A2:C2',
            'rangeAmount'       =>  'C1',
            'rangeData'         =>  'A3:C10000',
            'title'             =>  'Экогонка 2021 для учебных заведений'
        ],
        'persons'       =>  [
            'spreadsheet_id'    =>  '1gCS2McfwT0xpKmkFZy1PGnVlz2LbWaDxfI75m1JZVdQ',
            'list_id'           =>  'Лист1',
            'rangeHead'         =>  'A2:D2',
            'rangeAmount'       =>  'D1',
            'rangeData'         =>  'A3:D10000',
            'title'             =>  'Экогонка 2021 физлиц'
        ],
    ]);
    $app->add('config', [
        'google_api.config.path'    =>  dirname(__DIR__, 1) . '/config/ecoparser-384f4c127530.json',
    ]);

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