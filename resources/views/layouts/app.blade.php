<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Karyawan Irfan Putera') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireScripts

         <!-- Alpine.js -->
         <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

         <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.9/fullcalendar.min.css" rel="stylesheet">
         <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.9/fullcalendar.min.js"></script>

         <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.9/index.global.min.js"></script>
         <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.9/index.global.min.js"></script>

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased" x-data="{
    open: JSON.parse(localStorage.getItem('sidebar-open')) ?? true,
    dropdownOpen: false
    }" >
        <x-banner />

        <div class="min-h-screen bg-gray-100 flex">
            @include('components.sidebar')
            <div class="flex-1 overflow-y-auto transition-all duration-300"
                :class="open ? 'ml-40' : 'ml-14'">
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="p-4">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('modals')
    </body>
</html>
