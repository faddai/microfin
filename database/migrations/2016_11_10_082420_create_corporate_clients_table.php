<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorporateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporate_clients', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date_of_incorporation')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('nature_of_business')->nullable();
            $table->string('company_ownership_type')->nullable();
            $table->string('statement_frequency')->nullable();
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
        Schema::dropIfExists('corporate_clients');
    }
}
