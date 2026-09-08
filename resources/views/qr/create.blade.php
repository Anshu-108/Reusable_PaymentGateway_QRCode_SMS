@extends('layouts.app')

@section('title', 'Generate QR')

@section('content')

    <div class="min-h-screen bg-gray-100 py-10">
        <div class="mx-auto max-w-2xl px-4">
            <div class="rounded-2xl bg-white p-6 shadow-lg sm:p-8">
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Generate QR Code
                    </h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Enter your details to Generate QR Code.
                    </p>
                </div>

                @if(session('error'))
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                        <p class="text-sm text-red-700">
                            {{ session('error') }}
                        </p>
                    </div>
                @endif

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

                <form method="POST" action="{{ route('qr.generate') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                            Name
                        </label>

                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            maxlength="255"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter your name">

                        @error('name')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-gray-700">
                            Email Address
                        </label>

                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            maxlength="255"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter your email">

                        @error('email')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="mb-2 block text-sm font-medium text-gray-700">
                            Mobile Number
                        </label>

                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                            maxlength="10" minlength="10" pattern="[6-9][0-9]{9}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter 10 digit mobile number">

                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="website" class="mb-2 block text-sm font-medium text-gray-700">
                            Website
                        </label>

                         <input type="tel" id="website" name="website" value="{{ old('website') }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="https://example.com">

                        @error('website')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="address" class="mb-2 block text-sm font-medium text-gray-700">
                            Address
                        </label>

                        <textarea id="address" name="address" rows="4" maxlength="500"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter Address">{{ old('address') }}</textarea>

                        @error('address')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        style="background-color: #f97316; color: white;"
                        class="w-full rounded-lg px-6 py-3 font-semibold transition hover:opacity-90 cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-300">
                        Generate QR Code
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
@endsection
