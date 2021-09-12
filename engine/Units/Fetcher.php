<?php


namespace EcoParser\Units;


use DigitalStars\Sheets\DSheets;

class Fetcher
{
    const quotes = array("&laquo;", "&raquo;", "&#187;", "&#171;", "«", "»", "'", '"', "&#039;");

    public function forceUpdateMarket()
    {
        list(
            'head' => $head,
            'data'  =>  $data,
            'amount'    => $amount
            ) = $this->getSheetContent('markets');

        $filtred_data = [];

        foreach ($data as $row) {
            // валидируем значения (примитивный способ, обрезка начальных/конечных пробелов, приведение числа к целому, проверка на пустоту)
            // если какое-то из значений после приведения пустое - пропускаем строку
            if (empty(trim($row[1]))) continue 1;
            if (empty(trim($row[2]))) continue 1;
            if (empty((int)trim($row[3]))) continue 1;

            $filtred_data[] = [
                'fio'       =>  $row[1],
                'workplace' =>  $row[2],
                'weight'    =>  (int)$row[3],
                'key_fio'   =>  self::stringToKey($row[1]),
                'key_workplace' => self::stringToKey($row[2])
            ];
        }

        if (empty($filtred_data)) {
            return -1;
        }

        $this->pdo->beginTransaction();

        try {
            $this->pdo->query("TRUNCATE TABLE markets");
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
            $this->pdo->exec("INSERT INTO last_update (`key`, `value`) VALUES ('markets', NOW()) ON DUPLICATE KEY UPDATE `value` = NOW() ");
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
        }
        $this->pdo->commit();

        return count($filtred_data);
    }

    public function forceUpdateEducational()
    {
        list(
            'head' => $head,
            'data'  =>  $data,
            'amount'    => $amount
            ) = $this->getSheetContent('educational');

        $filtred_data = [];

        foreach ($data as $row) {
            // валидируем значения (примитивный способ, обрезка начальных/конечных пробелов, приведение числа к целому, проверка на пустоту)
            // если какое-то из значений после приведения пустое - пропускаем строку
            if (empty(trim($row[1]))) continue 1;
            if (empty((int)trim($row[2]))) continue 1;

            $filtred_data[] = [
                'title'         =>  $row[1],
                'weight'        =>  (int)$row[2],
                'key_title'     =>  self::stringToKey($row[1]),
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
            $this->pdo->exec("INSERT INTO last_update (`key`, `value`) VALUES ('educational', NOW()) ON DUPLICATE KEY UPDATE `value` = NOW() ");
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
        }
        $this->pdo->commit();

        return count($filtred_data);
    }

    public function forceUpdatePersons()
    {
        list(
            'head' => $head,
            'data'  =>  $data,
            'amount'    => $amount
            ) = $this->getSheetContent('persons');

        $filtred_data = [];

        foreach ($data as $row) {
            // валидируем значения (примитивный способ, обрезка начальных/конечных пробелов, приведение числа к целому, проверка на пустоту)
            // если какое-то из значений после приведения пустое - пропускаем строку
            if (empty(trim($row[1]))) continue 1;
            if (empty(trim($row[2]))) continue 1;
            if (empty((int)trim($row[3]))) continue 1;

            $filtred_data[] = [
                'fio'           =>  $row[1],
                'birthday'      =>  $row[2],
                'weight'        =>  (int)$row[2],
                'key_fio'       =>  self::stringToKey($row[1]),
                'key_birthday'  =>  self::stringToKey($row[2]),
            ];
        }

        if (empty($filtred_data)) {
            return -1;
        }

        $this->pdo->beginTransaction();

        try {
            $this->pdo->query("TRUNCATE TABLE persons");
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
            $this->pdo->exec("INSERT INTO last_update (`key`, `value`) VALUES ('persons', NOW()) ON DUPLICATE KEY UPDATE `value` = NOW() ");
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            dd($e->getMessage());
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
        $sheet_config = $this->loadSpreadSheetConfig( $this->sheets[$name] );
        $gapi_config = $this->app->get('config')['google_api.config.path'];

        $sheet
            = DSheets::create($sheet_config['spreadsheet_id'], $gapi_config)->setSheet($sheet_config['list_id']);

        $result = [
            'head'      =>  $sheet->get( $sheet_config['rangeHead'])[0],
            'data'      =>  $sheet->get( $sheet_config['rangeData']),
            'amount'    =>  $sheet->get ( $sheet_config['rangeAmount'])[0][0]
        ];

        return  !is_null($key)
            ? $result[$key]
            : $result;
    }

    /**
     * Очищает строку от спецсимволов, кавычек и прочего. Используется для построения "ключевой" строки.
     *
     * @param string $input
     * @return string
     */
    private static function stringToKey(string $input):string
    {
        $s = trim($input);
        $s = strip_tags($s);
        $s = str_replace(self::quotes, '', $s);
        $s = mb_strtoupper($s);
        return $s;
    }


}