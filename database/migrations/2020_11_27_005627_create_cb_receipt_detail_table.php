<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbReceiptDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_receipt_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('cb_receipt_id');
            $table->foreign('cb_receipt_id')->references('id')->on('cb_receipt')->onDelete('cascade');

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
        Schema::dropIfExists('cb_receipt_detail');
    }
}
