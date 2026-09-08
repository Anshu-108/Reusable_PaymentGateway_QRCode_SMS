@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')

    <div class="flex min-h-[calc(100vh-73px)] items-center justify-center px-4 py-8 sm:px-6 lg:px-8">

        <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-lg">

            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-bold text-gray-900">
                Payment Successful
            </h1>

            <p class="mt-2 text-sm text-gray-600">
                Your payment has been successfully verified.
            </p>

            @if(session('payment_id'))
                <div class="mt-6 rounded-lg bg-gray-50 p-4 text-left">
                    <p class="text-xs text-gray-500">
                        Payment ID
                    </p>

                    <p class="mt-1 break-all text-sm font-medium text-gray-900">
                        {{ session('payment_id') }}
                    </p>
                </div>
            @endif

            <a href="{{ route('payment.index') }}"
                class="mt-6 inline-block w-full rounded-lg bg-orange-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-700">
                Make Another Payment
            </a>

        </div>
    </div>

@endsection