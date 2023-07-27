<?php

use App\Events\CommentAdded;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;

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

Route::get('/', [AuthController::class, 'ShowAuthForm'])->name('login')->middleware('guest');

Route::post('/authenticate', [AuthController::class, 'Authenticate']);

Route::get('/comments', [CommentController::class, 'ListComments'])->middleware('auth');

Route::get('/comments/next', [CommentController::class, 'ListMoreComments'])->middleware('auth');

Route::post('/comments/add', [CommentController::class, 'AddComment']);

Route::post('/comments/vote', [CommentController::class, 'Vote']);

Route::post('/logout', [AuthController::class, 'Logout']);
