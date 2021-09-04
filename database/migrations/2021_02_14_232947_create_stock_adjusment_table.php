<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAdjusmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adjusment', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();

            $table->integer('type')->nullable();

            $table->unsignedBigInteger('branch_office_id')->nullable();
            $table->foreign('branch_office_id')->references('id')->on('master_branch_offices')->onDelete('restrict');

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('master_warehouses')->onDelete('restrict');

            $table->integer('minus')->default(0);

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
        Schema::dropIfExists('stock_adjusment');
    }
}
