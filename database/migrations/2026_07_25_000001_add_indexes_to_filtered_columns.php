<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds indexes to columns that are filtered or ordered by the application's
 * stats/query methods but were previously un-indexed. Foreign keys and unique
 * slugs are already indexed; this covers the remaining hot columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // PostService::stats() / scopePublished() filter on status;
            // published feeds order by published_at.
            $table->index(['status', 'published_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            // CategoryService::stats() filters on is_active.
            $table->index('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            // UserService::stats() filters on created_at (today / this week).
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
