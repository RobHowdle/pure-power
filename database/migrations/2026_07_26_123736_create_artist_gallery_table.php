<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_gallery', function (Blueprint $table) {
            $table->id();

            $table->foreignId('artist_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('image');
            $table->string('thumbnail')->nullable();

            $table->string('caption')->nullable();
            $table->string('photographer')->nullable();

            $table->boolean('featured')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artist_gallery');
    }
};