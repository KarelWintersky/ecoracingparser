<?php

namespace EcoParser;

use Arris\App;
use DigitalStars\Sheets\DSheets;
use Dotenv\Dotenv;
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
        $this->datasheets = $this->app->get('sheets');

        $this->response = [
            'version'   =>  '1.0',
            'title'     =>  'GoogleSheets EcoParser API',
            'calledAt'  =>  self::dtNow(),
        ];
    }

    /**
     * API Endpoint /
     *
     * @return void
     */
    public function about()
    {
        say($this->response);
    }

    /**
     * Error
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
     * Получить данные таблицы по NAME
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

        // since PHP 7.1
        list(
            'head' => $head,
            'data'  =>  $data,
            'amount'    => $amount
            ) = $this->getSheetContent($name);

        say(array_merge($this->response, [
            'stats'     =>  [
                'lastUpdate'    =>  date('Y-m-d H:i:s'),
                'rowsCount'     =>  count($data)
            ],
            'head'      =>  [
                'title'     =>  'Собрано отходов',
                'amount'    =>  $amount
            ],
            'tablehead' =>  $head,
            'table'     =>  $data
        ]));

        /*switch ($name) {
            case 'markets': {
                break;
            }
            case 'educational': {
                break;
            }
            case 'persons': {
                break;
            }
        }*/
    }

    /**
     * Force Update данных из гугл-таблиц в таблицы БД
     */
    public function forceUpdate()
    {
        $status = [];

        $status['market'] = [
            'rows'      =>  $this->forceUpdateMarket(),
            'timestamp' =>  self::dtNow()
        ];

        $status['educational'] = [
            'rows'      =>  $this->forceUpdateEducational(),
            'timestamp' =>  self::dtNow()
        ];

        $status['persons'] = [
            'rows'      =>  $this->forceUpdatePersons(),
            'timestamp' =>  self::dtNow()
        ];

        say(array_merge($this->response, [
            'updateStatus'  =>  $status
        ]));
    }

    /**
     * Статистика
     */
    public function getStats() {    }

}