<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

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


Auth::routes(['register' => false]);

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('/');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/payments', [App\Http\Controllers\UserController::class, 'payments'])->name('payments');
Route::get('/payments/{filter}', [App\Http\Controllers\UserController::class, 'payments'])->name('payments');
Route::get('/student_available', [App\Http\Controllers\UserController::class, 'student_available'])->name('student_available');
Route::get('/approve/{id}', [App\Http\Controllers\UserController::class, 'approve'])->name('approve');
Route::get('/decline/{id}', [App\Http\Controllers\UserController::class, 'decline'])->name('decline');
Route::get('/findPlan', [App\Http\Controllers\UserController::class, 'findPlan'])->name('findPlan');
Route::get('/successPlan', [App\Http\Controllers\UserController::class, 'successPlan'])->name('successPlan');
Route::post('/successPlan', [App\Http\Controllers\UserController::class, 'successPlan'])->name('successPlan');
Route::get('/cancelPlan', [App\Http\Controllers\UserController::class, 'cancelPlan'])->name('cancelPlan');
Route::post('/cancelPlan', [App\Http\Controllers\UserController::class, 'cancelPlan'])->name('cancelPlan');
Route::get('/findSubjects', [App\Http\Controllers\UserController::class, 'findSubjects'])->name('findSubjects');
Route::get('/findSubjectsAvailable/{subject_id}', [App\Http\Controllers\UserController::class, 'findSubjectsAvailable'])->name('findSubjectsAvailable');
Route::post('/choose', [App\Http\Controllers\UserController::class, 'choose'])->name('choose');
Route::get('/online', [App\Http\Controllers\UserController::class, 'online'])->name('online');
Route::get('/start', [App\Http\Controllers\UserController::class, 'start'])->name('start');
Route::get('/pause', [App\Http\Controllers\UserController::class, 'pause'])->name('pause');
Route::get('/end', [App\Http\Controllers\UserController::class, 'end'])->name('end');
Route::get('/check', [App\Http\Controllers\UserController::class, 'check'])->name('check');
Route::get('/about', [App\Http\Controllers\HomeController::class, 'about'])->name('about');
Route::get('/offer', [App\Http\Controllers\HomeController::class, 'offer'])->name('offer');
Route::get('/job', [App\Http\Controllers\HomeController::class, 'job'])->name('job');
Route::get('/support', [App\Http\Controllers\HomeController::class, 'support'])->name('support');

Route::get('/list', [App\Http\Controllers\AdminController::class, 'list'])->name('list');
Route::get('/payment', [App\Http\Controllers\AdminController::class, 'payment'])->name('payment');
Route::get('/payment/{user_id}', [App\Http\Controllers\AdminController::class, 'payment'])->name('payment');
Route::post('/getList', [App\Http\Controllers\AdminController::class, 'getList'])->name('getList');
Route::post('/getPayment', [App\Http\Controllers\AdminController::class, 'getPayment'])->name('getPayment');
Route::get('/action/{user_id}', [App\Http\Controllers\AdminController::class, 'action'])->name('action');
Route::get('/successSalary/{payments_id}', [App\Http\Controllers\AdminController::class, 'successSalary'])->name('successSalary');
Route::get('/show/{file}', [App\Http\Controllers\AdminController::class, 'show'])->name('show');

Route::get('/404', function(){
    return abort(404);
 });

 Route::post('/register/{type}', [App\Http\Controllers\HomeController::class, 'register'])->name('register');
 Route::get('/register/{type}', [App\Http\Controllers\HomeController::class, 'register'])->name('register');
 Route::get('/register', [App\Http\Controllers\HomeController::class, 'register'])->name('register');
 Route::get('/set_password', [App\Http\Controllers\HomeController::class, 'set_password'])->name('set_password');
 Route::post('/set_password', [App\Http\Controllers\HomeController::class, 'set_password'])->name('set_password');
 