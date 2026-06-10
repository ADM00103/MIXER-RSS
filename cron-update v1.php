<?php
// cron-update.php - обновляет RSS кэш
// Секретный ключ (измените на свой!)
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

// Получаем свежую RSS ленту
$generator_url = 'https://' . $_SERVER['HTTP_HOST'] . '/rss-generator.php';

echo "Fetching RSS from: $generator_url\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $generator_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_USERAGENT => 'Cron-Updater/1.0',
]);
$content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($http_code === 200 && !empty($content)) {
    file_put_contents($cache_file, $content);
    echo "✅ SUCCESS: RSS cached to " . $cache_file . "\n";
    echo "File size: " . strlen($content) . " bytes\n";
    echo "Updated at: " . date('Y-m-d H:i:s') . "\n";
} else {
    echo "❌ ERROR: HTTP code $http_code\n";
    if ($error) echo "CURL error: $error\n";
}

echo "=== Done ===\n";
?>