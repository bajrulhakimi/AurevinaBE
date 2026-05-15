<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shipping')) {
            DB::statement('ALTER TABLE shipping MODIFY courier VARCHAR(100) NULL');
            DB::statement('ALTER TABLE shipping MODIFY tracking_number VARCHAR(100) NULL');

            Schema::table('shipping', function (Blueprint $table) {
                if (!Schema::hasColumn('shipping', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
            });
        }

        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('product_id')
                    ->constrained('orders')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('chat_messages') && Schema::hasColumn('chat_messages', 'order_id')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropConstrainedForeignId('order_id');
            });
        }

        if (Schema::hasTable('shipping') && Schema::hasColumn('shipping', 'updated_at')) {
            Schema::table('shipping', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
};
