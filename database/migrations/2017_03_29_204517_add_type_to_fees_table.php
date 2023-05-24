<?php

use App\Entities\Fee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->string('type', 15)->default(Fee::PERCENTAGE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
