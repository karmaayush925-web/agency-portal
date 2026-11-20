<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\InvoicePaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('invoices/{invoice}/checkout', [InvoicePaymentController::class, 'checkout'])
        ->name('invoices.checkout');
    Route::post('invoices/{invoice}/checkout', [InvoicePaymentController::class, 'process'])
        ->name('invoices.checkout.process');
        
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
    Route::patch('invoices/{invoice}/pay', [InvoiceController::class, 'markAsPaid'])
        ->middleware('role:admin,manager')
        ->name('invoices.pay');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.pdf');
});

Route::resource('clients', ClientController::class)
        ->except(['edit', 'update'])
        ->middleware('role:admin,manager');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::patch('projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status');
        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

require __DIR__.'/auth.php';
