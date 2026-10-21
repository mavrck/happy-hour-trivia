<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('key')->nullable()->unique();
            $table->string('category')->nullable();
        });

        Schema::table('games', function (Blueprint $table) {
            $table->json('question_ids')->nullable();
            $table->unsignedSmallInteger('question_count')->default(20);
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['question_ids', 'question_count']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['key', 'category']);
        });
    }
};
