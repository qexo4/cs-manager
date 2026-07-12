<?php
// Włączenie pliku konfiguracyjnego z połączeniem PDO oraz obsługą .env
require_once 'config.php'; 

// Skrypt cron odpala się przy odświeżeniu, aby aktualizować statusy w tle
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
$remisCount = 0; 

$chartLabels = [];
$chartData = [];

foreach ($accounts as $acc) {
    $totalAmount += $acc['amount'];
    $totalEndAmount += $acc['end_amount'];
    $totalProfit += $acc['profit'];
    
    $currentResults = explode(',', $acc['result']);
    foreach ($currentResults as $res) {
        $resTrimmed = trim($res);
        if ($resTrimmed === 'win') {
            $winsCount++;
        } elseif ($resTrimmed === 'lose') {
            $lossesCount++;
        } elseif ($resTrimmed === 'remis') {
            $remisCount++;
        }
    }

    $chartLabels[] = $acc['name'];
    $chartData[] = $acc['profit'];
}

$chartLabels = array_reverse($chartLabels);
$chartData = array_reverse($chartData);
?>
<!DOCTYPE html>
<html lang="pl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Kont - CS Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #111827; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #4b5563; }
    </style>
</head>
<body class="bg-[#0b0f19] text-gray-100 font-sans antialiased min-h-screen p-4 md:p-8 selection:bg-emerald-500 selection:text-gray-900">

    <div class="max-w-7xl mx-auto space-y-8">
        
        <header class="relative bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 p-6 rounded-2xl shadow-2xl border border-gray-800 overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-56 h-56 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-700/50 pb-4 space-y-2 md:space-y-0">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">
                        Panel Zarządzania Kontami
                    </h1>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 pt-4">
                <div class="bg-gray-800/40 backdrop-blur-md p-4 rounded-xl border border-gray-700/60 transition-all duration-200 hover:scale-[1.02] hover:border-gray-600">
                    <p class="text-[11px] text-gray-400 uppercase font-bold tracking-wider">Liczba Kont</p>
                    <p class="text-2xl font-black text-blue-400 mt-1"><?= $totalAccounts ?></p>
                </div>
                <div class="bg-gray-800/40 backdrop-blur-md p-4 rounded-xl border border-gray-700/60 transition-all duration-200 hover:scale-[1.02] hover:border-gray-600">
                    <p class="text-[11px] text-gray-400 uppercase font-bold tracking-wider">Suma Wkładów</p>
                    <p class="text-2xl font-black text-amber-400 mt-1"><?= number_format($totalAmount, 2, ',', ' ') ?> <span class="text-xs font-normal text-gray-400">PLN</span></p>
                </div>
                <div class="bg-gray-800/40 backdrop-blur-md p-4 rounded-xl border border-emerald-500/20 transition-all duration-200 hover:scale-[1.02] hover:bg-emerald-500/[0.02]">
                    <p class="text-[11px] text-emerald-400 uppercase font-bold tracking-wider">Łączny Zysk Netto</p>
                    <p class="text-2xl font-black text-emerald-400 mt-1"><?= number_format($totalProfit, 2, ',', ' ') ?> <span class="text-xs font-normal text-emerald-500">PLN</span></p>
                </div>
                <div class="bg-gray-800/40 backdrop-blur-md p-4 rounded-xl border border-gray-700/60 transition-all duration-200 hover:scale-[1.02] hover:border-gray-600">
                    <p class="text-[11px] text-gray-400 uppercase font-bold tracking-wider">Statystyki Ogólne</p>
                    <div class="flex items-center gap-2 mt-1 text-sm font-bold">
                        <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/10"><?= $winsCount ?>W</span>
                        <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/10"><?= $remisCount ?>R</span>
                        <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/10"><?= $lossesCount ?>L</span>
                    </div>
                </div>
                <div class="bg-gray-800/40 backdrop-blur-md p-4 rounded-xl border border-amber-500/20 col-span-1 sm:col-span-2 lg:col-span-1 transition-all duration-200 hover:scale-[1.02]">
                    <p class="text-[11px] text-amber-400 uppercase font-bold tracking-wider mb-1">Najbliższe Unbany (Top 3)</p>
                    <div id="top-shortest-ban" class="text-xs font-bold text-gray-200 space-y-1">
                        Obliczanie...
                    </div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="bg-gradient-to-b from-gray-800 to-gray-900 p-6 rounded-2xl shadow-xl border border-gray-800 flex flex-col justify-between">
                <div>
                    <h2 class="text-xl font-bold mb-5 text-gray-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Menedżer Formularza
                    </h2>
                    
                    <form id="account-form" action="process.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nazwa konta</label>
                            <input type="text" name="name" required class="w-full bg-gray-950/60 border border-gray-700 rounded-xl p-3 text-white placeholder-gray-500 focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:outline-none transition-all" placeholder="np. CS-Konto-01">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Link do profilu Steam</label>
                            <input type="url" name="steam_url" class="w-full bg-gray-950/60 border border-gray-700 rounded-xl p-3 text-white placeholder-gray-500 focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:outline-none transition-all" placeholder="https://steamcommunity.com/profiles/7656119...">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Kwota (Wkład)</label>
                                <input type="number" id="input-amount" name="amount" step="0.01" required class="w-full bg-gray-950/60 border border-gray-700 rounded-xl p-3 text-white focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Koniec (Finał)</label>
                                <input type="number" id="input-end" name="end_amount" step="0.01" required class="w-full bg-gray-950/60 border border-gray-700 rounded-xl p-3 text-white focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:outline-none transition-all">
                            </div>
                        </div>
                        
                        <div class="bg-gray-950/40 p-3 rounded-xl border border-gray-800 text-xs text-gray-400 flex justify-between items-center">
                            <span>Zysk z sesji (Live):</span>
                            <span id="live-profit" class="font-bold text-gray-300 bg-gray-950 px-2.5 py-1 rounded-md border border-gray-800">0,00 PLN</span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Wynik Sesji</label>
                            <select name="result" class="w-full bg-gray-950/60 border border-gray-700 rounded-xl p-3 text-white focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:outline-none transition-all cursor-pointer">
                                <option value="win">🟢 WIN (Wygrana)</option>
                                <option value="remis">🟡 REMIS (Remis)</option>
                                <option value="lose">🔴 LOSE (Przegrana)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Modyfikator Blokady (Ban)</label>
                            <select name="ban_days" id="ban-days-select" class="w-full bg-gray-950/60 border border-gray-700 rounded-xl p-3 text-white focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 focus:outline-none transition-all cursor-pointer" onchange="toggleCustomDateInput()">
                                <option value="0">Brak aktywnej blokady</option>
                                <option value="7">🔒 Blokada turniejowa (7 dni)</option>
                                <option value="8">🔒 Blokada komercyjna (8 dni)</option>
                                <option value="30">🔒 Blokada pełna (30 dni)</option>
                                <option value="custom">📅 Definiuj własny czas unbana...</option>
                            </select>
                        </div>

                        <div id="custom-date-container" class="hidden bg-gray-950/50 p-3 rounded-xl border border-amber-500/20 space-y-2 animate-fadeIn">
                            <label class="block text-[11px] font-bold text-amber-400 uppercase tracking-wider">Dokładny moment zakończenia:</label>
                            <input type="datetime-local" id="custom-date-input" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        </div>
                    </form>
                </div>

                <div class="pt-4">
                    <button type="submit" form="account-form" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-gray-950 font-bold p-3.5 rounded-xl transition duration-200 shadow-lg shadow-emerald-950/20 active:scale-[0.99] cursor-pointer text-center">
                        Zapisz zmiany w bazie
                    </button>
                </div>
            </div>

            <div class="lg:col-span-2 bg-gradient-to-b from-gray-800 to-gray-900 p-6 rounded-2xl shadow-xl border border-gray-800 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-100 flex items-center gap-2">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                        Wykres Efektywności Finansowej
                    </h2>
                    <span class="text-[10px] bg-gray-700/50 text-gray-300 px-2 py-1 rounded font-mono uppercase">Netto PLN</span>
                </div>
                <div class="relative w-full h-72 md:h-full min-h-[300px]">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-2xl shadow-2xl border border-gray-800 overflow-hidden">
            <div class="p-5 bg-gradient-to-r from-gray-900 to-gray-850 border-b border-gray-800 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    Szczegółowa Ewidencja Kont
                </h2>
                <span class="text-xs font-semibold px-2.5 py-1 bg-gray-800 border border-gray-700 rounded-full text-gray-400">SQL Database</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-950/50 text-gray-400 text-[11px] uppercase tracking-wider font-bold border-b border-gray-800">
                            <th class="p-4">Nazwa profilu</th>
                            <th class="p-4">Depozyt wejściowy</th>
                            <th class="p-4">Stan Końcowy</th>
                            <th class="p-4">Zysk Skumulowany</th>
                            <th class="p-4">Log historii zmian</th>
                            <th class="p-4">Bieżący Status / Blokada</th>
                            <th class="p-4 text-right">Zarządzanie</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/60 bg-gray-900/20">
                        <?php if (empty($accounts)): ?>
                            <tr>
                                <td colspan="7" class="p-12 text-center text-gray-500 font-medium">
                                    <p class="text-base mb-1">Brak rekordów w strukturze PostgreSQL</p>
                                    <p class="text-xs text-gray-600">Użyj bocznego formularza w celu wykonania operacji INSERT.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($accounts as $index => $acc): ?>
                                <tr class="hover:bg-gray-800/30 transition-all duration-150">
                                    <td class="p-4 font-semibold text-gray-100 tracking-wide">
                                        <?php if (!empty($acc['steam_url'])): ?>
                                            <a href="<?= htmlspecialchars($acc['steam_url']) ?>" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-400 hover:underline transition-colors" title="Otwórz profil Steam w nowej karcie">
                                                <?= htmlspecialchars($acc['name']) ?>
                                            </a>
                                        <?php else: ?>
                                            <?= htmlspecialchars($acc['name']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-gray-400 font-mono text-sm"><?= number_format($acc['amount'], 2, ',', ' ') ?></td>
                                    <td class="p-4 text-amber-400 font-semibold font-mono text-sm"><?= number_format($acc['end_amount'], 2, ',', ' ') ?></td>
                                    <td class="p-4 font-mono text-sm <?= $acc['profit'] >= 0 ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold' ?>">
                                        <?= ($acc['profit'] >= 0 ? '+' : '') . number_format($acc['profit'], 2, ',', ' ') ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-wrap gap-1 max-w-[180px]">
                                            <?php 
                                            $history = explode(',', $acc['result']);
                                            foreach ($history as $hItem): 
                                                $hItem = trim($hItem);
                                                if ($hItem === 'win'): ?>
                                                    <span class="px-1.5 py-0.5 text-[9px] font-black bg-emerald-500/10 text-emerald-400 rounded border border-emerald-500/20">W</span>
                                                <?php elseif ($hItem === 'remis'): ?>
                                                    <span class="px-1.5 py-0.5 text-[9px] font-black bg-amber-500/10 text-amber-400 rounded border border-amber-500/20">R</span>
                                                <?php elseif ($hItem === 'lose'): ?>
                                                    <span class="px-1.5 py-0.5 text-[9px] font-black bg-rose-500/10 text-rose-400 rounded border border-rose-500/20">L</span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <?php if ($acc['ban_days'] > 0 && $acc['ban_start_at']): ?>
                                            <?php 
                                            $startTimeMs = strtotime($acc['ban_start_at']) * 1000;
                                            $banDays = $acc['ban_days'];
                                            ?>
                                            <span id="timer-<?= $index ?>" class="ban-countdown px-2.5 py-1 text-[11px] font-mono bg-rose-500/10 text-rose-400 rounded-lg border border-rose-500/20 inline-block shadow-inner" 
                                                  data-start="<?= $startTimeMs ?>" 
                                                  data-days="<?= $banDays ?>"
                                                  data-name="<?= htmlspecialchars($acc['name']) ?>">
                                                Przeliczanie...
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 text-[11px] font-medium bg-emerald-500/10 text-emerald-400 rounded-lg border border-emerald-500/20 inline-flex items-center gap-1">
                                                <span class="w-1 h-1 rounded-full bg-emerald-400"></span> Operacyjne
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-right">
                                        <a href="process.php?action=delete&id=<?= $acc['id'] ?>" onclick="return confirm('Autoryzacja usunięcia profilu: <?= htmlspecialchars($acc['name']) ?>?')" class="text-rose-400 hover:text-rose-300 font-bold text-xs bg-rose-500/5 hover:bg-rose-500/10 px-3 py-1.5 rounded-lg border border-rose-500/10 transition-all">Usuń</a>
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
        function toggleCustomDateInput() {
            const select = document.getElementById('ban-days-select');
            const container = document.getElementById('custom-date-container');
            const input = document.getElementById('custom-date-input');
            
            if (select.value === 'custom') {
                container.classList.remove('hidden');
                input.setAttribute('required', 'required');
            } else {
                container.classList.add('hidden');
                input.removeAttribute('required');
            }
        }

        document.getElementById('account-form').addEventListener('submit', function(e) {
            const select = document.getElementById('ban-days-select');
            if (select.value === 'custom') {
                const customDateVal = document.getElementById('custom-date-input').value;
                if (!customDateVal) {
                    e.preventDefault();
                    alert('Podaj datę unbana!');
                    return;
                }

                const targetDate = new Date(customDateVal);
                const now = new Date();
                const diffMs = targetDate - now;
                const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

                if (diffDays <= 0) {
                    e.preventDefault();
                    alert('Data musi być w przyszłości!');
                    return;
                }

                select.options[select.selectedIndex].value = diffDays;
            }
        });

        const inputAmount = document.getElementById('input-amount');
        const inputEnd = document.getElementById('input-end');
        const liveProfit = document.getElementById('live-profit');

        function calculateLiveProfit() {
            const amount = parseFloat(inputAmount.value) || 0;
            const end = parseFloat(inputEnd.value) || 0;
            const profit = end - amount;
            
            liveProfit.innerText = (profit >= 0 ? '+' : '') + profit.toFixed(2).replace('.', ',') + ' PLN';
            
            if(profit >= 0) {
                liveProfit.className = "font-mono font-bold text-emerald-400 bg-emerald-500/5 px-2.5 py-1 rounded-md border border-emerald-500/20";
            } else {
                liveProfit.className = "font-mono font-bold text-rose-400 bg-rose-500/5 px-2.5 py-1 rounded-md border border-rose-500/20";
            }
        }
        inputAmount.addEventListener('input', calculateLiveProfit);
        inputEnd.addEventListener('input', calculateLiveProfit);

        function updateBanTimers() {
            const now = Date.now();
            let bannedAccounts = [];
            
            // Zbieramy dane o wszystkich zbanowanych kontach z tabeli
            document.querySelectorAll('.ban-countdown').forEach((timerEl) => {
                const startTime = parseInt(timerEl.getAttribute('data-start'));
                const banDays = parseInt(timerEl.getAttribute('data-days'));
                const accName = timerEl.getAttribute('data-name');
                const endTime = startTime + (banDays * 24 * 60 * 60 * 1000);
                const timeLeft = endTime - now;

                if (timeLeft <= 0) {
                    timerEl.innerHTML = "Blokada zwolniona";
                    timerEl.className = "px-2.5 py-1 text-[11px] font-medium bg-amber-500/10 text-amber-400 rounded-lg border border-amber-500/20";
                } else {
                    const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                    let label = "";
                    if (days > 0) label += `${days}d `;
                    label += `${hours}h ${minutes}m ${seconds}s`;

                    timerEl.innerHTML = `🔒 ${label}`;

                    // Dodajemy konto do tablicy obiektów w celu wyłonienia TOP 3 najbliższych unbanów
                    bannedAccounts.push({
                        name: accName,
                        timeLeft: timeLeft,
                        formattedString: `${accName}: ${days > 0 ? days + 'd ' : ''}${hours}h ${minutes}m`
                    });
                }
            });

            const topBox = document.getElementById('top-shortest-ban');
            
            if (bannedAccounts.length === 0) {
                topBox.innerHTML = "Pełna dostępność";
                topBox.className = "text-sm font-bold text-emerald-400 mt-1.5";
            } else {
                // Sortujemy od najmniejszego pozostałego czasu unbana
                bannedAccounts.sort((a, b) => a.timeLeft - b.timeLeft);
                
                // Wybieramy maksymalnie 3 najbliższe konta
                const top3 = bannedAccounts.slice(0, 3);
                
                // Generujemy linie tekstu dla kafelka
                let htmlOutput = "";
                top3.forEach((acc) => {
                    htmlOutput += `<div class="truncate text-amber-400">• ${acc.formattedString}</div>`;
                });
                
                topBox.innerHTML = htmlOutput;
                topBox.className = "text-xs font-bold text-gray-200 mt-1.5 space-y-0.5";
            }
        }
        setInterval(updateBanTimers, 1000);
        updateBanTimers();

        // INICJALIZACJA WYKRESU O WYŻSZEY ESTETYCE
        const ctx = document.getElementById('profitChart').getContext('2d');
        const chartLabels = <?php echo json_encode($chartLabels); ?>;
        const chartData = <?php echo json_encode($chartData); ?>;

        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Zysk Netto (PLN)',
                    data: chartData,
                    backgroundColor: gradient,
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(16, 185, 129, 0.6)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleColor: '#9ca3af',
                        bodyColor: '#10b981',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: { color: '#6b7280', font: { family: 'Inter' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#6b7280', font: { family: 'Inter' } }
                    }
                }
            }
        });
    </script>
</body>
</html>
