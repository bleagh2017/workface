<?php

use Illuminate\Http\Request;

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

Route::namespace('Api')->group(function () {
    //登录路由
    Route::post('/login', 'LoginUserController@login');
    //Route::get('/checkin', 'LoginUserController@checkin');
    
});

Route::middleware('auth:api')->group(function () {
    //个人信息
    //Route::post('/getHomePage', 'Api\LoginUserController@test');
    //活动
    Route::post('/createEvent', 'Api\EventController@create');
    Route::post('/getEvent', 'Api\EventController@getList');
    Route::post('/uploadEvent', 'Api\EventController@upload');
    Route::post('/updateEvent', 'Api\EventController@update');
    Route::post('/releaseEvent', 'Api\EventController@release');
    Route::post('/getCode', 'Api\EventController@getCode');
    //分享者
    Route::post('/getSharerName', 'Api\SharerController@getName');
    Route::post('/insertSharer', 'Api\SharerController@insertSharer');
    Route::post('/updateSharer', 'Api\SharerController@updateSharer');
    //报名
    Route::post('/signUp', 'Api\EventSignUpController@signUp');
    Route::post('/signUpList', 'Api\EventSignUpController@signUpList');
    Route::post('/passSign', 'Api\EventSignUpController@passSign');
    Route::post('/signInfo', 'Api\EventSignUpController@signInfo');
    Route::post('/transferSign', 'Api\EventSignUpController@transferSign');
    Route::post('/cancelSign', 'Api\EventSignUpController@cancelSign');
    
    //签到
    Route::post('/checkIn', 'Api\CheckInController@checkIn');
    //用户列表
    Route::post('/getUserList', 'Api\LoginUserController@getUserList');
    Route::post('/getUserInfo', 'Api\LoginUserController@getUserInfo');
    Route::post('/changeUserType', 'Api\LoginUserController@changeUserType');
    //修改配置
    Route::post('/setConfig', 'Api\ConfigController@setConfig');
    //福利列表
    Route::post('/itemList', 'Api\ItemInfoController@itemList');
    Route::post('/insertItem', 'Api\ItemInfoController@insertItem');
    Route::post('/updateItem', 'Api\ItemInfoController@updateItem');
    Route::post('/changeItemState', 'Api\ItemInfoController@changeItemState');
    Route::post('/addItemNum', 'Api\ItemInfoController@addItemNum');
    Route::post('/getItemCode', 'Api\ItemInfoController@getItemCode');
    Route::get('/saveCode', 'Api\ItemInfoController@saveCode');
    Route::post('/sendExcel', 'Api\ItemInfoController@sendExcel');
    Route::post('/getShopItem', 'Api\ItemInfoController@getShopItem');
    Route::post('/exchange', 'Api\ItemInfoController@exchange');
    //获取优惠券列表
    Route::post('/getCouponList', 'Api\CouponListController@getCouponList');
    Route::post('/billCoupon', 'Api\CouponListController@billCoupon');
    Route::post('/getRedemList', 'Api\CouponListController@getRedemList');
});
