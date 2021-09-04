<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReconditionDisposalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recondition_disposal', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('recondition_id');
            $table->foreign('recondition_id')->references('id')->on('recondition')->onDelete('restrict');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('master_products')->onDelete('restrict');

            $table->double('quantity')->default(0);

            $table->text('description')->nullable();

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
        Schema::dropIfExists('recondition_disposal');
    }
}
