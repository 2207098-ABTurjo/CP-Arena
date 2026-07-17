<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SubmissionController extends Controller
{
    public function index()
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $userId = Session::get('user_id');

        $submissions = DB::select("
            SELECT s.sub_id, p.title, p.platform, p.rating, p.tags, s.status, s.time_ms, s.memory_kb, s.submission_time
            FROM submissions s
            JOIN problems p ON s.problem_id = p.problem_id
            WHERE s.user_id = ?
            ORDER BY s.submission_time DESC
        ", [$userId]);

        return view('submissions', ['submissions' => $submissions]);
    }

    public function create($problem_id)
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $problem = DB::selectOne("SELECT * FROM problems WHERE problem_id = ?", [$problem_id]);

        if (!$problem) {
            return redirect('/problems')->with('error', 'Problem not found');
        }

        return view('submit_code', ['problem' => $problem]);
    }

    public function store(Request $request)
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $request->validate([
            'problem_id' => 'required|integer',
            'code' => 'required',
        ]);

        $userId = Session::get('user_id');
        $problemId = $request->problem_id;
        $code = $request->code;

        $problem = DB::selectOne("SELECT rating, tags FROM problems WHERE problem_id = ?", [$problemId]);
        $rating = $problem->rating ?? 800;
        $tags = $problem->tags ?? 'general';

        $verdict = $this->simulateJudge($rating);

        $timeMs = rand(100, 2000);
        $memoryKb = rand(1000, 256000);

        // Call PL/SQL procedure sp_add_submission
        DB::statement("
            BEGIN
                sp_add_submission(:user_id, :problem_id, :status, :code, :time_ms, :memory_kb, :rating, :tags);
            END;
        ", [
            'user_id' => $userId,
            'problem_id' => $problemId,
            'status' => $verdict,
            'code' => $code,
            'time_ms' => $timeMs,
            'memory_kb' => $memoryKb,
            'rating' => $rating,
            'tags' => $tags,
        ]);

        // Call PL/SQL procedure sp_generate_recommendations
        DB::statement("BEGIN sp_generate_recommendations(:user_id); END;", ['user_id' => $userId]);

        $msg = $verdict == 'Accepted' ? 'Problem solved!' : 'Verdict: ' . $verdict;
        return redirect('/submissions')->with('success', $msg);
    }

    private function simulateJudge($rating)
    {
        $baseChance = max(15, 85 - (($rating - 800) / 1200) * 65);
        $roll = rand(1, 100);

        if ($roll <= $baseChance) {
            return 'Accepted';
        }

        $verdicts = ['Wrong Answer', 'Time Limit Exceeded', 'Runtime Error', 'Compilation Error'];
        return $verdicts[array_rand($verdicts)];
    }
}