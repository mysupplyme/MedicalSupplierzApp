<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('whats_app_messages', function (Blueprint $table) {
            $table->id();
            $table->string('from_number');
            $table->text('message_text')->nullable();
            $table->string('message_type')->default('text');
            $table->text('response_sent')->nullable();
            $table->json('webhook_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('whats_app_messages');
    }
};