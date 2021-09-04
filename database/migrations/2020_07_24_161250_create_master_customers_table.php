<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_customers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('master_customer_categories')->onDelete('restrict');

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('master_customer_types')->onDelete('restrict');

            $table->string('code')->unique();
            $table->string('name');
            $table->string('store')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->text('address');
            $table->text('address_do')->nullable();

            $table->string('owner_name')->nullable();
            $table->string('website')->nullable();
            $table->decimal('plafon_piutang', 16, 4)->default(0);

            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();

            $table->string('text_provinsi')->nullable();
            $table->string('text_kota')->nullable();
            $table->string('text_kecamatan')->nullable();
            $table->string('text_kelurahan')->nullable();

            $table->string('zipcode')->nullable();

            $table->string('image_store')->nullable();

            $table->boolean('notification_email')->default(false);

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
        Schema::dropIfExists('master_customers');
    }
}
