<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'finance.',
    'prefix' => '/finance',
    'namespace' => 'Finance'
], function () {

    Route::group(['as' => 'payment.', 'prefix' => '/payment'], function () {
        Route::get('{id}/acc', 'CBPaymentController@acc')->name('acc');
        Route::get('{id}/pdf', 'CBPaymentController@pdf')->name('pdf');
    });
    Route::resource('payment', 'CBPaymentController');

    Route::group(['as' => 'payment_invoice.', 'prefix' => '/payment_invoice'], function () {
        Route::get('{id}/acc', 'CBPaymentInvoiceController@acc')->name('acc');
        Route::post('get_ppb', 'CBPaymentInvoiceController@get_ppb')->name('get_ppb');
        Route::post('get_pbm', 'CBPaymentInvoiceController@get_pbm')->name('get_pbm');
        Route::get('{id}/pdf', 'CBPaymentInvoiceController@pdf')->name('pdf');
    });
    Route::resource('payment_invoice', 'CBPaymentInvoiceController');

    Route::group(['as' => 'receipt.', 'prefix' => '/receipt'], function () {
        Route::get('{id}/acc', 'CBReceiptController@acc')->name('acc');
        Route::get('{id}/pdf', 'CBReceiptController@pdf')->name('pdf');
    });
    Route::resource('receipt', 'CBReceiptController');

    Route::group(['as' => 'receipt_invoice.', 'prefix' => '/receipt_invoice'], function () {
        Route::get('{id}/acc', 'CBReceiptInvoiceController@acc')->name('acc');
        Route::post('get_sales_order', 'CBReceiptInvoiceController@get_sales_order')->name('get_sales_order');
        Route::get('{id}/pdf', 'CBReceiptInvoiceController@pdf')->name('pdf');
    });
    Route::resource('receipt_invoice', 'CBReceiptInvoiceController');

    Route::group(['as' => 'marketplace_receipt.', 'prefix' => '/marketplace_receipt'], function () {
        Route::get('/import_template', 'MarketplaceReceiptController@import_template')->name('import_template');
        Route::post('/import', 'MarketplaceReceiptController@import')->name('import');
    });
    Route::resource('marketplace_receipt', 'MarketplaceReceiptController');

    Route::group(['as' => 'setting_finance.', 'prefix' => '/setting_finance'], function () {
    });
    Route::resource('setting_finance', 'SettingFinanceController');

    Route::group(['as' => 'secret_setting.', 'prefix' => '/secret_setting'], function () {
    });
    Route::resource('secret_setting', 'SecretSettingController');

    Route::group(['as' => 'journal_entry.', 'prefix' => '/journal_entry'], function () {
    });
    Route::resource('journal_entry', 'JournalEntryController');

    Route::group(['as' => 'journal_setting.', 'prefix' => '/journal_setting'], function () {
    });
    Route::resource('journal_setting', 'JournalSettingController');
});