<?php
// Definicja parametrów połączenia z bazą PostgreSQL (Render/VPS)
$dbUrl = 'postgresql://sc2_user:wXGoNehFbduj2j6wSlJqkWrfkbMTPrOY@dpg-d8hfpme47okc738jnjt0-a/sc2';
$dbopts = parse_url($dbUrl);
$host = $dbopts["host"];
$port = isset($dbopts["port"]) ? $dbopts["port"] : "5432";
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$db   = ltrim($dbopts["path"], '/');
$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Wymuszenie rzucania wyjątków przy błędach SQL do łatwego debugowania
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Zwracanie wyników jako czyste tablice asocjacyjne
    PDO::ATTR_EMULATE_PREPARES => false, // Wyłączenie emulacji – natywne bindowanie chroni przed SQL Injection
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // Logowanie błędu na serwerze i ukrycie wrażliwych danych przed użytkownikiem
     error_log("Błąd bazy danych: " . $e->getMessage());
     die("Błąd połączenia z bazą danych. Spróbuj ponownie później.");
}

/**
 * Wysyła powiadomienie tekstowe na serwer Discord przy użyciu protokołu cURL i formatu JSON.
 */
function sendDiscordMessage($message) {
    // Twój nowy, działający URL Webhooka Discord
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout chroni aplikację przed zawieszeniem, gdyby API Discorda wolno działało
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Błąd cURL Discord: " . $error);
    }
    return $response;
}
?>
