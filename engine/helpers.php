<?php

namespace EcoParser;

use Dotenv\Dotenv;

/**
 * @param $data
 */
function say($data) {
    \Arris\App::factory()->set('json', $data);
}

/**
 * Возвращает текущую метку времени строкой
 *
 * @return string
 */
function dtNow():string {
    return date('Y-m-d H:i:s');
}

/**
 * Загружает конфигурацию гуглодокумента из конфиг-файла
 *
 * @param $filename
 * @return array
 */
function loadSpreadSheetConfig($filename) {
    Dotenv::create( PATH_CONFIG, $filename )->overload();
    return [
        'spreadsheet_id'    =>  getenv('spreadsheet_id'),
        'list_id'           =>  getenv('list_id'),
        'rangeHead'         =>  getenv('rangeHead'),
        'rangeAmount'       =>  getenv('rangeAmount'),
        'rangeData'         =>  getenv('rangeData'),
        'title'             =>  getenv('title')
    ];
}