<?php

use App\Support\OrderClientBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->index(['client_id', 'created_at']);
        });

        OrderClientBackfill::run();
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropIndex(['client_id', 'created_at']);
            $table->dropColumn('client_id');
        });
    }
};
