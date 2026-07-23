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
        Schema::create('gigs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('ticket_url')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_hidden')->default(false);
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gigs');
    }
};