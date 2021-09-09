<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_setting', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            
            $table->unsignedBigInteger('debet_coa');
            $table->foreign('debet_coa')->references('id')->on('master_coa')->onDelete('restrict');

            $table->string('debet_note')->nullable();

            $table->unsignedBigInteger('credit_coa');
            $table->foreign('credit_coa')->references('id')->on('master_coa')->onDelete('restrict');

            $table->string('credit_note')->nullable();

            $table->integer('status');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journal_setting');
    }
}
