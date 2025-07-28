<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::group([
    'namespace' => 'App\Api\Systemadmin\Modules\Company\Controllers',
    'middleware' => [
        \App\Http\Middleware\ValidateCustomToken::class,

    ]
], function () {

    /* company api */
    Route::post('/company', 'CompanyController@store')->name('crm.companies.store'); // Create a company
    Route::get('/company', 'CompanyController@index')->name('crm.companies.index'); // List all company
    Route::get('/company/{id}', 'CompanyController@show')->name('crm.companies.view'); // Show a company
    Route::delete('/company/{id}', 'CompanyController@destroy')->name('crm.companies.delete'); // Delete a company
    Route::post('/company/{id}', 'CompanyController@update')->name('crm.companies.update'); // Update a company

    /* company user api */
    Route::get('/companyuser/{companyId}', 'CompanyController@companyuserIndex')->name('crm.companyuser.index'); // List all companyuser
    Route::post('/companyuser', 'CompanyController@companyuserStore')->name('crm.companyuser.store'); // Create a company user
    Route::get('/companyuser/{companyId}/{userId}', 'CompanyController@companyuserShow')->name('crm.companyuser.view'); // Show a company user
    Route::post('/companyuser/{companyId}/{userId}', 'CompanyController@companyuserUpdate')->name('crm.companyuser.update'); // Update a company user
    Route::post('/companyuserdelete', 'CompanyController@companyuserdestroy')->name('crm.companyuser.delete');
    Route::get('/userroleupdate/{companyId}/{userId}', 'CompanyController@userRoleUpdate')->name('crm.companyuser.userrole'); //not used

    /* company general settings api */
    Route::get('/companygeneralsettings/{companyId}', 'CompanyController@companygeneralIndex')->name('crm.companygeneral.index'); // List all companygeneral
    // Route::post('/companygeneralsettings', 'CompanyController@companygeneralStore')->name('crm.companygeneral.store'); // Create a companygeneral
    Route::get('/companygeneralsettings/{companyId}/{userId}', 'CompanyController@companygeneralShow')->name('crm.companygeneral.view'); // Show a companygeneral
    Route::post('/companygeneralsettings/{companyId}/{userId}', 'CompanyController@companygeneralUpdate')->name('crm.companygeneral.update'); // Update a companygeneral
    Route::post('/companygeneraldelete', 'CompanyController@companygeneralDestroy')->name('crm.companygeneral.delete');

    /* Company roles */
    Route::get('/companyroles/{companyId}', 'CompanyController@getCompanyRoles')->name('crm.companyrole.index');
    Route::delete('/companyroles/{companyId}/{roleId}', 'CompanyController@destroyCompanyRoles')->name('crm.privileges.delete');


    /* company privileges crm,cms and shop */
    Route::post('/companyprivileges', 'CompanyController@companyPrivilegesStore')->name('crm.privileges.store');
    Route::get('/companyprivileges/{roleId}', 'CompanyController@companyPrivilegesShow')->name('crm.privileges.view');
    Route::post('/companyprivilegesupdate', 'CompanyController@companyPrivilegesUpdate')->name('crm.privileges.update');
    /* Company modules for crm,cms and shop */
    Route::get('/companymodules', 'CompanyController@companymodules')->name('company.modules.index');

    /* Latest company data */
    Route::get('companylatestdata', 'CompanyController@companyLatestData')->name('company.latestdata');

    /*Company SSO settings */
    Route::post('/companyssosetting', 'CompanyController@SsoSettingAccess')->name('company.ssosetting');
    Route::get('/viewssoaccess/{companyId}', 'CompanyController@showSsoSetttingAccess')->name('company.viewssoaccess');
    Route::get('/viewlogincredentials/{companyId}', 'CompanyController@showLoginSettings')->name('company.viewlogincredentials');

    Route::get('/accesscontrolapps/{companyId}', 'CompanyController@accessControlAppsList')->name('company.accesscontrol.applist');
    Route::post('/footersetting', 'CompanyController@customerFooterSetting')->name('company.footersetting');
});

/*Company Socket Notification API */
Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware'=>[\App\Http\Middleware\ValidateCustomToken::class]], function () {

    Route::post('/companynotifymessage', 'NotificationController@companyNotificationMessage')->name('company.companynotifymessage');
    Route::post('/updatedrolenotify', 'NotificationController@updatedRoleNotify')->name('company.updatedrolenotify');
    Route::post('/adminuserupdatedrole', 'NotificationController@adminuserUpdatedRoleNotify')->name('adminuser.adminuserupdatedrole');
});
