<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHppToSaleReturnDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_return_detail', function (Blueprint $table) {
            $table->double('hpp')->after('quantity')->nullable();
            $table->double('price')->after('hpp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_return_detail', function (Blueprint $table) {
            $table->dropColumn('hpp');
            $table->dropColumn('price');
        });
    }
}
