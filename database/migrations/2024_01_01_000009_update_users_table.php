<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('specialty_id')->nullable()->constrained('specialties')->onDelete('set null');
            $table->foreignId('sub_specialty_id')->nullable()->constrained('sub_specialties')->onDelete('set null');
            $table->foreignId('residency_country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->foreignId('nationality_country_id')->nullable()->constrained('countries')->onDelete('set null');
            $table->dropColumn(['speciality', 'sub_speciality']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('speciality')->nullable();
            $table->string('sub_speciality')->nullable();
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['sub_specialty_id']);
            $table->dropForeign(['residency_country_id']);
            $table->dropForeign(['nationality_country_id']);
            $table->dropColumn(['specialty_id', 'sub_specialty_id', 'residency_country_id', 'nationality_country_id']);
        });
    }
};