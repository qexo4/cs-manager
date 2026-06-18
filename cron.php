<?php
require_once 'config.php';

// Szukamy kont z aktywną blokadą
$stmt = $pdo->query("SELECT * FROM accounts WHERE ban_days > 0 AND ban_start_at IS NOT NULL");
$accounts = $stmt->fetchAll();

// Pobieramy aktualny czas serwera
$now = new DateTime();

foreach ($accounts as $acc) {
    $banStart = new DateTime($acc['ban_start_at']);
    $banDays = $acc['ban_days'];
    
    // Obliczamy koniec bana
    $banEnd = clone $banStart;
    $banEnd->modify("+$banDays days");
    
    // Logika warunku: jeśli obecny czas jest większy lub równy końcowi bana
    if ($now >= $banEnd) {
        // Czyścimy flagi bana w bazie danych dla danego konta
        $updateStmt = $pdo->prepare("UPDATE accounts SET ban_days = 0, ban_start_at = NULL WHERE id = ?");
        $updateStmt->execute([$acc['id']]);
        
        // Przygotowanie profesjonalnej wiadomości Embed lub zwykłej tekstowej z emoji
        $msg = "🎉 🔔 **KONIEC BANA!**\n";
        $msg .= "👤 Konto: **{$acc['name']}** zostało automatycznie odbanowane!\n";
        $msg .= "✅ Status: Gotowe do ponownej pracy. Czas trwania bana wynosił: $banDays dni.";
        
        if (function_exists('sendDiscordMessage')) {
            sendDiscordMessage($msg);
        }
        
        echo "Sukces: Odbanowano konto i wysłano powiadomienie: " . $acc['name'] . "\n";
    }
}

?>
