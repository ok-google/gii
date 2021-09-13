<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'accounting.',
    'prefix' => '/accounting',
    'namespace' => 'Accounting'
], function () {

    Route::group(['as' => 'coa.', 'prefix' => '/coa'], function () {
        Route::get('/select_parent_level_1', 'CoaController@select_parent_level_1')->name('select_parent_level_1');
        Route::get('/select_parent_level_2', 'CoaController@select_parent_level_2')->name('select_parent_level_2');
        Route::get('/select_parent_level_3', 'CoaController@select_parent_level_3')->name('select_parent_level_3');
        Route::get('/import_template', 'CoaController@import_template')->name('import_template');
        Route::post('/import', 'CoaController@import')->name('import');
        Route::get('/export', 'CoaController@export')->name('export');
    });
    Route::resource('coa', 'CoaController');

    Route::group(['as' => 'journal.', 'prefix' => '/journal'], function () {
        Route::get('/posting', 'JournalController@posting')->name('posting');
        Route::get('/unpost/{id}', 'JournalController@unpost')->name('unpost');
        // Route::get('/create/profit_loss/{data?}/{protect?}', 'JournalController@profit_loss')->name('profit_loss');
    });
    Route::resource('journal', 'JournalController');

    // Route::group(['as' => 'general_ledger.', 'prefix' => '/general_ledger'], function () {
    //     Route::get('/create/pdf/{data?}/{protect?}', 'GeneralLedgerController@pdf')->name('pdf');
    // });
    // Route::resource('general_ledger', 'GeneralLedgerController');

    Route::group(['as' => 'setting_profit_loss.', 'prefix' => '/setting_profit_loss'], function () {
    });
    Route::resource('setting_profit_loss', 'SettingProfitLossController');

    Route::group(['as' => 'daily_report.', 'prefix' => '/daily_report'], function () {
        Route::get('/create/pdf/{coa?}/{date?}', 'DailyReportController@pdf')->name('pdf');
    });
    Route::resource('daily_report', 'DailyReportController');

});