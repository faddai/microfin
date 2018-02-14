<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('firstname', 60)->index()->nullable();
            $table->string('lastname', 60)->index()->nullable();
            $table->string('middlename', 60)->nullable();
            $table->string('phone1', 20)->nullable();
            $table->string('phone2', 20)->nullable();
            $table->boolean('status')->default(1);
            $table->unsignedInteger('relationship_manager')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('clientable_id')->nullable();
            $table->string('clientable_type')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('photo')->nullable();
            $table->string('signature')->nullable();
            $table->unsignedInteger('created_by')->index()->nullable();
            $table->timestamps();

            // rel manager relationship
            $table->foreign('relationship_manager', 'fk_clients_relationship_manager')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('set null');

            $table->foreign('created_by', 'fk_clients_created_by')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
