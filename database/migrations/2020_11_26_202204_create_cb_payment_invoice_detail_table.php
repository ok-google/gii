<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbPaymentInvoiceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_payment_invoice_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('cb_payment_invoice_id');
            $table->foreign('cb_payment_invoice_id')->references('id')->on('cb_payment_invoice')->onDelete('cascade');

            $table->unsignedBigInteger('ppb_id');
            $table->foreign('ppb_id')->references('id')->on('ppb')->onDelete('restrict');

            $table->double('total');
            $table->double('paid');
            
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
        Schema::dropIfExists('cb_payment_invoice_detail');
    }
}
