<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductConversionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_conversion_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('product_conversion_id');
            $table->foreign('product_conversion_id')->references('id')->on('product_conversion')->onDelete('cascade');

            $table->unsignedBigInteger('product_from');
            $table->foreign('product_from')->references('id')->on('master_products')->onDelete('restrict');

            $table->unsignedBigInteger('product_to');
            $table->foreign('product_to')->references('id')->on('master_products')->onDelete('restrict');

            $table->double('qty');

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_conversion_detail');
    }
}
