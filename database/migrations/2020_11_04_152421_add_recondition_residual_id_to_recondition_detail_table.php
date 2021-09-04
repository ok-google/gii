<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReconditionResidualIdToReconditionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recondition_detail', function (Blueprint $table) {
            $table->unsignedBigInteger('recondition_residual_id')->after('sale_return_detail_id')->nullable();
            $table->foreign('recondition_residual_id')->references('id')->on('recondition_residual')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recondition_detail', function (Blueprint $table) {
            $table->dropForeign(['recondition_residual_id']);
            $table->dropColumn('recondition_residual_id');
        });
    }
}
