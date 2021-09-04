<?php

Route::get('/', 'DashboardController@index')->name('index');

Route::get('/logout', 'AuthenticationController@logout')->name('logout');