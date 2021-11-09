<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('reviews');
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('mobile_user_id', 100);
            $table->text('description');
            $table->string('image', 200);
            $table->string('lat', 100);
            $table->string('long', 100);
            $table->string('rating', 100);
            $table->string('categorie_id', 100);
            $table->string('publish', 100);
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
        Schema::dropIfExists('reviews');
    }
}
