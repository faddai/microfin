<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPhotoAndSignatureColumnsFromIndividualClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('individual_clients', function (Blueprint $table) {
            $table->dropColumn(['photo', 'signature']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('individual_clients', function (Blueprint $table) {
            $table->string('photo')->nullable();
            $table->string('signature')->nullable();
        });
    }
}
