@extends('layouts.app')

@section('title', 'Login - CP-Arena')

@section('content')
<div class="login-box">
    <h2>CP-Arena Login</h2>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom: 16px;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="/login">
        @csrf
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <div class="link">
        New user? <a href="/register">Create account</a>
    </div>
</div>
@endsection