<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 | Page Not Found</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #172033;
            background: #f4f7fb;
        }

        .error-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        .error-panel {
            width: 100%;
            max-width: 560px;
            padding: 42px 34px;
            text-align: center;
            background: #ffffff;
            border: 1px solid #dfe7f3;
            border-radius: 8px;
            box-shadow: 0 18px 45px rgba(23, 32, 51, 0.08);
        }

        .error-code {
            margin: 0 0 12px;
            font-size: 82px;
            line-height: 1;
            font-weight: 700;
            color: #0f6b8f;
        }

        .error-title {
            margin: 0 0 12px;
            font-size: 28px;
            line-height: 1.25;
            font-weight: 700;
        }

        .error-message {
            margin: 0 auto 28px;
            max-width: 420px;
            font-size: 16px;
            line-height: 1.6;
            color: #5b667a;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }

        .error-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 140px;
            min-height: 44px;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .error-button-primary {
            color: #ffffff;
            background: #0f6b8f;
            border: 1px solid #0f6b8f;
        }

        .error-button-primary:hover {
            background: #0b5877;
            border-color: #0b5877;
        }

        .error-button-secondary {
            color: #172033;
            background: #ffffff;
            border: 1px solid #c8d4e4;
        }

        .error-button-secondary:hover {
            border-color: #0f6b8f;
            color: #0f6b8f;
        }

        @media (max-width: 480px) {
            .error-panel {
                padding: 34px 22px;
            }

            .error-code {
                font-size: 64px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="error-page">
        <section class="error-panel" aria-labelledby="error-title">
            <h1 class="error-code">404</h1>
            <h2 class="error-title" id="error-title">Page Not Found</h2>
            <p class="error-message">
                The page you are looking for does not exist, has moved, or you may not have permission to access it.
            </p>
            <div class="error-actions">
                <a class="error-button error-button-primary" href="{{ url('dashboard') }}">Dashboard</a>
                <!-- <a class="error-button error-button-secondary" href="{{ url('/') }}">Go Home</a> -->
            </div>
        </section>
    </main>
</body>
</html>
