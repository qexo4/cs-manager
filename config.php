<?php
$dbUrl = 'postgresql://sc2_user:wXGoNehFbduj2j6wSlJqkWrfkbMTPrOY@dpg-d8hfpme47okc738jnjt0-a/sc2';
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

function sendDiscordMessage($message) {
    // TWÓJ NOWY LINK
    $webhookUrl = "https://discord.com/api/webhooks/1516845357148012584/cQvSxqpuqDWjWS8-y_J7lRlXXMezqF0n-UfklpSdsrb-zLlj_RcY4jVaQOScsPGtP958";

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
    
    if ($error) error_log("Błąd cURL: " . $error);
    return $response;
}
?>
