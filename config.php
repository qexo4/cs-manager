<?php
// =========================================================================
// 1. DANE POŁĄCZENIOWE DO BAZY POSTGRESQL (SUPABASE IPV4 POOLER)
// =========================================================================
$host = 'aws-0-eu-west-1.pooler.supabase.com'; 
$port = '6543';                                
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
     die("Błąd połączenia z nową bazą danych: " . $e->getMessage());
}

// =========================================================================
// 2. FUNKCJE POMOCNICZE (POWIADOMIENIA DISCORD)
// =========================================================================
/**
 * Wysyła sformatowaną wiadomość na Discord za pomocą Webhooka
 */
function sendDiscordMessage($message) {
    // Twój aktualny link webhooka z Discorda
    $webhookUrl = "https://discord.com/api/webhooks/1512507843801124876/JB3O32EtcgKbUmBDjnTY9kJAmszKY7oIVS9FLin6bKsEsPZOmN1fxGZeJ_XT2xIUucNb";

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Błąd cURL (Discord): " . $error);
    }
    return $response;
}
?>
