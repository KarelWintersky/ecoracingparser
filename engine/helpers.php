<?php

namespace EcoParser;

use Dotenv\Dotenv;

/**
 * @param $data
 */
function say($data) {
    (\Arris\App::factory())->set('json', $data);
}

/**
 * Возвращает текущую метку времени строкой (если не передан таймштамп)
 *
 * @param null $ts
 * @return string
 */
function dtNow($ts = null):string {
    $format = 'Y-m-d H:i:s';
    return is_null($ts) ? date($format) : date($format, $ts);
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

/**
 * Очищает строку от спецсимволов, кавычек и прочего. Используется для построения "ключевой" строки.
 *
 * @param string $input
 * @return string
 */
function stringToKey(string $input):string
{
    $quotes = array("&laquo;", "&raquo;", "&#187;", "&#171;", "«", "»", "'", '"', "&#039;");

    $s = trim($input);
    $s = strip_tags($s);
    $s = str_replace($quotes, '', $s);
    $s = mb_strtoupper($s);
    return $s;
}