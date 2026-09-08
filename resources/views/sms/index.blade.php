@extends('layouts.app')

@section('title', 'Send SMS')

@section('content')

    <div class="min-h-screen bg-gray-100 py-10">
        <div class="mx-auto max-w-2xl px-4">
            <div class="rounded-2xl bg-white p-6 shadow-lg sm:p-8">

                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Send SMS
                    </h1>

                    <p class="mt-2 text-sm text-gray-500">
                        Enter the mobile number and message to send SMS.
                    </p>
                </div>

                {{-- Success Message --}}
                @if(session('success'))
                    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4">
                        <p class="text-sm text-green-700">
                            {{ session('success') }}
                        </p>
                    </div>
                @endif

                {{-- Error Message --}}
                @if(session('error'))
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                        <p class="text-sm text-red-700">
                            {{ session('error') }}
                        </p>
                    </div>
                @endif

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                        <ul class="list-inside list-disc text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('sms.send') }}"
                      class="space-y-6">

                    @csrf

                    {{-- Mobile Number --}}
                    <div>
                        <label for="mobile"
                               class="mb-2 block text-sm font-medium text-gray-700">
                            Mobile Number
                        </label>

                        <input
                            type="tel"
                            id="mobile"
                            name="mobile"
                            value="{{ old('mobile') }}"
                            maxlength="20"
                            minlength="10"
                            inputmode="numeric"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter 10 digit mobile number">

                        @error('mobile')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message"
                               class="mb-2 block text-sm font-medium text-gray-700">
                            SMS Message
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            rows="5"
                            maxlength="1000"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter SMS message">{{ old('message') }}</textarea>

                        <div class="mt-1 flex justify-between">
                            @error('message')
                                <p class="text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @else
                                <span></span>
                            @enderror

                            <span id="characterCount"
                                  class="text-xs text-gray-500">
                                0 / 1000
                            </span>
                        </div>
                    </div>

                    {{-- Send Button --}}
                    <button
                        type="submit"
                        style="background-color: #f97316; color: white;"
                        class="w-full rounded-lg px-6 py-3 font-semibold transition hover:opacity-90 cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-300">

                        Send SMS

                    </button>

                </form>

            </div>
        </div>
    </div>

    <script>
        const message = document.getElementById('message');
        const characterCount = document.getElementById('characterCount');

        message.addEventListener('input', function () {
            characterCount.textContent =
                this.value.length + ' / 1000';
        });

        // Set count when validation fails
        characterCount.textContent =
            message.value.length + ' / 1000';
    </script>

@endsection