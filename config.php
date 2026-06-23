<?php
// =========================================================================
// 0. ŁADOWANIE ZMIENNYCH ŚRODOWISKOWYCH (Lokalny parser .env)
// =========================================================================
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorowanie komentarzy (#)
        if (strpos(trim($line), '#') === 0) continue; 
        
        // Rozdzielanie po pierwszym znaku '=' (wartości mogą zawierać '=')
        list($name, $value) = explode('=', $line, 2);
        
        $name = trim($name);
        // Usunięcie białych znaków oraz opcjonalnych cudzysłowów
        $value = trim($value, " \t\n\r\0\x0B\""); 
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

/**
 * Funkcja pomocnicza do bezpiecznego pobierania zmiennych
 */
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// =========================================================================
// 1. DANE POŁĄCZENIOWE DO BAZY POSTGRESQL
// =========================================================================
$host = env('DB_HOST'); 
$port = env('DB_PORT', '5432'); // 5432 jako domyślny fallback
$user = env('DB_USER');       
$pass = env('DB_PASS');                       
$db   = env('DB_NAME', 'postgres');

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // TYMCZASOWY DEBUG - usuniemy to po znalezieniu przyczyny
     die("Błąd bazy: " . $e->getMessage() . " | DSN: $dsn | User: $user");
}

// =========================================================================
// 2. FUNKCJE POMOCNICZE (POWIADOMIENIA DISCORD)
// =========================================================================
function sendDiscordMessage($message) {
    // Pobranie URL z env
    $webhookUrl = env('DISCORD_WEBHOOK_URL');
    
    if (!$webhookUrl) {
        error_log("Błąd: Nie zdefiniowano DISCORD_WEBHOOK_URL");
        return false;
    }

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
