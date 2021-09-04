<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();
            $table->string('name');

            $table->unsignedBigInteger('brand_reference_id');
            $table->foreign('brand_reference_id')->references('id')->on('master_brand_references')->onDelete('restrict');

            $table->unsignedBigInteger('sub_brand_reference_id');
            $table->foreign('sub_brand_reference_id')->references('id')->on('master_sub_brand_references')->onDelete('restrict');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('master_product_categories')->onDelete('restrict');

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('master_product_types')->onDelete('restrict');

            $table->text('description')->nullable();

            $table->decimal('quantity', 16, 4)->default(0);

            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('master_units')->onDelete('restrict');

            $table->integer('status');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_products');
    }
}
