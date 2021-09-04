<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMutationDisplayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mutation_display', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();

            $table->unsignedBigInteger('warehouse_from');
            $table->foreign('warehouse_from')->references('id')->on('master_warehouses')->onDelete('restrict');

            $table->unsignedBigInteger('warehouse_to');
            $table->foreign('warehouse_to')->references('id')->on('master_warehouses')->onDelete('restrict');

            $table->text('description')->nullable();

            $table->integer('status');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('acc_by')->nullable();

            $table->timestamps();
            $table->timestamp('acc_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mutation_display');
    }
}
