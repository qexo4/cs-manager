<?php
// =========================================================================
// 1. DANE POŁĄCZENIOWE DO BAZY POSTGRESQL
// =========================================================================
$host = 'aws-0-eu-west-1.pooler.supabase.com'; 
$port = '5432';                                
$user = 'postgres.gspabzptmmwboauxwvip';       
$pass = 'ZXC123asd!@#1'; 
$db   = 'postgres';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Błąd połączenia z bazą danych: " . $e->getMessage());
}

// =========================================================================
// 2. FUNKCJE POMOCNICZE (POWIADOMIENIA DISCORD)
// =========================================================================
function sendDiscordMessage($message) {
    // Darmowe Proxy - omija blokadę IP z Rendera
    $webhookUrl = "https://webhook.lewisakura.moe/api/webhooks/1519052406787277065/88ydtTiZwu-4H94mol1eoAQEY4yR0-OylXqrMig3InDLss1-1XyAGSQxgp7pQ7NcyEGh";

    $json_data = json_encode([
        "content" => $message,
        "username" => "CS-Manager Bot",
        "tts" => false
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'User-Agent: PHP-Script'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    // Zoptymalizowany czas (3 sekundy to idealny balans między szybkością a niezawodnością proxy)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
?>
