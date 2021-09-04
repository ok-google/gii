<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'report.',
    'prefix' => '/report',
    'namespace' => 'Report'
], function () {

    Route::group(['as' => 'profit_loss_report.', 'prefix' => '/profit_loss_report'], function () {
        Route::get('/create/pdf/{data?}/{protect?}', 'ProfitLossReportController@pdf')->name('pdf');
    });
    Route::resource('profit_loss_report', 'ProfitLossReportController');

    Route::group(['as' => 'general_ledger.', 'prefix' => '/general_ledger'], function () {
        Route::get('/create/pdf/{data?}/{protect?}', 'GeneralLedgerController@pdf')->name('pdf');
    });
    Route::resource('general_ledger', 'GeneralLedgerController');

    Route::group(['as' => 'cash_flow_report.', 'prefix' => '/cash_flow_report'], function () {
        Route::get('/create/pdf/{data?}/{protect?}', 'CashFlowReportController@pdf')->name('pdf');
    });
    Route::resource('cash_flow_report', 'CashFlowReportController');

    Route::group(['as' => 'balance_sheet.', 'prefix' => '/balance_sheet'], function () {
        Route::get('/create/pdf/{data?}/{protect?}', 'BalanceSheetController@pdf')->name('pdf');
    });
    Route::resource('balance_sheet', 'BalanceSheetController');

    // Route::group(['as' => 'daily_report.', 'prefix' => '/daily_report'], function () {
    //     Route::get('/create/pdf/{coa?}/{date?}', 'DailyReportController@pdf')->name('pdf');
    // });
    // Route::resource('daily_report', 'DailyReportController');

});