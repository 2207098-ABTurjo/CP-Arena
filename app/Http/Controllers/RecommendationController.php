<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RecommendationController extends Controller
{
    public function index()
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $userId = Session::get('user_id');

        // Call PL/SQL procedure sp_generate_recommendations
        DB::statement("BEGIN sp_generate_recommendations(:user_id); END;", ['user_id' => $userId]);

        // Get recommendations
        $recommendations = DB::select("
            SELECT p.problem_id, p.title, p.rating, p.tags, p.platform, r.rec_date
            FROM recommendations r
            JOIN problems p ON r.problem_id = p.problem_id
            WHERE r.user_id = ?
            ORDER BY r.rec_date DESC
        ", [$userId]);

        return view('recommendations', ['recommendations' => $recommendations]);
    }
}