<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContainerToPpbDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ppb_detail', function (Blueprint $table) {
            $table->string('no_container')->after('no_urut')->nullable();
            $table->string('qty_container')->after('no_container')->nullable();
            $table->string('colly_qty')->after('qty_container')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ppb_detail', function (Blueprint $table) {
            $table->dropColumn('no_container');
            $table->dropColumn('qty_container');
            $table->dropColumn('colly_qty');
        });
    }
}
