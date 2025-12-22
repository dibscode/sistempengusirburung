<?php
$conn = new mysqli("localhost", "root", "", "esp32cam");

$totalFoto = 0;
$totalOtomatis = 0;
$totalManual = 0;
$rows = [];

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

  $q = $conn->query("SELECT id, filename, waktu FROM foto WHERE jenis='manual' ORDER BY id DESC LIMIT 60");
  if ($q) {
    while ($r = $q->fetch_assoc()) {
      $rows[] = $r;
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
  <link rel="icon" type="image/png" href="https://nurulfikri.ac.id/wp-content/uploads/2022/06/Main-Logo-STTNF.png">
  <script src="https://cdn.tailwindcss.com"></script>

  <script>
    setTimeout(() => {
      window.location.reload();
    }, 30000);
  </script>
</head>
<body class="bg-white min-h-screen text-slate-900">

<main class="max-w-md mx-auto px-4 pt-4 pb-24">
  <header class="flex items-center justify-between">
    <div>
      <div class="text-sm font-semibold text-slate-900">Gambar Manual</div>
      <div class="mt-1 text-xs text-slate-500">Menampilkan foto dengan jenis = manual</div>
    </div>
    <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-right">
      <div class="text-[11px] text-slate-500">Total</div>
      <div class="text-lg font-semibold text-orange-600"><?= $totalManual ?></div>
    </div>
  </header>

  <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
    <div class="text-xs text-slate-500">Ringkasan</div>
    <div class="mt-2 grid grid-cols-3 gap-2">
      <div class="rounded-xl bg-slate-50 p-3">
        <div class="text-[11px] text-slate-500">Total</div>
        <div class="mt-1 text-sm font-semibold text-slate-900"><?= $totalFoto ?></div>
      </div>
      <div class="rounded-xl bg-blue-50 p-3">
        <div class="text-[11px] text-slate-500">Auto</div>
        <div class="mt-1 text-sm font-semibold text-blue-700"><?= $totalOtomatis ?></div>
      </div>
      <div class="rounded-xl bg-orange-50 p-3">
        <div class="text-[11px] text-slate-500">Manual</div>
        <div class="mt-1 text-sm font-semibold text-orange-600"><?= $totalManual ?></div>
      </div>
    </div>
  </section>

  <section class="mt-4">
    <div class="flex items-center justify-between">
      <div class="text-sm font-semibold text-slate-900">Foto Terbaru</div>
      <div class="text-[11px] text-slate-500">Max 60</div>
    </div>

    <?php if (count($rows) === 0) { ?>
      <div class="mt-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-500">
        Belum ada data foto manual.
      </div>
    <?php } else { ?>
      <div class="mt-3 grid grid-cols-2 gap-3">
        <?php foreach ($rows as $r) { ?>
          <div class="group relative rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <button
              type="button"
              class="absolute right-2 top-2 z-10 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-white/90 text-slate-700 hover:bg-white"
              aria-label="Hapus foto"
              onclick="event.stopPropagation(); openDeleteConfirm(<?= (int)$r['id'] ?>)"
            >
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 6h18" />
                <path d="M8 6V4h8v2" />
                <path d="M19 6l-1 14H6L5 6" />
                <path d="M10 11v6" />
                <path d="M14 11v6" />
              </svg>
            </button>

            <button
              type="button"
              class="block w-full text-left"
              onclick="openPreview('<?= htmlspecialchars($r['filename']) ?>')"
              aria-label="Lihat foto"
            >
              <img src="uploads/<?= htmlspecialchars($r['filename']) ?>" class="h-32 w-full object-cover" loading="lazy" alt="Foto manual">
            </button>
            <div class="p-3">
              <div class="text-xs text-slate-500">Waktu</div>
              <div class="mt-1 text-xs font-medium text-slate-900 break-words"><?= htmlspecialchars($r['waktu']) ?></div>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </section>

  <!-- Preview Modal -->
  <div id="previewModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60" onclick="closePreview()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-3">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold text-slate-900">Preview</div>
          <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100" onclick="closePreview()" aria-label="Tutup">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M18 6 6 18" />
              <path d="M6 6l12 12" />
            </svg>
          </button>
        </div>
        <img id="previewImage" src="" class="mt-3 max-h-[70vh] w-full rounded-xl object-contain" alt="Preview">
      </div>
    </div>
  </div>

  <!-- Confirm Delete Modal -->
  <div id="confirmModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/60" onclick="closeDeleteConfirm()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md rounded-2xl bg-white p-4">
        <div class="text-sm font-semibold text-slate-900">Hapus foto?</div>
        <div class="mt-1 text-xs text-slate-500">Aksi ini tidak bisa dibatalkan.</div>
        <div class="mt-4 flex gap-2">
          <button type="button" class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" onclick="closeDeleteConfirm()">Tidak</button>
          <button id="confirmDeleteBtn" type="button" class="flex-1 rounded-xl bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600">Ya, hapus</button>
        </div>
      </div>
    </div>
  </div>
</main>

<nav class="fixed bottom-0 left-0 right-0 border-t border-slate-200 bg-white">
  <div class="mx-auto max-w-md px-3">
    <div class="grid grid-cols-3 py-2">
      <a href="index.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-slate-500 hover:text-blue-700">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 10.5 12 3l9 7.5" />
          <path d="M5 10v10h14V10" />
          <path d="M9 20v-7h6v7" />
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

      <a href="manual.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-orange-600">
        <div class="relative">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M4.5 7.5A2.25 2.25 0 0 0 2.25 9.75v8.25A2.25 2.25 0 0 0 4.5 20.25h15A2.25 2.25 0 0 0 21.75 18V9.75A2.25 2.25 0 0 0 19.5 7.5h-3.086a.75.75 0 0 1-.53-.22l-1.46-1.46A2.25 2.25 0 0 0 12.834 5.25h-1.668a2.25 2.25 0 0 0-1.59.66l-1.46 1.46a.75.75 0 0 1-.53.22H4.5Z" />
            <path d="M12 10.5a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Z" />
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
let deleteId = null;

function openPreview(filename) {
  const modal = document.getElementById('previewModal');
  const img = document.getElementById('previewImage');
  img.src = 'uploads/' + filename;
  modal.classList.remove('hidden');
}

function closePreview() {
  const modal = document.getElementById('previewModal');
  const img = document.getElementById('previewImage');
  img.src = '';
  modal.classList.add('hidden');
}

function openDeleteConfirm(id) {
  deleteId = id;
  const modal = document.getElementById('confirmModal');
  modal.classList.remove('hidden');
}

function closeDeleteConfirm() {
  deleteId = null;
  const modal = document.getElementById('confirmModal');
  modal.classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closePreview();
    closeDeleteConfirm();
  }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
  if (!deleteId) return;

  const body = new URLSearchParams();
  body.set('id', String(deleteId));

  try {
    const res = await fetch('delete_photo.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    });

    const json = await res.json().catch(() => ({}));
    if (!res.ok || !json.ok) {
      alert('Gagal menghapus foto.');
      return;
    }

    closeDeleteConfirm();
    window.location.reload();
  } catch (err) {
    alert('Gagal menghapus foto.');
  }
});
</script>

</body>
</html>
