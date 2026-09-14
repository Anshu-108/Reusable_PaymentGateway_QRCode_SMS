@extends('layouts.app')

@section('title', 'Make Payment')

@section('content')

    <div class="min-h-screen bg-gray-100 py-10">
        <div class="mx-auto max-w-2xl px-4">
            <div class="rounded-2xl bg-white p-6 shadow-lg sm:p-8">
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Make Payment Test 123
                    </h1>
                    <p class="mt-2 text-sm text-gray-500">
                        Enter your details to continue with payment.
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

                <form method="POST" action="{{ route('payment.create') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="customer_name" class="mb-2 block text-sm font-medium text-gray-700">
                            Customer Name
                        </label>

                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}"
                            maxlength="255"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter your name">

                        @error('customer_name')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_email" class="mb-2 block text-sm font-medium text-gray-700">
                            Email Address
                        </label>

                        <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}"
                            maxlength="255"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter your email">

                        @error('customer_email')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="mb-2 block text-sm font-medium text-gray-700">
                            Mobile Number
                        </label>

                        <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}"
                            maxlength="10" minlength="10" pattern="[6-9][0-9]{9}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter 10 digit mobile number">

                        @error('customer_phone')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="mb-2 block text-sm font-medium text-gray-700">
                            Amount
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                                ₹
                            </span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="1" max="1000000"
                                step="0.01"
                                class="w-full rounded-lg border border-gray-300 py-3 pl-9 pr-4 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                                placeholder="Enter amount">

                        </div>

                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="mb-2 block text-sm font-medium text-gray-700">
                            Description
                        </label>

                        <textarea id="description" name="description" rows="4" maxlength="500"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm outline-none transition focus:border-orange-500 focus:ring-2 focus:ring-orange-200"
                            placeholder="Enter payment description">{{ old('description') }}</textarea>

                        @error('description')
                            <p class="mt-1 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        style="background-color: #f97316; color: white;"
                        class="w-full rounded-lg px-6 py-3 font-semibold transition hover:opacity-90 cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-300">
                        Proceed to Payment
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
@endsection