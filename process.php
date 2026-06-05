<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $amount = floatval($_POST['amount']);
    $end_amount = floatval($_POST['end_amount']);
    $result = $_POST['result'];
    $ban_days = intval($_POST['ban_days']);
    
    // AUTOMATYCZNE OBLICZANIE ZYSKU: Koniec - Kwota
    $profit = $end_amount - $amount;
    
    // Jeśli wybrano ban > 0 dni, zapisujemy aktualny czas jako start bana
    $ban_start_at = $ban_days > 0 ? date('Y-m-d H:i:s') : null;

    // Poprawiona składnia UPSERT pod PostgreSQL (ON CONFLICT)
    $sql = "INSERT INTO accounts (name, amount, end_amount, profit, result, ban_days, ban_start_at) 
            VALUES (:name, :amount, :end_amount, :profit, :result, :ban_days, :ban_start_at) 
            ON CONFLICT (name) DO UPDATE SET 
                amount = :amount2, 
                end_amount = :end_amount2,
                profit = :profit2, 
                result = :result2, 
                ban_days = :ban_days2, 
                ban_start_at = :ban_start_at2";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'amount' => $amount,
        'end_amount' => $end_amount,
        'profit' => $profit,
        'result' => $result,
        'ban_days' => $ban_days,
        'ban_start_at' => $ban_start_at,
        'amount2' => $amount,
        'end_amount2' => $end_amount,
        'profit2' => $profit,
        'result2' => $result,
        'ban_days2' => $ban_days,
        'ban_start_at2' => $ban_start_at
    ]);

    // Formatowanie ładnej wiadomości na Discord
    $msg = "📝 **Zaktualizowano / Dodano konto w panelu!**\n";
    $msg .= "👤 **Nazwa:** $name\n";
    $msg .= "💰 **Zysk:** $profit PLN ($result)\n";
    if ($ban_days > 0) {
        $msg .= "⏱️ **Status:** Nałożono ban na $ban_days dni.";
    } else {
        $msg .= "✅ **Status:** Brak bana (Aktywne).";
    }

    // Wysyłamy powiadomienie (funkcja z config.php)
    if (function_exists('sendDiscordMessage')) {
        sendDiscordMessage($msg);
    }

    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $pdo->prepare("DELETE FROM accounts WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: index.php');
    exit;
}
?>
