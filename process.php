<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $amount = floatval($_POST['amount']);
    $end_amount = floatval($_POST['end_amount']);
    $result = $_POST['result'];
    
    // AUTOMATYCZNE OBLICZANIE ZYSKU: Koniec - Kwota
    $profit = $end_amount - $amount;
    
    // Usunięto całkowicie pobieranie $_POST['ban_days'], co naprawia błąd "Undefined array key"

    // Oczyszczona składnia UPSERT pod PostgreSQL - bez obsługi kolumn ban_days i ban_start_at
    $sql = "INSERT INTO accounts (name, amount, end_amount, profit, result) 
            VALUES (:name, :amount, :end_amount, :profit, :result) 
            ON CONFLICT (name) DO UPDATE SET 
                amount = :amount2, 
                end_amount = :end_amount2,
                profit = :profit2, 
                result = :result2";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'amount' => $amount,
        'end_amount' => $end_amount,
        'profit' => $profit,
        'result' => $result,
        'amount2' => $amount,
        'end_amount2' => $end_amount,
        'profit2' => $profit,
        'result2' => $result
    ]);

    // Formatowanie wiadomości na Discord bez wzmianki o banach
    $msg = "📝 **Zaktualizowano / Dodano konto w panelu!**\n";
    $msg .= "👤 **Nazwa:** $name\n";
    $msg .= "💰 **Zysk:** $profit PLN ($result)\n";

    // Wysyłamy powiadomienie do Discorda (funkcja z config.php)
    if (function_exists('sendDiscordMessage')) {
        sendDiscordMessage($msg);
    }

    // Teraz przekierowanie wykona się prawidłowo, bo nie ma żadnych Warningów wcześniej
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
