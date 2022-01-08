<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserLatLong extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('image', 100)->nullable()->change();
            $table->string('hashtags', 100)->nullable()->change();
            $table->string('rating', 100)->nullable()->change();            
            $table->string('lat', 100)->nullable()->change();
            $table->string('long', 100)->nullable()->change();
            $table->string('usr_lat', 100)->nullable()->change();
            $table->string('usr_long', 100)->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->string('categorie_id', 100)->nullable()->change();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            //
        });
    }
}
