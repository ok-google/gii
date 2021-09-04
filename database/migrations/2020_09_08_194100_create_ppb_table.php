<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePpbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppb', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('master_supplier')->onDelete('restrict');

            $table->text('address')->nullable();

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('master_warehouses')->onDelete('restrict');

            $table->integer('transaction_type');

            $table->unsignedBigInteger('sea_freight')->nullable();
            $table->foreign('sea_freight')->references('id')->on('master_ekspedisi')->onDelete('restrict');

            $table->unsignedBigInteger('local_freight')->nullable();
            $table->foreign('local_freight')->references('id')->on('master_ekspedisi')->onDelete('restrict');

            $table->integer('edit_counter')->default(0);

            $table->double('grand_total_rmb')->nullable();
            $table->double('grand_total_idr')->nullable();

            $table->text('description')->nullable();

            $table->integer('status');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ppb');
    }
}
