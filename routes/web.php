<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ArenaController;

// Arahkan Root ke Dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard & Panduan
    Route::get('/dashboard', [ArenaController::class, 'dashboard'])->name('dashboard');
    Route::get('/panduan', [ArenaController::class, 'panduan'])->name('panduan');

    // Profile Routes (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Arena & Debat Routes
    Route::post('/arena/store', [ArenaController::class, 'store'])->name('arena.store');
    Route::get('/arena/{id}', [ArenaController::class, 'show'])->name('arena.show');
    Route::get('/arena/{id}/arguments', [ArenaController::class, 'getArguments'])->name('arena.arguments');
    Route::post('/arena/{id}/start', [ArenaController::class, 'startAi'])->name('arena.start');
    Route::post('/arena/{id}/promote/{userId}', [ArenaController::class, 'promote'])->name('arena.promote');
    
    // Fitur Interaksi (Like & Komentar)
    Route::post('/arena/{id}/argument/{argId}/like', [ArenaController::class, 'toggleLike']);
    Route::post('/arena/{id}/comment', [ArenaController::class, 'storeComment']);
    Route::get('/arena/{id}/comments', [ArenaController::class, 'getComments']);
    Route::delete('/arena/{id}/comment/{commentId}', [ArenaController::class, 'destroyComment']);
    
    // Manajemen Ruangan & Follow-Up AI
    Route::delete('/arena/{id}', [ArenaController::class, 'destroy'])->name('arena.destroy');
    Route::post('/arena/{id}/follow-up', [ArenaController::class, 'followUp'])->name('arena.followup');
});

require __DIR__.'/auth.php';