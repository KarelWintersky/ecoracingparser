<?php

namespace EcoParser;

use Arris\App;
use DigitalStars\Sheets\DSheets;
use Dotenv\Dotenv;
use EcoParser\Units\Fetcher;
use EcoParser\Units\Getter;
use PDO;

class API
{
    /**
     * App Instance
     * @var App
     */
    private $app;

    /**
     * Конфигурация листов (значения - конфиг-файлы)
     *
     * @var array
     */
    private $sheets;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * Данные для ответа
     *
     * @var array
     */
    private $response;

    public function __construct()
    {
        $this->app = App::factory();
        $this->sheets = $this->app->get('sheets');
        $this->pdo = $this->app->get('pdo');

        $this->response = [
            'version'   =>  '1.0',
            'title'     =>  'EcoRacing GoogleSheets Parser API',
            'calledAt'  =>  dtNow(),
        ];
    }

    /**
     * @endpoint: /
     * @endpoint: /about
     *
     * @return void
     */
    public function about()
    {
        say($this->response);
    }

    /**
     * @endpoint: ERROR
     *
     * @param string $message
     * @return void
     */
    public function error($message = '')
    {
        $this->response['state'] = 'Error';
        if (!empty($message)) {
            $this->response['message'] = $message;
        }

        say($this->response);
    }

    /**
     * @endpoint: /getTableData/{name}
     * @endpoint: /getTableData
     *
     * Получить данные таблицы по NAME
     *
     * @param string|null $name
     * @return bool
     */
    public function getTableData(string $name = null)
    {
        $page = $_REQUEST['page'] ?? null;

        if (is_null($name)) {
            $this->error('Given no datasheet name');
            return false;
        }

        if (!array_key_exists($name, $this->sheets)) {
            $this->error("Invalid datasheet name: {$name}");
            return false;
        }

        $_getter = new Getter();

        // since PHP 7.1
        list(
            'head'          =>  $head,
            'data'          =>  $data,
            'total_weight'  =>  $total_weight,
            'rows'          =>  $total_rows,
            'last_update'   =>  $last_update
            ) = $_getter->getSheetData($name);

        say(array_merge($this->response, [
            'stats'     =>  [
                'lastUpdate'    =>  $last_update,
                'rowsCount'     =>  $total_rows
            ],
            'head'      =>  [
                'title'         =>  'Собрано отходов',
                'total_weight'  =>  $total_weight
            ],
            'tablehead' =>  $head,
            'table'     =>  $data
        ]));

        return true;
    }

    /**
     * @endpoint /forceUpdate
     *
     * Force Update данных из гугл-таблиц в таблицы БД
     */
    public function forceUpdate()
    {
        $status = [];

        $_fetcher = new Fetcher();

        $status['market'] = [
            'rows'      =>  $_fetcher->forceUpdateMarket(),
            'timestamp' =>  dtNow()
        ];

        $status['educational'] = [
            'rows'      =>  $_fetcher->forceUpdateEducational(),
            'timestamp' =>  dtNow()
        ];

        $status['persons'] = [
            'rows'      =>  $_fetcher->forceUpdatePersons(),
            'timestamp' =>  dtNow()
        ];

        say(array_merge($this->response, [
            'updateStatus'  =>  $status
        ]));
    }

    /**
     * @endpoint: /getStats
     *
     * Статистика
     */
    public function getStats()
    {
        say(array_merge($this->response, [
            'stats' =>  (new Getter())->getStats()
        ]));
    }

}