<?php
// Włączamy wyświetlanie absolutnie wszystkich błędów na ekranie
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

echo "<h3>🛠️ Test połączenia z Discordem</h3>";

if (!function_exists('sendDiscordMessage')) {
    die("❌ BŁĄD KRYTYCZNY: Funkcja sendDiscordMessage() nie istnieje! Upewnij się, że masz nowy kod w config.php.");
}

echo "Funkcja załadowana poprawnie. Próbuję wysłać wiadomość...<br><br>";

// Próba wysłania testowej wiadomości
$result = sendDiscordMessage("🔔 **TEST POŁĄCZENIA** - Panel komunikuje się z serwerem!");

echo "<strong>Odpowiedź z serwera Discord:</strong><br>";
echo "<pre>";
var_dump($result);
echo "</pre>";

if ($result === false) {
    echo "<br><span style='color: red; font-weight: bold;'>Wystąpił błąd! Sprawdź logi Render lub działanie cURL.</span>";
} else {
    echo "<br><span style='color: green; font-weight: bold;'>Wysłano pomyślnie. Sprawdź swój kanał na Discordzie!</span>";
}
?>
