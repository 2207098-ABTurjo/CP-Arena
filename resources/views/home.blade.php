<!DOCTYPE html>
<html>
<head>
    <title>CP-Arena Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f0f2f5; }
        .navbar { background: #2c3e50; color: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { font-size: 20px; font-weight: bold; }
        .navbar .menu a { color: white; text-decoration: none; margin-left: 25px; font-size: 14px; }
        .navbar .menu a:hover { text-decoration: underline; }
        .container { padding: 40px; max-width: 1000px; margin: 0 auto; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h1 { color: #2c3e50; margin-top: 0; }
        .stats { display: flex; gap: 20px; margin-top: 20px; }
        .stat-box { flex: 1; background: #ecf0f1; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-box h3 { margin: 0; color: #7f8c8d; font-size: 14px; }
        .stat-box p { margin: 10px 0 0; font-size: 24px; font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="logo">🏆 CP-Arena</div>
        <div class="menu">
            <a href="/home">Home</a>
            <a href="#">Submissions</a>
            <a href="#">Problems</a>
            <a href="#">Recommendations</a>
            <a href="/logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h1>Welcome, {{ $username }}! 👋</h1>
            <p>This is your personal CP practice tracker. More features will be added gradually.</p>
            
            <div class="stats">
                <div class="stat-box">
                    <h3>Total Solves</h3>
                    <p>0</p>
                </div>
                <div class="stat-box">
                    <h3>Rating Categories</h3>
                    <p>0</p>
                </div>
                <div class="stat-box">
                    <h3>Tag Categories</h3>
                    <p>0</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>