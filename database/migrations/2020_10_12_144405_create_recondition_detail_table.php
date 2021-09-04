<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReconditionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recondition_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('recondition_id');
            $table->foreign('recondition_id')->references('id')->on('recondition')->onDelete('restrict');

            $table->unsignedBigInteger('receiving_detail_colly_id')->nullable();
            $table->foreign('receiving_detail_colly_id')->references('id')->on('receiving_detail_colly')->onDelete('restrict');

            $table->unsignedBigInteger('quality_control2_id')->nullable();
            $table->foreign('quality_control2_id')->references('id')->on('quality_control2')->onDelete('restrict');

            $table->unsignedBigInteger('sale_return_detail_id')->nullable();
            $table->foreign('sale_return_detail_id')->references('id')->on('sale_return_detail')->onDelete('restrict');

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
        Schema::dropIfExists('recondition_detail');
    }
}
