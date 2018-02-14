<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropClientIdAndDescriptionFromCollateralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collaterals', function (Blueprint $table) {
            $table->dropForeign('fk_collaterals_client_id');
            $table->dropColumn(['client_id', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collaterals', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable();
            $table->string('description')->nullable();

            $table->foreign('client_id', 'fk_collaterals_client_id')->references('id')->on('clients')
                ->onUpdate('cascade')->onDelete('set null');
        });
    }
}
