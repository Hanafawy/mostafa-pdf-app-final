<?php

use App\Http\Controllers\PdfFileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
Route::get('/pdf/last', [PdfFileController::class, 'latestPdf'])->name('pdf.open');


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
// PDF routes for authenticated users
Route::middleware('auth')
    ->prefix('dashboard/files/pdf')
    ->group(function () {
    Route::get('/',[PdfFileController::class, 'index']);
    Route::post('/', [PdfFileController::class, 'store']);
    Route::get('/{pdf}', [PdfFileController::class, 'show']);
    Route::put('/{pdf}', [PdfFileController::class, 'update']);
    Route::delete('/{pdf}', [PdfFileController::class, 'destroy']);
    Route::post('/bulk-update', [PdfFileController::class, 'bulkUpdate'])->name('pdf.bulk-update');
    Route::post('/bulk-delete', [PdfFileController::class, 'bulkDelete'])->name('pdf.bulk-delete');
});
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
