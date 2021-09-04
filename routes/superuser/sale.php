<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'sale.',
    'prefix' => '/sale',
    'namespace' => 'Sale'
], function () {

    Route::group(['as' => 'sales_order.', 'prefix' => '/sales_order'], function () {

        Route::get('/search_sku', 'SalesOrderController@search_sku')->name('search_sku');
        Route::get('{id}/acc', 'SalesOrderController@acc')->name('acc');
        Route::post('/bulk_acc', 'SalesOrderController@bulk_acc')->name('bulk_acc');
        Route::get('/import_template', 'SalesOrderController@import_template')->name('import_template');
        Route::post('/import', 'SalesOrderController@import')->name('import');
        Route::get('/export', 'SalesOrderController@export')->name('export');
        Route::get('/create/pdf/{data?}/{protect?}', 'SalesOrderController@pdf')->name('pdf');
        Route::delete('force_delete/{id}', 'SalesOrderController@force_delete')->name('force_delete');
    });
    Route::resource('sales_order', 'SalesOrderController');

    Route::group(['as' => 'delivery_order.', 'prefix' => '/delivery_order'], function () {
        Route::get('/create/packing_pdf/{data?}/{protect?}', 'DeliveryOrderController@packing_pdf')->name('packing_pdf');
        Route::get('/create/delivery_order_pdf/{data?}/{protect?}', 'DeliveryOrderController@delivery_order_pdf')->name('delivery_order_pdf');
        Route::get('/create/delivery_order_pdf_non_marketplace/{data?}/{protect?}', 'DeliveryOrderController@delivery_order_pdf_non_marketplace')->name('delivery_order_pdf_non_marketplace');
        Route::post('get_store', 'DeliveryOrderController@get_store')->name('get_store');
    });
    Route::resource('delivery_order', 'DeliveryOrderController');

    Route::group(['as' => 'sale_return.', 'prefix' => '/sale_return'], function () {
        Route::get('{id}/acc', 'SaleReturnController@acc')->name('acc');
        Route::post('get_product', 'SaleReturnController@get_product')->name('get_product');
        Route::get('/search_do', 'SaleReturnController@search_do')->name('search_do');
    });
    Route::resource('sale_return', 'SaleReturnController');

    Route::group(['as' => 'do_validate.', 'prefix' => '/do_validate'], function () {
        Route::post('get_barcode', 'DOValidateController@get_barcode')->name('get_barcode');
    });
    Route::resource('do_validate', 'DOValidateController');

    Route::group(['as' => 'buy_back.', 'prefix' => '/buy_back'], function () {
        Route::post('get_sku', 'BuyBackController@get_sku')->name('get_sku');
        Route::get('{id}/acc', 'BuyBackController@acc')->name('acc');
        Route::get('/search_so', 'BuyBackController@search_so')->name('search_so');
    });
    Route::resource('buy_back', 'BuyBackController');

});