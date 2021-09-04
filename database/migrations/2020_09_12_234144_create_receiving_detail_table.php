<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivingDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiving_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('receiving_id');
            $table->foreign('receiving_id')->references('id')->on('receiving')->onDelete('restrict');

            $table->unsignedBigInteger('ppb_id');
            $table->foreign('ppb_id')->references('id')->on('ppb')->onDelete('restrict');

            $table->unsignedBigInteger('ppb_detail_id');
            $table->foreign('ppb_detail_id')->references('id')->on('ppb_detail')->onDelete('restrict');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('master_products')->onDelete('restrict');

            $table->integer('quantity')->nullable();

            $table->double('total_quantity_ri')->nullable();
            $table->double('total_quantity_colly')->nullable();

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
        Schema::dropIfExists('receiving_detail');
    }
}
