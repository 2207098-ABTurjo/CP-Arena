@extends('layouts.app')

@section('title', 'Profile - CP-Arena')

@section('content')
<div class="page-header">
    <h1>My Profile</h1>
</div>

<div class="card" style="max-width: 500px;">
    <form method="POST" action="/profile">
        @csrf
        <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-control" value="{{ $user->username }}" disabled style="background: #f8f9fa;">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" class="form-control" value="{{ $user->email }}" disabled style="background: #f8f9fa;">
        </div>
        <div class="form-group">
            <label>Codeforces Handle</label>
            <input type="text" name="cf_handle" class="form-control" 
                   value="{{ $user->cf_handle ?? '' }}" 
                   placeholder="e.g. tourist" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>

<div class="card">
    <h2>Codeforces Sync</h2>
    <p style="color: #555; margin-bottom: 16px; font-size: 14px;">
        Your handle: <strong>{{ $user->cf_handle ?? 'Not set' }}</strong>
    </p>
    <a href="/sync-codeforces" class="btn btn-success">Sync Submissions from Codeforces</a>
    <p style="font-size: 12px; color: #7f8c8d; margin-top: 10px;">
        This will fetch your last 50 submissions from Codeforces and add them here.
    </p>
</div>
@endsection