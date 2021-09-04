<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceivingDetailCollyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receiving_detail_colly', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();

            $table->unsignedBigInteger('receiving_detail_id');
            $table->foreign('receiving_detail_id')->references('id')->on('receiving_detail')->onDelete('restrict');

            $table->double('quantity_ri');
            $table->double('quantity_colly');

            $table->integer('status_qc')->default(0);
            $table->double('quantity_mutation')->default(0);
            $table->double('quantity_recondition')->default(0);

            $table->integer('status_mutation')->default(0);
            $table->integer('status_recondition')->default(0);

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
        Schema::dropIfExists('receiving_detail_colly');
    }
}
