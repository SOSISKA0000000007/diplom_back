<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateListingsTable extends Migration
{
    public function up()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->string('status')->default('active')->change();
        });
    }
}
