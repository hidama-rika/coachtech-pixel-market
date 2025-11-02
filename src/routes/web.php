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

// 開発用表示ルート Route::view('URI', 'ビュー名');
Route::view('/login', 'login');
Route::view('/register', 'register');
Route::view('/address', 'shipping-address_edit');
Route::view('/', 'index');
Route::view('/mypage/profile', 'profile_edit');
Route::view('/sell', 'new_items');
Route::view('/purchase', 'new_purchases');
Route::view('/mypage', 'mypage');
Route::view('/item', 'show');