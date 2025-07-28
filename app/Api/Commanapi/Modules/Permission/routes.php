<?php
use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'App\Api\Commanapi\Modules\Permission\Controllers'], function() {

     /* Compnay modules access api*/
   Route::post('/CheckCompanyModuleAccess', 'CompanyModuleAccessController@CheckCompanyModuleAccess')->name('CheckCompanyModuleAccess');
   Route::post('/checkapplicationaccess', 'ApplicationAccessController@checkApplicationAccess')->name('CheckApplicationaccess');

});