<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Disabled</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        main { background: #fff; border: 1px solid #d9e0e7; max-width: 480px; padding: 32px; text-align: center; box-shadow: 0 8px 28px rgba(0, 0, 0, .08); }
        h1 { margin-top: 0; color: #1f2937; }
        p { color: #4b5563; line-height: 1.5; }
        a { display: inline-block; margin-top: 16px; background: #0d6efd; color: #fff; padding: 10px 18px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <main>
        <h1>Registration Disabled</h1>
        <p>Self-registration is not enabled for this application. Please contact the administrator to create or activate an account.</p>
        <a href="{{ route('login') }}">Go to Login</a>
    </main>
</body>
</html>
