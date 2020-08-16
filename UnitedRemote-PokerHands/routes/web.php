<?php

use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::get('/', 'PokerHandsMainController@index');

Route::get('/home', 'PokerHandsMainController@index')->name('home');

Route::get('/start-poker-hands', 'PokerHandsMainController@index');

Route::post('/upload-poker-hands' , 'PokerHandsMainController@submitPokerHands');

Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');



