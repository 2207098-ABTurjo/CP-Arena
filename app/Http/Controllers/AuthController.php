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
        ]);

        DB::insert('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)', [
            $request->username,
            $request->email,
            Hash::make($request->password),
            'user'
        ]);

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

    public function home()
    {
        if (!Session::has('user_id')) {
            return redirect('/login');
        }

        return view('home', ['username' => Session::get('username')]);
    }
}