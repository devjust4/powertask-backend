<?php

use App\Http\Controllers\BlocksController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\PeriodsController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\SubtasksController;
use App\Http\Controllers\TasksController;
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
    Route::put('toggle/{id}', [TasksController::class, 'toggleCheck']);
});
Route::prefix('subtask')->group(function() {
    Route::post('create/{id}', [SubtasksController::class, 'create']);
    Route::put('edit/{id}', [SubtasksController::class, 'edit']);
    Route::delete('delete/{id}', [SubtasksController::class, 'delete']);
    Route::put('toggle/{id}', [SubtasksController::class, 'toggleCheck']);
});

Route::prefix('period')->group(function() {
    Route::post('create', [PeriodsController::class, 'create']);
    Route::put('edit/{id}', [PeriodsController::class, 'edit']);
    Route::delete('delete/{id}', [PeriodsController::class, 'delete']);
});

Route::prefix('block')->group(function() {
    Route::post('create', [BlocksController::class, 'create']);
    Route::put('edit/{id}', [BlocksController::class, 'edit']);
    Route::delete('delete/{id}', [BlocksController::class, 'delete']);
});

Route::prefix('course')->group(function() {
    Route::post('create', [CoursesController::class, 'create']);
    Route::put('edit/{id}', [CoursesController::class, 'edit']);
    Route::delete('delete/{id}', [CoursesController::class, 'delete']);
    Route::get('list/{id}', [CoursesController::class, 'list']);
});

Route::prefix('session')->group(function() {
    Route::post('create', [SessionsController::class, 'create']);
    Route::delete('delete/{id}', [SessionsController::class, 'delete']);
    Route::get('list/{id}', [SessionsController::class, 'list']);
});

Route::prefix('event')->group(function() {
    Route::post('create', [EventsController::class, 'create']);
    Route::put('edit/{id}', [EventsController::class, 'edit']);
    Route::delete('delete/{id}', [EventsController::class, 'delete']);
    Route::get('getEvents/{id}', [EventsController::class, 'getEvents']);
});

Route::prefix('subject')->group(function() {
    Route::put('edit/{id}', [ClassroomController::class, 'editSubject']);
});



Route::middleware('getUserFromToken')->prefix('auth')->group(function() {
    Route::post('create', [ClassroomController::class, 'create']);
    Route::get('getSubjects', [ClassroomController::class, 'getSubjects']);
});

