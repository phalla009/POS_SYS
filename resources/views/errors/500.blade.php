<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f1420;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            text-align: center;
            max-width: 480px;
        }
        .icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 24px;
            background: rgba(239, 68, 68, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon svg {
            width: 36px;
            height: 36px;
            color: #ef4444;
        }
        h1 {
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #f8fafc;
        }
        p {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-block;
            padding: 10px 24px;
            background: #3b82f6;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #2563eb;
        }
        .code {
            margin-top: 24px;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
        </svg>
    </div>
    <h1>We'll be right back</h1>
    <p>Sorry, something went wrong on our end. Our team has been notified — please try again in a few moments.</p>
    <a href="{{ url('/') }}" class="btn">Go to Homepage</a>
    <div class="code">Error 500</div>
</div>
</body>
</html>
