<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The admin dashboard counts activity by created_at (today / this week) and
 * loads the newest rows with ORDER BY created_at DESC LIMIT 6. activity_log had
 * no index on created_at, so each of those queries scanned the whole table,
 * which keeps growing with every logged change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
