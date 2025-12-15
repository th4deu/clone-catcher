<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clone_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('domain')->index();
            $table->string('url', 500);
            $table->string('client_ip')->nullable();
            $table->string('client_user_agent', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('screen_resolution')->nullable();
            $table->string('language')->nullable();
            $table->json('requests')->nullable();
            $table->timestamp('client_timestamp')->nullable();
            $table->timestamps();

            $table->index(['domain', 'created_at']);
            $table->index('client_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clone_logs');
    }
};
