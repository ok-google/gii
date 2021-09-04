<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScanByToDeliveryOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_order_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('scan_by')->after('status_validate')->nullable();
            $table->foreign('scan_by')->references('id')->on('superusers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_order_detail', function (Blueprint $table) {
            $table->dropForeign(['scan_by']);
            $table->dropColumn('scan_by');
        });
    }
}
