<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashFlowSaldoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_flow_saldo', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('periode_id');
            $table->foreign('periode_id')->references('id')->on('journal_periode')->onDelete('restrict');

            $table->double('beginning_balance');

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
        Schema::dropIfExists('cash_flow_saldo');
    }
}
