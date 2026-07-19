<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'cf_handle' => 'required|string|max:50',
        ]);

        DB::insert('INSERT INTO users (username, email, password, cf_handle, role) VALUES (?, ?, ?, ?, ?)', [
            $request->username,
            $request->email,
            Hash::make($request->password),
            $request->cf_handle,
            'user'
        ]);

        $user = DB::selectOne("SELECT user_id FROM users WHERE username = ?", [$request->username]);

        DB::statement("BEGIN sp_generate_recommendations(:user_id); END;", ['user_id' => $user->user_id]);

        return redirect('/login')->with('success', 'Account created! Please login.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = DB::select('SELECT * FROM users WHERE username = ?', [$request->username]);

        if (!empty($user) && Hash::check($request->password, $user[0]->password)) {
            Session::put('user_id', $user[0]->user_id);
            Session::put('username', $user[0]->username);
            Session::put('role', $user[0]->role);
            return redirect('/home');
        }

        return back()->with('error', 'Invalid username or password');
    }

    public function logout()
    {
        Session::flush();
        return redirect('/login');
    }

    public function profile()
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $userId = Session::get('user_id');
        $user = DB::selectOne("SELECT user_id, username, email, cf_handle FROM users WHERE user_id = ?", [$userId]);

        return view('profile', ['user' => $user]);
    }

    public function updateProfile(Request $request)
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $userId = Session::get('user_id');

        $request->validate([
            'cf_handle' => 'required|string|max:50',
        ]);

        DB::update("UPDATE users SET cf_handle = ? WHERE user_id = ?", [
            $request->cf_handle,
            $userId
        ]);

        return redirect('/profile')->with('success', 'Profile updated successfully!');
    }

    public function home()
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        $userId = Session::get('user_id');

        $totalSolves = DB::selectOne("
            SELECT COUNT(*) as cnt FROM submissions WHERE user_id = ? AND status = 'Accepted'
        ", [$userId])->cnt ?? 0;

        $ratingCats = DB::selectOne("
            SELECT COUNT(*) as cnt FROM solve_ratings WHERE user_id = ?
        ", [$userId])->cnt ?? 0;

        $tagCats = DB::selectOne("
            SELECT COUNT(*) as cnt FROM solve_tags WHERE user_id = ?
        ", [$userId])->cnt ?? 0;

        $ratingDist = [];
        try {
            $result = DB::select("
                DECLARE
                    c SYS_REFCURSOR;
                BEGIN
                    sp_get_rating_distribution(:user_id, c);
                    :cursor := c;
                END;
            ", [
                'user_id' => $userId,
                'cursor' => ['value' => null, 'type' => \PDO::PARAM_STMT],
            ]);
            
            if (isset($result['cursor']) && $result['cursor'] instanceof \PDOStatement) {
                while ($row = $result['cursor']->fetch(\PDO::FETCH_ASSOC)) {
                    $ratingDist[] = (object)$row;
                }
                $result['cursor']->closeCursor();
            }
        } catch (\Exception $e) {
            $ratingDist = DB::select("SELECT rating, solved_count FROM solve_ratings WHERE user_id = ? ORDER BY rating ASC", [$userId]);
        }

        $tagDist = [];
        try {
            $result = DB::select("
                DECLARE
                    c SYS_REFCURSOR;
                BEGIN
                    sp_get_tag_distribution(:user_id, c);
                    :cursor := c;
                END;
            ", [
                'user_id' => $userId,
                'cursor' => ['value' => null, 'type' => \PDO::PARAM_STMT],
            ]);
            
            if (isset($result['cursor']) && $result['cursor'] instanceof \PDOStatement) {
                while ($row = $result['cursor']->fetch(\PDO::FETCH_ASSOC)) {
                    $tagDist[] = (object)$row;
                }
                $result['cursor']->closeCursor();
            }
        } catch (\Exception $e) {
            $tagDist = DB::select("SELECT tags, solved_count FROM solve_tags WHERE user_id = ? ORDER BY solved_count DESC", [$userId]);
        }

        $recentSubs = DB::select("
            SELECT * FROM (
                SELECT s.sub_id, p.title, p.platform, s.status, s.submission_time
                FROM submissions s
                JOIN problems p ON s.problem_id = p.problem_id
                WHERE s.user_id = ?
                ORDER BY s.submission_time DESC
            ) WHERE ROWNUM <= 5
        ", [$userId]);

        $recommendations = DB::select("
            SELECT p.problem_id, p.title, p.rating, p.tags, p.platform
            FROM recommendations r
            JOIN problems p ON r.problem_id = p.problem_id
            WHERE r.user_id = ?
            ORDER BY r.rec_date DESC
        ", [$userId]);

        $user = DB::selectOne("SELECT cf_handle FROM users WHERE user_id = ?", [$userId]);

        return view('home', [
            'username' => Session::get('username'),
            'cf_handle' => $user->cf_handle ?? null,
            'total_solves' => $totalSolves,
            'rating_cats' => $ratingCats,
            'tag_cats' => $tagCats,
            'rating_dist' => $ratingDist,
            'tag_dist' => $tagDist,
            'recent_subs' => $recentSubs,
            'recommendations' => $recommendations,
        ]);
    }
}