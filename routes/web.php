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
    $scopes = [
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile'
    ];

    $response['url'] = Socialite::driver('google')->scopes($scopes)->redirect()->getTargetUrl();
    return response()->json($response);
})->name('loginRedirect');

Route::get('/auth/callback', function () {
    $student = new Student();
    $student->name = 'Nombre';
    $student->email = 'nombre@gmail.com';
    $student->image_url = 'foto01.png';
    $student->save();

    $response['msg'] = 'Usuario creado correctamente';
    $response['data'] = $student;

    return response()->json($response);
});
