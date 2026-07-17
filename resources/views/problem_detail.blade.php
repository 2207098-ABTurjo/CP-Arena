@extends('layouts.app')

@section('title', $problem->title . ' - CP-Arena')

@section('content')
<div class="page-header">
    <h1>{{ $problem->title }}</h1>
    <div>
        @if($solved)
            <span class="badge badge-green" style="margin-right: 8px;">Solved</span>
        @endif
        <a href="/submit/{{ $problem->problem_id }}" class="btn btn-success">Submit Code</a>
    </div>
</div>

<div class="card">
    <div class="problem-meta" style="margin-bottom: 16px;">
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

    @if($problem->cf_contest_id && $problem->cf_index)
    <div style="background: #e8f4f8; padding: 20px; border-radius: 8px; margin-bottom: 16px; text-align: center;">
        <p style="color: #1565c0; font-size: 14px; margin-bottom: 12px;">
            Read the full problem statement, input/output format, and examples on Codeforces
        </p>
        <a href="https://codeforces.com/problemset/problem/{{ $problem->cf_contest_id }}/{{ $problem->cf_index }}" 
           target="_blank" 
           class="btn btn-primary" style="padding: 10px 24px;">
            Open Problem on Codeforces &rarr;
        </a>
    </div>
    @endif
</div>

<div class="card">
    <h2>Submit Your Solution</h2>
    <p style="color: #555; margin-bottom: 16px; font-size: 14px;">
        After reading the problem on Codeforces, write your solution here and submit.
    </p>
    <a href="/submit/{{ $problem->problem_id }}" class="btn btn-success" style="padding: 12px 32px; font-size: 16px;">Go to Code Editor</a>
</div>

<div style="text-align: center; margin-top: 24px;">
    <a href="/problems" class="btn" style="background: #95a5a6; color: white;">Back to Problems</a>
</div>
@endsection