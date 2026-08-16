<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/api/search', [\App\Http\Controllers\DocsController::class, 'search_content'])->name('api.search');

Route::get('/linter-utility', [\App\Http\Controllers\LinterUtilityController::class, 'index'])->name('linter.utility');
Route::post('/linter-utility/generate', [\App\Http\Controllers\LinterUtilityController::class, 'generateYaml'])->name('linter.utility.generate');
Route::post('/linter-utility/parse', [\App\Http\Controllers\LinterUtilityController::class, 'parseYaml'])->name('linter.utility.parse');

// Newsletter (must be registered before the catch-all docs route)
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::post('/newsletter/send-cancel-link', [NewsletterController::class, 'send_cancel_link'])->name('newsletter.send-cancel-link');
Route::get('/newsletter/confirm/{uuid}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::post('/newsletter/confirm/{uuid}', [NewsletterController::class, 'confirm_submit'])->name('newsletter.confirm.submit');
Route::get('/newsletter/cancel/{uuid}', [NewsletterController::class, 'cancel'])->name('newsletter.cancel');
Route::post('/newsletter/unsubscribe/{uuid}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('/{page?}', [DocsController::class, 'show_document'])->where('page', '.*');

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
