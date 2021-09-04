<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoaIdToPpbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ppb', function (Blueprint $table) {
            $table->unsignedBigInteger('coa_id')->after('transaction_type')->nullable();
            $table->foreign('coa_id')->references('id')->on('master_coa')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ppb', function (Blueprint $table) {
            $table->dropForeign(['coa_id']);
            $table->dropColumn('coa_id');
        });
    }
}
