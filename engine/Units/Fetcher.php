<?php

namespace EcoParser\Units;

use Arris\App;
use Arris\Path;
use DigitalStars\Sheets\DSheets;
use PDO;
use function EcoParser\loadSpreadSheetConfig;
use function EcoParser\stringToKey;

/**
 * Class Fetcher
 *
 * Юнит-класс импорта данных из гуглотаблицы и вставки их в БД
 *
 * @package EcoParser\Units
 */
class Fetcher
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

    public function __construct()
    {
        $this->app = App::factory();
        $this->sheets = $this->app->get('sheets');
        $this->pdo = $this->app->get('pdo');
    }

    public function forceUpdateMarket()
    {
        /**
         * Зачем нужен маппер?
         *
         * Он сопоставляет ИМЯ поля и НОМЕР этого поля во входных данных.
         * Визуально удобнее проверять/получать значение поля по ИМЕНИ, а не по НОМЕРУ. Ниже шанс ошибки.
         *
         * Красивее было бы сделать маппер через stdClass и вызывать $mapper->fio; но это лишний код и ненужный уровень абстракции
         */
        $mapper = [
            'fio'       =>  1,
            'workplace' =>  2,
            'weight'    =>  3
        ];

        list(
            'head' => $head,
            'data'  =>  $data,
            'amount'    => $amount
            ) = $this->getSheetContent('markets');

        $filtred_data = [];
        $total_weight = 0;

        foreach ($data as $row) {
            // валидируем значения (примитивный способ, обрезка начальных/конечных пробелов, приведение числа к целому, проверка на пустоту)
            // если какое-то из значений после приведения пустое - пропускаем строку
            if (empty(trim($row[ $mapper['fio'] ]))) continue 1;
            if (empty(trim($row[ $mapper['workplace'] ]))) continue 1;
            if (empty((int)trim($row[ $mapper['weight'] ]))) continue 1;

            $total_weight += (int)$row[ $mapper['weight'] ];

            $filtred_data[] = [
                'fio'       =>  $row[ $mapper['fio'] ],
                'workplace' =>  $row[ $mapper['workplace'] ],
                'weight'    =>  (int)$row[ $mapper['weight'] ],
                'key_fio'   =>  stringToKey($row[ $mapper['fio']]),
                'key_workplace' => stringToKey($row[ $mapper['workplace'] ])
            ];
        }

        if (empty($filtred_data)) {
            return -1;
        }

        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec("TRUNCATE TABLE markets");
            foreach ($filtred_data as $row) {
                $sth = $this->pdo->prepare("
INSERT INTO markets (fio, workplace, weight, key_fio, key_workplace) VALUES(:fio, :workplace, :weight, :key_fio, :key_workplace) 
ON DUPLICATE KEY UPDATE weight = weight + :weight, fio = :fio, workplace = :workplace");
                $sth->bindValue('fio', $row['fio']);
                $sth->bindValue('workplace', $row['workplace']);
                $sth->bindValue('key_fio', $row['key_fio']);
                $sth->bindValue('key_workplace', $row['key_workplace']);
                $sth->bindValue('weight', $row['weight'], PDO::PARAM_INT);
                $sth->execute();
            }
            $sth_lu = $this->pdo->prepare("
INSERT INTO last_update (`list`, `dt`, `rows`, `total_weight`) VALUES ('markets', NOW(), :r, :total_weight) ON DUPLICATE KEY UPDATE `dt` = NOW(), `rows` = :r, `total_weight` = :total_weight 
");
            $sth_lu->bindValue('r', count($filtred_data), PDO::PARAM_INT);
            $sth_lu->bindValue('total_weight', $total_weight, PDO::PARAM_INT);
            $sth_lu->execute();

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
        $this->pdo->commit();

        return count($filtred_data);
    }

    /**
     * @return int
     */
    public function forceUpdateEducational()
    {
        $mapper = [
            'title'     =>  1,
            'weight'    =>  2
        ];

        list(
            'head' => $head,
            'data'  =>  $data,
            'amount'    => $amount
            ) = $this->getSheetContent('educational');

        $filtred_data = [];
        $total_weight = 0;

        foreach ($data as $row) {
            // валидируем значения (примитивный способ, обрезка начальных/конечных пробелов, приведение числа к целому, проверка на пустоту)
            // если какое-то из значений после приведения пустое - пропускаем строку
            if (empty(trim($row[ $mapper['title'] ]))) continue 1;
            if (empty((int)trim($row[ $mapper['weight'] ]))) continue 1;

            $total_weight += (int)$row[ $mapper['weight'] ];

            $filtred_data[] = [
                'title'         =>  $row[ $mapper['title'] ],
                'weight'        =>  (int)$row[ $mapper['weight'] ],
                'key_title'     =>  stringToKey($row[ $mapper['title'] ]),
            ];
        }

        if (empty($filtred_data)) {
            return -1;
        }

        $this->pdo->beginTransaction();

        try {
            $this->pdo->query("TRUNCATE TABLE educational");
            foreach ($filtred_data as $row) {
                $sth = $this->pdo->prepare("
INSERT INTO educational (title, key_title, weight) VALUES(:title, :key_title, :weight) 
ON DUPLICATE KEY UPDATE weight = weight + :weight, title = :title");
                $sth->bindValue('title', $row['title']);
                $sth->bindValue('key_title', $row['key_title']);
                $sth->bindValue('weight', $row['weight'], PDO::PARAM_INT);
                $sth->execute();
            }

            $sth_lu = $this->pdo->prepare("
INSERT INTO last_update (`list`, `dt`, `rows`, `total_weight`) VALUES ('educational', NOW(), :r, :total_weight) ON DUPLICATE KEY UPDATE `dt` = NOW(), `rows` = :r, `total_weight` = :total_weight 
");
            $sth_lu->bindValue('r', count($filtred_data), PDO::PARAM_INT);
            $sth_lu->bindValue('total_weight', $total_weight, PDO::PARAM_INT);
            $sth_lu->execute();

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
        $this->pdo->commit();

        return count($filtred_data);
    }

    /**
     *
     * @return int
     */
    public function forceUpdatePersons()
    {
        $mapper = [
            'fio'       =>  1,
            'birthday'  =>  2,
            'weight'    =>  3
        ];

        list(
            'head' => $head,
            'data'  =>  $data,
            'amount'    => $amount
            ) = $this->getSheetContent('persons');

        $filtred_data = [];
        $total_weight = 0;

        foreach ($data as $row) {
            // валидируем значения (примитивный способ, обрезка начальных/конечных пробелов, приведение числа к целому, проверка на пустоту)
            // если какое-то из значений после приведения пустое - пропускаем строку
            if (empty(trim($row[ $mapper['fio'] ]))) continue 1;
            if (empty(trim($row[ $mapper['birthday'] ]))) continue 1;
            if (empty((int)trim($row[ $mapper['weight']  ]))) continue 1;

            $total_weight += (int)$row[ $mapper['weight'] ];

            $filtred_data[] = [
                'fio'           =>  $row[ $mapper['fio'] ],
                'birthday'      =>  $row[ $mapper['birthday'] ],
                'weight'        =>  (int)$row[ $mapper['weight'] ],
                'key_fio'       =>  stringToKey($row[ $mapper['fio'] ]),
                'key_birthday'  =>  stringToKey($row[ $mapper['birthday'] ]),
            ];
        }

        if (empty($filtred_data)) {
            return -1;
        }

        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec("TRUNCATE TABLE persons");
            foreach ($filtred_data as $row) {
                $sth = $this->pdo->prepare("
INSERT INTO persons (fio, birthday, weight, key_fio, key_birthday) VALUES(:fio, :birthday, :weight, :key_fio, :key_birthday) 
ON DUPLICATE KEY UPDATE weight = weight + :weight, fio = :fio, birthday = :birthday");
                $sth->bindValue('fio', $row['fio']);
                $sth->bindValue('birthday', $row['birthday']);
                $sth->bindValue('key_fio', $row['key_fio']);
                $sth->bindValue('key_birthday', $row['key_birthday']);
                $sth->bindValue('weight', $row['weight'], PDO::PARAM_INT);
                $sth->execute();
            }

            $sth_lu = $this->pdo->prepare("
INSERT INTO last_update (`list`, `dt`, `rows`, `total_weight`) VALUES ('persons', NOW(), :r, :total_weight) ON DUPLICATE KEY UPDATE `dt` = NOW(), `rows` = :r, `total_weight` = :total_weight 
");
            $sth_lu->bindValue('r', count($filtred_data), PDO::PARAM_INT);
            $sth_lu->bindValue('total_weight', $total_weight, PDO::PARAM_INT);
            $sth_lu->execute();

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }

        $this->pdo->commit();

        return count($filtred_data);
    }

    /**
     * Возвращает данные по листу в виде SET-а
     *
     * @param $name
     * @param null $key
     * @return array['head', 'data', 'amount']
     */
    public function getSheetContent($name, $key = null)
    {
        $sheet_config = loadSpreadSheetConfig( $this->sheets[$name] );
        $gapi_config = Path::create(PATH_CONFIG)->joinName( getenv('SERVICE_ACCOUNT_CONFIG') )->toString();

        $sheet       = DSheets::create($sheet_config['spreadsheet_id'], $gapi_config)->setSheet($sheet_config['list_id']);

        $result = [
            'head'      =>  $sheet->get( $sheet_config['rangeHead'])[0],
            'data'      =>  $sheet->get( $sheet_config['rangeData']),
            'amount'    =>  $sheet->get ( $sheet_config['rangeAmount'])[0][0]
        ];

        return  !is_null($key)
            ? $result[$key]
            : $result;
    }


}