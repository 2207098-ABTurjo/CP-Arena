@extends('layouts.app')

@section('title', 'Dashboard - CP-Arena')

@section('content')
<div class="page-header">
    <h1>Welcome back, {{ $username }}!</h1>
    <div>
        <a href="/profile" class="btn btn-primary btn-sm">Profile</a>
        <a href="/sync-codeforces" class="btn btn-success btn-sm">Sync CF</a>
    </div>
</div>

<div class="card" style="background: #e8f4f8;">
    <p style="font-size: 14px; color: #2980b9;">
        Codeforces handle: <strong>{{ $cf_handle ?? 'Not set' }}</strong> | 
        <a href="/sync-codeforces" style="color: #2980b9;">Sync submissions</a>
    </p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Total Solves</div>
        <div class="number">{{ $total_solves }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Rating Categories</div>
        <div class="number">{{ $rating_cats }}</div>
    </div>
    <div class="stat-card">
        <div class="label">Tag Categories</div>
        <div class="number">{{ $tag_cats }}</div>
    </div>
</div>

@if(count($rating_dist) > 0)
<div class="card">
    <h2>Rating Distribution</h2>
    <table>
        <thead>
            <tr>
                <th>Rating</th>
                <th>Solved</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rating_dist as $r)
            <tr>
                <td><span class="badge badge-purple">{{ $r->rating }}</span></td>
                <td>{{ $r->solved_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(count($tag_dist) > 0)
<div class="card">
    <h2>Tag Distribution</h2>
    <table>
        <thead>
            <tr>
                <th>Tag</th>
                <th>Solved</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tag_dist as $t)
            <tr>
                <td><span class="tag-chip">{{ $t->tags }}</span></td>
                <td>{{ $t->solved_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="card">
    <h2>Recent Submissions</h2>
    @if(count($recent_subs) > 0)
        <table>
            <thead>
                <tr>
                    <th>Problem</th>
                    <th>Platform</th>
                    <th>Verdict</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_subs as $sub)
                <tr>
                    <td>{{ $sub->title }}</td>
                    <td><span class="badge badge-gray">{{ $sub->platform }}</span></td>
                    <td>
                        @if($sub->status == 'Accepted')
                            <span class="verdict-ac">{{ $sub->status }}</span>
                        @elseif($sub->status == 'Wrong Answer')
                            <span class="verdict-wa">{{ $sub->status }}</span>
                        @elseif($sub->status == 'Time Limit Exceeded')
                            <span class="verdict-tle">{{ $sub->status }}</span>
                        @else
                            <span class="verdict-re">{{ $sub->status }}</span>
                        @endif
                    </td>
                    <td style="color: #7f8c8d; font-size: 13px;">{{ $sub->submission_time }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p>No submissions yet. Start solving problems!</p>
        </div>
    @endif
</div>

<div class="card">
    <h2>Recommended For You</h2>
    @if(count($recommendations) > 0)
        <table>
            <thead>
                <tr>
                    <th>Problem</th>
                    <th>Rating</th>
                    <th>Tags</th>
                    <th>Platform</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recommendations as $rec)
                <tr>
                    <td><a href="/problems/{{ $rec->problem_id }}" style="color: #3498db; text-decoration: none;">{{ $rec->title }}</a></td>
                    <td><span class="badge badge-purple">{{ $rec->rating }}</span></td>
                    <td>
                        <div class="tag-list">
                            @foreach(explode(',', $rec->tags) as $tag)
                                <span class="tag-chip">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td><span class="badge badge-gray">{{ $rec->platform }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p>No recommendations yet. Add some submissions first!</p>
        </div>
    @endif
</div>
@endsection