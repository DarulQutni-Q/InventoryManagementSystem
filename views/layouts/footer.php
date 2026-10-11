<?php
// footer.php menutup <main> dan <div> yang dibuka di header.php,
// lalu menulis satu blok <script> utilitas yang tipis.
// Dipanggil terakhir di setiap halaman, setelah konten halaman selesai.
?>
    </div><!-- /container pembungkus konten -->
</main><!-- /main-content -->
</div><!-- /flex wrapper sidebar+konten -->

<script>
(function () {
    'use strict';

    // Auto-dismiss flash messages setelah 5 detik —
    // user punya cukup waktu baca tapi halaman tidak penuh notifikasi basi
    ['flash-success', 'flash-error'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s ease';
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 420);
        }, 5000);
    });

    // toggle sidebar mobile
    var btn     = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    if (!btn || !sidebar || !overlay) return;

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        // Fokus ke elemen pertama sidebar agar screen reader langsung masuk
        var first = sidebar.querySelector('a');
        if (first) first.focus();
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
        btn.focus(); // kembalikan fokus ke tombol pemicu
    }

    btn.addEventListener('click', function () {
        sidebar.classList.contains('-translate-x-full') ? openSidebar() : closeSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    // Escape menutup sidebar — wajib untuk keyboard-only navigation
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
            closeSidebar();
        }
    });
}());
</script>
</body>
</html>
