<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'purchasing.',
    'prefix' => '/purchasing',
    'namespace' => 'Purchasing'
], function () {

    Route::group(['as' => 'purchase_order.', 'prefix' => '/purchase_order'], function () {
        Route::get('/step/{id}', 'PurchaseOrderController@step')->name('step');
        Route::get('{id}/publish', 'PurchaseOrderController@publish')->name('publish');
        Route::get('{id}/save_modify/{save_type}', 'PurchaseOrderController@save_modify')->name('save_modify');
        Route::get('{id}/acc', 'PurchaseOrderController@acc')->name('acc');
        Route::get('/import_template', 'PurchaseOrderController@import_template')->name('import_template');
        Route::post('/import/{id}', 'PurchaseOrderController@import')->name('import');
        Route::get('/create/pdf/{data?}/{protect?}', 'PurchaseOrderController@pdf')->name('pdf');

        Route::group(['as' => 'detail.'], function () {
            Route::get('{id}/detail/create', 'PurchaseOrderDetailController@create')->name('create');
            Route::post('{id}/detail', 'PurchaseOrderDetailController@store')->name('store');
            Route::get('{id}/detail/{detail_id}/edit', 'PurchaseOrderDetailController@edit')->name('edit');
            Route::put('{id}/detail/{detail_id}', 'PurchaseOrderDetailController@update')->name('update');
            Route::delete('{id}/detail/{detail_id}', 'PurchaseOrderDetailController@destroy')->name('destroy');
            Route::post('/detail/bulk_delete', 'PurchaseOrderDetailController@bulk_delete')->name('bulk_delete');
        });
    });
    Route::resource('purchase_order', 'PurchaseOrderController');

    Route::group(['as' => 'receiving.', 'prefix' => '/receiving'], function () {
        Route::get('/step/{id}', 'ReceivingController@step')->name('step');
        Route::get('{id}/publish', 'ReceivingController@publish')->name('publish');
        Route::get('{id}/acc', 'ReceivingController@acc')->name('acc');
        Route::get('/create/pdf/{data?}/{protect?}', 'ReceivingController@pdf')->name('pdf');
        Route::get('/create/print_barcode/{data?}/{protect?}', 'ReceivingController@print_barcode')->name('print_barcode');

        Route::group(['as' => 'detail.'], function () {
            Route::get('{id}/detail/{detail_id}/colly', 'ReceivingDetailController@show')->name('show');
            Route::get('{id}/detail/create', 'ReceivingDetailController@create')->name('create');
            Route::post('{id}/detail', 'ReceivingDetailController@store')->name('store');
            Route::delete('{id}/detail/{detail_id}/delete', 'ReceivingDetailController@destroy')->name('destroy');

            Route::get('{id}/detail/{detail_id}/edit', 'ReceivingDetailController@edit')->name('edit');
            Route::put('{id}/detail/{detail_id}', 'ReceivingDetailController@update')->name('update');

            Route::post('detail/get_sku_json', 'ReceivingDetailController@get_sku_json')->name('get_sku_json');

            Route::group(['as' => 'colly.'], function () {
                Route::get('{id}/colly/{detail_id}/create', 'ReceivingDetailCollyController@create')->name('create');
                Route::post('{id}/{detail_id}/colly', 'ReceivingDetailCollyController@store')->name('store');
                Route::get('{id}/detail/{detail_id}/colly/{colly_id}/edit', 'ReceivingDetailCollyController@edit')->name('edit');
                Route::put('{id}/detail/{detail_id}/colly/{colly_id}', 'ReceivingDetailCollyController@update')->name('update');
                Route::delete('{id}/detail/{detail_id}/colly/{colly_id}/delete', 'ReceivingDetailCollyController@destroy')->name('destroy');
            });

        });
    });
    Route::resource('receiving', 'ReceivingController');

});