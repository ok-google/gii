<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbReceiptInvoiceDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_receipt_invoice_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('cb_receipt_invoice_id');
            $table->foreign('cb_receipt_invoice_id')->references('id')->on('cb_receipt_invoice')->onDelete('cascade');

            $table->unsignedBigInteger('sales_order_id');
            $table->foreign('sales_order_id')->references('id')->on('sales_order')->onDelete('restrict');

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
        Schema::dropIfExists('cb_receipt_invoice_detail');
    }
}
