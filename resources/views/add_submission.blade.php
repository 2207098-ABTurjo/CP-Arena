@extends('layouts.app')

@section('title', 'Add Submission - CP-Arena')

@section('content')
<div class="page-header">
    <h1>Record Submission</h1>
</div>

<div class="card" style="max-width: 500px;">
    <form method="POST" action="/submissions">
        @csrf
        <div class="form-group">
            <label>Problem</label>
            <select name="problem_id" class="form-control" required>
                <option value="">Select a problem</option>
                @foreach($problems as $p)
                    <option value="{{ $p->problem_id }}">{{ $p->title }} ({{ $p->platform }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="Accepted">Accepted</option>
                <option value="Wrong Answer">Wrong Answer</option>
                <option value="Time Limit Exceeded">Time Limit Exceeded</option>
                <option value="Runtime Error">Runtime Error</option>
                <option value="Compilation Error">Compilation Error</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Save Submission</button>
        <a href="/submissions" class="btn" style="background: #95a5a6; color: white; margin-left: 8px;">Cancel</a>
    </form>
</div>
@endsection