<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccByToReceivingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receiving', function (Blueprint $table) {
            $table->integer('acc_by')->after('updated_by')->nullable();
            $table->timestamp('acc_at')->after('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receiving', function (Blueprint $table) {
            $table->dropColumn('acc_by');
            $table->dropColumn('acc_at');
        });
    }
}
