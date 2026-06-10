<?php
// RSS Mixer - смешивает несколько RSS лент и отдаёт как один RSS
// Загрузите этот файл на хостинг и откройте в браузере или добавьте в RSS-ридер

// Настройки
$max_items = 200;          // максимум новостей в выдаче (увеличил)
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
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
    <title>RSS Mixer - Все новости IT</title>
    <link><?= htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) ?></link>
    <description>Смесь RSS лент из 14 источников - полные тексты новостей</description>
    <language>ru-ru</language>
    <lastBuildDate><?= date(DATE_RSS) ?></lastBuildDate>
    <generator>RSS Mixer 2.0</generator>

<?php foreach ($all_items as $item): ?>
    <item>
        <title><![CDATA[<?= $item['title'] ?>]]></title>
        <link><?= htmlspecialchars($item['link']) ?></link>
        <guid><?= htmlspecialchars($item['link']) ?></guid>
        <pubDate><?= $item['pubDate'] ?></pubDate>
        <description><![CDATA[<?= $item['description'] ?>]]></description>
        <content:encoded><![CDATA[<?= $item['content_encoded'] ?? $item['description'] ?>]]></content:encoded>
        
        <!-- Информация об источнике -->
        <source url="<?= htmlspecialchars($item['source_url']) ?>"><?= htmlspecialchars($item['source']) ?></source>
        <dc:creator><?= htmlspecialchars($item['source']) ?></dc:creator>
        
        <!-- Дополнительная информация об источнике -->
        <custom:source xmlns:custom="http://custom.rss/ns/1.0">
            <custom:name><?= htmlspecialchars($item['source']) ?></custom:name>
            <custom:link><?= htmlspecialchars($item['source_url']) ?></custom:link>
        </custom:source>
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
            
            $items[] = [
                'title' => clean_text((string)$item->title),
                'link' => (string)$item->link,
                'description' => clean_text((string)$item->description, 0), // 0 = без ограничения
                'content_encoded' => clean_text($content_encoded, 0),
                'pubDate' => date(DATE_RSS, strtotime((string)$item->pubDate)),
                'source' => $source_name,
                'source_url' => $url,
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
            
            $items[] = [
                'title' => clean_text((string)$entry->title),
                'link' => $link,
                'description' => clean_text($content, 0),
                'content_encoded' => clean_text($content, 0),
                'pubDate' => date(DATE_RSS, strtotime((string)$entry->updated)),
                'source' => $source_name,
                'source_url' => $url,
            ];
        }
    }
    
    return $items;
}

// Альтернативная загрузка через curl
function curl_get_contents($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'RSS Mixer/2.0 (https://github.com/)',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING => '', // Поддерживает сжатие
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
    
    // Удаляем HTML теги (опционально, закомментируйте если хотите сохранить HTML)
    // $text = strip_tags($text);
    
    // Преобразуем HTML сущности
    $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    
    // Убираем лишние пробелы, но сохраняем переносы строк
    $text = preg_replace('/[ \t]+/', ' ', $text);
    
    $text = trim($text);
    
    // Обрезаем только если указан max_len > 0
    if ($max_len > 0 && mb_strlen($text) > $max_len) {
        $text = mb_substr($text, 0, $max_len) . '…';
    }
    
    return $text;
}
?>