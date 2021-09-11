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
     *
     */
    public function getTableData(string $id = null)
    {
        if (is_null($id)) {
            $this->error('Incorrect datasheet name');
            return false;
        }



        say([
            'id'    =>  $id
        ]);
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