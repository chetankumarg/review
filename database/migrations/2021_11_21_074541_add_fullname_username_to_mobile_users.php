<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFullnameUsernameToMobileUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mobile_users', function (Blueprint $table) {
            //
            $table->dropColumn('last_name');
            $table->dropColumn('picture');
            $table->renameColumn('first_name', 'full_name');
            $table->string('user_name',200)->after('first_name');
            $table->string('profile_picture',200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mobile_users', function (Blueprint $table) {
            //
        });
    }
}
