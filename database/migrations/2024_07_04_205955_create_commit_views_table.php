<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW commits_view AS
            SELECT
                COUNT(*) AS number_commits,
                DATE(created_at) AS created_at_date,
                repository_id,
                author_name
            FROM
                commits
            GROUP BY
                DATE(created_at),
                author_name,
                repository_id
            ORDER BY
                created_at_date
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS commits_view");
    }
};
