<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maru Mehsana - Place Details</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Get daily updates and local news of mehsana.">
    <meta property="og:title" content="Find Local News on Maru Mehsana">
    <meta property="og:description" content="Discover local businesses in Mehsana">
    <meta property="og:image" content="https://your-domain.com/logo.png">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #0077b6, #00a8e8);
            color: white;
            text-align: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0077b6;
            margin-bottom: 20px;
        }
        .message {
            font-size: 18px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .download-btn {
            background: #0077b6;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: background 0.3s;
        }
        .download-btn:hover {
            background: #005577;
        }
        .loading {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏪 Maru Mehsana</div>
        <div class="message">
            Opening News in the app...
            <br><br>
            If the app doesn't open automatically, download it from the Play Store.
        </div>
        
        <a href="{{ $playStoreUrl }}" class="download-btn">
            📱 Download App
        </a>
        
        <div class="loading">
            <small>Redirecting in 3 seconds...</small>
        </div>
    </div>

    <script>
        // Try to open the app
        window.location.href = '{{ $appDeepLink }}';
        
        // Fallback to Play Store after 3 seconds
        setTimeout(function() {
            window.location.href = '{{ $playStoreUrl }}';
        }, 3000);
    </script>
</body>
</html>