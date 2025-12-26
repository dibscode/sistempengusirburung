<?php
// $conn = new mysqli("localhost", "root", "", "esp32cam");
$conn = new mysqli("localhost", "dibscode", "Bh#DD|8X7wk+", "dibscode_deteksiburung");

$totalFoto = 0;
$totalOtomatis = 0;
$totalManual = 0;

if (!$conn->connect_error) {
  $qTotal = $conn->query("SELECT COUNT(*) AS c FROM foto");
  if ($qTotal) {
    $totalFoto = (int)($qTotal->fetch_assoc()['c'] ?? 0);
  }

  $qAuto = $conn->query("SELECT COUNT(*) AS c FROM foto WHERE jenis='otomatis'");
  if ($qAuto) {
    $totalOtomatis = (int)($qAuto->fetch_assoc()['c'] ?? 0);
  }

  $qManual = $conn->query("SELECT COUNT(*) AS c FROM foto WHERE jenis='manual'");
  if ($qManual) {
    $totalManual = (int)($qManual->fetch_assoc()['c'] ?? 0);
  }
}

$status = 0;
$kontrolRow = null;
if (!$conn->connect_error) {
  $kontrolRow = $conn->query("SELECT status FROM kontrol WHERE id=1");
  if ($kontrolRow) {
    $kontrol = $kontrolRow->fetch_assoc();
    $status = (int)($kontrol['status'] ?? 0);
  }
}

// Statistik chart (foto per waktu)
date_default_timezone_set("Asia/Jakarta");

$chartSeries = [
  'day' => ['labels' => [], 'data' => []],
  'week' => ['labels' => [], 'data' => []],
  'month' => ['labels' => [], 'data' => []],
];

// Harian: 7 hari terakhir (termasuk hari ini)
$dayKeys = [];
$today = new DateTimeImmutable('today');
for ($i = 6; $i >= 0; $i--) {
  $d = $today->sub(new DateInterval('P' . $i . 'D'));
  $key = $d->format('Y-m-d');
  $dayKeys[] = $key;
  $chartSeries['day']['labels'][] = $d->format('d/m');
  $chartSeries['day']['data'][] = 0;
}

// Mingguan: 8 minggu terakhir (ISO week)
$weekKeys = [];
$mondayThisWeek = new DateTimeImmutable('monday this week');
for ($i = 7; $i >= 0; $i--) {
  $monday = $mondayThisWeek->sub(new DateInterval('P' . ($i * 7) . 'D'));
  $isoYear = $monday->format('o');
  $isoWeek = $monday->format('W');
  $key = $isoYear . '-W' . $isoWeek;
  $weekKeys[] = $key;
  $chartSeries['week']['labels'][] = 'W' . $isoWeek;
  $chartSeries['week']['data'][] = 0;
}

// Bulanan: 12 bulan terakhir
$monthKeys = [];
$firstThisMonth = new DateTimeImmutable('first day of this month');
for ($i = 11; $i >= 0; $i--) {
  $m = $firstThisMonth->sub(new DateInterval('P' . $i . 'M'));
  $key = $m->format('Y-m');
  $monthKeys[] = $key;
  $chartSeries['month']['labels'][] = $m->format('m/Y');
  $chartSeries['month']['data'][] = 0;
}

if (!$conn->connect_error) {
  // Harian
  $qDay = $conn->query("SELECT DATE(waktu) AS d, COUNT(*) AS c FROM foto WHERE waktu >= (CURDATE() - INTERVAL 6 DAY) GROUP BY DATE(waktu) ORDER BY d");
  if ($qDay) {
    $map = [];
    while ($r = $qDay->fetch_assoc()) {
      $k = (string)($r['d'] ?? '');
      if ($k !== '') {
        $map[$k] = (int)($r['c'] ?? 0);
      }
    }
    foreach ($dayKeys as $idx => $k) {
      $chartSeries['day']['data'][$idx] = (int)($map[$k] ?? 0);
    }
  }

  // Mingguan (mode 3 = ISO)
  $qWeek = $conn->query("SELECT YEARWEEK(waktu, 3) AS yw, COUNT(*) AS c FROM foto WHERE waktu >= (CURDATE() - INTERVAL 7 WEEK) GROUP BY YEARWEEK(waktu, 3) ORDER BY yw");
  if ($qWeek) {
    $map = [];
    while ($r = $qWeek->fetch_assoc()) {
      $yw = (string)($r['yw'] ?? '');
      if (preg_match('/^\\d{6}$/', $yw)) {
        $year = substr($yw, 0, 4);
        $week = substr($yw, 4, 2);
        $key = $year . '-W' . $week;
        $map[$key] = (int)($r['c'] ?? 0);
      }
    }
    foreach ($weekKeys as $idx => $k) {
      $chartSeries['week']['data'][$idx] = (int)($map[$k] ?? 0);
    }
  }

  // Bulanan
  $qMonth = $conn->query("SELECT DATE_FORMAT(waktu, '%Y-%m') AS ym, COUNT(*) AS c FROM foto WHERE waktu >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01') GROUP BY DATE_FORMAT(waktu, '%Y-%m') ORDER BY ym");
  if ($qMonth) {
    $map = [];
    while ($r = $qMonth->fetch_assoc()) {
      $k = (string)($r['ym'] ?? '');
      if ($k !== '') {
        $map[$k] = (int)($r['c'] ?? 0);
      }
    }
    foreach ($monthKeys as $idx => $k) {
      $chartSeries['month']['data'][$idx] = (int)($map[$k] ?? 0);
    }
  }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Monitoring Pengusir Burung</title>
  <link rel="icon" type="image/png" href="https://upload.wikimedia.org/wikipedia/commons/6/65/Lambang_Resmi_UMJ.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <!-- Auto refresh 30 detik -->
  <script>
    setTimeout(() => {
      window.location.reload();
    }, 30000);
  </script>
</head>

<body class="bg-white min-h-screen text-slate-900">

<main class="max-w-md mx-auto px-4 pt-4 pb-24">
  <!-- Header (sesuai sketsa: icon kiri + nama proyek) -->
  <header class="flex items-center gap-3">
    <div class="h-12 w-12 rounded-xl border-2 border-blue-600 bg-white">
      <img src="https://upload.wikimedia.org/wikipedia/commons/6/65/Lambang_Resmi_UMJ.png" alt="Camera Icon" class="h-full w-full object-contain p-2">
    </div>
    <div>
      <div class="text-sm font-semibold text-slate-900">Sistem Pengusir Burung</div>
      <div class="text-xs text-slate-500">Smart Monitoring System</div>
    </div>
  </header>

  <!-- Card judul + deskripsi -->
  <!-- <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
    <div class="text-base font-semibold text-slate-900">Monitoring Foto</div>
    <div class="mt-1 text-xs text-slate-500">Foto otomatis diambil setiap 30 detik dan disimpan ke server</div>
  </section> -->

  <!-- Grid statistik (2 kolom) -->
  <section class="mt-4 grid grid-cols-2 gap-3">
   
    <!-- Total Foto -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500">Total Foto</div>
      <div class="mt-2 text-2xl font-semibold text-slate-900"><?= $totalFoto ?></div>
      <div class="mt-1 text-xs text-slate-500">Semua jenis</div>
    </div>

     <!-- ON/OFF -->
    <div
      id="soundCard"
      role="button"
      tabindex="0"
      onclick="toggleControl()"
      onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();toggleControl();}"
      aria-label="<?= $status === 1 ? 'Sound ON' : 'Sound OFF' ?>"
      class="rounded-2xl border p-4 cursor-pointer select-none transition-colors text-white
      <?= $status === 1 ? 'border-blue-700 bg-blue-600 hover:bg-blue-700' : 'border-orange-600 bg-orange-500 hover:bg-orange-600' ?>"
    >
      <div id="soundLabel" class="text-xs">Sound</div>
      <div class="mt-3 flex flex-col items-center justify-center">
        <span id="toggleLabel" class="sr-only"><?= $status === 1 ? 'Sound ON' : 'Sound OFF' ?></span>

        <!-- Icon ON -->
        <svg id="iconOn" class="h-7 w-7 <?= $status === 1 ? '' : 'hidden' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M12 2v10" />
          <path d="M7.5 4.8a8 8 0 1 0 9 0" />
        </svg>

        <!-- Icon OFF -->
        <svg id="iconOff" class="h-7 w-7 <?= $status === 0 ? '' : 'hidden' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M12 2v10" />
          <path d="M7.5 4.8a8 8 0 1 0 9 0" />
          <path d="M4 20 20 4" />
        </svg>

        <div id="stateText" class="mt-2 text-xs font-semibold"><?= $status === 1 ? 'Nyala' : 'Mati' ?></div>
      </div>
    </div>

    <!-- Total Auto -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500">Total Auto</div>
      <div class="mt-2 text-2xl font-semibold text-blue-700"><?= $totalOtomatis ?></div>
      <div class="mt-1 text-xs text-slate-500">jenis = otomatis</div>
    </div>

    <!-- Total Manual -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500">Total Manual</div>
      <div class="mt-2 text-2xl font-semibold text-orange-600"><?= $totalManual ?></div>
      <div class="mt-1 text-xs text-slate-500">jenis = manual</div>
    </div>
  </section>

  <!-- Statistik Line Chart -->
  <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-sm font-semibold text-slate-900">Statistik Foto</div>
        <div class="mt-1 text-xs text-slate-500">Filter: Hari / Minggu / Bulan</div>
      </div>
      <div class="inline-flex gap-2">
        <button id="filterDay" type="button" class="rounded-xl px-3 py-2 text-xs font-semibold bg-blue-600 text-white hover:bg-blue-700">Hari</button>
        <button id="filterWeek" type="button" class="rounded-xl px-3 py-2 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">Minggu</button>
        <button id="filterMonth" type="button" class="rounded-xl px-3 py-2 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">Bulan</button>
      </div>
    </div>

    <span id="chartColor" class="hidden text-blue-600"></span>
    <span id="chartGrid" class="hidden text-slate-200"></span>

    <div class="mt-4 h-44">
      <canvas id="statsChart"></canvas>
    </div>
  </section>

  <!-- Tombol foto manual -->
  <section class="mt-4">
    <form action="set_capture.php" method="post">
      <button class="w-full rounded-2xl bg-blue-600 px-4 py-4 text-sm font-semibold text-white hover:bg-blue-700 transition">
        Foto Manual
      </button>
    </form>
    <div class="mt-2 text-[11px] text-slate-500">Men-set kontrol capture=1 (server akan mengambil foto manual)</div>
  </section>
</main>

<!-- Bottom Navbar (mobile-first) -->
<nav class="fixed bottom-0 left-0 right-0 border-t border-slate-200 bg-white">
  <div class="mx-auto max-w-md px-3">
    <div class="grid grid-cols-3 py-2">
      <a href="index.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-blue-700">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path fill-rule="evenodd" d="M11.47 3.84a1 1 0 0 1 1.06 0l8.5 5.5a1 1 0 0 1-.54 1.84H20v8a2 2 0 0 1-2 2h-4a1 1 0 0 1-1-1v-5H11v5a1 1 0 0 1-1 1H6a2 2 0 0 1-2-2v-8h-.49a1 1 0 0 1-.54-1.84l8.5-5.5Z" clip-rule="evenodd" />
        </svg>
        <div class="text-[11px] font-medium">Home</div>
      </a>

      <a href="auto.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-slate-500 hover:text-blue-700">
        <div class="relative">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 8v4l3 2" />
            <circle cx="12" cy="12" r="9" />
          </svg>
          <span class="absolute -right-2 -top-2 min-w-5 rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold text-white text-center">
            <?= $totalOtomatis ?>
          </span>
        </div>
        <div class="text-[11px] font-medium">Auto</div>
      </a>

      <a href="manual.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-slate-500 hover:text-orange-600">
        <div class="relative">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 7h4l2-2h4l2 2h4v12H4z" />
            <circle cx="12" cy="13" r="3" />
          </svg>
          <span class="absolute -right-2 -top-2 min-w-5 rounded-full bg-orange-500 px-1.5 py-0.5 text-[10px] font-semibold text-white text-center">
            <?= $totalManual ?>
          </span>
        </div>
        <div class="text-[11px] font-medium">Manual</div>
      </a>
    </div>
  </div>
</nav>

<script>
const series = <?= json_encode($chartSeries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

let status = <?= $status ?>; // ambil status dari PHP

// Chart
const dayBtn = document.getElementById('filterDay');
const weekBtn = document.getElementById('filterWeek');
const monthBtn = document.getElementById('filterMonth');

const chartColor = getComputedStyle(document.getElementById('chartColor')).color;
const gridColor = getComputedStyle(document.getElementById('chartGrid')).color;

const ctx = document.getElementById('statsChart');
let currentFilter = 'day';

const statsChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: series[currentFilter].labels,
    datasets: [
      {
        label: 'Foto',
        data: series[currentFilter].data,
        borderColor: chartColor,
        backgroundColor: chartColor,
        borderWidth: 2,
        pointRadius: 2,
        pointHoverRadius: 3,
        tension: 0.35,
        fill: false,
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { intersect: false, mode: 'index' },
    },
    scales: {
      x: {
        grid: { display: false },
        ticks: { color: '#64748b' },
      },
      y: {
        beginAtZero: true,
        grid: { color: gridColor },
        ticks: { precision: 0, color: '#64748b' },
      }
    }
  }
});

function setFilter(next) {
  currentFilter = next;
  statsChart.data.labels = series[next].labels;
  statsChart.data.datasets[0].data = series[next].data;
  statsChart.update();

  const active = 'bg-blue-600 text-white hover:bg-blue-700';
  const inactive = 'bg-blue-50 text-blue-700 hover:bg-blue-100';

  dayBtn.className = 'rounded-xl px-3 py-2 text-xs font-semibold ' + (next === 'day' ? active : inactive);
  weekBtn.className = 'rounded-xl px-3 py-2 text-xs font-semibold ' + (next === 'week' ? active : inactive);
  monthBtn.className = 'rounded-xl px-3 py-2 text-xs font-semibold ' + (next === 'month' ? active : inactive);
}

dayBtn.addEventListener('click', () => setFilter('day'));
weekBtn.addEventListener('click', () => setFilter('week'));
monthBtn.addEventListener('click', () => setFilter('month'));

function toggleControl() {
  const card = document.getElementById("soundCard");
  const label = document.getElementById("soundLabel");
  const a11yLabel = document.getElementById("toggleLabel");
  const iconOn = document.getElementById("iconOn");
  const iconOff = document.getElementById("iconOff");
  const stateText = document.getElementById("stateText");

  // toggle nilai
  status = status === 1 ? 0 : 1;

  // ubah tampilan tombol
  if (status === 1) {
    card.setAttribute("aria-label", "Sound ON");
    a11yLabel.textContent = "Sound ON";
    iconOff.classList.add("hidden");
    iconOn.classList.remove("hidden");
    stateText.textContent = "Nyala";

    card.classList.remove("border-orange-600", "bg-orange-500", "hover:bg-orange-600");
    card.classList.add("border-blue-700", "bg-blue-600", "hover:bg-blue-700");
  } else {
    card.setAttribute("aria-label", "Sound OFF");
    a11yLabel.textContent = "Sound OFF";
    iconOn.classList.add("hidden");
    iconOff.classList.remove("hidden");
    stateText.textContent = "Mati";

    card.classList.remove("border-blue-700", "bg-blue-600", "hover:bg-blue-700");
    card.classList.add("border-orange-600", "bg-orange-500", "hover:bg-orange-600");
  }

  // kirim status ke server
  fetch("toggle.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "status=" + status
  });
}
</script>




</body>
</html>
