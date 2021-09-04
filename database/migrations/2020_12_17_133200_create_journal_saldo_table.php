<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalSaldoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal_saldo', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('periode_id');
            $table->foreign('periode_id')->references('id')->on('journal_periode')->onDelete('restrict');

            $table->unsignedBigInteger('coa_id');
            $table->foreign('coa_id')->references('id')->on('master_coa')->onDelete('restrict');

            $table->integer('position');

            $table->double('saldo');

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
        Schema::dropIfExists('journal_saldo');
    }
}
