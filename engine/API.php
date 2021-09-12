<?php

namespace EcoParser;

use Arris\App;
use DigitalStars\Sheets\DSheets;
use PDO;
use function EcoParser\say;

class API
{
    /**
     * App Instance
     * @var App
     */
    private $app;

    /**
     * Конфигурация листов
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

    /**
     * Общая конфигурация
     *
     * @var array
     */
    private $config;

    public function __construct()
    {
        $app = $this->app = App::factory();
        $this->sheets = $this->app->get('sheets');
        $this->pdo = $this->app->get('pdo');
        $this->datasheets = $this->app->get('sheets');

        $this->response = [
            'version'   =>  '1.0',
            'title'     =>  'GoogleSheets EcoParser API',
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

        $sheet_config = $this->sheets[$name];
        $gapi_config = $this->app->get('config')['google_api.config.path'];

        $sheet
            = DSheets::create($sheet_config['spreadsheet_id'], $gapi_config)
            ->setSheet($sheet_config['list_id']);

        $head = $sheet->get( $sheet_config['rangeHead'])[0];
        $data = $sheet->get( $sheet_config['rangeData']);
        $amount = $sheet->get ( $sheet_config['rangeAmount'])[0][0];

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
     * Force Update данных по таблицам в БД
     */
    public function forceUpdate()
    {




        say([
            'status'    =>  'ok'
        ]);

    }

    /**
     * Статистика
     */
    public function getStats()
    {

    }

}