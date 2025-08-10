<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('client_subscriptions', 'original_transaction_id')) {
                $table->string('original_transaction_id')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('client_subscriptions', 'expires_date')) {
                $table->timestamp('expires_date')->nullable()->after('end_at');
            }
            if (!Schema::hasColumn('client_subscriptions', 'auto_renew_status')) {
                $table->boolean('auto_renew_status')->default(true)->after('expires_date');
            }
        });
    }

    public function down()
    {
        Schema::table('client_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['original_transaction_id', 'product_id', 'expires_date', 'auto_renew_status']);
        });
    }
};