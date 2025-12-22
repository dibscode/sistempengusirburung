<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - ESP32-CAM Monitoring</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white min-h-screen text-slate-900">

<main class="max-w-md mx-auto px-4 pt-4 pb-24">
  <header class="flex items-center gap-3">
    <div class="h-12 w-12 rounded-full border-2 border-blue-600 bg-white"></div>
    <div>
      <div class="text-sm font-semibold text-slate-900">Profile</div>
      <div class="text-xs text-slate-500">Data statis</div>
    </div>
  </header>

  <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
    <div class="text-xs text-slate-500">Nama</div>
    <div class="mt-1 text-base font-semibold text-slate-900">(Isi nama di sini)</div>

    <div class="mt-4 grid grid-cols-2 gap-3">
      <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4">
        <div class="text-xs text-slate-500">Primary</div>
        <div class="mt-1 text-sm font-semibold text-blue-700">Blue</div>
      </div>
      <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
        <div class="text-xs text-slate-500">Secondary</div>
        <div class="mt-1 text-sm font-semibold text-orange-600">Orange</div>
      </div>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
      <div class="text-xs text-slate-500">Catatan</div>
      <div class="mt-1 text-sm text-slate-700">
        Halaman ini statis. Silakan ubah kontennya sesuai kebutuhan.
      </div>
    </div>
  </section>
</main>

<nav class="fixed bottom-0 left-0 right-0 border-t border-slate-200 bg-white">
  <div class="mx-auto max-w-md px-3">
    <div class="grid grid-cols-4 py-2">
      <a href="index.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-slate-500 hover:text-blue-700">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 10.5 12 3l9 7.5" />
          <path d="M5 10v10h14V10" />
          <path d="M9 20v-7h6v7" />
        </svg>
        <div class="text-[11px] font-medium">Home</div>
      </a>

      <a href="auto.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-slate-500 hover:text-blue-700">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 8v4l3 2" />
          <circle cx="12" cy="12" r="9" />
        </svg>
        <div class="text-[11px] font-medium">Auto</div>
      </a>

      <a href="manual.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-slate-500 hover:text-orange-600">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 7h4l2-2h4l2 2h4v12H4z" />
          <circle cx="12" cy="13" r="3" />
        </svg>
        <div class="text-[11px] font-medium">Manual</div>
      </a>

      <a href="profile.php" class="flex flex-col items-center justify-center gap-1 rounded-xl px-2 py-2 text-blue-700">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21a8 8 0 0 0-16 0" />
          <circle cx="12" cy="8" r="4" />
        </svg>
        <div class="text-[11px] font-medium">Profile</div>
      </a>
    </div>
  </div>
</nav>

</body>
</html>
