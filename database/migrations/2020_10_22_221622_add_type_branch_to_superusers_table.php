<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeBranchToSuperusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->integer('type')->after('id')->nullable();

            $table->unsignedBigInteger('branch_office_id')->after('type')->nullable();
            $table->foreign('branch_office_id')->references('id')->on('master_branch_offices')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('superusers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropForeign(['branch_office_id']);
            $table->dropColumn('branch_office_id');
        });
    }
}
