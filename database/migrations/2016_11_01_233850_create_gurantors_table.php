<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGurantorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gurantors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 60)->nullable();
            $table->string('work_phone', 15)->nullable();
            $table->string('personal_phone', 15)->nullable();
            $table->integer('years_known')->nullable();
            $table->string('employer')->nullable();
            $table->string('job_title')->nullable();
            $table->string('email', 60)->nullable();
            $table->text('residential_address')->nullable();
            $table->unsignedInteger('client_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gurantors');
    }
}
