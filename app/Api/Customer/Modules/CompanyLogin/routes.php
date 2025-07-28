<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Api\Customer\Modules\CompanyLogin\Controllers'], function()
{ 
    Route::post('/companybanner', 'CompanyloginController@companyBanner')->name('customer.companybanner');
    Route::post('/companyuserlogin', 'CompanyloginController@companyUserlogin')->name('customer.companylogin');
    Route::post('/customerforgotpassword', 'CompanyloginController@forgotPassword')->name('customer.forgotpassword');
    Route::post('/customer/sentotp', 'CompanyloginController@otpSend')->name('customer.otpSend');
    Route::post('/customer/otpverify', 'CompanyloginController@otpVerify')->name('customer.otpVerify');
});

Route::group([
    'namespace' => 'App\Api\Customer\Modules\CompanyLogin\Controllers',
    'middleware' => [
        \App\Http\Middleware\ValidateCustomToken::class,
        \App\Http\Middleware\CompanyDynamicDatabaseConnection::class,
    ]
], function() {
    
    Route::post('/userpageactivity', 'UserActivityController@pageActivityStore')->name('customer.userpageactivity'); 
    Route::post('/userloginactivity', 'UserActivityController@userActivityStore')->name('customer.userloginactivity');    
    Route::post('/signout', 'CompanyloginController@signOut')->name('customer.signout');  
    Route::post('/changepassword', 'CompanyloginController@changePassword')->name('customer.changepassword');
    Route::get('/customerdashboard', 'DashboardController@index')->name('customer.dashboard');
    Route::post('/appaccess', 'DashboardController@appAccess')->name('customer.appaccess');
    Route::get('/pagemodulelist', 'DashboardController@pageModuleList')->name('customer.pagemodulelist');
    Route::post('/appactivity', 'DashboardController@appActivity')->name('customer.appactivity');

    Route::post('/admindashboard', 'DashboardController@adminDashboard')->name('customer.admindashboard');
    Route::post('/globaldashboard', 'DashboardController@globalDashboard')->name('customer.globaldashboard');
    Route::post('/globaldashboard/taskoverview', 'DashboardController@taskOverview')->name('customer.taskOverview');
    Route::post('/admindashboardchart', 'DashboardController@adminDashboardChart')->name('customer.admindashboardchart');

    Route::post('/customer/storenote', 'MyNotesController@storeNote')->name('customer.storeNote');
    Route::get('/customer/getnote/{id}', 'MyNotesController@getNote')->name('customer.getNote');
    Route::get('/customer/getnotes', 'MyNotesController@getNotes')->name('customer.getNotes');
    Route::post('/customer/updatenote', 'MyNotesController@updateNote')->name('customer.updateNote');
    Route::delete('/customer/deletenotes/{id}', 'MyNotesController@deleteNotes')->name('customer.deleteNotes');

});

/*Customer portal admin dashboard Socket Notification API */
Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware'=>[\App\Http\Middleware\ValidateCustomToken::class]], function () {

    Route::post('/admindashboardcount', 'NotificationController@adminDashboardCountMessage')->name('customer.admincountmessage');
    Route::post('/globaldashboardcount', 'NotificationController@globalDashboardCountMessage')->name('customer.globalcountmessage');
});


