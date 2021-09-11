<?php

use Arris\AppLogger;
use Arris\AppRouter;

require_once dirname(__DIR__, 1) . '/vendor/autoload.php';

$app = \Arris\App::factory();
$app->add('json', []);

$app->add('sheets', [
    'markets'   =>  [
        'spreadsheet_id'    =>  '1I2HPoFo7ApLauvWb1SnuwCiOr1onqqPNxNjt73dALrI',
        'list_id'           =>  'Лист1',
        'range'             =>  'B3:E10000',
        'title'             =>  'Экогонка сотрудники магазинов МЕГА 2021'
    ],
    'educational'   =>  [
        'spreadsheet_id'    =>  '1_yICjeY_YONTwCdwd2LQGIwyCghTD7TK5i4MFQPq8tg',
        'list_id'           =>  'Лист1',
        'range'             =>  'A3:C10000',
        'title'             =>  'Экогонка 2021 для учебных заведений'
    ],
    'persons'       =>  [
        'spreadsheet_id'    =>  '1gCS2McfwT0xpKmkFZy1PGnVlz2LbWaDxfI75m1JZVdQ',
        'list_id'           =>  'Лист1',
        'range'             =>  'A3:D10000',
        'title'             =>  'Экогонка 2021 физлиц'
    ],
]);

AppLogger::init('EcoParser', bin2hex(random_bytes(8)), [
    'default_logfile_path'      => dirname(__DIR__, 1) . '/logs/',
    'default_logfile_prefix'    => '/' . date_format(date_create(), 'Y-m-d') . '__'
] );
AppRouter::init(AppLogger::addScope('router'));
AppRouter::setDefaultNamespace('\EcoParser');

AppRouter::get('/', 'API@about');
AppRouter::get('/getTableData', 'API@error');
AppRouter::get('/getTableData/{id}', 'API@getTableData');
AppRouter::get('/forceUpdate', 'API@forceUpdate');
AppRouter::get('/getStats', 'API@getStats');

AppRouter::dispatch();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($app->get('json') , JSON_PRETTY_PRINT);

die;