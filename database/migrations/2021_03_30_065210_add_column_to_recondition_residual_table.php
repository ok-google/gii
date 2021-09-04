<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToReconditionResidualTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recondition_residual', function (Blueprint $table) {
            $table->string('type_text')->after('warehouse_reparation_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recondition_residual', function (Blueprint $table) {
            $table->dropColumn('type_text');
        });
    }
}
