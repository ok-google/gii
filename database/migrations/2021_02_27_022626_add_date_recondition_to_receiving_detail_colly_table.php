<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateReconditionToReceivingDetailCollyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receiving_detail_colly', function (Blueprint $table) {
            $table->timestamp('date_recondition')->after('description')->nullable();
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
            $table->dropColumn('date_recondition');
        });
    }
}
