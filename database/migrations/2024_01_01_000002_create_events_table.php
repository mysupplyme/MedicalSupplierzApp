<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['workshop', 'webinar', 'conference', 'exhibition']);
            $table->text('description');
            $table->date('date');
            $table->time('time');
            $table->integer('duration'); // in minutes
            $table->decimal('price', 8, 2);
            $table->string('image');
            $table->string('speaker');
            $table->integer('capacity');
            $table->integer('registered')->default(0);
            $table->json('tags');
            $table->enum('status', ['upcoming', 'ongoing', 'completed'])->default('upcoming');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};