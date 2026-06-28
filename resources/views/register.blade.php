<!DOCTYPE html>
<html>
<head>
    <title>CP-Arena Sign Up</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 360px; }
        h2 { text-align: center; color: #333; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #2196F3; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        button:hover { background: #1976D2; }
        .link { text-align: center; margin-top: 18px; font-size: 14px; }
        .link a { color: #2196F3; text-decoration: none; font-weight: bold; }
        .error { color: #d32f2f; font-size: 13px; text-align: center; margin: 3px 0; }
    </style>
</head>
<body>
    <div class="box">
        <h2>📝 CP-Arena Sign Up</h2>
        
        @if($errors->any())
            @foreach($errors->all() as $error)
                <p class="error">{{ $error }}</p>
            @endforeach
        @endif

        <form method="POST" action="/register">
            @csrf
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            <button type="submit">Sign Up</button>
        </form>
        
        <div class="link">
            <p>Already have an account? <a href="/login">Login here</a></p>
        </div>
    </div>
</body>
</html>