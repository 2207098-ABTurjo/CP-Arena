@extends('layouts.app')

@section('title', 'Submit Code - CP-Arena')

@section('content')
<div class="page-header">
    <h1>Submit Solution</h1>
</div>

<div class="card">
    <div class="problem-meta">
        <span class="badge badge-purple">Rating: {{ $problem->rating ?? 'N/A' }}</span>
        <span class="badge badge-gray">Platform: {{ $problem->platform }}</span>
        <div class="tag-list">
            @if($problem->tags)
                @foreach(explode(',', $problem->tags) as $tag)
                    <span class="tag-chip">{{ trim($tag) }}</span>
                @endforeach
            @endif
        </div>
    </div>
    <h2 style="margin-top: 12px; border: none; padding: 0;">{{ $problem->title }}</h2>
</div>

<div class="card">
    <form method="POST" action="/submissions">
        @csrf
        <input type="hidden" name="problem_id" value="{{ $problem->problem_id }}">
        
        <div class="form-group">
            <label>Your Code</label>
            <textarea name="code" class="code-editor" placeholder="// Write your solution here...
#include <bits/stdc++.h>
using namespace std;

int main() {
    
    return 0;
}" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">Submit Code</button>
        <a href="/problems/{{ $problem->problem_id }}" class="btn" style="background: #95a5a6; color: white; margin-left: 8px;">View Problem</a>
    </form>
</div>
@endsection