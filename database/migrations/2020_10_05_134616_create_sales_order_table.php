<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();
            $table->string('marketplace_order')->nullable();

            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('master_warehouses')->onDelete('restrict');

            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('master_customers')->onDelete('restrict');

            $table->string('customer_marketplace')->nullable();

            $table->text('address_marketplace')->nullable();

            $table->unsignedBigInteger('ekspedisi_id')->nullable();
            $table->foreign('ekspedisi_id')->references('id')->on('master_ekspedisi')->onDelete('restrict');

            $table->string('ekspedisi_marketplace')->nullable();

            $table->string('resi')->nullable();
            $table->double('total')->nullable();
            $table->double('tax')->nullable();
            $table->double('discount')->nullable();
            $table->double('shipping_fee')->nullable();

            $table->double('grand_total')->nullable();

            $table->text('description')->nullable();

            $table->integer('status_sales_order')->default(0);

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
        Schema::dropIfExists('sales_order');
    }
}
