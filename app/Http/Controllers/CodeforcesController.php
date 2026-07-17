<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class CodeforcesController extends Controller
{
    public function sync()
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $userId = Session::get('user_id');
        $user = DB::selectOne("SELECT cf_handle FROM users WHERE user_id = ?", [$userId]);

        if (!$user || !$user->cf_handle) {
            return redirect('/home')->with('error', 'Please set your Codeforces handle first in profile settings.');
        }

        $handle = $user->cf_handle;

        // Fetch submissions from Codeforces API
        $response = Http::get("https://codeforces.com/api/user.status?handle={$handle}&from=1&count=50");

        if (!$response->successful()) {
            return redirect('/home')->with('error', 'Failed to fetch Codeforces data. Please check your handle.');
        }

        $submissions = $response->json()['result'] ?? [];
        $syncedCount = 0;

        foreach ($submissions as $sub) {
            $verdict = $sub['verdict'] ?? 'UNKNOWN';
            $problem = $sub['problem'] ?? [];
            $contestId = $problem['contestId'] ?? '';
            $index = $problem['index'] ?? '';
            $problemName = $contestId . $index . ' - ' . ($problem['name'] ?? 'Unknown');

            // Map Codeforces verdict to our status
            $status = $this->mapVerdict($verdict);

            // Find problem in our database
            $dbProblem = DB::selectOne("SELECT problem_id, rating, tags FROM problems WHERE cf_contest_id = ? AND cf_index = ?", [$contestId, $index]);

            if (!$dbProblem) {
                // Add problem if not exists
                $rating = $problem['rating'] ?? 800;
                $tags = implode(',', $problem['tags'] ?? []);
                
                DB::insert('INSERT INTO problems (title, rating, tags, platform, cf_contest_id, cf_index) VALUES (?, ?, ?, ?, ?, ?)', [
                    $problemName,
                    $rating,
                    $tags,
                    'Codeforces',
                    (string)$contestId,
                    $index
                ]);
                
                $dbProblem = DB::selectOne("SELECT problem_id, rating, tags FROM problems WHERE cf_contest_id = ? AND cf_index = ?", [$contestId, $index]);
            }

            // Check if already synced
            $existing = DB::selectOne("SELECT COUNT(*) as cnt FROM submissions WHERE user_id = ? AND problem_id = ? AND status = ?", [
                $userId, $dbProblem->problem_id, $status
            ]);

            if ($existing->cnt == 0 && $status != 'In Queue') {
                // Insert via PL/SQL
                DB::statement("
                    BEGIN
                        sp_add_submission(:user_id, :problem_id, :status, :code, :time_ms, :memory_kb, :rating, :tags);
                    END;
                ", [
                    'user_id' => $userId,
                    'problem_id' => $dbProblem->problem_id,
                    'status' => $status,
                    'code' => '// Synced from Codeforces',
                    'time_ms' => $sub['timeConsumedMillis'] ?? 0,
                    'memory_kb' => ($sub['memoryConsumedBytes'] ?? 0) / 1024,
                    'rating' => $dbProblem->rating ?? 800,
                    'tags' => $dbProblem->tags ?? 'general',
                ]);

                $syncedCount++;
            }
        }

        // Regenerate recommendations
        DB::statement("BEGIN sp_generate_recommendations(:user_id); END;", ['user_id' => $userId]);

        return redirect('/submissions')->with('success', "Synced {$syncedCount} submissions from Codeforces!");
    }

    private function mapVerdict($cfVerdict)
    {
        $map = [
            'OK' => 'Accepted',
            'WRONG_ANSWER' => 'Wrong Answer',
            'TIME_LIMIT_EXCEEDED' => 'Time Limit Exceeded',
            'MEMORY_LIMIT_EXCEEDED' => 'Memory Limit Exceeded',
            'RUNTIME_ERROR' => 'Runtime Error',
            'COMPILATION_ERROR' => 'Compilation Error',
            'PRESENTATION_ERROR' => 'Wrong Answer',
            'IDLENESS_LIMIT_EXCEEDED' => 'Time Limit Exceeded',
            'CHALLENGED' => 'Wrong Answer',
            'SKIPPED' => 'Skipped',
            'TESTING' => 'In Queue',
        ];

        return $map[$cfVerdict] ?? 'Unknown';
    }
}