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
        Schema::table('job_ratings', function (Blueprint $table) {
            if (!Schema::hasColumn('job_ratings', 'source')) {
                $table->string('source')->nullable();
            }
            $table->dropUnique(['user_id', 'job_id']);
            $table->unique(['user_id', 'source', 'job_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_ratings', function (Blueprint $table) {
            $table->dropColumn('source');
            $table->dropUnique(['user_id', 'source', 'job_id']);
        });
    }
};
