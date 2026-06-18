<?php
// Przywrócono: skrypt cron odpala się przy odświeżeniu strony, aby aktualizować bany w tle
require_once 'cron.php'; 

// Pobranie danych
$stmt = $pdo->query("SELECT * FROM accounts ORDER BY id DESC");
$accounts = $stmt->fetchAll();

// Zmienne do statystyk
$totalAccounts = count($accounts);
$totalAmount = 0;
$totalEndAmount = 0;
$totalProfit = 0;
$winsCount = 0;
$lossesCount = 0;

$chartLabels = [];
$chartData = [];

foreach ($accounts as $acc) {
    $totalAmount += $acc['amount'];
    $totalEndAmount += $acc['end_amount'];
    $totalProfit += $acc['profit'];
    
    if ($acc['result'] === 'win') {
        $winsCount++;
    } else {
        $lossesCount++;
    }

    $chartLabels[] = $acc['name'];
    $chartData[] = $acc['profit'];
}

$chartLabels = array_reverse($chartLabels);
$chartData = array_reverse($chartData);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Kont - cs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-7xl mx-auto space-y-8">
        
        <header class="bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-700 space-y-4">
            <div>
                <h1 class="text-3xl font-bold text-emerald-400">Panel Kont</h1>
                <p class="text-gray-400 text-sm">Baza danych: <span class="text-emerald-500 font-semibold">cs</span></p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 pt-2">
                <div class="bg-gray-700/40 p-4 rounded-xl border border-gray-600">
                    <p class="text-xs text-gray-400 uppercase font-semibold">Liczba Kont</p>
                    <p class="text-2xl font-bold text-blue-400"><?= $totalAccounts ?></p>
                </div>
                <div class="bg-gray-700/40 p-4 rounded-xl border border-gray-600">
                    <p class="text-xs text-gray-400 uppercase font-semibold">Suma Wkładów</p>
                    <p class="text-2xl font-bold text-amber-400"><?= number_format($totalAmount, 2, ',', ' ') ?> PLN</p>
                </div>
                <div class="bg-gray-700/40 p-4 rounded-xl border border-gray-600">
                    <p class="text-xs text-gray-400 uppercase font-semibold font-bold text-emerald-400">Łączny Zysk Netto</p>
                    <p class="text-2xl font-bold text-emerald-400"><?= number_format($totalProfit, 2, ',', ' ') ?> PLN</p>
                </div>
                <div class="bg-gray-700/40 p-4 rounded-xl border border-gray-600">
                    <p class="text-xs text-gray-400 uppercase font-semibold">Wyniki ogólne</p>
                    <p class="text-2xl font-bold text-gray-200">
                        <span class="text-emerald-400"><?= $winsCount ?> W</span> <span class="text-gray-500">/</span> <span class="text-red-400"><?= $lossesCount ?> L</span>
                    </p>
                </div>
                <div class="bg-gray-700/40 p-4 rounded-xl border border-gray-600 col-span-2 md:col-span-1">
                    <p class="text-xs text-gray-400 uppercase font-semibold text-amber-400 font-bold">Najbliższy Unban</p>
                    <p id="top-shortest-ban" class="text-base font-bold text-gray-300 mt-1 break-all">Obliczanie...</p>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-700 h-fit">
                <h2 class="text-xl font-bold mb-4 text-gray-200">Dodaj / Aktualizuj konto</h2>
                <form id="account-form" action="process.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Nazwa konta</label>
                        <input type="text" name="name" required class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Kwota (Wkład)</label>
                            <input type="number" id="input-amount" name="amount" step="0.01" required class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Koniec (Finał)</label>
                            <input type="number" id="input-end" name="end_amount" step="0.01" required class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>
                    
                    <div class="bg-gray-900/50 p-2.5 rounded-lg border border-gray-700/50 text-sm text-gray-400 flex justify-between">
                        <span>Obliczony zysk live:</span>
                        <span id="live-profit" class="font-bold text-gray-300">0,00 PLN</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase mb-1">Wynik</label>
                        <select name="result" class="w-full bg-gray-700 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="win">WIN (Wygrana)</option>
                            <option value="lose">LOSE (Przegrana)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-gray-900 font-bold p-3 rounded-lg transition duration-200 cursor-pointer">
                        Zatwierdź i Zapisz
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-700 flex flex-col justify-between">
                <h2 class="text-xl font-bold mb-4 text-gray-200">Wykres Profitu Netto per Konto</h2>
                <div class="relative w-full h-64 md:h-full max-h-[340px]">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-2xl shadow-lg border border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-700">
                <h2 class="text-xl font-bold text-gray-200">Lista Kont i Szczegóły</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-700/50 text-gray-400 text-xs uppercase font-semibold">
                            <th class="p-4">Konto</th>
                            <th class="p-4">Kwota (Wkład)</th>
                            <th class="p-4">Koniec (Finał)</th>
                            <th class="p-4">Zysk (Netto)</th>
                            <th class="p-4">Wynik</th>
                            <th class="p-4">Status / Czas do końca bana</th>
                            <th class="p-4 text-right">Akcje</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if (empty($accounts)): ?>
                            <tr><td colspan="7" class="p-8 text-center text-gray-500">Brak danych w bazie cs. Dodaj swoje pierwsze konto.</td></tr>
                        <?php else: ?>
                            <?php foreach ($accounts as $index => $acc): ?>
                                <tr class="hover:bg-gray-750 transition-colors">
                                    <td class="p-4 font-medium text-gray-200"><?= htmlspecialchars($acc['name']) ?></td>
                                    <td class="p-4 text-gray-400"><?= number_format($acc['amount'], 2, ',', ' ') ?> PLN</td>
                                    <td class="p-4 text-amber-400 font-semibold"><?= number_format($acc['end_amount'], 2, ',', ' ') ?> PLN</td>
                                    <td class="p-4 <?= $acc['profit'] >= 0 ? 'text-emerald-400' : 'text-rose-400' ?> font-bold">
                                        <?= number_format($acc['profit'], 2, ',', ' ') ?> PLN
                                    </td>
                                    <td class="p-4">
                                        <?php if ($acc['result'] === 'win'): ?>
                                            <span class="px-2.5 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20">WIN</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-xs font-bold bg-red-500/10 text-red-400 rounded-full border border-red-500/20">LOSE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <?php if (isset($acc['ban_days']) && $acc['ban_days'] > 0 && !empty($acc['ban_start_at'])): ?>
                                            <?php 
                                            $startTimeMs = strtotime($acc['ban_start_at']) * 1000;
                                            $banDays = $acc['ban_days'];
                                            ?>
                                            <span id="timer-<?= $index ?>" class="ban-countdown px-2.5 py-1 text-xs font-medium bg-red-500/10 text-red-400 rounded-full border border-red-500/20" 
                                                  data-start="<?= $startTimeMs ?>" 
                                                  data-days="<?= $banDays ?>"
                                                  data-name="<?= htmlspecialchars($acc['name']) ?>">
                                                Obliczanie...
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-xs font-medium bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20">Brak Blokady</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="process.php?action=delete&id=<?= $acc['id'] ?>" onclick="return confirm('Usunąć konto <?= htmlspecialchars($acc['name']) ?>?')" class="text-red-400 hover:text-red-300 font-semibold text-sm transition">Usuń</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // LIVE OBLICZANIE ZYSKU W FORMULARZU
        const inputAmount = document.getElementById('input-amount');
        const inputEnd = document.getElementById('input-end');
        const liveProfit = document.getElementById('live-profit');

        function calculateLiveProfit() {
            const amount = parseFloat(inputAmount.value) || 0;
            const end = parseFloat(inputEnd.value) || 0;
            const profit = end - amount;
            
            liveProfit.innerText = profit.toFixed(2).replace('.', ',') + ' PLN';
            
            if(profit >= 0) {
                liveProfit.className = "font-bold text-emerald-400";
            } else {
                liveProfit.className = "font-bold text-red-400";
            }
        }
        inputAmount.addEventListener('input', calculateLiveProfit);
        inputEnd.addEventListener('input', calculateLiveProfit);

        // PRZYWRÓCONO: LIVE COUNTDOWN BANA + AKTUALIZACJA GÓRNEGO KAFELKA
        function updateBanTimers() {
            const now = Date.now();
            let shortestTime = Infinity;
            let shortestLabel = "";
            
            document.querySelectorAll('.ban-countdown').forEach((timerEl) => {
                const startTime = parseInt(timerEl.getAttribute('data-start'));
                const banDays = parseInt(timerEl.getAttribute('data-days'));
                const accName = timerEl.getAttribute('data-name');
                const endTime = startTime + (banDays * 24 * 60 * 60 * 1000);
                const timeLeft = endTime - now;

                if (timeLeft <= 0) {
                    timerEl.innerHTML = "Ban minął!";
                    timerEl.className = "px-2.5 py-1 text-xs font-medium bg-amber-500/10 text-amber-400 rounded-full border border-amber-500/20";
                } else {
                    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                    let label = "";
                    if (days > 0) label += `${days}d `;
                    label += `${hours}h ${minutes}m ${seconds}s`;

                    timerEl.innerHTML = `Zostało: ${label} (${banDays} dni)`;

                    if (timeLeft < shortestTime) {
                        shortestTime = timeLeft;
                        shortestLabel = `${accName} (${label})`;
                    }
                }
            });

            const topBox = document.getElementById('top-shortest-ban');
            if (shortestTime === Infinity) {
                topBox.innerText = "Brak blokad";
                topBox.className = "text-xl font-bold text-emerald-400 mt-1";
            } else {
                topBox.innerText = shortestLabel;
                topBox.className = "text-base font-bold text-amber-400 mt-1 break-all";
            }
        }
        setInterval(updateBanTimers, 1000);
        updateBanTimers();

        // CHART (WYKRES)
        const ctx = document.getElementById('profitChart').getContext('2d');
        const chartLabels = <?php echo json_encode($chartLabels); ?>;
        const chartData = <?php echo json_encode($chartData); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Zysk Netto (PLN)',
                    data: chartData,
                    backgroundColor: 'rgba(52, 211, 153, 0.2)',
                    borderColor: 'rgba(52, 211, 153, 1)',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: { color: '#9ca3af' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    }
                }
            }
        });
    </script>
</body>
</html>
