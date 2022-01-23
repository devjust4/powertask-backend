<?php

use App\Http\Controllers\SubtasksController;
use App\Http\Controllers\TasksController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('task')->group(function() {
    Route::post('create', [TasksController::class, 'create']);
    Route::put('edit/{id}', [TasksController::class, 'edit']);
    Route::get('get/{id}', [TasksController::class, 'get']);
    Route::get('list/{id}', [TasksController::class, 'list']);
    Route::delete('delete/{id}', [TasksController::class, 'delete']);
});
Route::prefix('subtask')->group(function() {
    Route::post('create/{id}', [SubtasksController::class, 'create']);
    Route::put('edit/{id}', [SubtasksController::class, 'edit']);
    Route::delete('delete/{id}', [SubtasksController::class, 'delete']);
});
