<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropClientableSpecificColumnsFromClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['firstname', 'lastname', 'middlename', 'dob', 'gender', 'photo', 'signature']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string("firstname", 60)->index()->nullable();
            $table->string("lastname", 60)->index()->nullable();
            $table->string("middlename", 60)->nullable();
            $table->date("dob")->nullable();
            $table->string("gender")->nullable();
            $table->string("photo")->nullable();
            $table->string("signature")->nullable();
        });
    }
}
