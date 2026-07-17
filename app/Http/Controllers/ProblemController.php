<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProblemController extends Controller
{
    public function index(Request $request)
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $query = "SELECT * FROM problems WHERE 1=1";
        $params = [];

        if ($request->filled('tag')) {
            $query .= " AND tags LIKE ?";
            $params[] = '%' . $request->tag . '%';
        }

        if ($request->filled('platform')) {
            $query .= " AND platform = ?";
            $params[] = $request->platform;
        }

        if ($request->filled('min_rating')) {
            $query .= " AND rating >= ?";
            $params[] = $request->min_rating;
        }

        if ($request->filled('max_rating')) {
            $query .= " AND rating <= ?";
            $params[] = $request->max_rating;
        }

        $sort = $request->get('sort', 'rating_asc');
        switch ($sort) {
            case 'rating_desc':
                $query .= " ORDER BY rating DESC NULLS LAST";
                break;
            case 'title_asc':
                $query .= " ORDER BY title ASC";
                break;
            case 'title_desc':
                $query .= " ORDER BY title DESC";
                break;
            default:
                $query .= " ORDER BY rating ASC NULLS LAST";
        }

        $problems = DB::select($query, $params);

        // Get all unique tags for filter dropdown
        $allTags = DB::select("SELECT DISTINCT tags FROM problems WHERE tags IS NOT NULL");
        $tagList = [];
        foreach ($allTags as $t) {
            $parts = explode(',', $t->tags);
            foreach ($parts as $part) {
                $tag = trim($part);
                if ($tag && !in_array($tag, $tagList)) {
                    $tagList[] = $tag;
                }
            }
        }
        sort($tagList);

        // Get all platforms
        $platforms = DB::select("SELECT DISTINCT platform FROM problems WHERE platform IS NOT NULL");

        return view('problems', [
            'problems' => $problems,
            'tagList' => $tagList,
            'platforms' => $platforms,
            'filters' => $request->all(),
        ]);
    }

    public function show($id)
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $problem = DB::selectOne("SELECT * FROM problems WHERE problem_id = ?", [$id]);

        if (!$problem) {
            return redirect('/problems')->with('error', 'Problem not found');
        }

        // Check if user has solved this
        $userId = Session::get('user_id');
        $solved = DB::selectOne("
            SELECT COUNT(*) as cnt FROM submissions 
            WHERE user_id = ? AND problem_id = ? AND status = 'Accepted'
        ", [$userId, $id]);

        return view('problem_detail', [
            'problem' => $problem,
            'solved' => ($solved->cnt ?? 0) > 0
        ]);
    }
}