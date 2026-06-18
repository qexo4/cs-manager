<?php
// Dane połączeniowe do bazy PostgreSQL zaktualizowane pod Supabase
$dbUrl = 'postgresql://postgres:ZXC123asd!@#1@db.gspabzptmmwboauxwvip.supabase.co:5432/postgres';

$dbopts = parse_url($dbUrl);
$host = $dbopts["host"];
$port = isset($dbopts["port"]) ? $dbopts["port"] : "5432";
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$db   = ltrim($dbopts["path"], '/');
$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Błąd połączenia: " . $e->getMessage());
}

/**
 * Wysyła sformatowaną wiadomość na Discord za pomocą Webhooka
 */
function sendDiscordMessage($message) {
    // Twój link podany w zgłoszeniu
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
        error_log("Błąd cURL: " . $error);
    }
    return $response;
}
?>
