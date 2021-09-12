# `/` или `/about`

Информация об API.

Возвращает JSON:
```
{
    "version": "1.0",
    "title": "GoogleSheets EcoParser API",
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
    "title": "GoogleSheets EcoParser API",
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

