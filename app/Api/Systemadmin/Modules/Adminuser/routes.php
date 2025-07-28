<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Api\Systemadmin\Modules\Adminuser\Controllers',
 'middleware'=>[
    \App\Http\Middleware\ValidateCustomToken::class, 
  ]
], function () {
    Route::get('/users', 'AdminuserController@index')->name('crm.adminusers.index');
    Route::post('/users', 'AdminuserController@store')->name('crm.adminusers.store');
    Route::get('/users/{id}', 'AdminuserController@edit')->name('crm.adminusers.view');
    Route::post('/usersupdate', 'AdminuserController@update')->name('crm.adminusers.update');
    Route::delete('/users/{id}', 'AdminuserController@delete')->name('crm.adminusers.delete');
    Route::post('/resetpassword/{id}', 'AdminuserController@resetPassword')->name('crm.passwordreset.reset');
    Route::get('/userscreate', 'AdminuserController@create')->name('crm.adminusers.create');
    Route::get('/getroles', 'AdminuserController@getRoles')->name('crm.adminusers.getroles');
    Route::get('/updateduserprivileges/{id}', 'AdminuserController@adminUserUpdatedPrivileges')->name('crm.adminusers.updatedprivileges');
});
