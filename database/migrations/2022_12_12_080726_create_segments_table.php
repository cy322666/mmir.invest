<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('lead_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->integer('sale')->default(0);
            $table->integer('sale_invest')->default(0);
            $table->integer('sale_apart')->default(0);
            $table->integer('count_leads')->default(1);
            $table->boolean('is_double')->default(false);
            $table->string('link_double_phone')->nullable();
            $table->string('link_double_email')->nullable();
            $table->integer('count_leads_invest')->default(0);
            $table->integer('count_leads_apart')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('segments');
    }
};
