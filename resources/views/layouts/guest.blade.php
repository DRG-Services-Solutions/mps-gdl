<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <!-- Fondo con gradiente profesional de salud -->
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 via-white to-cyan-50">
            
            <!-- Decoración de fondo opcional -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5 pointer-events-none"></div>
            
            <!-- Contenedor principal -->
            <div class="relative z-10 w-full max-w-md">
                
                <!-- Logo y Header -->
                <div class="text-center mb-8">
                    <a href="/" class="inline-block">
                        <div class=" p-4 rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300">
                            <x-application-logo class="w-16 h-16 fill-current text-white" />
                        </div>
                    </a>
                    <h1 class="mt-4 text-2xl font-bold text-gray-800">
                        {{ config('app.name', 'DRGTrack') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Sistema de Gestión
                    </p>
                </div>

                <!-- Card de contenido -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <!-- Barra decorativa superior -->
                    <div class="h-2 bg-gradient-to-r from-blue-600 via-cyan-500 to-teal-500"></div>
                    
                    <!-- Contenido -->
                    <div class="px-8 py-10">
                        {{ $slot }}
                    </div>
                </div>

                <!-- Footer opcional -->
                <div class="mt-6 mb-6 text-center">
                    <p class="text-xs text-gray-500">
                        &copy; {{ date('Y') }} Distribuciones MPS. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>