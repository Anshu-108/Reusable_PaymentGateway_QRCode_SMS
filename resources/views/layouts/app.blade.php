<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @yield('title', 'My Platform')
        </title>
        @vite('resources/css/app.css')
    </head>

    <body class="bg-gray-100 text-gray-900">
        
        <nav class="bg-white border-b border-gray-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">

                    <div class="flex items-center">
                        <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-600">
                            My Platform
                        </a>
                    </div>

                    <div class="flex items-center gap-6">
                        <a href="{{ route('payment.index') }}"
                        class="text-gray-700 hover:text-indigo-600 font-medium transition">
                            Payment Gateway
                        </a>

                        <a href="{{ route('qr.create') }}"
                        class="text-gray-700 hover:text-indigo-600 font-medium transition">
                            Generate QR
                        </a>

                        <a href="{{ route('sms.index') }}"
                        class="text-gray-700 hover:text-indigo-600 font-medium transition">
                            SMS Gateway
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        @yield('content')
    </body>

</html>