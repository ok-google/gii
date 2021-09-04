<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'inventory.',
    'prefix' => '/inventory',
    'namespace' => 'Inventory'
], function () {

    Route::group(['as' => 'mutation.', 'prefix' => '/mutation'], function () {

        Route::post('get_barcode', 'MutationController@get_barcode')->name('get_barcode');
        Route::delete('delete_barcode/{id}', 'MutationController@delete_barcode')->name('delete_barcode');
        Route::get('{id}/acc', 'MutationController@acc')->name('acc');
    });
    Route::resource('mutation', 'MutationController');

    Route::group(['as' => 'recondition.', 'prefix' => '/recondition'], function () {
        Route::get('/search_sku', 'ReconditionController@search_sku')->name('search_sku');
        Route::post('store_setting', 'ReconditionController@store_setting')->name('store_setting');
        Route::get('{id}/acc', 'ReconditionController@acc')->name('acc');
    });
    Route::resource('recondition', 'ReconditionController');

    Route::group(['as' => 'stock.', 'prefix' => '/stock'], function () {
        Route::get('{warehouse_id}/detail/{product_id}', 'StockController@detail')->name('detail');
    });
    Route::resource('stock', 'StockController');

    Route::group(['as' => 'stock_adjusment.', 'prefix' => '/stock_adjusment'], function () {
        Route::post('get_sku', 'StockAdjusmentController@get_sku')->name('get_sku');
        Route::get('{id}/acc', 'StockAdjusmentController@acc')->name('acc');
    });
    Route::resource('stock_adjusment', 'StockAdjusmentController');

    Route::group(['as' => 'mutation_display.', 'prefix' => '/mutation_display'], function () {
        Route::get('/search_sku', 'MutationDisplayController@search_sku')->name('search_sku');
        Route::get('{id}/acc', 'MutationDisplayController@acc')->name('acc');
    });
    Route::resource('mutation_display', 'MutationDisplayController');

    Route::group(['as' => 'product_conversion.', 'prefix' => '/product_conversion'], function () {
        Route::get('/search_sku', 'ProductConversionController@search_sku')->name('search_sku');
        Route::get('{id}/acc', 'ProductConversionController@acc')->name('acc');
    });
    Route::resource('product_conversion', 'ProductConversionController');

});