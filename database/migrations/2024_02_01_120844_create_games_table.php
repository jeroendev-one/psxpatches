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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title_id')->unique();
            $table->string('name');
            $table->string('current_version')->nullable()->default(null);
            $table->string('content_id')->nullable()->default(null);
            $table->string('region');
            $table->string('publisher')->nullable()->default('Unknown');
            $table->string('icon')->nullable()->default(null);
            $table->string('background')->nullable()->default(null);
            $table->bigInteger('latest_patch_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
