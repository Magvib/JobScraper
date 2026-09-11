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
        if (!Schema::hasColumn('job_ratings', 'source')) {
            Schema::table('job_ratings', function (Blueprint $table) {
                $table->string('source')->nullable();
            });
        }

        // Add the replacement index before dropping the old one: MySQL needs
        // an index leading with user_id to serve the foreign key constraint,
        // so each statement runs in its own ALTER to guarantee the order.
        if (!Schema::hasIndex('job_ratings', ['user_id', 'source', 'job_id'], 'unique')) {
            Schema::table('job_ratings', function (Blueprint $table) {
                $table->unique(['user_id', 'source', 'job_id']);
            });
        }

        if (Schema::hasIndex('job_ratings', ['user_id', 'job_id'], 'unique')) {
            Schema::table('job_ratings', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'job_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original unique index before dropping the source
        // column, so the user_id foreign key always has a backing index.
        if (!Schema::hasIndex('job_ratings', ['user_id', 'job_id'], 'unique')) {
            Schema::table('job_ratings', function (Blueprint $table) {
                $table->unique(['user_id', 'job_id']);
            });
        }

        if (Schema::hasIndex('job_ratings', ['user_id', 'source', 'job_id'], 'unique')) {
            Schema::table('job_ratings', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'source', 'job_id']);
            });
        }

        Schema::table('job_ratings', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};