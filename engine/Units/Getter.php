<?php

namespace EcoParser\Units;

use Arris\App;
use PDO;

/**
 * Class Getter
 *
 * Юнит-класс получения данных из БД
 *
 * @package EcoParser\Units
 */
class Getter
{
    const mapper_spreadsheet_to_table = [
        'markets'       =>  'markets',
        'educational'   =>  'educational',
        'persons'       =>  'persons'
    ];

    const fields_of_table = [
        'markets'       =>  ['fio', 'workplace', 'weight'],
        'educational'   =>  ['title', 'weight' ],
        'persons'       =>  ['fio', 'birthday', 'weight']
    ];

    const head_of_table = [
        'markets'       =>  ['ФИО', 'Место работы', 'Общий вес грамм'],
        'educational'   =>  ['Учебное заведение', 'Общий вес грамм' ],
        'persons'       =>  ['ФИО', 'дата рождения без года', 'общий вес грамм']
    ];

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

    public function __construct()
    {
        $this->app = App::factory();
        $this->sheets = $this->app->get('sheets');
        $this->pdo = $this->app->get('pdo');
    }

    /**
     * Возвращает структуру данных по листу:
     *
     * @param $name
     * @return array['head', 'data', 'rows', 'total_weight', 'last_update']
     */
    public function getSheetData($name)
    {
        // $orderBy = $_REQUEST['orderBy'];
        $table = self::mapper_spreadsheet_to_table[ $name ];
        $fields = implode(',', self::fields_of_table[$name]);

        $sth = $this->pdo->query("SELECT {$fields} FROM {$table} ORDER BY weight DESC");

        $data = [];
        $head = [];

        foreach ($sth->fetchAll() as $i => $row) {
            if (getenv('USE_FIRSTROW_AS_HEAD') && $i === 0) {
                $head = $row;
                continue;
            }

            $data[] = $row;
        }

        if (empty($head)) {
            $head = self::head_of_table[$name];
        }

        $sth = $this->pdo->query("SELECT * FROM last_update WHERE list = '{$table}' LIMIT 1");
        $stats = $sth->fetch();

        return [
            'head'      => $head,
            'data'      =>  $data,
            'rows'      =>  $stats['rows'],
            'total_weight'  =>  $stats['total_weight'],
            'last_update'   =>  $stats['dt']
        ];
    }

    /**
     * @return array
     */
    public function getStats()
    {
        $sth = $this->pdo->query("SELECT * FROM last_update ORDER BY list");
        $set = [];

        // в идеале было бы сделать вызов с коллбэком (PDO::FETCH_FUNC), но я не помню как
        foreach ($sth->fetchAll() as $row) {
            $set[ $row['list'] ] = $row;
        }

        return $set;

        /*
        $sth->fetchAll(\PDO::FETCH_FUNC, function ($place, $is_visible, $code, $dt_update) use (&$dataset) {
            $dataset[ $place ] = [
                'id_place'      =>  $place,
                'is_visible'    =>  $is_visible,
                'code'          =>  $code,
                'dt_update'     =>  $dt_update
            ];
        });
         */
    }

}