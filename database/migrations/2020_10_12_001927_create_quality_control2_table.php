<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQualityControl2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quality_control2', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('master_warehouses')->onDelete('restrict');

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('master_products')->onDelete('restrict');

            $table->double('quantity')->default(0);

            $table->text('description')->nullable();

            $table->integer('status_recondition')->default(0);

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
        Schema::dropIfExists('quality_control2');
    }
}
