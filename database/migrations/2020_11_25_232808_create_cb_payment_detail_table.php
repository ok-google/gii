<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbPaymentDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_payment_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('cb_payment_id');
            $table->foreign('cb_payment_id')->references('id')->on('cb_payment')->onDelete('cascade');

            $table->unsignedBigInteger('coa_id');
            $table->foreign('coa_id')->references('id')->on('master_coa')->onDelete('cascade');
            
            $table->string('name');

            $table->double('total');

            $table->integer('status_transaction');
            
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
        Schema::dropIfExists('cb_payment_detail');
    }
}
