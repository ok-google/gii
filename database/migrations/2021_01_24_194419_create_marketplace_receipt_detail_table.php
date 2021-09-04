<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketplaceReceiptDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_receipt_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('marketplace_receipt_id');
            $table->foreign('marketplace_receipt_id')->references('id')->on('marketplace_receipt')->onDelete('restrict');

            $table->double('payment');
            $table->double('cost')->nullable();

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
        Schema::dropIfExists('marketplace_receipt_detail');
    }
}
