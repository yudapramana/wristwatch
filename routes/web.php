<?php

use App\Http\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Vue Router Catch-All (HARUS PALING BAWAH)
|--------------------------------------------------------------------------
*/
Route::get('{view}', [ApplicationController::class, '__invoke']) // <-- Pastikan diarahkan ke method, jangan closure
    ->where('view', '^(?!api|storage|build|assets|_debugbar|secure).*$');