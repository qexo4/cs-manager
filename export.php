<?php
// Eksport wszystkich kont do pliku CSV (otwiera się poprawnie w Excelu / Arkuszach Google)
require_once 'config.php';

$stmt = $pdo->query("SELECT * FROM accounts ORDER BY id DESC");
$accounts = $stmt->fetchAll();

$filename = 'cs-manager-export_' . date('Y-m-d_H-i') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// BOM na starcie pliku, żeby Excel poprawnie odczytał polskie znaki (UTF-8)
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Nagłówki kolumn
fputcsv($output, [
    'ID',
    'Nazwa',
    'Link Steam',
    'Wkład (PLN)',
    'Stan końcowy (PLN)',
    'Zysk skumulowany (PLN)',
    'Historia wyników',
    'Dni bana',
    'Start bana',
]);

foreach ($accounts as $acc) {
    fputcsv($output, [
        $acc['id'],
        $acc['name'],
        $acc['steam_url'],
        number_format((float)$acc['amount'], 2, ',', ''),
        number_format((float)$acc['end_amount'], 2, ',', ''),
        number_format((float)$acc['profit'], 2, ',', ''),
        $acc['result'],
        $acc['ban_days'],
        $acc['ban_start_at'],
    ]);
}

fclose($output);
exit;
