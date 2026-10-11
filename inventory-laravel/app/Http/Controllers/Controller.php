<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Pastikan hanya Administrator yang memiliki izin untuk tindakan ini.
     */
    protected function authorizeAdmin(): void
    {
        if (! request()->user()?->isAdmin()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Administrator.');
        }
    }
}
