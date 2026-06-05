<?php
require_once 'config.php';

// Szukamy kont, które mają aktualnie nałożonego bana
$stmt = $pdo->query("SELECT * FROM accounts WHERE ban_days > 0 AND ban_start_at IS NOT NULL");
$accounts = $stmt->fetchAll();

$now = new DateTime();

foreach ($accounts as $acc) {
    $banStart = new DateTime($acc['ban_start_at']);
    $banDays = $acc['ban_days'];
    
    // Wyliczamy dokładny moment zakończenia bana
    $banEnd = clone $banStart;
    $banEnd->modify("+$banDays days");
    
    // Jeżeli obecny czas minął już datę końca bana -> usuwamy ban
    if ($now >= $banEnd) {
        $updateStmt = $pdo->prepare("UPDATE accounts SET ban_days = 0, ban_start_at = NULL WHERE id = ?");
        $updateStmt->execute([$acc['id']]);
        
        // Wysyłamy powiadomienie na Twój kanał Discord!
        $msg = "🎉 🔔 **KONIEC BANA!**\n";
        $msg .= "👤 Użytkownik **{$acc['name']}** został właśnie automatycznie odbanowany!\n";
        $msg .= "✅ Konto jest ponownie gotowe do użycia.";
        
        if (function_exists('sendDiscordMessage')) {
            sendDiscordMessage($msg);
        }
        
        echo "Odbanowano konto: " . $acc['name'] . "\n";
    }
}
echo "Skrypt cron zakończony.";
?>
