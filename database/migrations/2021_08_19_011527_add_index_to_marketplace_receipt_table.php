<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToMarketplaceReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplace_receipt', function (Blueprint $table) {
            $table->index('code');
            $table->foreign('code')->references('code')->on('sales_order')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketplace_receipt', function (Blueprint $table) {
            $table->dropIndex(['code']);
            $table->dropForeign(['code']);
        });
    }
}
