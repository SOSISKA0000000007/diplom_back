<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoordinatesToListingsTable extends Migration
{
    public function up()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('latitude', 9, 6)->nullable()->after('address');
            $table->decimal('longitude', 9, 6)->nullable()->after('latitude');
        });
    }

    public function down()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
}
