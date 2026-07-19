<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class CodeforcesSyncController extends Controller
{
    public function sync()
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $userId = Session::get('user_id');
        $cfHandle = Session::get('cf_handle');

        if (empty($cfHandle)) {
            return redirect('/profile')->with('error', 'Please set your Codeforces handle first!');
        }

        try {
            $response = Http::get("https://codeforces.com/api/user.status?handle={$cfHandle}&from=1&count=50");

            if (!$response->successful()) {
                return redirect('/submissions')->with('error', 'Failed to fetch from Codeforces API');
            }

            $data = $response->json();
            $submissions = $data['result'] ?? [];

            $syncedCount = 0;

            foreach ($submissions as $sub) {
                $cfContestId = $sub['problem']['contestId'] ?? '';
                $cfIndex = $sub['problem']['index'] ?? '';
                $verdict = $this->mapCfVerdict($sub['verdict'] ?? '');
                $timeMs = $sub['timeConsumedMillis'] ?? 0;
                $memoryKb = $sub['memoryConsumedBytes'] ?? 0;
                $memoryKb = intval($memoryKb / 1024);

                if (empty($cfContestId) || empty($cfIndex)) continue;

                $problem = DB::selectOne("
                    SELECT * FROM problems
                    WHERE cf_contest_id = ? AND cf_index = ?
                ", [$cfContestId, $cfIndex]);

                if (!$problem) {
                    $title = $cfContestId . $cfIndex . ' - ' . ($sub['problem']['name'] ?? 'Unknown');
                    $rating = $sub['problem']['rating'] ?? 800;
                    $tags = implode(',', $sub['problem']['tags'] ?? []);
                    $platform = 'Codeforces';

                    DB::insert("
                        INSERT INTO problems (title, rating, tags, platform, cf_contest_id, cf_index)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$title, $rating, $tags, $platform, $cfContestId, $cfIndex]);

                    $problem = DB::selectOne("
                        SELECT * FROM problems WHERE cf_contest_id = ? AND cf_index = ?
                    ", [$cfContestId, $cfIndex]);
                }

                $exists = DB::selectOne("
                    SELECT COUNT(*) as cnt FROM submissions
                    WHERE user_id = ? AND problem_id = ? AND source = 'codeforces'
                ", [$userId, $problem->problem_id]);

                if ($exists->cnt > 0) continue;

                if ($verdict) {
                    DB::statement("
                        BEGIN
                            sp_sync_cf_submission(:user_id, :problem_id, :status, :time_ms, :memory_kb, :rating, :tags);
                        END;
                    ", [
                        'user_id' => $userId,
                        'problem_id' => $problem->problem_id,
                        'status' => $verdict,
                        'time_ms' => $timeMs,
                        'memory_kb' => $memoryKb,
                        'rating' => $problem->rating ?? 800,
                        'tags' => $problem->tags ?? 'general',
                    ]);

                    $syncedCount++;
                }
            }

            DB::statement("BEGIN sp_generate_recommendations(:user_id); END;", ['user_id' => $userId]);

            return redirect('/submissions')->with('success', "Synced {$syncedCount} submissions from Codeforces!");

        } catch (\Exception $e) {
            return redirect('/submissions')->with('error', 'Error syncing: ' . $e->getMessage());
        }
    }

    private function mapCfVerdict($cfVerdict)
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
        ];

        return $map[$cfVerdict] ?? '';
    }
}