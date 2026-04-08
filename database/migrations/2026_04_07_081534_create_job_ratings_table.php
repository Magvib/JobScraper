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
        Schema::create('job_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('job_id');
            
            $table->float('skills_match')->nullable();
            $table->string('skills_match_reasoning', 1000)->nullable();
            $table->float('experience_relevance')->nullable();
            $table->string('experience_relevance_reasoning', 1000)->nullable();
            $table->float('seniority_fit')->nullable();
            $table->string('seniority_fit_reasoning', 1000)->nullable();
            $table->float('keyword_match')->nullable();
            $table->string('keyword_match_reasoning', 1000)->nullable();

            $table->float('rating')->nullable();
            
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            
            $table->timestamps();

            $table->unique(['user_id', 'job_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_ratings');
    }
};
