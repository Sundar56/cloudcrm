<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'namespace' => 'App\Api\Customer\Modules\Settings\Controllers',
    'middleware' => [
        \App\Http\Middleware\ValidateCustomToken::class,
        \App\Http\Middleware\CompanyDynamicDatabaseConnection::class,
    ]
], function () {
    Route::get('/viewssosettings', 'SettingsController@viewSSOsettings')->name('customer.ssosettings.view');
    Route::post('/updatessosettings', 'SettingsController@updateSSOsettings')->name('customer.ssosettings.update');

    Route::get('/globalsettingvalues', 'SettingsController@viewGlobalSettingValues')->name('customer.globalsetting.get');
    Route::post('/createsmtpsettings', 'SettingsController@createSmtpGlobalSettings')->name('customer.globalsettings.create');
    Route::get('/viewsmtplobalsettings', 'SettingsController@viewSmtpGlobalsettings')->name('customer.globalsettings.view');
    Route::post('/createsmssettings', 'SettingsController@createSmsGlobalSettings')->name('customer.smsglobalsettings.store');
    Route::get('/viewsmsglobalsettings', 'SettingsController@viewSmsGlobalsettings')->name('customer.smsglobalsettings.view');

    Route::post('/mailconfig', 'SettingsController@testConfigMail')->name('customer.globalsettings.testmail');

    Route::post('/createusersettings', 'SettingsController@createUsersettings')->name('customer.usersettings.store');
    Route::get('/viewusersettings', 'SettingsController@showUsersettings')->name('customer.usersettings.view');

    Route::get('/customerappmodules/{companyId}', 'SettingsController@customerAppModules')->name('customer.accesscontrol.get');
    Route::get('/customerappprivileges/{roleId}', 'SettingsController@viewAccessControlPrivileges')->name('customer.accesscontrol.view');
});

/*Customer portal admin dashboard Socket Notification API */
Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware'=>[\App\Http\Middleware\ValidateCustomToken::class]], function () {

    Route::post('/mailconfignotify', 'NotificationController@mailConfigNotification')->name('customer.mailconfignotify');
});


