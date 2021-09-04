<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryOrderIdForeignToSaleReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_return', function (Blueprint $table) {
            $table->dropForeign(['delivery_order_id']);

            $table->foreign('delivery_order_id')->references('id')->on('delivery_order_detail')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_return', function (Blueprint $table) {
            //
        });
    }
}
