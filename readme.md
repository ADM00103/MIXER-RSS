Вот готовый README.md для вашего RSS Mixer на GitHub:

```markdown
# 📡 RSS Mixer — Агрегатор новостей из 14 источников

Простой и быстрый RSS-микшер, который собирает новости из 14 IT-источников, объединяет их в одну ленту и кэширует в статический XML-файл. Работает даже на самых дешёвых хостингах без 504 ошибок.

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## ✨ Возможности

- 📰 Смешивает **14 RSS-лент** в одну
- 🖼️ Извлекает **одну главную картинку** на новость (без дублей)
- 🗂️ **Кэширует результат** в статический XML-файл (мгновенная отдача)
- 🤖 **Автоматическое обновление** по Cron каждые 15 минут
- 🔗 Добавляет информацию об **источнике** и ссылку на оригинал
- 🇷🇺 Поддержка **кириллицы** и любых кодировок
- ⚡ Работает на **любом хостинге** (даже самом дешёвом)

## 🚀 Быстрый старт

### 1. Скопируйте файлы на хостинг

```
/public_html/
   ├── rss-generator.php    # Генератор RSS
   ├── cron-update.php      # Обновление кэша
   └── rss.xml              # Кэш-файл (создастся автоматически)
```

### 2. Настройте секретный ключ

В файле `cron-update.php` измените строку:

```php
$secret_key = 'my_secret_key_2024';  // Придумайте свой ключ
```

### 3. Проверьте работу вручную

Откройте в браузере:

```
https://ваш-сайт/rss-generator.php
```

Убедитесь, что видна RSS-лента.

Затем обновите кэш:

```
https://ваш-сайт/cron-update.php?token=my_secret_key_2024
```

Должно появиться сообщение `✅ SUCCESS`.

### 4. Настройте Cron

**Вариант A: Через панель хостинга (cPanel/ISPmanager)**

Добавьте задачу с интервалом `*/15 * * * *`:

```bash
/usr/local/bin/php /home/ваш_логин/www/cron-update.php token=my_secret_key_2024
```

**Вариант B: Бесплатный сервис [cron-job.org](https://cron-job.org)**

- URL: `https://ваш-сайт/cron-update.php?token=my_secret_key_2024`
- Интервал: `Every 15 minutes`

### 5. Добавьте RSS в свой ридер

Используйте ссылку:

```
https://ваш-сайт/rss.xml
```

## 📋 Список источников

| Источник | RSS URL |
|----------|---------|
| Habr | `https://habr.com/ru/rss/hubs/all/` |
| 3DNews | `https://3dnews.ru/news/rss/` |
| Xakep | `https://xakep.ru/feed/` |
| 3DNews Games | `https://feeds.feedburner.com/3dnews/elyv` |
| 3DNews Soft | `https://feeds.feedburner.com/3dnews/fbuv` |
| 3DNews Other | `https://feeds.feedburner.com/3dnews/halb` |
| Zapier | `https://feeds.feedburner.com/zapier/tGin` |
| FreeSteam | `http://feeds.feedburner.com/freesteam/lQDE` |
| iXBT | `https://feeds.feedburner.com/ixbt/gSRD` |
| iXBT Games v2 | `https://politepol.com/fd/3vpeexJrRBn9` |
| iXBT Games | `http://feeds.feedburner.com/gametech/dVHe` |
| MMO13 | `https://feeds.feedburner.com/mmo13/vBSwUWG0mTx` |
| Player One | `https://feeds.feedburner.com/mail/zILX` |
| Techimo | `https://techimo.ru/rss/` |
| Steam Games | `http://feeds.feedburner.com/SteamOriginUplayGog` |

## ⚙️ Настройка

### Изменение количества новостей

В `rss-generator.php` найдите строку:

```php
$max_items = 200;  // Увеличьте или уменьшите по желанию
```

### Изменение интервала обновления

Измените Cron-задачу:
- `*/10 * * * *` — каждые 10 минут
- `0 */1 * * *` — каждый час
- `0 0 * * *` — раз в сутки

### Добавление своего источника

В `rss-generator.php` добавьте в массив `$sources`:

```php
$sources = [
    // ... существующие источники ...
    [
        'name' => 'Название источника',
        'url' => 'https://example.com/rss/'
    ],
];
```

## 🛠️ Технические детали

- **Выходной формат:** RSS 2.0 с расширениями (Media RSS, Content)
- **Кэширование:** Статический XML-файл `rss.xml`
- **Обработка изображений:** Извлекается только первая картинка из контента (исключая иконки, логотипы)
- **Поддерживаемые форматы:** RSS 2.0, Atom
- **Кодировка:** UTF-8 (автоматическое определение и конвертация)

## 🐛 Устранение проблем

### Ошибка 403 при запуске cron-update.php
Проверьте, что в URL передан правильный `token`.

### Ошибка 504 Gateway Time-out
Скрипт использует кэширование, поэтому генерация происходит только через Cron. Если ошибка появляется при открытии `rss-generator.php` — уменьшите `$max_items` до 50.

### Не отображаются картинки
Некоторые RSS-ленты не содержат изображений. Убедитесь, что в исходной ленте есть картинки в тегах `<media:content>` или внутри HTML.

### Пустой rss.xml
Проверьте права на запись:
```bash
chmod 755 rss.xml
chmod 755 .
```

## 📄 Лицензия

MIT License — используйте как хотите.

## 🙏 Благодарности

Всем источникам новостей за отличный контент!

---

⭐ **Поставьте звезду**, если проект оказался полезным!
```

## Дополнительные файлы для GitHub:

### Файл `.gitignore`

```
# Ignore cache files
rss.xml

# Ignore IDE files
.vscode/
.idea/
*.swp

# Ignore OS files
.DS_Store
Thumbs.db
```

### Файл `LICENSE` (MIT)

```
MIT License

Copyright (c) 2026

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions...

[Полный текст лицензии MIT]
```

## Структура репозитория:

```
rss-mixer/
├── README.md              # Документация
├── LICENSE                # Лицензия MIT
├── .gitignore            # Игнорируемые файлы
├── rss-generator.php     # Генератор RSS
├── cron-update.php       # Обновление кэша
└── rss.xml               # Кэш-файл (создаётся автоматически)
```

## Как залить на GitHub:

```bash
# Инициализация репозитория
git init
git add .
git commit -m "Initial commit: RSS Mixer"

# Добавление удалённого репозитория
git remote add origin https://github.com/ваш-логин/rss-mixer.git

# Пуш на GitHub
git branch -M main
git push -u origin main
```

