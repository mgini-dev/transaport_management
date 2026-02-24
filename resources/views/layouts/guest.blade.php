<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('layouts.partials.head')
    <body class="nmis-body">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8">
            <a href="/" class="mb-6 flex items-center gap-3">
                <x-application-logo class="h-12 w-auto" />
                <span class="text-xl font-bold text-white">Nexus NMIS</span>
            </a>

            <div class="w-full sm:max-w-md px-6 py-5 bg-white shadow-xl rounded-xl border border-slate-200">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
