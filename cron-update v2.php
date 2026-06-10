<?php
// cron-update.php - обновляет RSS кэш (исправленная версия для Beget)
// Секретный ключ (обязательно измените на свой!)
$secret_key = 'my_secret_key_2024';

// Проверка безопасности
if (!isset($_GET['token']) || $_GET['token'] !== $secret_key) {
    http_response_code(403);
    die('Forbidden - wrong token');
}

echo "=== RSS Cache Updater ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";

// Путь к файлу кэша
$cache_file = __DIR__ . '/rss.xml';

// Вместо curl-запроса к себе — просто включаем генератор и захватываем его вывод
ob_start();

// Подключаем файл-генератор (он сам отдаст RSS)
require __DIR__ . '/rss-generator.php';

$content = ob_get_clean();

if (!empty($content) && strpos($content, '<?xml') === 0) {
    file_put_contents($cache_file, $content);
    echo "✅ SUCCESS: RSS cached to " . $cache_file . "\n";
    echo "File size: " . strlen($content) . " bytes\n";
    echo "Updated at: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "❌ ERROR: Invalid RSS content\n";
    echo "First 200 chars of output:\n" . substr($content, 0, 200) . "\n";
}

echo "=== Done ===\n";
?>
