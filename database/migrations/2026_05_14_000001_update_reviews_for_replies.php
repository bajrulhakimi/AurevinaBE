<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE reviews MODIFY order_item_id BIGINT UNSIGNED NULL');

        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }

            if (!Schema::hasColumn('reviews', 'admin_reply')) {
                $table->text('admin_reply')->nullable()->after('review');
            }

            if (!Schema::hasColumn('reviews', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('admin_reply');
            }

            if (!Schema::hasColumn('reviews', 'replied_by')) {
                $table->foreignId('replied_by')
                    ->nullable()
                    ->after('replied_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE reviews MODIFY order_item_id BIGINT UNSIGNED NOT NULL');

        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'replied_by')) {
                $table->dropConstrainedForeignId('replied_by');
            }

            foreach (['replied_at', 'admin_reply', 'updated_at'] as $column) {
                if (Schema::hasColumn('reviews', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
