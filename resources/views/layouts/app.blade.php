<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NMIS') }}</title>
    <link rel="icon" href="{{ asset('images/nexus-logo.png') }}">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Pusher -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <!-- Font Awesome (for additional icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        :root {
            --nmis-primary: #1b3b86;
            --nmis-secondary: #2a9d8f;
            --nmis-accent: #6cb63f;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 8px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Smooth transitions */
        * {
            transition: background-color 0.2s, border-color 0.2s, box-shadow 0.2s;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased" x-data="{ 
    sidebarCollapsed: false, 
    mobileSidebar: false, 
    profileOpen: false,
    darkMode: false 
}">
    <div class="min-h-screen">
        <!-- Mobile overlay -->
        <div x-show="mobileSidebar" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden" 
             @click="mobileSidebar = false">
        </div>

        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content -->
        <div class="lg:pl-64 transition-all duration-300" :class="{ 'lg:pl-20': sidebarCollapsed }">
            <!-- Navigation -->
            @include('layouts.partials.navigation')

            <!-- Page Content -->
            <main class="py-6 px-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto">
                    <!-- Header Slot -->
                    @isset($header)
                        <div class="mb-8">
                            {{ $header }}
                        </div>
                    @endisset

                    <!-- Status Messages -->
                    @if (session('status'))
                        <div class="mb-6 animate-slide-down">
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/90 backdrop-blur-sm px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-emerald-100 p-1">
                                        <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="mb-6 animate-slide-down">
                            <div class="rounded-xl border border-rose-200 bg-rose-50/90 backdrop-blur-sm px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-rose-100 p-1">
                                        <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-rose-800">{{ $errors->first() }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Main Content Slot -->
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </div>
    </div>

    @stack('scripts')
    <div id="notification-toast-container" class="fixed right-4 top-20 z-[80] space-y-3"></div>
    
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-down {
            animation: slideDown 0.3s ease-out;
        }
        
        /* Card hover effects */
        .hover-lift {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        }
        
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, var(--nmis-primary), var(--nmis-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .notification-toast-enter {
            animation: slideInRight 0.25s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(24px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
    <script>
        window.NMIS = {
            userId: {{ (int) auth()->id() }},
            pusherKey: @js(env('PUSHER_APP_KEY')),
            pusherCluster: @js(env('PUSHER_APP_CLUSTER', 'mt1')),
            csrfToken: @js(csrf_token()),
            notificationDataUrl: @js(route('notifications.index')),
        };

        function updateGlobalNotificationBadge(unreadCount) {
            const badge = document.getElementById('global-notification-badge');
            const count = document.getElementById('global-notification-count');
            if (!badge || !count) return;

            if (unreadCount > 0) {
                badge.classList.remove('hidden');
                badge.classList.add('flex');
                count.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        function showNotificationToast(payload) {
            const container = document.getElementById('notification-toast-container');
            if (!container) return;

            const title = payload?.title || 'New Notification';
            const message = payload?.message || 'You have a new update.';
            const toast = document.createElement('div');
            toast.className = 'notification-toast-enter w-80 rounded-xl border border-slate-200/70 bg-white p-4 shadow-xl';
            toast.innerHTML = `
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--nmis-primary)]/10 text-[var(--nmis-primary)]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-900">${title}</p>
                        <p class="mt-1 text-sm text-slate-600">${message}</p>
                    </div>
                </div>
            `;
            container.prepend(toast);
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        async function refreshGlobalNotificationCount() {
            try {
                const response = await fetch(`${window.NMIS.notificationDataUrl}?skip=0&take=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const payload = await response.json();
                updateGlobalNotificationBadge(payload?.meta?.unread || 0);
            } catch (error) {
                console.error('Failed to refresh notification count', error);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (!window.NMIS?.pusherKey || !window.NMIS?.userId) {
                return;
            }

            Pusher.logToConsole = false;
            const pusher = new Pusher(window.NMIS.pusherKey, {
                cluster: window.NMIS.pusherCluster,
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': window.NMIS.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            });

            const channel = pusher.subscribe(`private-App.Models.User.${window.NMIS.userId}`);
            channel.bind('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (event) => {
                showNotificationToast(event);
                refreshGlobalNotificationCount();
                if (typeof window.refreshNotificationsCenter === 'function') {
                    window.refreshNotificationsCenter();
                }
            });
        });
    </script>
</body>
</html>
