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

        // Procedure 2: Add submission (solve_ratings bookkeeping is now handled
        // by trg_after_submission_insert below, so this procedure just inserts).
        $pdo->exec("
            CREATE OR REPLACE PROCEDURE sp_add_submission (
                p_user_id IN NUMBER,
                p_problem_id IN NUMBER,
                p_status IN VARCHAR2,
                p_rating IN NUMBER,
                p_tags IN VARCHAR2
            ) AS
            BEGIN
                INSERT INTO submissions (user_id, problem_id, status, submission_time)
                VALUES (p_user_id, p_problem_id, p_status, SYSTIMESTAMP);
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

        // Function 1 (converted from Procedure 4): Get rating-wise solve distribution.
        // Same single-cursor output, expressed as a RETURN instead of an OUT param.
        $pdo->exec("
            CREATE OR REPLACE FUNCTION fn_get_rating_distribution (
                p_user_id IN NUMBER
            ) RETURN SYS_REFCURSOR AS
                result_cursor SYS_REFCURSOR;
            BEGIN
                OPEN result_cursor FOR
                    SELECT rating, solved_count
                    FROM solve_ratings
                    WHERE user_id = p_user_id
                    ORDER BY rating ASC;
                RETURN result_cursor;
            END;
        ");

        // Function 2 (converted from Procedure 5): Get tag-wise solve distribution.
        // Same single-cursor output, expressed as a RETURN instead of an OUT param.
        $pdo->exec("
            CREATE OR REPLACE FUNCTION fn_get_tag_distribution (
                p_user_id IN NUMBER
            ) RETURN SYS_REFCURSOR AS
                result_cursor SYS_REFCURSOR;
            BEGIN
                OPEN result_cursor FOR
                    SELECT tags, solved_count
                    FROM solve_tags
                    WHERE user_id = p_user_id
                    ORDER BY solved_count DESC;
                RETURN result_cursor;
            END;
        ");

        // Trigger: after a submission is inserted, keep solve_ratings in sync.
        // This is the same logic that used to live inline in sp_add_submission —
        // moved here so it fires automatically on any insert into submissions,
        // not just ones that go through the procedure.
        $pdo->exec("
            CREATE OR REPLACE TRIGGER trg_after_submission_insert
            AFTER INSERT ON submissions
            FOR EACH ROW
            WHEN (NEW.status = 'Accepted')
            DECLARE
                v_rating NUMBER;
                v_count  NUMBER;
            BEGIN
                SELECT rating INTO v_rating
                FROM problems
                WHERE problem_id = :NEW.problem_id;

                SELECT COUNT(*) INTO v_count FROM solve_ratings
                WHERE user_id = :NEW.user_id AND rating = v_rating;

                IF v_count > 0 THEN
                    UPDATE solve_ratings
                    SET solved_count = solved_count + 1
                    WHERE user_id = :NEW.user_id AND rating = v_rating;
                ELSE
                    INSERT INTO solve_ratings (user_id, rating, solved_count)
                    VALUES (:NEW.user_id, v_rating, 1);
                END IF;
            EXCEPTION
                WHEN NO_DATA_FOUND THEN
                    NULL; -- problem_id not found; nothing to update
            END;
        ");
    }

    public function down(): void
    {
        $pdo = DB::connection()->getPdo();
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_get_user_stats'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_add_submission'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP PROCEDURE sp_generate_recommendations'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP FUNCTION fn_get_rating_distribution'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP FUNCTION fn_get_tag_distribution'; EXCEPTION WHEN OTHERS THEN NULL; END;");
        $pdo->exec("BEGIN EXECUTE IMMEDIATE 'DROP TRIGGER trg_after_submission_insert'; EXCEPTION WHEN OTHERS THEN NULL; END;");
    }
};