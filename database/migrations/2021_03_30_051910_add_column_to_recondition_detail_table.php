<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToReconditionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recondition_detail', function (Blueprint $table) {
            $table->double('quantity_recondition')->after('recondition_residual_id')->default(0);
            $table->double('quantity_disposal')->after('quantity_recondition')->default(0);
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
            $table->dropColumn('quantity_recondition');
            $table->dropColumn('quantity_disposal');
        });
    }
}
