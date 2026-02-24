<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NMIS') }}</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            --nmis-primary: #1b3b86;
            --nmis-secondary: #2a9d8f;
            --nmis-accent: #6cb63f;
        }
        
        body {
            background: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-slide-down {
            animation: slideDown 0.6s ease-out forwards;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8">
        <!-- Main Card - Everything inside here -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in">
            {{ $slot }}
        </div>
        
        <!-- Footer outside card but centered -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>© {{ date('Y') }} Nexus NMIS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>