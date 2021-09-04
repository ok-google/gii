<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterCoaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_coa', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code');

            $table->integer('type')->nullable();

            $table->unsignedBigInteger('branch_office_id')->nullable();
            $table->foreign('branch_office_id')->references('id')->on('master_branch_offices')->onDelete('restrict');

            $table->string('name');

            $table->integer('group');

            $table->unsignedBigInteger('parent_level_1')->nullable();
            $table->foreign('parent_level_1')->references('id')->on('master_coa')->onDelete('restrict');

            $table->unsignedBigInteger('parent_level_2')->nullable();
            $table->foreign('parent_level_2')->references('id')->on('master_coa')->onDelete('restrict');

            $table->unsignedBigInteger('parent_level_3')->nullable();
            $table->foreign('parent_level_3')->references('id')->on('master_coa')->onDelete('restrict');

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
        Schema::dropIfExists('master_coa');
    }
}
