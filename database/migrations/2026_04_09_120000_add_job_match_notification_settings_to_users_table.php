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
        Schema::table('users', function (Blueprint $table) {
            $table->float('notify_skills_match_threshold')->nullable();
            $table->float('notify_experience_relevance_threshold')->nullable();
            $table->float('notify_seniority_fit_threshold')->nullable();
            $table->float('notify_keyword_match_threshold')->nullable();
            $table->enum('notify_match_mode', ['any', 'all'])->default('any');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'notify_skills_match_threshold',
                'notify_experience_relevance_threshold',
                'notify_seniority_fit_threshold',
                'notify_keyword_match_threshold',
                'notify_match_mode',
            ]);
        });
    }
};
