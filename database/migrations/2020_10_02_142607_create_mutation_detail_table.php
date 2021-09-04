<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMutationDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mutation_detail', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('mutation_id');
            $table->foreign('mutation_id')->references('id')->on('mutation')->onDelete('restrict');

            $table->unsignedBigInteger('receiving_detail_colly_id');
            $table->foreign('receiving_detail_colly_id')->references('id')->on('receiving_detail_colly')->onDelete('restrict');
            
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
        Schema::dropIfExists('mutation_detail');
    }
}
