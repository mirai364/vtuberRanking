<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\VideoController;

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

Route::get('/', [VideoController::class, 'streamRanking']);
Route::get('/channel', [ChannelController::class, 'index']);
Route::get('/channel/detail/{id}', [ChannelController::class, 'detail']);
Route::get('/video/detail/{id}', [VideoController::class, 'detail']);
Route::get('/video/stream-ranking', [VideoController::class, 'streamRanking']);
Route::get('/video/hourly-stream-ranking', [VideoController::class, 'hourlyStreamRanking']);
Route::get('/video/hourly-stream-ranking/{id}', [VideoController::class, 'hourlyStreamRanking']);
Route::get('/video/daily-stream-ranking', [VideoController::class, 'dailyStreamRanking']);
Route::get('/video/daily-stream-ranking/{id}', [VideoController::class, 'dailyStreamRanking']);