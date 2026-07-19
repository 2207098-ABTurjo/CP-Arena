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
            SELECT s.sub_id, p.title, p.platform, p.rating, p.tags, s.status, s.submission_time
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

        $problem = DB::selectOne("SELECT rating, tags FROM problems WHERE problem_id = ?", [$problemId]);
        $rating = $problem->rating ?? 800;
        $tags = $problem->tags ?? 'general';

        $verdict = $this->simulateJudge($rating);

        DB::statement("
            BEGIN
                sp_add_submission(:user_id, :problem_id, :status, :rating, :tags);
            END;
        ", [
            'user_id' => $userId,
            'problem_id' => $problemId,
            'status' => $verdict,
            'rating' => $rating,
            'tags' => $tags,
        ]);

        if ($verdict == 'Accepted') {
            $this->updateIndividualTags($userId, $tags);
        }

        DB::statement("BEGIN sp_generate_recommendations(:user_id); END;", ['user_id' => $userId]);

        $msg = $verdict == 'Accepted' ? 'Problem solved!' : 'Verdict: ' . $verdict;
        return redirect('/submissions')->with('success', $msg);
    }

    private function updateIndividualTags($userId, $tagsString)
    {
        $tagsArray = array_map('trim', explode(',', $tagsString));
        
        foreach ($tagsArray as $tag) {
            if (empty($tag)) continue;
            
            $exists = DB::selectOne("SELECT COUNT(*) as cnt FROM solve_tags WHERE user_id = ? AND tags = ?", [$userId, $tag]);
            
            if ($exists->cnt > 0) {
                DB::update("UPDATE solve_tags SET solved_count = solved_count + 1 WHERE user_id = ? AND tags = ?", [$userId, $tag]);
            } else {
                DB::insert("INSERT INTO solve_tags (user_id, tags, solved_count) VALUES (?, ?, 1)", [$userId, $tag]);
            }
        }
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