<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCost123ToMarketplaceReceiptDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplace_receipt_detail', function (Blueprint $table) {
            $table->double('cost_1')->after('cost')->nullable();
            $table->double('cost_2')->after('cost_1')->nullable();
            $table->double('cost_3')->after('cost_2')->nullable();
            $table->double('payment_coa')->after('cost_3')->nullable();
            $table->double('cost_1_coa')->after('payment_coa')->nullable();
            $table->double('cost_2_coa')->after('cost_1_coa')->nullable();
            $table->double('cost_3_coa')->after('cost_2_coa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketplace_receipt_detail', function (Blueprint $table) {
            $table->dropColumn('cost_1');
            $table->dropColumn('cost_2');
            $table->dropColumn('cost_3');
            $table->dropColumn('payment_coa');
            $table->dropColumn('cost_1_coa');
            $table->dropColumn('cost_2_coa');
            $table->dropColumn('cost_3_coa');
        });
    }
}
