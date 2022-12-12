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
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('webinar_name')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
            $table->string('')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webinars');
    }
};
