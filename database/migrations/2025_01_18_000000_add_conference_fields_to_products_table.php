<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('conference_register_link')->nullable();
            $table->text('conference_speakers_trainers')->nullable();
            $table->datetime('conference_datetime')->nullable();
            $table->integer('conference_duration')->nullable(); // in minutes
            $table->string('conference_venue')->nullable();
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'conference_register_link',
                'conference_speakers_trainers', 
                'conference_datetime',
                'conference_duration',
                'conference_venue'
            ]);
        });
    }
};