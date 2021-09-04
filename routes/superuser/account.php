<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'account.',
    'prefix' => '/account'
], function () {
    
    Route::group(['as' => 'superuser.', 'prefix' => '/superuser'], function () {
        Route::get('/restore/{id}', 'Account\SuperuserController@restore')->name('restore');

        Route::post('/role/assign/{id}', 'Account\SuperuserController@assignRole')->name('role.assign');
        Route::post('/role/remove/{id?}', 'Account\SuperuserController@removeRole')->name('role.remove');

        Route::post('/permission/sync/{id}', 'Account\SuperuserController@syncPermission')->name('permission.sync');
    });
    Route::resource('superuser', 'Account\SuperuserController');
    
    Route::group(['as' => 'user.', 'prefix' => '/user'], function () {
    
    });

    Route::group(['as' => 'sales_person.', 'prefix' => '/sales_person'], function () {
        Route::get('/restore/{id}', 'Account\SalesPersonController@restore')->name('restore');

        Route::get('/{id}/zone', 'Account\SalesPersonZoneController@manage')->name('zone.manage');
        Route::post('/{id}/zone', 'Account\SalesPersonZoneController@add')->name('zone.add');
        Route::get('/{id}/zone/{zone_id}/remove', 'Account\SalesPersonZoneController@remove')->name('zone.remove');
    });
    Route::resource('sales_person', 'Account\SalesPersonController');

    Route::resource('user', 'Account\UserController');
});