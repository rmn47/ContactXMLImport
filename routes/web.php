<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get('/', function () {
    return redirect('/contacts');
});

// Contact routes
Route::resource('contacts', ContactController::class);
Route::get('/contacts-import', [ContactController::class, 'importForm'])->name('contacts.import.form');
Route::post('/contacts-import', [ContactController::class, 'import'])->name('contacts.import');
Route::get('/contacts-export', [ContactController::class, 'export'])->name('contacts.export');
