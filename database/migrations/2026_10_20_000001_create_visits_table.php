<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('path', 2048);
            // Daily-rotating hash of IP + user agent: counts unique visitors
            // without storing the raw IP address.
            $table->string('visitor_hash', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('referrer', 2048)->nullable();
            $table->timestamp('created_at')->index();

            $table->index(['visitor_hash', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
