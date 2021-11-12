<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            //table info
            $table->integer('room');

            //start
            $table->integer('month');
            $table->integer('day');
            $table->integer('hour');
            $table->integer('minute');

            //length
            $table->integer('hour_finish');
            $table->integer('minute_finish');

            //user info
            $table->string('email')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meetings');
    }
}
