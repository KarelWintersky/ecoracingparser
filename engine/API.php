<?php

namespace EcoParser;

use Arris\App;
use PDO;
use function EcoParser\say;

class API
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var array
     */
    private $sheets;

    /**
     * @var PDO
     */
    private $pdo;

    /**
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
            'title'     =>  'GoogleSheets EcoParser API',
        ];

    }

    /**
     * API Endpoint /
     *
     * @return array|string[]
     */
    public function about()
    {
        say($this->response);
    }

    /**
     * Error
     *
     * @return array
     */
    public function error()
    {
        say(array_merge($this->response, [
            'status'    =>  'error',
            'state'     =>  'No table id given'
        ]));
    }

    /**
     * Получить данные таблицы по NAME
     *
     */
    public function getTableData(string $id)
    {
        say([
            'id'    =>  $id
        ]);
    }

    /**
     * Force Update данных по таблицам в БД
     */
    public function forceUpdate()
    {

    }

    /**
     * Статистика
     */
    public function getStats()
    {

    }

}