@extends('layouts.app')

@section('title', 'Problems - CP-Arena')

@section('content')
<div class="page-header">
    <h1>Problem List</h1>
    <span style="color: #7f8c8d; font-size: 14px;">{{ count($problems) }} problems loaded</span>
</div>

<div class="card">
    <form method="GET" action="/problems">
        <div class="filter-bar">
            <div class="form-group">
                <label>Tag</label>
                <select name="tag" class="form-control">
                    <option value="">All tags</option>
                    @foreach($tagList as $tag)
                        <option value="{{ $tag }}" {{ ($filters['tag'] ?? '') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Platform</label>
                <select name="platform" class="form-control">
                    <option value="">All platforms</option>
                    @foreach($platforms as $p)
                        <option value="{{ $p->platform }}" {{ ($filters['platform'] ?? '') == $p->platform ? 'selected' : '' }}>{{ $p->platform }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Min Rating</label>
                <input type="number" name="min_rating" class="form-control" placeholder="e.g. 800" value="{{ $filters['min_rating'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Max Rating</label>
                <input type="number" name="max_rating" class="form-control" placeholder="e.g. 2000" value="{{ $filters['max_rating'] ?? '' }}">
            </div>
            <div class="form-group">
                <label>Sort By</label>
                <select name="sort" class="form-control">
                    <option value="rating_asc" {{ ($filters['sort'] ?? 'rating_asc') == 'rating_asc' ? 'selected' : '' }}>Rating (Low to High)</option>
                    <option value="rating_desc" {{ ($filters['sort'] ?? '') == 'rating_desc' ? 'selected' : '' }}>Rating (High to Low)</option>
                    <option value="title_asc" {{ ($filters['sort'] ?? '') == 'title_asc' ? 'selected' : '' }}>Title (A-Z)</option>
                    <option value="title_desc" {{ ($filters['sort'] ?? '') == 'title_desc' ? 'selected' : '' }}>Title (Z-A)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-bottom: 0;">Filter</button>
            <a href="/problems" class="btn btn-sm" style="background: #95a5a6; color: white; margin-bottom: 0;">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    @if(count($problems) > 0)
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Rating</th>
                    <th>Tags</th>
                    <th>Platform</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($problems as $p)
                <tr>
                    <td><a href="/problems/{{ $p->problem_id }}" style="color: #3498db; text-decoration: none;">{{ $p->title }}</a></td>
                    <td><span class="badge badge-purple">{{ $p->rating ?? 'N/A' }}</span></td>
                    <td>
                        <div class="tag-list">
                            @if($p->tags)
                                @foreach(explode(',', $p->tags) as $tag)
                                    <span class="tag-chip">{{ trim($tag) }}</span>
                                @endforeach
                            @else
                                <span class="badge badge-gray">No tags</span>
                            @endif
                        </div>
                    </td>
                    <td><span class="badge badge-gray">{{ $p->platform ?? 'Unknown' }}</span></td>
                    <td>
                        <a href="/submit/{{ $p->problem_id }}" class="btn btn-success btn-sm">Solve</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p>No problems found matching your filters.</p>
        </div>
    @endif
</div>
@endsection