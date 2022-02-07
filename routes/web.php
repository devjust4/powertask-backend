<?php

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
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
        'https://www.googleapis.com/auth/userinfo.profile',

        'https://www.googleapis.com/auth/classroom.courses.readonly',
        'https://www.googleapis.com/auth/classroom.course-work.readonly',
        'https://www.googleapis.com/auth/classroom.student-submissions.me.readonly',
        'https://www.googleapis.com/auth/classroom.announcements.readonly',
        'https://www.googleapis.com/auth/classroom.courseworkmaterials.readonly',
        'https://www.googleapis.com/auth/classroom.topics.readonly',
        'https://www.googleapis.com/auth/classroom.rosters.readonly',
    ];

    $response['url'] = Socialite::driver('google')->scopes($scopes)->redirect()->getTargetUrl();
    return response()->json($response);
})->name('loginRedirect');

Route::get('/auth/callback', function (Request $request) {
    $response['msg'] = 'Peticion redirigida correctamente';

    $response['token'] = Socialite::driver('google')->stateless()->user();

    return response()->json($response);
});
