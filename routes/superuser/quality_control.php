<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'quality_control.',
    'prefix' => '/quality_control',
    'namespace' => 'QualityControl'
], function () {

    Route::group(['as' => 'quality_control_1.', 'prefix' => '/quality_control_1'], function () {

        Route::post('get_barcode', 'QualityControl1Controller@get_barcode')->name('get_barcode');
    });
    Route::resource('quality_control_1', 'QualityControl1Controller');
    

    Route::group(['as' => 'quality_control_2.', 'prefix' => '/quality_control_2'], function () {
    });
    Route::resource('quality_control_2', 'QualityControl2Controller');

});