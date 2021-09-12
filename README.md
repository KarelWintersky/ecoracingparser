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
  `list` varchar(100) DEFAULT NULL,
  `dt` datetime DEFAULT NULL,
  `rows` int(11) DEFAULT NULL,
  `total_weight` int(11) DEFAULT NULL,
  UNIQUE KEY `last_update_key_IDX` (`list`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

# Конфиг nginx + CORS

Указан для http. CORS allowed from ALL hosts.

```

server {
    listen 80; 
    server_name <host>;

    root        /var/www/ecoparser/public;

    index       index.php index.html;

    access_log  /var/log/nginx/ecoparser.access.log;
    error_log   /var/log/nginx/ecoparser.error.log;

    gzip             on;
    gzip_static      on;
    gzip_min_length  1000;
    gzip_proxied     expired no-cache no-store private auth;
    gzip_types       application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass php-handler-7-4;
        fastcgi_index index.php;

        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Allow-Origin' '*';
            add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS';
            add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range';
            add_header 'Access-Control-Max-Age' 1728000;
            add_header 'Content-Type' 'text/plain; charset=utf-8';
            add_header 'Content-Length' 0;
            return 204;
        }

        if ($request_method = 'POST') {
            add_header 'Access-Control-Allow-Origin' '*' always;
            add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
            add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range' always;
            add_header 'Access-Control-Expose-Headers' 'Content-Length,Content-Range' always;
        }
        if ($request_method = 'GET') {
            add_header 'Access-Control-Allow-Origin' '*' always;
            add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
            add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range' always;
            add_header 'Access-Control-Expose-Headers' 'Content-Length,Content-Range' always;
        }

    }

    location ~ favicon.* {
        access_log off;
        log_not_found off;
    }
}
```






