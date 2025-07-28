<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Api\Customer\Modules\SocialAccounts\Controllers'], function()
{ 
    
    Route::post('/microsoftauth', 'MicrosoftController@auth')->name('microsoft.auth');
    Route::post('/googlelogin', 'GoogleController@googleLogin')->name('google.auth'); 
});

Route::group([
    'namespace' => 'App\Api\Customer\Modules\SocialAccounts\Controllers',
    'middleware' => [
        \App\Http\Middleware\ValidateCustomToken::class,
        \App\Http\Middleware\CompanyDynamicDatabaseConnection::class,
    ]
], function() {
    Route::get('/microsoftEvents', 'MicrosoftController@getCalendarevents')->name('microsoft.getCalendarEvents');
    Route::post('/microsoftEvent', 'MicrosoftController@storeCalendarEvent')->name('microsoft.storeCalendarEvent');
    Route::put('/microsoftEvent/{id}', 'MicrosoftController@updateCalendarEvent')->name('microsoft.updateCalendarEvent');
    Route::delete('/microsoftEvent/{id}', 'MicrosoftController@deleteCalendarEvent')->name('microsoft.deleteCalendarEvent');
    Route::get('/microsoftEvent/{id}', 'MicrosoftController@getCalendarEvent')->name('microsoft.getCalendarEvent');
    Route::post('/microsoftconnect', 'MicrosoftController@microsoftconnect')->name('microsoft.microsoftconnect');
    Route::post('/microsoft/eventResponse', 'MicrosoftController@eventResponse')->name('microsoft.eventResponse');
    Route::get('/microsoft/syncEvent', 'MicrosoftController@syncEvent')->name('microsoft.syncEvent');
    Route::get('/usersemail', 'MicrosoftController@usersMailList')->name('microsoft.usersemail');
    Route::post('/geteventsbydate', 'MicrosoftController@getEventsByDate')->name('microsoft.geteventsbydate');


    Route::get('/microsoft/integrate', 'MicrosoftController@getIntegrate')->name('microsoft.getIntegrate');
    Route::post('/microsoft/integrate', 'MicrosoftController@storeIntegrate')->name('microsoft.storeIntegrate');
    Route::get('/microsoft/integratemenu', 'MicrosoftController@integrateMenu')->name('microsoft.integrateMenu');
    Route::get('/microsoft/checkconnect', 'MicrosoftController@checkconnect')->name('microsoft.checkconnect');
    Route::post('/microsoft/synctoggle', 'MicrosoftController@synctoggle')->name('microsoft.checksync');
    Route::get('/microsoft/synctoggle', 'MicrosoftController@checksynctoggle')->name('microsoft.checksynctoggle');

    //uniconta
    Route::post('/uniconta/auth', 'UnicontaController@auth')->name('uniconta.auth');
    Route::get('/uniconta/getauth', 'UnicontaController@getAuth')->name('uniconta.getAuth');
    Route::get('/uniconta/customers', 'UnicontaController@getCustomers')->name('uniconta.getCustomers');
    Route::get('/uniconta/customer/{account}', 'UnicontaController@getCustomer')->name('uniconta.getCustomer');
    Route::put('/uniconta/customer', 'UnicontaController@updateCustomer')->name('uniconta.updateCustomer');
    Route::post('/uniconta/customer', 'UnicontaController@storeCustomer')->name('uniconta.storeCustomer');
    Route::delete('/uniconta/customer', 'UnicontaController@deleteCustomer')->name('uniconta.deleteCustomer');
    Route::get('/uniconta/staticData', 'UnicontaController@staticData')->name('uniconta.staticData');
    Route::get('/uniconta/checkconnect', 'UnicontaController@checkconnect')->name('uniconta.checkconnect');
    //get select option
    Route::get('/uniconta/customersoptions', 'UnicontaController@selectOption')->name('uniconta.customersoptions');

    Route::post('/uniconta/contact', 'UnicontaController@storeContact')->name('uniconta.storeContact');
    Route::post('/uniconta/updatecontact', 'UnicontaController@updateContact')->name('uniconta.updateContact');
    Route::get('/uniconta/contact/{id}', 'UnicontaController@getContact')->name('uniconta.getContact');
    Route::get('/uniconta/contacts/{account}', 'UnicontaController@getContacts')->name('uniconta.getContacts');
    Route::delete('/uniconta/contact', 'UnicontaController@deleteContact')->name('uniconta.deleteContact');

    Route::post('/uniconta/address', 'UnicontaController@storeAddress')->name('uniconta.storeAddress');
    Route::post('/uniconta/updateaddress', 'UnicontaController@updateAddress')->name('uniconta.updateAddress');
    Route::get('/uniconta/address/{aacount}', 'UnicontaController@getAddress')->name('uniconta.getAddress');
    Route::get('/uniconta/getdeliveryaddress/{id}', 'UnicontaController@getDeliveryAddress')->name('uniconta.getdeliveryaddress');
    Route::delete('/uniconta/address', 'UnicontaController@deleteAddress')->name('uniconta.deleteAddress');


    Route::post('/googleCredentials','GoogleController@updateGoogleCredentials')->name('google.update');
    Route::get('/viewgooglecredentials','GoogleController@viewGoogleCredentials')->name('google.view');
});
