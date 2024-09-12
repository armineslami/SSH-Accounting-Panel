<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include("layouts.head")
    <body class="font-sans antialiased">
        <div class="">
            @include('components.pull-to-refresh')
            <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
                @include('components.banner')
                @include('layouts.navigation')

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white dark:bg-gray-800 shadow">
                        <div class="px-4 sm:px-8 py-6">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
