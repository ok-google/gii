<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccByToPpbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ppb', function (Blueprint $table) {
            $table->double('kurs')->after('transaction_type')->nullable();
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
        Schema::table('ppb', function (Blueprint $table) {
            $table->dropColumn('kurs');
            $table->dropColumn('acc_by');
            $table->dropColumn('acc_at');
        });
    }
}
