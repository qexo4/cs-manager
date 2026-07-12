<?php
require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $steam_url = trim($_POST['steam_url'] ?? '');
    $amount = floatval($_POST['amount']);
    $end_amount = floatval($_POST['end_amount']);
    $result = trim($_POST['result']);
    $ban_days = intval($_POST['ban_days']); // Przywrócono pobieranie liczby dni bana
    
    // AUTOMATYCZNE OBLICZANIE ZYSKU: Koniec - Kwota
    $profit = $end_amount - $amount;
    
    // Jeśli wybrano ban > 0 dni, zapisujemy aktualny czas jako start bana
    $ban_start_at = $ban_days > 0 ? date('Y-m-d H:i:s') : null;
    // Pełna składnia UPSERT pod PostgreSQL z sumowaniem zysku i dopisywaniem wyniku po przecinku
    $sql = "INSERT INTO accounts (name, steam_url, amount, end_amount, profit, result, ban_days, ban_start_at) 
            VALUES (:name, :steam_url, :amount, :end_amount, :profit, :result, :ban_days, :ban_start_at) 
            ON CONFLICT (name) DO UPDATE SET 
                steam_url = COALESCE(NULLIF(:steam_url2, ''), accounts.steam_url), -- Nie nadpisuj linku pustą wartością
                amount = :amount2, 
                end_amount = :end_amount2,
                profit = accounts.profit + EXCLUDED.profit, -- Sumowanie zysku
                result = accounts.result || ', ' || EXCLUDED.result, -- Dopisywanie wyniku po przecinku (np. win, lose, remis)
                ban_days = :ban_days2, 
                ban_start_at = :ban_start_at2";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'steam_url' => $steam_url,
        'amount' => $amount,
        'end_amount' => $end_amount,
        'profit' => $profit,
        'result' => $result,
        'ban_days' => $ban_days,
        'ban_start_at' => $ban_start_at,
        'steam_url2' => $steam_url,
        'amount2' => $amount,
        'end_amount2' => $end_amount,
        'ban_days2' => $ban_days,
        'ban_start_at2' => $ban_start_at
    ]);
    // Tłumaczenie wyniku na ładny polski tekst do wiadomości Discord
    $pl_result = $result;
    if ($result === 'win') $pl_result = 'Wygrana (win)';
    if ($result === 'lose') $pl_result = 'Przegrana (lose)';
    if ($result === 'remis') $pl_result = 'Remis';
    // Formatowanie wiadomości na Discord wraz z informacjami o banie
    $msg = "📝 **Zaktualizowano / Dodano konto w panelu!**\n";
    $msg .= "👤 **Nazwa:** $name\n";
    $msg .= "💰 **Zysk z tej sesji:** $profit PLN ($pl_result)\n";
    if ($ban_days > 0) {
        $msg .= "⏱️ **Status:** Nałożono ban na $ban_days dni.";
    } else {
        $msg .= "✅ **Status:** Brak bana (Aktywne).";
    }
    // Wysyłamy powiadomienie do Discorda
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
    header('Location: /');
    exit;
}
