<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE GetUserStats (
                p_user_id IN NUMBER,
                total_solves OUT NUMBER,
                rating_categories OUT NUMBER,
                tag_categories OUT NUMBER
            ) AS
            BEGIN
                SELECT COUNT(*) INTO total_solves 
                FROM submissions 
                WHERE user_id = p_user_id AND status = 'Accepted';
                
                SELECT COUNT(*) INTO rating_categories 
                FROM solve_ratings 
                WHERE user_id = p_user_id;
                
                SELECT COUNT(*) INTO tag_categories 
                FROM solve_tags 
                WHERE user_id = p_user_id;
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE GetUserStats");
    }
};