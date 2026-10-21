<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('state')->default('lobby');
            $table->foreignId('question_id')->nullable();
            $table->unsignedBigInteger('started_at_ms')->nullable();
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('token', 64)->unique();
            $table->string('pin', 4);
            $table->unsignedInteger('score')->default(0);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('position')->unique();
            $table->text('body');
            $table->string('image')->nullable();
            $table->json('options');
            $table->unsignedTinyInteger('correct');
            $table->unsignedSmallInteger('seconds')->default(20);
            $table->timestamps();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('choice');
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
            $table->unique(['player_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('players');
        Schema::dropIfExists('games');
    }
};
