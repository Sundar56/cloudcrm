<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

$proxy_enabled   = getenv('PROXY_ENABLED');
if (!empty($proxy_enabled) && $proxy_enabled == true) {
    $proxy_url    = getenv('PROXY_URL');
    $proxy_schema = getenv('PROXY_SCHEMA');

    if (!empty($proxy_url)) {
        URL::forceRootUrl($proxy_url);
    }

    if (!empty($proxy_schema)) {
        URL::forceScheme($proxy_schema);
    }
}

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::group(['namespace' => 'App\Http\Controllers\Api'], function()
{  
    Route::post('/login', 'AuthController@customLogin'); 
    Route::post('/forgotpassword', 'AuthController@forgotPassword')->name('admin.forgotpassword');
    Route::post('/sendotp', 'AuthController@otpSend')->name('crm.sendotp');
    Route::post('/verifyotp', 'AuthController@otpVerify')->name('crm.OtpVerify');
});


Route::group(['namespace' => 'App\Http\Controllers\Api','middleware'=>[\App\Http\Middleware\ValidateCustomToken::class]], function() {
    
    Route::post('/change-password', 'AuthController@changePassword')->name('admin.changepassword');
    Route::get('/logout', 'AuthController@logOut')->name('admin.signout'); 
    Route::get('/dashboard', 'DashboardController@index')->name('crm.dashboard');
});




