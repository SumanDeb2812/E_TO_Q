<?php

use App\Http\Controllers\CreateController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InsertController;
use App\Http\Middleware\CheckIfFileExists;

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

Route::get('/', [CreateController::class, 'index']);
Route::post('/', [CreateController::class, 'create'])->name('create');
Route::get('/create-query', [CreateController::class, 'viewCreateQuery'])->middleware(CheckIfFileExists::class);
Route::post('/create-query', [CreateController::class, 'createQuery'])->name('create.query');
Route::get('/download', [CreateController::class, 'download'])->name('download');

Route::get('/insert', [InsertController::class, 'index']);
Route::post('/insert', [InsertController::class, 'insert'])->name('insert');
Route::get('/download', [InsertController::class, 'download'])->name('download');
