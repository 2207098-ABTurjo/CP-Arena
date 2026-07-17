@extends('layouts.app')

@section('title', 'Sign Up - CP-Arena')

@section('content')
<div class="login-box">
    <h2>Create Account</h2>

    @if($errors->any())
        @foreach($errors->all() as $error)
            <div class="alert alert-error" style="margin-bottom: 10px; padding: 8px 12px; font-size: 13px;">{{ $error }}</div>
        @endforeach
    @endif

    <form method="POST" action="/register">
        @csrf
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
        </div>
        <div class="form-group">
            <label>Codeforces Handle</label>
            <input type="text" name="cf_handle" class="form-control" placeholder="e.g. tourist" required>
            <p style="font-size: 12px; color: #7f8c8d; margin-top: 4px;">
                Your submissions will be synced automatically from Codeforces
            </p>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
        </div>
        <button type="submit" class="btn btn-success">Sign Up</button>
    </form>

    <div class="link">
        Already have an account? <a href="/login">Login here</a>
    </div>
</div>
@endsection