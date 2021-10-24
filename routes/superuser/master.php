<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin', 'auth:superuser'],
    'as' => 'master.',
    'prefix' => '/master',
    'namespace' => 'Master'
], function () {

    Route::group(['middleware' => ['permission:company-manage'], 'as' => 'company.', 'prefix' => '/company'], function () {
        Route::get('/', 'CompanyController@show')->name('show');
        Route::get('/edit', 'CompanyController@edit')->name('edit');
        Route::put('/update', 'CompanyController@update')->name('update');
    });

    Route::group(['as' => 'branch_office.', 'prefix' => '/branch_office'], function () {
        Route::get('/import_template', 'BranchOfficeController@import_template')->name('import_template');
        Route::post('/import', 'BranchOfficeController@import')->name('import');
        Route::get('/export', 'BranchOfficeController@export')->name('export');
    });
    Route::resource('branch_office', 'BranchOfficeController');

    Route::group(['as' => 'store.', 'prefix' => '/store'], function () {
        Route::get('/import_template', 'StoreController@import_template')->name('import_template');
        Route::post('/import', 'StoreController@import')->name('import');
        Route::get('/export', 'StoreController@export')->name('export');
    });
    Route::resource('store', 'StoreController');

    Route::group(['as' => 'warehouse.', 'prefix' => '/warehouse'], function () {
        Route::get('/import_template', 'WarehouseController@import_template')->name('import_template');
        Route::post('/import', 'WarehouseController@import')->name('import');
        Route::get('/export', 'WarehouseController@export')->name('export');
    });
    Route::resource('warehouse', 'WarehouseController');

    Route::group(['as' => 'product.', 'prefix' => '/product'], function () {
        Route::get('/import_template', 'ProductController@import_template')->name('import_template');
        Route::post('/import', 'ProductController@import')->name('import');
        Route::get('/export', 'ProductController@export')->name('export');

        Route::group(['as' => 'min_stock.'], function () {
            Route::get('{id}/min_stock/create', 'ProductMinStockController@create')->name('create');
            Route::post('{id}/min_stock', 'ProductMinStockController@store')->name('store');
            Route::get('{id}/min_stock/{min_stock_id}/edit', 'ProductMinStockController@edit')->name('edit');
            Route::put('{id}/min_stock/{min_stock_id}', 'ProductMinStockController@update')->name('update');
            Route::delete('{id}/min_stock/{min_stock_id}', 'ProductMinStockController@destroy')->name('destroy');
        });
    });
    Route::resource('product', 'ProductController');

    Route::group(['as' => 'product_img.', 'prefix' => '/product_img'], function () {
        Route::post('/delete/{id}', 'ProductImageController@delete');
        Route::get('/restore/{parent_id}/{id}', 'ProductImageController@restore')->name('restore');
        Route::get('/restore_all/{parent_id}', 'ProductImageController@restore_all')->name('restore_all');
        Route::get('/destroy/{parent_id}/{id}', 'ProductImageController@destroy')->name('destroy');
        Route::get('/destroy_all/{parent_id}', 'ProductImageController@destroy_all')->name('destroy_all');
    });

    Route::group(['as' => 'product_category.', 'prefix' => '/product_category'], function () {
        Route::get('/import_template', 'ProductCategoryController@import_template')->name('import_template');
        Route::post('/import', 'ProductCategoryController@import')->name('import');
        Route::get('/export', 'ProductCategoryController@export')->name('export');
    });
    Route::resource('product_category', 'ProductCategoryController');

    Route::group(['as' => 'product_type.', 'prefix' => '/product_type'], function () {
        Route::get('/import_template', 'ProductTypeController@import_template')->name('import_template');
        Route::post('/import', 'ProductTypeController@import')->name('import');
        Route::get('/export', 'ProductTypeController@export')->name('export');
    });
    Route::resource('product_type', 'ProductTypeController');

    Route::group(['as' => 'unit.', 'prefix' => '/unit'], function () {
        Route::get('/import_template', 'UnitController@import_template')->name('import_template');
        Route::post('/import', 'UnitController@import')->name('import');
        Route::get('/export', 'UnitController@export')->name('export');
    });
    Route::resource('unit', 'UnitController');

    Route::group(['as' => 'customer.', 'prefix' => '/customer'], function () {
        Route::get('/import_template', 'CustomerController@import_template')->name('import_template');
        Route::post('/import', 'CustomerController@import')->name('import');
        Route::get('/export', 'CustomerController@export')->name('export');

        Route::group(['as' => 'other_address.'], function() {
            Route::get('{id}/other_address/create', 'CustomerOtherAddressController@create')->name('create');
            Route::post('{id}/other_address', 'CustomerOtherAddressController@store')->name('store');
            Route::get('{id}/other_address/{address_id}/edit', 'CustomerOtherAddressController@edit')->name('edit');
            Route::put('{id}/other_address/{address_id}', 'CustomerOtherAddressController@update')->name('update');
            Route::delete('{id}/other_address/{address_id}', 'CustomerOtherAddressController@destroy')->name('destroy');
        });
    });
    Route::resource('customer', 'CustomerController');

    Route::group(['as' => 'customer_category.', 'prefix' => '/customer_category'], function () {
        Route::get('/import_template', 'CustomerCategoryController@import_template')->name('import_template');
        Route::post('/import', 'CustomerCategoryController@import')->name('import');
        Route::get('/export', 'CustomerCategoryController@export')->name('export');
    });
    Route::resource('customer_category', 'CustomerCategoryController');

    Route::group(['as' => 'customer_type.', 'prefix' => '/customer_type'], function () {
        Route::get('/import_template', 'CustomerTypeController@import_template')->name('import_template');
        Route::post('/import', 'CustomerTypeController@import')->name('import');
        Route::get('/export', 'CustomerTypeController@export')->name('export');
    });
    Route::resource('customer_type', 'CustomerTypeController');

    Route::group(['as' => 'brand_reference.', 'prefix' => '/brand_reference'], function () {
        Route::get('/import_template', 'BrandReferenceController@import_template')->name('import_template');
        Route::post('/import', 'BrandReferenceController@import')->name('import');
        Route::get('/export', 'BrandReferenceController@export')->name('export');
    });
    Route::resource('brand_reference', 'BrandReferenceController');

    Route::group(['as' => 'sub_brand_reference.', 'prefix' => '/sub_brand_reference'], function () {
        Route::get('/import_template', 'SubBrandReferenceController@import_template')->name('import_template');
        Route::post('/import', 'SubBrandReferenceController@import')->name('import');
        Route::get('/export', 'SubBrandReferenceController@export')->name('export');
    });
    Route::resource('sub_brand_reference', 'SubBrandReferenceController');

    Route::group(['as' => 'question.', 'prefix' => '/question'], function () {
        Route::get('/import_template', 'QuestionController@import_template')->name('import_template');
        Route::post('/import', 'QuestionController@import')->name('import');
        Route::get('/export', 'QuestionController@export')->name('export');
    });
    Route::resource('question', 'QuestionController');

    Route::group(['as' => 'supplier.', 'prefix' => '/supplier'], function () {
        Route::get('/import_template', 'SupplierController@import_template')->name('import_template');
        Route::post('/import', 'SupplierController@import')->name('import');
        Route::get('/export', 'SupplierController@export')->name('export');
    });
    Route::resource('supplier', 'SupplierController');

    Route::group(['as' => 'ekspedisi.', 'prefix' => '/ekspedisi'], function () {
        Route::get('/import_template', 'EkspedisiController@import_template')->name('import_template');
        Route::post('/import', 'EkspedisiController@import')->name('import');
        Route::get('/export', 'EkspedisiController@export')->name('export');
    });
    Route::resource('ekspedisi', 'EkspedisiController');

});