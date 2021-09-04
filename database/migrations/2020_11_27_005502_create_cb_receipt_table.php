<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCbReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cb_receipt', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code');

            $table->integer('type')->nullable();

            $table->unsignedBigInteger('branch_office_id')->nullable();
            $table->foreign('branch_office_id')->references('id')->on('master_branch_offices')->onDelete('restrict');

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
        Schema::dropIfExists('cb_receipt');
    }
}
