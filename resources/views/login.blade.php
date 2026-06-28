<!DOCTYPE html>
<html>
<head>
    <title>CP-Arena Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 360px; }
        h2 { text-align: center; color: #333; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #45a049; }
        .link { text-align: center; margin-top: 18px; font-size: 14px; }
        .link a { color: #4CAF50; text-decoration: none; font-weight: bold; }
        .message { text-align: center; font-size: 14px; margin-bottom: 10px; }
        .error { color: #d32f2f; }
        .success { color: #388e3c; }
    </style>
</head>
<body>
    <div class="box">
        <h2>🔥 CP-Arena Login</h2>
        
        @if(session('error'))
            <p class="message error">{{ session('error') }}</p>
        @endif
        @if(session('success'))
            <p class="message success">{{ session('success') }}</p>
        @endif

        <form method="POST" action="/login">
            @csrf
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit">Login</button>
        </form>
        
        <div class="link">
            <p>New user? <a href="/register">Create Account</a></p>
        </div>
    </div>
</body>
</html>