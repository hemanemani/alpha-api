<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OrgenikBulk Alpha') }}</title>

        <!-- Fonts -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
        <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="container">
            <div class="row min-h-screen sm:justify-center items-center">
                <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 sm:rounded-lg login-slot">
                    <div class="login-details">
                        <h2 class="login-slot-heading text-center">Sign In</h2>
                        <p class="login-slot-text text-center">Enter your Email and Password to sign in</p>
                    </div>
                    {{ $slot }}
                </div>
                <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6 text-center logo-heading mt-4 mb-4">
                    <a href="/">
                        Alpha
                    </a>
                    <p>
                        by Orgenik Bulk
                    </p>
                </div>
            </div>
        </div>
        <script>
            function toggleModal(show) {
            const modal = document.getElementById('forgot-password-modal');
            if (show) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            } else {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        </script>
    </body>
</html>
