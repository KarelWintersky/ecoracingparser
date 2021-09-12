# `/` или `/about`

Информация об API.

Возвращает JSON:
```
{
    "version": "1.0",
    "title": "EcoRacing GoogleSheets Parser API",
    "calledAt": "2021-09-12 05:26:03"
}
```

# `/forceUpdate`

Используется для (принудительного) обновления данных в БД.

Может быть вызвано с фронтэнда и вызывается CURL-ом для обновления данных по крону.

Возвращает JSON: 
```
{
    "version": "1.0",
    "title": "EcoRacing GoogleSheets Parser API",
    "calledAt": "2021-09-12 05:22:23",
    "updateStatus": {
        "market": {
            "rows": 4,
            "timestamp": "2021-09-12 05:22:25"
        },
        "educational": {
            "rows": 1,
            "timestamp": "2021-09-12 05:22:26"
        },
        "persons": {
            "rows": 5,
            "timestamp": "2021-09-12 05:22:27"
        }
    }
}
```

# `/getStats`

Возвращает статистику по загруженным в БД данным:

Возвращает JSON:
```
{
    "version": "1.0",
    "title": "EcoRacing GoogleSheets Parser API",
    "calledAt": "2021-09-12 07:29:31",
    "stats": {
        "educational": {
            "list": "educational",
            "dt": "2021-09-12 06:59:48",
            "rows": "1",
            "total_weight": "73200"
        },
        "markets": {
            "list": "markets",
            "dt": "2021-09-12 06:59:46",
            "rows": "4",
            "total_weight": "37100"
        },
        "persons": {
            "list": "persons",
            "dt": "2021-09-12 06:59:49",
            "rows": "5",
            "total_weight": "18350"
        }
    }
}
```

# `/getTableData/NAME`

Возвращает данные по листу (по имени). Сейчас листа 3: 
- `markets` - Экогонка сотрудники магазинов МЕГА 2021
- `educational` - Экогонка 2021 для учебных заведений
- `persons` - Экогонка 2021 для физлиц

Ответ для листа `markets`:
```
{
    "version": "1.0",
    "title": "EcoRacing GoogleSheets Parser API",
    "calledAt": "2021-09-12 07:35:06",
    "stats": {
        "lastUpdate": "2021-09-12 06:59:46",
        "rowsCount": "4"
    },
    "head": {
        "title": "Собрано отходов",
        "total_weight": "37100"
    },
    "tablehead": [
        "ФИО",
        "Место работы",
        "Общий вес грамм"
    ],
    "table": [
        {
            "fio": "Петров Иван Васильевич",
            "workplace": "зара",
            "weight": "15100"
        },
        ...
    ]
}
```

Ответ для листа `educational`:
```
{
    "version": "1.0",
    "title": "EcoRacing GoogleSheets Parser API",
    "calledAt": "2021-09-12 07:35:59",
    "stats": {
        "lastUpdate": "2021-09-12 06:59:48",
        "rowsCount": "1"
    },
    "head": {
        "title": "Собрано отходов",
        "total_weight": "73200"
    },
    "tablehead": [
        "Учебное заведение",
        "Общий вес грамм"
    ],
    "table": [
        {
            "title": "НОУ Школа Альтернатива им А.А. Иоффе",
            "weight": "73200"
        },
        ... 
    ]
}
```

Ответ для листа `persons`:
```
{
    "version": "1.0",
    "title": "EcoRacing GoogleSheets Parser API",
    "calledAt": "2021-09-12 07:36:22",
    "stats": {
        "lastUpdate": "2021-09-12 06:59:49",
        "rowsCount": "5"
    },
    "head": {
        "title": "Собрано отходов",
        "total_weight": "18350"
    },
    "tablehead": [
        "ФИО",
        "дата рождения без года",
        "общий вес грамм"
    ],
    "table": [
        {
            "fio": "Кирилов Александр Андреевич",
            "birthday": "10 января",
            "weight": "14590"
        },
        ...
    ]
}
```

Если вызывано без имени листа или с неправильным именем листа, возвращает:
```
{
    "version": "1.0",
    "title": "EcoRacing GoogleSheets Parser API",
    "calledAt": "2021-09-12 07:36:56",
    "state": "Error",
    "message": "Given no datasheet name" 
OR
    "message": "Invalid datasheet name: xxx"
}
```
