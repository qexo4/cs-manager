<?php
// Twój link do bazy danych z Rendera
$dbUrl = 'postgresql://sc2_user:wXGoNehFbduj2j6wSlJqkWrfkbMTPrOY@dpg-d8hfpme47okc738jnjt0-a/sc2';

// Rozbijamy link na części, żeby PHP zrozumiał dane
$dbopts = parse_url($dbUrl);

$host = $dbopts["host"];
$port = isset($dbopts["port"]) ? $dbopts["port"] : "5432";
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$db   = ltrim($dbopts["path"], '/');

// Zmiana konfiguracji na PostgreSQL (pgsql)
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
    // Funkcja wysyłająca powiadomienia na Discord
// Funkcja wysyłająca powiadomienia na Discord (Wersja Poprawiona)
function sendDiscordMessage($message) {
    // Upewnij się, że ten link jest poprawny i kompletny!
    $webhookUrl = "https://discord.com/api/webhooks/1516845357148012584/cQvSxqpuqDWjWS8-y_J7lRlXXMezqF0n-UfklpSdsrb-zLlj_RcY4jVaQOScsPGtP958";

    if (empty($webhookUrl)) {
        error_log("Błąd: Pusty URL Webhooka!");
        return false;
    }

    $json_data = json_encode([
        "content" => $message,
        "username" => "CS-Manager Bot",
        "tts" => false
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $ch = curl_init($webhookUrl);
    
    // Sprawdzamy, czy curl_init się udał
    if ($ch === false) {
        error_log("Błąd: curl_init zwrócił false!");
        return false;
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'User-Agent: PHP-Script'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $response = curl_exec($ch);
    
    if ($response === false) {
        error_log("Błąd cURL: " . curl_error($ch));
    }
    
    curl_close($ch);
    return $response;
}
?>
