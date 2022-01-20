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

Route::middleware('checkDBConnection')->group(function() {

    Route::prefix('task')->group(function() {
        Route::post('create', [TasksController::class, 'create']);
        Route::put('edit', [TasksController::class, 'edit']);
        Route::get('get/{id}', [TasksController::class, 'get']);
        Route::get('getAll', [TasksController::class, 'getAll']);
        Route::delete('delete', [TasksController::class, 'delete']);
    });
    Route::prefix('subtask')->group(function() {
        Route::put('create', [SubtasksController::class, 'create']);
        Route::put('edit', [SubtasksController::class, 'edit']);
        Route::put('get', [SubtasksController::class, 'get']);
        Route::put('delete', [SubtasksController::class, 'delete']);
    });

});
