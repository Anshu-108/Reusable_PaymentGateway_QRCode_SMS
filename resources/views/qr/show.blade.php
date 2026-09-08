@extends('layouts.app')

@section('title', 'QR Code Generated')

@section('content')

    <div class="min-h-screen bg-gray-100 py-10">
        <div class="mx-auto max-w-2xl px-4">
            <div class="rounded-2xl bg-white p-6 shadow-lg sm:p-8">
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        QR Code Generated
                    </h1>

                    <p class="mt-2 text-sm text-gray-500">
                        Your QR code has been generated successfully.
                    </p>
                </div>

                <div class="flex justify-center">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <img src="{{ asset('storage/qr-codes/' . $qrCode->qr_image) }}" alt="QR Code" class="h-72 w-72 object-contain">
                    </div>
                </div>

                <div class="mt-8 space-y-4">
                    @if ($qrCode->name)
                        <div class="flex border-b border-gray-100 pb-3">
                            <span class="w-32 font-semibold text-gray-700">
                                Name:
                            </span>
                            <span class="text-gray-600">
                                {{ $qrCode->name }}
                            </span>
                        </div>
                    @endif

                    @if ($qrCode->phone)
                        <div class="flex border-b border-gray-100 pb-3">
                            <span class="w-32 font-semibold text-gray-700">
                                Phone:
                            </span>
                            <span class="text-gray-600">
                                {{ $qrCode->phone }}
                            </span>
                        </div>
                    @endif

                    @if ($qrCode->email)
                        <div class="flex border-b border-gray-100 pb-3">
                            <span class="w-32 font-semibold text-gray-700">
                                Email:
                            </span>
                            <span class="text-gray-600">
                                {{ $qrCode->email }}
                            </span>
                        </div>
                    @endif

                    @if ($qrCode->website)
                        <div class="flex border-b border-gray-100 pb-3">
                            <span class="w-32 font-semibold text-gray-700">
                                Website:
                            </span>
                            <span class="text-gray-600">
                                {{ $qrCode->website }}
                            </span>
                        </div>
                    @endif

                    @if ($qrCode->address)
                        <div class="flex border-b border-gray-100 pb-3">
                            <span class="w-32 font-semibold text-gray-700">
                                Address:
                            </span>
                            <span class="text-gray-600">
                                {{ $qrCode->address }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="mt-8 flex justify-center">
                    <a
                        href="{{ route('qr.create') }}"
                        style="background-color: #f97316; color: white;"
                        class="w-full rounded-lg px-6 py-3 text-center font-semibold transition hover:opacity-90 cursor-pointer focus:outline-none focus:ring-2 focus:ring-orange-300"
                    >
                        Generate New QR
                    </a>
                </div>

            </div>
        </div>
    </div>

@endsection
