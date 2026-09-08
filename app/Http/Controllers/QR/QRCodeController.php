<?php

namespace App\Http\Controllers\QR;

use App\Http\Controllers\Controller;
use App\Models\QRCode;
use Illuminate\Http\Request;
use App\Services\QR\QRCodeService;

class QRCodeController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function create()
    {
        return view('qr.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:191'],
            'phone'     => ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'],
            'email'     => ['required', 'email', 'max:191'],
            'website'   => ['nullable', 'url', 'max:191'],
            'address'   => ['nullable', 'string', 'max:500'],
        ]);

        $qrCode = $this->qrCodeService->generate($validated);

        return redirect()->route('qr.show', $qrCode->qr_id);
    }

    public function show(string $qrId) {
        $qrCode = QRCode::where('qr_id', $qrId)->firstOrFail();

        return view('qr.show', compact('qrCode'));
    }
}
