<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'App\Api\Customer\Modules\Company\Controllers',
    'middleware' => [
        \App\Http\Middleware\ValidateCustomToken::class,
        \App\Http\Middleware\CompanyDynamicDatabaseConnection::class,
    ]
], function () {

    Route::get('/companyview', 'CompanyeditController@show')->name('customer.mycompany.view');
    Route::post('/companyupdate', 'CompanyeditController@update')->name('customer.mycompany.update');
});
