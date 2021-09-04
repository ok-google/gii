<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductIdToReconditionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recondition_detail', function (Blueprint $table) {
            $table->integer('product_id')->after('recondition_residual_id')->nullable();
            $table->text('description')->after('quantity_disposal')->nullable();
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
            $table->dropColumn('product_id');
            $table->dropColumn('description');
        });
    }
}
