<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePpbDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppb_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('ppb_id');
            $table->foreign('ppb_id')->references('id')->on('ppb')->onDelete('restrict');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('master_products')->onDelete('restrict');

            $table->integer('quantity')->nullable();
            $table->double('unit_price')->nullable();
            $table->double('local_freight_cost')->nullable();
            $table->double('total_price_rmb')->nullable();

            $table->double('kurs')->nullable();
            $table->double('sea_freight')->nullable();
            $table->double('local_freight')->nullable();
            $table->double('total_price_idr')->nullable();

            $table->integer('no_urut')->nullable();

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
        Schema::dropIfExists('ppb_detail');
    }
}
