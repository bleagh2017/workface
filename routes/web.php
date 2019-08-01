<?php

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


Route::group([ 'namespace' => 'Admin','prefix' => 'admin'],function (){
    Route::get('/', 'HomeController@index')->name('adminhome');
});
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/test', 'HomeController@test');
Route::post('/test', 'HomeController@test');
//自动调用脚本 处理兑换券过期
Route::get('/coupon', 'HomeController@checkExpiredCoupon');
Route::get('/redem', 'HomeController@checkExpiredRedem');

Auth::routes();

