@extends('layouts.app')

@section('title', 'Recommendations - CP-Arena')

@section('content')
<div class="page-header">
    <h1>Recommended Problems</h1>
</div>

<div class="card">
    <p style="margin-bottom: 16px; color: #7f8c8d; font-size: 14px;">
        These problems are recommended based on your weakest tags and current rating range.
    </p>

    @if(count($recommendations) > 0)
        <table>
            <thead>
                <tr>
                    <th>Problem</th>
                    <th>Rating</th>
                    <th>Tags</th>
                    <th>Platform</th>
                    <th>Recommended On</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recommendations as $r)
                <tr>
                    <td><a href="/problems/{{ $r->problem_id }}" style="color: #3498db; text-decoration: none;">{{ $r->title }}</a></td>
                    <td><span class="badge badge-purple">{{ $r->rating }}</span></td>
                    <td>
                        <div class="tag-list">
                            @foreach(explode(',', $r->tags) as $tag)
                                <span class="tag-chip">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td><span class="badge badge-gray">{{ $r->platform }}</span></td>
                    <td style="color: #7f8c8d; font-size: 13px;">{{ $r->rec_date }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p>No recommendations available yet. Solve some problems first!</p>
        </div>
    @endif
</div>
@endsection