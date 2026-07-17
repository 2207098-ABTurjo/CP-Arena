@extends('layouts.app')

@section('title', 'Submissions - CP-Arena')

@section('content')
<div class="page-header">
    <h1>My Submissions</h1>
    <a href="/sync-codeforces" class="btn btn-warning">Sync from Codeforces</a>
</div>

<div class="card">
    @if(count($submissions) > 0)
        <table>
            <thead>
                <tr>
                    <th>Problem</th>
                    <th>Platform</th>
                    <th>Rating</th>
                    <th>Verdict</th>
                    <th>Time</th>
                    <th>Memory</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                @foreach($submissions as $s)
                <tr>
                    <td>{{ $s->title }}</td>
                    <td><span class="badge badge-gray">{{ $s->platform }}</span></td>
                    <td><span class="badge badge-purple">{{ $s->rating ?? 'N/A' }}</span></td>
                    <td>
                        @if($s->status == 'Accepted')
                            <span class="verdict-ac">{{ $s->status }}</span>
                        @elseif($s->status == 'Wrong Answer')
                            <span class="verdict-wa">{{ $s->status }}</span>
                        @elseif($s->status == 'Time Limit Exceeded')
                            <span class="verdict-tle">{{ $s->status }}</span>
                        @elseif($s->status == 'Runtime Error')
                            <span class="verdict-re">{{ $s->status }}</span>
                        @elseif($s->status == 'Compilation Error')
                            <span class="verdict-ce">{{ $s->status }}</span>
                        @else
                            <span class="badge badge-gray">{{ $s->status }}</span>
                        @endif
                    </td>
                    <td>{{ $s->time_ms }} ms</td>
                    <td>{{ round($s->memory_kb / 1024, 1) }} MB</td>
                    <td>
                        @if($s->source == 'codeforces')
                            <span class="cf-badge">CF</span>
                        @else
                            <span class="badge badge-blue">Web</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p>No submissions yet. Go to Problems and start coding!</p>
        </div>
    @endif
</div>
@endsection