<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoaCreditToMarketplaceReceiptDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketplace_receipt_detail', function (Blueprint $table) {
            $table->double('credit_coa')->after('cost_3_coa')->nullable();
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
            $table->dropColumn('credit_coa');
        });
    }
}
