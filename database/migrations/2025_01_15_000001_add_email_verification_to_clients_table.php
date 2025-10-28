<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('email_activation_code', 6)->nullable()->after('email_verified_at');
            $table->boolean('verify_email_required')->default(true)->after('email_activation_code');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'email_activation_code', 'verify_email_required']);
        });
    }
};