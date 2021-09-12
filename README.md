# ecoracingparser
Парсер гуглотаблиц для "Экогонки" меги


# Фронт

http://ecogonka.tilda.ws/


# Бэкэнд

Используем 
https://packagist.org/packages/digitalstars/google-sheets + dotenv + Arris microframework 

# DEPLOY

1. Создать БД и пользователя для неё.
2. Создать в БД таблицы
3. Написать конфиги:
    - `common.conf` создать по примеру `common.example`, заполнить данными для подключения к БД
    - загрузить файл `service_account.json` с данными для доступа к Google API
    - создать файлы конфигов для гуглотаблиц (`sheet_educational.conf`, `sheet_markets.conf`, `sheet_persons.conf` по аналогии с `sheet_[example].conf`)
4. Поставить пакет
5. Ready?
 

# Требуемая структура БД:

```
CREATE TABLE `educational` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `key_title` varchar(100) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `educational_key_title_IDX` (`key_title`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `markets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fio` varchar(100) DEFAULT '' COMMENT 'фио',
  `workplace` varchar(100) DEFAULT '' COMMENT 'место работы',
  `weight` int(11) DEFAULT NULL COMMENT 'общий вес',
  `key_fio` varchar(100) DEFAULT NULL COMMENT 'UPPER(fio) for KEY',
  `key_workplace` varchar(100) DEFAULT NULL COMMENT 'UPPER(workplace) for KEY',
  PRIMARY KEY (`id`),
  UNIQUE KEY `markets_key_fio_IDX` (`key_fio`,`key_workplace`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `persons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fio` varchar(100) DEFAULT '' COMMENT 'ФИО',
  `birthday` varchar(36) DEFAULT '' COMMENT 'дата рождения без года',
  `weight` int(11) DEFAULT NULL,
  `key_fio` varchar(100) DEFAULT NULL COMMENT 'UPPER(fio) for KEY',
  `key_birthday` varchar(36) DEFAULT NULL COMMENT 'UPPER(birthday) for KEY',
  PRIMARY KEY (`id`),
  UNIQUE KEY `persons_key_fio_IDX` (`key_fio`,`key_birthday`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `last_update` (
  `key` varchar(100) DEFAULT NULL,
  `value` datetime DEFAULT NULL,
  UNIQUE KEY `last_update_key_IDX` (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

```






