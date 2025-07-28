<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Api\Systemadmin\Modules\Roles\Controllers', 'middleware'=>[\App\Http\Middleware\ValidateCustomToken::class]], function () {
    Route::get('/roles', 'RolesController@index')->name('crm.roles.index');
    Route::get('/rolescreate', 'RolesController@create')->name('crm.roles.create');
    Route::post('/roles', 'RolesController@store')->name('crm.roles.store');
    Route::post('/rolesupdate', 'RolesController@update')->name('crm.roles.update');
    Route::delete('/roles/{id}', 'RolesController@delete')->name('crm.roles.delete');
    Route::get('/getmodulesbyrole/{roleId}', 'RolesController@getModulesByRole')->name('crm.roles.view');
    Route::get('/adminmodules','RolesController@adminModulesList')->name('crm.roles.adminmoduleslist');
    Route::get('/rolesupdatedprivileges/{id}','RolesController@rolesUpdatedPrivilges')->name('crm.roles.updatedprivileges');
});

/*Roles Privileges Socket Notification API */
Route::group(['namespace' => 'App\Http\Controllers\Api', 'middleware'=>[\App\Http\Middleware\ValidateCustomToken::class]], function () {

    Route::post('/privilegesnotifymessage', 'NotificationController@privilegeNotificationMessage')->name('roles.privilegesnotifymessage');
});