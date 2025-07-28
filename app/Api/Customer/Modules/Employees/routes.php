<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'namespace' => 'App\Api\Customer\Modules\Employees\Controllers',
    'middleware' => [
        \App\Http\Middleware\ValidateCustomToken::class,
        \App\Http\Middleware\CompanyDynamicDatabaseConnection::class,
    ]
], function() {
    
    Route::get('/employeeslist', 'EmployeesController@employeesList')->name('customer.employees.index');
    Route::post('/employeescreate', 'EmployeesController@storeEmployees')->name('customer.employees.store');
    Route::post('/employeesdelete', 'EmployeesController@deleteEmployees')->name('customer.employees.delete');
    Route::post('/employeesresetmfa', 'EmployeesController@employeesResetMfa')->name('customer.employees.resetmfa');
    Route::get('/employeeroles', 'EmployeesController@getCompanyRoles')->name('customer.employees.getroles');
    Route::post('/userresetmfa', 'EmployeesController@resetMfa')->name('customer.employees.userresetmfa');
    Route::get('/employeeroleupdate/{companyId}/{userId}', 'EmployeesController@employeeRoleUpdate')->name('customer.employees.employeerole');
});

Route::group([
    'namespace' => 'App\Api\Customer\Modules\Employees\Controllers',
    'middleware' => [
        \App\Http\Middleware\ValidateCustomToken::class,
    ]
], function() {
    Route::get('/employeesview/{companyId}/{userId}', 'EmployeesController@showEmployees')->name('customer.employees.view');
    Route::post('/employeesupdate/{companyId}/{userId}', 'EmployeesController@updateEmployees')->name('customer.employees.update'); 
});