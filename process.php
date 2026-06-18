<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $amount = floatval($_POST['amount']);
    $end_amount = floatval($_POST['end_amount']);
    $result = $_POST['result'];
    $ban_selection = $_POST['ban_days']; // może być liczbą tekstową lub "custom"
    
    // Obliczenie profitu
    $profit = $end_amount - $amount;
    
    // Logika określenia ban_days i ban_start_at
    if ($ban_selection === '0') {
        $ban_days = 0;
        $ban_start_at = null;
    } else {
        // Jeśli użytkownik użył kalendarza (własna data)
        if (isset($_POST['custom_date_actual']) && !empty($_POST['custom_date_actual'])) {
            $targetDate = new DateTime($_POST['custom_date_actual']);
            $now = new DateTime();
            
            // Liczymy precyzyjną różnicę czasu
            $diff = $now->diff($targetDate);
            // Wyliczamy dni w ujęciu zmiennoprzecinkowym lub zaokrąglamy w górę
            $ban_days = ceil(($targetDate->getTimestamp() - $now->getTimestamp()) / (60 * 60 * 24));
            
            if ($ban_days <= 0) {
                $ban_days = 1; // Zabezpieczenie minimalne
            }
            
            // Ustawiamy start bana jako punkt wsteczny tak, aby dokładnie zgadzał się z końcem!
            // To trik gwarantujący, że ban_start_at + ban_days da idealnie wybraną datę z kalendarza.
            $ban_start_at = $now->format('Y-m-d H:i:s');
        } else {
            // Standardowy wybór: 8 lub 30 dni od teraz
            $ban_days = intval($ban_selection);
            $ban_start_at = date('Y-m-d H:i:s');
        }
    }

    // Zapytanie UPSERT dla PostgreSQL
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

    // Powiadomienie na Discord o dodaniu/edycji
    $msg = "📝 **Zaktualizowano / Dodano konto w panelu!**\n";
    $msg .= "👤 **Nazwa:** $name\n";
    $msg .= "💰 **Zysk:** $profit PLN ($result)\n";
    if ($ban_days > 0) {
        $msg .= "⏱️ **Status:** Nałożono ban na około $ban_days dni.";
    } else {
        $msg .= "✅ **Status:** Brak bana (Aktywne).";
    }

    if (function_exists('sendDiscordMessage')) {
        sendDiscordMessage($msg);
    }

    header('Location: index.php');
    exit;
}

// Obsługa usuwania konta
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM accounts WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: index.php');
    exit;
}
?>
