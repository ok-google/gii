<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbPaymentInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_payment_invoice', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code');

            $table->integer('type')->nullable();

            $table->unsignedBigInteger('branch_office_id')->nullable();
            $table->foreign('branch_office_id')->references('id')->on('master_branch_offices')->onDelete('restrict');

            $table->unsignedBigInteger('coa_id');
            $table->foreign('coa_id')->references('id')->on('master_coa')->onDelete('cascade');

            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('master_supplier')->onDelete('restrict');
            
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
        Schema::dropIfExists('cb_payment_invoice');
    }
}
