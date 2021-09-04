<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseReparationToSaleReturnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_return', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_reparation_id')->after('delivery_order_id')->nullable();
            $table->foreign('warehouse_reparation_id')->references('id')->on('master_warehouses')->onDelete('restrict');
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
            $table->dropForeign(['warehouse_reparation_id']);
            $table->dropColumn('warehouse_reparation_id');
        });
    }
}
