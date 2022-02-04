<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('login', function () {
    return redirect()->route('loginRedirect');
});

Route::get('/auth/redirect', function () {
    $response['url'] = Socialite::driver('google')->redirect()->getTargetUrl();
    return response()->json($response);
})->name('loginRedirect');

Route::get('/auth/callback', function () {
    $student = new Student();
    $student->name = 'Nombre';
    $student->email = 'nombre@gmail.com';
    $student->image_url = 'foto01.png';
    $student->save();

    return response()->json($student);
});
