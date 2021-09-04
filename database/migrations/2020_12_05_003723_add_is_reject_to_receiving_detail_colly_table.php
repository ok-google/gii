<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsRejectToReceivingDetailCollyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receiving_detail_colly', function (Blueprint $table) {
            $table->integer('is_reject')->after('quantity_colly')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receiving_detail_colly', function (Blueprint $table) {
            $table->dropColumn('is_reject');
        });
    }
}
