<?php
require_once 'config.php';

// Pobieramy wszystkie konta, które mają jeszcze aktywnego bana
$stmt = $pdo->query("SELECT * FROM accounts WHERE ban_days > 0 AND ban_start_at IS NOT NULL");
$accounts = $stmt->fetchAll();

$now = new DateTime();

foreach ($accounts as $acc) {
    $banStart = new DateTime($acc['ban_start_at']);
    $banDays = $acc['ban_days'];
    
    // Obliczamy datę, kiedy ban się kończy
    $banEnd = clone $banStart;
    $banEnd->modify("+$banDays days");
    
    // Jeśli aktualny czas jest WIĘKSZY lub RÓWNY dacie końca bana -> UNBAN!
    if ($now >= $banEnd) {
        // Aktualizujemy konto w bazie danych (czyszczenie bana)
        $updateStmt = $pdo->prepare("UPDATE accounts SET ban_days = 0, ban_start_at = NULL WHERE id = ?");
        $updateStmt->execute([$acc['id']]);
        
        // Wysyłamy powiadomienie o końcu bana na Discorda!
        $msg = "🎉 🔔 **KONIEC BANA!**\n";
        $msg .= "👤 Użytkownik **{$acc['name']}** został właśnie odbanowany automatycznie!\n";
        $msg .= "✅ Konto jest ponownie gotowe do działań.";
        
        if (function_exists('sendDiscordMessage')) {
            sendDiscordMessage($msg);
        }
        
        echo "Odbanowano: " . $acc['name'] . "\n";
    }
}
echo "Cron wykonany pomyślnie.";
?>
