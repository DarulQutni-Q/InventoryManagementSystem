<?php
/**
 * helpers/escape.php
 * Helper sanitasi output HTML untuk mencegah kerentanan Cross-Site Scripting (XSS).
 *
 * Mengapa ENT_QUOTES dan UTF-8 wajib:
 * - ENT_QUOTES meng-escape tanda kutip ganda (") DAN tanda kutip tunggal ('),
 *   sehingga aman dipakai baik di dalam tag teks maupun nilai atribut HTML (misal value="...").
 * - UTF-8 memastikan multi-byte character tidak di-bypass untuk eksploitasi XSS.
 * - Menerima ?string agar kolom database bernilai NULL tidak melempar deprecation error di PHP 8.1+.
 */

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
