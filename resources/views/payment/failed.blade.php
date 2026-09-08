@extends('layouts.app')

@section('title', 'Payment Failed')

@section('content')

    <div class="flex min-h-[70vh] items-center justify-center px-4 py-10">

        <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-lg">

            {{-- Failure Icon --}}
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100">
                <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900">
                Payment Failed
            </h1>

            <p class="mt-3 text-gray-600">
                Unfortunately, your payment could not be completed.
                Please try again.
            </p>

            @if(request('message'))
                <div class="mt-6 rounded-lg bg-red-50 p-4 text-left">
                    <p class="text-sm font-medium text-red-800">
                        {{ request('message') }}
                    </p>
                </div>
            @endif

            @if(request('payment_id'))
                <div class="mt-4 rounded-lg bg-gray-50 p-4 text-left">
                    <p class="text-sm text-gray-500">
                        Payment ID
                    </p>

                    <p class="mt-1 break-all font-mono text-sm text-gray-900">
                        {{ request('payment_id') }}
                    </p>
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">

                <a href="{{ route('payment.index') }}"
                    class="rounded-lg bg-orange-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-700">
                    Try Again
                </a>

                <a href="{{ url('/') }}"
                    class="rounded-lg border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Go Home
                </a>

            </div>

        </div>

    </div>

@endsection