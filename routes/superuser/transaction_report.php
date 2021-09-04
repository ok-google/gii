<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'transaction_report.',
    'prefix' => '/transaction_report',
    'namespace' => 'TransactionReport'
], function () {

    Route::group(['as' => 'purchase_report.', 'prefix' => '/purchase_report'], function () {
        Route::post('/create/pdf', 'PurchaseReportController@pdf')->name('pdf');
    });
    Route::resource('purchase_report', 'PurchaseReportController');

    Route::group(['as' => 'sales_report.', 'prefix' => '/sales_report'], function () {
        // Route::post('/create/pdf', 'SalesReportController@pdf')->name('pdf');
        Route::post('/export', 'SalesReportController@export')->name('export');
    });
    Route::resource('sales_report', 'SalesReportController');

    Route::group(['as' => 'daily_recapitulation.', 'prefix' => '/daily_recapitulation'], function () {
        Route::post('/create/pdf', 'DailyRecapitulationController@pdf')->name('pdf');
    });
    Route::resource('daily_recapitulation', 'DailyRecapitulationController');

    Route::group(['as' => 'delivery_progress.', 'prefix' => '/delivery_progress'], function () {
        // Route::post('/create/pdf', 'DeliveryProgressController@pdf')->name('pdf');
        Route::post('/export', 'DeliveryProgressController@export')->name('export');
    });
    Route::resource('delivery_progress', 'DeliveryProgressController');

    Route::group(['as' => 'all_stock.', 'prefix' => '/all_stock'], function () {
        // Route::post('/create/pdf', 'AllStockController@pdf')->name('pdf');
    });
    Route::resource('all_stock', 'AllStockController');

    Route::group(['as' => 'hpp_report.', 'prefix' => '/hpp_report'], function () {
    });
    Route::resource('hpp_report', 'HppReportController');

    Route::group(['as' => 'receiving_report.', 'prefix' => '/receiving_report'], function () {
    });
    Route::resource('receiving_report', 'ReceivingReportController');

    Route::group(['as' => 'gudang_utama_report.', 'prefix' => '/gudang_utama_report'], function () {
    });
    Route::resource('gudang_utama_report', 'GudangUtamaReportController');

    Route::group(['as' => 'stock_valuation.', 'prefix' => '/stock_valuation'], function () {
    });
    Route::resource('stock_valuation', 'StockValuationReportController');

});