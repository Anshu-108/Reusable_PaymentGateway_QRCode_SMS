<?php

namespace App\Http\Controllers\Api\QR;

use App\Http\Controllers\Controller;
use App\Services\QR\QRCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class QRController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Generate QR Code
     */
    public function generate(Request $request): JsonResponse
    {
        try {

            $validated = $request->validate([
                'name'      => ['required', 'string', 'max:191'],
                'phone'     => ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'],
                'email'     => ['required', 'email', 'max:191'],
                'website'   => ['nullable', 'url', 'max:191'],
                'address'   => ['nullable', 'string', 'max:500'],
            ]);

            // Use the same service as Web QR
            $qrCode = $this->qrCodeService->generate($validated);

            return response()->json([
                'status'    => true,
                'message'   => 'QR Code generated successfully.',
                'data'      => [
                    'qr_id'         => $qrCode->qr_id,
                    'name'          => $qrCode->name,
                    'phone'         => $qrCode->phone,
                    'email'         => $qrCode->email,
                    'website'       => $qrCode->website,
                    'address'       => $qrCode->address,
                    'qr_data'       => $qrCode->qr_data,
                    'qr_image'      => $qrCode->qr_image,
                    'qr_image_url'  => asset('storage/qr-codes/' . $qrCode->qr_image),
                ],
            ], 201);

        } catch (ValidationException $e) {

            return response()->json([
                'status'    => false,
                'message'   => 'Validation failed.',
                'errors'    => $e->errors(),
            ], 422);

        } catch (Throwable $e) {

            return response()->json([
                'status'    => false,
                'message'   => 'Unable to generate QR Code.',
            ], 500);
        }
    }
}