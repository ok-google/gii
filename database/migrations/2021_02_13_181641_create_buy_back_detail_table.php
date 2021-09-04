<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyBackDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buy_back_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('buy_back_id');
            $table->foreign('buy_back_id')->references('id')->on('buy_back')->onDelete('restrict');

            $table->unsignedBigInteger('sales_order_detail_id');
            $table->foreign('sales_order_detail_id')->references('id')->on('sales_order_detail')->onDelete('restrict');

            $table->double('buy_back_price');
            $table->double('buy_back_qty');
            $table->double('buy_back_total');

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
        Schema::dropIfExists('buy_back_detail');
    }
}
