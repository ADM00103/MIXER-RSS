<?php
// RSS Mixer - смешивает несколько RSS лент и отдаёт как один RSS
// Загрузите этот файл на хостинг и откройте в браузере или добавьте в RSS-ридер

// Настройки
$max_items = 200;          // максимум новостей в выдаче
$sources = [
    ['name' => 'Habr', 'url' => 'https://habr.com/ru/rss/hubs/all/'],
    ['name' => '3DNews', 'url' => 'https://3dnews.ru/news/rss/'],
    ['name' => 'Xakep', 'url' => 'https://xakep.ru/feed/'],
    ['name' => '3DNews Games', 'url' => 'https://feeds.feedburner.com/3dnews/elyv'],
    ['name' => '3DNews Soft', 'url' => 'https://feeds.feedburner.com/3dnews/fbuv'],
    ['name' => '3DNews Other', 'url' => 'https://feeds.feedburner.com/3dnews/halb'],
    ['name' => 'Zapier', 'url' => 'https://feeds.feedburner.com/zapier/tGin'],
    ['name' => 'FreeSteam', 'url' => 'http://feeds.feedburner.com/freesteam/lQDE'],
    ['name' => 'iXBT', 'url' => 'https://feeds.feedburner.com/ixbt/gSRD'],
    ['name' => 'iXBT Games v2', 'url' => 'https://politepol.com/fd/3vpeexJrRBn9'],
    ['name' => 'iXBT Games', 'url' => 'http://feeds.feedburner.com/gametech/dVHe'],
    ['name' => 'MMO13', 'url' => 'https://feeds.feedburner.com/mmo13/vBSwUWG0mTx'],
    ['name' => 'Player One', 'url' => 'https://feeds.feedburner.com/mail/zILX'],
    ['name' => 'Techimo', 'url' => 'https://techimo.ru/rss/'],
    ['name' => 'Steam Games', 'url' => 'http://feeds.feedburner.com/SteamOriginUplayGog'],
];

// Генерируем RSS
header('Content-Type: application/rss+xml; charset=utf-8');

$all_items = [];

foreach ($sources as $source) {
    $items = fetch_rss($source['url'], $source['name']);
    $all_items = array_merge($all_items, $items);
}

// Сортировка по дате (новые сверху)
usort($all_items, function($a, $b) {
    return strtotime($b['pubDate']) - strtotime($a['pubDate']);
});

// Ограничиваем количество
$all_items = array_slice($all_items, 0, $max_items);

// Выводим RSS
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" 
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>RSS Mixer - Все новости IT</title>
    <link><?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) ?></link>
    <description>Смесь RSS лент из 14 источников - полные тексты новостей с картинками</description>
    <language>ru-ru</language>
    <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    <generator>RSS Mixer 3.0</generator>
    <atom:link href="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" rel="self" type="application/rss+xml"/>

<?php foreach ($all_items as $item): ?>
    <item>
        <title><![CDATA[<?= $item['title'] ?>]]></title>
        <link><?= htmlspecialchars($item['link']) ?></link>
        <guid><?= htmlspecialchars($item['link']) ?></guid>
        <pubDate><?= $item['pubDate'] ?></pubDate>
        
        <!-- Описание с картинкой -->
        <description><![CDATA[
            <?php if (!empty($item['image'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="max-width: 100%; height: auto; border-radius: 8px;">
                </div>
            <?php endif; ?>
            <p><strong>📰 Источник: <?= htmlspecialchars($item['source']) ?></strong></p>
            <p>🔗 <a href="<?= htmlspecialchars($item['source_url']) ?>">Перейти на сайт источника</a></p>
            <hr/>
            <?= $item['description'] ?>
        ]]></description>
        
        <!-- Полный контент с картинкой -->
        <content:encoded><![CDATA[
            <?php if (!empty($item['image'])): ?>
                <div style="margin-bottom: 20px; text-align: center;">
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                </div>
            <?php endif; ?>
            <h3>📰 Источник: <?= htmlspecialchars($item['source']) ?></h3>
            <p>🔗 <a href="<?= htmlspecialchars($item['source_url']) ?>">Перейти на сайт источника</a></p>
            <hr/>
            <?= $item['content_encoded'] ?? $item['description'] ?>
        ]]></content:encoded>
        
        <!-- Media RSS теги для картинок (стандарт для подкастов и новостных ридеров) -->
        <?php if (!empty($item['image'])): ?>
            <media:thumbnail url="<?= htmlspecialchars($item['image']) ?>" />
            <media:content url="<?= htmlspecialchars($item['image']) ?>" medium="image">
                <media:title type="plain"><?= htmlspecialchars($item['title']) ?></media:title>
            </media:content>
        <?php endif; ?>
        
        <!-- Информация об источнике -->
        <source url="<?= htmlspecialchars($item['source_url']) ?>"><?= htmlspecialchars($item['source']) ?></source>
        <dc:creator><?= htmlspecialchars($item['source']) ?></dc:creator>
        
        <!-- Добавляем картинку через стандартный enclosure (для некоторых ридеров) -->
        <?php if (!empty($item['image'])): ?>
            <enclosure url="<?= htmlspecialchars($item['image']) ?>" type="image/jpeg" length="1" />
        <?php endif; ?>
    </item>

<?php endforeach; ?>
</channel>
</rss>

<?php
// Функция для получения и парсинга RSS
function fetch_rss($url, $source_name) {
    $xml_content = @file_get_contents($url);
    
    if ($xml_content === false) {
        // Пробуем через curl если allow_url_fopen выключен
        $xml_content = curl_get_contents($url);
    }
    
    if (empty($xml_content)) {
        return [];
    }
    
    // Исправляем кодировку
    $xml_content = fix_encoding($xml_content);
    
    // Парсим XML
    libxml_use_internal_errors(true);
    $rss = simplexml_load_string($xml_content);
    
    if ($rss === false) {
        return [];
    }
    
    $items = [];
    
    // Пробуем разные форматы RSS
    if (isset($rss->channel->item)) {
        // Стандартный RSS 2.0
        foreach ($rss->channel->item as $item) {
            // Получаем полный контент если есть
            $content_encoded = '';
            
            // Ищем content:encoded в разных пространствах имён
            $namespaces = $item->getNameSpaces(true);
            if (isset($namespaces['content'])) {
                $children = $item->children($namespaces['content']);
                if (isset($children->encoded)) {
                    $content_encoded = (string)$children->encoded;
                }
            }
            
            // Если нет content:encoded, используем описание
            if (empty($content_encoded)) {
                $content_encoded = (string)$item->description;
            }
            
            // Ищем картинку в разных местах
            $image_url = extract_image_from_item($item);
            
            $items[] = [
                'title' => clean_text((string)$item->title),
                'link' => (string)$item->link,
                'description' => clean_text((string)$item->description, 0),
                'content_encoded' => clean_text($content_encoded, 0),
                'pubDate' => date(DATE_RSS, strtotime((string)$item->pubDate)),
                'source' => $source_name,
                'source_url' => $url,
                'image' => $image_url,
            ];
        }
    } elseif (isset($rss->entry)) {
        // Atom формат
        foreach ($rss->entry as $entry) {
            // Получаем полный контент из atom
            $content = '';
            if (isset($entry->content)) {
                $content = (string)$entry->content;
            } elseif (isset($entry->summary)) {
                $content = (string)$entry->summary;
            }
            
            // Получаем ссылку
            $link = '';
            if (isset($entry->link['href'])) {
                $link = (string)$entry->link['href'];
            } elseif (isset($entry->link)) {
                $link = (string)$entry->link;
            }
            
            // Ищем картинку в atom
            $image_url = extract_image_from_atom($entry);
            
            $items[] = [
                'title' => clean_text((string)$entry->title),
                'link' => $link,
                'description' => clean_text($content, 0),
                'content_encoded' => clean_text($content, 0),
                'pubDate' => date(DATE_RSS, strtotime((string)$entry->updated)),
                'source' => $source_name,
                'source_url' => $url,
                'image' => $image_url,
            ];
        }
    }
    
    return $items;
}

// Извлечение картинки из RSS элемента
function extract_image_from_item($item) {
    // 1. Пробуем media:content
    $namespaces = $item->getNameSpaces(true);
    if (isset($namespaces['media'])) {
        $media = $item->children($namespaces['media']);
        if (isset($media->content)) {
            $attrs = $media->content->attributes();
            if (!empty($attrs['url'])) {
                return (string)$attrs['url'];
            }
        }
        if (isset($media->thumbnail)) {
            $attrs = $media->thumbnail->attributes();
            if (!empty($attrs['url'])) {
                return (string)$attrs['url'];
            }
        }
    }
    
    // 2. Пробуем enclosure
    if (isset($item->enclosure)) {
        $attrs = $item->enclosure->attributes();
        if (!empty($attrs['url']) && strpos((string)$attrs['type'], 'image/') !== false) {
            return (string)$attrs['url'];
        }
    }
    
    // 3. Ищем картинку в description
    $description = (string)$item->description;
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description, $matches)) {
        return $matches[1];
    }
    
    // 4. Ищем в content:encoded
    $namespaces = $item->getNameSpaces(true);
    if (isset($namespaces['content'])) {
        $children = $item->children($namespaces['content']);
        if (isset($children->encoded)) {
            $encoded = (string)$children->encoded;
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $encoded, $matches)) {
                return $matches[1];
            }
        }
    }
    
    // 5. Пробуем найти через custom теги
    if (isset($item->image)) {
        return (string)$item->image;
    }
    
    return '';
}

// Извлечение картинки из Atom элемента
function extract_image_from_atom($entry) {
    // Ищем media:thumbnail
    $namespaces = $entry->getNameSpaces(true);
    if (isset($namespaces['media'])) {
        $media = $entry->children($namespaces['media']);
        if (isset($media->thumbnail)) {
            $attrs = $media->thumbnail->attributes();
            if (!empty($attrs['url'])) {
                return (string)$attrs['url'];
            }
        }
    }
    
    // Ищем картинку в content
    if (isset($entry->content)) {
        $content = (string)$entry->content;
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $matches[1];
        }
    }
    
    // Ищем в summary
    if (isset($entry->summary)) {
        $summary = (string)$entry->summary;
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $summary, $matches)) {
            return $matches[1];
        }
    }
    
    return '';
}

// Альтернативная загрузка через curl
function curl_get_contents($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'RSS Mixer/3.0 (https://github.com/)',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => '',
    ]);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

// Исправление кодировки
function fix_encoding($text) {
    // Удаляем BOM
    $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
    
    // Пробуем определить кодировку
    $encoding = mb_detect_encoding($text, ['UTF-8', 'Windows-1251', 'KOI8-R', 'ISO-8859-1'], true);
    
    if ($encoding && $encoding !== 'UTF-8') {
        $text = mb_convert_encoding($text, 'UTF-8', $encoding);
    }
    
    // Заменяем неправильные XML сущности
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
    
    return $text;
}

// Очистка текста (без обрезания)
function clean_text($text, $max_len = 0) {
    if (empty($text)) {
        return '';
    }
    
    // Преобразуем HTML сущности
    $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    
    $text = trim($text);
    
    // Обрезаем только если указан max_len > 0
    if ($max_len > 0 && mb_strlen($text) > $max_len) {
        $text = mb_substr($text, 0, $max_len) . '…';
    }
    
    return $text;
}
?>