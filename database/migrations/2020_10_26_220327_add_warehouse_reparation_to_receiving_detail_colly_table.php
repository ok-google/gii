<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarehouseReparationToReceivingDetailCollyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receiving_detail_colly', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_reparation_id')->after('status_recondition')->nullable();
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
        Schema::table('receiving_detail_colly', function (Blueprint $table) {
            $table->dropForeign(['warehouse_reparation_id']);
            $table->dropColumn('warehouse_reparation_id');
        });
    }
}
