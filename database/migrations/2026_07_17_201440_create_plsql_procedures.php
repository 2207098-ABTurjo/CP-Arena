<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pdo = DB::connection()->getPdo();

        // Procedure 1: Get user dashboard stats
        $pdo->exec("
            CREATE OR REPLACE PROCEDURE sp_get_user_stats (
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

        // Procedure 2: Add submission and update stats (simplified)
        $pdo->exec("
            CREATE OR REPLACE PROCEDURE sp_add_submission (
                p_user_id IN NUMBER,
                p_problem_id IN NUMBER,
                p_status IN VARCHAR2,
                p_rating IN NUMBER,
                p_tags IN VARCHAR2
            ) AS
                v_count NUMBER;
            BEGIN
                INSERT INTO submissions (user_id, problem_id, status, submission_time)
                VALUES (p_user_id, p_problem_id, p_status, SYSTIMESTAMP);

                IF p_status = 'Accepted' THEN
                    SELECT COUNT(*) INTO v_count FROM solve_ratings
                    WHERE user_id = p_user_id AND rating = p_rating;

                    IF v_count > 0 THEN
                        UPDATE solve_ratings
                        SET solved_count = solved_count + 1
                        WHERE user_id = p_user_id AND rating = p_rating;
                    ELSE
                        INSERT INTO solve_ratings (user_id, rating, solved_count)
                        VALUES (p_user_id, p_rating, 1);
                    END IF;
                END IF;
            END;
        ");

        // Procedure 3: Generate recommendations
        $pdo->exec("
            CREATE OR REPLACE PROCEDURE sp_generate_recommendations (
                p_user_id IN NUMBER
            ) AS
                v_user_max_rating NUMBER;
                v_weak_tag VARCHAR2(100);
                v_problem_count NUMBER;
                v_has_submissions NUMBER;
            BEGIN
                DELETE FROM recommendations WHERE user_id = p_user_id;

                SELECT COUNT(*) INTO v_has_submissions FROM submissions WHERE user_id = p_user_id;

                BEGIN
                    SELECT MAX(rating) INTO v_user_max_rating
                    FROM solve_ratings
                    WHERE user_id = p_user_id;
                EXCEPTION
                    WHEN NO_DATA_FOUND THEN
                        v_user_max_rating := 800;
                END;

                IF v_user_max_rating IS NULL THEN
                    v_user_max_rating := 800;
                END IF;

                BEGIN
                    SELECT tags INTO v_weak_tag FROM (
                        SELECT tags FROM solve_tags
                        WHERE user_id = p_user_id
                        ORDER BY solved_count ASC
                    ) WHERE ROWNUM = 1;
                EXCEPTION
                    WHEN NO_DATA_FOUND THEN
                        v_weak_tag := NULL;
                END;

                IF v_weak_tag IS NULL THEN
                    BEGIN
                        SELECT tags INTO v_weak_tag FROM (
                            SELECT DISTINCT tags FROM problems
                            WHERE tags IS NOT NULL
                            AND ROWNUM <= 10
                        ) WHERE ROWNUM = 1;
                    EXCEPTION
                        WHEN NO_DATA_FOUND THEN
                            v_weak_tag := 'implementation';
                    END;
                END IF;

                IF v_weak_tag IS NULL THEN
                    v_weak_tag := 'implementation';
                END IF;

                SELECT COUNT(*) INTO v_problem_count FROM problems
                WHERE tags LIKE '%' || v_weak_tag || '%'
                AND rating BETWEEN v_user_max_rating - 200 AND v_user_max_rating + 200
                AND problem_id NOT IN (
                    SELECT problem_id FROM submissions WHERE user_id = p_user_id
                );

                IF v_problem_count = 0 THEN
                    SELECT COUNT(*) INTO v_problem_count FROM problems
                    WHERE tags LIKE '%' || v_weak_tag || '%'
                    AND rating BETWEEN v_user_max_rating - 500 AND v_user_max_rating + 500
                    AND problem_id NOT IN (
                        SELECT problem_id FROM submissions WHERE user_id = p_user_id
                    );
                END IF;

                IF v_problem_count = 0 THEN
                    SELECT COUNT(*) INTO v_problem_count FROM problems
                    WHERE problem_id NOT IN (
                        SELECT problem_id FROM submissions WHERE user_id = p_user_id
                    );
                END IF;

                IF v_problem_count > 0 THEN
                    FOR rec IN (
                        SELECT problem_id FROM problems
                        WHERE tags LIKE '%' || v_weak_tag || '%'
                        AND rating BETWEEN v_user_max_rating - 200 AND v_user_max_rating + 200
                        AND problem_id NOT IN (
                            SELECT problem_id FROM submissions WHERE user_id = p_user_id
                        )
                        AND ROWNUM <= 5
                    ) LOOP
                        INSERT INTO recommendations (user_id, problem_id, rec_date)
                        VALUES (p_user_id, rec.problem_id, SYSDATE);
                    END LOOP;

                    SELECT COUNT(*) INTO v_problem_count FROM recommendations WHERE user_id = p_user_id;
                    IF v_problem_count < 5 THEN
                        FOR rec IN (
                            SELECT problem_id FROM problems
                            WHERE tags LIKE '%' || v_weak_tag || '%'
                            AND rating BETWEEN v_user_max_rating - 500 AND v_user_max_rating + 500
                            AND problem_id NOT IN (
                                SELECT problem_id FROM submissions WHERE user_id = p_user_id
                            )
                            AND problem_id NOT IN (
                                SELECT problem_id FROM recommendations WHERE user_id = p_user_id
                            )
                            AND ROWNUM <= 5
                        ) LOOP
                            INSERT INTO recommendations (user_id, problem_id, rec_date)
                            VALUES (p_user_id, rec.problem_id, SYSDATE);
                        END LOOP;
                    END IF;

                    SELECT COUNT(*) INTO v_problem_count FROM recommendations WHERE user_id = p_user_id;
                    IF v_problem_count < 5 THEN
                        FOR rec IN (
                            SELECT problem_id FROM problems
                            WHERE problem_id NOT IN (
                                SELECT problem_id FROM submissions WHERE user_id = p_user_id
                            )
                            AND problem_id NOT IN (
                                SELECT problem_id FROM recommendations WHERE user_id = p_user_id
                            )
                            AND ROWNUM <= 5
                        ) LOOP
                            INSERT INTO recommendations (user_id, problem_id, rec_date)
                            VALUES (p_user_id, rec.problem_id, SYSDATE);
                        END LOOP;
                    END IF;
                ELSE
                    FOR rec IN (
                        SELECT problem_id FROM problems
                        WHERE ROWNUM <= 5
                    ) LOOP
                        INSERT INTO recommendations (user_id, problem_id, rec_date)
                        VALUES (p_user_id, rec.problem_id, SYSDATE);
                    END LOOP;
                END IF;
            END;
        ");

        // Procedure 4: Get rating-wise solve distribution
        $pdo->exec("
            CREATE OR REPLACE PROCEDURE sp_get_rating_distribution (
                p_user_id IN NUMBER,
                result_cursor OUT SYS_REFCURSOR
            ) AS
            BEGIN
                OPEN result_cursor FOR
                    SELECT rating, solved_count
                    FROM solve_ratings
                    WHERE user_id = p_user_id
                    ORDER BY rating ASC;
            END;
        ");

        // Procedure 5: Get tag-wise solve distribution
        $pdo->exec("
            CREATE OR REPLACE PROCEDURE sp_get_tag_distribution (
                p_user_id IN NUMBER,
                result_cursor OUT SYS_REFCURSOR
            ) AS
            BEGIN
                OPEN result_cursor FOR
                    SELECT tags, solved_count
                    FROM solve_tags
                    WHERE user_id = p_user_id
                    ORDER BY solved_count DESC;
            END;
        ");
    }

    public function down(): void
    {
        $pdo = DB::connection()->getPdo();
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_get_user_stats'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_add_submission'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_generate_recommendations'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_get_rating_distribution'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_get_tag_distribution'; EXCEPTION WHEN OTHERS THEN NULL; END;");
    }
};