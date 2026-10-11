<?php
// main.php adalah orkestrator layout — tidak wajib dipakai langsung.
//
// Pola 1 (paling umum — tiap halaman include sendiri):
//   <?php
//   $page_title = 'Dashboard';
//   require_once ROOT . '/views/layouts/header.php';
//   // ... echo konten halaman ...
//   require_once ROOT . '/views/layouts/footer.php';
//
// Pola 2 (output buffering — data disiapkan dulu baru render):
//   <?php
//   $page_title = 'Produk';
//   ob_start();
//   // ... echo konten ...
//   $content = ob_get_clean();
//   require_once ROOT . '/views/layouts/main.php';
//
// File ini menangani Pola 2: jika $content sudah di-set,
// ia di-echo antara header dan footer.
// Kalau main.php di-include tanpa $content, halaman tetap valid
// — tinggal konten kosong di antara header dan footer.

define('LAYOUTS_DIR', __DIR__);

require_once LAYOUTS_DIR . '/header.php';

if (isset($content)) {
    // $content sudah di-escape oleh masing-masing view —
    // echo langsung di sini, bukan htmlspecialchars lagi
    echo $content;
}

require_once LAYOUTS_DIR . '/footer.php';
