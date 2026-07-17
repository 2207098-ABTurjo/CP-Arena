<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CP-Arena')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
            min-height: 100vh;
        }
        .navbar {
            background: #2c3e50;
            color: white;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 56px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar .logo {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .navbar .logo span { color: #3498db; }
        .navbar .menu { display: flex; gap: 8px; }
        .navbar .menu a {
            color: #bdc3c7;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .navbar .menu a:hover, .navbar .menu a.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8ecf1;
        }
        .card h2 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover { background: #2980b9; }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover { background: #219a52; }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover { background: #c0392b; }
        .btn-sm { padding: 6px 14px; font-size: 13px; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #555;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d5d8dc;
            border-radius: 6px;
            font-size: 14px;
            transition: border 0.2s;
            font-family: 'Consolas', 'Monaco', monospace;
        }
        .form-control:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        select.form-control { cursor: pointer; font-family: 'Segoe UI', sans-serif; }
        textarea.form-control { min-height: 300px; resize: vertical; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        tr:hover { background: #f8f9fa; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-blue { background: #e3f2fd; color: #1565c0; }
        .badge-green { background: #e8f5e9; color: #2e7d32; }
        .badge-red { background: #ffebee; color: #c62828; }
        .badge-gray { background: #f5f5f5; color: #616161; }
        .badge-purple { background: #f3e5f5; color: #6a1b9a; }
        .badge-orange { background: #fff3e0; color: #e65100; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e8ecf1;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin: 8px 0;
        }
        .stat-card .label {
            font-size: 13px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .filter-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            align-items: flex-end;
        }
        .filter-bar .form-group { margin-bottom: 0; flex: 1; min-width: 150px; }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }
        .empty-state p { margin-top: 8px; font-size: 14px; }
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .tag-chip {
            background: #e8f4f8;
            color: #2980b9;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .page-header h1 {
            font-size: 24px;
            color: #2c3e50;
        }
        .login-box {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .login-box h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #2c3e50;
        }
        .login-box .form-group { margin-bottom: 18px; }
        .login-box .btn { width: 100%; padding: 12px; margin-top: 8px; }
        .login-box .link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: #7f8c8d;
        }
        .login-box .link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .login-box .link a:hover { text-decoration: underline; }
        .error-text {
            color: #e74c3c;
            font-size: 13px;
            margin-top: 4px;
        }
        .problem-meta {
            display: flex;
            gap: 12px;
            margin: 12px 0;
            flex-wrap: wrap;
        }
        .code-editor {
            background: #1e1e1e;
            color: #d4d4d4;
            border-radius: 8px;
            padding: 16px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 14px;
            line-height: 1.6;
            min-height: 300px;
            width: 100%;
            border: none;
            resize: vertical;
        }
        .code-editor:focus {
            outline: none;
        }
        .verdict-ac { color: #27ae60; font-weight: 600; }
        .verdict-wa { color: #e74c3c; font-weight: 600; }
        .verdict-tle { color: #f39c12; font-weight: 600; }
        .verdict-re { color: #9b59b6; font-weight: 600; }
        .verdict-ce { color: #e67e22; font-weight: 600; }
    </style>
</head>
<body>
    @if(Session::has('user_id'))
    <nav class="navbar">
        <div class="logo">CP<span>-Arena</span></div>
        <div class="menu">
            <a href="/home" class="{{ request()->is('home') ? 'active' : '' }}">Dashboard</a>
            <a href="/problems" class="{{ request()->is('problems*') ? 'active' : '' }}">Problems</a>
            <a href="/submissions" class="{{ request()->is('submissions*') ? 'active' : '' }}">Submissions</a>
            <a href="/recommendations" class="{{ request()->is('recommendations') ? 'active' : '' }}">Recommendations</a>
            <a href="/logout">Logout ({{ Session::get('username') }})</a>
        </div>
    </nav>
    @endif

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>