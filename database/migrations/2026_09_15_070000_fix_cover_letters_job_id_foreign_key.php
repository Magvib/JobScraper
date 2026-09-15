<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The job_id foreign key previously referenced the default "jobs"
     * table (the queue table shipped with Laravel). It should reference
     * "posts" instead, where the scraped job listings live.
     */
    public function up(): void
    {
        Schema::table('cover_letters', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->foreign('job_id')
                ->references('id')
                ->on('posts')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cover_letters', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->foreignId('job_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};